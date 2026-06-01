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
            'codigo'    => 'required|string|unique:tenant.tbl_sedes,codigo|max:20',
            'nombre'    => 'required|string|max:255',
            'direccion' => 'nullable|string|max:500',
            'lat'       => 'required|numeric|between:-90,90',
            'lng'       => 'required|numeric|between:-180,180',
            'radio_mts' => 'nullable|integer|min:10|max:5000',
            'is_active' => 'nullable|boolean',
        ]);

        $data['secret_key'] = bin2hex(random_bytes(16));
        $data['radio_mts'] ??= 150;
        $data['is_active'] ??= true;

        $sede = Sede::create($data);

        return response()->json(['data' => $sede], 201);
    }

    public function show(int $id): JsonResponse
    {
        $sede = Sede::findOrFail($id);
        return response()->json(['data' => $sede]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $sede = Sede::findOrFail($id);

        $data = $request->validate([
            'codigo'    => 'sometimes|string|unique:tenant.tbl_sedes,codigo,' . $id . '|max:20',
            'nombre'    => 'sometimes|string|max:255',
            'direccion' => 'nullable|string|max:500',
            'lat'       => 'sometimes|numeric|between:-90,90',
            'lng'       => 'sometimes|numeric|between:-180,180',
            'radio_mts' => 'nullable|integer|min:10|max:5000',
            'is_active' => 'nullable|boolean',
        ]);

        $sede->update($data);

        return response()->json(['data' => $sede]);
    }

    public function destroy(int $id): JsonResponse
    {
        $sede = Sede::findOrFail($id);
        $sede->delete();
        return response()->json(['message' => 'Sede eliminada correctamente.']);
    }

    public function qr(int $id): JsonResponse
    {
        $sede = Sede::findOrFail($id);

        $timeSlot = (int) floor(time() / 30);
        $qrValue  = $sede->generateQRValue($timeSlot);

        return response()->json([
            'sede' => [
                'id'     => $sede->id,
                'codigo' => $sede->codigo,
                'nombre' => $sede->nombre,
            ],
            'qr_value'          => $qrValue,
            'time_slot'         => $timeSlot,
            'expires_in_seconds' => 30 - (time() % 30),
        ]);
    }

    public function qrStatic(int $id): JsonResponse
    {
        $sede = Sede::findOrFail($id);

        if (!$sede->qr_static_token) {
            return response()->json(['message' => 'QR estático no habilitado para esta sede.'], 404);
        }

        return response()->json([
            'sede' => [
                'id'     => $sede->id,
                'codigo' => $sede->codigo,
                'nombre' => $sede->nombre,
            ],
            'qr_value' => $sede->generateStaticQRValue(),
            'tipo'     => 'estatico',
        ]);
    }

    public function enableStaticQR(int $id): JsonResponse
    {
        $sede = Sede::findOrFail($id);
        $sede->update(['qr_static_token' => bin2hex(random_bytes(16))]);

        return response()->json([
            'message'  => 'QR estático habilitado.',
            'qr_value' => $sede->generateStaticQRValue(),
        ]);
    }

    public function regenerateStaticQR(int $id): JsonResponse
    {
        $sede = Sede::findOrFail($id);
        $sede->update(['qr_static_token' => bin2hex(random_bytes(16))]);

        return response()->json([
            'message'  => 'QR estático regenerado. Los QR impresos anteriores ya no son válidos.',
            'qr_value' => $sede->generateStaticQRValue(),
        ]);
    }
}
