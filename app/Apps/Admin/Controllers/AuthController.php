<?php

declare(strict_types=1);

namespace App\Apps\Admin\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends BaseAdminController
{
    /**
     * Show the login form.
     */
    public function showLogin(): View
    {
        return view('admin::auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $email = strtolower($credentials['email']);
        $ip = $request->ip();
        $throttleKey = $email.'|'.$ip;

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Muitas tentativas de login. Tente novamente em {$seconds} segundos.",
            ]);
        }

        if (Auth::attempt($credentials)) {
            RateLimiter::clear($throttleKey);

            $request->session()->regenerate();

            return redirect()
                ->route('admin.dashboard.index')
                ->with('success', 'Bem-vindo de volta!');
        }

        RateLimiter::hit($throttleKey, 60);

        throw ValidationException::withMessages([
            'email' => 'As credenciais fornecidas não correspondem aos nossos registros.',
        ]);
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
