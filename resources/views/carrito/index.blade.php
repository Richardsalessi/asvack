@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-6xl p-4 sm:p-6">
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
                <div class="cart-item flex flex-col md:flex-row md:justify-between md:items-center gap-4 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md" id="cart-item-{{ $id }}">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full">
                        {{-- SOLO MINIATURAS EN CARRUSEL --}}
                        <div class="w-40 sm:w-48 md:w-56" data-thumbs="cart-{{ $id }}">
                            @php
                                $imgs = $producto['imagenes'] ?? [];
                                $thumbCount = max(count($imgs), 1);
                            @endphp

                            <div class="relative">
                                @if($thumbCount > 1)
                                <button type="button"
                                    class="thumb-prev absolute -left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/60 text-white grid place-items-center z-10"
                                    aria-label="Anterior">‹</button>
                                @endif

                                <div class="thumbs-track flex gap-2 overflow-x-auto scroll-smooth no-scrollbar {{ $thumbCount > 1 ? 'px-8' : 'justify-center' }}"
                                     data-thumbs-track>
                                    @forelse($imgs as $i => $src)
                                        <img
                                            src="{{ $src }}"
                                            data-index="{{ $i }}"
                                            alt="Miniatura {{ $i+1 }} de {{ $producto['nombre'] }}"
                                            class="thumb-item w-14 h-14 md:w-16 md:h-16 object-cover rounded border border-gray-300 dark:border-gray-700 cursor-pointer shrink-0">
                                    @empty
                                        <img
                                            src="{{ $producto['imagen'] }}"
                                            data-index="0"
                                            alt="Miniatura de {{ $producto['nombre'] }}"
                                            class="thumb-item w-14 h-14 md:w-16 md:h-16 object-cover rounded border border-gray-300 dark:border-gray-700 cursor-pointer shrink-0">
                                    @endforelse
                                </div>

                                @if($thumbCount > 1)
                                <button type="button"
                                    class="thumb-next absolute -right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/60 text-white grid place-items-center z-10"
                                    aria-label="Siguiente">›</button>
                                @endif
                            </div>
                        </div>

                        {{-- Detalles --}}
                        <div class="cart-item-details flex flex-col gap-2 flex-1" id="detalle-{{ $id }}">
                            <div class="cart-item-name font-semibold text-gray-900 dark:text-white leading-snug">{{ $producto['nombre'] }}</div>
                            <div class="cart-item-price text-gray-700 dark:text-gray-300">Precio: ${{ number_format($producto['precio'], 2, ',', '.') }}</div>

                            <div class="cart-item-quantity text-gray-600 dark:text-gray-300 flex items-center gap-3">
                                <span>Cantidad:</span>
                                <span class="cantidad-text font-semibold text-gray-900 dark:text-white">{{ $producto['cantidad'] }}</span>

                                {{-- Botón "–" accesible y con alto contraste (modo claro/oscuro) --}}
                                <button
                                    class="dec-btn w-9 h-9 md:w-9 md:h-9 rounded-full grid place-items-center border shadow-sm
                                           bg-gray-200 text-gray-900 border-gray-300 hover:bg-gray-300 active:scale-95
                                           dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600
                                           focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                                    data-id="{{ $id }}"
                                    aria-label="Quitar una unidad"
                                    title="Quitar una unidad">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M5 12h14" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="cart-item-total text-gray-900 dark:text-white font-bold">
                                Total: $<span class="total-individual">{{ number_format($producto['total'], 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Acciones (solo eliminar) --}}
                    <div class="cart-item-actions flex items-center justify-end md:justify-end mt-2 md:mt-0 w-full md:w-auto">
                        <button class="w-full sm:w-auto bg-red-500 text-white py-2 px-5 rounded-md hover:bg-red-600 transition duration-200 shadow-sm delete-button" data-id="{{ $id }}">
                            Eliminar
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div id="checkout-section" class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div>
                <strong class="text-xl text-gray-900 dark:text-white">Total:</strong>
                <span id="checkout-total" class="text-lg text-gray-900 dark:text-white" data-total="{{ $total }}">
                    ${{ number_format($total, 2, ',', '.') }}
                </span>
            </div>
            <a id="checkout-btn"
               href="{{ route('checkout') }}"
               class="w-full sm:w-auto md:min-w-[280px] md:max-w-[360px] md:self-center md:ml-auto text-center px-6 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-700 transition duration-300">
                Proceder a la compra
            </a>
        </div>

    @else
        <p class="text-gray-900 dark:text-white">No tienes productos en tu carrito.</p>
    @endif
</div>

{{-- Modal global para ver imágenes en grande --}}
<div id="imageModal" class="fixed inset-0 bg-black/75 items-center justify-center z-50 hidden">
    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden w-11/12 max-w-3xl">
    <button id="closeModal" class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-8 h-8 grid place-items-center">&times;</button>
    <img id="modalImage" src="" class="w-full h-[60vh] sm:h-[70vh] object-contain p-4">
    <button id="modalPrev" class="absolute top-1/2 left-2 sm:left-4 -translate-y-1/2 bg-gray-700 text-white rounded-full w-8 h-8 grid place-items-center hidden">&#8249;</button>
    <button id="modalNext" class="absolute top-1/2 right-2 sm:right-4 -translate-y-1/2 bg-gray-700 text-white rounded-full w-8 h-8 grid place-items-center hidden">&#8250;</button>
    </div>
</div>

<div id="toast" class="fixed bottom-5 right-5 bg-green-500 text-white p-3 rounded-md shadow-lg opacity-0 transition-opacity duration-300" style="z-index: 9999;">
    Producto eliminado del carrito.
</div>
@endsection

@section('scripts')
<style>
/* oculta scrollbars horizontales de las miniaturas (opcional) */
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<script>
/* Tu JS original tal cual */
document.addEventListener('DOMContentLoaded', function () {
    const checkoutSection = document.getElementById('checkout-section')
        || document.getElementById('checkout-total')?.closest('.mt-6')
        || document.getElementById('checkout-total')?.parentElement?.parentElement;
    const checkoutTotal   = document.getElementById('checkout-total');
    const deleteButtons   = document.querySelectorAll('.delete-button');

    function toggleCheckoutUI(cartCount) {
        if (cartCount === 0) {
            checkoutSection?.classList.add('hidden');
            if (checkoutTotal) {
                checkoutTotal.textContent = '$0,00';
                checkoutTotal.setAttribute('data-total', '0');
            }
            const cont = document.querySelector('#carrito-items');
            if (cont) cont.innerHTML = '<p class="text-gray-900 dark:text-white">No tienes productos en tu carrito.</p>';
        } else {
            checkoutSection?.classList.remove('hidden');
        }
    }

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
                    toggleCheckoutUI(data.cart_count);
                }
            });
        });
    });

    document.querySelectorAll('.dec-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const productId = this.getAttribute('data-id');
            this.disabled = true;

            fetch(`/carrito/quitar/${productId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ cantidad: 1 })
            })
            .then(res => res.json())
            .then(data => {
                this.disabled = false;

                if (!data.success) return;

                if (data.removido) {
                    document.getElementById(`cart-item-${productId}`)?.remove();
                } else {
                    const card = document.querySelector(`#detalle-${productId}`);
                    if (card) {
                        card.querySelector('.cantidad-text').textContent = data.nueva_cantidad;
                        card.querySelector('.total-individual').textContent = data.total_individual;
                    }
                }

                actualizarTotal(data);
                updateCartCount(data.cart_count);
                showToast(data.removido ? 'Producto eliminado del carrito.' : 'Cantidad actualizada.');
                toggleCheckoutUI(data.cart_count);
            })
            .catch(() => { this.disabled = false; });
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

    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const btnClose = document.getElementById('closeModal');
    const btnPrev  = document.getElementById('modalPrev');
    const btnNext  = document.getElementById('modalNext');

    let currentImages = [];
    let currentIndex  = 0;

    const openModal = (src, images = []) => {
        currentImages = images;
        currentIndex  = Math.max(0, images.indexOf(src));
        modalImage.src = src;
        btnPrev.classList.toggle('hidden', currentImages.length <= 1);
        btnNext.classList.toggle('hidden', currentImages.length <= 1);
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };
    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        currentImages = [];
    };
    btnClose?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', (e) => {
        if (modal.classList.contains('hidden')) return;
        if (e.key === 'Escape') closeModal();
        if (e.key === 'ArrowLeft')  showByIndex(currentIndex - 1);
        if (e.key === 'ArrowRight') showByIndex(currentIndex + 1);
    });
    const showByIndex = (i) => {
        if (!currentImages.length) return;
        currentIndex = (i + currentImages.length) % currentImages.length;
        modalImage.src = currentImages[currentIndex];
    };
    btnPrev?.addEventListener('click', (e)=>{ e.stopPropagation(); showByIndex(currentIndex - 1); });
    btnNext?.addEventListener('click', (e)=>{ e.stopPropagation(); showByIndex(currentIndex + 1); });

    document.querySelectorAll('[data-thumbs]').forEach((wrap) => {
        const track = wrap.querySelector('[data-thumbs-track]');
        const btnP  = wrap.querySelector('.thumb-prev');
        const btnN  = wrap.querySelector('.thumb-next');
        const thumbs = Array.from(wrap.querySelectorAll('.thumb-item'));
        const imagesSrc = thumbs.map(t => t.src);

        thumbs.forEach((t) => {
            t.addEventListener('click', (e) => {
                e.preventDefault();
                openModal(t.src, imagesSrc);
            });
        });

        if (thumbs.length <= 1) {
            btnP && (btnP.style.display = 'none');
            btnN && (btnN.style.display = 'none');
        } else {
            const step = 120;
            btnP?.addEventListener('click', () => track.scrollBy({ left: -step, behavior: 'smooth' }));
            btnN?.addEventListener('click', () => track.scrollBy({ left:  step, behavior: 'smooth' }));
        }
    });
});
</script>
@endsection
