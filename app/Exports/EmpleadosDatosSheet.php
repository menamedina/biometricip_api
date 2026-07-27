<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmpleadosDatosSheet implements FromArray, WithHeadings, WithTitle, WithStyles, WithEvents
{
    // Columna en Datos → columna ID en hoja Listas (datos desde fila 3)
    private const DROPDOWNS = [
        'F' => 'Listas!$A$3:$A$1000',  // departamento_id
        'G' => 'Listas!$C$3:$C$1000',  // cargo_id
        'H' => 'Listas!$E$3:$E$1000',  // horario_id
        'I' => 'Listas!$G$3:$G$1000',  // empleador_id
        'J' => 'Listas!$I$3:$I$1000',  // lider_id
        'K' => '"empleado,supervisor,admin"',
        'L' => '"1,0"',
    ];

    public function title(): string
    {
        return 'Empleados';
    }

    public function headings(): array
    {
        return [
            'nombre *',
            'email *',
            'cedula *',
            'telefono',
            'password',
            'departamento',
            'cargo',
            'horario',
            'empleador',
            'lider',
            'rol',
            'activo',
        ];
    }

    public function array(): array
    {
        return [
            ['Juan Pérez', 'juan@empresa.com', '1234567890', '3001234567', '', '', '', '', '', '', 'empleado', '1'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws = $event->sheet->getDelegate();

                foreach (range('A', 'L') as $col) {
                    $ws->getColumnDimension($col)->setAutoSize(true);
                }

                $ws->freezePane('A2');

                foreach (self::DROPDOWNS as $col => $formula) {
                    for ($row = 3; $row <= 500; $row++) {
                        $validation = $ws->getCell("{$col}{$row}")->getDataValidation();
                        $validation->setType(DataValidation::TYPE_LIST);
                        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                        $validation->setAllowBlank(true);
                        $validation->setShowDropDown(true);
                        $validation->setFormula1($formula);
                    }
                }

                // Columnas obligatorias con fondo amarillo
                foreach (['A', 'B', 'C'] as $col) {
                    $ws->getStyle("{$col}2:{$col}500")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('FFF2CC');
                }
            },
        ];
    }
}
