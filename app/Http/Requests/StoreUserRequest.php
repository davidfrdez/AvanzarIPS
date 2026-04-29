<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user !== null && $user->tienePermiso('usuarios.crear');
    }

    /** @return array<string, ValidationRule|array<int, mixed>|string> */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'correo' => [
                'required', 'email:rfc',
                Rule::unique('usuarios', 'correo')->whereNull('deleted_at'),
            ],
            'rol_id' => ['required', 'integer', 'exists:roles,id'],
            'especialidad_id' => ['nullable', 'integer', 'exists:especialidades,id'],
            'password' => [
                'required', 'string',
                Password::min(8)->mixedCase()->numbers(),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'correo.unique' => 'Este correo ya está registrado en el sistema.',
            'rol_id.exists' => 'El rol seleccionado no es válido.',
            'especialidad_id.exists' => 'La especialidad seleccionada no es válida.',
        ];
    }
}
