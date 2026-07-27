<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CargosTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return ['nombre', 'descripcion', 'is_active'];
    }

    public function array(): array
    {
        return [
            ['Gerente',  'Dirección general',  1],
            ['Analista', 'Análisis de datos',  1],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
