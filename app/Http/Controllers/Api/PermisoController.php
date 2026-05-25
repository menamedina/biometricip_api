<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permiso;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermisoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Permiso::orderBy('fecha', 'desc');

        if ($request->filled('user_id'))  $query->where('user_id', $request->user_id);
        if ($request->filled('estado'))   $query->where('estado', $request->estado);
        if ($request->filled('date_from')) $query->whereDate('fecha', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('fecha', '<=', $request->date_to);

        $permisos = $query->paginate($request->per_page ?? 30);

        // Adjuntar datos del usuario desde BD central
        $userIds = $permisos->pluck('user_id')->unique()->toArray();
        $users   = User::whereIn('id', $userIds)->get(['id','name','codigo_empleado'])->keyBy('id');

        $permisos->getCollection()->transform(function ($p) use ($users) {
            $p->user = $users[$p->user_id] ?? null;
            return $p;
        });

        return response()->json($permisos);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id'       => 'required|integer',
            'fecha'         => 'required|date',
            'tipo'          => 'required|in:salida_temprana,llegada_tarde,dia_completo,horas',
            'horas_permiso' => 'required|numeric|min:0|max:24',
            'motivo'        => 'nullable|string|max:500',
        ]);
        $data['estado'] = 'pendiente';

        $permiso = Permiso::create($data);
        $permiso->user = User::find($data['user_id'], ['id','name','codigo_empleado']);
        return response()->json(['data' => $permiso], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $permiso = Permiso::findOrFail($id);
        $data = $request->validate([
            'user_id'       => 'sometimes|integer',
            'fecha'         => 'sometimes|date',
            'tipo'          => 'sometimes|in:salida_temprana,llegada_tarde,dia_completo,horas',
            'horas_permiso' => 'sometimes|numeric|min:0|max:24',
            'motivo'        => 'nullable|string|max:500',
        ]);
        $permiso->update($data);
        return response()->json(['data' => $permiso]);
    }

    public function aprobar(Request $request, int $id): JsonResponse
    {
        $permiso = Permiso::findOrFail($id);
        $permiso->update([
            'estado'       => 'aprobado',
            'aprobado_por' => $request->user()->id,
        ]);
        return response()->json(['message' => 'Permiso aprobado.', 'data' => $permiso]);
    }

    public function rechazar(Request $request, int $id): JsonResponse
    {
        $permiso = Permiso::findOrFail($id);
        $permiso->update([
            'estado'       => 'rechazado',
            'aprobado_por' => $request->user()->id,
        ]);
        return response()->json(['message' => 'Permiso rechazado.', 'data' => $permiso]);
    }

    public function destroy(int $id): JsonResponse
    {
        Permiso::findOrFail($id)->delete();
        return response()->json(['message' => 'Permiso eliminado.']);
    }
}
