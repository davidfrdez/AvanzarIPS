<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\Sexo;
use App\Enums\TipoDocumento;
use Carbon\CarbonImmutable;

final readonly class PacienteDTO
{
    public function __construct(
        public TipoDocumento $tipoDocumento,
        public string $cedula,
        public string $nombres,
        public string $apellidos,
        public CarbonImmutable $fechaNacimiento,
        public Sexo $sexo,
        public string $direccion,
        public string $barrio,
        public string $telefono,
        public string $eps,
        public ?string $correo = null,
        public ?string $ocupacion = null,
        public ?string $regimenSalud = null,
        public ?string $categoriaEps = null,
        public ?string $nombreResponsable = null,
        public ?string $telefonoResponsable = null,
        public ?string $parentescoResponsable = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            tipoDocumento: TipoDocumento::from((string) $data['tipo_documento']),
            cedula: (string) $data['cedula'],
            nombres: (string) $data['nombres'],
            apellidos: (string) $data['apellidos'],
            fechaNacimiento: CarbonImmutable::parse((string) $data['fecha_nacimiento']),
            sexo: Sexo::from((string) $data['sexo']),
            direccion: (string) $data['direccion'],
            barrio: (string) $data['barrio'],
            telefono: (string) $data['telefono'],
            eps: (string) $data['eps'],
            correo: isset($data['correo']) ? (string) $data['correo'] : null,
            ocupacion: isset($data['ocupacion']) ? (string) $data['ocupacion'] : null,
            regimenSalud: isset($data['regimen_salud']) ? (string) $data['regimen_salud'] : null,
            categoriaEps: isset($data['categoria_eps']) ? (string) $data['categoria_eps'] : null,
            nombreResponsable: isset($data['nombre_responsable']) ? (string) $data['nombre_responsable'] : null,
            telefonoResponsable: isset($data['telefono_responsable']) ? (string) $data['telefono_responsable'] : null,
            parentescoResponsable: isset($data['parentesco_responsable']) ? (string) $data['parentesco_responsable'] : null,
        );
    }

    /** @return array<string, scalar|null> */
    public function toModelArray(): array
    {
        return [
            'tipo_documento' => $this->tipoDocumento->value,
            'cedula' => $this->cedula,
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'fecha_nacimiento' => $this->fechaNacimiento->toDateString(),
            'sexo' => $this->sexo->value,
            'direccion' => $this->direccion,
            'barrio' => $this->barrio,
            'telefono' => $this->telefono,
            'correo' => $this->correo,
            'ocupacion' => $this->ocupacion,
            'eps' => $this->eps,
            'regimen_salud' => $this->regimenSalud,
            'categoria_eps' => $this->categoriaEps,
            'nombre_responsable' => $this->nombreResponsable,
            'telefono_responsable' => $this->telefonoResponsable,
            'parentesco_responsable' => $this->parentescoResponsable,
        ];
    }
}
