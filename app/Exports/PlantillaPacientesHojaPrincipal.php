<?php

declare(strict_types=1);

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class PlantillaPacientesHojaPrincipal implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function title(): string
    {
        return 'Pacientes';
    }

    /** @return array<int, string> */
    public function headings(): array
    {
        return [
            'tipo_documento',
            'cedula',
            'nombres',
            'apellidos',
            'fecha_nacimiento',
            'sexo',
            'direccion',
            'barrio',
            'telefono',
            'correo',
            'ocupacion',
            'eps',
            'regimen_salud',
            'categoria_eps',
            'nombre_responsable',
            'telefono_responsable',
            'parentesco_responsable',
        ];
    }

    /** @return array<int, array<int, string>> */
    public function array(): array
    {
        return [
            [
                'CC',
                '1020304050',
                'Juan',
                'Pérez',
                '1990-05-15',
                'M',
                'Calle 123 # 45-67',
                'Centro',
                '3001234567',
                'juan@example.com',
                'Ingeniero',
                'Sura',
                'Contributivo',
                'A',
                'María Pérez',
                '3009876543',
                'Madre',
            ],
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
