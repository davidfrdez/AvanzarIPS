<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Actividad */
class ActividadResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'objetivo_id' => $this->objetivo_id,
            'nombre' => $this->nombre,
            'respuestas' => RespuestaResource::collection($this->whenLoaded('respuestas')),
        ];
    }
}
