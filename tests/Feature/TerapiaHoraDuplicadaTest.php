<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Actividad;
use App\Models\Especialidad;
use App\Models\Objetivo;
use App\Models\Paciente;
use App\Models\Permiso;
use App\Models\Respuesta;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Valida la regla: un paciente NO puede tener dos terapias en la
 * misma franja horaria (hora en punto), pero SÍ puede tener varias
 * terapias distintas a distintas horas del mismo día.
 */
final class TerapiaHoraDuplicadaTest extends TestCase
{
    use RefreshDatabase;

    private User $profesional;
    private array $payload;

    protected function setUp(): void
    {
        parent::setUp();

        // --- Rol + Permiso ---
        $rol = Rol::create(['nombre' => 'Terapeuta']);
        $permiso = Permiso::create([
            'nombre'      => 'Registrar Terapia',
            'vista'       => 'terapias.registrar',
            'descripcion' => 'Puede registrar terapias',
        ]);
        $rol->permisos()->attach($permiso->id);

        // --- Profesional autenticado ---
        $this->profesional = User::create([
            'nombre'      => 'Ana Terapeuta',
            'correo'      => 'ana@example.com',
            'password'    => Hash::make('secret'),
            'rol_id'      => $rol->id,
            'esta_activo' => true,
        ]);

        // --- Árbol clínico ---
        $objetivo   = Objetivo::create(['nombre' => 'Movilidad', 'descripcion' => 'Test']);
        $actividad  = Actividad::create(['objetivo_id' => $objetivo->id, 'nombre' => 'Ejercicio']);
        $respuesta  = Respuesta::create(['actividad_id' => $actividad->id, 'texto_predeterminado' => 'Completo']);
        $especialidad = Especialidad::create(['nombre' => 'Fisioterapia']);

        // --- Paciente ---
        $paciente = Paciente::create([
            'tipo_documento'    => 'CC',
            'cedula'            => '123456789',
            'nombres'           => 'Juan',
            'apellidos'         => 'Pérez',
            'fecha_nacimiento'  => '1990-01-01',
            'sexo'              => 'M',
            'direccion'         => 'Calle 1 # 2-3',
            'barrio'            => 'Centro',
            'telefono'          => '3001234567',
            'eps'               => 'SURA',
            'regimen_salud'     => 'Contributivo',
            'categoria_eps'     => 'A',
        ]);

        // --- Payload base ---
        $this->payload = [
            'paciente_id'      => $paciente->id,
            'objetivo_id'      => $objetivo->id,
            'actividad_id'     => $actividad->id,
            'especialidad_id'  => $especialidad->id,
            'firma_electronica' => 'FIRMA_TEST_001',
            'resultados'       => [
                [
                    'respuesta_id' => $respuesta->id,
                    'marcado'      => true,
                    'notas_libres' => null,
                ],
            ],
        ];
    }

    /** Primera terapia en la franja actual → 201 Created */
    public function test_primera_terapia_se_registra_correctamente(): void
    {
        Sanctum::actingAs($this->profesional);

        $response = $this->postJson('/api/terapias', $this->payload);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseCount('terapias', 1);
    }

    /** Segunda terapia en la MISMA franja horaria → 422 */
    public function test_segunda_terapia_en_misma_hora_es_rechazada(): void
    {
        Sanctum::actingAs($this->profesional);

        // Primera terapia exitosa
        $this->postJson('/api/terapias', $this->payload)->assertStatus(201);

        // Segunda terapia en la misma franja
        $response = $this->postJson('/api/terapias', $this->payload);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonFragment(['status' => 'error']);

        // Solo debe haber una terapia registrada
        $this->assertDatabaseCount('terapias', 1);
    }

    /** Segunda terapia con fecha_hora en hora DIFERENTE → 201 (permitido) */
    public function test_terapia_en_hora_diferente_del_mismo_dia_es_permitida(): void
    {
        Sanctum::actingAs($this->profesional);

        // Primera terapia a las 08:00
        $payloadManana = array_merge($this->payload, [
            'fecha_hora' => now()->startOfDay()->addHours(8)->toDateTimeString(),
        ]);
        $this->postJson('/api/terapias', $payloadManana)->assertStatus(201);

        // Segunda terapia a las 10:00 (distinta franja) — debe ser permitida
        $payloadTarde = array_merge($this->payload, [
            'fecha_hora' => now()->startOfDay()->addHours(10)->toDateTimeString(),
        ]);
        $response = $this->postJson('/api/terapias', $payloadTarde);

        $response->assertStatus(201);
        $this->assertDatabaseCount('terapias', 2);
    }

    /** Usuario sin permiso terapias.registrar → 403 */
    public function test_usuario_sin_permiso_recibe_403(): void
    {
        $rolSinPermiso = Rol::create(['nombre' => 'Recepcionista']);
        $sinPermiso = User::create([
            'nombre'      => 'Pedro Recepción',
            'correo'      => 'pedro@example.com',
            'password'    => Hash::make('secret'),
            'rol_id'      => $rolSinPermiso->id,
            'esta_activo' => true,
        ]);

        Sanctum::actingAs($sinPermiso);

        $this->postJson('/api/terapias', $this->payload)
            ->assertStatus(403);
    }
}
