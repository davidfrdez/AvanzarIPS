<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Especialidad;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuariosSeeder extends Seeder
{
    public function run(): void
    {
        $rolAdmin = Rol::where('nombre', 'Administrador')->first();
        $rolMedico = Rol::where('nombre', 'Medico')->first();

        $espSinEspecificar = Especialidad::query()->orderBy('id')->first();
        $espFisioterapia = Especialidad::where('nombre', 'LIKE', '%Fisio%')->first() ?? $espSinEspecificar;

        if ($rolAdmin) {
            User::updateOrCreate(
                ['correo' => 'santiagodavid980@gmail.com'],
                [
                    'nombre' => 'David',
                    'rol_id' => $rolAdmin->id,
                    'especialidad_id' => $espSinEspecificar?->id,
                    'password' => Hash::make('admin1234'),
                    'esta_activo' => true,
                ]
            );
            User::updateOrCreate(
                ['correo' => 'admin@gmail.com'],
                [
                    'nombre' => 'Admin',
                    'rol_id' => $rolAdmin->id,
                    'especialidad_id' => $espSinEspecificar?->id,
                    'password' => Hash::make('admin1234'),
                    'esta_activo' => true,
                ]
            );
            User::updateOrCreate(
                ['correo' => 'contacto@dalioss.com'],
                [
                    'nombre' => 'dalioss',
                    'rol_id' => $rolAdmin->id,
                    'especialidad_id' => $espSinEspecificar?->id,
                    'password' => Hash::make('dalioss1234'),
                    'esta_activo' => true,
                ]
            );
        }

        if ($rolMedico) {
            User::updateOrCreate(
                ['correo' => 'fepiperuiz11@gmail.com'],
                [
                    'nombre' => 'Daniel (Secundario)',
                    'rol_id' => $rolMedico->id,
                    'especialidad_id' => $espFisioterapia?->id,
                    'password' => Hash::make('admin1234'),
                    'esta_activo' => true,
                ]
            );
        }
    }
}
