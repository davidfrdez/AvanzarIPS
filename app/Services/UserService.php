<?php

namespace App\Services;

use App\Models\User;
use App\Models\Rol;
use App\Models\Paciente;
use App\Models\AuditoriaCambio;
use App\Models\Especialidad;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserService
{
    public function createUser(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'nombre' => $data['nombre'],
                'correo' => $data['correo'],
                'rol_id' => $data['rol_id'],
                'password' => Hash::make($data['password']),
                'esta_activo' => true,
            ]);

            AuditoriaCambio::create([
                'usuario_id' => Auth::id(),
                'accion' => 'CREAR',
                'nombre_tabla' => 'usuarios',
                'registro_id' => $user->id,
                'detalles' => 'Se creó un nuevo usuario con rol_id: ' . $user->rol_id,
            ]);

            return $user;
        });
    }
    public function getRoles()
    {
        return DB::transaction(function () {
            $roles = Rol::get(['id', 'nombre']);
            return $roles;
        });
    }
    public function getEspecialidades()
    {
        return DB::transaction(function () {
            $roles = Especialidad::get(['id', 'nombre']);
            return $roles;
        });
    }
    public function createPaciente(array $data)
    {
        return DB::transaction(function () use ($data) {
            
            $paciente = Paciente::create([
                'cedula' => $data['cedula'],
                'nombre' => $data['nombre'],
                'eps' => $data['eps'],
            ]);

            // Trazabilidad legal obligatoria
            AuditoriaCambio::create([
                'usuario_id' => Auth::id(), // El usuario logueado que registró al paciente
                'accion' => 'CREAR',
                'nombre_tabla' => 'pacientes',
                'registro_id' => $paciente->id,
                'detalles' => 'Se registró un nuevo paciente con cédula: ' . $paciente->cedula,
            ]);

            return $paciente;
        });
    }
}