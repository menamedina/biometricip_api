<?php

namespace App\Http\Controllers;

use App\Models\LoginImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AdminLoginImageController extends Controller
{
    public function index(): View
    {
        $images = LoginImage::orderBy('orden')->get();

        return view('admin.login-images.index', compact('images'));
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'imagen'     => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'titulo'     => 'nullable|string|max:150',
            'orden'      => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $path = $request->file('imagen')->store('login-images', 'public');

        $image = LoginImage::create([
            'titulo'     => $request->input('titulo'),
            'imagen'     => $path,
            'orden'      => $request->input('orden', 0),
            'activo'     => true,
        ]);

        return response()->json([
            'message' => 'Imagen agregada correctamente',
            'image'   => [
                'id'         => $image->id,
                'titulo'     => $image->titulo,
                'url'        => asset('storage/' . $image->imagen),
                'orden'      => $image->orden,
                'activo'     => $image->activo,
            ],
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $image = LoginImage::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'titulo'     => 'nullable|string|max:150',
            'orden'      => 'nullable|integer|min:0',
            'activo'     => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $image->update([
            'titulo'     => $request->input('titulo', $image->titulo),
            'orden'      => $request->input('orden', $image->orden),
            'activo'     => $request->has('activo') ? $request->boolean('activo') : $image->activo,
        ]);

        return response()->json(['message' => 'Imagen actualizada']);
    }

    public function destroy(int $id): JsonResponse
    {
        $image = LoginImage::findOrFail($id);

        // Eliminar archivo físico
        if ($image->imagen) {
            Storage::disk('public')->delete($image->imagen);
        }

        $image->delete();

        return response()->json(['message' => 'Imagen eliminada']);
    }

    public function toggleActive(int $id): JsonResponse
    {
        $image = LoginImage::findOrFail($id);
        $image->update(['activo' => !$image->activo]);

        return response()->json([
            'message' => $image->activo ? 'Imagen activada' : 'Imagen desactivada',
            'activo'  => $image->activo,
        ]);
    }
}
