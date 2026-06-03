<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Visitante;
use App\Models\VisitanteImagen;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisitanteController extends Controller
{
    public function foto(int $id): JsonResponse
    {
        $img = VisitanteImagen::where('visitante_id', $id)
            ->where('tipo', 'entrada')
            ->first();

        if (!$img) {
            return response()->json(['foto' => null]);
        }

        return response()->json(['foto' => $img->foto_base64]);
    }

    public function index(Request $request): JsonResponse
    {
        $query = Visitante::with(['sede', 'imagenes' => fn ($q) => $q->where('tipo', 'entrada')])
            ->orderBy('hora_entrada', 'desc');

        if ($request->filled('sede_id')) {
            $query->where('sede_id', $request->sede_id);
        }

        if ($request->filled('desde')) {
            $query->whereDate('hora_entrada', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->whereDate('hora_entrada', '<=', $request->hasta);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('cedula', 'like', "%{$s}%")
                  ->orWhere('nombre', 'like', "%{$s}%");
            });
        }

        $visitantes = $query->paginate(50);

        // Adjuntar thumbnail de entrada directamente en el objeto
        $visitantes->getCollection()->transform(function ($v) {
            $img = $v->imagenes->first();
            $v->imagen_entrada = $img?->thumbnail_base64;
            unset($v->imagenes);
            return $v;
        });

        return response()->json($visitantes);
    }
}
