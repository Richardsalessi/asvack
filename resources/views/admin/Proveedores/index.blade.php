@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-4xl font-bold mb-6 text-gray-900 dark:text-white">Lista de Proveedores</h1>
    <a href="{{ route('admin.proveedores.create') }}" class="bg-green-500 hover:bg-green-700 text-white px-6 py-3 rounded mb-4 inline-block transition-all duration-300">Añadir Proveedor</a>
    <div class="overflow-x-auto mt-4">
        <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <thead class="bg-gray-100 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-gray-900 dark:text-gray-300">Nombre</th>
                    <th class="px-6 py-3 text-left text-gray-900 dark:text-gray-300">Email</th>
                    <th class="px-6 py-3 text-left text-gray-900 dark:text-gray-300">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($proveedores as $proveedor)
                <tr class="border-b border-gray-200 dark:border-gray-700">
                    <td class="px-6 py-4 text-gray-900 dark:text-gray-300">{{ $proveedor->name }}</td>
                    <td class="px-6 py-4 text-gray-900 dark:text-gray-300">{{ $proveedor->email }}</td>
                    <td class="px-6 py-4">
                        <!-- Botón para editar proveedor -->
                        <a href="{{ route('admin.proveedores.edit', $proveedor) }}" class="bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded transition-all duration-300 mr-2">Editar</a>
                        
                        <!-- Formulario para eliminar proveedor -->
                        <form action="{{ route('admin.proveedores.destroy', $proveedor) }}" method="POST" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white px-4 py-2 rounded transition-all duration-300" onclick="return confirm('¿Estás seguro de eliminar este proveedor?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
