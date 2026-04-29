<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateActividadRequest extends FormRequest
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
            'objetivo_id' => ['sometimes', 'integer', 'exists:objetivos,id'],
            'nombre' => ['sometimes', 'required', 'string', 'max:150'],
        ];
    }
}
