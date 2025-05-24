@extends('layouts.app')

@section('content')
<div class="py-8">
    <!-- Logo de la empresa -->
    <div class="text-center mb-8">
        <img src="{{ asset('images/logo-inicio-negro.webp') }}" alt="Logo de Asvack" class="mx-auto w-48 dark:hidden" style="user-select: none;" draggable="false">
        <img src="{{ asset('images/logo-inicio-blanco.webp') }}" alt="Logo de Asvack" class="mx-auto w-48 hidden dark:block" style="user-select: none;" draggable="false">
    </div>

    <!-- Título principal -->
    <div class="text-center mb-8">
        <h1 class="text-5xl font-extrabold text-gray-900 dark:text-white mb-4">Bienvenido a Asvack</h1>
        <p class="text-2xl text-gray-600 dark:text-gray-300">Componentes de calidad para todos tus equipos.</p>
    </div>

    <!-- Video como banner extendido al ancho total -->
    <div class="relative w-screen left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] mb-8">
        <video autoplay loop muted class="w-full object-cover h-[500px] rounded-md shadow-lg">
            <source src="{{ asset('videos/Video.mp4') }}" type="video/mp4">
            Tu navegador no soporta la reproducción de videos.
        </video>
    </div>

    <!-- Título para la sección de algunos productos -->
    <div class="text-center mt-16 mb-8">
        <h2 class="text-4xl font-bold text-gray-900 dark:text-white">Algunos de Nuestros Productos</h2>
        <p class="text-lg text-gray-600 dark:text-gray-300">Descubre una selección de nuestros mejores productos.</p>
    </div>

            <!-- Sección de productos aleatorios -->
            <div class="container mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 items-stretch">
                @foreach ($productosAleatorios as $producto)
            <x-producto-card :producto="$producto" />
        @endforeach
    </div>
</div>

<!-- TOAST ÚNICO REUTILIZABLE -->
<div id="toast-global" class="fixed bottom-5 right-5 text-white p-4 rounded-md shadow-lg opacity-0 transition-opacity duration-300 z-50 flex items-center gap-2"
    style="min-width: 300px;">
    <span id="toast-icon">✅</span>
    <span id="toast-text">Mensaje genérico</span>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const scrollToTopBtn = document.getElementById("scrollToTopBtn");

    window.addEventListener("scroll", function () {
        if (window.scrollY > 200) {
            scrollToTopBtn.classList.remove("opacity-0", "pointer-events-none");
            scrollToTopBtn.classList.add("opacity-100");
        } else {
            scrollToTopBtn.classList.add("opacity-0", "pointer-events-none");
        }
    });

    scrollToTopBtn.addEventListener("click", function () {
        window.scrollTo({ top: 0, behavior: "smooth" });
    });
});
</script>

<style>
/* Asegurar que el botón sea completamente clickeable */
#scrollToTopBtn {
    position: fixed;
    bottom: 80px; /* Distancia desde la parte inferior */
    right: 25px; /* Distancia desde la derecha */
    display: flex;
    align-items: center;
    justify-content: center;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background-color: #4338ca;
    color: white;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: opacity 0.3s ease-in-out, transform 0.2s;
    font-size: 24px;
    z-index: 1000; /* Asegurar que esté por encima de otros elementos */
    pointer-events: auto; /* Garantizar que sea clickeable */
}

/* Asegurar que el botón sea totalmente clickeable */
#scrollToTopBtn::before {
    content: "";
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
}

#scrollToTopBtn:hover {
    background-color: #3730a3;
    transform: scale(1.1);
}

#scrollToTopBtn:active {
    transform: scale(0.9);
}
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.add-to-cart-form');

    forms.forEach(form => {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            // Verificar si el usuario está autenticado usando una variable de Blade
            let isAuthenticated = {{ Auth::check() ? 'true' : 'false' }};

            if (!isAuthenticated) {
                // Redirigir al login si no está autenticado
                window.location.href = "{{ route('login') }}";
                return;
            }

            // Si está autenticado, procesar la solicitud normalmente
            const formData = new FormData(form);
            fetch(form.action, {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToastGlobal('success', 'Producto agregado al carrito.');
                        updateCartCount(data.cart_count);
                    } else {
                        showToastGlobal('error', data.message);
                    }
                })
                
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
});

        function showToastGlobal(type, message) {
        const toast = document.getElementById('toast-global');
        const icon = document.getElementById('toast-icon');
        const text = document.getElementById('toast-text');

        if (type === 'success') {
            toast.classList.remove('bg-red-600');
            toast.classList.add('bg-green-600');
            icon.textContent = '✅';
        } else {
            toast.classList.remove('bg-green-600');
            toast.classList.add('bg-red-600');
            icon.textContent = '⚠️';
        }

        text.textContent = message;

        toast.classList.remove('opacity-0');
        toast.classList.add('opacity-100');

        setTimeout(() => {
            toast.classList.remove('opacity-100');
            toast.classList.add('opacity-0');
        }, 3500);
    }

    function updateCartCount(count) {
        const cartCount = document.querySelector('#cart-count');
        if (cartCount) {
            cartCount.innerText = count;
        }
    }

</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sliders = document.querySelectorAll('.slider');
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');
        const closeModalButton = document.getElementById('closeModal');
        const modalPrevButton = document.getElementById('modalPrev');
        const modalNextButton = document.getElementById('modalNext');
        let currentModalIndex = 0;
        let currentSliderImages = [];
        let currentSlider = null;
        let autoRotateInterval;

        // Update cart count dynamically
        function updateCartCount(count) {
            const cartCount = document.querySelector('#cart-count');
            if (cartCount) {
                cartCount.innerText = count; // Update the cart count in the navbar
            }
        } 
        // Display toast notification
        function showToast() {
            const toast = document.getElementById('toast');
            toast.classList.remove('opacity-0');
            toast.classList.add('opacity-100');
            setTimeout(() => {
                toast.classList.remove('opacity-100');
                toast.classList.add('opacity-0');
            }, 3000);
        }
</script>
@endsection
