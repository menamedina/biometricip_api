<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TenantHelper;
use App\Http\Controllers\Controller;
use App\Models\Sede;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class SedeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $sedes = Sede::orderBy('nombre')->get();
        } catch (\Throwable $e) {
            $sedes = collect();
        }
        return response()->json(['data' => $sedes]);
    }

    public function store(Request $request): JsonResponse
    {
        Log::info('SedeController::store iniciado', ['payload' => $request->all(), 'user_id' => $request->user()?->id]);

        try {
            $data = $request->validate([
                'codigo'    => 'required|string|unique:tenant.tbl_sedes,codigo|max:20',
                'nombre'    => 'required|string|max:255',
                'direccion' => 'nullable|string|max:500',
                'lat'       => 'required|numeric|between:-90,90',
                'lng'       => 'required|numeric|between:-180,180',
                'radio_mts' => 'nullable|integer|min:10|max:5000',
                'is_active' => 'nullable|boolean',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e; // 422 — Laravel lo maneja correctamente
        } catch (\Throwable $e) {
            Log::error('SedeController::store validación falló', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Error al validar: ' . $e->getMessage()], 500);
        }

        Log::info('SedeController::store validación OK', ['data' => $data]);

        try {
            $data['secret_key'] = bin2hex(random_bytes(16));
            $data['radio_mts'] ??= 150;
            $data['is_active'] ??= true;

            $sede = Sede::create($data);
            Log::info('SedeController::store sede creada', ['sede_id' => $sede->id]);
        } catch (\Throwable $e) {
            Log::error('SedeController::store fallo al crear', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Error al crear la sede: ' . $e->getMessage()], 500);
        }

        return response()->json(['data' => $sede], 201);
    }

    public function show(int $id): JsonResponse
    {
        $sede = Sede::findOrFail($id);
        return response()->json(['data' => $sede]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        Log::info('SedeController::update iniciado', ['id' => $id, 'payload' => $request->all(), 'user_id' => $request->user()?->id]);

        try {
            $sede = Sede::findOrFail($id);
        } catch (\Throwable $e) {
            Log::error('SedeController::update sede no encontrada', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Sede no encontrada.'], 404);
        }

        try {
            $data = $request->validate([
                'codigo'    => 'sometimes|string|unique:tenant.tbl_sedes,codigo,' . $id . '|max:20',
                'nombre'    => 'sometimes|string|max:255',
                'direccion' => 'nullable|string|max:500',
                'lat'       => 'sometimes|numeric|between:-90,90',
                'lng'       => 'sometimes|numeric|between:-180,180',
                'radio_mts' => 'nullable|integer|min:10|max:5000',
                'is_active' => 'nullable|boolean',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('SedeController::update validación falló', ['id' => $id, 'errors' => $e->errors()]);
            throw $e;
        } catch (\Throwable $e) {
            Log::error('SedeController::update error en validación', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Error al validar: ' . $e->getMessage()], 500);
        }

        try {
            $sede->update($data);
            Log::info('SedeController::update sede actualizada', ['id' => $id]);
        } catch (\Throwable $e) {
            Log::error('SedeController::update fallo al actualizar', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Error al actualizar la sede: ' . $e->getMessage()], 500);
        }

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

    public function qrV3(Request $request, int $id): JsonResponse
    {
        $sede = Sede::findOrFail($id);

        if (!$sede->qr_v3_token) {
            return response()->json(['message' => 'QR Web no habilitado para esta sede.'], 404);
        }

        $webToken = $this->resolveWebToken($request);

        return response()->json([
            'sede'     => ['id' => $sede->id, 'codigo' => $sede->codigo, 'nombre' => $sede->nombre],
            'qr_value' => $sede->generateV3QRUrl($webToken),
            'tipo'     => 'web',
        ]);
    }

    public function enableQRV3(Request $request, int $id): JsonResponse
    {
        $sede = Sede::findOrFail($id);

        if (!$sede->qr_v3_token) {
            $sede->update(['qr_v3_token' => bin2hex(random_bytes(16))]);
        }

        $webToken = $this->resolveWebToken($request);

        return response()->json([
            'message'  => 'QR Web habilitado.',
            'qr_value' => $sede->generateV3QRUrl($webToken),
        ]);
    }

    public function regenerateQRV3(Request $request, int $id): JsonResponse
    {
        $sede = Sede::findOrFail($id);
        $sede->update(['qr_v3_token' => bin2hex(random_bytes(16))]);

        $webToken = $this->resolveWebToken($request);

        return response()->json([
            'message'  => 'QR Web regenerado. Los QR impresos anteriores ya no son válidos.',
            'qr_value' => $sede->generateV3QRUrl($webToken),
        ]);
    }

    private function resolveWebToken(Request $request): string
    {
        $empresaId = ($request->user()->admin_tenant && $request->hasHeader('X-Empresa-Id'))
            ? (int) $request->header('X-Empresa-Id')
            : $request->user()->empresa_id;

        return rtrim(strtr(base64_encode(Crypt::encryptString((string) $empresaId)), '+/', '-_'), '=');
    }
}
