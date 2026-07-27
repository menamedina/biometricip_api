<?php

namespace App\Exports;

use App\Models\Cargo;
use App\Models\Departamento;
use App\Models\Empleador;
use App\Models\Horario;
use App\Models\Sede;
use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EmpleadosTemplateExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(private int $empresaId) {}

    public function sheets(): array
    {
        $deptos      = Departamento::orderBy('nombre')->get(['id', 'nombre']);
        $cargos      = Cargo::orderBy('nombre')->get(['id', 'nombre']);
        $horarios    = Horario::where('is_active', true)->orderBy('nombre')->get(['id', 'nombre']);
        $empleadores = Empleador::where('is_active', true)->orderBy('nombre')->get(['id', 'nombre']);
        $sedes       = Sede::where('is_active', true)->orderBy('nombre')->get(['id', 'nombre']);
        $lideres     = User::where('empresa_id', $this->empresaId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'codigo_empleado']);

        return [
            new EmpleadosDatosSheet(),
            new EmpleadosListasSheet($deptos, $cargos, $horarios, $empleadores, $lideres, $sedes),
        ];
    }
}
