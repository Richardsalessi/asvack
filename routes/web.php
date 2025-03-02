<?php

use App\Http\Controllers\Admin\ProveedorController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\Provider\ProveedorProductoController;
use App\Http\Controllers\CarritoController;
use App\Models\Producto;
use Illuminate\Support\Facades\Route;

// Página principal accesible para todos
Route::get('/', function () {
    $productosAleatorios = Producto::inRandomOrder()->take(6)->with('imagenes', 'categoria', 'creador')->get();
    return view('welcome', compact('productosAleatorios'));
})->name('home');

// Página de catálogo para usuarios anónimos y autenticados
Route::get('/catalogo', [ProductoController::class, 'catalogo'])->name('catalogo');

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
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// CRUD de categorías, solo para administrador
Route::middleware(['auth', 'can:admin-access'])->group(function () {
    Route::resource('admin/categorias', CategoriaController::class)
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
        ->names([
            'index' => 'admin.productos.index',
            'create' => 'admin.productos.create',
            'store' => 'admin.productos.store',
            'show' => 'admin.productos.show',
            'edit' => 'admin.productos.edit',
            'update' => 'admin.productos.update',
            'destroy' => 'admin.productos.destroy',
        ]);

    // CRUD de proveedores, solo para administrador
    Route::resource('admin/proveedores', ProveedorController::class)
        ->except(['show'])
        ->parameters(['proveedores' => 'proveedor'])
        ->names([
            'index' => 'admin.proveedores.index',
            'create' => 'admin.proveedores.create',
            'store' => 'admin.proveedores.store',
            'edit' => 'admin.proveedores.edit',
            'update' => 'admin.proveedores.update',
            'destroy' => 'admin.proveedores.destroy',
        ]);
});

// Rutas protegidas para el proveedor
Route::middleware(['auth', 'can:provider-access'])->prefix('provider')->name('provider.')->group(function () {
    // Ruta para ver y gestionar categorías por el proveedor
    Route::get('categorias', [CategoriaController::class, 'index'])->name('categorias.index');

    // CRUD de productos solo para el proveedor autenticado
    Route::resource('productos', ProveedorProductoController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
        ->names([
            'index' => 'productos.index',
            'create' => 'productos.create',
            'store' => 'productos.store',
            'edit' => 'productos.edit',
            'update' => 'productos.update',
            'destroy' => 'productos.destroy',
        ]);
});

// Rutas de autenticación
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

// Rutas del carrito
Route::middleware('auth')->group(function () {
    // Ruta para ver el carrito
    Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito');

    // Ruta para agregar productos al carrito
    Route::post('/carrito/agregar/{id}', [CarritoController::class, 'agregar'])->name('carrito.agregar');

    // Ruta para eliminar productos del carrito
    Route::delete('/carrito/eliminar/{id}', [CarritoController::class, 'eliminar'])->name('carrito.eliminar');
});

// Ruta para el checkout
Route::middleware(['auth'])->get('/checkout', function () {
    return view('checkout');  // Crea una vista para el checkout
})->name('checkout');

// Cargar rutas de autenticación predeterminadas de Laravel
require __DIR__.'/auth.php';
