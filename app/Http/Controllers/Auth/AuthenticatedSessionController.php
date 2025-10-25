<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        // Autentica con Fortify/Breeze
        $request->authenticate();

        // Regenera ID de sesión para prevenir fijación de sesión
        $request->session()->regenerate();

        // ⬇️ Fuerza un GET limpio (Safari-friendly)
        return to_route('home', [], 303);
        // Alternativa:
        // return redirect()->to(route('home'))->setStatusCode(303);
    }

    public function destroy(Request $request): RedirectResponse
    {
        // Cierra sesión
        Auth::guard('web')->logout();

        // Invalida y regenera token CSRF
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // ⬇️ También 303 al salir
        return to_route('home', [], 303);
        // Alternativa:
        // return redirect()->to(route('home'))->setStatusCode(303);
    }
}
