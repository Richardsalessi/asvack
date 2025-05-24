<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CarritoController extends Controller
{
    // Ver el carrito
    public function index()
    {
        $carrito = session()->get('carrito', []);

        // Agregar imágenes y calcular el total de cada producto
        foreach ($carrito as $id => &$producto) {
            $producto['imagen'] = $this->getImagenBase64($id);
            $producto['total'] = $producto['precio'] * $producto['cantidad'];
        }

        // Calcular el total general
        $total = array_sum(array_column($carrito, 'total'));

        return view('carrito.index', compact('carrito', 'total'));
    }

    // Obtener imagen en base64
    private function getImagenBase64($id)
    {
        $producto = Producto::find($id);
        $imagen = $producto->imagenes()->first();
        if ($imagen && $imagen->contenido) {
            return 'data:image/png;base64,' . $imagen->contenido;
        }
        return asset('storage/placeholder.png');
    }

    // Agregar un producto al carrito
    public function agregar(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);
        $imagen = $this->getImagenBase64($id);

        $cantidadAAgregar = (int) $request->input('cantidad', 1);

        $carrito = session()->get('carrito', []);

        $cantidadEnCarrito = isset($carrito[$id]) ? $carrito[$id]['cantidad'] : 0;
        $cantidadTotal = $cantidadEnCarrito + $cantidadAAgregar;

        if ($cantidadTotal > $producto->stock) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes agregar más de las unidades disponibles (' . $producto->stock . ').'
            ]);
        }

        // Agregar o actualizar el producto en el carrito
        $carrito[$id] = [
            'nombre' => $producto->nombre,
            'precio' => $producto->precio,
            'imagen' => $imagen,
            'cantidad' => $cantidadTotal,
        ];

        session()->put('carrito', $carrito);

        // Recalcular el total de unidades para mostrar en el ícono del carrito
        $totalUnidades = array_sum(array_column($carrito, 'cantidad'));
        session()->put('cart_count', $totalUnidades);

        return response()->json([
            'success' => true,
            'cart_count' => $totalUnidades
        ]);
    }


    // Eliminar un producto del carrito
    public function eliminar(Request $request, $id)
    {
        $carrito = session()->get('carrito', []);

        if (isset($carrito[$id])) {
            unset($carrito[$id]);
            session()->put('carrito', $carrito);
        }

        if (count($carrito) == 0) {
            session()->forget('cart_count');
            $totalUnidades = 0;
        } else {
            $totalUnidades = array_sum(array_column($carrito, 'cantidad'));
            session()->put('cart_count', $totalUnidades);
        }

        $total = 0;
        foreach ($carrito as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }

        return response()->json([
            'success' => true,
            'cart_count' => $totalUnidades,
            'total' => $total,
            'message' => 'Producto eliminado del carrito'
        ]);
    }

    // Quitar una cantidad específica de un producto del carrito
    public function quitar(Request $request, $id)
    {
        $carrito = session()->get('carrito', []);

        if (isset($carrito[$id])) {
            $cantidadARestar = $request->cantidad;
            $carrito[$id]['cantidad'] -= $cantidadARestar;

            if ($carrito[$id]['cantidad'] <= 0) {
                unset($carrito[$id]);
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
            'success' => true,
            'cart_count' => $totalUnidades,
            'total' => $total,
            'message' => 'Cantidad actualizada'
        ]);
    }
}
