<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    /**
     * Vista principal de roles y permisos.
     */
    public function index()
    {
        // La ruta ya está protegida por middleware role:admin
        return view('admin.roles.index');
    }

    /**
     * Lista todos los roles con conteo de permisos.
     */
    public function list(): JsonResponse
    {
        $roles = Role::withCount('permissions')->orderBy('name')->get();

        return response()->json($roles);
    }

    /**
     * Permisos de un rol específico, agrupados por módulo.
     */
    public function show(int $id): JsonResponse
    {

        $rol = Role::with('permissions')->findOrFail($id);

        $permisosAgrupados = $rol->permissions
            ->groupBy(fn ($p) => explode('.', $p->name)[0])
            ->map(fn ($grupo) => $grupo->map(fn ($p) => [
                'id'     => $p->id,
                'name'   => $p->name,
                'accion' => explode('.', $p->name)[1] ?? $p->name,
            ])->values());

        return response()->json([
            'id'                => $rol->id,
            'name'              => $rol->name,
            'permissions_count' => $rol->permissions->count(),
            'permisos'          => $permisosAgrupados,
        ]);
    }

    /**
     * Lista todos los permisos del sistema agrupados por módulo.
     */
    public function allPermissions(): JsonResponse
    {
        $agrupados = Permission::orderBy('name')
            ->get()
            ->groupBy(fn ($p) => explode('.', $p->name)[0])
            ->map(fn ($grupo) => $grupo->map(fn ($p) => [
                'id'     => $p->id,
                'name'   => $p->name,
                'accion' => explode('.', $p->name)[1] ?? $p->name,
            ])->values());

        return response()->json($agrupados);
    }

    /**
     * Crear un nuevo rol con permisos.
     */
    public function store(Request $request): JsonResponse
    {
        abort_unless(auth()->user()->can('roles.crear'), 403);

        $request->validate([
            'name'        => 'required|string|max:125|unique:mysql.roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:mysql.permissions,name',
        ]);

        $rol = DB::connection('mysql')->transaction(function () use ($request) {
            $rol = Role::create(['name' => $request->name, 'guard_name' => 'web']);
            if (!empty($request->permissions)) {
                $rol->syncPermissions($request->permissions);
            }
            return $rol;
        });

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'message' => 'Rol creado correctamente.',
            'id'      => $rol->id,
            'name'    => $rol->name,
        ], 201);
    }

    /**
     * Actualizar nombre y permisos de un rol.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        abort_unless(auth()->user()->can('roles.editar'), 403);

        $request->validate([
            'name'          => 'required|string|max:125|unique:mysql.roles,name,' . $id,
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|exists:mysql.permissions,name',
        ]);

        $rol = Role::findOrFail($id);

        DB::connection('mysql')->transaction(function () use ($rol, $request) {
            $rol->update(['name' => $request->name]);
            $rol->syncPermissions($request->permissions ?? []);
        });

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json(['message' => 'Rol actualizado correctamente.']);
    }

    /**
     * Eliminar un rol (no permitido si tiene usuarios asignados).
     */
    public function destroy(int $id): JsonResponse
    {
        abort_unless(auth()->user()->can('roles.eliminar'), 403);

        $rol = Role::findOrFail($id);

        $usersCount = DB::connection('mysql')
            ->table('model_has_roles')
            ->where('role_id', $id)
            ->where('model_type', \App\Models\User::class)
            ->count();

        if ($usersCount > 0) {
            return response()->json([
                'message' => "No se puede eliminar el rol \"{$rol->name}\" porque tiene {$usersCount} usuario(s) asignado(s).",
            ], 422);
        }

        $rol->delete();

        return response()->json(['message' => 'Rol eliminado correctamente.']);
    }
}
