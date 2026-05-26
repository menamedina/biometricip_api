<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendancePhoto;
use App\Models\AttendanceRecord;
use App\Models\Horario;
use App\Models\Sede;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AttendanceRecord::with(['user', 'sede']);

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('fecha_hora', [
                $request->date_from . ' 00:00:00',
                $request->date_to   . ' 23:59:59',
            ]);
        } else {
            $query->whereDate('fecha_hora', $request->date ?? today());
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('sede_id')) {
            $query->where('sede_id', $request->sede_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $records = $query->with(['photo', 'horario'])->orderBy('fecha_hora', 'asc')->paginate($request->per_page ?? 50);

        return response()->json($records);
    }

    public function clock(Request $request): JsonResponse
    {
        Log::info('=== CLOCK REQUEST RECIBIDO ===', [
            'lat' => $request->lat,
            'lng' => $request->lng,
            'tipo' => $request->tipo,
            'metodo' => $request->metodo,
            'user_id' => $request->user()?->id,
        ]);

        $request->validate([
            'qr_value'      => 'nullable|string',
            'lat'           => 'required|numeric|between:-90,90',
            'lng'           => 'required|numeric|between:-180,180',
            'metodo'        => 'required|in:qr,biometrico,reconocimiento_facial,foto',
            'foto_evidencia'=> 'nullable|string',
            'tipo'          => 'required|in:entrada,salida',
        ]);

        $user = $request->user();

        if (!$user->is_active) {
            return response()->json(['message' => 'Usuario inactivo.'], 403);
        }

        // Cargar horario del empleado (snapshot para guardar en el registro)
        $horario = $user->horario_id ? Horario::find($user->horario_id) : null;

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

        Log::info('=== GEOCERCA DEBUG ===', [
            'user_lat' => $request->lat,
            'user_lng' => $request->lng,
            'sede_lat' => $sede->lat,
            'sede_lng' => $sede->lng,
            'sede_nombre' => $sede->nombre,
            'distancia_calculada_mts' => round($distancia, 2),
            'radio_permitido_mts' => $sede->radio_mts,
            'dentro_de_geocerca' => $distancia <= $sede->radio_mts,
            'metodo' => $request->metodo,
        ]);

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
            'user_id'    => $user->id,
            'sede_id'    => $sede->id,
            'horario_id' => $horario?->id,
            'tipo'       => $request->tipo,
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

    public function offlineSync(Request $request): JsonResponse
    {
        $request->validate([
            'qr_value' => 'required|string',
            'tipo'     => 'required|in:entrada,salida',
        ]);

        $user = $request->user();

        if (!$user->is_active) {
            return response()->json(['message' => 'Usuario inactivo.'], 403);
        }

        // Extraer todo del QR: sede, hora, coordenadas, radio
        $qrData = json_decode($request->qr_value, true);
        if (!$qrData || !isset($qrData['s'], $qrData['t'], $qrData['h'], $qrData['lat'], $qrData['lng'])) {
            return response()->json(['message' => 'QR invalido o incompleto.'], 422);
        }

        $sede = Sede::where('codigo', $qrData['s'])->first();
        if (!$sede || !$sede->is_active) {
            return response()->json(['message' => 'Sede no encontrada o inactiva.'], 422);
        }

        // La hora viene del QR: time slot * 30 = timestamp unix
        $qrTimestamp = (int) $qrData['t'] * 30;
        $fechaHoraQr = Carbon::createFromTimestamp($qrTimestamp);

        // Rechazar si el QR tiene mas de 24 horas
        if ($fechaHoraQr->diffInHours(now(), false) > 24) {
            return response()->json(['message' => 'Registro offline demasiado antiguo (mayor a 24 horas).'], 422);
        }

        // Deteccion de duplicados: mismo usuario, tipo y hora del QR dentro de 2 minutos
        $duplicate = AttendanceRecord::where('user_id', $user->id)
            ->where('tipo', $request->tipo)
            ->where('fecha_hora', '>=', $fechaHoraQr->copy()->subMinutes(2))
            ->where('fecha_hora', '<=', $fechaHoraQr->copy()->addMinutes(2))
            ->exists();

        if ($duplicate) {
            return response()->json(['message' => 'Registro duplicado.'], 422);
        }

        // Validar hash del QR contra el timestamp que trae el propio QR
        $qrValidado = $sede->validateQRValueAtTime($request->qr_value, $qrTimestamp);
        if (!$qrValidado) {
            return response()->json(['message' => 'El codigo QR no es valido.'], 422);
        }

        // Validar secuencia contra registros anteriores a la hora del QR
        $ultimoTipo = AttendanceRecord::where('user_id', $user->id)
            ->where('fecha_hora', '<', $fechaHoraQr)
            ->orderBy('fecha_hora', 'desc')
            ->value('tipo');

        $permitidos = match ($ultimoTipo) {
            null, 'salida' => ['entrada'],
            'entrada'      => ['salida'],
            default        => ['entrada'],
        };

        if (!in_array($request->tipo, $permitidos)) {
            $esperado = $permitidos[0] === 'entrada' ? 'Entrada' : 'Salida';
            return response()->json([
                'message' => "Marcacion no permitida. El siguiente paso era: {$esperado}.",
            ], 422);
        }

        $horario = $user->horario_id ? Horario::find($user->horario_id) : null;

        // Coordenadas y geocerca vienen del QR (el empleado estuvo en el kiosco)
        $record = AttendanceRecord::create([
            'user_id'                   => $user->id,
            'sede_id'                   => $sede->id,
            'horario_id'                => $horario?->id,
            'tipo'                      => $request->tipo,
            'lat'                       => $qrData['lat'],
            'lng'                       => $qrData['lng'],
            'metodo'                    => 'qr',
            'qr_validado'               => true,
            'geocerca_validada'         => true,
            'distancia_oficina_mts'     => 0,
            'fecha_hora'                => $fechaHoraQr,
            'es_sincronizacion_offline' => true,
        ]);

        $record->load(['user', 'sede']);

        return response()->json([
            'message' => 'Registro offline sincronizado correctamente.',
            'data'    => $record,
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

    public function myReport(Request $request): JsonResponse
    {
        $user  = $request->user();
        $year  = (int) ($request->year  ?? now()->year);
        $month = (int) ($request->month ?? now()->month);

        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end   = $start->copy()->endOfMonth()->endOfDay();

        // Mes anterior para comparativas
        $prevStart = $start->copy()->subMonth()->startOfDay();
        $prevEnd   = $start->copy()->subMonth()->endOfMonth()->endOfDay();

        $records = AttendanceRecord::where('user_id', $user->id)
            ->whereBetween('fecha_hora', [$start, $end])
            ->orderBy('fecha_hora')
            ->get();

        $prevRecords = AttendanceRecord::where('user_id', $user->id)
            ->whereBetween('fecha_hora', [$prevStart, $prevEnd])
            ->get();

        // Días laborados (días con al menos una entrada)
        $diasLaborados = $records->where('tipo', 'entrada')
            ->groupBy(fn($r) => Carbon::parse($r->fecha_hora)->toDateString())
            ->count();

        $prevDiasLaborados = $prevRecords->where('tipo', 'entrada')
            ->groupBy(fn($r) => Carbon::parse($r->fecha_hora)->toDateString())
            ->count();

        // Entradas y salidas
        $entradas     = $records->where('tipo', 'entrada')->count();
        $salidas      = $records->where('tipo', 'salida')->count();
        $prevEntradas = $prevRecords->where('tipo', 'entrada')->count();
        $prevSalidas  = $prevRecords->where('tipo', 'salida')->count();

        // Horas trabajadas (entrada+salida por día)
        $totalMinutos = 0;
        $horasPorDia  = [];

        $byDay = $records->groupBy(fn($r) => Carbon::parse($r->fecha_hora)->toDateString());
        foreach ($byDay as $day => $dayRecords) {
            $entrada = $dayRecords->where('tipo', 'entrada')->sortBy('fecha_hora')->first();
            $salida  = $dayRecords->where('tipo', 'salida')->sortByDesc('fecha_hora')->first();
            if ($entrada && $salida) {
                $mins = Carbon::parse($entrada->fecha_hora)->diffInMinutes(Carbon::parse($salida->fecha_hora));
                $totalMinutos += $mins;
                $horasPorDia[Carbon::parse($day)->day] = round($mins / 60, 1);
            } else {
                $horasPorDia[Carbon::parse($day)->day] = 0;
            }
        }

        // Tardanzas (entrada después de la hora configurada, por defecto 09:00)
        $horaEntrada = '09:00:00';
        if ($user->horario_id) {
            $horario = \App\Models\Horario::find($user->horario_id);
            if ($horario) $horaEntrada = $horario->hora_entrada;
        }

        $tardanzas     = $records->where('tipo', 'entrada')->filter(fn($r) => Carbon::parse($r->fecha_hora)->format('H:i:s') > $horaEntrada)->count();
        $prevTardanzas = $prevRecords->where('tipo', 'entrada')->filter(fn($r) => Carbon::parse($r->fecha_hora)->format('H:i:s') > $horaEntrada)->count();

        // Faltas (días hábiles sin entrada)
        $diasHabiles   = 0;
        $cursor = $start->copy();
        while ($cursor->lte($end) && $cursor->lte(now())) {
            if (!$cursor->isWeekend()) $diasHabiles++;
            $cursor->addDay();
        }
        $faltas     = max(0, $diasHabiles - $diasLaborados);
        $prevFaltas = max(0, (int) $prevRecords->where('tipo','entrada')->groupBy(fn($r)=>Carbon::parse($r->fecha_hora)->toDateString())->count() > 0
            ? ($diasHabiles - $prevDiasLaborados) : 0);

        $pct = fn($cur, $prev) => $prev > 0 ? round((($cur - $prev) / $prev) * 100) : ($cur > 0 ? 100 : 0);

        return response()->json([
            'year'  => $year,
            'month' => $month,
            'dias_laborados'  => $diasLaborados,
            'horas_trabajadas' => [
                'horas'   => intdiv($totalMinutos, 60),
                'minutos' => $totalMinutos % 60,
            ],
            'tardanzas' => $tardanzas,
            'faltas'    => $faltas,
            'entradas'  => $entradas,
            'salidas'   => $salidas,
            'horas_por_dia' => empty($horasPorDia) ? new \stdClass() : $horasPorDia,
            'comparativa' => [
                'entradas_pct'   => $pct($entradas, $prevEntradas),
                'salidas_pct'    => $pct($salidas, $prevSalidas),
                'tardanzas_pct'  => $pct($tardanzas, $prevTardanzas),
                'faltas_pct'     => $pct($faltas, $prevFaltas),
            ],
        ]);
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

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'tipo' => 'required|in:entrada,salida',
        ]);

        $record = AttendanceRecord::findOrFail($id);
        $record->update([
            'tipo' => $request->tipo,
        ]);

        return response()->json([
            'message' => 'Registro actualizado correctamente.',
            'data'    => $record->fresh(['user', 'sede']),
        ]);
    }

    public function storeManual(Request $request): JsonResponse
    {
        $request->validate([
            'user_id'    => 'required|integer|exists:users,id',
            'tipo'       => 'required|in:entrada,salida',
            'fecha_hora' => 'required|date',
        ]);

        $empleado = User::findOrFail($request->user_id);
        $horario = $empleado->horario_id ? Horario::find($empleado->horario_id) : null;

        $record = AttendanceRecord::create([
            'user_id'               => $empleado->id,
            'sede_id'               => $empleado->sede_id,
            'horario_id'            => $horario?->id,
            'tipo'                  => $request->tipo,
            'lat'                   => 0,
            'lng'                   => 0,
            'metodo'                => 'manual',
            'qr_validado'           => false,
            'geocerca_validada'     => false,
            'distancia_oficina_mts' => 0,
            'fecha_hora'            => $request->fecha_hora,
        ]);

        $record->load(['user', 'sede']);

        return response()->json([
            'message' => 'Registro manual creado correctamente.',
            'data'    => $record,
        ], 201);
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
