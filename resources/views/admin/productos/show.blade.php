@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-6">{{ $producto->nombre }}</h1>
    <div class="mb-4">
        <strong>Descripción:</strong> {{ $producto->descripcion }}
    </div>
    <div class="mb-4">
        <strong>Precio:</strong> ${{ number_format($producto->precio, 2) }}
    </div>
    <div class="mb-4">
        <strong>Categoría:</strong> {{ $producto->categoria->nombre }}
    </div>
    <div class="mb-4">
        <strong>Imágenes:</strong>
        @if($producto->imagenes->isNotEmpty())
            <div class="flex space-x-4">
                @foreach($producto->imagenes as $imagen)
                    <img src="{{ asset('storage/' . $imagen->ruta) }}" alt="Imagen de {{ $producto->nombre }}" class="w-32 h-32 object-cover">
                @endforeach
            </div>
        @else
            <p>No hay imágenes disponibles</p>
        @endif
    </div>
    <a href="{{ route('admin.productos.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded">Volver</a>
</div>
@endsection
