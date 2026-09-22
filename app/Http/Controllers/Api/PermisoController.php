<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permiso;
use App\Models\TipoPermiso;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermisoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Permiso::with('tipoPermiso')
            ->orderByRaw('COALESCE(fecha_inicio, fecha) DESC');

        if ($request->filled('user_id'))  $query->where('user_id', $request->user_id);
        if ($request->filled('estado'))   $query->where('estado', $request->estado);
        if ($request->filled('date_from')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('fecha_inicio', '>=', $request->date_from)
                  ->orWhere(function ($q2) use ($request) {
                      $q2->whereNull('fecha_inicio')->whereDate('fecha', '>=', $request->date_from);
                  });
            });
        }
        if ($request->filled('date_to')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('fecha_inicio', '<=', $request->date_to)
                  ->orWhere(function ($q2) use ($request) {
                      $q2->whereNull('fecha_inicio')->whereDate('fecha', '<=', $request->date_to);
                  });
            });
        }

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
            'user_id'         => 'required|integer',
            'tipo_permiso_id' => 'required|integer',
            'fecha_inicio'    => 'required|date',
            'fecha_fin'       => 'required|date|after:fecha_inicio',
            'motivo'          => 'nullable|string|max:500',
        ]);
        $data['estado'] = 'pendiente';

        $permiso = Permiso::create($data);
        $permiso->load('tipoPermiso');
        $permiso->user = User::find($data['user_id'], ['id','name','codigo_empleado']);
        return response()->json(['data' => $permiso], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $permiso = Permiso::findOrFail($id);
        $data = $request->validate([
            'user_id'         => 'sometimes|integer',
            'tipo_permiso_id' => 'sometimes|integer',
            'fecha_inicio'    => 'sometimes|date',
            'fecha_fin'       => 'sometimes|date|after:fecha_inicio',
            'motivo'          => 'nullable|string|max:500',
        ]);
        $permiso->update($data);
        $permiso->load('tipoPermiso');
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

    public function tiposPermiso(): JsonResponse
    {
        $tipos = TipoPermiso::where('is_active', true)->orderBy('nombre')->get();
        return response()->json($tipos);
    }
}
