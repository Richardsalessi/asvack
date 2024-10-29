@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-8 bg-white dark:bg-gray-800 shadow rounded-lg">
        <h1 class="text-3xl font-semibold text-gray-800 dark:text-white mb-6">Listado de Cotizaciones</h1>

        <table class="min-w-full bg-white dark:bg-gray-800">
            <thead>
                <tr>
                    <th class="py-2 px-4 border-b dark:border-gray-700">ID</th>
                    <th class="py-2 px-4 border-b dark:border-gray-700">Cliente</th>
                    <th class="py-2 px-4 border-b dark:border-gray-700">Producto</th>
                    <th class="py-2 px-4 border-b dark:border-gray-700">Estado</th>
                    <th class="py-2 px-4 border-b dark:border-gray-700">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <!-- Aquí irían los registros de cotizaciones -->
                <tr>
                    <td class="py-2 px-4 border-b dark:border-gray-700">1</td>
                    <td class="py-2 px-4 border-b dark:border-gray-700">Cliente Ejemplo</td>
                    <td class="py-2 px-4 border-b dark:border-gray-700">Producto Ejemplo</td>
                    <td class="py-2 px-4 border-b dark:border-gray-700">Pendiente</td>
                    <td class="py-2 px-4 border-b dark:border-gray-700">
                        <a href="#" class="text-blue-500 hover:text-blue-700">Ver</a> |
                        <a href="#" class="text-yellow-500 hover:text-yellow-700">Editar</a> |
                        <form action="#" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700">Eliminar</button>
                        </form>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection
