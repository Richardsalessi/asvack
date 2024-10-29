<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\Provider\CotizacionController;
use App\Models\Producto;
use Illuminate\Support\Facades\Route;

// Página principal
Route::get('/', function () {
    $productos = Producto::all();
    return view('welcome', compact('productos'));
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

// Rutas protegidas para perfil del usuario
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// CRUD de categorías, protegido por permisos de administrador
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

// CRUD de productos, protegido por permisos de administrador
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

// CRUD de productos para proveedores
Route::resource('provider/productos', ProductoController::class)
    ->middleware(['auth', 'can:provider-access'])
    ->names([
        'index' => 'provider.productos.index',
        'create' => 'provider.productos.create',
        'store' => 'provider.productos.store',
        'show' => 'provider.productos.show',
        'edit' => 'provider.productos.edit',
        'update' => 'provider.productos.update',
        'destroy' => 'provider.productos.destroy',
    ]);

// CRUD de cotizaciones para proveedores
Route::resource('provider/cotizaciones', CotizacionController::class)
    ->middleware(['auth', 'can:provider-access'])
    ->names([
        'index' => 'provider.cotizaciones.index',
        'create' => 'provider.cotizaciones.create',
        'store' => 'provider.cotizaciones.store',
        'show' => 'provider.cotizaciones.show',
        'edit' => 'provider.cotizaciones.edit',
        'update' => 'provider.cotizaciones.update',
        'destroy' => 'provider.cotizaciones.destroy',
    ]);

// Ruta para responder cotizaciones (con AJAX)
Route::post('provider/cotizaciones/{id}/responder', [CotizacionController::class, 'responder'])
    ->middleware(['auth', 'can:provider-access'])
    ->name('provider.cotizaciones.responder');

// Rutas para actualizar el estado de las cotizaciones (con AJAX)
Route::patch('provider/cotizaciones/{id}/en-proceso', [CotizacionController::class, 'marcarEnProceso'])
    ->middleware(['auth', 'can:provider-access'])
    ->name('provider.cotizaciones.en_proceso');

Route::patch('provider/cotizaciones/{id}/finalizado', [CotizacionController::class, 'marcarFinalizado'])
    ->middleware(['auth', 'can:provider-access'])
    ->name('provider.cotizaciones.finalizado');

// Autenticación
require __DIR__.'/auth.php';
