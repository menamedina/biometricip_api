<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use App\Models\Departamento;
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

    public function store(Request $request): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;

        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email',
            'password'        => 'required|string|min:6',
            'codigo_empleado' => [
                'required', 'string', 'max:20',
                Rule::unique('users', 'codigo_empleado')->where('empresa_id', $empresaId),
            ],
            'role'            => 'nullable|in:admin,empleado',
            'admin_tenant'    => 'nullable|boolean',
            'departamento_id' => 'nullable|integer',
            'cargo_id'        => 'nullable|integer',
            'horario_id'      => 'nullable|integer',
            'telefono'        => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name'            => $data['name'],
            'email'           => $data['email'],
            'password'        => Hash::make($data['password']),
            'role'            => $data['role'] ?? 'empleado',
            'admin_tenant'    => $data['admin_tenant'] ?? false,
            'is_active'       => true,
            'empresa_id'      => $empresaId,
            'codigo_empleado' => $data['codigo_empleado'],
            'departamento_id' => $data['departamento_id'] ?? null,
            'cargo_id'        => $data['cargo_id'] ?? null,
            'horario_id'      => $data['horario_id'] ?? null,
            'telefono'        => $data['telefono'] ?? null,
        ]);

        return response()->json(['data' => $this->withNames($user)], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $empleado = User::where('id', $id)
            ->where('empresa_id', $request->user()->empresa_id)
            ->where('role', 'empleado')
            ->firstOrFail();

        return response()->json(['data' => $this->withNames($empleado)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;

        $empleado = User::where('id', $id)
            ->where('empresa_id', $empresaId)
            ->where('role', 'empleado')
            ->firstOrFail();

        $data = $request->validate([
            'name'            => 'sometimes|string|max:255',
            'email'           => 'sometimes|email|unique:users,email,' . $empleado->id,
            'password'        => 'nullable|string|min:6',
            'codigo_empleado' => [
                'sometimes', 'string', 'max:20',
                Rule::unique('users', 'codigo_empleado')
                    ->where('empresa_id', $empresaId)
                    ->ignore($empleado->id),
            ],
            'role'            => 'nullable|in:admin,empleado',
            'admin_tenant'    => 'nullable|boolean',
            'departamento_id' => 'nullable|integer',
            'cargo_id'        => 'nullable|integer',
            'horario_id'      => 'nullable|integer',
            'telefono'        => 'nullable|string|max:20',
            'is_active'       => 'nullable|boolean',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
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

    private function withNames(User $user): array
    {
        $data = $user->toArray();
        $data['departamento'] = $user->departamento_id
            ? Departamento::find($user->departamento_id)?->nombre
            : null;
        $data['cargo'] = $user->cargo_id
            ? Cargo::find($user->cargo_id)?->nombre
            : null;
        return $data;
    }
}
