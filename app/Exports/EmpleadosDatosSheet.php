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
    // Columna en Datos → columna en hoja Listas (cols A-F, desde fila 2)
    private const DROPDOWNS = [
        'E' => 'Listas!$A$2:$A$1000',  // departamento
        'F' => 'Listas!$B$2:$B$1000',  // cargo
        'G' => 'Listas!$C$2:$C$1000',  // horario
        'H' => 'Listas!$D$2:$D$1000',  // empleador
        'I' => 'Listas!$E$2:$E$1000',  // lider
        'J' => '"empleado,supervisor,admin"',
        'K' => 'Listas!$F$2:$F$1000',  // sede
        'L' => '"1,0"',                 // activo
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
            'departamento',
            'cargo',
            'horario',
            'empleador',
            'lider',
            'rol',
            'sede',
            'activo',
        ];
    }

    public function array(): array
    {
        return [
            ['Juan Pérez', 'juan@empresa.com', '1234567890', '3001234567', '', '', '', '', '', 'empleado', '', '1'],
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
                    for ($row = 2; $row <= 500; $row++) {
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
