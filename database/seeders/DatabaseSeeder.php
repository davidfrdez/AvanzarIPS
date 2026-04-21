<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,          // 1. Primero nacen los roles base (Administrador, Médico, etc.)
            EspecialidadesSeeder::class, // 2. Se crean las especialidades (1: Sin especificar, 2: Fisioterapia, etc.)
            UsuariosSeeder::class,       // 3. Nacen los usuarios y se les asigna su rol y especialidad
            PermisosSeeder::class,       // 4. Se definen todos los permisos disponibles en el sistema
            PermisoRolSeeder::class,     // 5. Se vinculan los permisos específicos a cada rol (Tabla pivote)
            CitaSeeder::class,           // 6. Se programan las citas vinculando pacientes, médicos y especialidades
        ]);
    }
}
