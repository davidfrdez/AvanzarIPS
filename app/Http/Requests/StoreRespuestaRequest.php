<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreRespuestaRequest extends FormRequest
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
            'actividad_id' => ['required', 'integer', 'exists:actividades,id'],
            'texto_predeterminado' => ['required', 'string', 'max:1000'],
        ];
    }
}
