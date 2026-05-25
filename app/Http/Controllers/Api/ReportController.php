<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Departamento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportController extends Controller
{
    public function attendance(Request $request): Response
    {
        $request->validate([
            'date_from'  => 'required|date',
            'date_to'    => 'required|date|after_or_equal:date_from',
            'user_id'    => 'nullable|integer',
            'sede_id'    => 'nullable|integer',
        ]);

        $query = AttendanceRecord::with(['user', 'sede'])
            ->whereBetween('fecha_hora', [
                $request->date_from . ' 00:00:00',
                $request->date_to . ' 23:59:59',
            ]);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('sede_id')) {
            $query->where('sede_id', $request->sede_id);
        }

        $records = $query->orderBy('fecha_hora', 'asc')->get();

        $csv = $this->generateCSV($records);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="reporte_asistencia_' . $request->date_from . '_' . $request->date_to . '.csv"',
            'Content-Transfer-Encoding' => 'binary',
        ]);
    }

    public function employeeStats(Request $request): JsonResponse
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
            'user_id'   => 'nullable|integer',
        ]);

        $query = AttendanceRecord::with('user')
            ->whereBetween('fecha_hora', [
                $request->date_from . ' 00:00:00',
                $request->date_to . ' 23:59:59',
            ]);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $records = $query->orderBy('fecha_hora', 'asc')->get();

        $grouped = $records->groupBy('user_id')->map(function ($userRecords) {
            $user = $userRecords->first()->user;
            $entradas = $userRecords->whereIn('tipo', ['entrada', 'regreso_almuerzo']);
            $salidas  = $userRecords->whereIn('tipo', ['salida', 'salida_almuerzo']);

            $tardanzas = $userRecords->where('tipo', 'entrada')
                ->filter(fn($r) => $r->fecha_hora->format('H:i:s') > '09:00:00')
                ->count();

            return [
                'empleado'       => $user,
                'dias_trabajados' => $userRecords->pluck('fecha_hora')->map->format('Y-m-d')->unique()->count(),
                'total_entradas' => $entradas->count(),
                'total_salidas'  => $salidas->count(),
                'tardanzas'      => $tardanzas,
            ];
        })->values();

        return response()->json(['data' => $grouped]);
    }

    private function generateCSV($records): string
    {
        // Precargar nombres de departamentos para evitar N+1
        $deptoMap = Departamento::pluck('nombre', 'id')->all();

        // Agrupar por empleado + fecha
        $grouped = $records->groupBy(function ($r) {
            return $r->user_id . '_' . $r->fecha_hora->format('Y-m-d');
        });

        $output = fopen('php://temp', 'r+');

        // BOM UTF-8 para que Excel abra columnas correctamente
        fwrite($output, "\xEF\xBB\xBF");

        fputcsv($output, [
            'Empleado', 'Código', 'Departamento', 'Sede', 'Fecha',
            'Entrada 1', 'Salida 1',
            'Entrada 2', 'Salida 2',
            'Entrada 3', 'Salida 3',
            'Entrada 4', 'Salida 4',
            'Total Horas',
        ], ';');

        foreach ($grouped as $rows) {
            $user  = $rows->first()->user;
            $sede  = $rows->first()->sede;
            $fecha = $rows->first()->fecha_hora->format('d/m/Y');

            // Ordenar todos los registros cronológicamente
            $sorted = $rows->sortBy('fecha_hora')->values();

            // Separar entradas y salidas en orden
            $entradas = $sorted->whereIn('tipo', ['entrada', 'regreso_almuerzo'])->values();
            $salidas  = $sorted->whereIn('tipo', ['salida', 'salida_almuerzo'])->values();

            $pares = [];
            $totalMins = 0;
            for ($i = 0; $i < 4; $i++) {
                $e = $entradas->get($i);
                $s = $salidas->get($i);
                $pares[] = $e ? $e->fecha_hora->format('H:i:s') : '—';
                $pares[] = $s ? $s->fecha_hora->format('H:i:s') : '—';
                if ($e && $s) {
                    $totalMins += $e->fecha_hora->diffInMinutes($s->fecha_hora);
                }
            }

            $h = intdiv($totalMins, 60);
            $m = $totalMins % 60;
            $total = $totalMins > 0 ? sprintf('%dh %02dm', $h, $m) : '—';

            $deptoNombre = $user->departamento_id
                ? ($deptoMap[$user->departamento_id] ?? 'N/A')
                : 'N/A';

            fputcsv($output, array_merge([
                $user->name ?? 'N/A',
                $user->codigo_empleado ?? 'N/A',
                $deptoNombre,
                $sede->nombre ?? 'N/A',
                $fecha,
            ], $pares, [$total]), ';');
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
