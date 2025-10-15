<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use App\Models\Orden;
use App\Models\OrdenDetalle;
use App\Models\Producto;

class CheckoutController extends Controller
{
    /** 1) Resumen previo al pago */
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

    /** 2) Crear/actualizar orden pendiente e ir a pagar */
    public function create(Request $request)
    {
        $carrito = session('carrito', []);
        if (empty($carrito)) {
            return redirect()->route('carrito')->with('error', 'Tu carrito está vacío.');
        }

        $ids = array_keys($carrito);
        $productos = Producto::whereIn('id', $ids)->get()->keyBy('id');

        $subtotal = 0;
        foreach ($carrito as $prodId => $item) {
            if (!isset($productos[$prodId])) {
                return back()->with('error', "El producto #{$prodId} ya no está disponible.");
            }
            $precio = (float) $productos[$prodId]->precio;
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
                    $existente->update([
                        'subtotal' => $subtotal,
                        'envio'    => $envio,
                        'total'    => $total,
                    ]);

                    OrdenDetalle::where('orden_id', $existente->id)->delete();

                    foreach ($carrito as $prodId => $item) {
                        $producto = $productos[$prodId];
                        $precio = (float) $producto->precio;
                        $cantidad = (int) $item['cantidad'];

                        OrdenDetalle::create([
                            'orden_id'        => $existente->id,
                            'producto_id'     => $producto->id,
                            'cantidad'        => $cantidad,
                            'precio_unitario' => $precio,
                            'subtotal'        => $precio * $cantidad,
                        ]);
                    }

                    return $existente;
                }
            }

            $nueva = Orden::create([
                'user_id'       => auth()->id(),
                'estado'        => 'pendiente',
                'subtotal'      => $subtotal,
                'envio'         => $envio,
                'total'         => $total,
                'datos_envio'   => null,
                'intentos_pago' => 0,
            ]);

            foreach ($carrito as $prodId => $item) {
                $producto = $productos[$prodId];
                $precio = (float) $producto->precio;
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

        session(['orden_pendiente_id' => $orden->id]);

        return redirect()->route('checkout.pay')
            ->with('success', "Orden #{$orden->id} lista para pagar.");
    }

    /** 3) Pantalla de pago (widget ePayco) */
    public function pay(Request $request)
    {
        $ordenId = session('orden_pendiente_id');
        if (!$ordenId) {
            return redirect()->route('checkout')->with('error', 'No hay una orden pendiente.');
        }

        $orden = Orden::with(['detalles.producto.imagenes'])->findOrFail($ordenId);

        // Adjunta imágenes desde sesión (si las hay)
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

        // Generar invoice único por intento
        $orden->intentos_pago = (int)($orden->intentos_pago ?? 0) + 1;
        $invoice = $this->generarInvoiceUnico($orden->id, $orden->intentos_pago);
        $orden->ref_epayco = $invoice;
        if (Schema::hasColumn('ordenes', 'ultimo_invoice')) {
            $orden->ultimo_invoice = $invoice;
        }
        $orden->save();

        // Config para ePayco
        $epayco = [
            'public_key'   => config('services.epayco.public_key', env('EPAYCO_PUBLIC_KEY')),
            'test'         => filter_var(env('EPAYCO_TEST', true), FILTER_VALIDATE_BOOLEAN),
            'currency'     => env('EPAYCO_CURRENCY', 'COP'),
            'lang'         => env('EPAYCO_LANG', 'ES'),
            'response_url' => env('EPAYCO_RESPONSE_URL', url('/checkout/response')),
            'confirm_url'  => env('EPAYCO_CONFIRMATION_URL', url('/api/webhook/epayco')),
            'invoice'      => $invoice,
            'amount'       => number_format((float) $orden->total, 2, '.', ''),
            'name'         => 'Compra Asvack #'.$orden->id,
            'description'  => 'Pago de orden #'.$orden->id,
            'extra1'       => (string) $orden->id,
        ];

        $datosEnvio = (array) ($orden->datos_envio ?? []);
        $shippingOK = (bool) data_get($orden->datos_envio ?? [], 'validated', false);

        return view('checkout.pay', compact('orden', 'epayco', 'datosEnvio', 'shippingOK'));
    }

    /** 3.1) Guardar datos de facturación / envío */
    public function saveShipping(Request $request)
    {
        $ordenId = session('orden_pendiente_id');
        if (!$ordenId) {
            return redirect()->route('checkout')->with('error', 'No hay una orden pendiente.');
        }

        $data = $request->validate([
            'facturacion.nombre'       => 'required|string|max:120',
            'facturacion.apellidos'    => 'required|string|max:120',
            'facturacion.cedula'       => 'required|string|max:40',
            'facturacion.telefono'     => 'required|string|max:40',
            'facturacion.email'        => 'nullable|email|max:160',
            'facturacion.direccion'    => 'required|string|max:255',
            'facturacion.ciudad'       => 'required|string|max:120',
            'facturacion.departamento' => 'required|string|max:120',
            'envio.nombre'             => 'required|string|max:120',
            'envio.apellidos'          => 'required|string|max:120',
            'envio.direccion'          => 'required|string|max:255',
            'envio.ciudad'             => 'required|string|max:120',
            'envio.departamento'       => 'required|string|max:120',
            'envio.notas'              => 'nullable|string|max:500',
            'acepta_terminos'          => 'accepted',
        ], [
            'acepta_terminos.accepted' => 'Debes aceptar los términos y condiciones para continuar.',
        ]);

        $orden = Orden::with('detalles')->where('id', $ordenId)
            ->where('user_id', auth()->id())
            ->where('estado', 'pendiente')
            ->firstOrFail();

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

    /** 4) Página de respuesta robusta */
    public function response(Request $request)
    {
        $refPayco = (string) $request->query('ref_payco', '');
        $orden = null;

        // 1️⃣ Busca por ref_epayco o ultimo_invoice
        if ($refPayco !== '') {
            $orden = Orden::where('ref_epayco', 'like', "%{$refPayco}%")
                ->orWhere('ultimo_invoice', 'like', "%{$refPayco}%")
                ->latest('id')
                ->first();
        }

        // 2️⃣ Si no, intenta por x_extra1 (id orden)
        if (!$orden && $request->filled('x_extra1') && ctype_digit((string) $request->input('x_extra1'))) {
            $orden = Orden::find((int) $request->input('x_extra1'));
        }

        // 3️⃣ Si no, consulta a ePayco (servicio de validación)
        if (!$orden && $refPayco) {
            try {
                $resp = Http::timeout(15)->get("https://secure.epayco.co/validation/v1/reference/{$refPayco}")->json();
                $extra1 = data_get($resp, 'data.x_extra1');
                if ($extra1 && ctype_digit((string)$extra1)) {
                    $orden = Orden::find((int)$extra1);
                    if ($orden && empty($orden->payload)) {
                        $orden->payload = data_get($resp, 'data', []);
                        $orden->save();
                    }
                }
            } catch (\Throwable $e) {
                // nada
            }
        }

        // 4️⃣ Último recurso: sesión
        if (!$orden && session()->has('orden_pendiente_id')) {
            $orden = Orden::find(session('orden_pendiente_id'));
        }

        // 5️⃣ Si está pagada → limpiar carrito/sesión
        if ($orden && $orden->estado === 'pagada') {
            $this->clearCartSession();
        }

        return view('checkout.response', [
            'ref_payco' => $refPayco,
            'orden'     => $orden,
            'data'      => $request->all(),
        ]);
    }

    /** Helpers */
    private function shippingCost(float $subtotal): float
    {
        return $subtotal >= 50000 ? 0.0 : 10000.0;
    }

    private function clearCartSession(): void
    {
        session()->forget(['carrito', 'cart_count', 'orden_pendiente_id']);
    }

    private function generarInvoiceUnico(int $orderId, int $intento): string
    {
        $stamp = now()->format('YmdHis');
        return "ORD-{$orderId}-{$stamp}-{$intento}";
    }
}
