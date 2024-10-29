@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-8 bg-white dark:bg-gray-800 shadow rounded-lg">
        <h1 class="text-3xl font-semibold text-gray-800 dark:text-white mb-6">Crear Nueva Cotización</h1>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Completa los detalles para crear una nueva cotización.</p>

        <form action="{{ route('provider.cotizaciones.store') }}" method="POST">
            @csrf
            <div class="mb-6">
                <label for="cliente" class="block mb-2 text-gray-800 dark:text-gray-200">Cliente</label>
                <input type="text" id="cliente" name="cliente" class="w-full p-3 border rounded dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
            </div>
            <div class="mb-6">
                <label for="estado" class="block mb-2 text-gray-800 dark:text-gray-200">Estado</label>
                <select id="estado" name="estado" class="w-full p-3 border rounded dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
                    <option>Pendiente</option>
                    <option>Aprobado</option>
                    <option>Rechazado</option>
                </select>
            </div>
            <div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded transition-all duration-300 hover:bg-blue-700">
                    Crear Cotización
                </button>
            </div>
        </form>
    </div>
@endsection
