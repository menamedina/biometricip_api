<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class EmpresaController extends Controller
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

        $empresas = Empresa::withCount('users')->orderBy('nombre')->get();
        return response()->json(['data' => $empresas]);
    }

    public function showById(Request $request, int $id): JsonResponse
    {
        if ($denied = $this->checkAdminTenant($request)) return $denied;

        $empresa = Empresa::findOrFail($id);
        return response()->json(['data' => $empresa]);
    }

    public function store(Request $request): JsonResponse
    {
        if ($denied = $this->checkAdminTenant($request)) return $denied;

        $data = $request->validate([
            'nombre'         => 'required|string|max:255',
            'ruc'            => 'nullable|string|max:20|unique:tbl_empresas,ruc',
            'email'          => 'nullable|email|max:255',
            'telefono'       => 'nullable|string|max:20',
            'plan'           => 'nullable|in:bronce,plata,oro',
            'max_usuarios'   => 'nullable|integer|min:1',
            'admin_email'    => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:6',
            'admin_name'     => 'nullable|string|max:255',
        ]);

        $empresa = Empresa::create([
            'nombre'    => $data['nombre'],
            'ruc'       => $data['ruc'] ?? null,
            'email'     => $data['email'] ?? null,
            'telefono'  => $data['telefono'] ?? null,
            'plan'          => $data['plan'] ?? 'bronce',
            'max_usuarios'  => $data['max_usuarios'] ?? 50,
            'is_active'     => true,
        ]);

        Artisan::call('tenant:create-db',        ['empresa_id' => $empresa->id]);
        Artisan::call('tenant:create-structure',  ['empresa_id' => $empresa->id]);
        Artisan::call('tenant:seed', [
            'empresa_id'       => $empresa->id,
            '--admin-email'    => $data['admin_email'],
            '--admin-password' => $data['admin_password'],
            '--admin-name'     => $data['admin_name'] ?? null,
        ]);

        return response()->json(['data' => $empresa->fresh()], 201);
    }

    public function updateById(Request $request, int $id): JsonResponse
    {
        if ($denied = $this->checkAdminTenant($request)) return $denied;

        $empresa = Empresa::findOrFail($id);

        $data = $request->validate([
            'nombre'    => 'sometimes|string|max:255',
            'ruc'       => 'nullable|string|max:20|unique:tbl_empresas,ruc,' . $empresa->id,
            'email'     => 'nullable|email|max:255',
            'telefono'  => 'nullable|string|max:20',
            'plan'          => 'nullable|in:bronce,plata,oro',
            'max_usuarios'  => 'nullable|integer|min:1',
            'is_active'     => 'nullable|boolean',
        ]);

        $empresa->update($data);
        return response()->json(['data' => $empresa]);
    }

    public function destroyById(Request $request, int $id): JsonResponse
    {
        if ($denied = $this->checkAdminTenant($request)) return $denied;

        $empresa = Empresa::findOrFail($id);
        $empresa->update(['is_active' => false]);
        return response()->json(['message' => 'Empresa desactivada.']);
    }

    // --- Empresa propia (admin normal) ---

    public function miEmpresa(Request $request): JsonResponse
    {
        $empresa = Empresa::find($request->user()->empresa_id);
        if (!$empresa) {
            return response()->json(['message' => 'Empresa no encontrada.'], 404);
        }
        return response()->json(['data' => $empresa]);
    }

    public function updateMiEmpresa(Request $request): JsonResponse
    {
        $empresa = Empresa::find($request->user()->empresa_id);
        if (!$empresa) {
            return response()->json(['message' => 'Empresa no encontrada.'], 404);
        }

        $data = $request->validate([
            'nombre'    => 'sometimes|string|max:255',
            'ruc'       => 'nullable|string|max:20|unique:tbl_empresas,ruc,' . $empresa->id,
            'email'     => 'nullable|email|max:255',
            'telefono'  => 'nullable|string|max:20',
        ]);

        $empresa->update($data);
        return response()->json(['data' => $empresa]);
    }
}
