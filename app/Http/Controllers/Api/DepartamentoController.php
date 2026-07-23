<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use App\Models\Departamento;
use App\Models\Horario;
use App\Models\Sede;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartamentoController extends Controller
{
    // ── Catálogos combinados (deptos + cargos + horarios en una sola llamada) ─
    public function catalogos(): JsonResponse
    {
        try {
            return response()->json([
                'departamentos' => Departamento::orderBy('nombre')->get(),
                'cargos'        => Cargo::orderBy('nombre')->get(),
                'horarios'      => Horario::where('is_active', true)->orderBy('nombre')->get(),
                'sedes'         => Sede::where('is_active', true)->orderBy('nombre')->get(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'departamentos' => [],
                'cargos'        => [],
                'horarios'      => [],
                'sedes'         => [],
            ]);
        }
    }

    // ── Departamentos ────────────────────────────────────────────────────────

    public function index(): JsonResponse
    {
        $deptos = Departamento::orderBy('nombre')->get();
        return response()->json(['data' => $deptos]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:100|unique:tenant.tbl_departamentos,nombre',
            'descripcion' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ]);
        $data['is_active'] ??= true;

        $depto = Departamento::create($data);
        return response()->json(['data' => $depto], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $depto = Departamento::findOrFail($id);
        $data  = $request->validate([
            'nombre'      => 'sometimes|string|max:100|unique:tenant.tbl_departamentos,nombre,' . $id,
            'descripcion' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ]);
        $depto->update($data);
        return response()->json(['data' => $depto]);
    }

    public function destroy(int $id): JsonResponse
    {
        Departamento::findOrFail($id)->delete();
        return response()->json(['message' => 'Departamento eliminado.']);
    }

    // ── Cargos ───────────────────────────────────────────────────────────────

    public function cargos(): JsonResponse
    {
        $cargos = Cargo::orderBy('nombre')->get();
        return response()->json(['data' => $cargos]);
    }

    public function storeCargo(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ]);
        $data['is_active'] ??= true;

        $cargo = Cargo::create($data);
        return response()->json(['data' => $cargo], 201);
    }

    public function updateCargo(Request $request, int $id): JsonResponse
    {
        $cargo = Cargo::findOrFail($id);
        $data  = $request->validate([
            'nombre'      => 'sometimes|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ]);
        $cargo->update($data);
        return response()->json(['data' => $cargo]);
    }

    public function destroyCargo(int $id): JsonResponse
    {
        Cargo::findOrFail($id)->delete();
        return response()->json(['message' => 'Cargo eliminado.']);
    }
}
