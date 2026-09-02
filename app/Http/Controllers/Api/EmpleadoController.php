<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TenantHelper;
use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Cargo;
use App\Models\Departamento;
use App\Models\Empleador;
use App\Models\Empresa;
use App\Models\ImagenRostro;
use App\Models\User;
use App\Models\UserImagen;
use App\Models\UserSede;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
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
        } elseif ($request->filled('empresa_id')) {
            $query->where('empresa_id', $request->empresa_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $cargoIds = Cargo::where('nombre', 'like', "%{$search}%")->pluck('id');
            $query->where(function ($q) use ($search, $cargoIds) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('codigo_empleado', 'like', "%{$search}%")
                    ->orWhere('cedula', 'like', "%{$search}%")
                    ->orWhere('telefono', 'like', "%{$search}%")
                    ->orWhere('centro_costo', 'like', "%{$search}%")
                    ->orWhere('ruta', 'like', "%{$search}%")
                    ->orWhereIn('cargo_id', $cargoIds);
            });
        }

        if ($request->filled('departamento_id')) {
            $query->where('departamento_id', $request->departamento_id);
        }

        if ($request->filled('cargo_id')) {
            $query->where('cargo_id', $request->cargo_id);
        }

        if ($request->filled('horario_id')) {
            $query->where('horario_id', $request->horario_id);
        }

        if ($request->filled('empleador_id')) {
            $query->where('empleador_id', $request->empleador_id);
        }

        if ($request->filled('lider_id')) {
            $query->where('lider_id', $request->lider_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('sede_id')) {
            $query->whereHas('userSedes', fn($q) => $q->where('sede_id', $request->sede_id));
        }

        $allowed = ['name','email','cedula','codigo_empleado','role','departamento_id','cargo_id','horario_id','empleador_id','lider_id','telefono','centro_costo','ruta','is_active','created_at'];
        $sortCol = in_array($request->sort, $allowed) ? $request->sort : 'name';
        $sortDir = $request->dir === 'desc' ? 'desc' : 'asc';

        $empleados = $query
            ->select(['id','name','cedula','email','role','tipo','admin_tenant','is_active','exportar_empleados','importar_empleados','crear_empleado','editar_empleado','empresa_id','empleador_id','lider_id','codigo_empleado','departamento_id','cargo_id','horario_id','telefono','centro_costo','ruta','foto_url','last_login_at','created_at',
                DB::raw('(face_descriptor IS NOT NULL) AS has_face_descriptor')])
            ->with('userSedes')
            ->orderBy($sortCol, $sortDir)
            ->paginate($request->per_page ?? 20);

        // Resolver nombres de cargo/departamento por empresa (cada una tiene su propio tenant DB)
        $nombresPorEmpresa = [];
        $empresaIds = $empleados->pluck('empresa_id')->filter()->unique()->values();
        foreach ($empresaIds as $empId) {
            try {
                \App\Helpers\TenantHelper::switchTenant((int) $empId);
                $nombresPorEmpresa[$empId] = [
                    'cargos' => Cargo::pluck('nombre', 'id'),
                    'deptos' => Departamento::pluck('nombre', 'id'),
                    'sedes'  => \App\Models\Sede::pluck('nombre', 'id'),
                ];
            } catch (\Throwable $e) {
                $nombresPorEmpresa[$empId] = ['cargos' => collect(), 'deptos' => collect(), 'sedes' => collect()];
            }
        }
        // Restaurar tenant de la sesión si aplica
        if (session('empresa_id')) {
            try { \App\Helpers\TenantHelper::switchTenant((int) session('empresa_id')); } catch (\Throwable) {}
        }

        // Thumbnails de foto de perfil (1 sola consulta para toda la página)
        $userIds    = $empleados->pluck('id')->all();
        $thumbnails = UserImagen::whereIn('user_id', $userIds)
            ->get(['user_id', 'imagen_thumbnail'])
            ->keyBy('user_id');

        $empleados->getCollection()->transform(function ($user) use ($nombresPorEmpresa, $thumbnails) {
            $data  = $user->toArray();
            $data['sede_ids']            = $user->userSedes->pluck('sede_id')->values()->all();
            $data['encrypted_id']        = Crypt::encryptString((string) $user->id);
            $maps = $nombresPorEmpresa[$user->empresa_id] ?? ['cargos' => collect(), 'deptos' => collect(), 'sedes' => collect()];
            $data['cargo_nombre']        = $maps['cargos']->get($user->cargo_id);
            $data['departamento_nombre'] = $maps['deptos']->get($user->departamento_id);
            $data['sede_nombres']        = collect($data['sede_ids'])->map(fn($id) => $maps['sedes']->get($id))->filter()->values()->all();
            $data['foto_perfil_thumbnail'] = $thumbnails->get($user->id)?->imagen_thumbnail;
            return $data;
        });

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
            $totalActivos = User::where('empresa_id', $empresaId)->where('is_active', true)->count();
            return response()->json([
                'message' => "Límite de usuarios alcanzado ({$totalActivos}/{$maxUsuarios}). Actualice su plan para agregar más usuarios.",
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
            'email'           => ['required', 'email',
                Rule::unique('users', 'email')->where('empresa_id', $empresaId),
            ],
            'password'        => 'required|string|min:6',
            'role'            => 'nullable|in:admin,supervisor,empleado',
            'tipo'            => 'nullable|in:usuario,kiosco',
            'admin_tenant'    => 'nullable|boolean',
            'departamento_id' => 'nullable|integer',
            'cargo_id'        => 'nullable|integer',
            'horario_id'      => 'nullable|integer',
            'empleador_id'    => 'nullable|integer',
            'lider_id'        => 'nullable|integer|exists:users,id',
            'sede_ids'        => 'nullable|array',
            'sede_ids.*'      => 'integer',
            'cedula'          => ['required', 'string', 'max:20',
                Rule::unique('users', 'cedula')->where('empresa_id', $empresaId),
            ],
            'telefono'        => 'nullable|string|max:20',
            'centro_costo'    => 'nullable|string|max:100',
            'ruta'            => 'nullable|string|max:100',
            'exportar_empleados' => 'nullable|boolean',
            'importar_empleados' => 'nullable|boolean',
            'crear_empleado'     => 'nullable|boolean',
            'editar_empleado'    => 'nullable|boolean',
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
            'empleador_id'    => $data['empleador_id'] ?? null,
            'lider_id'        => $data['lider_id'] ?? null,
            'cedula'          => $data['cedula'] ?? null,
            'telefono'        => $data['telefono'] ?? null,
            'centro_costo'    => $data['centro_costo'] ?? null,
            'ruta'            => $data['ruta'] ?? null,
            'exportar_empleados' => $data['exportar_empleados'] ?? false,
            'importar_empleados' => $data['importar_empleados'] ?? false,
            'crear_empleado'     => $data['crear_empleado'] ?? false,
            'editar_empleado'    => $data['editar_empleado'] ?? false,
        ]);

        $this->syncSedes($user->id, $empresaId, $data['sede_ids'] ?? []);

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
        $data['tiene_movimientos'] = false; // se consulta lazy vía /admin/empleados/{id}/movimientos

        return response()->json(['data' => $data]);
    }

    public function tieneMovimientos(Request $request, int $id): JsonResponse
    {
        $authUser = $request->user();
        if (!$authUser->admin_tenant) {
            return response()->json(['tiene_movimientos' => false]);
        }

        $empleado = User::where('id', $id)->first();
        if (!$empleado || !$empleado->empresa_id) {
            return response()->json(['tiene_movimientos' => false]);
        }

        try {
            TenantHelper::switchTenant($empleado->empresa_id);
            $tiene = AttendanceRecord::where('user_id', $id)->exists();
        } catch (\Throwable $e) {
            $tiene = false;
        }

        return response()->json(['tiene_movimientos' => $tiene]);
    }

    public function update(Request $request, string $token): JsonResponse
    {
        try { $id = (int) Crypt::decryptString($token); }
        catch (\Throwable $e) { return response()->json(['message' => 'ID inválido.'], 422); }

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
            'email'           => ['sometimes', 'email',
                Rule::unique('users', 'email')->where('empresa_id', $efectivoEmpresaId)->ignore($empleado->id),
            ],
            'password'        => 'nullable|string|min:6',
            'codigo_empleado' => [
                'sometimes', 'string', 'max:20',
                Rule::unique('users', 'codigo_empleado')
                    ->where('empresa_id', $efectivoEmpresaId)
                    ->ignore($empleado->id),
            ],
            'role'            => 'nullable|in:admin,supervisor,empleado',
            'tipo'            => 'nullable|in:usuario,kiosco',
            'admin_tenant'    => 'nullable|boolean',
            'empresa_id'      => 'nullable|integer',
            'departamento_id' => 'nullable|integer',
            'cargo_id'        => 'nullable|integer',
            'horario_id'      => 'nullable|integer',
            'empleador_id'    => 'nullable|integer',
            'lider_id'        => 'nullable|integer|exists:users,id',
            'sede_ids'        => 'nullable|array',
            'sede_ids.*'      => 'integer',
            'cedula'          => ['nullable', 'string', 'max:20',
                Rule::unique('users', 'cedula')->where('empresa_id', $efectivoEmpresaId)->ignore($empleado->id),
            ],
            'telefono'        => 'nullable|string|max:20',
            'centro_costo'    => 'nullable|string|max:100',
            'ruta'            => 'nullable|string|max:100',
            'is_active'       => 'nullable|boolean',
            'exportar_empleados' => 'nullable|boolean',
            'importar_empleados' => 'nullable|boolean',
            'crear_empleado'     => 'nullable|boolean',
            'editar_empleado'    => 'nullable|boolean',
        ]);

        // Validar límite de usuarios:
        // - Si se desactiva el usuario, siempre permitir
        // - Si se mantiene activo o se activa, validar que no se supere el máximo
        $seDesactiva = isset($data['is_active']) && !$data['is_active'];
        $estaraActivo = $seDesactiva ? false : ($data['is_active'] ?? $empleado->is_active);

        if ($estaraActivo) {
            $excludeId = $cambiaEmpresa ? null : $empleado->id;
            if ($denied = $this->checkMaxUsuarios($efectivoEmpresaId, $excludeId)) return $denied;
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

        // Sincronizar sedes en tbl_user_sedes
        if ($request->has('sede_ids')) {
            $efectivoEmpresaId = $empresaId ?? $empleado->empresa_id;
            $this->syncSedes($empleado->id, $efectivoEmpresaId, $data['sede_ids'] ?? []);
        }
        unset($data['sede_ids']);

        DB::transaction(function () use ($empleado, $data) {
            DB::statement('SET @audit_user_id = ?', [auth()->id()]);
            $empleado->update($data);
        });

        return response()->json(['data' => $this->withNames($empleado->fresh())]);
    }

    public function conteoUsuarios(Request $request): JsonResponse
    {
        $authUser  = $request->user();
        $empresaId = $authUser->admin_tenant
            ? (int) $request->query('empresa_id', 0)
            : $authUser->empresa_id;

        if (!$empresaId) {
            return response()->json(['activos' => 0, 'max' => 0]);
        }

        $empresa = Empresa::find($empresaId);
        if (!$empresa) {
            return response()->json(['activos' => 0, 'max' => 0]);
        }

        $max      = $empresa->max_usuarios ?? 50;
        $activos  = User::where('empresa_id', $empresaId)->where('is_active', true)->count();
        $total    = User::where('empresa_id', $empresaId)->count();

        return response()->json(['activos' => $activos, 'total' => $total, 'max' => $max]);
    }

    public function destroy(Request $request, string $token): JsonResponse
    {
        try { $id = (int) Crypt::decryptString($token); }
        catch (\Throwable $e) { return response()->json(['message' => 'ID inválido.'], 422); }

        $authUser = $request->user();
        $query = User::where('id', $id);

        if (!$authUser->admin_tenant) {
            $query->where('empresa_id', $authUser->empresa_id);
        }

        $empleado = $query->firstOrFail();
        DB::transaction(function () use ($empleado) {
            DB::statement('SET @audit_user_id = ?', [auth()->id()]);
            $empleado->update(['is_active' => false]);
        });

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

    private function redimensionarImagen(string $base64Input, int $width, int $height): string
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
            return $base64Input;
        }

        $dst = imagecreatetruecolor($width, $height);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, imagesx($src), imagesy($src));

        ob_start();
        imagejpeg($dst, null, 85);
        $jpegBytes = ob_get_clean();

        imagedestroy($src);
        imagedestroy($dst);

        return 'data:image/jpeg;base64,' . base64_encode($jpegBytes);
    }

    private function redimensionarRostro(string $base64Input): string
    {
        return $this->redimensionarImagen($base64Input, 400, 400);
    }

    // ─── Foto de perfil ──────────────────────────────────────────────────────

    public function getImagenPerfil(Request $request, int $id): JsonResponse
    {
        $this->resolveEmpleado($request, $id);

        $imagen = UserImagen::where('user_id', $id)->first();

        return response()->json([
            'tiene_imagen'     => (bool) $imagen,
            'imagen_thumbnail' => $imagen?->imagen_thumbnail,
            'imagen_base64'    => $imagen?->imagen_base64,
        ]);
    }

    public function storeImagenPerfil(Request $request, int $id): JsonResponse
    {
        $this->resolveEmpleado($request, $id);

        $request->validate([
            'imagen_base64' => 'required|string',
        ]);

        $imagenCompleta  = $this->redimensionarImagen($request->imagen_base64, 400, 400);
        $imagenThumbnail = $this->redimensionarImagen($request->imagen_base64, 150, 150);

        UserImagen::updateOrCreate(
            ['user_id' => $id],
            ['imagen_base64' => $imagenCompleta, 'imagen_thumbnail' => $imagenThumbnail]
        );

        return response()->json([
            'message'          => 'Foto de perfil guardada correctamente.',
            'imagen_thumbnail' => $imagenThumbnail,
        ]);
    }

    public function destroyImagenPerfil(Request $request, int $id): JsonResponse
    {
        $this->resolveEmpleado($request, $id);

        UserImagen::where('user_id', $id)->delete();

        return response()->json(['message' => 'Foto de perfil eliminada.']);
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
        unset($data['face_descriptor'], $data['password'], $data['remember_token']);
        $data['encrypted_id'] = Crypt::encryptString((string) $user->id);
        $data['departamento'] = $user->departamento_id
            ? Departamento::find($user->departamento_id)?->nombre
            : null;
        $data['cargo'] = $user->cargo_id
            ? Cargo::find($user->cargo_id)?->nombre
            : null;
        $data['empresa'] = $user->empresa_id
            ? Empresa::find($user->empresa_id)?->nombre
            : null;
        $data['empleador'] = $user->empleador_id
            ? Empleador::find($user->empleador_id)?->nombre
            : null;
        $data['lider'] = $user->lider_id
            ? User::find($user->lider_id)?->name
            : null;

        $data['sede_ids'] = UserSede::where('user_id', $user->id)
            ->pluck('sede_id')
            ->values()
            ->all();

        return $data;
    }

    private function syncSedes(int $userId, int $empresaId, array $sedeIds): void
    {
        // Eliminar sedes que ya no están en la lista
        UserSede::where('user_id', $userId)
            ->whereNotIn('sede_id', $sedeIds)
            ->delete();

        // Insertar las nuevas sedes que no existen aún
        foreach ($sedeIds as $sedeId) {
            UserSede::firstOrCreate(
                ['user_id' => $userId, 'sede_id' => $sedeId],
                ['empresa_id' => $empresaId]
            );
        }
    }
}
