<?php

declare(strict_types=1);

namespace App\Exports;

use App\Enums\Sexo;
use App\Enums\TipoDocumento;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Plantilla descargable para carga masiva de pacientes.
 *
 * Archivo xlsx con dos hojas:
 *  - "Pacientes": encabezados oficiales + 1 fila de ejemplo.
 *  - "Catálogos": valores válidos para los campos enum (tipo_documento, sexo).
 */
final class PlantillaPacientesExport implements WithMultipleSheets
{
    use Exportable;

    /** @return array<int, object> */
    public function sheets(): array
    {
        return [
            new PlantillaPacientesHojaPrincipal(),
            new PlantillaPacientesHojaCatalogos(
                tipoDocumento: TipoDocumento::values(),
                sexo: Sexo::values(),
            ),
        ];
    }
}
