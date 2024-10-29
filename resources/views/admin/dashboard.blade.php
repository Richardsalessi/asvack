@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-8 bg-white dark:bg-gray-800 shadow rounded-lg">
        <h1 class="text-3xl font-semibold text-gray-800 dark:text-white mb-6">Dashboard del Administrador</h1>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Bienvenid@, {{ Auth::user()->name }}.</p>
        
        @role('admin')
            <div class="bg-green-100 text-green-700 dark:bg-green-800 dark:text-green-200 p-4 rounded-md mb-6">
                <p class="font-medium">Tienes acceso total al sistema.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Sección para administrar productos -->
                <div class="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 p-6 rounded-md shadow-md">
                    <h2 class="text-2xl font-bold mb-4">Administrar Productos</h2>
                    <p class="mb-4">Gestiona todos los productos disponibles en la plataforma.</p>
                    <a href="{{ route('admin.productos.index') }}" class="bg-blue-500 text-white px-4 py-2 rounded transition-all duration-300 hover:bg-blue-700">
                        Ir a Productos
                    </a>
                </div>
                
                <!-- Sección para administrar categorías -->
                <div class="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 p-6 rounded-md shadow-md">
                    <h2 class="text-2xl font-bold mb-4">Administrar Categorías</h2>
                    <p class="mb-4">Gestiona las categorías de los productos.</p>
                    <a href="{{ route('admin.categorias.index') }}" class="bg-yellow-500 text-white px-4 py-2 rounded transition-all duration-300 hover:bg-yellow-700">
                        Ir a Categorías
                    </a>
                </div>
            </div>
        @endrole
    </div>
@endsection
