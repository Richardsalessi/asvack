<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Categoria;

class CatalogoController extends Controller
{
    public function index(Request $request)
    {
        // Definir valores predeterminados
        $categoriaSeleccionada = $request->query('categoria', 'todos');
        $precioSeleccionado = $request->query('precio', '');

        // Obtener todas las categorías
        $categorias = Categoria::all();
        $productos = Producto::with('imagenes');

        // FILTRO POR CATEGORÍA
        if ($categoriaSeleccionada !== 'todos') {
            $productos->where('categoria_id', $categoriaSeleccionada);
        }

        // FILTRO POR PRECIO
        if (in_array($precioSeleccionado, ['menor', 'mayor'])) {
            $orden = $precioSeleccionado === 'menor' ? 'asc' : 'desc';
            $productos->orderBy('precio', $orden);
        }

        return view('catalogo', [
            'productos' => $productos->get(),
            'categorias' => $categorias,
            'categoriaSeleccionada' => $categoriaSeleccionada,
            'precioSeleccionado' => $precioSeleccionado
        ]);
    }
    
        public function filtrar(Request $request)
    {
        $categoria = $request->query('categoria');
        $precio = $request->query('precio');

        $productos = Producto::with('imagenes');

        if ($categoria !== 'todos') {
            $productos->where('categoria_id', $categoria);
        }

        if ($precio === 'menor') {
            $productos->orderBy('precio', 'asc');
        } elseif ($precio === 'mayor') {
            $productos->orderBy('precio', 'desc');
        }

        return response()->json($productos->get());
    }

}
