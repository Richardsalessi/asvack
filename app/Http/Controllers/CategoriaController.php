<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todas las categorías
        $categorias = Categoria::all();
        return view('admin.categorias.index', compact('categorias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Mostrar el formulario de creación de categoría
        return view('admin.categorias.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar la solicitud
        $request->validate([
            'nombre' => 'required|unique:categorias|max:255',
            'descripcion' => 'nullable|string',
        ]);

        // Crear la nueva categoría
        Categoria::create($request->all());

        // Redirigir al índice de categorías con un mensaje de éxito
        return redirect()->route('admin.categorias.index')->with('success', 'Categoría creada con éxito.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Categoria $categoria)
    {
        // Mostrar el formulario de edición de la categoría
        return view('admin.categorias.edit', compact('categoria'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Categoria $categoria)
    {
        // Validar la solicitud
        $request->validate([
            'nombre' => 'required|max:255|unique:categorias,nombre,' . $categoria->id,
            'descripcion' => 'nullable|string',
        ]);

        // Actualizar la categoría con los datos del formulario
        $categoria->update($request->all());

        // Redirigir al índice de categorías con un mensaje de éxito
        return redirect()->route('admin.categorias.index')->with('success', 'Categoría actualizada con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Categoria $categoria)
    {
        // Eliminar la categoría
        $categoria->delete();

        // Redirigir al índice de categorías con un mensaje de éxito
        return redirect()->route('admin.categorias.index')->with('success', 'Categoría eliminada con éxito.');
    }
}
