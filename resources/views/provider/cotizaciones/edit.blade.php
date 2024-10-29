@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-8 bg-white dark:bg-gray-800 shadow rounded-lg">
        <h1 class="text-3xl font-semibold text-gray-800 dark:text-white mb-6">Editar Cotización</h1>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Modifica los detalles de la cotización.</p>

        <form action="{{ route('provider.cotizaciones.update', $cotizacion->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-6">
                <label for="cliente" class="block mb-2 text-gray-800 dark:text-gray-200">Cliente</label>
                <input type="text" id="cliente" name="cliente" value="{{ $cotizacion->cliente }}" class="w-full p-3 border rounded dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
            </div>
            <div class="mb-6">
                <label for="estado" class="block mb-2 text-gray-800 dark:text-gray-200">Estado</label>
                <select id="estado" name="estado" class="w-full p-3 border rounded dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
                    <option {{ $cotizacion->estado == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option {{ $cotizacion->estado == 'Aprobado' ? 'selected' : '' }}>Aprobado</option>
                    <option {{ $cotizacion->estado == 'Rechazado' ? 'selected' : '' }}>Rechazado</option>
                </select>
            </div>
            <div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded transition-all duration-300 hover:bg-blue-700">
                    Actualizar Cotización
                </button>
            </div>
        </form>
    </div>
@endsection
