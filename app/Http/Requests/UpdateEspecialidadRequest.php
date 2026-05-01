<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateEspecialidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user !== null && $user->tienePermiso('especialidades.gestionar');
    }

    public function rules(): array
    {
        $id = $this->route('especialidad')?->id;

        return [
            'nombre' => [
                'required', 'string', 'max:120',
                Rule::unique('especialidades', 'nombre')->ignore($id),
            ],
        ];
    }
}
