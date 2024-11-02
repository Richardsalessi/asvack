<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Imagen;
use App\Models\Categoria;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::with('imagenes', 'categoria', 'creador')->get();
        return view('admin.productos.index', compact('productos'));
    }

    public function create()
    {
        $categorias = Categoria::all();
        return view('admin.productos.create', compact('categorias'));
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

        return redirect()->route('admin.productos.index')->with('success', 'Producto creado con éxito.');
    }

    public function edit(Producto $producto)
    {
        $categorias = Categoria::all();
        return view('admin.productos.edit', compact('producto', 'categorias'));
    }

    public function update(Request $request, Producto $producto)
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

        $producto->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio,
            'categoria_id' => $request->categoria_id,
            'stock' => $request->stock,
            'contacto_whatsapp' => $request->contacto_whatsapp,
        ]);

        if ($request->has('eliminar_imagenes')) {
            foreach ($request->eliminar_imagenes as $imagenId) {
                $imagen = Imagen::find($imagenId);
                if ($imagen && $imagen->producto_id == $producto->id) {
                    $imagen->delete();
                }
            }
        }

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

        return redirect()->route('admin.productos.index')->with('success', 'Producto actualizado con éxito.');
    }

    public function destroy(Producto $producto)
    {
        foreach ($producto->imagenes as $imagen) {
            $imagen->delete();
        }

        $producto->delete();
        return redirect()->route('admin.productos.index')->with('success', 'Producto eliminado con éxito.');
    }

    public function catalogo(Request $request)
    {
        // Iniciamos la consulta de productos con las relaciones necesarias
        $query = Producto::with('imagenes', 'categoria', 'creador');

        // Aplicar filtro de categoría si está seleccionado
        if ($request->filled('category')) {
            $query->where('categoria_id', $request->input('category'));
        }

        // Aplicar filtro de proveedor si está seleccionado
        if ($request->filled('provider')) {
            $query->where('user_id', $request->input('provider'));
        }

        // Aplicar filtro de orden por precio si está seleccionado
        if ($request->filled('price')) {
            $query->orderBy('precio', $request->input('price'));
        }

        // Ejecutar la consulta y obtener los productos filtrados
        $productos = $query->get();

        // Cargar todas las categorías y proveedores para los filtros en la vista
        $categorias = Categoria::all();
        $proveedores = User::role('provider')->get();

        return view('catalogo', compact('productos', 'categorias', 'proveedores'));
    }
}
