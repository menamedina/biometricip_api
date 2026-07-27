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
    // Grupos: [columna inicio, etiqueta]
    // A-B: Departamento, C-D: Cargo, E-F: Horario, G-H: Empleador, I-J: Lider, K: Rol, L: Activo
    private const GROUPS = [
        ['col' => 'A', 'label' => 'Departamento'],
        ['col' => 'C', 'label' => 'Cargo'],
        ['col' => 'E', 'label' => 'Horario'],
        ['col' => 'G', 'label' => 'Empleador'],
        ['col' => 'I', 'label' => 'Lider'],
    ];

    public function __construct(
        private Collection $deptos,
        private Collection $cargos,
        private Collection $horarios,
        private Collection $empleadores,
        private Collection $lideres,
    ) {}

    public function title(): string
    {
        return 'Listas';
    }

    public function array(): array
    {
        $maxRows = max(
            $this->deptos->count(),
            $this->cargos->count(),
            $this->horarios->count(),
            $this->empleadores->count(),
            $this->lideres->count(),
            3,
        );

        $roles   = ['empleado', 'supervisor', 'admin'];
        $activos = ['1', '0'];

        // Fila 1: encabezados de grupo (merged en AfterSheet)
        // Fila 2: sub-encabezados ID | Nombre
        // Las dos primeras filas del array se añaden antes de los datos
        $rows = [
            // fila 1 — marcadores de posición; el merge se hace en AfterSheet
            ['Departamento', '', 'Cargo', '', 'Horario', '', 'Empleador', '', 'Lider', '', 'Rol', 'Activo'],
            // fila 2 — sub-encabezados
            ['ID', 'Nombre', 'ID', 'Nombre', 'ID', 'Nombre', 'ID', 'Nombre', 'ID', 'Nombre', '', ''],
        ];

        for ($i = 0; $i < $maxRows; $i++) {
            $depto     = $this->deptos->get($i);
            $cargo     = $this->cargos->get($i);
            $horario   = $this->horarios->get($i);
            $empleador = $this->empleadores->get($i);
            $lider     = $this->lideres->get($i);

            $rows[] = [
                $depto?->id     ?? '', $depto?->nombre     ?? '',
                $cargo?->id     ?? '', $cargo?->nombre     ?? '',
                $horario?->id   ?? '', $horario?->nombre   ?? '',
                $empleador?->id ?? '', $empleador?->nombre ?? '',
                $lider?->id     ?? '', $lider?->name       ?? '',
                $roles[$i]   ?? '',
                $activos[$i] ?? '',
            ];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws = $event->sheet->getDelegate();

                // Autosize
                foreach (range('A', 'L') as $col) {
                    $ws->getColumnDimension($col)->setAutoSize(true);
                }

                // ── Fila 1: merge y estilo de grupos ─────────────────────────
                $groupStyle = [
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ];

                foreach (self::GROUPS as $g) {
                    $col1 = $g['col'];
                    $col2 = chr(ord($col1) + 1);
                    $ws->mergeCells("{$col1}1:{$col2}1");
                    $ws->getStyle("{$col1}1:{$col2}1")->applyFromArray($groupStyle);
                }

                // Rol y Activo en fila 1
                $ws->getStyle('K1:L1')->applyFromArray($groupStyle);

                // ── Fila 2: sub-encabezados ───────────────────────────────────
                $subStyle = [
                    'font'      => ['bold' => true],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ];
                $ws->getStyle('A2:L2')->applyFromArray($subStyle);

                // Congelar las dos filas de encabezado
                $ws->freezePane('A3');
            },
        ];
    }
}
