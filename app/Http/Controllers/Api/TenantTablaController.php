<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TenantHelper;
use App\Http\Controllers\Controller;
use App\Models\TenantTabla;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenantTablaController extends Controller
{
    private function checkAdminTenant(Request $request): ?JsonResponse
    {
        if (!$request->user()->admin_tenant) {
            return response()->json(['message' => 'Acceso restringido a admin multi-empresa.'], 403);
        }
        return null;
    }

    public function index(Request $request): JsonResponse
    {
        if ($denied = $this->checkAdminTenant($request)) return $denied;

        $tablas = TenantTabla::orderBy('orden')->orderBy('nombre_tabla')->get();
        return response()->json(['data' => $tablas]);
    }

    public function store(Request $request): JsonResponse
    {
        if ($denied = $this->checkAdminTenant($request)) return $denied;

        $data = $request->validate([
            'nombre_tabla'      => 'required|string|max:100|unique:tbl_admin_tenant,nombre_tabla',
            'descripcion'       => 'nullable|string|max:255',
            'es_bd_central'     => 'required|boolean',
            'copiar_estructura' => 'required|boolean',
            'copiar_datos'      => 'required|boolean',
            'activo'            => 'required|boolean',
            'orden'             => 'nullable|integer|min:0',
        ]);

        $tabla = TenantTabla::create($data);
        return response()->json(['data' => $tabla], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        if ($denied = $this->checkAdminTenant($request)) return $denied;

        $tabla = TenantTabla::findOrFail($id);

        $data = $request->validate([
            'nombre_tabla'      => 'sometimes|string|max:100|unique:tbl_admin_tenant,nombre_tabla,' . $id,
            'descripcion'       => 'nullable|string|max:255',
            'es_bd_central'     => 'sometimes|boolean',
            'copiar_estructura' => 'sometimes|boolean',
            'copiar_datos'      => 'sometimes|boolean',
            'activo'            => 'sometimes|boolean',
            'orden'             => 'nullable|integer|min:0',
        ]);

        $tabla->update($data);
        return response()->json(['data' => $tabla]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        if ($denied = $this->checkAdminTenant($request)) return $denied;

        TenantTabla::findOrFail($id)->delete();
        return response()->json(['message' => 'Tabla eliminada.']);
    }

    // El endpoint de descarga SQL se maneja desde AdminController (ruta web con sesión)
    // para evitar problemas con Bearer token en window.location.href
}

