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
 * Plantilla EJEMPLO para futura carga masiva de usuarios (personal/médicos).
 *
 * Solo descarga: la importación correspondiente queda como TODO.
 * No se incluye `password` por seguridad — el flujo recomendado para futuro
 * import será generar passwords temporales y forzar reset al primer login.
 */
final class PlantillaUsuariosExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    use Exportable;

    /** @return array<int, string> */
    public function headings(): array
    {
        return [
            'nombre',
            'correo',
            'rol_id',
            'especialidad_id',
            'esta_activo', // 1 / 0
        ];
    }

    /** @return array<int, array<int, string|int>> */
    public function array(): array
    {
        return [
            ['Dra. Ejemplo', 'doctora@avanzar.test', 2, 1, 1],
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
