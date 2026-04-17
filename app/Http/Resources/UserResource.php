<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'correo' => $this->correo,
            // Obtenemos el nombre del rol si está cargado
            'rol' => $this->whenLoaded('rol', function () {
                return $this->rol->nombre;
            }),
            // Extraemos solo la columna 'vista' de la colección de permisos
            'vistas' => $this->whenLoaded('rol', function () {
                return $this->rol->permisos->pluck('vista');
            }),
        ];
    }
}
