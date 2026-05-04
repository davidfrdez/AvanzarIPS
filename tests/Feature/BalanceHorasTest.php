<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Actividad;
use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\Objetivo;
use App\Models\Paciente;
use App\Models\Permiso;
use App\Models\Respuesta;
use App\Models\Rol;
use App\Models\Terapia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Valida el control de cupo mensual de terapias:
 *
 *  - GET /api/pacientes/{id}/balance-horas devuelve el saldo correcto.
 *  - POST /api/terapias bloquea si terapias_mes >= citas_mes.
 *  - Si hay cupo disponible, la terapia se registra normalmente.
 */
final class BalanceHorasTest extends TestCase
{
    use RefreshDatabase;

    private User $profesional;
    private Paciente $paciente;
    private Especialidad $especialidad;
    private array $payloadTerapia;

    protected function setUp(): void
    {
        parent::setUp();

        $rol = Rol::create(['nombre' => 'Terapeuta']);
        $permiso = Permiso::create([
            'nombre'      => 'Registrar Terapia',
            'vista'       => 'terapias.registrar',
            'descripcion' => 'Test',
        ]);
        $rol->permisos()->attach($permiso->id);

        $this->profesional = User::create([
            'nombre'      => 'Ana',
            'correo'      => 'ana@test.com',
            'password'    => Hash::make('secret'),
            'rol_id'      => $rol->id,
            'esta_activo' => true,
        ]);

        $this->paciente = Paciente::create([
            'tipo_documento'   => 'CC',
            'cedula'           => '111222333',
            'nombres'          => 'Luis',
            'apellidos'        => 'Torres',
            'fecha_nacimiento' => '1985-03-10',
            'sexo'             => 'M',
            'direccion'        => 'Calle 5',
            'barrio'           => 'Norte',
            'telefono'         => '3101112233',
            'eps'              => 'SURA',
        ]);

        $this->especialidad = Especialidad::create(['nombre' => 'Fisioterapia']);
        $objetivo   = Objetivo::create(['nombre' => 'Movilidad', 'descripcion' => 'Test']);
        $actividad  = Actividad::create(['objetivo_id' => $objetivo->id, 'nombre' => 'Ejercicio']);
        $respuesta  = Respuesta::create(['actividad_id' => $actividad->id, 'texto_predeterminado' => 'OK']);

        $this->payloadTerapia = [
            'paciente_id'       => $this->paciente->id,
            'objetivo_id'       => $objetivo->id,
            'actividad_id'      => $actividad->id,
            'especialidad_id'   => $this->especialidad->id,
            'firma_electronica' => 'FIRMA',
            'resultados'        => [
                ['respuesta_id' => $respuesta->id, 'marcado' => true, 'notas_libres' => null],
            ],
        ];
    }

    /** Helper: crea una cita programada para este mes */
    private function crearCitaMes(int $n = 1): void
    {
        for ($i = 0; $i < $n; $i++) {
            Cita::create([
                'paciente_id'    => $this->paciente->id,
                'medico_id'      => $this->profesional->id,
                'especialidad_id' => $this->especialidad->id,
                'programada_para' => now()->startOfMonth()->addDays($i)->addHours(9),
            ]);
        }
    }

    // ── GET balance-horas ──────────────────────────────────────────────────

    /** Sin citas ni terapias → todo en 0, no puede registrar */
    public function test_balance_cero_cuando_no_hay_citas(): void
    {
        Sanctum::actingAs($this->profesional);

        $res = $this->getJson("/api/pacientes/{$this->paciente->id}/balance-horas");

        $res->assertOk()
            ->assertJsonPath('data.horas_programadas', 0)
            ->assertJsonPath('data.horas_ejecutadas', 0)
            ->assertJsonPath('data.horas_disponibles', 0)
            ->assertJsonPath('data.puede_registrar', false);
    }

    /** Con 3 citas y 1 terapia → 2 disponibles */
    public function test_balance_refleja_cupo_restante(): void
    {
        Sanctum::actingAs($this->profesional);
        $this->crearCitaMes(3);

        Terapia::create([
            'paciente_id'      => $this->paciente->id,
            'profesional_id'   => $this->profesional->id,
            'objetivo_id'      => $this->payloadTerapia['objetivo_id'],
            'actividad_id'     => $this->payloadTerapia['actividad_id'],
            'especialidad_id'  => $this->especialidad->id,
            'firma_electronica' => 'FX',
            'fecha_hora'       => now()->startOfMonth()->addHours(8),
        ]);

        $res = $this->getJson("/api/pacientes/{$this->paciente->id}/balance-horas");

        $res->assertOk()
            ->assertJsonPath('data.horas_programadas', 3)
            ->assertJsonPath('data.horas_ejecutadas', 1)
            ->assertJsonPath('data.horas_disponibles', 2)
            ->assertJsonPath('data.puede_registrar', true);
    }

    /** Parámetro ?mes= con formato incorrecto → 422 */
    public function test_mes_invalido_devuelve_422(): void
    {
        Sanctum::actingAs($this->profesional);

        $this->getJson("/api/pacientes/{$this->paciente->id}/balance-horas?mes=mayo")
            ->assertStatus(422);
    }

    // ── POST /terapias con cupo ────────────────────────────────────────────

    /** Sin citas → no puede registrar terapia → 422 */
    public function test_terapia_sin_citas_programadas_es_bloqueada(): void
    {
        Sanctum::actingAs($this->profesional);

        $this->postJson('/api/terapias', $this->payloadTerapia)
            ->assertStatus(422)
            ->assertJsonPath('status', 'error');

        $this->assertDatabaseCount('terapias', 0);
    }

    /** Con cupo → terapia se registra */
    public function test_terapia_con_cupo_disponible_se_registra(): void
    {
        Sanctum::actingAs($this->profesional);
        $this->crearCitaMes(2);

        $this->postJson('/api/terapias', $this->payloadTerapia)
            ->assertStatus(201);

        $this->assertDatabaseCount('terapias', 1);
    }

    /** Cupo agotado → segunda terapia bloqueada con datos de resumen */
    public function test_terapia_bloqueada_cuando_cupo_agotado(): void
    {
        Sanctum::actingAs($this->profesional);
        $this->crearCitaMes(1); // solo 1 cita autorizada

        // Primera terapia ocupa el cupo
        $this->postJson('/api/terapias', $this->payloadTerapia)
            ->assertStatus(201);

        // Segunda en hora diferente → bloqueada por cupo
        $payload2 = array_merge($this->payloadTerapia, [
            'fecha_hora' => now()->startOfDay()->addHours(14)->toDateTimeString(),
        ]);
        $res = $this->postJson('/api/terapias', $payload2);

        $res->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('data.horas_programadas', 1)
            ->assertJsonPath('data.horas_ejecutadas', 1);

        $this->assertDatabaseCount('terapias', 1);
    }
}
