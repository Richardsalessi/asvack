<!-- resources/views/checkout.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold text-center mb-6">Resumen de tu Compra</h1>

    <div class="bg-white dark:bg-gray-900 shadow-lg p-6 rounded-lg">
        <h2 class="text-2xl font-semibold mb-4">Productos en el Carrito</h2>
        
        <!-- Aquí mostrarías los productos del carrito -->
        @foreach($carrito as $id => $producto)
            <div class="flex justify-between items-center mb-4">
                <div>{{ $producto['nombre'] }} (x{{ $producto['cantidad'] }})</div>
                <div>${{ number_format($producto['precio'] * $producto['cantidad'], 0, ',', '.') }}</div>
            </div>
        @endforeach

        <div class="flex justify-between items-center mt-4">
            <strong>Total:</strong>
            <span>${{ number_format(array_sum(array_column($carrito, 'precio')) , 0, ',', '.') }}</span>
        </div>

        <!-- Aquí iría el botón para proceder con la compra -->
        <a href="{{ route('pago') }}" class="mt-6 inline-block w-full bg-blue-600 text-white text-center py-2 rounded-md hover:bg-blue-800 transition duration-300">
            Proceder con la compra
        </a>
    </div>
</div>
@endsection
