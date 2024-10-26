@extends('layouts.app')

@section('content')
<div class="py-8">
    <!-- Título principal -->
    <div class="text-center mb-8">
        <h1 class="text-5xl font-extrabold text-gray-900 dark:text-white mb-4">Bienvenido a Asvack</h1>
        <p class="text-2xl text-gray-600 dark:text-gray-300">Somos una empresa dedicada a ofrecer componentes de calidad para tus equipos.</p>
    </div>

    <!-- Video como banner extendido al ancho total -->
    <div class="relative w-screen left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] mb-8">
        <video autoplay loop muted class="w-full object-cover h-[500px]"> <!-- Video ajustado para ocupar toda la pantalla -->
            <source src="{{ asset('storage/Video.mp4') }}" type="video/mp4">
            Tu navegador no soporta la reproducción de videos.
        </video>
    </div>

    <!-- Catálogo de productos -->
    <div class="container mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach ($productos as $producto)
            <div class="bg-gray-100 dark:bg-gray-800 p-6 rounded-lg shadow-lg hover:shadow-2xl transition-all duration-300">
                <img src="{{ $producto->imagen }}" alt="Imagen de {{ $producto->nombre }}" class="h-48 w-full object-cover rounded-t-lg mb-4">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">{{ $producto->nombre }}</h2>
                <p class="text-gray-500 dark:text-gray-400">Categoría: {{ $producto->categoria }}</p>
                <p class="text-gray-600 dark:text-gray-300 mt-2">{{ $producto->descripcion }}</p>
                <p class="text-gray-800 dark:text-gray-100 mt-4 font-bold text-lg">${{ number_format($producto->precio, 2) }}</p>
                @guest
                    <a href="{{ route('login') }}" class="mt-4 inline-block px-6 py-3 bg-purple-600 text-white font-semibold rounded hover:bg-purple-800 transition-all duration-300">Inicia sesión para solicitar una cotización</a>
                @endguest
            </div>
        @endforeach
    </div>
</div>
@endsection
