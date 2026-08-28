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

class EmpleadosExportDatosSheet implements FromArray, WithHeadings, WithTitle, WithStyles, WithEvents
{
    private const DROPDOWNS = [
        'E' => 'Listas!$A$2:$A$1000',
        'F' => 'Listas!$B$2:$B$1000',
        'G' => 'Listas!$C$2:$C$1000',
        'H' => 'Listas!$D$2:$D$1000',
        'I' => 'Listas!$E$2:$E$1000',
        'J' => '"empleado,supervisor,admin"',
        'K' => 'Listas!$F$2:$F$1000',
        'L' => '"1,0"',
    ];

    public function __construct(private array $rows) {}

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
        return $this->rows;
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
                $ws      = $event->sheet->getDelegate();
                $lastRow = max($ws->getHighestRow(), 2);

                foreach (range('A', 'L') as $col) {
                    $ws->getColumnDimension($col)->setAutoSize(true);
                }

                $ws->freezePane('A2');

                $dropdownEnd = max($lastRow + 100, 500);
                foreach (self::DROPDOWNS as $col => $formula) {
                    for ($row = 2; $row <= $dropdownEnd; $row++) {
                        $validation = $ws->getCell("{$col}{$row}")->getDataValidation();
                        $validation->setType(DataValidation::TYPE_LIST);
                        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                        $validation->setAllowBlank(true);
                        $validation->setShowDropDown(true);
                        $validation->setFormula1($formula);
                    }
                }

                foreach (['A', 'B', 'C'] as $col) {
                    $ws->getStyle("{$col}2:{$col}{$dropdownEnd}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('FFF2CC');
                }
            },
        ];
    }
}
