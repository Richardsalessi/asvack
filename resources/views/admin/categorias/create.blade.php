@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-6">Añadir Categoría</h1>

    <form action="{{ route('admin.categorias.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label for="nombre" class="block text-gray-700">Nombre:</label>
            <input type="text" name="nombre" id="nombre" class="w-full p-2 border rounded" required>
        </div>

        <div class="mb-4">
            <label for="descripcion" class="block text-gray-700">Descripción:</label>
            <textarea name="descripcion" id="descripcion" class="w-full p-2 border rounded"></textarea>
        </div>

        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Guardar Categoría</button>
    </form>
</div>
@endsection
