<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Horario;
use App\Models\HorarioDia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HorarioController extends Controller
{
    public function index(): JsonResponse
    {
        $horarios = Horario::with('dias')->orderBy('nombre')->get();
        return response()->json(['data' => $horarios]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre'                        => 'required|string|max:100',
            'is_active'                     => 'nullable|boolean',
            'dias'                          => 'nullable|array',
            'dias.*.dia_semana'             => 'required|integer|min:1|max:7',
            'dias.*.hora_entrada'           => 'nullable|date_format:H:i:s',
            'dias.*.hora_salida'            => 'nullable|date_format:H:i:s',
            'dias.*.duracion_almuerzo_min'  => 'nullable|integer|min:0|max:240',
            'dias.*.retardo_min'            => 'nullable|integer|min:0|max:120',
        ]);

        $data['is_active'] ??= true;

        $horario = Horario::create([
            'nombre'    => $data['nombre'],
            'is_active' => $data['is_active'],
        ]);

        $this->syncDias($horario, $data['dias'] ?? []);

        return response()->json(['data' => $horario->load('dias')], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['data' => Horario::with('dias')->findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $horario = Horario::findOrFail($id);

        $data = $request->validate([
            'nombre'                        => 'sometimes|string|max:100',
            'is_active'                     => 'nullable|boolean',
            'dias'                          => 'nullable|array',
            'dias.*.dia_semana'             => 'required|integer|min:1|max:7',
            'dias.*.hora_entrada'           => 'nullable|date_format:H:i:s',
            'dias.*.hora_salida'            => 'nullable|date_format:H:i:s',
            'dias.*.duracion_almuerzo_min'  => 'nullable|integer|min:0|max:240',
            'dias.*.retardo_min'            => 'nullable|integer|min:0|max:120',
        ]);

        $horario->update([
            'nombre'    => $data['nombre']    ?? $horario->nombre,
            'is_active' => $data['is_active'] ?? $horario->is_active,
        ]);

        if (isset($data['dias'])) {
            $this->syncDias($horario, $data['dias']);
        }

        return response()->json(['data' => $horario->load('dias')]);
    }

    public function destroy(int $id): JsonResponse
    {
        Horario::findOrFail($id)->update(['is_active' => false]);
        return response()->json(['message' => 'Horario desactivado.']);
    }

    private function syncDias(Horario $horario, array $dias): void
    {
        // Eliminar todos los días existentes y recrear
        $horario->dias()->delete();

        foreach ($dias as $d) {
            if (!isset($d['hora_entrada']) || !$d['hora_entrada']) continue; // día no laboral, no guardar
            HorarioDia::create([
                'horario_id'            => $horario->id,
                'dia_semana'            => $d['dia_semana'],
                'hora_entrada'          => $d['hora_entrada'],
                'hora_salida'           => $d['hora_salida'] ?? null,
                'duracion_almuerzo_min' => $d['duracion_almuerzo_min'] ?? null,
                'retardo_min'           => $d['retardo_min'] ?? 0,
            ]);
        }
    }
}
