<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\HistoriaClinicaIngreso */
class HistoriaClinicaIngresoResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'paciente_id' => $this->paciente_id,
            'medico_id' => $this->medico_id,
            'motivo_consulta' => $this->motivo_consulta,
            'enfermedad_actual' => $this->enfermedad_actual,
            'anamnesis' => $this->anamnesis,
            'ant_personales' => $this->ant_personales,
            'ant_familiares' => $this->ant_familiares,
            'ant_quirurgicos' => $this->ant_quirurgicos,
            'ant_patologicos' => $this->ant_patologicos,
            'ant_farmacologicos' => $this->ant_farmacologicos,
            'ant_ginecolologicos' => $this->ant_ginecolologicos,
            'impresion_diagnostica' => $this->impresion_diagnostica,
            'origen_enfermedad' => $this->origen_enfermedad,
            'plan_tratamiento' => $this->plan_tratamiento,
            'pronostico' => $this->pronostico,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
