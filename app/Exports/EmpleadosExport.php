<?php

namespace App\Exports;

use App\Models\Cargo;
use App\Models\Departamento;
use App\Models\Empleador;
use App\Models\Horario;
use App\Models\Sede;
use App\Models\User;
use App\Models\UserSede;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EmpleadosExport implements WithMultipleSheets
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

        // Build lookup maps for "id - nombre" format
        $deptoMap     = $deptos->pluck('nombre', 'id')->toArray();
        $cargoMap     = $cargos->pluck('nombre', 'id')->toArray();
        $horarioMap   = $horarios->pluck('nombre', 'id')->toArray();
        $empleadorMap = $empleadores->pluck('nombre', 'id')->toArray();
        $liderMap     = $lideres->mapWithKeys(fn ($l) => [$l->id => $l->name])->toArray();
        $sedeMap      = $sedes->pluck('nombre', 'id')->toArray();

        // Get sede assignments
        $sedeAssignments = UserSede::where('empresa_id', $this->empresaId)
            ->pluck('sede_id', 'user_id')
            ->toArray();

        // Get employees
        $employees = User::where('empresa_id', $this->empresaId)
            ->orderBy('name')
            ->get();

        $rows = [];
        foreach ($employees as $emp) {
            $sedeId = $sedeAssignments[$emp->id] ?? null;

            $rows[] = [
                $emp->name,
                $emp->email,
                $emp->cedula,
                $emp->telefono ?? '',
                isset($deptoMap[$emp->departamento_id]) ? "{$emp->departamento_id} - {$deptoMap[$emp->departamento_id]}" : '',
                isset($cargoMap[$emp->cargo_id]) ? "{$emp->cargo_id} - {$cargoMap[$emp->cargo_id]}" : '',
                isset($horarioMap[$emp->horario_id]) ? "{$emp->horario_id} - {$horarioMap[$emp->horario_id]}" : '',
                isset($empleadorMap[$emp->empleador_id]) ? "{$emp->empleador_id} - {$empleadorMap[$emp->empleador_id]}" : '',
                isset($liderMap[$emp->lider_id]) ? "{$emp->lider_id} - {$liderMap[$emp->lider_id]}" : '',
                $emp->role ?? 'empleado',
                $sedeId && isset($sedeMap[$sedeId]) ? "{$sedeId} - {$sedeMap[$sedeId]}" : '',
                $emp->is_active ? '1' : '0',
            ];
        }

        return [
            new EmpleadosExportDatosSheet($rows),
            new EmpleadosListasSheet($deptos, $cargos, $horarios, $empleadores, $lideres, $sedes),
        ];
    }
}
