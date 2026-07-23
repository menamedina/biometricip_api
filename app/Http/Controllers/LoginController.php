<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
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

            if (!$user->is_active) {
                return back()->withErrors(['email' => 'Tu cuenta ha sido desactivada.'])->withInput();
            }

            $remember = $request->boolean('remember');
            Auth::login($user, $remember);
            $request->session()->regenerate();

            $user->update(['last_login_at' => now()]);

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

    // ── Recuperación de contraseña ──────────────────────────────────────────

    public function showForgotPassword(): View
    {
        return view('admin.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        ResetPassword::createUrlUsing(function (User $user, string $token) {
            return route('admin.password.reset', ['token' => $token, 'email' => $user->email]);
        });

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'Te enviamos un enlace para restablecer tu contraseña. Revisa tu correo.');
        }

        return back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(string $token, Request $request): View
    {
        return view('admin.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token'                 => 'required',
            'email'                 => 'required|email',
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ], [
            'password.min'              => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'        => 'Las contraseñas no coinciden.',
            'password_confirmation.required' => 'Confirma tu nueva contraseña.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])
                     ->setRememberToken(Str::random(60));
                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('admin.login.show')
                ->with('status', 'Contraseña restablecida exitosamente. Ya puedes iniciar sesión.');
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
