<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class CarritoController extends Controller
{
    // ============================
    // Helpers de imágenes
    // ============================

    // Todas las imágenes en base64 (para carrusel)
    private function getImagenesBase64($id)
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

    // Compat: una imagen principal (si te sirve en otras partes)
    private function getImagenBase64($id)
    {
        $producto = Producto::find($id);
        $imagen = $producto?->imagenes()->first();
        return $imagen && $imagen->contenido
            ? 'data:image/png;base64,' . $imagen->contenido
            : asset('storage/placeholder.png');
    }

    // ============================
    // Vistas / Acciones
    // ============================

    // Ver carrito
    public function index()
    {
        $carrito = session()->get('carrito', []);

        foreach ($carrito as $id => &$producto) {
            // Asegurar arreglo de imágenes para carrusel
            if (!isset($producto['imagenes']) || !is_array($producto['imagenes']) || empty($producto['imagenes'])) {
                $producto['imagenes'] = $this->getImagenesBase64($id);
            }

            // Compat: imagen "principal"
            if (!isset($producto['imagen'])) {
                $producto['imagen'] = $producto['imagenes'][0] ?? asset('storage/placeholder.png');
            }

            // Total individual
            $producto['total'] = ((float)$producto['precio']) * ((int)$producto['cantidad']);
        }
        unset($producto);

        $total = array_sum(array_column($carrito, 'total'));

        return view('carrito.index', compact('carrito', 'total'));
    }

    // Agregar producto
    public function agregar(Request $request, $id)
    {
        $producto = Producto::with('imagenes')->findOrFail($id);

        $cantidadAAgregar = (int) $request->input('cantidad', 1);
        $carrito = session()->get('carrito', []);
        $cantidadEnCarrito = isset($carrito[$id]) ? (int)$carrito[$id]['cantidad'] : 0;
        $cantidadTotal = $cantidadEnCarrito + max(1, $cantidadAAgregar);

        if ($cantidadTotal > (int)$producto->stock) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes agregar más de las unidades disponibles (' . $producto->stock . ').'
            ]);
        }

        // Todas las imágenes para el carrusel del carrito
        $imagenes = $producto->imagenes->map(
            fn($img) => 'data:image/jpeg;base64,' . $img->contenido
        )->values()->all();

        $carrito[$id] = [
            'nombre'    => $producto->nombre,
            'precio'    => (float)$producto->precio,
            'cantidad'  => $cantidadTotal,
            'imagen'    => $imagenes[0] ?? asset('storage/placeholder.png'), // compat
            'imagenes'  => $imagenes ?: [asset('storage/placeholder.png')],
        ];

        session()->put('carrito', $carrito);

        $totalUnidades = array_sum(array_column($carrito, 'cantidad'));
        session()->put('cart_count', $totalUnidades);

        return response()->json([
            'success'       => true,
            'cart_count'    => $totalUnidades,
        ]);
    }

    // Eliminar producto completo
    public function eliminar(Request $request, $id)
    {
        $carrito = session()->get('carrito', []);

        if (isset($carrito[$id])) {
            unset($carrito[$id]);
            session()->put('carrito', $carrito);
        }

        $totalUnidades = array_sum(array_column($carrito, 'cantidad'));
        if ($totalUnidades == 0) {
            session()->forget('cart_count');
        } else {
            session()->put('cart_count', $totalUnidades);
        }

        $nuevoTotal = 0;
        foreach ($carrito as $item) {
            $nuevoTotal += $item['precio'] * $item['cantidad'];
        }

        return response()->json([
            'success'           => true,
            'cart_count'        => $totalUnidades,
            'total_formateado'  => '$' . number_format($nuevoTotal, 2, ',', '.'),
            'message'           => 'Producto eliminado del carrito'
        ]);
    }

    // Quitar una cantidad específica
    public function quitar(Request $request, $id)
    {
        $carrito = session()->get('carrito', []);
        $cantidadARestar = (int) $request->input('cantidad', 1);
        $removido = false;
        $nuevaCantidad = 0;
        $totalIndividual = 0;

        if (isset($carrito[$id])) {
            $carrito[$id]['cantidad'] -= $cantidadARestar;

            if ($carrito[$id]['cantidad'] <= 0) {
                unset($carrito[$id]);
                $removido = true;
            } else {
                $nuevaCantidad = $carrito[$id]['cantidad'];
                $totalIndividual = number_format($carrito[$id]['precio'] * $nuevaCantidad, 2, ',', '.');
            }

            session()->put('carrito', $carrito);
        }

        $totalUnidades = array_sum(array_column($carrito, 'cantidad'));
        $total = 0;
        foreach ($carrito as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }

        if ($totalUnidades == 0) {
            session()->forget('cart_count');
        } else {
            session()->put('cart_count', $totalUnidades);
        }

        return response()->json([
            'success'           => true,
            'removido'          => $removido,
            'nueva_cantidad'    => $nuevaCantidad,
            'total_individual'  => $totalIndividual,
            'cart_count'        => $totalUnidades,
            'total_formateado'  => '$' . number_format($total, 2, ',', '.'),
            'total_raw'         => $total,
            'message'           => 'Cantidad actualizada'
        ]);
    }
}
