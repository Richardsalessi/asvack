@extends('layouts.app')

@section('content')
<div class="py-8">
    <!-- Título principal -->
    <div class="text-center mb-8">
        <h1 class="text-5xl font-extrabold text-gray-900 dark:text-white mb-4">Catálogo de Productos</h1>
        <p class="text-2xl text-gray-600 dark:text-gray-300">Explora los componentes de calidad que tenemos para ti.</p>
    </div>

    <!-- Filtros -->
    <div class="container mx-auto mb-8 px-4 flex flex-wrap justify-center gap-4">
    <!-- Filtro de Categorías -->
    <select id="categoriaFiltro" class="w-48 bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-200 rounded-lg p-2">
        <option value="todos">Todas las Categorías</option>
        @foreach($categorias as $categoria)
            <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
        @endforeach
    </select>

    <!-- Filtro de Precios -->
    <select id="precioFiltro" class="w-48 bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-200 rounded-lg p-2">
        <option value="">Ordenar por Precio</option>
        <option value="menor">Menor a Mayor</option>
        <option value="mayor">Mayor a Menor</option>
    </select>
</div>

            <!-- Sección de productos -->
            <div id="productos-container" class="container mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 items-stretch">
                @forelse ($productos as $producto)
            @include('components.producto-card', ['producto' => $producto])
        @empty
            <p class="text-center text-gray-700 dark:text-gray-300 col-span-3">No hay productos disponibles en esta categoría.</p>
        @endforelse
    </div>
</div>

<!-- Modal para mostrar la imagen ampliada -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 items-center justify-center z-50" style="display: none;">
    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden w-11/12 max-w-3xl mx-auto mt-20" style="user-select: none;">
        <button id="closeModal" class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center z-30" style="user-select: none;">&times;</button>
        <img id="modalImage" src="" alt="Imagen ampliada del producto" class="w-full object-contain p-4 opacity-0 transition-opacity duration-1000 z-10">
        <button id="modalPrev" class="absolute top-1/2 left-4 transform -translate-y-1/2 bg-gray-700 text-white rounded-full w-8 h-8 flex items-center justify-center z-20" style="display: none; user-select: none;">&#8249;</button>
        <button id="modalNext" class="absolute top-1/2 right-4 transform -translate-y-1/2 bg-gray-700 text-white rounded-full w-8 h-8 flex items-center justify-center z-20" style="display: none; user-select: none;">&#8250;</button>
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

        // Auto-rotation for product images
        function startAutoRotate(sliderImages) {
            stopAutoRotate();
            autoRotateInterval = setInterval(() => {
                changeImage('next', sliderImages);
            }, 3000);
        }

        function stopAutoRotate() {
            clearInterval(autoRotateInterval);
        }

        sliders.forEach(slider => {
            let currentIndex = 0;
            const images = slider.querySelectorAll('.slider-image');
            const prevButton = slider.querySelector('.prev-button');
            const nextButton = slider.querySelector('.next-button');

            if (images.length > 1) {
                startAutoRotate(images);

                nextButton?.addEventListener('click', () => {
                    changeImage('next', images);
                });

                prevButton?.addEventListener('click', () => {
                    changeImage('prev', images);
                });
            }

            slider.addEventListener('click', function (event) {
                if (!event.target.classList.contains('prev-button') && !event.target.classList.contains('next-button')) {
                    currentSlider = slider;
                    currentSliderImages = images;
                    const visibleImage = Array.from(images).findIndex(image => image.classList.contains('opacity-100'));
                    currentModalIndex = visibleImage !== -1 ? visibleImage : 0;

                    showImageInModal(currentModalIndex);

                    stopAutoRotate();

                    if (currentSliderImages.length > 1) {
                        modalPrevButton.style.display = 'block';
                        modalNextButton.style.display = 'block';
                    } else {
                        modalPrevButton.style.display = 'none';
                        modalNextButton.style.display = 'none';
                    }

                    modal.style.display = 'flex';
                }
            });
        });

        function changeImage(direction, images) {
            images[currentModalIndex].classList.remove('opacity-100');
            images[currentModalIndex].classList.add('opacity-0');

            currentModalIndex = (direction === 'next') ? (currentModalIndex + 1) % images.length : (currentModalIndex - 1 + images.length) % images.length;
            images[currentModalIndex].classList.remove('opacity-0');
            images[currentModalIndex].classList.add('opacity-100');

            if (modal.style.display === 'flex') {
                showImageInModal(currentModalIndex);
            }
        }

        closeModalButton.addEventListener('click', function () {
            modal.style.display = 'none';
            startAutoRotate(currentSliderImages);
        });

        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.style.display = 'none';
                startAutoRotate(currentSliderImages);
            }
        });

        modalNextButton.addEventListener('click', function () {
            changeImage('next', currentSliderImages);
        });

        modalPrevButton.addEventListener('click', function () {
            changeImage('prev', currentSliderImages);
        });

        function showImageInModal(index) {
            modalImage.classList.remove('opacity-100');
            modalImage.classList.add('opacity-0');
            modalImage.src = currentSliderImages[index].src;
            setTimeout(() => {
                modalImage.classList.remove('opacity-0');
                modalImage.classList.add('opacity-100');
            }, 10);
        }

        // AJAX request to add to cart without page reload
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(form);
                const formAction = form.action;

                fetch(formAction, {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(); // Show the toast notification
                        updateCartCount(data.cart_count);  // Update the cart count dynamically in the navbar
                    else {
                        showToastGlobal('error', data.message);
                    }

                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });
    });

</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const categoriaFiltro = document.getElementById('categoriaFiltro');
    const precioFiltro = document.getElementById('precioFiltro');
    const contenedor = document.getElementById('productos-container');

    function renderProductos(productos) {
    const contenedor = document.getElementById('productos-container');
    const token = document.querySelector('meta[name=csrf-token]').getAttribute('content');
    const isLoggedIn = document.querySelector('meta[name=user-authenticated]').getAttribute('content') === 'true';

    contenedor.innerHTML = '';

    if (productos.length === 0) {
        contenedor.innerHTML = '<p class="text-center text-gray-700 dark:text-gray-300 col-span-3">No hay productos disponibles.</p>';
        return;
    }

    // 🔁 ESTILO UNIFICADO COMO EN WELCOME Y CATÁLOGO (modo oscuro, botones, tarjetas)
    productos.forEach(producto => {
    const imagen = producto.imagenes.length > 0
        ? `data:image/png;base64,${producto.imagenes[0].contenido}`
        : '/storage/placeholder.png';

        contenedor.innerHTML += `
        <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-105 flex flex-col text-center h-full">
            <div class="h-64 w-full mb-4 overflow-hidden relative flex items-center justify-center">
                <img src="${imagen}" alt="Imagen de ${producto.nombre}" class="object-contain max-h-full" style="user-select: none;">
            </div>

            <div class="flex flex-col justify-start min-h-[230px]">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-1">${producto.nombre}</h2>
                <p class="text-lg font-bold text-gray-900 dark:text-white mb-1">Especificaciones técnicas:</p>
                <p class="text-gray-900 dark:text-white text-sm line-clamp-4 leading-relaxed">${producto.descripcion}</p>
            </div>

            <div class="mt-4">
                <p class="text-gray-900 dark:text-white font-bold text-lg"><strong>Precio:</strong> $${new Intl.NumberFormat('es-CO').format(producto.precio)}</p>
                <p class="text-gray-900 dark:text-white mb-2"><strong>Unidades disponibles:</strong> ${producto.stock}</p>
            </div>

            <form method="POST" action="/carrito/agregar/${producto.id}" class="add-to-cart-form mt-auto w-full">
                <input type="hidden" name="_token" value="${token}">
                <label class="block text-sm font-semibold text-gray-900 dark:text-white">Cantidad</label>
                <input type="number" name="cantidad" value="1" min="1" max="${producto.stock}" class="w-16 p-2 border rounded-md text-center cantidad-input bg-white text-black dark:bg-gray-800 dark:text-white dark:border-gray-700" required>

                ${isLoggedIn
                    ? `<button type="submit" class="btn-agregar-carrito">Agregar al carrito</button>`
                    : `<button type="button" onclick="window.location.href='/login'" class="btn-agregar-carrito">Inicia sesión para comprar</button>`
                }
            </form>
        </div>
    `;
});


    volverAVincularFormularios();
}

    function volverAVincularFormularios() {
        const forms = document.querySelectorAll('.add-to-cart-form');

        forms.forEach(form => {
            form.addEventListener('submit', function (event) {
                event.preventDefault();

                const formData = new FormData(form);
                const action = form.action;

                fetch(action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToastGlobal('success', 'Producto agregado al carrito.');
                        updateCartCount(data.cart_count);
                    } else {
                        showToastGlobal('error', data.message || 'Error al agregar.');
                    }
                })
                .catch(() => {
                    showToastGlobal('error', 'Error al conectar con el servidor.');
                });
            });
        });
    }


    async function filtrarProductos() {
        const categoria = categoriaFiltro.value;
        const precio = precioFiltro.value;

        try {
            const response = await fetch(`/api/catalogo/filtrar?categoria=${categoria}&precio=${precio}`);
            const data = await response.json();
            renderProductos(data);
        } catch (error) {
            console.error('Error al filtrar productos:', error);
        }
    }

    categoriaFiltro.addEventListener('change', filtrarProductos);
    precioFiltro.addEventListener('change', filtrarProductos);
});
</script>

@endsection