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
        // Buscamos el ID del rol Administrador y Médico
        $rolAdmin = Rol::where('nombre', 'Administrador')->first();
        $rolmedico = Rol::where('nombre', 'Medico')->first();
        
        // Asignamos el ID encontrado
        $adminId = $rolAdmin->id;
        $medicoId = $rolmedico->id;
        
        // 1. Creación del Administrador (Sin especialidad -> ID 1)
        User::create([
            'nombre' => 'David',
            'rol_id' => $adminId,
            'especialidad_id' => 1, // <-- Agregado: Sin especificar
            'correo' => 'santiagodavid980@gmail.com',
            'password' => Hash::make('admin1234'),
        ]);

        // 2. Creación del Médico (Fisioterapia -> ID 2)
        User::create([
            'nombre' => 'Daniel (Secundario)',
            'rol_id' => $medicoId,
            'especialidad_id' => 2, // <-- Agregado: Fisioterapia
            'correo' => 'fepiperuiz11@gmail.com',
            'password' => Hash::make('admin1234'),
        ]);
    }
}