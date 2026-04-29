<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'correo' => $this->correo,
            'esta_activo' => (bool) $this->esta_activo,
            'rol_id' => $this->rol_id,
            'rol' => $this->whenLoaded('rol', fn () => [
                'id' => $this->rol->id,
                'nombre' => $this->rol->nombre,
            ]),
            'permisos' => $this->whenLoaded('rol', fn () => $this->rol->permisos->pluck('vista')->all()),
            'especialidad_id' => $this->especialidad_id,
            'especialidad' => $this->whenLoaded('especialidad', fn () => $this->especialidad ? [
                'id' => $this->especialidad->id,
                'nombre' => $this->especialidad->nombre,
            ] : null),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
            'deleted_at' => optional($this->deleted_at)->toIso8601String(),
        ];
    }
}
