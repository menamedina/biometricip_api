<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminApiController extends Controller
{
    public function index(): View
    {
        $this->authorize('api.ver');

        $user = Auth::user();

        return view('admin.api.index', compact('user'));
    }

    public function regenerateToken(): JsonResponse
    {
        $this->authorize('api.gestionar');

        $user = Auth::user();

        $token = Str::random(64);

        $user->update(['api_token' => $token]);

        return response()->json(['api_token' => $token]);
    }

    public function updateScope(Request $request): JsonResponse
    {
        $this->authorize('api.gestionar');

        $request->validate([
            'scope_registros' => 'required|in:empresa,equipo,personal',
        ]);

        $user = Auth::user();

        $user->update(['scope_registros' => $request->scope_registros]);

        return response()->json(['message' => 'Visibilidad actualizada.', 'scope_registros' => $request->scope_registros]);
    }
}
