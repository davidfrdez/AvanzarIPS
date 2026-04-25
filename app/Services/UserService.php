<?php

namespace App\Services;

use App\Models\User;
use App\Models\Rol;
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
                'especialidad_id' => $data['especialidad_id'] ?? null,
                'password' => Hash::make($data['password']),
                'esta_activo' => true,
            ]);

            AuditoriaCambio::create([
                'usuario_id' => Auth::id() ?? 1,
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
        return Rol::select(['id', 'nombre'])->get();
    }

    public function getEspecialidades()
    {
        return Especialidad::select(['id', 'nombre'])->get();
    }
}