@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Tu Carrito de Compras</h1>

    @if(session()->has('success'))
        <div class="bg-green-500 text-white p-4 rounded-md mb-4">
            {{ session('success') }}
        </div>
    @endif

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
                        <div class="h-24 w-24">
                            <img src="{{ $producto['imagen'] }}" alt="{{ $producto['nombre'] }}" class="w-full h-full object-contain rounded-md">
                        </div>

                        <div class="cart-item-details">
                            <div class="cart-item-name font-semibold text-gray-900 dark:text-white">{{ $producto['nombre'] }}</div>
                            <div class="cart-item-price text-gray-700 dark:text-gray-300">Precio: ${{ number_format($producto['precio'], 2, ',', '.') }}</div>
                            <div class="cart-item-quantity text-gray-500 dark:text-gray-400">Cantidad: {{ $producto['cantidad'] }}</div>
                            <div class="cart-item-total text-gray-900 dark:text-white font-bold">Total: ${{ number_format($producto['total'], 2, ',', '.') }}</div>
                        </div>
                    </div>

                    <div class="cart-item-actions">
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
                <span id="checkout-total" class="text-lg text-gray-900 dark:text-white" data-total="{{ $total }}">
                ${{ number_format($total, 2, ',', '.') }}
            </span>

            </div>
            <a href="{{ route('checkout') }}" class="px-6 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-700 transition duration-300">
                Proceder a la compra
            </a>
        </div>
    @else
        <p class="text-gray-900 dark:text-white">No tienes productos en tu carrito.</p>
    @endif
</div>

<div id="toast" class="fixed bottom-5 right-5 bg-green-500 text-white p-3 rounded-md shadow-lg opacity-0 transition-opacity duration-300" style="z-index: 9999;">
    Producto eliminado del carrito.
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteButtons = document.querySelectorAll('.delete-button');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            const productId = this.getAttribute('data-id');

            fetch(`/carrito/eliminar/${productId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const productItem = document.getElementById(`cart-item-${productId}`);
                    if (productItem) productItem.remove();

                    // Actualiza el total visual del carrito
                    const totalSpan = document.getElementById('checkout-total');
                    if (totalSpan && data.total_formateado) {
                        totalSpan.textContent = data.total_formateado;
                        totalSpan.setAttribute('data-total', data.total_raw);
                    }

                    // Si el carrito queda vacío
                    if (data.cart_count === 0) {
                        document.querySelector('#carrito-items').innerHTML =
                            '<p class="text-gray-900 dark:text-white">No tienes productos en tu carrito.</p>';
                    }

                    updateCartCount(data.cart_count);
                    showToast('Producto eliminado del carrito.');
                } else {
                    alert('Error al eliminar el producto');
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

    function updateCartCount(count) {
        const badge = document.querySelector('#cart-count');
        if (badge) badge.innerText = count;
    }

    function showToast(message) {
        const toast = document.getElementById('toast');
        toast.innerText = message;
        toast.classList.remove('opacity-0');
        toast.classList.add('opacity-100');
        setTimeout(() => {
            toast.classList.remove('opacity-100');
            toast.classList.add('opacity-0');
        }, 2000);
    }
});
</script>
@endsection
