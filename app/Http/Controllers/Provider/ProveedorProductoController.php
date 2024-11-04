<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Imagen;

class ProveedorProductoController extends Controller
{
    public function index()
    {
        // Obtener solo los productos del proveedor autenticado
        $productos = Producto::where('user_id', Auth::id())->with('imagenes', 'categoria')->get();
        return view('provider.productos.index', compact('productos'));
    }

    public function create()
    {
        $categorias = Categoria::all();
        return view('provider.productos.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'precio' => str_replace('.', '', $request->input('precio')),
            'contacto_whatsapp' => '+57' . $request->input('contacto_whatsapp')
        ]);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'precio' => 'required|numeric|min:0|max:100000000',
            'categoria_id' => 'required|exists:categorias,id',
            'stock' => 'required|integer|min:0',
            'contacto_whatsapp' => 'required|string|size:13',
            'imagenes.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $producto = Producto::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio,
            'categoria_id' => $request->categoria_id,
            'stock' => $request->stock,
            'contacto_whatsapp' => $request->contacto_whatsapp,
            'user_id' => Auth::id(),
        ]);

        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $imagen) {
                $path = $imagen->getClientOriginalName();
                $contenido = base64_encode(file_get_contents($imagen->getRealPath()));

                $producto->imagenes()->create([
                    'ruta' => $path,
                    'contenido' => $contenido
                ]);
            }
        }

        return redirect()->route('provider.productos.index')->with('success', 'Producto creado con éxito.');
    }

    public function edit(Producto $producto)
    {
        // Verificar que el proveedor autenticado es el dueño del producto
        if ($producto->user_id !== Auth::id()) {
            abort(403);
        }

        $categorias = Categoria::all();
        return view('provider.productos.edit', compact('producto', 'categorias'));
    }

    public function update(Request $request, Producto $producto)
    {
        if ($producto->user_id !== Auth::id()) {
            abort(403);
        }

        $request->merge([
            'precio' => str_replace('.', '', $request->input('precio')),
            'contacto_whatsapp' => '+57' . $request->input('contacto_whatsapp')
        ]);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'precio' => 'required|numeric|min:0|max:100000000',
            'categoria_id' => 'required|exists:categorias,id',
            'stock' => 'required|integer|min:0',
            'contacto_whatsapp' => 'required|string|size:13',
            'imagenes.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $producto->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio,
            'categoria_id' => $request->categoria_id,
            'stock' => $request->stock,
            'contacto_whatsapp' => $request->contacto_whatsapp,
        ]);

        // Eliminar imágenes seleccionadas
        if ($request->has('eliminar_imagenes')) {
            foreach ($request->eliminar_imagenes as $imagenId) {
                $imagen = Imagen::find($imagenId);
                if ($imagen && $imagen->producto_id == $producto->id) {
                    $imagen->delete();
                }
            }
        }

        // Agregar nuevas imágenes
        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $imagen) {
                $path = $imagen->getClientOriginalName();
                $contenido = base64_encode(file_get_contents($imagen->getRealPath()));

                $producto->imagenes()->create([
                    'ruta' => $path,
                    'contenido' => $contenido
                ]);
            }
        }

        return redirect()->route('provider.productos.index')->with('success', 'Producto actualizado con éxito.');
    }

    public function destroy(Producto $producto)
    {
        // Verificar que el proveedor autenticado es el dueño del producto
        if ($producto->user_id !== Auth::id()) {
            abort(403);
        }

        // Eliminar imágenes relacionadas
        foreach ($producto->imagenes as $imagen) {
            $imagen->delete();
        }

        // Eliminar el producto
        $producto->delete();

        return redirect()->route('provider.productos.index')->with('success', 'Producto eliminado con éxito.');
    }
}
