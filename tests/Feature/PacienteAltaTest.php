<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Paciente;
use App\Models\Permiso;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Cubre los endpoints de alta/reactivación de pacientes:
 *   PUT /api/pacientes/{id}/alta        → esta_activo = false
 *   PUT /api/pacientes/{id}/reactivar   → esta_activo = true
 *
 * También verifica que GET /api/pacientes filtre por ?estado=
 */
final class PacienteAltaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Paciente $paciente;

    protected function setUp(): void
    {
        parent::setUp();

        // --- Rol con permiso pacientes.gestionar ---
        $rol = Rol::create(['nombre' => 'Administrador']);
        $permiso = Permiso::create([
            'nombre'      => 'Gestionar Pacientes',
            'vista'       => 'pacientes.gestionar',
            'descripcion' => 'Alta y reactivación de pacientes',
        ]);
        $rol->permisos()->attach($permiso->id);

        $this->admin = User::create([
            'nombre'      => 'Admin Test',
            'correo'      => 'admin@test.com',
            'password'    => Hash::make('secret'),
            'rol_id'      => $rol->id,
            'esta_activo' => true,
        ]);

        $this->paciente = Paciente::create([
            'tipo_documento' => 'CC',
            'cedula'         => '987654321',
            'nombres'        => 'Maria',
            'apellidos'      => 'Lopez',
            'fecha_nacimiento' => '1985-06-15',
            'sexo'           => 'F',
            'direccion'      => 'Carrera 5 # 10-20',
            'barrio'         => 'Chapinero',
            'telefono'       => '3109876543',
            'eps'            => 'NUEVA EPS',
            'esta_activo'    => true,
        ]);
    }

    /** PUT /alta → esta_activo pasa a false */
    public function test_dar_alta_desactiva_el_paciente(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson("/api/pacientes/{$this->paciente->id}/alta");

        $response->assertOk()
            ->assertJsonPath('data.esta_activo', false);

        $this->assertDatabaseHas('pacientes', [
            'id'          => $this->paciente->id,
            'esta_activo' => false,
        ]);
    }

    /** PUT /reactivar → esta_activo vuelve a true */
    public function test_reactivar_activa_el_paciente_dado_de_alta(): void
    {
        Sanctum::actingAs($this->admin);

        // Primero dar de alta
        $this->paciente->update(['esta_activo' => false]);

        $response = $this->putJson("/api/pacientes/{$this->paciente->id}/reactivar");

        $response->assertOk()
            ->assertJsonPath('data.esta_activo', true);

        $this->assertDatabaseHas('pacientes', [
            'id'          => $this->paciente->id,
            'esta_activo' => true,
        ]);
    }

    /** GET /pacientes por defecto solo devuelve activos */
    public function test_index_devuelve_solo_activos_por_defecto(): void
    {
        Sanctum::actingAs($this->admin);

        // Crear un segundo paciente inactivo
        Paciente::create([
            'tipo_documento' => 'CC',
            'cedula'         => '111222333',
            'nombres'        => 'Carlos',
            'apellidos'      => 'Ruiz',
            'fecha_nacimiento' => '1970-03-01',
            'sexo'           => 'M',
            'direccion'      => 'Av 1 # 2-3',
            'barrio'         => 'Sur',
            'telefono'       => '3001112222',
            'eps'            => 'SURA',
            'esta_activo'    => false,
        ]);

        $response = $this->getJson('/api/pacientes');

        $response->assertOk();
        // Solo debe aparecer el paciente activo del setUp
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($this->paciente->id));
        $this->assertFalse($ids->contains(fn ($id) => $id !== $this->paciente->id));
    }

    /** GET /pacientes?estado=inactivos filtra dados de alta */
    public function test_index_con_estado_inactivos_filtra_correctamente(): void
    {
        Sanctum::actingAs($this->admin);

        $this->paciente->update(['esta_activo' => false]);

        $response = $this->getJson('/api/pacientes?estado=inactivos');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($this->paciente->id));
    }

    /** GET /pacientes?estado=todos devuelve activos e inactivos */
    public function test_index_con_estado_todos_incluye_ambos(): void
    {
        Sanctum::actingAs($this->admin);

        $inactivo = Paciente::create([
            'tipo_documento' => 'CC',
            'cedula'         => '555666777',
            'nombres'        => 'Pedro',
            'apellidos'      => 'Vega',
            'fecha_nacimiento' => '1960-01-01',
            'sexo'           => 'M',
            'direccion'      => 'Calle 9 # 1-1',
            'barrio'         => 'Norte',
            'telefono'       => '3005556677',
            'eps'            => 'COMPENSAR',
            'esta_activo'    => false,
        ]);

        $response = $this->getJson('/api/pacientes?estado=todos');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($this->paciente->id));
        $this->assertTrue($ids->contains($inactivo->id));
    }

    /** DELETE desactiva el paciente en lugar de eliminarlo */
    public function test_delete_desactiva_en_lugar_de_eliminar(): void
    {
        Sanctum::actingAs($this->admin);

        $this->deleteJson("/api/pacientes/{$this->paciente->id}")
            ->assertOk()
            ->assertJsonPath('data.esta_activo', false);

        // El registro sigue en la BD
        $this->assertDatabaseHas('pacientes', [
            'id'          => $this->paciente->id,
            'esta_activo' => false,
        ]);
        $this->assertDatabaseCount('pacientes', 1);
    }

    /** Sin permiso pacientes.gestionar → 403 */
    public function test_usuario_sin_permiso_recibe_403(): void
    {
        $rolSin = Rol::create(['nombre' => 'Terapeuta']);
        $sin = User::create([
            'nombre'      => 'Terapeuta Test',
            'correo'      => 'terapeuta@test.com',
            'password'    => Hash::make('secret'),
            'rol_id'      => $rolSin->id,
            'esta_activo' => true,
        ]);
        Sanctum::actingAs($sin);

        $this->putJson("/api/pacientes/{$this->paciente->id}/alta")
            ->assertStatus(403);

        $this->putJson("/api/pacientes/{$this->paciente->id}/reactivar")
            ->assertStatus(403);

        $this->deleteJson("/api/pacientes/{$this->paciente->id}")
            ->assertStatus(403);
    }
}
