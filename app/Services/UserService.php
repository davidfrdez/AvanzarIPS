<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Especialidad;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

final class UserService
{
    /**
     * @param  array<string, mixed>  $data
     * @throws Throwable
     */
    public function createUser(array $data): User
    {
        return DB::transaction(static function () use ($data): User {
            return User::create([
                'nombre' => (string) $data['nombre'],
                'correo' => (string) $data['correo'],
                'rol_id' => (int) $data['rol_id'],
                'especialidad_id' => isset($data['especialidad_id']) ? (int) $data['especialidad_id'] : null,
                'password' => Hash::make((string) $data['password']),
                'esta_activo' => true,
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @throws Throwable
     */
    public function updateUser(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $payload = array_filter([
                'nombre' => $data['nombre'] ?? null,
                'correo' => $data['correo'] ?? null,
                'rol_id' => isset($data['rol_id']) ? (int) $data['rol_id'] : null,
                'especialidad_id' => array_key_exists('especialidad_id', $data)
                    ? ($data['especialidad_id'] !== null ? (int) $data['especialidad_id'] : null)
                    : null,
            ], static fn ($v): bool => $v !== null);

            if (!empty($data['password'])) {
                $payload['password'] = Hash::make((string) $data['password']);
            }

            $user->update($payload);

            return $user->refresh();
        });
    }

    public function desactivar(User $user): User
    {
        $user->update(['esta_activo' => false]);
        return $user->refresh();
    }

    public function activar(User $user): User
    {
        $user->update(['esta_activo' => true]);
        return $user->refresh();
    }

    public function softDelete(User $user): bool
    {
        $user->update(['esta_activo' => false]);
        return (bool) $user->delete();
    }

    /** @return Collection<int, Rol> */
    public function getRoles(): Collection
    {
        return Rol::query()->select(['id', 'nombre'])->orderBy('nombre')->get();
    }

    /** @return Collection<int, Especialidad> */
    public function getEspecialidades(): Collection
    {
        return Especialidad::query()->select(['id', 'nombre'])->orderBy('nombre')->get();
    }

    /** @return Collection<int, User> */
    public function getAllUsers(): Collection
    {
        return User::query()->with(['rol', 'especialidad'])->orderBy('nombre')->get();
    }

    /** @return Collection<int, User> */
    public function getMedicos(): Collection
    {
        return User::query()
            ->with(['rol', 'especialidad'])
            ->where('esta_activo', true)
            ->where(function ($q): void {
                $q->whereNotNull('especialidad_id')
                  ->orWhereHas('rol', static function ($r): void {
                      $r->where('nombre', 'LIKE', '%médic%')
                        ->orWhere('nombre', 'LIKE', '%medic%')
                        ->orWhere('nombre', 'LIKE', '%especialista%')
                        ->orWhere('nombre', 'LIKE', '%profesional%');
                  });
            })
            ->orderBy('nombre')
            ->get();
    }
}
