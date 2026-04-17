<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User; // Asegúrate de que este sea el nombre correcto de tu modelo (User o Usuario)
use App\Models\Rol;

class UsuariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscamos el ID del rol Administrador
        $rolAdmin = Rol::where('nombre', 'Administrador')->first();
        $rolmedico = Rol::where('nombre', 'Medico')->first();
        // Asignamos el ID encontrado
        $adminId = $rolAdmin->id;
        $medicoId = $rolmedico->id;
        // Creamos los usuarios
        User::create([
            'nombre' => 'David Admin',
            'rol_id' => $adminId,
            'correo' => 'admin@test.com',
            'password' => Hash::make('admin1234'),
        ]);

        User::create([
            'nombre' => 'Daniel',
            'rol_id' => $medicoId,
            'correo' => 'Daniel@test.com',
            'password' => Hash::make('admin1234'),
        ]);

        User::create([
            'nombre' => 'David',
            'rol_id' => $adminId,
            'correo' => 'santiagodavid980@gmail.com',
            'password' => Hash::make('admin1234'),
        ]);

        User::create([
            'nombre' => 'Daniel (Secundario)',
            'rol_id' => $medicoId,
            'correo' => 'fepiperuiz11@gmail.com',
            'password' => Hash::make('admin1234'),
        ]);
    }
}
