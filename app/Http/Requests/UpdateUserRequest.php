<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user !== null && $user->tienePermiso('usuarios.editar');
    }

    /** @return array<string, ValidationRule|array<int, mixed>|string> */
    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('user');

        return [
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'correo' => [
                'sometimes', 'required', 'email:rfc',
                Rule::unique('usuarios', 'correo')
                    ->ignore($userId)
                    ->whereNull('deleted_at'),
            ],
            'rol_id' => ['sometimes', 'required', 'integer', 'exists:roles,id'],
            'especialidad_id' => ['nullable', 'integer', 'exists:especialidades,id'],
            'password' => [
                'sometimes', 'nullable', 'string',
                Password::min(8)->mixedCase()->numbers(),
            ],
        ];
    }
}
