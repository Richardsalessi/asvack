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

    // Función que obtiene la imagen base64 de un producto
    private function getImagenBase64($id)
    {
        $producto = Producto::find($id);
        $imagen = $producto->imagenes()->first(); // Tomamos la primera imagen asociada al producto
        if ($imagen && $imagen->contenido) {
            return 'data:image/png;base64,' . $imagen->contenido; // Devolvemos la imagen en formato base64
        }
        return asset('storage/placeholder.png'); // Si no tiene imagen, usamos una por defecto
    }

    // Agregar un producto al carrito
    public function agregar(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);
        $imagen = $this->getImagenBase64($id); // Obtenemos la imagen base64

        // Verificar la cantidad solicitada y si hay suficiente stock
        $cantidadAAgregar = $request->cantidad;
        if ($cantidadAAgregar > $producto->stock) {
            return response()->json(['success' => false, 'message' => 'No hay suficiente stock para agregar esa cantidad.']);
        }

        // Obtener el carrito actual o crear uno vacío
        $carrito = session()->get('carrito', []);

        // Verificar si el producto ya está en el carrito
        if (isset($carrito[$id])) {
            // Si el producto ya está en el carrito, incrementar la cantidad
            $carrito[$id]['cantidad'] += $cantidadAAgregar;
        } else {
            // Agregar el producto al carrito
            $carrito[$id] = [
                'nombre' => $producto->nombre,
                'precio' => $producto->precio,
                'imagen' => $imagen,
                'cantidad' => $cantidadAAgregar,
            ];
        }

        // Guardar el carrito actualizado en la sesión
        session()->put('carrito', $carrito);
        session()->put('cart_count', count($carrito)); // Actualizamos la cantidad de productos en el carrito

        // Responder con el número actualizado de productos en el carrito
        return response()->json(['success' => true, 'cart_count' => count($carrito)]);
    }

    // Eliminar un producto del carrito
    public function eliminar(Request $request, $id)
    {
        // Obtenemos el carrito desde la sesión
        $carrito = session()->get('carrito', []);

        // Verificamos si el producto existe en el carrito
        if (isset($carrito[$id])) {
            unset($carrito[$id]); // Eliminamos el producto
            session()->put('carrito', $carrito); // Guardamos el carrito actualizado
        }

        // Si el carrito está vacío, eliminamos el contador
        if (count($carrito) == 0) {
            session()->forget('cart_count');
        } else {
            session()->put('cart_count', count($carrito)); // Actualizamos el número de productos en el carrito
        }

        // Calculamos el total actualizado
        $total = array_sum(array_column($carrito, 'total'));

        // Retornamos una respuesta en JSON con el estado de la eliminación
        return response()->json([
            'success' => true,
            'cart_count' => count($carrito),
            'total' => $total,
            'message' => 'Producto eliminado del carrito'
        ]);
    }
}
