<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\Admin\EnvioController;
use App\Models\Producto;
use App\Http\Controllers\Admin\TarifaEnvioController;

/*
|--------------------------------------------------------------------------
| Web Routes (con sesión/CSRF)
|--------------------------------------------------------------------------
| Estas rutas cargan vistas Blade, usan sesión y autenticación.
| ⚠️ El webhook de ePayco NO VA AQUÍ (está en routes/api.php).
|--------------------------------------------------------------------------
*/

// ============================
// 🏠 Home (público)
// ============================
Route::get('/', function () {
    $productosAleatorios = Producto::with('imagenes', 'categoria')
        ->inRandomOrder()
        ->take(6)
        ->get();

    return view('welcome', compact('productosAleatorios'));
})->name('home');

// ============================
// 👤 Dashboard
// ============================
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn () => redirect()->route('home'))->name('dashboard');

    Route::get('/admin/dashboard', [OrdenController::class, 'adminDashboard'])
        ->middleware('can:admin-access')
        ->name('admin.dashboard');
});

// ============================
// 👥 Perfil (usuario autenticado)
// ============================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ============================
// 🛠️ Admin: Categorías y Productos
// ============================
Route::middleware(['auth', 'can:admin-access'])->group(function () {
    // Categorías
    Route::resource('admin/categorias', CategoriaController::class)->names([
        'index'   => 'admin.categorias.index',
        'create'  => 'admin.categorias.create',
        'store'   => 'admin.categorias.store',
        'show'    => 'admin.categorias.show',
        'edit'    => 'admin.categorias.edit',
        'update'  => 'admin.categorias.update',
        'destroy' => 'admin.categorias.destroy',
    ]);

    // Productos
    Route::resource('admin/productos', ProductoController::class)->names([
        'index'   => 'admin.productos.index',
        'create'  => 'admin.productos.create',
        'store'   => 'admin.productos.store',
        'show'    => 'admin.productos.show',
        'edit'    => 'admin.productos.edit',
        'update'  => 'admin.productos.update',
        'destroy' => 'admin.productos.destroy',
    ]);

    // Admin para tarifas
    Route::middleware(['auth', 'can:admin-access'])->group(function () {
        Route::resource('admin/tarifas-envio', TarifaEnvioController::class)->names([
            'index'   => 'admin.tarifas.index',
            'create'  => 'admin.tarifas.create',
            'store'   => 'admin.tarifas.store',
            'edit'    => 'admin.tarifas.edit',
            'update'  => 'admin.tarifas.update',
            'destroy' => 'admin.tarifas.destroy',
        ])->except(['show']);
    });

    // Ventas
    Route::get('/admin/ventas', [OrdenController::class, 'admin'])->name('ordenes.admin');

    // Envíos
    Route::prefix('admin')->group(function () {
        Route::post('envios/{orden}/configurar', [EnvioController::class, 'configurar'])->name('admin.envios.configurar');
        Route::post('envios/{orden}/estado', [EnvioController::class, 'cambiarEstado'])->name('admin.envios.estado');
    });
});

// ============================
// 🔐 Registro y login
// ============================
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

// ============================
// 🛒 Catálogo y Carrito
// ============================

// Catálogo público
Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo');
Route::get('/catalogo/filtrar', [CatalogoController::class, 'filtrar'])->name('catalogo.filtrar');

// Carrito (usuario autenticado)
Route::middleware('auth')->group(function () {
    Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito');
    Route::post('/carrito/agregar/{id}', [CarritoController::class, 'agregar'])->name('carrito.agregar');
    Route::delete('/carrito/eliminar/{id}', [CarritoController::class, 'eliminar'])->name('carrito.eliminar');
    Route::post('/carrito/quitar/{id}', [CarritoController::class, 'quitar'])->name('carrito.quitar');
});

// ============================
// 💳 Checkout / Pagos ePayco
// ============================
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
    Route::post('/checkout/create', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::get('/checkout/pay', [CheckoutController::class, 'pay'])->name('checkout.pay');
    Route::post('/checkout/pay/save', [CheckoutController::class, 'saveShipping'])->name('checkout.pay.save');

    // NUEVO: cotización de envío en vivo (AJAX)
    Route::post('/checkout/shipping/quote', [CheckoutController::class, 'quoteShipping'])
        ->name('checkout.shipping.quote');

    // Historial del cliente
    Route::get('/mis-compras', [OrdenController::class, 'index'])->name('ordenes.index');
    Route::get('/mis-compras/{orden}', [OrdenController::class, 'show'])->name('ordenes.show');
});

// Página de respuesta del checkout (informativa)
Route::get('/checkout/response', [CheckoutController::class, 'response'])
    ->name('checkout.response');

// ============================
// 📦 Auth por defecto de Laravel Breeze/Fortify
// ============================
require __DIR__.'/auth.php';
