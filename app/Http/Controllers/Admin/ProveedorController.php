<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ProveedorController extends Controller
{
    public function index()
    {
        $proveedores = User::role('provider')->get();
        return view('admin.proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        return view('admin.proveedores.create');
    }

    public function store(Request $request)
    {
        // Validación de datos
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            // Crear el usuario proveedor
            $proveedor = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Asignar el rol 'provider' al usuario
            if ($proveedor) {
                $proveedor->assignRole('provider');
            }

            // Log para confirmar que el usuario y el rol se asignaron
            Log::info("Proveedor creado: {$proveedor->name} con rol de proveedor");

            return redirect()->route('admin.proveedores.index')->with('success', 'Proveedor creado con éxito.');
        } catch (\Exception $e) {
            // Log de error en caso de fallo
            Log::error("Error al crear proveedor: " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al crear el proveedor.']);
        }
    }

    public function edit(User $proveedor)
    {
        return view('admin.proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, User $proveedor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $proveedor->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $proveedor->name = $request->name;
        $proveedor->email = $request->email;

        if ($request->filled('password')) {
            $proveedor->password = Hash::make($request->password);
        }

        $proveedor->save();

        return redirect()->route('admin.proveedores.index')->with('success', 'Proveedor actualizado con éxito.');
    }

    public function destroy(User $proveedor)
    {
        $proveedor->delete();

        return redirect()->route('admin.proveedores.index')->with('success', 'Proveedor eliminado con éxito.');
    }
}
