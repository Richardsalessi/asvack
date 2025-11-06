<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Orden;
use Illuminate\Http\Request;

class CarritoController extends Controller
{
    // ============================
    // Helpers de imágenes
    // ============================

    /** Devuelve todas las imágenes (base64) para el carrusel del carrito (NO se guardan en sesión) */
    private function getImagenesBase64(int $id): array
    {
        $producto = Producto::with('imagenes')->find($id);
        if (!$producto) {
            return [asset('storage/placeholder.png')];
        }

        $arr = $producto->imagenes
            ? $producto->imagenes->map(fn($img) => 'data:image/jpeg;base64,' . $img->contenido)->values()->all()
            : [];

        return count($arr) ? $arr : [asset('storage/placeholder.png')];
    }

    /** Compat: imagen “principal” como respaldo */
    private function getImagenBase64(int $id): string
    {
        $producto = Producto::with('imagenes')->find($id);
        $imagen = $producto?->imagenes()->first();
        return $imagen && $imagen->contenido
            ? 'data:image/png;base64,' . $imagen->contenido
            : asset('storage/placeholder.png');
    }

    // ============================
    // Helpers de carrito/orden
    // ============================

    /** Recalcula total de unidades en el carrito y actualiza badge de sesión */
    private function syncCartCount(array $carrito): int
    {
        $totalUnidades = array_sum(array_map(fn($it) => (int) $it['cantidad'], $carrito));
        if ($totalUnidades <= 0) {
            session()->forget('cart_count');
        } else {
            session()->put('cart_count', $totalUnidades);
        }
        return $totalUnidades;
    }

    /** Recalcula el total en dinero del carrito */
    private function calcTotal(array $carrito): float
    {
        $total = 0.0;
        foreach ($carrito as $item) {
            $total += ((float) $item['precio']) * ((int) $item['cantidad']);
        }
        return $total;
    }

    /** Si el carrito quedó vacío, cancela la orden pendiente y limpia la sesión */
    private function cancelarOrdenPendienteSiCarritoVacio(): void
    {
        $carrito = session('carrito', []);
        if (!empty($carrito)) {
            return; // aún hay items
        }

        $ordenId = session('orden_pendiente_id');
        if (!$ordenId) {
            return;
        }

        $orden = Orden::find($ordenId);
        if ($orden && $orden->estado === 'pendiente') {
            $orden->estado = 'cancelada';
            $orden->save();
        }

        session()->forget('orden_pendiente_id');
    }

    // ============================
    // Vistas / Acciones públicas
    // ============================

    /** Página del carrito */
    public function index()
    {
        $carrito = session()->get('carrito', []);

        // Completar estructura e imágenes (se resuelven aquí, NO en sesión)
        foreach ($carrito as $id => &$producto) {
            if (!isset($producto['imagenes']) || !is_array($producto['imagenes']) || empty($producto['imagenes'])) {
                $producto['imagenes'] = $this->getImagenesBase64((int) $id);
            }
            if (!isset($producto['imagen'])) {
                $producto['imagen'] = $producto['imagenes'][0] ?? asset('storage/placeholder.png');
            }
            $producto['total'] = ((float) $producto['precio']) * ((int) $producto['cantidad']);
        }
        unset($producto);

        $total = array_sum(array_column($carrito, 'total'));

        // Por si el usuario llega después de vaciar (refresco / otra pestaña)
        if (empty($carrito)) {
            $this->cancelarOrdenPendienteSiCarritoVacio();
        }

        return view('carrito.index', compact('carrito', 'total'));
    }

    /** Agregar producto al carrito (AJAX/POST) */
    public function agregar(Request $request, int $id)
    {
        $producto = Producto::with('imagenes')->findOrFail($id);

        $cantidadSolicitada = max(1, (int) $request->input('cantidad', 1));
        $carrito = session()->get('carrito', []);
        $enCarrito = isset($carrito[$id]) ? (int) $carrito[$id]['cantidad'] : 0;
        $cantidadTotal = $enCarrito + $cantidadSolicitada;

        // Stock
        if ($cantidadTotal > (int) $producto->stock) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes agregar más de las unidades disponibles (' . $producto->stock . ').',
            ], 422);
        }

        // 🚫 No guardar imágenes ni base64 en sesión (solo datos mínimos)
        $carrito[$id] = [
            'id'       => $producto->id,
            'nombre'   => $producto->nombre,
            'precio'   => (float) $producto->precio,
            'cantidad' => $cantidadTotal,
            // Opcional: podrías guardar una RUTA corta, nunca base64
            // 'imagen_path' => $producto->imagenes()->value('ruta') ?? null,
        ];

        session()->put('carrito', $carrito);

        $cartCount = $this->syncCartCount($carrito);
        $totalRaw  = $this->calcTotal($carrito);

        return response()->json([
            'success'          => true,
            'cart_count'       => $cartCount,
            'total_raw'        => $totalRaw,
            'total_formateado' => '$' . number_format($totalRaw, 2, ',', '.'),
        ]);
    }

    /** Eliminar un producto del carrito por completo */
    public function eliminar(Request $request, int $id)
    {
        $carrito = session()->get('carrito', []);

        if (isset($carrito[$id])) {
            unset($carrito[$id]);
            session()->put('carrito', $carrito);
        }

        $cartCount = $this->syncCartCount($carrito);
        $totalRaw  = $this->calcTotal($carrito);

        // Si quedó vacío, cancela la orden pendiente (si existe)
        $this->cancelarOrdenPendienteSiCarritoVacio();

        return response()->json([
            'success'          => true,
            'cart_count'       => $cartCount,
            'total_raw'        => $totalRaw,
            'total_formateado' => '$' . number_format($totalRaw, 2, ',', '.'),
            'message'          => 'Producto eliminado del carrito.',
        ]);
    }

    /** Quitar una cantidad específica de un producto */
    public function quitar(Request $request, int $id)
    {
        $carrito = session()->get('carrito', []);
        $cantidadARestar = max(1, (int) $request->input('cantidad', 1));

        $removido = false;
        $nuevaCantidad = 0;
        $totalIndividual = 0;

        if (isset($carrito[$id])) {
            $carrito[$id]['cantidad'] -= $cantidadARestar;

            if ($carrito[$id]['cantidad'] <= 0) {
                unset($carrito[$id]);
                $removido = true;
            } else {
                $nuevaCantidad   = (int) $carrito[$id]['cantidad'];
                $totalIndividual = number_format(
                    ((float) $carrito[$id]['precio']) * $nuevaCantidad,
                    2, ',', '.'
                );
            }

            session()->put('carrito', $carrito);
        }

        $cartCount = $this->syncCartCount($carrito);
        $totalRaw  = $this->calcTotal($carrito);

        // Si quedó vacío, cancela la orden pendiente (si existe)
        $this->cancelarOrdenPendienteSiCarritoVacio();

        return response()->json([
            'success'           => true,
            'removido'          => $removido,
            'nueva_cantidad'    => $nuevaCantidad,
            'total_individual'  => $totalIndividual,
            'cart_count'        => $cartCount,
            'total_raw'         => $totalRaw,
            'total_formateado'  => '$' . number_format($totalRaw, 2, ',', '.'),
            'message'           => 'Cantidad actualizada.',
        ]);
    }
}
    