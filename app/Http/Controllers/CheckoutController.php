<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Orden;
use App\Models\OrdenDetalle;
use App\Models\Producto;

class CheckoutController extends Controller
{
    /**
     * 1) Revisión del carrito (pantalla de resumen antes de crear la orden)
     */
    public function show(Request $request)
    {
        $carrito = session('carrito', []);
        if (empty($carrito)) {
            return redirect()->route('carrito')->with('error', 'Tu carrito está vacío.');
        }

        $subtotal = 0;
        foreach ($carrito as $item) {
            $subtotal += ((float) $item['precio']) * ((int) $item['cantidad']);
        }
        $envio = 0;
        $total = $subtotal + $envio;

        return view('checkout.show', compact('carrito', 'subtotal', 'envio', 'total'));
    }

    /**
     * 2) Crea/actualiza la orden "pendiente" y redirige a la pantalla de pago (idempotente).
     */
    public function create(Request $request)
    {
        $carrito = session('carrito', []);
        if (empty($carrito)) {
            return redirect()->route('carrito')->with('error', 'Tu carrito está vacío.');
        }

        // Asegura que los productos existen y toma el precio actual desde BD
        $ids = array_keys($carrito);
        $productos = Producto::whereIn('id', $ids)->get()->keyBy('id');

        $subtotal = 0;
        foreach ($carrito as $prodId => $item) {
            if (!isset($productos[$prodId])) {
                return back()->with('error', "El producto #{$prodId} ya no está disponible.");
            }
            $precio   = (float) $productos[$prodId]->precio;
            $cantidad = (int) $item['cantidad'];
            $subtotal += $precio * $cantidad;
        }
        $envio = 0;
        $total = $subtotal + $envio;

        // Idempotencia: si hay una orden pendiente en sesión, la actualizamos.
        $ordenIdSesion = session('orden_pendiente_id');

        $orden = DB::transaction(function () use ($ordenIdSesion, $productos, $carrito, $subtotal, $envio, $total) {

            if ($ordenIdSesion) {
                $existente = Orden::where('id', $ordenIdSesion)
                    ->where('user_id', auth()->id())
                    ->where('estado', 'pendiente')
                    ->lockForUpdate()
                    ->first();

                if ($existente) {
                    // Actualiza totales
                    $existente->update([
                        'subtotal' => $subtotal,
                        'envio'    => $envio,
                        'total'    => $total,
                    ]);

                    // Refresca detalles con la info actual del carrito
                    OrdenDetalle::where('orden_id', $existente->id)->delete();

                    foreach ($carrito as $prodId => $item) {
                        $producto = $productos[$prodId];
                        $precio   = (float) $producto->precio;
                        $cantidad = (int) $item['cantidad'];

                        OrdenDetalle::create([
                            'orden_id'        => $existente->id,
                            'producto_id'     => $producto->id,
                            'cantidad'        => $cantidad,
                            'precio_unitario' => $precio,
                            'subtotal'        => $precio * $cantidad,
                        ]);
                    }

                    return $existente; // ← Reutilizamos la orden existente
                }
            }

            // No había orden pendiente válida: crear una nueva
            $nueva = Orden::create([
                'user_id'  => auth()->id(),
                'estado'   => 'pendiente',
                'subtotal' => $subtotal,
                'envio'    => $envio,
                'total'    => $total,
            ]);

            foreach ($carrito as $prodId => $item) {
                $producto = $productos[$prodId];
                $precio   = (float) $producto->precio;
                $cantidad = (int) $item['cantidad'];

                OrdenDetalle::create([
                    'orden_id'        => $nueva->id,
                    'producto_id'     => $producto->id,
                    'cantidad'        => $cantidad,
                    'precio_unitario' => $precio,
                    'subtotal'        => $precio * $cantidad,
                ]);
            }

            return $nueva;
        });

        // Guarda la orden en sesión para la pantalla de pago
        session(['orden_pendiente_id' => $orden->id]);

        // (No descontamos stock aquí; eso lo hará el Webhook si ePayco aprueba)
        return redirect()->route('checkout.pay')
            ->with('success', "Orden #{$orden->id} lista para pagar.");
    }

    /**
     * 3) Pantalla de pago: muestra la orden y configura el widget de ePayco
     */
    public function pay(Request $request)
    {
        $ordenId = session('orden_pendiente_id');
        if (!$ordenId) {
            return redirect()->route('checkout')->with('error', 'No hay una orden pendiente.');
        }

        // Traemos productos con imágenes (por si hay en BD)
        $orden = Orden::with(['detalles.producto.imagenes'])->findOrFail($ordenId);

        // 🔹 Adjunta imágenes desde el CARRITO (sesión) como fuente principal
        $carrito = session('carrito', []);
        foreach ($orden->detalles as $detalle) {
            $prodId = $detalle->producto_id;
            $imgsSesion = [];
            if (isset($carrito[$prodId])) {
                $p = $carrito[$prodId];
                // puede venir 'imagenes' (array) o 'imagen' (string)
                if (!empty($p['imagenes']) && is_array($p['imagenes'])) {
                    $imgsSesion = $p['imagenes'];
                } elseif (!empty($p['imagen'])) {
                    $imgsSesion = [$p['imagen']];
                }
            }
            // Asignamos un atributo dinámico que la vista usará primero
            $detalle->setAttribute('imagenes_sesion', $imgsSesion);
        }

        // Datos para la vista (el monto sale SIEMPRE del total de la orden)
        $epayco = [
            'public_key'   => config('services.epayco.public_key', env('EPAYCO_PUBLIC_KEY')),
            'test'         => filter_var(env('EPAYCO_TEST', true), FILTER_VALIDATE_BOOLEAN),
            'currency'     => env('EPAYCO_CURRENCY', 'COP'),
            'lang'         => env('EPAYCO_LANG', 'ES'),
            'response_url' => env('EPAYCO_RESPONSE_URL', config('app.url').'/checkout/response'),
            'confirm_url'  => env('EPAYCO_CONFIRMATION_URL', config('app.url').'/webhook/epayco'),
            'invoice'      => 'ORD-'.$orden->id,
            'amount'       => number_format((float) $orden->total, 2, '.', ''),
            'name'         => 'Compra Asvack #'.$orden->id,
            'description'  => 'Pago de orden #'.$orden->id,
            'extra1'       => (string) $orden->id, // para que el webhook identifique la orden
        ];

        return view('checkout.pay', compact('orden', 'epayco'));
    }

    /**
     * 4) Página de respuesta (solo informativa; el estado real lo fija el webhook)
     *    Limpia carrito/sesión si la orden ya quedó pagada.
     */
    public function response(Request $request)
    {
        // Datos que envía ePayco al regresar
        $data = $request->all();

        // Intentar encontrar la orden por x_extra1 o por la sesión
        $orden = null;
        if ($request->filled('x_extra1') && ctype_digit((string) $request->input('x_extra1'))) {
            $orden = Orden::with('detalles.producto')->find($request->input('x_extra1'));
        }
        if (!$orden && session()->has('orden_pendiente_id')) {
            $orden = Orden::with('detalles.producto')->find(session('orden_pendiente_id'));
        }

        // Si el webhook ya marcó la orden como pagada, limpiamos carrito y punteros
        if ($orden && $orden->estado === 'pagada') {
            $this->clearCartSession();
        }

        // (No cambiamos estados aquí)
        return view('checkout.response', compact('data', 'orden'));
    }

    /**
     * Helper: limpia completamente los datos de carrito/orden en sesión.
     */
    private function clearCartSession(): void
    {
        session()->forget(['carrito', 'cart_count', 'orden_pendiente_id']);
    }
}
