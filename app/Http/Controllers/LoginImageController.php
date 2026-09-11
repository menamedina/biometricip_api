<?php

namespace App\Http\Controllers;

use App\Models\LoginImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LoginImageController extends Controller
{
    /**
     * API pública — devuelve las URLs de imágenes activas para el login.
     * Solo lectura, no expone datos sensibles.
     */
    public function index(Request $request): JsonResponse
    {
        $images = LoginImage::forLogin()
            ->map(fn ($img) => [
                'titulo' => $img->titulo,
                'url'    => $img->imagen
                    ? asset('storage/' . $img->imagen)
                    : null,
            ])
            ->filter(fn ($img) => $img['url'] !== null)
            ->values();

        return response()->json(['data' => $images]);
    }
}
