<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Models\Orden;
use App\Models\OrdenDetalle;
use App\Models\Producto;
use App\Models\TarifaEnvio;

class CheckoutController extends Controller
{
    /** Resuelve una imagen válida (base64 o URL) para el producto */
    private function resolveProductoImagen(?Producto $producto): string
    {
        if (!$producto) {
            return asset('storage/placeholder.png');
        }

        $img = $producto->imagenes()->first();
        if (!$img) {
            return asset('storage/placeholder.png');
        }

        // Si guardas base64 en DB (columna "contenido")
        if (!empty($img->contenido)) {
            return 'data:image/jpeg;base64,' . $img->contenido;
        }

        // Si guardas ruta/archivo (columna "ruta")
        if (!empty($img->ruta)) {
            if (str_starts_with($img->ruta, 'public/')) {
                return Storage::url($img->ruta);
            }
            return asset($img->ruta);
        }

        return asset('storage/placeholder.png');
    }

    /** 1) Resumen previo al pago (no mostramos envío aún) */
    public function show(Request $request)
    {
        $carrito = session('carrito', []);
        if (empty($carrito)) {
            return redirect()->route('carrito')->with('error', 'Tu carrito está vacío.');
        }

        // Enriquecer cada item del carrito con la imagen resuelta desde BD
        $subtotal = 0.0;
        foreach ($carrito as $prodId => &$item) {
            $producto = Producto::with('imagenes')->find((int) $prodId);

            // Nombre/Precio de respaldo por si faltan en sesión
            $item['nombre']  = $item['nombre']  ?? ($producto?->nombre ?? 'Producto');
            $item['precio']  = (float) ($item['precio'] ?? ($producto?->precio ?? 0));
            $item['cantidad']= (int)   ($item['cantidad'] ?? 1);

            // 🔑 Imagen (antes venía de sesión; ahora la resolvemos aquí)
            $item['imagen'] = $this->resolveProductoImagen($producto);

            $subtotal += $item['precio'] * $item['cantidad'];
        }
        unset($item);

        // Mantén la interfaz tal cual
        $envio = null;      // La vista puede mostrar “Se calculará en el siguiente paso”
        $total = $subtotal; // Por ahora total = subtotal

        // Pasamos el carrito ya enriquecido con 'imagen'
        return view('checkout.show', compact('carrito', 'subtotal', 'envio', 'total'));
    }

    /** 2) Crear/actualizar orden pendiente e ir a pagar */
    public function create(Request $request)
    {
        $carrito = session('carrito', []);
        if (empty($carrito)) {
            return redirect()->route('carrito')->with('error', 'Tu carrito está vacío.');
        }

        // Trae precios actuales desde BD (congelamos en detalles)
        $ids       = array_keys($carrito);
        $productos = Producto::whereIn('id', $ids)->get()->keyBy('id');

        $subtotal = 0.0;
        foreach ($carrito as $prodId => $item) {
            if (!isset($productos[$prodId])) {
                return back()->with('error', "El producto #{$prodId} ya no está disponible.");
            }
            $precio   = (float) $productos[$prodId]->precio;
            $cantidad = (int) $item['cantidad'];
            $subtotal += $precio * $cantidad;
        }

        // Envío PROVISIONAL = 0 (se recalcula cuando el usuario guarde su ciudad)
        $envio = 0.0;
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

                    // Refresca detalles
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

                    return $existente;
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
                'intentos_pago' => 0,
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
            $prodId     = $detalle->producto_id;
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

        // ========= Revalidar envío vigente ANTES de generar el invoice =========
        $datosEnvio = $orden->datos_envio ?? [];
        if (!is_array($datosEnvio)) {
            $datosEnvio = json_decode((string) $datosEnvio, true) ?: [];
        }
        $shippingOK = method_exists($orden, 'tieneEnvioValidado')
            ? $orden->tieneEnvioValidado()
            : (bool) data_get($datosEnvio, 'validated', false);

        if ($shippingOK) {
            $ciudad = (string) data_get($datosEnvio, 'envio.ciudad', '');
            if ($ciudad !== '') {
                $subtotal = 0.0;
                foreach ($orden->detalles as $d) {
                    $subtotal += (float) $d->precio_unitario * (int) $d->cantidad;
                }

                $envioVigente = $this->shippingCostByCity($ciudad);
                $totalVigente = $subtotal + $envioVigente;

                if ((float)$orden->envio !== (float)$envioVigente || (float)$orden->total !== (float)$totalVigente) {
                    $orden->update([
                        'subtotal' => $subtotal,
                        'envio'    => $envioVigente,
                        'total'    => $totalVigente,
                    ]);
                    session()->flash('success', 'Actualizamos el costo de envío con la tarifa vigente.');
                }
            }
        }

        // Generar invoice único por intento (evita E035) con el TOTAL actualizado
        $orden->intentos_pago = (int)($orden->intentos_pago ?? 0) + 1;
        $invoice              = $this->generarInvoiceUnico($orden->id, $orden->intentos_pago);
        $orden->ref_epayco    = $invoice;
        if (Schema::hasColumn('ordenes', 'ultimo_invoice')) {
            $orden->ultimo_invoice = $invoice;
        }
        $orden->save();

        // Config ePayco (monto final desde BD)
        $epayco = [
            'public_key'   => config('services.epayco.public_key', env('EPAYCO_PUBLIC_KEY')),
            'test'         => filter_var(env('EPAYCO_TEST', true), FILTER_VALIDATE_BOOLEAN),
            'currency'     => env('EPAYCO_CURRENCY', 'COP'),
            'lang'         => env('EPAYCO_LANG', 'ES'),

            // URLs robustas
            'response_url' => env('EPAYCO_RESPONSE_URL', url('/checkout/response')),
            'confirm_url'  => env('EPAYCO_CONFIRMATION_URL', url('/api/webhook/epayco')),

            'invoice'      => $invoice,
            'amount'       => number_format((float) $orden->total, 2, '.', ''),
            'name'         => 'Compra Asvack #'.$orden->id,
            'description'  => 'Pago de orden #'.$orden->id,
            'extra1'       => (string) $orden->id,
        ];

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
            // Facturación
            'facturacion.nombre'       => 'required|string|max:120',
            'facturacion.apellidos'    => 'required|string|max:120',
            'facturacion.cedula'       => 'required|string|max:40',
            'facturacion.telefono'     => 'required|string|max:40',
            'facturacion.email'        => 'nullable|email|max:160',
            'facturacion.direccion'    => 'required|string|max:255',
            'facturacion.ciudad'       => 'required|string|max:120',
            'facturacion.departamento' => 'required|string|max:120',
            'facturacion.barrio'       => 'nullable|string|max:120',

            // Envío
            'envio.nombre'             => 'required|string|max:120',
            'envio.apellidos'          => 'required|string|max:120',
            'envio.direccion'          => 'required|string|max:255',
            'envio.ciudad'             => 'required|string|max:120',
            'envio.departamento'       => 'required|string|max:120',
            'envio.barrio'             => 'nullable|string|max:120',
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

        // Subtotal desde detalles (congelados)
        $subtotal = 0.0;
        foreach ($orden->detalles as $det) {
            $subtotal += (float) $det->precio_unitario * (int) $det->cantidad;
        }

        // === SOLO CIUDAD ===
        $ciudad = (string) data_get($data, 'envio.ciudad');
        $envio  = $this->shippingCostByCity($ciudad);
        $total  = $subtotal + $envio;

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

    /** 3.2) Cotizar envío en vivo (AJAX) — SOLO CIUDAD */
    public function quoteShipping(Request $request)
    {
        $ordenId = session('orden_pendiente_id');
        if (!$ordenId) {
            return response()->json([
                'ok' => false,
                'message' => 'No hay una orden pendiente.',
            ], 422);
        }

        $orden = Orden::with('detalles')->where('id', $ordenId)
            ->where('user_id', auth()->id())
            ->where('estado', 'pendiente')
            ->firstOrFail();

        // Subtotal actual desde los detalles
        $subtotal = 0.0;
        foreach ($orden->detalles as $d) {
            $subtotal += (float) $d->precio_unitario * (int) $d->cantidad;
        }

        $data   = $request->validate(['ciudad' => 'nullable|string|max:120']);
        $ciudad = $data['ciudad'] ?? null;

        if (!$ciudad) {
            return response()->json([
                'ok'       => true,
                'subtotal' => (int) round($subtotal),
                'envio'    => null,
                'total'    => (int) round($subtotal),
                'message'  => 'Ingresa tu ciudad para ver el costo de envío en tiempo real.',
            ]);
        }

        $envio = $this->shippingCostByCity($ciudad);
        $total = $subtotal + $envio;

        return response()->json([
            'ok'       => true,
            'subtotal' => (int) round($subtotal),
            'envio'    => (int) round($envio),
            'total'    => (int) round($total),
            'message'  => 'Cotización actualizada.',
        ]);
    }

    /** 4) Página de respuesta robusta (consulta a ePayco y muestra estado “real”) */
    public function response(Request $request)
    {
        $refPayco = (string) $request->query('ref_payco', '');
        $orden = null;
        $gateway = [
            'raw'     => null,
            'status'  => null,   // APROBADA | RECHAZADA | PENDIENTE | FALLIDA | DESCONOCIDA
            'message' => null,
            'amount'  => null,
            'currency'=> null,
            'ref'     => $refPayco ?: null,
        ];

        // 1) Busca por ref_epayco o ultimo_invoice
        if ($refPayco !== '') {
            $orden = Orden::where('ref_epayco', 'like', "%{$refPayco}%")
                ->orWhere('ultimo_invoice', 'like', "%{$refPayco}%")
                ->latest('id')
                ->first();
        }

        // 2) Si no, intenta por x_extra1 (id orden)
        if (!$orden && $request->filled('x_extra1') && ctype_digit((string) $request->input('x_extra1'))) {
            $orden = Orden::find((int) $request->input('x_extra1'));
        }

        // 3) Consulta a ePayco (servicio de validación)
        if ($refPayco !== '') {
            try {
                $resp = Http::timeout(15)
                    ->get("https://secure.epayco.co/validation/v1/reference/{$refPayco}")
                    ->json();

                $gateway['raw']      = $resp;
                $gateway['amount']   = (float) str_replace(',', '.', (string) data_get($resp, 'data.x_amount', '0'));
                $gateway['currency'] = (string) data_get($resp, 'data.x_currency_code', '');
                $codResp             = (string) data_get($resp, 'data.x_cod_response', '');
                // 1=Aprobada, 2=Rechazada, 3=Pendiente, 4=Fallida
                $gateway['status']   = match ($codResp) {
                    '1' => 'APROBADA',
                    '2' => 'RECHAZADA',
                    '3' => 'PENDIENTE',
                    '4' => 'FALLIDA',
                    default => 'DESCONOCIDA',
                };
                $gateway['message']  = (string) data_get($resp, 'data.x_response_reason_text', '');
                $gateway['ref']      = $refPayco;

                // Si aún no tenemos orden, intenta por x_extra1
                if (!$orden) {
                    $extra1 = data_get($resp, 'data.x_extra1');
                    if ($extra1 && ctype_digit((string)$extra1)) {
                        $orden = Orden::find((int)$extra1);
                    }
                }

                // Guarda payload crudo si tenemos orden
                if ($orden && empty($orden->payload)) {
                    $orden->payload = data_get($resp, 'data', []);
                    $orden->save();
                }

                // Si la orden no está "pagada" y el gateway dice APROBADA con monto correcto → marca pagada
                if ($orden && $orden->estado !== 'pagada' && $gateway['status'] === 'APROBADA') {
                    $montoOk = abs(((float)$orden->total) - $gateway['amount']) < 0.01;
                    if ($montoOk) {
                        $orden->estado = 'pagada';
                        $orden->save();
                    }
                }
            } catch (\Throwable $e) {
                // no romper la vista
                $gateway['status']  = $gateway['status'] ?: 'DESCONOCIDA';
                $gateway['message'] = $gateway['message'] ?: 'No se pudo consultar el estado en ePayco.';
            }
        }

        // 4) Último recurso: sesión
        if (!$orden && session()->has('orden_pendiente_id')) {
            $orden = Orden::find(session('orden_pendiente_id'));
        }

        // 5) Si está pagada → limpiar carrito/sesión
        if ($orden && $orden->estado === 'pagada') {
            $this->clearCartSession();
        }

        // Estado que mostraremos en la UI (prioriza lo que diga el gateway)
        $estadoMostrable = $orden?->estado ?? 'pendiente';
        if ($gateway['status'] === 'APROBADA') {
            $estadoMostrable = 'pagada';
        } elseif (in_array($gateway['status'], ['RECHAZADA', 'FALLIDA'])) {
            $estadoMostrable = 'rechazada';
        }

        return view('checkout.response', [
            'ref_payco'       => $refPayco,
            'orden'           => $orden,
            'gateway'         => $gateway,
            'estadoMostrable' => $estadoMostrable,
            'data'            => $request->all(),
        ]);
    }

    /* =========================
     * Helpers de envío / sesión
     * ========================= */

    /** Fallback provisional (no se usa para el cálculo final) */
    private function shippingCost(float $subtotal): float
    {
        return 0.0;
    }

    /**
     * Costo por CIUDAD únicamente (sin umbral de envío gratis).
     * - Busca tarifa activa exacta por ciudad.
     * - Si no existe, usa fallback global $10.000.
     */
    private function shippingCostByCity(?string $ciudad): float
    {
        $ciudad = $ciudad ? trim($ciudad) : null;

        if ($ciudad) {
            $t = TarifaEnvio::where('activo', 1)
                ->where('ciudad', $ciudad)
                ->first(); // 👈 SIN whereNull('barrio')

            if ($t) {
                return (float) $t->costo;
            }
        }

        // Fallback global
        return 10000.0;
    }

    /** Limpia sesión/carrito */
    private function clearCartSession(): void
    {
        session()->forget(['carrito', 'cart_count', 'orden_pendiente_id']);
    }

    /** Invoice único para ePayco */
    private function generarInvoiceUnico(int $orderId, int $intento): string
    {
        $stamp = now()->format('YmdHis');
        return "ORD-{$orderId}-{$stamp}-{$intento}";
    }
}
