<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Orden;

class ClearCartIfOrderSettled
{
    public function handle(Request $request, Closure $next)
    {
        if (session()->has('orden_pendiente_id')) {
            $ordenId = session('orden_pendiente_id');

            $orden = Orden::find($ordenId);
            if ($orden && $orden->user_id === optional($request->user())->id) {
                if (in_array($orden->estado, ['pagada', 'rechazada', 'cancelada'], true)) {
                    if ($orden->estado === 'pagada') {
                        // Compra exitosa: vaciar carrito y badge
                        session()->forget(['carrito', 'cart_count']);
                    }
                    // En cualquier estado no pendiente, ya no apuntes a esta orden
                    session()->forget('orden_pendiente_id');
                }
            } else {
                // Si la orden no existe o no corresponde al usuario, limpia el puntero
                session()->forget('orden_pendiente_id');
            }
        }

        return $next($request);
    }
}
