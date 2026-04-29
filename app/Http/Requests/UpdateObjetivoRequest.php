<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateObjetivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user !== null && $user->tienePermiso('objetivos.gestionar');
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'nombre' => ['sometimes', 'required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ];
    }
}
