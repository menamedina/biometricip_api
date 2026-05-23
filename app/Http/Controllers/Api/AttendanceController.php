<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendancePhoto;
use App\Models\AttendanceRecord;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AttendanceRecord::with(['user', 'sede'])
            ->whereDate('fecha_hora', $request->date ?? today());

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('sede_id')) {
            $query->where('sede_id', $request->sede_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $records = $query->with('photo')->orderBy('fecha_hora', 'desc')->paginate($request->per_page ?? 50);

        return response()->json($records);
    }

    public function clock(Request $request): JsonResponse
    {
        $request->validate([
            'qr_value' => 'nullable|string',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'metodo' => 'required|in:qr,biometrico,reconocimiento_facial,foto',
            'foto_evidencia' => 'nullable|string',
            'tipo' => 'required|in:entrada,salida_almuerzo,regreso_almuerzo,salida',
        ]);

        $user = $request->user();

        if (!$user->is_active) {
            return response()->json(['message' => 'Usuario inactivo.'], 403);
        }

        $qrValidado = false;
        $sede = null;

        if ($request->metodo === 'foto') {
            // Buscar la sede activa más cercana a las coordenadas del usuario
            $sedes = Sede::where('is_active', true)->get();
            $menorDistancia = PHP_INT_MAX;
            foreach ($sedes as $s) {
                $d = $this->calcularDistancia((float) $request->lat, (float) $request->lng, (float) $s->lat, (float) $s->lng);
                if ($d < $menorDistancia) {
                    $menorDistancia = $d;
                    $sede = $s;
                }
            }
            if (!$sede) {
                return response()->json(['message' => 'No hay sedes activas registradas.'], 422);
            }
            $qrValidado = false;
        } else {
            $qrData = json_decode($request->qr_value, true);
            if (!$qrData || !isset($qrData['s'])) {
                return response()->json(['message' => 'QR inválido.'], 422);
            }

            $sede = Sede::where('codigo', $qrData['s'])->first();
            if (!$sede || !$sede->is_active) {
                return response()->json(['message' => 'Sede no encontrada o inactiva.'], 422);
            }

            $qrValidado = $sede->validateQRValue($request->qr_value);
            if (!$qrValidado) {
                return response()->json(['message' => 'El código QR no es válido o ha expirado.'], 422);
            }
        }

        $distancia = $this->calcularDistancia(
            (float) $request->lat,
            (float) $request->lng,
            (float) $sede->lat,
            (float) $sede->lng
        );

        $geocercaValidada = $distancia <= $sede->radio_mts;
        if (!$geocercaValidada && $request->metodo !== 'foto') {
            return response()->json([
                'message' => 'Estás fuera del rango permitido de la oficina.',
                'distancia_mts' => round($distancia, 2),
                'radio_permitido_mts' => $sede->radio_mts,
            ], 422);
        }

        $fotoBase64 = null;
        $thumbBase64 = null;
        if ($request->filled('foto_evidencia')) {
            [$fotoBase64, $thumbBase64] = $this->procesarFoto($request->foto_evidencia);
        }

        $record = AttendanceRecord::create([
            'user_id' => $user->id,
            'sede_id' => $sede->id,
            'tipo' => $request->tipo,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'foto_evidencia' => $fotoBase64 ? 'base64' : null,
            'metodo' => $request->metodo,
            'qr_validado' => $qrValidado,
            'geocerca_validada' => $geocercaValidada,
            'distancia_oficina_mts' => round($distancia, 2),
            'fecha_hora' => now(),
        ]);

        if ($fotoBase64 && $thumbBase64) {
            AttendancePhoto::create([
                'attendance_record_id' => $record->id,
                'foto_base64' => $fotoBase64,
                'thumbnail_base64' => $thumbBase64,
            ]);
        }

        $record->load(['user', 'sede']);

        return response()->json([
            'message' => 'Asistencia registrada correctamente.',
            'data' => $record,
        ], 201);
    }

    public function getPhoto(int $id): JsonResponse
    {
        $photo = AttendancePhoto::where('attendance_record_id', $id)->first();

        if (!$photo) {
            return response()->json(['message' => 'Sin foto'], 404);
        }

        return response()->json(['foto_base64' => $photo->foto_base64]);
    }

    public function myHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        $date = $request->date ?? today();

        $records = AttendanceRecord::with('sede')
            ->where('user_id', $user->id)
            ->whereDate('fecha_hora', $date)
            ->orderBy('fecha_hora', 'desc')
            ->get();

        return response()->json(['data' => $records]);
    }

    public function stats(Request $request): JsonResponse
    {
        $date = $request->date ?? today();

        $empresaId = $request->user()->empresa_id;

        $totalEmpleados = User::where('empresa_id', $empresaId)
            ->where('role', 'empleado')
            ->where('is_active', true)
            ->count();

        $presentes = AttendanceRecord::whereDate('fecha_hora', $date)
            ->whereIn('tipo', ['entrada', 'regreso_almuerzo'])
            ->distinct('user_id')
            ->count('user_id');

        $ausentes = $totalEmpleados - $presentes;

        $tardanzas = AttendanceRecord::whereDate('fecha_hora', $date)
            ->where('tipo', 'entrada')
            ->whereTime('fecha_hora', '>', '09:00:00')
            ->count();

        return response()->json([
            'total_empleados' => $totalEmpleados,
            'presentes' => $presentes,
            'ausentes' => max(0, $ausentes),
            'tardanzas' => $tardanzas,
        ]);
    }

    public function latestRecords(Request $request): JsonResponse
    {
        $limit = $request->limit ?? 20;

        $records = AttendanceRecord::with(['user', 'sede'])
            ->whereDate('fecha_hora', today())
            ->orderBy('fecha_hora', 'desc')
            ->take($limit)
            ->get();

        return response()->json(['data' => $records]);
    }

    private function calcularDistancia(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function procesarFoto(string $base64Data): array
    {
        if (!preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
            return [null, null];
        }

        $imageType = $matches[1];
        $rawBase64 = substr($base64Data, strpos($base64Data, ',') + 1);
        $decoded = base64_decode($rawBase64);

        if ($decoded === false) {
            return [null, null];
        }

        $thumbBase64 = $this->generarThumbnailBase64($decoded, $imageType, 40, 40);

        return [$base64Data, $thumbBase64 ?? $base64Data];
    }

    private function generarThumbnailBase64(string $imageData, string $imageType, int $width, int $height): ?string
    {
        $src = imagecreatefromstring($imageData);
        if ($src === false) {
            return null;
        }

        $origW = imagesx($src);
        $origH = imagesy($src);
        $thumb = imagecreatetruecolor($width, $height);

        if (in_array($imageType, ['png', 'webp'])) {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
        }

        imagecopyresampled($thumb, $src, 0, 0, 0, 0, $width, $height, $origW, $origH);
        imagedestroy($src);

        ob_start();
        match ($imageType) {
            'png'  => imagepng($thumb),
            'webp' => imagewebp($thumb),
            default => imagejpeg($thumb, null, 85),
        };
        $output = ob_get_clean();
        imagedestroy($thumb);

        if (!$output) return null;

        return 'data:image/' . $imageType . ';base64,' . base64_encode($output);
    }
}
