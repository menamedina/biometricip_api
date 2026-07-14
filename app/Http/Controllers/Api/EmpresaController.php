<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
            'db_name'        => 'nullable|string|max:64|regex:/^[a-zA-Z0-9_]+$/',
            'db_user'        => 'nullable|string|max:64',
            'db_pass'        => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $empresa = Empresa::create([
                'nombre'       => $data['nombre'],
                'ruc'          => $data['ruc'] ?? null,
                'email'        => $data['email'] ?? null,
                'telefono'     => $data['telefono'] ?? null,
                'plan'         => $data['plan'] ?? 'bronce',
                'max_usuarios' => $data['max_usuarios'] ?? 50,
                'is_active'    => true,
            ]);

            // Registrar el tenant con las credenciales de BD provistas
            DB::table('tenants')->insert([
                'empresa_id' => $empresa->id,
                'db_name'    => $data['db_name'] ?? null,
                'db_user'    => $data['db_user'] ?? null,
                'db_pass'    => $data['db_pass'] ?? null,
                'data'       => '{}',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Crear usuario administrador en BD central
            DB::table('users')->insert([
                'name'            => $data['admin_name'] ?? ('Admin ' . $data['nombre']),
                'email'           => $data['admin_email'],
                'password'        => Hash::make($data['admin_password']),
                'role'            => 'admin',
                'is_active'       => 1,
                'empresa_id'      => $empresa->id,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            DB::commit();

            return response()->json(['data' => $empresa->fresh()->loadCount('users')], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear el tenant: ' . $e->getMessage()], 500);
        }
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

    // --- Token de agente local ---

    public function generateAgentToken(Request $request, int $id): JsonResponse
    {
        if ($denied = $this->checkAdminTenant($request)) return $denied;

        $request->validate([
            'vigencia_dias' => 'nullable|integer|min:1|max:3650',
        ]);

        $empresa = Empresa::findOrFail($id);
        $dias    = $request->vigencia_dias ?? 365;

        $token = Str::random(48);

        $empresa->update([
            'agent_token'         => $token,
            'agent_token_vigencia' => now()->addDays($dias),
        ]);

        return response()->json([
            'message'   => 'Token generado correctamente.',
            'token'     => $token,
            'vigencia'  => $empresa->agent_token_vigencia->format('Y-m-d H:i:s'),
            'dias'      => $dias,
        ]);
    }

    public function revokeAgentToken(Request $request, int $id): JsonResponse
    {
        if ($denied = $this->checkAdminTenant($request)) return $denied;

        $empresa = Empresa::findOrFail($id);
        $empresa->update(['agent_token' => null, 'agent_token_vigencia' => null]);

        return response()->json(['message' => 'Token revocado.']);
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
