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
            'cedula' => ['required', 'string', 'unique:pacientes,cedula'],
            'nombre' => ['required', 'string', 'max:255'],
            'eps' => ['required', 'string', 'max:100'],
        ];
    }
    public function messages(): array
    {
        return [
            'cedula.unique' => 'Ya existe un paciente registrado con este número de cédula.'
        ];
    }
}
