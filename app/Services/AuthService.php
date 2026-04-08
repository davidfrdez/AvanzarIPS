<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    public function login(array $data): array
    {
        $user = User::where('correo', $data['correo'])->first();

        // 2. Validación: ¿El usuario existe?
        if (!$user) {
            throw ValidationException::withMessages([
                'correo' => ['Este correo electrónico no está registrado.'],
            ]);
        }

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'correo' => ['Las credenciales son incorrectas.'],
            ]);
        }

        if (!$user->esta_activo) {
            throw ValidationException::withMessages([
                'correo' => ['Tu cuenta está desactivada. Contacta al administrador.'],
            ]);
        }

        // Limpieza opcional: borrar tokens viejos antes de crear uno nuevo (evita basura en la DB)
        $user->tokens()->delete();

        $token = $user->createToken('AvanzarAuthToken')->plainTextToken;

        return [
            'token' => $token,
            'user'  => $user
        ];
    }

    /**
     * @param User $user
     */
    public function logout($user): void
    {
        // 1. Verificamos que el usuario tenga un token actual (para evitar error si es null)
        $token = $user->currentAccessToken();

        // 2. Usamos una validación para que el editor sepa qué clase es
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        activity()->causedBy($user)
            ->withProperties([
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ])
            ->log('El usuario cerró sesión exitosamente');
    }
}
