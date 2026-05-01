<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class ImportPacientesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user !== null && $user->tienePermiso('pacientes.crear');
    }

    /** @return array<string, ValidationRule|array<int, mixed>|string> */
    public function rules(): array
    {
        return [
            'archivo' => [
                'required',
                'file',
                'mimes:xlsx,xls',
                'max:5120', // 5 MB
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'archivo.required' => 'Debes adjuntar el archivo Excel en el campo "archivo".',
            'archivo.mimes' => 'El archivo debe tener extensión .xlsx o .xls.',
            'archivo.max' => 'El archivo no puede superar los 5 MB.',
        ];
    }
}
