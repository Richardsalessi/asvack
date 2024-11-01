<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Página principal accesible para todos, tanto autenticados como anónimos
Route::get('/', function () {
    $productosAleatorios = Producto::inRandomOrder()->take(6)->with('imagenes', 'categoria', 'creador')->get();
    return view('welcome', compact('productosAleatorios'));
})->name('home');

// Página de catálogo para usuarios anónimos y autenticados
Route::get('/catalogo', function () {
    $productos = Producto::with('imagenes', 'categoria', 'creador')->get();
    $categorias = Categoria::all();
    $proveedores = User::role('provider')->get();
    return view('catalogo', compact('productos', 'categorias', 'proveedores'));
})->name('catalogo');

// Redirección al `home` después de iniciar sesión
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('home');
    })->name('dashboard');

    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->middleware('can:admin-access')->name('admin.dashboard');

    Route::get('/provider/dashboard', function () {
        return view('provider.dashboard');
    })->middleware('can:provider-access')->name('provider.dashboard');
});

// Rutas protegidas para perfil de usuario
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// CRUD de categorías, solo para administrador
Route::resource('admin/categorias', CategoriaController::class)
    ->middleware(['auth', 'can:admin-access'])
    ->names([
        'index' => 'admin.categorias.index',
        'create' => 'admin.categorias.create',
        'store' => 'admin.categorias.store',
        'show' => 'admin.categorias.show',
        'edit' => 'admin.categorias.edit',
        'update' => 'admin.categorias.update',
        'destroy' => 'admin.categorias.destroy',
    ]);

// CRUD de productos, solo para administrador
Route::resource('admin/productos', ProductoController::class)
    ->middleware(['auth', 'can:admin-access'])
    ->names([
        'index' => 'admin.productos.index',
        'create' => 'admin.productos.create',
        'store' => 'admin.productos.store',
        'show' => 'admin.productos.show',
        'edit' => 'admin.productos.edit',
        'update' => 'admin.productos.update',
        'destroy' => 'admin.productos.destroy',
    ]);

// Autenticación
require __DIR__.'/auth.php';
