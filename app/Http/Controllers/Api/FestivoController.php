<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Festivo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FestivoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Festivo::orderBy('fecha');
        if ($request->filled('year')) {
            $query->whereYear('fecha', $request->year);
        }
        return response()->json(['data' => $query->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fecha'     => 'required|date|unique:tenant.tbl_festivos,fecha',
            'nombre'    => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] ??= true;

        $festivo = Festivo::create($data);
        return response()->json(['data' => $festivo], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $festivo = Festivo::findOrFail($id);
        $data = $request->validate([
            'fecha'     => 'sometimes|date|unique:tenant.tbl_festivos,fecha,' . $id,
            'nombre'    => 'sometimes|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);
        $festivo->update($data);
        return response()->json(['data' => $festivo]);
    }

    public function destroy(int $id): JsonResponse
    {
        Festivo::findOrFail($id)->update(['is_active' => false]);
        return response()->json(['message' => 'Festivo desactivado.']);
    }
}
