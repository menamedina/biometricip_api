<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmpleadoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Empleado::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('codigo_empleado', 'like', "%{$search}%")
                    ->orWhere('departamento', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('departamento')) {
            $query->where('departamento', $request->departamento);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $empleados = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 20);

        return response()->json($empleados);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'codigo_empleado' => 'required|string|unique:empleados,codigo_empleado|max:20',
            'departamento' => 'nullable|string|max:100',
            'cargo' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'empleado',
        ]);

        $empleado = Empleado::create([
            'user_id' => $user->id,
            'codigo_empleado' => $data['codigo_empleado'],
            'departamento' => $data['departamento'] ?? null,
            'cargo' => $data['cargo'] ?? null,
            'telefono' => $data['telefono'] ?? null,
        ]);

        $empleado->load('user');

        return response()->json(['data' => $empleado], 201);
    }

    public function show(Empleado $empleado): JsonResponse
    {
        $empleado->load('user');

        return response()->json(['data' => $empleado]);
    }

    public function update(Request $request, Empleado $empleado): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $empleado->user_id,
            'password' => 'nullable|string|min:6',
            'codigo_empleado' => 'sometimes|string|unique:empleados,codigo_empleado,' . $empleado->id . '|max:20',
            'departamento' => 'nullable|string|max:100',
            'cargo' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        if (isset($data['name'])) {
            $empleado->user->update(['name' => $data['name']]);
        }
        if (isset($data['email'])) {
            $empleado->user->update(['email' => $data['email']]);
        }
        if (!empty($data['password'])) {
            $empleado->user->update(['password' => Hash::make($data['password'])]);
        }

        $empleado->update(array_filter($data, fn($key) => in_array($key, [
            'codigo_empleado', 'departamento', 'cargo', 'telefono', 'is_active',
        ]), ARRAY_FILTER_USE_KEY));

        $empleado->load('user');

        return response()->json(['data' => $empleado]);
    }

    public function destroy(Empleado $empleado): JsonResponse
    {
        $empleado->update(['is_active' => false]);
        $empleado->user->update(['is_active' => false]);

        return response()->json(['message' => 'Empleado desactivado correctamente.']);
    }

    public function updateFaceDescriptor(Request $request, Empleado $empleado): JsonResponse
    {
        $request->validate([
            'face_descriptor' => 'required|array',
        ]);

        $empleado->update(['face_descriptor' => $request->face_descriptor]);

        return response()->json(['message' => 'Descriptor facial actualizado.']);
    }

    public function departamentos(): JsonResponse
    {
        $departamentos = Empleado::whereNotNull('departamento')
            ->distinct()
            ->orderBy('departamento')
            ->pluck('departamento');

        return response()->json(['data' => $departamentos]);
    }
}
