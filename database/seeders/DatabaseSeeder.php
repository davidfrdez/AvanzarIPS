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
            RolesSeeder::class,    // 1. Primero nacen los roles
            UsuariosSeeder::class, // 2. Luego nacen los usuarios y se les asigna su rol
            PermisosSeeder::class,// 3. Luego Se crean permisos para cada rol
            PermisoRolSeeder::class, // 4. Luego nacen los usuarios y se les asigna su rol
        ]);
    }
}
