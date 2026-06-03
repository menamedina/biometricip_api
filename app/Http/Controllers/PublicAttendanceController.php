<?php

namespace App\Http\Controllers;

use App\Helpers\TenantHelper;
use App\Models\AttendancePhoto;
use App\Models\AttendanceRecord;
use App\Models\Sede;
use App\Models\User;
use App\Models\Visitante;
use App\Models\VisitanteImagen;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;

class PublicAttendanceController extends Controller
{
    public function show(string $webToken, string $sedeCode, string $token): View
    {
        $empresaId = $this->resolveEmpresaId($webToken);
        abort_if(!$empresaId, 404);

        TenantHelper::switchTenant($empresaId);

        $sede = Sede::where('codigo', $sedeCode)->where('is_active', true)->firstOrFail();

        abort_if(!$sede->validateV3Token($token), 404);

        return view('public.attendance', compact('sede', 'webToken', 'sedeCode', 'token'));
    }

    public function store(Request $request, string $webToken, string $sedeCode, string $token): JsonResponse
    {
        $empresaId = $this->resolveEmpresaId($webToken);
        if (!$empresaId) {
            return response()->json(['message' => 'QR inválido.'], 404);
        }

        TenantHelper::switchTenant($empresaId);

        $sede = Sede::where('codigo', $sedeCode)->where('is_active', true)->first();

        if (!$sede || !$sede->validateV3Token($token)) {
            return response()->json(['message' => 'QR inválido o expirado.'], 422);
        }

        $request->validate([
            'tipo_usuario'   => 'required|in:empleado,visitante',
            'cedula'         => 'required|string|max:20',
            'tipo'           => 'required|in:entrada,salida',
            'foto_evidencia' => 'required|string',
            // Campos adicionales para visitante en entrada
            'nombre'         => 'required_if:tipo_usuario,visitante,tipo,entrada|nullable|string|max:255',
            'telefono'       => 'nullable|string|max:20',
            'eps'            => 'nullable|string|max:100',
            'arl'            => 'nullable|string|max:100',
            'persona_visita' => 'required_if:tipo_usuario,visitante,tipo,entrada|nullable|string|max:255',
        ], [
            'tipo_usuario.required'      => 'Indica si eres empleado o visitante.',
            'cedula.required'            => 'La cédula es obligatoria.',
            'tipo.required'              => 'Selecciona Entrada o Salida.',
            'foto_evidencia.required'    => 'La foto es obligatoria.',
            'nombre.required_if'         => 'El nombre es obligatorio.',
            'persona_visita.required_if' => '¿A quién visitas? Este campo es obligatorio.',
        ]);

        [$fotoFull, $fotoThumb] = $this->procesarFoto($request->foto_evidencia);

        if ($request->tipo_usuario === 'empleado') {
            return $this->registrarEmpleado($request, $sede, (int) $empresaId, $fotoFull, $fotoThumb);
        }

        return $this->registrarVisitante($request, $sede, $fotoFull, $fotoThumb);
    }

    // ── Empleado ────────────────────────────────────────────────────────────────

    private function registrarEmpleado(Request $request, Sede $sede, int $empresaId, ?string $fotoFull, ?string $fotoThumb): JsonResponse
    {
        $user = User::where('cedula', $request->cedula)
            ->where('empresa_id', $empresaId)
            ->where('is_active', true)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró ningún empleado con esa cédula. Contacta a tu administrador para que te registre.',
            ], 404);
        }

        $record = AttendanceRecord::create([
            'user_id'               => $user->id,
            'sede_id'               => $sede->id,
            'tipo'                  => $request->tipo,
            'metodo'                => 'qr_web',
            'qr_validado'           => true,
            'geocerca_validada'     => false,
            'distancia_oficina_mts' => null,
            'foto_evidencia'        => $fotoFull ? 'base64' : null,
            'fecha_hora'            => now(),
        ]);

        if ($fotoFull) {
            AttendancePhoto::create([
                'attendance_record_id' => $record->id,
                'foto_base64'          => $fotoFull,
                'thumbnail_base64'     => $fotoThumb ?? $fotoFull,
            ]);
        }

        $tipoLabel = $request->tipo === 'entrada' ? 'Entrada' : 'Salida';

        return response()->json([
            'success' => true,
            'message' => "¡{$tipoLabel} registrada correctamente!",
            'tipo'    => 'empleado',
            'nombre'  => $user->name,
        ]);
    }

    // ── Visitante ────────────────────────────────────────────────────────────────

    private function registrarVisitante(Request $request, Sede $sede, ?string $fotoFull, ?string $fotoThumb): JsonResponse
    {
        if ($request->tipo === 'entrada') {
            $visitante = Visitante::create([
                'sede_id'        => $sede->id,
                'nombre'         => $request->nombre,
                'cedula'         => $request->cedula,
                'telefono'       => $request->telefono,
                'eps'            => $request->eps,
                'arl'            => $request->arl,
                'persona_visita' => $request->persona_visita,
                'hora_entrada'   => now(),
                'hora_salida'    => null,
            ]);

            if ($fotoFull) {
                VisitanteImagen::create([
                    'visitante_id'     => $visitante->id,
                    'tipo'             => 'entrada',
                    'foto_base64'      => $fotoFull,
                    'thumbnail_base64' => $fotoThumb ?? $fotoFull,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => '¡Entrada registrada! Bienvenido.',
                'tipo'    => 'visitante',
            ]);
        }

        // Salida: buscar última visita abierta (sin hora_salida)
        $visitante = Visitante::where('cedula', $request->cedula)
            ->where('sede_id', $sede->id)
            ->whereNull('hora_salida')
            ->orderBy('hora_entrada', 'desc')
            ->first();

        if (!$visitante) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró un registro de entrada activo para esta cédula.',
            ], 404);
        }

        $visitante->update(['hora_salida' => now()]);

        if ($fotoFull) {
            VisitanteImagen::create([
                'visitante_id'     => $visitante->id,
                'tipo'             => 'salida',
                'foto_base64'      => $fotoFull,
                'thumbnail_base64' => $fotoThumb ?? $fotoFull,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => '¡Salida registrada! Hasta pronto.',
            'tipo'    => 'visitante',
        ]);
    }

    // ── Helpers de imagen ────────────────────────────────────────────────────────

    private function procesarFoto(string $base64Data): array
    {
        if (!preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
            return [null, null];
        }

        $imageType = $matches[1];
        $rawBase64 = substr($base64Data, strpos($base64Data, ',') + 1);
        $decoded   = base64_decode($rawBase64);

        if ($decoded === false) {
            return [null, null];
        }

        $thumbBase64 = $this->generarThumbnailBase64($decoded, $imageType, 40, 40);

        return [$base64Data, $thumbBase64 ?? $base64Data];
    }

    private function resolveEmpresaId(string $webToken): ?int
    {
        try {
            $padded    = $webToken . str_repeat('=', (4 - strlen($webToken) % 4) % 4);
            $encrypted = base64_decode(strtr($padded, '-_', '+/'));
            return (int) Crypt::decryptString($encrypted);
        } catch (\Throwable) {
            return null;
        }
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
