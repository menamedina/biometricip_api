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
    // Cols A-L: pares ID|Nombre por catálogo (para BUSCARV)
    //   A-B Depto, C-D Cargo, E-F Horario, G-H Empleador, I-J Lider, K-L Sede
    //   M Rol, N Activo
    // Cols P-V: auxiliares "id - nombre" usados por los dropdowns de Datos
    //   P Depto, Q Cargo, R Horario, S Empleador, T Lider, U Sede
    private const GROUPS = [
        ['colId' => 'A', 'colNom' => 'B', 'aux' => 'P', 'label' => 'Departamento'],
        ['colId' => 'C', 'colNom' => 'D', 'aux' => 'Q', 'label' => 'Cargo'],
        ['colId' => 'E', 'colNom' => 'F', 'aux' => 'R', 'label' => 'Horario'],
        ['colId' => 'G', 'colNom' => 'H', 'aux' => 'S', 'label' => 'Empleador'],
        ['colId' => 'I', 'colNom' => 'J', 'aux' => 'T', 'label' => 'Lider'],
        ['colId' => 'K', 'colNom' => 'L', 'aux' => 'U', 'label' => 'Sede'],
    ];

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
            3,
        );

        $roles   = ['empleado', 'supervisor', 'admin'];
        $activos = ['1', '0'];

        $rows = [
            // Fila 1: encabezados de grupo (se mergen en AfterSheet)
            ['Departamento', '', 'Cargo', '', 'Horario', '', 'Empleador', '', 'Lider', '', 'Sede', '', 'Rol', 'Activo', '', 'Desplegables (usar en hoja Empleados)', '', '', '', '', ''],
            // Fila 2: sub-encabezados
            ['ID', 'Nombre', 'ID', 'Nombre', 'ID', 'Nombre', 'ID', 'Nombre', 'ID', 'Nombre', 'ID', 'Nombre', '', '', '', 'Departamento', 'Cargo', 'Horario', 'Empleador', 'Lider', 'Sede'],
        ];

        for ($i = 0; $i < $maxRows; $i++) {
            $depto     = $this->deptos->get($i);
            $cargo     = $this->cargos->get($i);
            $horario   = $this->horarios->get($i);
            $empleador = $this->empleadores->get($i);
            $lider     = $this->lideres->get($i);
            $sede      = $this->sedes->get($i);

            $rows[] = [
                $depto?->id     ?? '', $depto?->nombre     ?? '',
                $cargo?->id     ?? '', $cargo?->nombre     ?? '',
                $horario?->id   ?? '', $horario?->nombre   ?? '',
                $empleador?->id ?? '', $empleador?->nombre ?? '',
                $lider?->id     ?? '', $lider?->name       ?? '',
                $sede?->id      ?? '', $sede?->nombre      ?? '',
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

                foreach (array_merge(range('A', 'N'), range('P', 'U')) as $col) {
                    $ws->getColumnDimension($col)->setAutoSize(true);
                }

                $lastRow = $ws->getHighestRow();

                // ── Fila 1: merge grupos + estilo ────────────────────────────
                $groupStyle = [
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ];
                foreach (self::GROUPS as $g) {
                    $ws->mergeCells("{$g['colId']}1:{$g['colNom']}1");
                    $ws->getStyle("{$g['colId']}1:{$g['colNom']}1")->applyFromArray($groupStyle);
                }
                $ws->getStyle('M1:N1')->applyFromArray($groupStyle);

                // Encabezado sección auxiliar
                $auxHeaderStyle = [
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '70AD47']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ];
                $ws->mergeCells('P1:U1');
                $ws->getStyle('P1:U1')->applyFromArray($auxHeaderStyle);

                // ── Fila 2: sub-encabezados ───────────────────────────────────
                $subStyle = [
                    'font'      => ['bold' => true],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ];
                $ws->getStyle('A2:N2')->applyFromArray($subStyle);

                $auxSubStyle = [
                    'font'      => ['bold' => true],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2EFDA']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ];
                $ws->getStyle('P2:U2')->applyFromArray($auxSubStyle);

                // ── Columnas auxiliares: fórmulas "id - nombre" ───────────────
                $formulas = [
                    'P' => ['id' => 'A', 'nom' => 'B'],
                    'Q' => ['id' => 'C', 'nom' => 'D'],
                    'R' => ['id' => 'E', 'nom' => 'F'],
                    'S' => ['id' => 'G', 'nom' => 'H'],
                    'T' => ['id' => 'I', 'nom' => 'J'],
                    'U' => ['id' => 'K', 'nom' => 'L'],
                ];
                for ($row = 3; $row <= $lastRow; $row++) {
                    foreach ($formulas as $auxCol => $src) {
                        $ws->getCell("{$auxCol}{$row}")
                           ->setValue("=IF({$src['id']}{$row}=\"\",\"\",{$src['id']}{$row}&\" - \"&{$src['nom']}{$row})");
                    }
                }

                if ($lastRow >= 3) {
                    $ws->getStyle("P3:U{$lastRow}")->getFill()
                       ->setFillType(Fill::FILL_SOLID)
                       ->getStartColor()->setRGB('F0FFF0');
                }

                $ws->freezePane('A3');
            },
        ];
    }
}
