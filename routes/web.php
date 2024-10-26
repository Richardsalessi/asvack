<?php

use App\Http\Controllers\ProfileController;
use App\Models\Producto;
use Illuminate\Support\Facades\Route;

// Página principal
Route::get('/', function () {
    $productos = Producto::all(); // Obtén todos los productos
    return view('welcome', compact('productos'));
})->name('home');

// Redirección al home después de iniciar sesión
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        // Redirigir a la página principal
        return redirect()->route('home');
    })->name('dashboard');

    // Rutas para cada dashboard con permisos específicos
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->middleware('can:admin-access')->name('admin.dashboard');

    Route::get('/provider/dashboard', function () {
        return view('provider.dashboard');
    })->middleware('can:provider-access')->name('provider.dashboard');

    Route::get('/client/dashboard', function () {
        return view('client.dashboard');
    })->middleware('can:client-access')->name('client.dashboard');
});

// Rutas protegidas por autenticación para la edición del perfil del usuario
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
