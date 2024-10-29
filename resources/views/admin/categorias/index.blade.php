@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100 mb-6">Lista de Categorías</h1>
    <a href="{{ route('admin.categorias.create') }}" class="bg-green-500 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded transition duration-300 mb-4 inline-block">Añadir Categoría</a>
    <div class="overflow-x-auto mt-4">
        <table class="table-auto w-full">
            <thead>
                <tr class="bg-gray-200 dark:bg-gray-700 text-left">
                    <th class="px-4 py-2 text-gray-800 dark:text-gray-200">Nombre</th>
                    <th class="px-4 py-2 text-gray-800 dark:text-gray-200">Descripción</th>
                    <th class="px-4 py-2 text-gray-800 dark:text-gray-200">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800">
                @foreach ($categorias as $categoria)
                <tr class="border-b border-gray-300 dark:border-gray-700">
                    <td class="border px-4 py-2 text-gray-800 dark:text-gray-200">{{ $categoria->nombre }}</td>
                    <td class="border px-4 py-2 text-gray-800 dark:text-gray-200">{{ $categoria->descripcion }}</td>
                    <td class="border px-4 py-2 flex space-x-2">
                        <a href="{{ route('admin.categorias.edit', $categoria) }}" class="bg-blue-500 hover:bg-blue-700 text-white px-2 py-1 rounded transition duration-300">Editar</a>
                        <form action="{{ route('admin.categorias.destroy', $categoria) }}" method="POST" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white px-2 py-1 rounded transition duration-300" onclick="return confirm('¿Estás seguro de eliminar esta categoría?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
