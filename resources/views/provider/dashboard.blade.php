@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-8 bg-white dark:bg-gray-800 shadow rounded-lg">
        <h1 class="text-3xl font-semibold text-gray-800 dark:text-white mb-6">Dashboard del Proveedor</h1>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Bienvenid@, {{ Auth::user()->name }}.</p>

        @role('provider')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Sección para gestionar productos -->
                <div class="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 p-6 rounded-md shadow-md">
                    <h2 class="text-2xl font-bold mb-4">Gestionar Productos</h2>
                    <p class="mb-4">Administra los productos que ofreces a través de la plataforma.</p>
                    <a href="{{ route('provider.productos.index') }}" class="bg-blue-500 text-white px-4 py-2 rounded transition-all duration-300 hover:bg-blue-700">
                        Ir a Productos
                    </a>
                </div>
                
                <!-- Sección para responder solicitudes de cotización -->
                <div class="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 p-6 rounded-md shadow-md">
                    <h2 class="text-2xl font-bold mb-4">Responder Cotizaciones</h2>
                    <p class="mb-4">Gestiona las solicitudes de cotización recibidas de clientes.</p>
                    <a href="{{ route('provider.cotizaciones.index') }}" class="bg-yellow-500 text-white px-4 py-2 rounded transition-all duration-300 hover:bg-yellow-700">
                        Ir a Cotizaciones
                    </a>
                </div>
            </div>
        @endrole
    </div>
@endsection
