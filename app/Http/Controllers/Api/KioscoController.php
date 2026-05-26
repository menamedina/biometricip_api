<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ImagenRostro;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KioscoController extends Controller
{
    /**
     * Identifica a un empleado por su descriptor facial.
     *
     * Recibe un descriptor 128D generado en el dispositivo Flutter con ML Kit,
     * lo compara contra los face_descriptor almacenados en la tabla users
     * (promedio de todas las imágenes registradas) y retorna el empleado
     * cuya distancia euclidiana sea menor al umbral definido.
     */
    public function identificar(Request $request): JsonResponse
    {
        $request->validate([
            'descriptor' => 'required|array|min:1',
        ]);

        $descriptorEntrada = $request->descriptor;
        $authUser          = $request->user();
        $empresaId         = $authUser->empresa_id;

        if (!$empresaId) {
            return response()->json(['message' => 'El kiosco no tiene empresa asignada.'], 422);
        }

        // Cargar todos los empleados activos con descriptor facial de la empresa
        $empleados = User::where('empresa_id', $empresaId)
            ->where('is_active', true)
            ->where('tipo', 'usuario')              // solo empleados normales, no otros kioscos
            ->whereNotNull('face_descriptor')
            ->get(['id', 'name', 'codigo_empleado', 'foto_url', 'face_descriptor',
                   'departamento_id', 'cargo_id', 'sede_id']);

        if ($empleados->isEmpty()) {
            return response()->json(['message' => 'No hay empleados con rostro registrado.'], 404);
        }

        $umbral      = 0.6;  // distancia máxima para considerar match
        $mejorMatch  = null;
        $mejorDist   = PHP_FLOAT_MAX;

        foreach ($empleados as $empleado) {
            $descriptor = $empleado->face_descriptor;
            if (!is_array($descriptor) || count($descriptor) !== count($descriptorEntrada)) {
                continue;
            }

            $distancia = $this->distanciaEuclidiana($descriptorEntrada, $descriptor);

            if ($distancia < $mejorDist) {
                $mejorDist  = $distancia;
                $mejorMatch = $empleado;
            }
        }

        if (!$mejorMatch || $mejorDist > $umbral) {
            return response()->json(['message' => 'Rostro no reconocido.'], 404);
        }

        return response()->json([
            'data' => [
                'id'              => $mejorMatch->id,
                'name'            => $mejorMatch->name,
                'codigo_empleado' => $mejorMatch->codigo_empleado,
                'foto_url'        => $mejorMatch->foto_url,
                'distancia'       => round($mejorDist, 4),
            ],
        ]);
    }

    /**
     * Retorna descriptores faciales de todos los empleados de la empresa
     * para cache local en el dispositivo kiosco (uso offline).
     */
    public function empleadosDescriptores(Request $request): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;

        if (!$empresaId) {
            return response()->json(['data' => []]);
        }

        $empleados = User::where('empresa_id', $empresaId)
            ->where('is_active', true)
            ->where('tipo', 'usuario')
            ->whereNotNull('face_descriptor')
            ->get(['id', 'name', 'codigo_empleado', 'foto_url', 'face_descriptor']);

        $data = $empleados->map(fn($u) => [
            'user_id'         => $u->id,
            'name'            => $u->name,
            'codigo_empleado' => $u->codigo_empleado,
            'foto_url'        => $u->foto_url,
            'face_descriptor' => $u->face_descriptor,
        ]);

        return response()->json(['data' => $data]);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    private function distanciaEuclidiana(array $a, array $b): float
    {
        $suma = 0.0;
        $n    = count($a);
        for ($i = 0; $i < $n; $i++) {
            $diff  = ($a[$i] ?? 0.0) - ($b[$i] ?? 0.0);
            $suma += $diff * $diff;
        }
        return sqrt($suma);
    }
}
