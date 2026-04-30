<?php

declare(strict_types=1);

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Plantilla EJEMPLO para futura carga masiva de citas.
 *
 * Solo descarga: la importación correspondiente queda como TODO.
 * Sirve como referencia para que el frontend pueda mostrar la plantilla
 * y para que el equipo pueda definir el contrato cuando implementen el import.
 */
final class PlantillaCitasExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    use Exportable;

    /** @return array<int, string> */
    public function headings(): array
    {
        return [
            'paciente_id',
            'medico_id',
            'especialidad_id',
            'programada_para', // formato: YYYY-MM-DD HH:MM:SS
        ];
    }

    /** @return array<int, array<int, string|int>> */
    public function array(): array
    {
        return [
            [1, 2, 2, '2026-05-20 10:00:00'],
        ];
    }

    /** @return array<int|string, array<string, mixed>> */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1F4E78'],
                ],
            ],
        ];
    }
}
