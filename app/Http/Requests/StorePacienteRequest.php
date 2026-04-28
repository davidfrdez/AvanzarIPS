<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePacienteRequest extends FormRequest
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
            'tipo_documento' => ['required', 'string'],
            'cedula' => ['required', 'string', 'unique:pacientes,cedula'],
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'fecha_nacimiento' => ['required', 'date'],
            'sexo' => ['required', 'string'],
            'direccion' => ['required', 'string'],
            'barrio' => ['required', 'string'],
            'telefono' => ['required', 'string'],
            'correo' => ['nullable', 'email'],
            'ocupacion' => ['nullable', 'string'],
            'eps' => ['required', 'string'],
            'regimen_salud' => ['nullable', 'string'],
            'categoria_eps' => ['nullable', 'string'],
            'nombre_responsable' => ['nullable', 'string'],
            'telefono_responsable' => ['nullable', 'string'],
            'parentesco_responsable' => ['nullable', 'string'],
        ];
    }
    public function messages(): array
    {
        return [
            'cedula.unique' => 'Ya existe un paciente registrado con este número de cédula.'
        ];
    }
}
