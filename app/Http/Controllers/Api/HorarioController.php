<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Horario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HorarioController extends Controller
{
    public function index(): JsonResponse
    {
        $horarios = Horario::orderBy('nombre')->get();
        return response()->json(['data' => $horarios]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre'                => 'required|string|max:100',
            'hora_entrada'          => 'required|date_format:H:i:s',
            'hora_salida'           => 'required|date_format:H:i:s',
            'duracion_almuerzo_min' => 'nullable|integer|min:0|max:240',
            'is_active'             => 'nullable|boolean',
        ]);
        $data['is_active'] ??= true;

        $horario = Horario::create($data);
        return response()->json(['data' => $horario], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['data' => Horario::findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $horario = Horario::findOrFail($id);
        $data = $request->validate([
            'nombre'                => 'sometimes|string|max:100',
            'hora_entrada'          => 'sometimes|date_format:H:i:s',
            'hora_salida'           => 'sometimes|date_format:H:i:s',
            'duracion_almuerzo_min' => 'nullable|integer|min:0|max:240',
            'is_active'             => 'nullable|boolean',
        ]);
        $horario->update($data);
        return response()->json(['data' => $horario]);
    }

    public function destroy(int $id): JsonResponse
    {
        Horario::findOrFail($id)->update(['is_active' => false]);
        return response()->json(['message' => 'Horario desactivado.']);
    }
}
