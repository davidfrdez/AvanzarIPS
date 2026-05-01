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

final class PlantillaPacientesHojaCatalogos implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    /**
     * @param array<int, string> $tipoDocumento
     * @param array<int, string> $sexo
     */
    public function __construct(
        private readonly array $tipoDocumento,
        private readonly array $sexo,
    ) {
    }

    public function title(): string
    {
        return 'Catálogos';
    }

    /** @return array<int, string> */
    public function headings(): array
    {
        return ['tipo_documento (válidos)', 'sexo (válidos)'];
    }

    /** @return array<int, array<int, string>> */
    public function array(): array
    {
        $rows = [];
        $max = max(count($this->tipoDocumento), count($this->sexo));
        for ($i = 0; $i < $max; $i++) {
            $rows[] = [
                $this->tipoDocumento[$i] ?? '',
                $this->sexo[$i] ?? '',
            ];
        }

        return $rows;
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
