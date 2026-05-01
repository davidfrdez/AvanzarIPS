<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreEspecialidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user !== null && $user->tienePermiso('especialidades.gestionar');
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120', 'unique:especialidades,nombre'],
        ];
    }
}
