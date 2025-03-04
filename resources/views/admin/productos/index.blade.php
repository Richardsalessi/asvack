@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-4xl font-bold mb-6 text-gray-900 dark:text-white">Lista de Productos</h1>
    <a href="{{ route('admin.productos.create') }}" class="bg-green-500 hover:bg-green-700 text-white px-6 py-3 rounded mb-4 inline-block transition-all duration-300">Añadir Producto</a>

    <!-- Formulario de Filtros -->
    <form action="{{ route('admin.productos.index') }}" method="GET" class="mb-6 flex flex-wrap items-center gap-4 bg-gray-100 dark:bg-gray-700 p-4 rounded-lg shadow">
        <!-- Filtro por Categoría -->
        <div>
            <label for="categoria" class="block text-gray-700 dark:text-gray-300 font-bold">Categoría:</label>
            <select name="categoria" id="categoria" class="px-4 py-2 border rounded-lg text-gray-900 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 focus:ring-2 focus:ring-blue-500">
                <option value="">Todas</option>
                @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}" {{ request('categoria') == $categoria->id ? 'selected' : '' }}>
                        {{ $categoria->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Filtro por Precio -->
        <div>
            <label for="precio" class="block text-gray-700 dark:text-gray-300 font-bold">Ordenar por Precio:</label>
            <select name="precio" id="precio" class="px-4 py-2 border rounded-lg text-gray-900 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 focus:ring-2 focus:ring-blue-500">
                <option value="">Sin ordenar</option>
                <option value="asc" {{ request('precio') == 'asc' ? 'selected' : '' }}>Menor a mayor</option>
                <option value="desc" {{ request('precio') == 'desc' ? 'selected' : '' }}>Mayor a menor</option>
            </select>
        </div>

        <!-- Filtro por Stock -->
        <div>
            <label for="stock" class="block text-gray-700 dark:text-gray-300 font-bold">Ordenar por Stock:</label>
            <select name="stock" id="stock" class="px-4 py-2 border rounded-lg text-gray-900 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 focus:ring-2 focus:ring-blue-500">
                <option value="">Sin ordenar</option>
                <option value="asc" {{ request('stock') == 'asc' ? 'selected' : '' }}>Menor a mayor</option>
                <option value="desc" {{ request('stock') == 'desc' ? 'selected' : '' }}>Mayor a menor</option>
            </select>
        </div>

        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-all duration-300 mt-4">Aplicar Filtros</button>
    </form>

    <div class="overflow-x-auto mt-4">
        <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <thead class="bg-gray-100 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-gray-900 dark:text-gray-300">Imágenes</th>
                    <th class="px-6 py-3 text-left text-gray-900 dark:text-gray-300">Nombre</th>
                    <th class="px-6 py-3 text-left text-gray-900 dark:text-gray-300">Descripción</th>
                    <th class="px-6 py-3 text-left text-gray-900 dark:text-gray-300">Precio</th>
                    <th class="px-6 py-3 text-left text-gray-900 dark:text-gray-300">Categoría</th>
                    <th class="px-6 py-3 text-left text-gray-900 dark:text-gray-300">Stock</th>
                    <th class="px-6 py-3 text-left text-gray-900 dark:text-gray-300">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($productos as $producto)
                <tr class="border-b border-gray-200 dark:border-gray-700">
                    <td class="px-6 py-4 flex gap-2 items-center">
                        @foreach ($producto->imagenes->take(3) as $imagen)
                            <a href="#" class="open-modal" data-image-url="data:image/jpeg;base64,{{ $imagen->contenido }}">
                                <img src="data:image/jpeg;base64,{{ $imagen->contenido }}" alt="Imagen de {{ $producto->nombre }}" class="h-16 w-16 object-cover rounded-lg shadow-md hover:shadow-lg transition-all duration-300">
                            </a>
                        @endforeach
                    </td>

                    <td class="px-6 py-4 text-gray-900 dark:text-gray-300">{{ $producto->nombre }}</td>
                    <td class="px-6 py-4 text-gray-900 dark:text-gray-300">
                        <a href="#" class="open-desc-modal text-blue-500 hover:underline" data-description="{{ $producto->descripcion }}">Ver descripción</a>
                    </td>
                    <td class="px-6 py-4 text-gray-900 dark:text-gray-300">${{ number_format($producto->precio, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-gray-900 dark:text-gray-300">{{ $producto->categoria->nombre ?? 'Sin categoría' }}</td>
                    <td class="px-6 py-4 text-gray-900 dark:text-gray-300">{{ $producto->stock }}</td>
                    
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.productos.edit', $producto) }}" class="bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded transition-all duration-300 mr-2">Editar</a>
                        <form action="{{ route('admin.productos.destroy', $producto) }}" method="POST" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white px-4 py-2 rounded transition-all duration-300" onclick="return confirm('¿Estás seguro de eliminar este producto?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL PARA IMÁGENES -->
<div id="imageModal" class="hidden fixed inset-0 bg-black bg-opacity-50 justify-center items-center">
    <div class="relative bg-white dark:bg-gray-900 p-4 rounded-lg shadow-lg max-w-lg">
        <button id="closeImageModal" class="absolute top-2 right-2 text-gray-600 dark:text-gray-300 text-2xl font-bold hover:text-red-500">&times;</button>
        <img id="modalImage" src="" class="w-full h-auto rounded-lg no-select no-drag">
    </div>
</div>

<!-- MODAL PARA DESCRIPCIÓN -->
<div id="descModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
    <div class="relative bg-white dark:bg-gray-900 p-4 rounded-lg shadow-lg max-w-lg">
        <button id="closeDescModal" class="absolute top-2 right-2 text-gray-600 dark:text-gray-300 text-xl font-bold hover:text-red-500">&times;</button>
        <p id="descContent" class="text-gray-800 dark:text-gray-300"></p>
    </div>
</div>

<!-- Script para manejar los modales -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // MODAL DE IMÁGENES
        const imageModal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');
        const closeImageModal = document.getElementById('closeImageModal');

        document.querySelectorAll('.open-image-modal').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                modalImage.src = button.querySelector('img').src;
                imageModal.classList.remove('hidden');
            });
        });

        closeImageModal.addEventListener('click', function () {
            imageModal.classList.add('hidden');
        });

        imageModal.addEventListener('click', function (e) {
            if (e.target === imageModal) {
                imageModal.classList.add('hidden');
            }
        });

        // MODAL DE DESCRIPCIÓN
        const descModal = document.getElementById('descModal');
        const descContent = document.getElementById('descContent');
        const closeDescModal = document.getElementById('closeDescModal');

        document.querySelectorAll('.open-desc-modal').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                descContent.textContent = button.getAttribute('data-description');
                descModal.classList.remove('hidden');
            });
        });

        closeDescModal.addEventListener('click', function () {
            descModal.classList.add('hidden');
        });

        descModal.addEventListener('click', function (e) {
            if (e.target === descModal) {
                descModal.classList.add('hidden');
            }
        });
    });
</script>

<!-- Estilos para bloquear selección y arrastre de imágenes -->
<style>
    /* Evitar que la imagen sea seleccionable */
    .no-select {
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
    }

    /* Evitar que la imagen sea arrastrable */
    .no-drag {
        pointer-events: none;
        user-drag: none;
        -webkit-user-drag: none;
        -moz-user-drag: none;
        -ms-user-drag: none;
    }

    /* Ajustar el botón de cierre (X) */
    #closeImageModal, #closeDescModal {
        cursor: pointer;
        background: none;
        border: none;
        font-size: 24px;
    }

    /* Mejorar la visibilidad del modal */
    #imageModal img {
        max-width: 100%;
        max-height: 80vh;
        display: block;
        margin: auto;
    }

    
</style>

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
        const descModal = document.getElementById('descModal');
        const descModalContent = document.getElementById('descModalContent');
        const openDescModalButtons = document.querySelectorAll('.open-desc-modal');
        const descContent = document.getElementById('descContent');
        const closeDescModalButton = document.getElementById('closeDescModal');

        const imageModal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');
        const closeModalButton = document.getElementById('closeModal');

        // Abrir el modal de descripción
        openDescModalButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                descContent.textContent = button.getAttribute('data-description');
                descModal.classList.remove('modal-hidden');
                descModalContent.classList.add('translate-y-0', 'opacity-100');
            });
        });

        closeDescModalButton.addEventListener('click', function () {
            descModal.classList.add('modal-hidden');
            descModalContent.classList.remove('translate-y-0', 'opacity-100');
        });

        descModal.addEventListener('click', function (e) {
            if (e.target === descModal) {
                descModal.classList.add('modal-hidden');
                descModalContent.classList.remove('translate-y-0', 'opacity-100');
            }
        });

        // Abrir el modal de imagen
        document.querySelectorAll('.open-modal').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                modalImage.src = button.getAttribute('data-image-url');
                imageModal.classList.remove('hidden');
                imageModal.classList.add('flex');
            });
        });

        closeModalButton.addEventListener('click', function () {
            imageModal.classList.add('hidden');
            imageModal.classList.remove('flex');
        });

        imageModal.addEventListener('click', function (e) {
            if (e.target === imageModal) {
                imageModal.classList.add('hidden');
                imageModal.classList.remove('flex');
            }
        });

        const backToTopButton = document.getElementById('backToTopButton');
        backToTopButton.addEventListener('click', function (e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        window.addEventListener('scroll', function () {
            if (window.scrollY > 200) {
                backToTopButton.classList.remove('hidden');
            } else {
                backToTopButton.classList.add('hidden');
            }
        });
    });
</script>

<style>
    .no-select {
        user-select: none;
    }
    #backToTopButton {
        display: none;
    }
    .modal-hidden {
        display: none;
    }
</style>
@endsection 
