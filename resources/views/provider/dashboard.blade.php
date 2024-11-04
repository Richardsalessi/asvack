@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-8 bg-white dark:bg-gray-800 shadow rounded-lg">
        <h1 class="text-3xl font-semibold text-gray-800 dark:text-white mb-6">Proveedor Dashboard</h1>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Bienvenido, proveedor {{ Auth::user()->name }}.</p>
        
        @role('provider')
            <div class="bg-blue-100 text-blue-700 dark:bg-blue-800 dark:text-blue-200 p-4 rounded-md mb-6">
                <p class="font-medium">Puedes gestionar tus productos y categorías.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Sección para gestionar productos del proveedor -->
                <div class="bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200 p-6 rounded-md shadow-md">
                    <h2 class="text-2xl font-bold mb-4">Mis Productos</h2>
                    <p class="mb-4">Administra tus productos fácilmente desde aquí.</p>
                    <a href="{{ route('provider.productos.index') }}" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600 transition">
                        Ver mis productos
                    </a>
                </div>
                
                <!-- Sección para ver categorías disponibles -->
                <div class="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 p-6 rounded-md shadow-md">
                    <h2 class="text-2xl font-bold mb-4">Ver Categorías</h2>
                    <p class="mb-4">Consulta las categorías de productos disponibles.</p>
                    <a href="{{ route('provider.categorias.index') }}" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 transition">
                        Ver categorías
                    </a>
                </div>
            </div>
        @endrole
    </div>
@endsection
