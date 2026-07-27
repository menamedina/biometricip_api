<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SimpleCollectionExport implements FromArray, WithHeadings, WithStyles, WithEvents
{
    public function __construct(private array $rows) {}

    public function headings(): array
    {
        return array_keys($this->rows[0] ?? []);
    }

    public function array(): array
    {
        return array_map('array_values', $this->rows);
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
            },
        ];
    }
}
