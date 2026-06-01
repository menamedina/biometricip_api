<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TenantHelper;
use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Cargo;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\ImagenRostro;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmpleadoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $authUser = $request->user();

        $query = User::query();

        // Si NO es admin_tenant, solo ve usuarios de su propia empresa
        if (!$authUser->admin_tenant) {
            $query->where('empresa_id', $authUser->empresa_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('codigo_empleado', 'like', "%{$search}%");
            });
        }

        if ($request->filled('departamento_id')) {
            $query->where('departamento_id', $request->departamento_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $empleados = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 20);

        return response()->json($empleados);
    }

    private function checkMaxUsuarios(int $empresaId, ?int $excludeUserId = null): ?JsonResponse
    {
        $empresa = Empresa::find($empresaId);
        if (!$empresa) return null;

        $maxUsuarios = $empresa->max_usuarios ?? 50;
        $query = User::where('empresa_id', $empresaId)->where('is_active', true);
        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }
        $actuales = $query->count();

        if ($actuales >= $maxUsuarios) {
            return response()->json([
                'message' => "Límite de usuarios alcanzado ({$actuales}/{$maxUsuarios}). Actualice su plan para agregar más usuarios.",
            ], 422);
        }

        return null;
    }

    public function store(Request $request): JsonResponse
    {
        $authUser  = $request->user();
        // admin_tenant puede crear en cualquier empresa (empresa_id viene del request)
        $empresaId = $authUser->admin_tenant
            ? $request->integer('empresa_id') ?: null
            : $authUser->empresa_id;

        if (!$empresaId) {
            return response()->json(['message' => 'Debes seleccionar una empresa.'], 422);
        }

        // Validar límite de usuarios
        if ($denied = $this->checkMaxUsuarios($empresaId)) return $denied;

        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email',
            'password'        => 'required|string|min:6',
            'role'            => 'nullable|in:admin,empleado',
            'tipo'            => 'nullable|in:usuario,kiosco',
            'admin_tenant'    => 'nullable|boolean',
            'departamento_id' => 'nullable|integer',
            'cargo_id'        => 'nullable|integer',
            'horario_id'      => 'nullable|integer',
            'sede_id'         => 'nullable|integer',
            'telefono'        => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name'            => $data['name'],
            'email'           => $data['email'],
            'password'        => Hash::make($data['password']),
            'role'            => $data['role'] ?? 'empleado',
            'tipo'            => $data['tipo'] ?? 'usuario',
            'admin_tenant'    => $data['admin_tenant'] ?? false,
            'is_active'       => true,
            'empresa_id'      => $empresaId,
            'codigo_empleado' => $this->generarCodigo($empresaId),
            'departamento_id' => $data['departamento_id'] ?? null,
            'cargo_id'        => $data['cargo_id'] ?? null,
            'horario_id'      => $data['horario_id'] ?? null,
            'sede_id'         => $data['sede_id'] ?? null,
            'telefono'        => $data['telefono'] ?? null,
        ]);

        return response()->json(['data' => $this->withNames($user)], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $authUser = $request->user();
        $query = User::where('id', $id);

        if (!$authUser->admin_tenant) {
            $query->where('empresa_id', $authUser->empresa_id);
        }

        $empleado = $query->firstOrFail();

        $data = $this->withNames($empleado);

        // Para admin_tenant: indicar si tiene movimientos (para bloquear cambio de empresa en UI)
        if ($authUser->admin_tenant && $empleado->empresa_id) {
            TenantHelper::switchTenant($empleado->empresa_id);
            $data['tiene_movimientos'] = AttendanceRecord::where('user_id', $empleado->id)->exists();
        } else {
            $data['tiene_movimientos'] = false;
        }

        return response()->json(['data' => $data]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $authUser  = $request->user();
        $empresaId = $authUser->admin_tenant
            ? ($request->integer('empresa_id') ?: null)
            : $authUser->empresa_id;

        $query = User::where('id', $id);
        if (!$authUser->admin_tenant) {
            $query->where('empresa_id', $authUser->empresa_id);
        }
        $empleado = $query->firstOrFail();

        // Si admin_tenant intenta cambiar la empresa, verificar que no tenga movimientos
        $cambiaEmpresa = $authUser->admin_tenant
            && $request->filled('empresa_id')
            && (int) $request->empresa_id !== (int) $empleado->empresa_id;

        if ($cambiaEmpresa) {
            if ($empleado->empresa_id) {
                TenantHelper::switchTenant((int) $empleado->empresa_id);
                $tieneMovimientos = AttendanceRecord::where('user_id', $empleado->id)->exists();
                if ($tieneMovimientos) {
                    return response()->json([
                        'message' => 'No se puede cambiar la empresa porque el empleado tiene registros de asistencia.',
                    ], 422);
                }
            }
        }

        $efectivoEmpresaId = $empresaId ?? $empleado->empresa_id;

        $data = $request->validate([
            'name'            => 'sometimes|string|max:255',
            'email'           => 'sometimes|email|unique:users,email,' . $empleado->id,
            'password'        => 'nullable|string|min:6',
            'codigo_empleado' => [
                'sometimes', 'string', 'max:20',
                Rule::unique('users', 'codigo_empleado')
                    ->where('empresa_id', $efectivoEmpresaId)
                    ->ignore($empleado->id),
            ],
            'role'            => 'nullable|in:admin,empleado',
            'tipo'            => 'nullable|in:usuario,kiosco',
            'admin_tenant'    => 'nullable|boolean',
            'empresa_id'      => 'nullable|integer',
            'departamento_id' => 'nullable|integer',
            'cargo_id'        => 'nullable|integer',
            'horario_id'      => 'nullable|integer',
            'sede_id'         => 'nullable|integer',
            'telefono'        => 'nullable|string|max:20',
            'is_active'       => 'nullable|boolean',
        ]);

        // Si se está activando un usuario inactivo, validar límite
        if (isset($data['is_active']) && $data['is_active'] && !$empleado->is_active) {
            if ($denied = $this->checkMaxUsuarios($empresaId, $empleado->id)) return $denied;
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // empresa_id solo se actualiza si es admin_tenant Y el cambio está permitido
        if (!$authUser->admin_tenant || !$cambiaEmpresa) {
            unset($data['empresa_id']);
        }

        $empleado->update($data);

        return response()->json(['data' => $this->withNames($empleado->fresh())]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $empleado = User::where('id', $id)
            ->where('empresa_id', $request->user()->empresa_id)
            ->where('role', 'empleado')
            ->firstOrFail();

        $empleado->update(['is_active' => false]);

        return response()->json(['message' => 'Empleado desactivado correctamente.']);
    }

    public function updateFaceDescriptor(Request $request, int $id): JsonResponse
    {
        $empleado = User::where('id', $id)
            ->where('empresa_id', $request->user()->empresa_id)
            ->where('role', 'empleado')
            ->firstOrFail();

        $request->validate([
            'face_descriptor' => 'required|array',
        ]);

        $empleado->update(['face_descriptor' => $request->face_descriptor]);

        return response()->json(['message' => 'Descriptor facial actualizado.']);
    }

    public function departamentos(): JsonResponse
    {
        $deptos = Departamento::where('is_active', true)->orderBy('nombre')->get(['id', 'nombre']);
        return response()->json(['data' => $deptos]);
    }

    // ─── Imágenes de rostro ──────────────────────────────────────────────────

    public function getImagenesRostro(Request $request, int $id): JsonResponse
    {
        $this->resolveEmpleado($request, $id);

        // con_imagen=true incluye base64 (usado por el panel admin web)
        $campos = $request->boolean('con_imagen', false)
            ? ['id', 'orden', 'imagen_base64', 'created_at']
            : ['id', 'orden', 'created_at'];

        $imagenes = ImagenRostro::where('user_id', $id)
            ->orderBy('orden')
            ->get($campos);

        return response()->json(['data' => $imagenes]);
    }

    public function storeImagenRostro(Request $request, int $id): JsonResponse
    {
        $this->resolveEmpleado($request, $id);

        $total = ImagenRostro::where('user_id', $id)->count();
        if ($total >= 5) {
            return response()->json(['message' => 'Máximo 5 imágenes de rostro por empleado.'], 422);
        }

        $request->validate([
            'imagen_base64' => 'required|string',
            'descriptor'    => 'nullable|array',
        ]);

        // Redimensionar a 400x400 con GD
        $imagenBase64 = $this->redimensionarRostro($request->imagen_base64);

        $imagen = ImagenRostro::create([
            'user_id'       => $id,
            'imagen_base64' => $imagenBase64,
            'descriptor'    => $request->descriptor,
            'orden'         => $total + 1,
        ]);

        // Recalcular descriptor promedio en users
        $this->actualizarDescriptorPromedio($id);

        return response()->json(['data' => ['id' => $imagen->id, 'orden' => $imagen->orden]], 201);
    }

    public function destroyImagenRostro(Request $request, int $id, int $imageId): JsonResponse
    {
        $this->resolveEmpleado($request, $id);

        $imagen = ImagenRostro::where('id', $imageId)->where('user_id', $id)->firstOrFail();
        $imagen->delete();

        // Reordenar
        ImagenRostro::where('user_id', $id)
            ->orderBy('orden')
            ->get()
            ->each(function ($img, $index) {
                $img->update(['orden' => $index + 1]);
            });

        // Recalcular descriptor promedio
        $this->actualizarDescriptorPromedio($id);

        return response()->json(['message' => 'Imagen eliminada.']);
    }

    private function resolveEmpleado(Request $request, int $id): User
    {
        $authUser = $request->user();
        $query    = User::where('id', $id);
        if (!$authUser->admin_tenant) {
            $query->where('empresa_id', $authUser->empresa_id);
        }
        return $query->firstOrFail();
    }

    private function actualizarDescriptorPromedio(int $userId): void
    {
        $descriptores = ImagenRostro::where('user_id', $userId)
            ->whereNotNull('descriptor')
            ->pluck('descriptor')
            ->toArray();

        if (empty($descriptores)) {
            User::where('id', $userId)->update(['face_descriptor' => null]);
            return;
        }

        $longitud = count($descriptores[0]);
        $promedio = array_fill(0, $longitud, 0.0);

        foreach ($descriptores as $desc) {
            for ($i = 0; $i < $longitud; $i++) {
                $promedio[$i] += $desc[$i];
            }
        }

        $total = count($descriptores);
        $promedio = array_map(fn($v) => $v / $total, $promedio);

        User::where('id', $userId)->update(['face_descriptor' => json_encode($promedio)]);
    }

    private function redimensionarRostro(string $base64Input): string
    {
        // Extraer datos del data URI (acepta data:image/...;base64,... o base64 puro)
        if (str_contains($base64Input, ',')) {
            [, $data] = explode(',', $base64Input, 2);
        } else {
            $data = $base64Input;
        }

        $bytes = base64_decode($data);
        $src   = @imagecreatefromstring($bytes);

        if (!$src) {
            // Si GD no puede procesarla, retornar tal cual
            return $base64Input;
        }

        $dst = imagecreatetruecolor(400, 400);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, 400, 400, imagesx($src), imagesy($src));

        ob_start();
        imagejpeg($dst, null, 85);
        $jpegBytes = ob_get_clean();

        imagedestroy($src);
        imagedestroy($dst);

        return 'data:image/jpeg;base64,' . base64_encode($jpegBytes);
    }

    private function generarCodigo(int $empresaId): string
    {
        $ultimo = User::where('empresa_id', $empresaId)
            ->where('codigo_empleado', 'regexp', '^EMP-[0-9]+$')
            ->orderByRaw('CAST(SUBSTRING(codigo_empleado, 5) AS UNSIGNED) DESC')
            ->value('codigo_empleado');

        $siguiente = $ultimo ? ((int) substr($ultimo, 4)) + 1 : 1;

        // Evitar colisión en caso de que existan códigos manuales con ese número
        while (User::where('empresa_id', $empresaId)
            ->where('codigo_empleado', 'EMP-' . str_pad($siguiente, 4, '0', STR_PAD_LEFT))
            ->exists()) {
            $siguiente++;
        }

        return 'EMP-' . str_pad($siguiente, 4, '0', STR_PAD_LEFT);
    }

    private function withNames(User $user): array
    {
        $data = $user->toArray();
        $data['departamento'] = $user->departamento_id
            ? Departamento::find($user->departamento_id)?->nombre
            : null;
        $data['cargo'] = $user->cargo_id
            ? Cargo::find($user->cargo_id)?->nombre
            : null;
        $data['empresa'] = $user->empresa_id
            ? Empresa::find($user->empresa_id)?->nombre
            : null;
        return $data;
    }
}
