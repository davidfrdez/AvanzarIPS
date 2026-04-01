<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Rol;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $rolAdmin = Rol::create(['nombre' => 'Administrador']);
        $rolUser  = Rol::create(['nombre' => 'Usuario']);

        User::factory()->create([
            'nombre' => 'Test User',
            'correo' => 'test@example.com',
            'rol_id' => $rolAdmin->id,
        ]);
        User::factory()->create([
            'nombre' => 'Daniel',
            'correo' => 'Daniel@example.com',
            'rol_id' => $rolAdmin->id,
        ]);
    }
}
