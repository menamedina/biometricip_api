<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sede;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SedeController extends Controller
{
    public function index(): JsonResponse
    {
        $sedes = Sede::orderBy('nombre')->get();
        return response()->json(['data' => $sedes]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'codigo' => 'required|string|unique:sedes,codigo|max:20',
            'nombre' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:500',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'radio_mts' => 'nullable|integer|min:10|max:5000',
            'is_active' => 'nullable|boolean',
        ]);

        $data['secret_key'] = bin2hex(random_bytes(16));
        $data['radio_mts'] ??= 150;
        $data['is_active'] ??= true;

        $sede = Sede::create($data);

        return response()->json(['data' => $sede], 201);
    }

    public function show(Sede $sede): JsonResponse
    {
        return response()->json(['data' => $sede]);
    }

    public function update(Request $request, Sede $sede): JsonResponse
    {
        $data = $request->validate([
            'codigo' => 'sometimes|string|unique:sedes,codigo,' . $sede->id . '|max:20',
            'nombre' => 'sometimes|string|max:255',
            'direccion' => 'nullable|string|max:500',
            'lat' => 'sometimes|numeric|between:-90,90',
            'lng' => 'sometimes|numeric|between:-180,180',
            'radio_mts' => 'nullable|integer|min:10|max:5000',
            'is_active' => 'nullable|boolean',
        ]);

        $sede->update($data);

        return response()->json(['data' => $sede]);
    }

    public function destroy(Sede $sede): JsonResponse
    {
        $sede->delete();
        return response()->json(['message' => 'Sede eliminada correctamente.']);
    }

    public function qr(Sede $sede): JsonResponse
    {
        $timeSlot = (int) floor(time() / 30);
        $qrValue = $sede->generateQRValue($timeSlot);

        return response()->json([
            'sede' => [
                'id' => $sede->id,
                'codigo' => $sede->codigo,
                'nombre' => $sede->nombre,
            ],
            'qr_value' => $qrValue,
            'time_slot' => $timeSlot,
            'expires_in_seconds' => 30 - (time() % 30),
        ]);
    }
}
