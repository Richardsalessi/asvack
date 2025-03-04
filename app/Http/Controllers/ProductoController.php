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
    public function index(Request $request)
    {
        // Obtener solo los administradores para el filtro
        $administradores = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        // Obtener todas las categorías para el filtro
        $categorias = Categoria::all();

        // Construir la consulta de productos
        $query = Producto::with('imagenes', 'categoria');

        if ($request->filled('admin')) {
            $query->where('user_id', $request->admin);
        }

        if ($request->filled('categoria')) {
            $query->where('categoria_id', $request->categoria);
        }

        if ($request->filled('precio')) {
            $query->orderBy('precio', $request->precio);
        }

        if ($request->filled('stock')) {
            $query->orderBy('stock', $request->stock);
        }

        $productos = $query->get();

        return view('admin.productos.index', compact('productos', 'administradores', 'categorias'));
    }


    public function create()
    {
        $categorias = Categoria::all();
        return view('admin.productos.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'precio' => str_replace('.', '', $request->input('precio'))
        ]);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'precio' => 'required|numeric|min:0|max:100000000',
            'categoria_id' => 'required|exists:categorias,id',
            'stock' => 'required|integer|min:0',
            'imagenes.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $producto = Producto::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio,
            'categoria_id' => $request->categoria_id,
            'stock' => $request->stock,
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
            'precio' => str_replace('.', '', $request->input('precio'))
        ]);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'precio' => 'required|numeric|min:0|max:100000000',
            'categoria_id' => 'required|exists:categorias,id',
            'stock' => 'required|integer|min:0',
            'imagenes.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $producto->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio,
            'categoria_id' => $request->categoria_id,
            'stock' => $request->stock,
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
        $query = Producto::with('imagenes', 'categoria');

        // Filtrar por categoría
        if ($request->filled('category')) {
            $query->where('categoria_id', $request->category);
        }

        // Ordenar por precio
        if ($request->filled('price') && in_array($request->price, ['asc', 'desc'])) {
            $query->orderBy('precio', $request->price);
        }

        $productos = $query->get();
        $categorias = Categoria::all();

        return view('catalogo', compact('productos', 'categorias'));
    }
}
