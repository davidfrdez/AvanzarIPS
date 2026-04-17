<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rol;
class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Creamos los roles usando Eloquent
        Rol::create(['nombre' => 'Administrador']);
        Rol::create(['nombre' => 'Medico']);
        Rol::create(['nombre' => 'Coordinador']);
    }
}
