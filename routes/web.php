<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CompraController;
use App\Models\Producto;
use Illuminate\Support\Facades\Route;

// Página principal con productos aleatorios
Route::get('/', function () {
    $productosAleatorios = Producto::inRandomOrder()->take(6)->with('imagenes', 'categoria', 'creador')->get();
    return view('welcome', compact('productosAleatorios')); // Pasar 'productosAleatorios' a la vista
})->name('home');

// Redirección al home después de iniciar sesión
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

// CRUD de categorías, protegido por autenticación y permiso de administrador
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

// CRUD de productos, protegido por autenticación y permiso de administrador
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

// Rutas para el proceso de compra, solo accesible para clientes autenticados
Route::middleware(['auth', 'can:client-access'])->group(function () {
    Route::get('/comprar/{id}', [CompraController::class, 'showFormulario'])->name('compra.formulario');
    Route::post('/comprar/procesar/{id}', [CompraController::class, 'procesarCompra'])->name('compra.procesar');
});

// Autenticación
require __DIR__.'/auth.php';
