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
    // Cols A-J: pares ID|Nombre por catálogo  (para BUSCARV)
    // Cols L:   separador
    // Cols N-R: columnas auxiliares "id - nombre" usadas por los dropdowns de Datos
    private const GROUPS = [
        ['colId' => 'A', 'colNombre' => 'B', 'aux' => 'N', 'label' => 'Departamento'],
        ['colId' => 'C', 'colNombre' => 'D', 'aux' => 'O', 'label' => 'Cargo'],
        ['colId' => 'E', 'colNombre' => 'F', 'aux' => 'P', 'label' => 'Horario'],
        ['colId' => 'G', 'colNombre' => 'H', 'aux' => 'Q', 'label' => 'Empleador'],
        ['colId' => 'I', 'colNombre' => 'J', 'aux' => 'R', 'label' => 'Lider'],
    ];

    public function __construct(
        private Collection $deptos,
        private Collection $cargos,
        private Collection $horarios,
        private Collection $empleadores,
        private Collection $lideres,
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
            3,
        );

        $roles   = ['empleado', 'supervisor', 'admin'];
        $activos = ['1', '0'];

        // Fila 1: encabezados de grupo (se mergen en AfterSheet)
        // Fila 2: sub-encabezados ID | Nombre
        // A-J datos, K vacío, L-M: Rol/Activo, N separador header, N-R auxiliares
        $rows = [
            ['Departamento', '', 'Cargo', '', 'Horario', '', 'Empleador', '', 'Lider', '', 'Rol', 'Activo', '', 'Desplegables (usar en hoja Empleados)', '', '', '', ''],
            ['ID', 'Nombre', 'ID', 'Nombre', 'ID', 'Nombre', 'ID', 'Nombre', 'ID', 'Nombre', '', '', '', 'Departamento', 'Cargo', 'Horario', 'Empleador', 'Lider'],
        ];

        for ($i = 0; $i < $maxRows; $i++) {
            $depto     = $this->deptos->get($i);
            $cargo     = $this->cargos->get($i);
            $horario   = $this->horarios->get($i);
            $empleador = $this->empleadores->get($i);
            $lider     = $this->lideres->get($i);

            // Las fórmulas de las columnas auxiliares (N-R) se agregan en AfterSheet
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
                foreach (array_merge(range('A', 'L'), range('N', 'R')) as $col) {
                    $ws->getColumnDimension($col)->setAutoSize(true);
                }

                $lastRow = $ws->getHighestRow();

                // ── Fila 1: merge grupos A-J y estilo ────────────────────────
                $groupStyle = [
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ];
                foreach (self::GROUPS as $g) {
                    $ws->mergeCells("{$g['colId']}1:{$g['colNombre']}1");
                    $ws->getStyle("{$g['colId']}1:{$g['colNombre']}1")->applyFromArray($groupStyle);
                }
                $ws->getStyle('K1:L1')->applyFromArray($groupStyle);

                // Encabezado sección auxiliar (N1 en adelante)
                $auxHeaderStyle = [
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '70AD47']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ];
                $ws->mergeCells('N1:R1');
                $ws->getStyle('N1:R1')->applyFromArray($auxHeaderStyle);

                // ── Fila 2: sub-encabezados ───────────────────────────────────
                $subStyle = [
                    'font'      => ['bold' => true],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ];
                $ws->getStyle('A2:L2')->applyFromArray($subStyle);

                $auxSubStyle = [
                    'font'      => ['bold' => true],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2EFDA']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ];
                $ws->getStyle('N2:R2')->applyFromArray($auxSubStyle);

                // ── Columnas auxiliares: fórmulas "id - nombre" (fila 3+) ─────
                // N = Departamento, O = Cargo, P = Horario, Q = Empleador, R = Lider
                $formulas = [
                    'N' => ['id' => 'A', 'nom' => 'B'],
                    'O' => ['id' => 'C', 'nom' => 'D'],
                    'P' => ['id' => 'E', 'nom' => 'F'],
                    'Q' => ['id' => 'G', 'nom' => 'H'],
                    'R' => ['id' => 'I', 'nom' => 'J'],
                ];
                for ($row = 3; $row <= $lastRow; $row++) {
                    foreach ($formulas as $auxCol => $src) {
                        $id  = $src['id'];
                        $nom = $src['nom'];
                        $ws->getCell("{$auxCol}{$row}")
                           ->setValue("=IF({$id}{$row}=\"\",\"\",{$id}{$row}&\" - \"&{$nom}{$row})");
                    }
                }

                // Estilo fondo verde claro para auxiliares
                if ($lastRow >= 3) {
                    $ws->getStyle("N3:R{$lastRow}")->getFill()
                       ->setFillType(Fill::FILL_SOLID)
                       ->getStartColor()->setRGB('F0FFF0');
                }

                $ws->freezePane('A3');
            },
        ];
    }
}
