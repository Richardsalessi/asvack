<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Imagen;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::with('imagenes', 'categoria')->get();
        return view('admin.productos.index', compact('productos'));
    }

    public function create()
    {
        $categorias = Categoria::all();
        return view('admin.productos.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        // Remover los puntos del precio ingresado y validarlo
        $request->merge([
            'precio' => str_replace('.', '', $request->input('precio'))
        ]);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'precio' => 'required|numeric|min:0|max:100000000',
            'categoria_id' => 'required|exists:categorias,id',
            'imagenes.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $producto = Producto::create($request->only('nombre', 'descripcion', 'precio', 'categoria_id'));

        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $imagen) {
                $path = $imagen->store('imagenes', 'public');
                $producto->imagenes()->create(['ruta' => $path]);
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
        // Remover los puntos del precio ingresado y validarlo
        $request->merge([
            'precio' => str_replace('.', '', $request->input('precio'))
        ]);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'precio' => 'required|numeric|min:0|max:100000000',
            'categoria_id' => 'required|exists:categorias,id',
            'imagenes.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Actualizar los datos del producto
        $producto->update($request->only('nombre', 'descripcion', 'precio', 'categoria_id'));

        // Eliminar imágenes seleccionadas
        if ($request->has('eliminar_imagenes')) {
            foreach ($request->eliminar_imagenes as $imagenId) {
                $imagen = Imagen::find($imagenId);
                if ($imagen && $imagen->producto_id == $producto->id) {
                    Storage::disk('public')->delete($imagen->ruta);
                    $imagen->delete();
                }
            }
        }

        // Añadir nuevas imágenes
        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $imagen) {
                $path = $imagen->store('imagenes', 'public');
                $producto->imagenes()->create(['ruta' => $path]);
            }
        }

        return redirect()->route('admin.productos.index')->with('success', 'Producto actualizado con éxito.');
    }

    public function destroy(Producto $producto)
    {
        foreach ($producto->imagenes as $imagen) {
            Storage::disk('public')->delete($imagen->ruta);
            $imagen->delete();
        }

        $producto->delete();
        return redirect()->route('admin.productos.index')->with('success', 'Producto eliminado con éxito.');
    }
}
