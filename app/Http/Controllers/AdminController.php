<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function showLogin(): View
    {
        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

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

        // Store token in session for JS API calls
        $token = $user->createToken('web-token')->plainTextToken;
        session(['api_token' => $token]);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->user()->tokens()->delete();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login.show');
    }

    public function dashboard(): View
    {
        return view('admin.dashboard');
    }

    public function sedesIndex(): View
    {
        return view('admin.sedes.index');
    }

    public function empleadosIndex(): View
    {
        return view('admin.empleados.index');
    }

    public function attendanceIndex(): View
    {
        return view('admin.attendance.index');
    }
}
