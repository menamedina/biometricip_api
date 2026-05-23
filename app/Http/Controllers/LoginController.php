<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLogin(): View
    {
        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        try {
            $user = User::where('email', $credentials['email'])->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return back()->withErrors(['email' => 'Credenciales incorrectas.'])->withInput();
            }

            if ($user->role !== 'admin') {
                return back()->withErrors(['email' => 'Solo los administradores pueden acceder.'])->withInput();
            }

            if (!$user->is_active) {
                return back()->withErrors(['email' => 'Tu cuenta ha sido desactivada.'])->withInput();
            }

            Auth::login($user);
            $request->session()->regenerate();

            $token = $user->createToken('web-token')->plainTextToken;
            session(['api_token'  => $token]);
            session(['empresa_id' => $user->empresa_id]);

            return redirect()->intended(route('admin.dashboard'));

        } catch (\Throwable $e) {
            Log::error('Admin login exception', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            return back()->withErrors(['email' => 'Error interno: ' . $e->getMessage()])->withInput();
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->user()->tokens()->delete();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login.show');
    }
}
