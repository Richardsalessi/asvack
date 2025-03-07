@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">
        <h1 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Tu Carrito de Compras</h1>

        {{-- Mostrar el mensaje de éxito si existe --}}
        @if(session()->has('success'))
            <div class="bg-green-500 text-white p-4 rounded-md mb-4">
                {{ session('success') }}
            </div>
        @endif

        {{-- Mostrar el mensaje de error si existe --}}
        @if(session()->has('error'))
            <div class="bg-red-500 text-white p-4 rounded-md mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if(count($carrito) > 0)
            <div class="flex flex-col space-y-4" id="carrito-items">
                @foreach($carrito as $id => $producto)
                    <div class="cart-item flex justify-between items-center bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md" id="cart-item-{{ $id }}">
                        <div class="flex items-center space-x-4">
                            <!-- Imagen del producto -->
                            <div class="h-24 w-24">
                                <img src="{{ $producto['imagen'] }}" alt="{{ $producto['nombre'] }}" class="w-full h-full object-contain rounded-md">
                            </div>

                            <div class="cart-item-details">
                                <div class="cart-item-name font-semibold text-gray-900 dark:text-white">{{ $producto['nombre'] }}</div>
                                <div class="cart-item-price text-gray-700 dark:text-gray-300">Precio: ${{ number_format($producto['precio'], 2) }}</div>
                                <div class="cart-item-quantity text-gray-500 dark:text-gray-400">Cantidad: {{ $producto['cantidad'] }}</div>
                                <div class="cart-item-total text-gray-900 dark:text-white font-bold">Total: ${{ number_format($producto['total'], 2) }}</div>
                            </div>
                        </div>

                        <div class="cart-item-actions">
                            <!-- Botón para eliminar producto -->
                            <button class="bg-red-500 text-white py-2 px-4 rounded-md hover:bg-red-600 transition duration-200 delete-button" data-id="{{ $id }}">
                                Eliminar
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex justify-between items-center">
                <div>
                    <strong class="text-xl text-gray-900 dark:text-white">Total:</strong>
                    <span class="text-lg text-gray-900 dark:text-white cart-total">${{ number_format($total, 2) }}</span>
                </div>
                <a href="{{ route('checkout') }}" class="px-6 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-700 transition duration-300">
                    Proceder a la compra
                </a>
            </div>

        @else
            <p class="text-gray-900 dark:text-white">No tienes productos en tu carrito.</p>
        @endif
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-5 right-5 bg-green-500 text-white p-3 rounded-md shadow-lg opacity-0 transition-opacity duration-300" style="z-index: 9999;">
        Producto eliminado del carrito.
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Eliminar producto del carrito usando AJAX
            const deleteButtons = document.querySelectorAll('.delete-button');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();

                    const productId = button.getAttribute('data-id');

                    fetch(`/carrito/eliminar/${productId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Eliminar el producto de la vista
                            const productItem = document.getElementById(`cart-item-${productId}`);
                            productItem.remove();

                            // Si el carrito está vacío, mostrar mensaje vacío
                            if (data.cart_count === 0) {
                                document.querySelector('.cart-total').innerText = '$0.00'; // Restablecer total
                                document.querySelector('#carrito-items').innerHTML = '<p class="text-gray-900 dark:text-white">No tienes productos en tu carrito.</p>';
                            }

                            // Actualizar el contador del carrito en el navbar
                            updateCartCount(data.cart_count);
                            
                        } else {
                            alert('Error al eliminar el producto del carrito');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                });
            });

            // Función para actualizar el contador del carrito en el navbar
            function updateCartCount(count) {
                const cartCount = document.querySelector('#cart-count');
                if (cartCount) {
                    cartCount.innerText = count;
                }
            }
        });
    </script>
@endsection
