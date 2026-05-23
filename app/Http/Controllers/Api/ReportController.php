<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportController extends Controller
{
    public function attendance(Request $request): Response
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'empleado_id' => 'nullable|exists:empleados,id',
            'sede_id' => 'nullable|exists:sedes,id',
        ]);

        $query = AttendanceRecord::with(['empleado.user', 'sede'])
            ->whereBetween('fecha_hora', [
                $request->date_from . ' 00:00:00',
                $request->date_to . ' 23:59:59',
            ]);

        if ($request->filled('empleado_id')) {
            $query->where('empleado_id', $request->empleado_id);
        }

        if ($request->filled('sede_id')) {
            $query->where('sede_id', $request->sede_id);
        }

        $records = $query->orderBy('fecha_hora', 'asc')->get();

        $csv = $this->generateCSV($records);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="reporte_asistencia_' . $request->date_from . '_' . $request->date_to . '.csv"',
        ]);
    }

    public function employeeStats(Request $request): JsonResponse
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'empleado_id' => 'nullable|exists:empleados,id',
        ]);

        $query = AttendanceRecord::with('empleado.user')
            ->whereBetween('fecha_hora', [
                $request->date_from . ' 00:00:00',
                $request->date_to . ' 23:59:59',
            ]);

        if ($request->filled('empleado_id')) {
            $query->where('empleado_id', $request->empleado_id);
        }

        $records = $query->orderBy('fecha_hora', 'asc')->get();

        $grouped = $records->groupBy('empleado_id')->map(function ($employeeRecords) {
            $empleado = $employeeRecords->first()->empleado;
            $entradas = $employeeRecords->whereIn('tipo', ['entrada', 'regreso_almuerzo']);
            $salidas = $employeeRecords->whereIn('tipo', ['salida', 'salida_almuerzo']);

            $tardanzas = $employeeRecords->where('tipo', 'entrada')
                ->filter(fn($r) => $r->fecha_hora->format('H:i:s') > '09:00:00')
                ->count();

            return [
                'empleado' => $empleado,
                'dias_trabajados' => $employeeRecords->pluck('fecha_hora')->map->format('Y-m-d')->unique()->count(),
                'total_entradas' => $entradas->count(),
                'total_salidas' => $salidas->count(),
                'tardanzas' => $tardanzas,
            ];
        })->values();

        return response()->json(['data' => $grouped]);
    }

    private function generateCSV($records): string
    {
        $output = fopen('php://temp', 'r+');

        fputcsv($output, [
            'Empleado', 'Código', 'Departamento', 'Sede', 'Tipo',
            'Fecha/Hora', 'Método', 'QR Válido', 'Geocerca',
            'Distancia (mts)', 'Lat', 'Lng',
        ]);

        foreach ($records as $record) {
            fputcsv($output, [
                $record->empleado->user->name ?? 'N/A',
                $record->empleado->codigo_empleado ?? 'N/A',
                $record->empleado->departamento ?? 'N/A',
                $record->sede->nombre ?? 'N/A',
                $record->tipo,
                $record->fecha_hora->format('Y-m-d H:i:s'),
                $record->metodo,
                $record->qr_validado ? 'Sí' : 'No',
                $record->geocerca_validada ? 'Sí' : 'No',
                $record->distancia_oficina_mts,
                $record->lat,
                $record->lng,
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
