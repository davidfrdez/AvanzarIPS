<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCitaRequest extends FormRequest
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
            'paciente_id'     => ['required', 'exists:pacientes,id'],
            'medico_id'       => ['required', 'exists:usuarios,id'],
            'especialidad_id' => ['required','exists:especialidades,id'],
            'programada_para' => ['required', 'date', 'after:now'], // La cita debe ser a futuro
        ];
    }
}
