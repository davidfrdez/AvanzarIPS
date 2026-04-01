<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
class RolesYUsuariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    
    public function run(): void
    {
        // 1. Crear Roles básicos
        $adminId = DB::table('roles')->insertGetId(['nombre' => 'Administrador']);
        DB::table('roles')->insert(['nombre' => 'Medico']);
        DB::table('roles')->insert(['nombre' => 'Coordinador']);

        // 2. Crear el primer Usuario Admin
        User::create([
            'nombre' => 'David Admin',
            'rol_id' => $adminId,
            'correo' => 'admin@test.com',
            'password' => Hash::make('admin1234'), // IMPORTANTE: Siempre usar Hash::make
        ]);
        User::create([
            'nombre' => 'Daniel',
            'rol_id' => $adminId,
            'correo' => 'Daniel@test.com',
            'password' => Hash::make('admin1234'), // IMPORTANTE: Siempre usar Hash::make
        ]);
        User::create([
            'nombre' => 'David ',
            'rol_id' => $adminId,
            'correo' => 'santiagodavid980@gmail.com',
            'password' => Hash::make('admin1234'), // IMPORTANTE: Siempre usar Hash::make
        ]);
    }
}
