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

    <!-- Sección de productos (SSR inicial) -->
    <div id="productos-container" class="container mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 items-stretch">
        @forelse ($productos as $producto)
            @include('components.producto-card', ['producto' => $producto])
        @empty
            <p class="text-center text-gray-700 dark:text-gray-300 col-span-3">No hay productos disponibles en esta categoría.</p>
        @endforelse
    </div>
</div>

<!-- Modal imagen global (ya lo usas en Welcome) -->
@include('components.modal-imagen')

<!-- TOAST ÚNICO REUTILIZABLE -->
<div id="toast-global" class="fixed bottom-5 right-5 text-white p-4 rounded-md shadow-lg opacity-0 transition-opacity duration-300 z-50 flex items-center gap-2" style="min-width: 300px;">
    <span id="toast-icon">✅</span>
    <span id="toast-text">Mensaje genérico</span>
</div>

<script>
/* -------- utilidades toast + carrito (no tocar) -------- */
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
    if (cartCount) cartCount.innerText = count;
}
</script>

<style>
/* Botón subir */
#scrollToTopBtn {
    position: fixed; bottom: 80px; right: 25px; display: flex; align-items: center; justify-content: center;
    width: 56px; height: 56px; border-radius: 50%; background-color: #4338ca; color: white;
    box-shadow: 0 4px 6px rgba(0,0,0,.1); cursor: pointer; transition: opacity .3s, transform .2s; font-size: 24px; z-index: 1000;
}
#scrollToTopBtn:hover { background-color: #3730a3; transform: scale(1.1); }
#scrollToTopBtn:active { transform: scale(.9); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    /* ===== MODAL GLOBAL ===== */
    const modal      = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const btnClose   = document.getElementById('closeModal');
    const btnPrev    = document.getElementById('modalPrev');
    const btnNext    = document.getElementById('modalNext');

    let currentSliderImages = []; // <img> lista de un slider
    let currentIndex = 0;         // índice visible en modal

    const openModal = (src, imagesList = []) => {
        currentSliderImages = imagesList;
        modalImage.style.opacity = 0;
        modalImage.src = src || '';
        modal.style.display = 'flex';
        requestAnimationFrame(() => modalImage.style.opacity = 1);
        // mostrar flechas sólo si hay varias
        const many = currentSliderImages.length > 1;
        btnPrev.style.display = many ? 'flex' : 'none';
        btnNext.style.display = many ? 'flex' : 'none';
    };
    const closeModal = () => {
        modal.style.display = 'none';
        modalImage.src = '';
        currentSliderImages = [];
    };
    const showByIndex = (i) => {
        if (!currentSliderImages.length) return;
        currentIndex = (i + currentSliderImages.length) % currentSliderImages.length;
        modalImage.style.opacity = 0;
        modalImage.src = currentSliderImages[currentIndex].src;
        requestAnimationFrame(() => modalImage.style.opacity = 1);
    };

    btnClose?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', (e) => {
        if (modal.style.display !== 'flex') return;
        if (e.key === 'Escape') closeModal();
        if (e.key === 'ArrowLeft')  showByIndex(currentIndex - 1);
        if (e.key === 'ArrowRight') showByIndex(currentIndex + 1);
    });
    btnPrev?.addEventListener('click', (e) => { e.stopPropagation(); showByIndex(currentIndex - 1); });
    btnNext?.addEventListener('click', (e) => { e.stopPropagation(); showByIndex(currentIndex + 1); });

    /* ===== CARRUSEL: inicializa cualquier tarjeta con [data-slider] ===== */
    function initCatalogCarousels(scope) {
        (scope || document).querySelectorAll('[data-slider]').forEach((slider) => {
            // evita doble-binding
            if (slider.dataset.bound === '1') return;
            slider.dataset.bound = '1';

            const slides = slider.querySelectorAll('.slide');
            const dots   = slider.querySelectorAll('.dot');
            const prev   = slider.querySelector('.prev');
            const next   = slider.querySelector('.next');
            const thumbs = slider.parentElement.querySelectorAll('.thumb'); // miniaturas bajo el slider
            let idx = 0;

            // auto-rotación (por slider)
            let timer = null;
            const start = () => {
                stop();
                if (slides.length > 1) timer = setInterval(() => { idx = (idx + 1) % slides.length; paint(idx); }, 3500);
            };
            const stop = () => { if (timer) clearInterval(timer); timer = null; };

            const paint = (i) => {
                slides.forEach((s, k) => s.classList.toggle('hidden', k !== i));
                dots.forEach((d, k) => d.className = 'dot w-2.5 h-2.5 rounded-full ' + (k === i ? 'bg-white' : 'bg-white/50'));
                thumbs.forEach((t, k) => {
                    t.classList.toggle('ring-2', k === i);
                    t.classList.toggle('ring-blue-500', k === i);
                    if (k === i) t.classList.remove('border-gray-300','dark:border-gray-700');
                    else t.classList.add('border-gray-300','dark:border-gray-700');
                });
            };

            prev?.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); idx = (idx - 1 + slides.length) % slides.length; paint(idx); start(); });
            next?.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); idx = (idx + 1) % slides.length; paint(idx); start(); });

            dots.forEach((d, k) => d.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); idx = k; paint(idx); start(); }));
            thumbs.forEach((t) => t.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); idx = parseInt(t.dataset.index,10)||0; paint(idx); start(); }));

            // abrir modal al hacer click en la imagen grande
            slider.addEventListener('click', (e) => {
                const img = e.target.closest('img.slide');
                if (!img) return;
                const imgsList = Array.from(slides);
                currentIndex = idx;
                openModal(imgsList[idx].src, imgsList);
                stop(); // pausa auto-rotación mientras está el modal
            });

            // al cerrar modal, reanudar
            btnClose?.addEventListener('click', start);
            modal?.addEventListener('click', (e) => { if (e.target === modal) start(); });

            paint(0);
            start();
        });
    }

    /* ===== Formularios "agregar al carrito" (dinámicos o SSR) ===== */
    function bindCartForms(scope) {
        (scope || document).querySelectorAll('.add-to-cart-form').forEach(form => {
            // evitar duplicados
            if (form.dataset.bound === '1') return;
            form.dataset.bound = '1';

            form.addEventListener('submit', function (event) {
                event.preventDefault();

                // Si no hay auth, redirige (el botón SSR ya lo hace, pero por si llega aquí)
                const isAuthenticated = "{{ Auth::check() ? 'true' : 'false' }}" === 'true';
                if (!isAuthenticated) { window.location.href = "{{ route('login') }}"; return; }

                fetch(form.action, { method: 'POST', body: new FormData(form) })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            showToastGlobal('success', 'Producto agregado al carrito.');
                            updateCartCount(data.cart_count);
                        } else {
                            showToastGlobal('error', data.message || 'Error al agregar.');
                        }
                    })
                    .catch(() => showToastGlobal('error', 'Error al conectar con el servidor.'));
            });
        });
    }

    /* ===== Render dinámico tras filtrar ===== */
    const categoriaFiltro = document.getElementById('categoriaFiltro');
    const precioFiltro    = document.getElementById('precioFiltro');
    const contenedor      = document.getElementById('productos-container');

    function tarjetaProductoHTML(p) {
        // Construye el carrusel igual que la tarjeta SSR
        const tieneImgs = Array.isArray(p.imagenes) && p.imagenes.length > 0;
        let slides = '', dots = '', thumbs = '';
        if (tieneImgs) {
            p.imagenes.forEach((im, i) => {
                slides += `<img src="data:image/jpeg;base64,${im.contenido}" alt="Imagen de ${p.nombre}" class="slide ${i===0?'':'hidden'} absolute inset-0 w-full h-full object-contain cursor-pointer" draggable="false">`;
                dots   += `<span class="dot w-2.5 h-2.5 rounded-full ${i===0?'bg-white':'bg-white/50'}"></span>`;
                thumbs += `<img src="data:image/jpeg;base64,${im.contenido}" alt="Miniatura ${i+1} de ${p.nombre}" class="thumb w-14 h-14 object-cover rounded-md border ${i===0?'ring-2 ring-blue-500 border-transparent':'border-gray-300 dark:border-gray-700'} cursor-pointer shrink-0" data-index="${i}" draggable="false">`;
            });
        }

        return `
        <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-105 flex flex-col text-center h-full">
            <div class="w-full mb-4 select-none">
                ${
                    tieneImgs
                    ? `
                    <div class="relative h-64 w-full group rounded-md bg-white/40 dark:bg-black/20 overflow-hidden" data-slider>
                        ${slides}
                        ${p.imagenes.length>1 ? `
                            <button type="button" class="prev absolute left-2 top-1/2 -translate-y-1/2 rounded-full w-8 h-8 bg-black/60 text-white text-lg grid place-items-center opacity-0 group-hover:opacity-100 transition" aria-label="Anterior">‹</button>
                            <button type="button" class="next absolute right-2 top-1/2 -translate-y-1/2 rounded-full w-8 h-8 bg-black/60 text-white text-lg grid place-items-center opacity-0 group-hover:opacity-100 transition" aria-label="Siguiente">›</button>
                            <div class="dots absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-1">${dots}</div>
                        `:''}
                    </div>
                    ${p.imagenes.length>1 ? `<div class="mt-3 flex gap-2 justify-center overflow-x-auto px-1">${thumbs}</div>` : ''}
                    `
                    : `
                    <div class="relative h-64 w-full rounded-md bg-white/40 dark:bg-black/20 overflow-hidden grid place-items-center">
                        <img src="/storage/placeholder.png" alt="Imagen de ${p.nombre}" class="max-h-full object-contain" draggable="false">
                    </div>
                    `
                }
            </div>

            <div class="flex flex-col justify-start min-h-[230px]">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-1">${p.nombre}</h2>
                <p class="text-lg font-bold text-gray-900 dark:text-white mb-1">Especificaciones técnicas:</p>
                <p class="text-gray-900 dark:text-white text-sm line-clamp-4 leading-relaxed">${p.descripcion}</p>
            </div>

            <div class="mt-4">
                <p class="text-gray-900 dark:text-white font-bold text-lg"><strong>Precio:</strong> $${new Intl.NumberFormat('es-CO').format(p.precio)}</p>
                <p class="text-gray-900 dark:text-white mb-2"><strong>Unidades disponibles:</strong> ${p.stock}</p>
            </div>

            <form method="POST" action="/carrito/agregar/${p.id}" class="add-to-cart-form mt-auto w-full">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <label class="block text-sm font-semibold text-gray-900 dark:text-white">Cantidad</label>
                <input type="number" name="cantidad" value="1" min="1" max="${p.stock}" class="w-16 p-2 border rounded-md text-center cantidad-input bg-white text-black dark:bg-gray-800 dark:text-white dark:border-gray-700" required>
                @if(Auth::check())
                    <button type="submit" class="btn-agregar-carrito">Agregar al carrito</button>
                @else
                    <button type="button" onclick="window.location.href='{{ route('login') }}'" class="btn-agregar-carrito">Inicia sesión para comprar</button>
                @endif
            </form>
        </div>
        `;
    }

    async function filtrarProductos() {
        const categoria = categoriaFiltro.value;
        const precio = precioFiltro.value;

        try {
            const res = await fetch(`/api/catalogo/filtrar?categoria=${categoria}&precio=${precio}`);
            const data = await res.json(); // => [{ id, nombre, descripcion, precio, stock, imagenes:[{contenido}, ...] }]
            contenedor.innerHTML = '';

            if (!Array.isArray(data) || data.length === 0) {
                contenedor.innerHTML = '<p class="text-center text-gray-700 dark:text-gray-300 col-span-3">No hay productos disponibles.</p>';
                return;
            }

            // Render con carrusel
            const html = data.map(tarjetaProductoHTML).join('');
            contenedor.innerHTML = html;

            // Re-vincular sólo en lo recién pintado
            initCatalogCarousels(contenedor);
            bindCartForms(contenedor);
        } catch (err) {
            console.error('Error al filtrar productos:', err);
            showToastGlobal('error', 'No se pudo actualizar el catálogo.');
        }
    }

    categoriaFiltro.addEventListener('change', filtrarProductos);
    precioFiltro.addEventListener('change', filtrarProductos);

    // En la carga inicial (SSR), no tocamos las tarjetas incluidas por Blade,
    // pero si existieran dinámicas, esta llamada no rompe nada:
    initCatalogCarousels();
    bindCartForms();
});
</script>
@endsection
