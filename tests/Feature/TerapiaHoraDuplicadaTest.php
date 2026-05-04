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
 * Valida las reglas de registro de terapias:
 *
 *  1. Un paciente NO puede tener dos terapias en la misma franja horaria
 *     (hora en punto), usando la fecha_hora enviada — no now().
 *  2. SÍ puede tener varias terapias distintas a distintas horas del mismo día.
 *  3. Registro retroactivo (fecha anterior a hoy) requiere `terapias.retroactivo`.
 *  4. Usuario sin permiso `terapias.registrar` → 403.
 */
final class TerapiaHoraDuplicadaTest extends TestCase
{
    use RefreshDatabase;

    private User $profesional;
    private User $admin;
    private array $payload;

    protected function setUp(): void
    {
        parent::setUp();

        // --- Rol terapeuta (solo terapias.registrar) ---
        $rolTerapeuta = Rol::create(['nombre' => 'Terapeuta']);
        $permisoRegistrar = Permiso::create([
            'nombre'      => 'Registrar Terapia',
            'vista'       => 'terapias.registrar',
            'descripcion' => 'Puede registrar terapias',
        ]);
        $rolTerapeuta->permisos()->attach($permisoRegistrar->id);

        // --- Rol admin (terapias.registrar + terapias.retroactivo) ---
        $rolAdmin = Rol::create(['nombre' => 'Administrador']);
        $permisoRetroactivo = Permiso::create([
            'nombre'      => 'Registro Retroactivo',
            'vista'       => 'terapias.retroactivo',
            'descripcion' => 'Registrar terapias con fecha anterior',
        ]);
        $rolAdmin->permisos()->attach([$permisoRegistrar->id, $permisoRetroactivo->id]);

        // --- Usuarios ---
        $this->profesional = User::create([
            'nombre'      => 'Ana Terapeuta',
            'correo'      => 'ana@example.com',
            'password'    => Hash::make('secret'),
            'rol_id'      => $rolTerapeuta->id,
            'esta_activo' => true,
        ]);

        $this->admin = User::create([
            'nombre'      => 'Carlos Admin',
            'correo'      => 'admin@example.com',
            'password'    => Hash::make('secret'),
            'rol_id'      => $rolAdmin->id,
            'esta_activo' => true,
        ]);

        // --- Árbol clínico ---
        $objetivo    = Objetivo::create(['nombre' => 'Movilidad', 'descripcion' => 'Test']);
        $actividad   = Actividad::create(['objetivo_id' => $objetivo->id, 'nombre' => 'Ejercicio']);
        $respuesta   = Respuesta::create(['actividad_id' => $actividad->id, 'texto_predeterminado' => 'Completo']);
        $especialidad = Especialidad::create(['nombre' => 'Fisioterapia']);

        // --- Paciente ---
        $paciente = Paciente::create([
            'tipo_documento'   => 'CC',
            'cedula'           => '123456789',
            'nombres'          => 'Juan',
            'apellidos'        => 'Pérez',
            'fecha_nacimiento' => '1990-01-01',
            'sexo'             => 'M',
            'direccion'        => 'Calle 1 # 2-3',
            'barrio'           => 'Centro',
            'telefono'         => '3001234567',
            'eps'              => 'SURA',
            'regimen_salud'    => 'Contributivo',
            'categoria_eps'    => 'A',
        ]);

        // --- Payload base (sin fecha_hora → usa now()) ---
        $this->payload = [
            'paciente_id'       => $paciente->id,
            'objetivo_id'       => $objetivo->id,
            'actividad_id'      => $actividad->id,
            'especialidad_id'   => $especialidad->id,
            'firma_electronica' => 'FIRMA_TEST_001',
            'resultados'        => [
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

        $this->postJson('/api/terapias', $this->payload)
            ->assertStatus(201)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseCount('terapias', 1);
    }

    /** Segunda terapia en la MISMA franja horaria → 422 */
    public function test_segunda_terapia_en_misma_hora_es_rechazada(): void
    {
        Sanctum::actingAs($this->profesional);

        $hora = now()->startOfDay()->addHours(9)->toDateTimeString(); // 09:00 hoy

        $this->postJson('/api/terapias', array_merge($this->payload, ['fecha_hora' => $hora]))
            ->assertStatus(201);

        // Misma hora: 09:30 cae en la misma franja 09:00-09:59
        $horaConflicto = now()->startOfDay()->addHours(9)->addMinutes(30)->toDateTimeString();
        $this->postJson('/api/terapias', array_merge($this->payload, ['fecha_hora' => $horaConflicto]))
            ->assertStatus(422)
            ->assertJsonPath('status', 'error');

        $this->assertDatabaseCount('terapias', 1);
    }

    /** Terapia en hora DIFERENTE del mismo día → 201 (permitida) */
    public function test_terapia_en_hora_diferente_del_mismo_dia_es_permitida(): void
    {
        Sanctum::actingAs($this->profesional);

        $hora1 = now()->startOfDay()->addHours(8)->toDateTimeString();  // 08:00
        $hora2 = now()->startOfDay()->addHours(10)->toDateTimeString(); // 10:00

        $this->postJson('/api/terapias', array_merge($this->payload, ['fecha_hora' => $hora1]))
            ->assertStatus(201);

        $this->postJson('/api/terapias', array_merge($this->payload, ['fecha_hora' => $hora2]))
            ->assertStatus(201);

        $this->assertDatabaseCount('terapias', 2);
    }

    /** Usuario sin terapias.registrar → 403 */
    public function test_usuario_sin_permiso_recibe_403(): void
    {
        $rolSin = Rol::create(['nombre' => 'Recepcionista']);
        $sin = User::create([
            'nombre'      => 'Pedro Recepción',
            'correo'      => 'pedro@example.com',
            'password'    => Hash::make('secret'),
            'rol_id'      => $rolSin->id,
            'esta_activo' => true,
        ]);

        Sanctum::actingAs($sin);

        $this->postJson('/api/terapias', $this->payload)
            ->assertStatus(403);
    }

    /** Terapeuta sin terapias.retroactivo manda fecha de ayer → 403 */
    public function test_terapeuta_no_puede_registrar_fecha_anterior(): void
    {
        Sanctum::actingAs($this->profesional);

        $ayer = now()->subDay()->toDateTimeString();

        $this->postJson('/api/terapias', array_merge($this->payload, ['fecha_hora' => $ayer]))
            ->assertStatus(403)
            ->assertJsonPath('status', 'error');

        $this->assertDatabaseCount('terapias', 0);
    }

    /** Admin con terapias.retroactivo puede registrar fecha anterior → 201 */
    public function test_admin_puede_registrar_terapia_retroactiva(): void
    {
        Sanctum::actingAs($this->admin);

        $ayer = now()->subDay()->startOfDay()->addHours(9)->toDateTimeString();

        $this->postJson('/api/terapias', array_merge($this->payload, ['fecha_hora' => $ayer]))
            ->assertStatus(201)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseCount('terapias', 1);
    }

    /** Admin retroactivo también respeta el bloqueo de franja horaria en fecha pasada */
    public function test_retroactivo_bloquea_duplicado_en_misma_franja_pasada(): void
    {
        Sanctum::actingAs($this->admin);

        $ayer9h    = now()->subDay()->startOfDay()->addHours(9)->toDateTimeString();    // ayer 09:00
        $ayer9h30  = now()->subDay()->startOfDay()->addHours(9)->addMinutes(30)->toDateTimeString(); // ayer 09:30

        $this->postJson('/api/terapias', array_merge($this->payload, ['fecha_hora' => $ayer9h]))
            ->assertStatus(201);

        $this->postJson('/api/terapias', array_merge($this->payload, ['fecha_hora' => $ayer9h30]))
            ->assertStatus(422); // misma franja 09:xx

        $this->assertDatabaseCount('terapias', 1);
    }
}
