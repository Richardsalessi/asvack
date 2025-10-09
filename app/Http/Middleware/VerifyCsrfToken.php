<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Rutas excluidas de la verificación CSRF (webhooks, etc.)
     */
    protected $except = [
        'webhook/*',         // o 'webhook/epayco' si prefieres solo esa
    ];
}
