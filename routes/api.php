<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| API Routes (sin sesión/CSRF, con prefijo /api)
|--------------------------------------------------------------------------
*/

// Ping para probar que la API está arriba
Route::get('/ping', fn () => response()->json(['message' => 'API online ✅']))
    ->name('api.ping');

// Webhook de ePayco (acepta GET y POST) + limitador básico
Route::match(['GET', 'POST'], '/webhook/epayco', [WebhookController::class, 'handle'])
    ->middleware('throttle:120,1')
    ->name('webhook.epayco');
