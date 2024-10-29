@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100 mb-6">Editar Categoría</h1>

    <form action="{{ route('admin.categorias.update', $categoria) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="nombre" class="block text-gray-700 dark:text-gray-200">Nombre:</label>
            <input type="text" name="nombre" id="nombre" value="{{ $categoria->nombre }}" class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200" required>
        </div>

        <div class="mb-4">
            <label for="descripcion" class="block text-gray-700 dark:text-gray-200">Descripción:</label>
            <textarea name="descripcion" id="descripcion" class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 resize-none">{{ $categoria->descripcion }}</textarea>
        </div>

        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded transition duration-300">Actualizar Categoría</button>
    </form>
</div>
@endsection
