<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'     => $this->id,
            'nombre' => $this->nombre,
            'correo' => $this->correo,
            'rol_id' => $this->rol_id,
            'activo' => (bool) $this->esta_activo,
        ];
    }
}