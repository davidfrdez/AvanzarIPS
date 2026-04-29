<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class HistoriaClinicaIngresoDTO
{
    public function __construct(
        public int $medicoId,
        public ?string $motivoConsulta = null,
        public ?string $enfermedadActual = null,
        public ?string $anamnesis = null,
        public ?string $antPersonales = null,
        public ?string $antFamiliares = null,
        public ?string $antQuirurgicos = null,
        public ?string $antPatologicos = null,
        public ?string $antFarmacologicos = null,
        public ?string $antGinecologicos = null,
        public ?string $impresionDiagnostica = null,
        public ?string $origenEnfermedad = null,
        public ?string $planTratamiento = null,
        public ?string $pronostico = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            medicoId: (int) $data['medico_id'],
            motivoConsulta: isset($data['motivo_consulta']) ? (string) $data['motivo_consulta'] : null,
            enfermedadActual: isset($data['enfermedad_actual']) ? (string) $data['enfermedad_actual'] : null,
            anamnesis: isset($data['anamnesis']) ? (string) $data['anamnesis'] : null,
            antPersonales: isset($data['ant_personales']) ? (string) $data['ant_personales'] : null,
            antFamiliares: isset($data['ant_familiares']) ? (string) $data['ant_familiares'] : null,
            antQuirurgicos: isset($data['ant_quirurgicos']) ? (string) $data['ant_quirurgicos'] : null,
            antPatologicos: isset($data['ant_patologicos']) ? (string) $data['ant_patologicos'] : null,
            antFarmacologicos: isset($data['ant_farmacologicos']) ? (string) $data['ant_farmacologicos'] : null,
            antGinecologicos: isset($data['ant_ginecolologicos']) ? (string) $data['ant_ginecolologicos'] : null,
            impresionDiagnostica: isset($data['impresion_diagnostica']) ? (string) $data['impresion_diagnostica'] : null,
            origenEnfermedad: isset($data['origen_enfermedad']) ? (string) $data['origen_enfermedad'] : null,
            planTratamiento: isset($data['plan_tratamiento']) ? (string) $data['plan_tratamiento'] : null,
            pronostico: isset($data['pronostico']) ? (string) $data['pronostico'] : null,
        );
    }

    /** @return array<string, scalar|null> */
    public function toModelArray(int $pacienteId): array
    {
        return [
            'paciente_id' => $pacienteId,
            'medico_id' => $this->medicoId,
            'motivo_consulta' => $this->motivoConsulta,
            'enfermedad_actual' => $this->enfermedadActual,
            'anamnesis' => $this->anamnesis,
            'ant_personales' => $this->antPersonales,
            'ant_familiares' => $this->antFamiliares,
            'ant_quirurgicos' => $this->antQuirurgicos,
            'ant_patologicos' => $this->antPatologicos,
            'ant_farmacologicos' => $this->antFarmacologicos,
            'ant_ginecolologicos' => $this->antGinecologicos,
            'impresion_diagnostica' => $this->impresionDiagnostica,
            'origen_enfermedad' => $this->origenEnfermedad,
            'plan_tratamiento' => $this->planTratamiento,
            'pronostico' => $this->pronostico,
        ];
    }
}
