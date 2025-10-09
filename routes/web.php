<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CatalogoController;
use App\Models\Producto;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\OrdenController;

// Página principal accesible para todos
Route::get('/', function () {
    $productosAleatorios = Producto::inRandomOrder()->take(6)->with('imagenes', 'categoria')->get();
    return view('welcome', compact('productosAleatorios'));
})->name('home');

// Redirección al `home` después de iniciar sesión
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('home');
    })->name('dashboard');

    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->middleware('can:admin-access')->name('admin.dashboard');
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

    // Ruta para eliminar unidad de un producto del carrito
    Route::post('/carrito/quitar/{id}', [CarritoController::class, 'quitar'])->name('carrito.quitar');

});

// Ruta para catalogo
Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo');
// Ruta AJAX para filtrar productos dinámicamente
Route::get('/api/catalogo/filtrar', [CatalogoController::class, 'filtrar'])->name('catalogo.filtrar');

//___________________________________________________________________________________________
// Rutas protegidas para el cliente autenticado
Route::middleware('auth')->group(function () {
    // 1) Checkout (revisión final + crear orden y redirigir a ePayco)
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout'); // <-- nombre viejo
    Route::post('/checkout/create', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::get('/checkout/pay', [CheckoutController::class, 'pay'])->name('checkout.pay');
    Route::post('/checkout/pay/save', [CheckoutController::class, 'saveShipping'])->name('checkout.pay.save');



    // 2) Historial del cliente
    Route::get('/mis-compras', [OrdenController::class, 'index'])->name('ordenes.index');
    Route::get('/mis-compras/{orden}', [OrdenController::class, 'show'])->name('ordenes.show');
});

// 4) Vista admin de ventas (luego le pones tu middleware de rol)
Route::get('/admin/ventas', [OrdenController::class, 'admin'])->name('ordenes.admin');


// Webhook ePayco
Route::post('/webhook/epayco', [WebhookController::class, 'handle'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
    ->name('webhook.epayco');

Route::get('/checkout/response', [CheckoutController::class, 'response'])
    ->name('checkout.response');    

// Cargar rutas de autenticación predeterminadas de Laravel
require __DIR__.'/auth.php';
