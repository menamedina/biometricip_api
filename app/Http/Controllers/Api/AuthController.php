<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TenantHelper;
use App\Http\Controllers\Controller;
use App\Models\Cargo;
use App\Models\Departamento;
use App\Models\Empleador;
use App\Models\Empresa;
use App\Models\Horario;
use App\Models\Sede;
use App\Models\User;
use App\Models\UserSede;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email'              => 'required|email',
                'password'           => 'required',
                'tratamiento_datos'  => 'nullable|boolean',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['Las credenciales proporcionadas son incorrectas.'],
                ]);
            }

            if (!$user->is_active) {
                return response()->json(['message' => 'Tu cuenta ha sido desactivada.'], 403);
            }

            if ($user->empresa_id !== null) {
                TenantHelper::switchTenant($user->empresa_id);
            }

            // Validar tratamiento de datos
            if (!$user->tratamiento_datos) {
                // Primera vez: requiere aceptación
                if (!$request->tratamiento_datos) {
                    return response()->json([
                        'message' => 'Debes aceptar la política de tratamiento de datos.',
                        'requires_tratamiento' => true,
                    ], 422);
                }
                $user->update([
                    'tratamiento_datos'    => true,
                    'tratamiento_datos_at' => now(),
                ]);
            } else {
                // Ya aceptó antes: actualizar fecha
                $user->update(['tratamiento_datos_at' => now()]);
            }

            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'user'  => $this->userData($user),
                'token' => $token,
            ]);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $msg = '[LOGIN ERROR] email=' . $request->email
                 . ' | ' . $e->getMessage()
                 . ' | ' . $e->getFile() . ':' . $e->getLine();
            error_log($msg);
            Log::error('API Login exception', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'email'   => $request->email,
            ]);
            return response()->json(['message' => 'Error interno: ' . $e->getMessage()], 500);
        }
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->userData($request->user()),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }

    public function uploadProfilePhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'photo.max'    => 'La imagen no debe superar los 2MB.',
            'photo.image'  => 'El archivo debe ser una imagen.',
            'photo.mimes'  => 'Solo se permiten imágenes JPG o PNG.',
        ]);

        $user = $request->user();
        $file = $request->file('photo');

        // Nombre: SHA1 del contenido + timestamp (20 chars) + extensión
        $sha  = substr(sha1(file_get_contents($file->getRealPath()) . $user->id . time()), 0, 20);
        $ext  = $file->getClientOriginalExtension() ?: 'jpg';
        $name = $sha . '.' . $ext;

        // Eliminar foto anterior si existe
        if ($user->foto_url) {
            $oldPath = public_path('perfil/' . basename($user->foto_url));
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $file->move(public_path('perfil'), $name);

        $url = url('perfil/' . $name);
        $user->update(['foto_url' => $url]);

        return response()->json(['foto_url' => $url, 'message' => 'Foto actualizada correctamente.']);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        // Respuesta genérica para no revelar si el email existe
        if (!$user || !$user->is_active) {
            return response()->json(['message' => 'Si el correo está registrado, recibirás el código OTP.']);
        }

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'otp_code'       => $otp,
            'otp_expires_at' => now()->addMinutes(6),
        ]);

        try {
            Mail::raw(
                "Tu código de verificación BiometricIP es: {$otp}\n\nEste código expira en 6 minutos.",
                function ($m) use ($user, $otp) {
                    $m->to($user->email, $user->name)
                      ->subject('Código OTP - Recuperar contraseña BiometricIP');
                }
            );
        } catch (\Throwable $e) {
            Log::error('OTP email error', ['error' => $e->getMessage(), 'email' => $user->email]);
            return response()->json(['message' => 'Error al enviar el correo. Intenta más tarde.'], 500);
        }

        return response()->json(['message' => 'Si el correo está registrado, recibirás el código OTP.']);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->otp_code !== $request->otp || !$user->otp_expires_at || now()->isAfter($user->otp_expires_at)) {
            return response()->json(['message' => 'Código inválido o expirado.'], 422);
        }

        return response()->json(['message' => 'Código válido.']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email'        => 'required|email',
            'otp'          => 'required|string|size:6',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->otp_code !== $request->otp || !$user->otp_expires_at || now()->isAfter($user->otp_expires_at)) {
            return response()->json(['message' => 'Código inválido o expirado.'], 422);
        }

        $user->update([
            'password'       => Hash::make($request->new_password),
            'otp_code'       => null,
            'otp_expires_at' => null,
        ]);

        return response()->json(['message' => 'Contraseña actualizada correctamente.']);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'new_password.required'  => 'La nueva contraseña es obligatoria.',
            'new_password.min'       => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'new_password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $user = $request->user();

        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json(['message' => 'Contraseña actualizada correctamente.']);
    }

    private function userData(User $user): array
    {
        $departamentoNombre = null;
        $cargoNombre        = null;
        $empresaNombre      = null;
        $empleadorNombre    = null;
        $liderNombre        = null;

        $horarioData = null;

        if ($user->empresa_id !== null) {
            $empresaNombre = Empresa::find($user->empresa_id)?->nombre;
            if ($user->departamento_id) {
                $departamentoNombre = Departamento::find($user->departamento_id)?->nombre;
            }
            if ($user->cargo_id) {
                $cargoNombre = Cargo::find($user->cargo_id)?->nombre;
            }
            if ($user->empleador_id) {
                $empleadorNombre = Empleador::find($user->empleador_id)?->nombre;
            }
            if ($user->lider_id) {
                $liderNombre = User::find($user->lider_id)?->name;
            }
            if ($user->horario_id) {
                $h = Horario::with('dias')->find($user->horario_id);
                if ($h) {
                    $horarioData = [
                        'id'    => $h->id,
                        'nombre'=> $h->nombre,
                        'dias'  => $h->dias,
                    ];
                }
            }
        }

        // Sedes múltiples desde tbl_user_sedes
        // Hacemos switchTenant por cada registro para garantizar el tenant correcto
        $sedesData   = [];
        $userSedes   = UserSede::where('user_id', $user->id)->get();
        $userSedeIds = $userSedes->pluck('sede_id')->all();

        foreach ($userSedes as $us) {
            TenantHelper::switchTenant($us->empresa_id);
            $sede = Sede::find($us->sede_id);
            if ($sede) {
                $sedesData[] = [
                    'id'        => $sede->id,
                    'nombre'    => $sede->nombre,
                    'lat'       => (float) $sede->lat,
                    'lng'       => (float) $sede->lng,
                    'radio_mts' => (float) $sede->radio_mts,
                ];
            }
        }

        // Restaurar tenant del usuario
        if ($user->empresa_id !== null) {
            TenantHelper::switchTenant($user->empresa_id);
        }

        return [
            'id'              => $user->id,
            'name'            => $user->name,
            'email'           => $user->email,
            'role'            => $user->role,
            'tipo'            => $user->tipo ?? 'usuario',
            'empresa_id'      => $user->empresa_id,
            'empresa'         => $empresaNombre,
            'codigo_empleado' => $user->codigo_empleado,
            'departamento_id' => $user->departamento_id,
            'cargo_id'        => $user->cargo_id,
            'horario_id'      => $user->horario_id,
            'sede_ids'        => $userSedeIds,
            'sedes'           => $sedesData,
            'departamento'    => $departamentoNombre,
            'cargo'           => $cargoNombre,
            'horario'         => $horarioData,
            'telefono'        => $user->telefono,
            'foto_url'        => $user->foto_url,
            'is_active'       => $user->is_active,
            'empleador'       => $empleadorNombre,
            'lider'           => $liderNombre,
        ];
    }
}
