<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Paciente */
class PacienteResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipo_documento' => $this->tipo_documento,
            'cedula' => $this->cedula,
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'nombre_completo' => trim("{$this->nombres} {$this->apellidos}"),
            'fecha_nacimiento' => $this->fecha_nacimiento instanceof \Carbon\CarbonInterface
                ? $this->fecha_nacimiento->toDateString()
                : (string) $this->fecha_nacimiento,
            'sexo' => $this->sexo,
            'direccion' => $this->direccion,
            'barrio' => $this->barrio,
            'telefono' => $this->telefono,
            'correo' => $this->correo,
            'ocupacion' => $this->ocupacion,
            'eps' => $this->eps,
            'regimen_salud' => $this->regimen_salud,
            'categoria_eps' => $this->categoria_eps,
            'nombre_responsable' => $this->nombre_responsable,
            'telefono_responsable' => $this->telefono_responsable,
            'parentesco_responsable' => $this->parentesco_responsable,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
            'historias_ingreso' => HistoriaClinicaIngresoResource::collection(
                $this->whenLoaded('historiasClinicasIngreso')
            ),
        ];
    }
}
