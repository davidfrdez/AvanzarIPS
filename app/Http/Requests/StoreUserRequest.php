<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'correo' => ['required', 'email', 'unique:usuarios,correo'],
            'rol_id' => ['required', 'exists:roles,id'],
            'password' => [
                'required',
                'string',
                Password::min(8) // Políticas de seguridad requeridas
                    ->mixedCase() // Requiere mayúsculas y minúsculas
                    ->numbers(),  // Requiere números
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'correo.unique' => 'Este correo ya está registrado en el sistema.',
            'rol_id.exists' => 'El rol seleccionado no es válido.'
        ];
    }
}
