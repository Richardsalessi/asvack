<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PDF;

class CompraController extends Controller
{
    public function showFormulario($id)
    {
        // Verificar que el usuario es un cliente
        if (!Auth::user()->hasRole('client')) {
            return redirect()->route('home')->withErrors(['error' => 'Solo los clientes pueden realizar compras.']);
        }

        // Buscar el producto por ID
        $producto = Producto::findOrFail($id);

        // Retornar una vista para el formulario de compra
        return view('compra.formulario', compact('producto'));
    }

    public function procesarCompra(Request $request, $id)
    {
        // Verificar que el usuario es un cliente
        if (!Auth::user()->hasRole('client')) {
            return redirect()->route('home')->withErrors(['error' => 'Solo los clientes pueden realizar compras.']);
        }

        // Validar los datos de entrada
        $request->validate([
            'cantidad' => 'required|integer|min:1',
            'nombre_cliente' => 'required|string|max:255',
            'email_cliente' => 'required|email|max:255',
            'telefono' => 'required|string|max:20',
            'ciudad' => 'required|string|max:255',
            'barrio' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
        ]);

        $producto = Producto::findOrFail($id);
        $cantidad = $request->input('cantidad');

        // Verificar el stock
        if ($producto->stock < $cantidad) {
            return redirect()->back()->withErrors(['stock' => 'La cantidad solicitada excede el stock disponible.']);
        }

        // Iniciar transacción
        DB::beginTransaction();
        try {
            // Reducir el stock del producto
            $producto->stock -= $cantidad;
            $producto->save();

            // Registrar la compra en la tabla intermedia `compras`
            $user = Auth::user();
            $user->compras()->attach($producto->id, [
                'cantidad' => $cantidad,
                'precio_total' => $cantidad * $producto->precio,
                'telefono' => $request->input('telefono'),
                'ciudad' => $request->input('ciudad'),
                'barrio' => $request->input('barrio'),
                'direccion' => $request->input('direccion'),
            ]);

            // Generar PDF de la compra
            $pdf = PDF::loadView('compra.recibo', [
                'producto' => $producto,
                'cantidad' => $cantidad,
                'nombre_cliente' => $request->input('nombre_cliente'),
                'email_cliente' => $request->input('email_cliente'),
                'telefono' => $request->input('telefono'),
                'ciudad' => $request->input('ciudad'),
                'barrio' => $request->input('barrio'),
                'direccion' => $request->input('direccion'),
                'total' => $cantidad * $producto->precio,
            ]);

            DB::commit();

            // Retornar el PDF descargable
            return $pdf->download('recibo_compra.pdf');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => 'Ocurrió un problema al procesar la compra.']);
        }
    }
}
