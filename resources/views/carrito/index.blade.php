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

                        <div class="cart-item-details" id="detalle-{{ $id }}">
                            <div class="cart-item-name font-semibold text-gray-900 dark:text-white">{{ $producto['nombre'] }}</div>
                            <div class="cart-item-price text-gray-700 dark:text-gray-300">Precio: ${{ number_format($producto['precio'], 2, ',', '.') }}</div>
                            <div class="cart-item-quantity text-gray-500 dark:text-gray-400">Cantidad: <span class="cantidad-text">{{ $producto['cantidad'] }}</span></div>
                            <div class="cart-item-total text-gray-900 dark:text-white font-bold">Total: $<span class="total-individual">{{ number_format($producto['total'], 2, ',', '.') }}</span></div>
                        </div>
                    </div>

                    <div class="cart-item-actions flex flex-col gap-2 items-end">
                        <form class="quitar-form flex items-center space-x-2" data-id="{{ $id }}">
                            <input type="number" name="cantidad" min="1" max="{{ $producto['cantidad'] }}" value="1" class="w-16 px-2 py-1 border rounded dark:bg-gray-700 dark:text-white text-center">
                            <button type="submit" class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600 transition">Quitar</button>
                        </form>

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
                    document.getElementById(`cart-item-${productId}`)?.remove();

                    actualizarTotal(data);
                    updateCartCount(data.cart_count);
                    showToast('Producto eliminado del carrito.');

                    if (data.cart_count === 0) {
                        document.querySelector('#carrito-items').innerHTML =
                            '<p class="text-gray-900 dark:text-white">No tienes productos en tu carrito.</p>';
                    }
                }
            });
        });
    });

    document.querySelectorAll('.quitar-form').forEach(form => {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            const productId = this.getAttribute('data-id');
            const cantidad = parseInt(this.querySelector('input[name="cantidad"]').value);

            if (!cantidad || cantidad < 1) return;

            fetch(`/carrito/quitar/${productId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ cantidad: cantidad })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.removido) {
                        document.getElementById(`cart-item-${productId}`)?.remove();
                    } else {
                        const card = document.querySelector(`#detalle-${productId}`);
                        card.querySelector('.cantidad-text').textContent = data.nueva_cantidad;
                        card.querySelector('.total-individual').textContent = data.total_individual;
                    }

                    actualizarTotal(data);
                    updateCartCount(data.cart_count);
                    showToast('Cantidad actualizada.');

                    if (data.cart_count === 0) {
                        document.querySelector('#carrito-items').innerHTML =
                            '<p class="text-gray-900 dark:text-white">No tienes productos en tu carrito.</p>';
                    }
                }
            });
        });
    });

    function actualizarTotal(data) {
        const totalSpan = document.getElementById('checkout-total');
        if (totalSpan && data.total_formateado) {
            totalSpan.textContent = data.total_formateado;
            totalSpan.setAttribute('data-total', data.total_raw);
        }
    }

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
