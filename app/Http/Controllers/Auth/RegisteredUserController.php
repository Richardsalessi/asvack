<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Muestra la vista de registro.
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Maneja el registro de un nuevo usuario.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            // ✅ Añadimos 'min:8' y dejamos la política por defecto
            'password' => ['required', 'confirmed', 'min:8', Rules\Password::defaults()],
        ], [
            // ✅ Mensajes claros en español
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.required' => 'La contraseña es obligatoria.',
            'email.unique' => 'Este correo ya está registrado.',
        ]);

        // Crear usuario
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Asignar el rol "cliente" automáticamente (si existe)
        $clienteRole = Role::where('name', 'cliente')->first();
        if ($clienteRole) {
            $user->assignRole($clienteRole);
        }

        event(new Registered($user));

        // Iniciar sesión automáticamente después del registro
        Auth::login($user);

        return redirect()->route('home');
    }
}
