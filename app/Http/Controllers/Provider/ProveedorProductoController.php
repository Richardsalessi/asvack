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
        // Procesar el número de WhatsApp para que contenga solo 10 dígitos y añadir el prefijo +57
        $contactoWhatsApp = preg_replace('/\D/', '', $request->input('contacto_whatsapp'));
        if (strlen($contactoWhatsApp) === 10) {
            $contactoWhatsApp = '+57' . $contactoWhatsApp;
        }

        $request->merge([
            'precio' => str_replace('.', '', $request->input('precio')),
            'contacto_whatsapp' => $contactoWhatsApp
        ]);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'precio' => 'required|numeric|min:0|max:100000000',
            'categoria_id' => 'required|exists:categorias,id',
            'stock' => 'required|integer|min:0',
            'contacto_whatsapp' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^\+573\d{9}$/', $value)) {
                        if (!ctype_digit(str_replace('+57', '', $value))) {
                            $fail('El número de WhatsApp solo debe contener dígitos.');
                        } else {
                            $fail('El número de WhatsApp debe contener exactamente 10 dígitos después del prefijo +57.');
                        }
                    }
                }
            ],
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

        $contactoWhatsApp = preg_replace('/\D/', '', $request->input('contacto_whatsapp'));
        if (strlen($contactoWhatsApp) === 10) {
            $contactoWhatsApp = '+57' . $contactoWhatsApp;
        }

        $request->merge([
            'precio' => str_replace('.', '', $request->input('precio')),
            'contacto_whatsapp' => $contactoWhatsApp
        ]);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'precio' => 'required|numeric|min:0|max:100000000',
            'categoria_id' => 'required|exists:categorias,id',
            'stock' => 'required|integer|min:0',
            'contacto_whatsapp' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^\+573\d{9}$/', $value)) {
                        if (!ctype_digit(str_replace('+57', '', $value))) {
                            $fail('El número de WhatsApp solo debe contener dígitos.');
                        } else {
                            $fail('El número de WhatsApp debe contener exactamente 10 dígitos después del prefijo +57.');
                        }
                    }
                }
            ],
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

        return redirect()->route('provider.productos.index')->with('success', 'Producto actualizado con éxito.');
    }

    public function destroy(Producto $producto)
    {
        if ($producto->user_id !== Auth::id()) {
            abort(403);
        }

        foreach ($producto->imagenes as $imagen) {
            $imagen->delete();
        }

        $producto->delete();
        return redirect()->route('provider.productos.index')->with('success', 'Producto eliminado con éxito.');
    }
}
