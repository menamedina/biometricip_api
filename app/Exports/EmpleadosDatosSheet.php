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
    // Columna en Datos → columna auxiliar "id - nombre" en hoja Listas (cols P-U, desde fila 3)
    private const DROPDOWNS = [
        'F' => 'Listas!$P$3:$P$1000',  // departamento
        'G' => 'Listas!$Q$3:$Q$1000',  // cargo
        'H' => 'Listas!$R$3:$R$1000',  // horario
        'I' => 'Listas!$S$3:$S$1000',  // empleador
        'J' => 'Listas!$T$3:$T$1000',  // lider
        'K' => '"empleado,supervisor,admin"',
        'L' => '"1,0"',
        'M' => 'Listas!$U$3:$U$1000',  // sede
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
            'sede',
        ];
    }

    public function array(): array
    {
        return [
            ['Juan Pérez', 'juan@empresa.com', '1234567890', '3001234567', '', '', '', '', '', '', 'empleado', '1', ''],
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

                foreach (range('A', 'M') as $col) {
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
