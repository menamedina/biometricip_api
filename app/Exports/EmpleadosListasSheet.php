<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EmpleadosListasSheet implements FromArray, WithTitle, WithEvents
{
    // Cols A-F: "id - nombre" por catálogo (referenciados por dropdowns de hoja Datos)
    //   A Departamento, B Cargo, C Horario, D Empleador, E Lider, F Sede

    public function __construct(
        private Collection $deptos,
        private Collection $cargos,
        private Collection $horarios,
        private Collection $empleadores,
        private Collection $lideres,
        private Collection $sedes,
    ) {}

    public function title(): string { return 'Listas'; }

    public function array(): array
    {
        $maxRows = max(
            $this->deptos->count(),
            $this->cargos->count(),
            $this->horarios->count(),
            $this->empleadores->count(),
            $this->lideres->count(),
            $this->sedes->count(),
            1,
        );

        // Fila 1: encabezados
        $rows = [
            ['Departamento', 'Cargo', 'Horario', 'Empleador', 'Lider', 'Sede'],
        ];

        for ($i = 0; $i < $maxRows; $i++) {
            $depto     = $this->deptos->get($i);
            $cargo     = $this->cargos->get($i);
            $horario   = $this->horarios->get($i);
            $empleador = $this->empleadores->get($i);
            $lider     = $this->lideres->get($i);
            $sede      = $this->sedes->get($i);

            $rows[] = [
                $depto     ? "{$depto->id} - {$depto->nombre}"         : '',
                $cargo     ? "{$cargo->id} - {$cargo->nombre}"         : '',
                $horario   ? "{$horario->id} - {$horario->nombre}"     : '',
                $empleador ? "{$empleador->id} - {$empleador->nombre}" : '',
                $lider     ? "{$lider->id} - {$lider->name}"           : '',
                $sede      ? "{$sede->id} - {$sede->nombre}"           : '',
            ];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws      = $event->sheet->getDelegate();
                $lastRow = $ws->getHighestRow();

                foreach (range('A', 'F') as $col) {
                    $ws->getColumnDimension($col)->setAutoSize(true);
                }

                // Fila 1: encabezado azul centrado
                $ws->getStyle('A1:F1')->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Filas de datos: fondo verde muy claro
                if ($lastRow >= 2) {
                    $ws->getStyle("A2:F{$lastRow}")->getFill()
                       ->setFillType(Fill::FILL_SOLID)
                       ->getStartColor()->setRGB('F0FFF0');
                }

                $ws->freezePane('A2');
            },
        ];
    }
}
