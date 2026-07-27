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
    // Cols A-N: pares ID|Nombre por catálogo (para BUSCARV)
    //   A-B Depto, C-D Cargo, E-F Horario, G-H Empleador, I-J Lider, K-L Sede, M Rol, N Activo
    // Col O: separador
    // Cols P-U: valores "id - nombre" directos usados por los dropdowns de Datos
    //   P Depto, Q Cargo, R Horario, S Empleador, T Lider, U Sede

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
                // A-N: pares ID | Nombre
                $depto?->id     ?? '', $depto?->nombre     ?? '',
                $cargo?->id     ?? '', $cargo?->nombre     ?? '',
                $horario?->id   ?? '', $horario?->nombre   ?? '',
                $empleador?->id ?? '', $empleador?->nombre ?? '',
                $lider?->id     ?? '', $lider?->name       ?? '',
                $sede?->id      ?? '', $sede?->nombre      ?? '',
                $roles[$i]   ?? '',
                $activos[$i] ?? '',
                // O: separador
                '',
                // P-U: "id - nombre" directos para dropdowns
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

                // Autosize
                foreach (array_merge(range('A', 'N'), range('P', 'U')) as $col) {
                    $ws->getColumnDimension($col)->setAutoSize(true);
                }

                // ── Fila 1: merge grupos + estilo ────────────────────────────
                $groupStyle = [
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ];
                $groups = [['A','B'],['C','D'],['E','F'],['G','H'],['I','J'],['K','L']];
                foreach ($groups as [$c1, $c2]) {
                    $ws->mergeCells("{$c1}1:{$c2}1");
                    $ws->getStyle("{$c1}1:{$c2}1")->applyFromArray($groupStyle);
                }
                $ws->getStyle('M1:N1')->applyFromArray($groupStyle);

                // Encabezado sección auxiliar (verde)
                $ws->mergeCells('P1:U1');
                $ws->getStyle('P1:U1')->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '70AD47']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // ── Fila 2: sub-encabezados ───────────────────────────────────
                $ws->getStyle('A2:N2')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $ws->getStyle('P2:U2')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2EFDA']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Fondo verde claro para auxiliares
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
