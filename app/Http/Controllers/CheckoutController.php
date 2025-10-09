<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema; // <-- IMPORTANTE
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
        $envio = $this->shippingCost($subtotal);
        $total = $subtotal + $envio;

        return view('checkout.show', compact('carrito', 'subtotal', 'envio', 'total'));
    }

    /**
     * 2) Crea/actualiza la orden "pendiente" (idempotente) y redirige a la pantalla de pago.
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

        $envio = $this->shippingCost($subtotal);
        $total = $subtotal + $envio;

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

                    // Refresca detalles según carrito actual
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

                    return $existente; // reutilizamos la misma orden
                }
            }

            // Crear nueva orden
            $nueva = Orden::create([
                'user_id'       => auth()->id(),
                'estado'        => 'pendiente',
                'subtotal'      => $subtotal,
                'envio'         => $envio,
                'total'         => $total,
                'datos_envio'   => null,
                'intentos_pago' => 0, // inicia contador
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

        return redirect()->route('checkout.pay')
            ->with('success', "Orden #{$orden->id} lista para pagar.");
    }

    /**
     * 3) Pantalla de pago: muestra la orden y configura el widget de ePayco.
     */
    public function pay(Request $request)
    {
        $ordenId = session('orden_pendiente_id');
        if (!$ordenId) {
            return redirect()->route('checkout')->with('error', 'No hay una orden pendiente.');
        }

        // Traer orden con productos e imágenes
        $orden = Orden::with(['detalles.producto.imagenes'])->findOrFail($ordenId);

        // Adjunta imágenes desde el CARRITO (sesión) como fuente principal
        $carrito = session('carrito', []);
        foreach ($orden->detalles as $detalle) {
            $prodId = $detalle->producto_id;
            $imgsSesion = [];
            if (isset($carrito[$prodId])) {
                $p = $carrito[$prodId];
                if (!empty($p['imagenes']) && is_array($p['imagenes'])) {
                    $imgsSesion = $p['imagenes'];
                } elseif (!empty($p['imagen'])) {
                    $imgsSesion = [$p['imagen']];
                }
            }
            $detalle->setAttribute('imagenes_sesion', $imgsSesion);
        }

        /**
         * === Clave para evitar E035 (referencia duplicada) ===
         * Incrementamos el contador de intentos y generamos un 'invoice' único
         * por intento. Persistimos en ref_epayco (y ultimo_invoice si existe).
         */
        $orden->intentos_pago = (int)($orden->intentos_pago ?? 0) + 1;
        $invoice = $this->generarInvoiceUnico($orden->id, $orden->intentos_pago);
        $orden->ref_epayco = $invoice;
        if (Schema::hasColumn('ordenes', 'ultimo_invoice')) { // <-- CORREGIDO
            $orden->ultimo_invoice = $invoice;
        }
        $orden->save();

        // Datos para el widget (el monto sale del total de la orden)
        $epayco = [
            'public_key'   => config('services.epayco.public_key', env('EPAYCO_PUBLIC_KEY')),
            'test'         => filter_var(env('EPAYCO_TEST', true), FILTER_VALIDATE_BOOLEAN),
            'currency'     => env('EPAYCO_CURRENCY', 'COP'),
            'lang'         => env('EPAYCO_LANG', 'ES'),

            // usa rutas públicas/https (en local puedes usar ngrok)
            'response_url' => env('EPAYCO_RESPONSE_URL', route('checkout.response')),
            'confirm_url'  => env('EPAYCO_CONFIRMATION_URL', route('webhook.epayco')),

            'invoice'      => $invoice, // <- ÚNICO por intento
            'amount'       => number_format((float) $orden->total, 2, '.', ''),
            'name'         => 'Compra Asvack #'.$orden->id,
            'description'  => 'Pago de orden #'.$orden->id,

            // Para que el webhook identifique la orden sin importar el invoice
            'extra1'       => (string) $orden->id,
        ];

        // Datos de envío/billing ya guardados (no autocompletamos)
        $datosEnvio = (array) ($orden->datos_envio ?? []);
        $shippingOK = (bool) data_get($orden->datos_envio ?? [], 'validated', false);

        return view('checkout.pay', compact('orden', 'epayco', 'datosEnvio', 'shippingOK'));
    }

    /**
     * 3.1) Guarda datos de facturación/envío, valida y recalcula totales.
     */
    public function saveShipping(Request $request)
    {
        $ordenId = session('orden_pendiente_id');
        if (!$ordenId) {
            return redirect()->route('checkout')->with('error', 'No hay una orden pendiente.');
        }

        $data = $request->validate([
            // Facturación
            'facturacion.nombre'       => 'required|string|max:120',
            'facturacion.apellidos'    => 'required|string|max:120',
            'facturacion.cedula'       => 'required|string|max:40',
            'facturacion.telefono'     => 'required|string|max:40',
            'facturacion.email'        => 'nullable|email|max:160',
            'facturacion.direccion'    => 'required|string|max:255',
            'facturacion.ciudad'       => 'required|string|max:120',
            'facturacion.departamento' => 'required|string|max:120',
            // Envío
            'envio.nombre'             => 'required|string|max:120',
            'envio.apellidos'          => 'required|string|max:120',
            'envio.direccion'          => 'required|string|max:255',
            'envio.ciudad'             => 'required|string|max:120',
            'envio.departamento'       => 'required|string|max:120',
            'envio.notas'              => 'nullable|string|max:500',
            // Términos
            'acepta_terminos'          => 'accepted',
        ], [
            'acepta_terminos.accepted' => 'Debes aceptar los términos y condiciones para continuar.',
        ]);

        $orden = Orden::with('detalles')->where('id', $ordenId)
            ->where('user_id', auth()->id())
            ->where('estado', 'pendiente')
            ->firstOrFail();

        // Recalcular subtotal por seguridad
        $subtotal = 0;
        foreach ($orden->detalles as $det) {
            $subtotal += (float) $det->precio_unitario * (int) $det->cantidad;
        }

        $envio = $this->shippingCost($subtotal);
        $total = $subtotal + $envio;

        $guardar = [
            'facturacion'     => $data['facturacion'],
            'envio'           => $data['envio'],
            'acepta_terminos' => true,
            'validated'       => true,
            'guardado_en'     => now()->toDateTimeString(),
        ];

        $orden->update([
            'subtotal'    => $subtotal,
            'envio'       => $envio,
            'total'       => $total,
            'datos_envio' => $guardar,
        ]);

        return redirect()->route('checkout.pay')->with('success', 'Datos de envío guardados. ¡Ya puedes pagar!');
    }

    /**
     * 4) Página de respuesta (solo informativa; el estado real lo fija el webhook).
     */
    public function response(Request $request)
    {
        $data = $request->all();

        $orden = null;
        if ($request->filled('x_extra1') && ctype_digit((string) $request->input('x_extra1'))) {
            $orden = Orden::with('detalles.producto')->find($request->input('x_extra1'));
        }
        if (!$orden && session()->has('orden_pendiente_id')) {
            $orden = Orden::with('detalles.producto')->find(session('orden_pendiente_id'));
        }

        // Si el webhook ya marcó como pagada, limpia sesión/carrito
        if ($orden && $orden->estado === 'pagada') {
            $this->clearCartSession();
        }

        return view('checkout.response', compact('data', 'orden'));
    }

    /**
     * Helper: costo de envío (gratis >= 50.000, si no $10.000).
     */
    private function shippingCost(float $subtotal): float
    {
        $umbral = 50000;
        $flat   = 10000.0;
        return $subtotal >= $umbral ? 0.0 : $flat;
    }

    /**
     * Helper: limpia completamente los datos de carrito/orden en sesión.
     */
    private function clearCartSession(): void
    {
        session()->forget(['carrito', 'cart_count', 'orden_pendiente_id']);
    }

    /**
     * Genera un invoice único y corto para ePayco (evita #E035).
     * Formato: ORD-<id>-<yyyymmddHHMMSS>-<n>
     */
    private function generarInvoiceUnico(int $orderId, int $intento): string
    {
        $stamp = now()->format('YmdHis');
        return "ORD-{$orderId}-{$stamp}-{$intento}";
    }
}
