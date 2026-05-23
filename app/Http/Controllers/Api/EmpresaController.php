<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class EmpresaController extends Controller
{
    public function index(): JsonResponse
    {
        $empresas = Empresa::with('tenant')->orderBy('nombre')->get();
        return response()->json(['data' => $empresas]);
    }

    public function show(Empresa $empresa): JsonResponse
    {
        return response()->json(['data' => $empresa->load('tenant')]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre'         => 'required|string|max:255',
            'ruc'            => 'nullable|string|max:20|unique:tbl_empresas,ruc',
            'email'          => 'nullable|email|max:255',
            'telefono'       => 'nullable|string|max:20',
            'admin_email'    => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:6',
            'admin_name'     => 'nullable|string|max:255',
        ]);

        $empresa = Empresa::create([
            'nombre'    => $data['nombre'],
            'ruc'       => $data['ruc'] ?? null,
            'email'     => $data['email'] ?? null,
            'telefono'  => $data['telefono'] ?? null,
            'is_active' => true,
        ]);

        Artisan::call('tenant:create-db',        ['empresa_id' => $empresa->id]);
        Artisan::call('tenant:create-structure',  ['empresa_id' => $empresa->id]);
        Artisan::call('tenant:seed', [
            'empresa_id'       => $empresa->id,
            '--admin-email'    => $data['admin_email'],
            '--admin-password' => $data['admin_password'],
            '--admin-name'     => $data['admin_name'] ?? null,
        ]);

        return response()->json(['data' => $empresa->fresh('tenant')], 201);
    }

    public function update(Request $request, Empresa $empresa): JsonResponse
    {
        $data = $request->validate([
            'nombre'    => 'sometimes|string|max:255',
            'ruc'       => 'nullable|string|max:20|unique:tbl_empresas,ruc,' . $empresa->id,
            'email'     => 'nullable|email|max:255',
            'telefono'  => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        $empresa->update($data);
        return response()->json(['data' => $empresa]);
    }

    public function destroy(Empresa $empresa): JsonResponse
    {
        $empresa->update(['is_active' => false]);
        return response()->json(['message' => 'Empresa desactivada.']);
    }
}
