<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login', ['admin' => false]);
    }

    public function createAdmin(): View
    {
        return view('auth.login', ['admin' => true]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        return $this->attempt($request, false);
    }

    public function storeAdmin(LoginRequest $request): RedirectResponse
    {
        return $this->attempt($request, true);
    }

    // Cada acceso solo admite su tipo de cuenta.
    private function attempt(LoginRequest $request, bool $admin): RedirectResponse
    {
        $request->authenticate();

        if ((bool) $request->user()->is_admin !== $admin) {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'email' => $admin
                    ? 'Esta cuenta no tiene permisos de administración.'
                    : 'Las cuentas de administración entran por el acceso de administradores.',
            ]);
        }

        $request->session()->regenerate();

        return $admin
            ? redirect()->route('admin')
            : redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}