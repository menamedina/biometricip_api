<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DepartamentosTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return ['nombre', 'descripcion', 'is_active'];
    }

    public function array(): array
    {
        return [
            ['Recursos Humanos', 'Gestión del personal', 1],
            ['Tecnología',       'Innovación y sistemas', 1],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
