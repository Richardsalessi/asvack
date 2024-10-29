@extends('layouts.app')

@section('content')
<div class="container mx-auto p-8 bg-white dark:bg-gray-800 shadow rounded-lg">
    <h1 class="text-2xl font-semibold text-gray-800 dark:text-white mb-6">Cotización de {{ $cotizacion->cliente->name }}</h1>
    
    <!-- Chat de la cotización -->
    <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg mb-6">
        @foreach($mensajes as $mensaje)
            <div class="mb-4">
                <strong class="{{ $mensaje->es_proveedor ? 'text-blue-600' : 'text-green-600' }}">
                    {{ $mensaje->es_proveedor ? 'Proveedor' : 'Cliente' }}
                </strong>
                <p class="text-gray-800 dark:text-gray-300">{{ $mensaje->contenido }}</p>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $mensaje->created_at->diffForHumans() }}</span>
            </div>
        @endforeach
    </div>

    <!-- Campo de entrada para responder -->
    <form action="{{ route('provider.cotizaciones.responder', $cotizacion->id) }}" method="POST">
        @csrf
        <div class="flex items-center">
            <input type="text" name="mensaje" placeholder="Escribe tu mensaje..." class="flex-grow p-3 border rounded dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
            <button type="submit" class="ml-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">
                Enviar
            </button>
        </div>
    </form>
</div>
@endsection
