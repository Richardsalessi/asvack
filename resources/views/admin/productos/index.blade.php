@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-4xl font-bold mb-6 text-gray-900 dark:text-white">Lista de Productos</h1>
    <a href="{{ route('admin.productos.create') }}" class="bg-green-500 hover:bg-green-700 text-white px-6 py-3 rounded mb-4 inline-block transition-all duration-300">Añadir Producto</a>

    <!-- Filtro de Proveedor -->
    <div class="mb-4">
        <form action="{{ route('admin.productos.index') }}" method="GET" class="flex items-center gap-4">
            <label for="proveedor" class="text-gray-700 dark:text-gray-300 font-bold">Filtrar por Proveedor:</label>
            <select name="proveedor" id="proveedor" class="w-64 px-4 py-2 border rounded-lg text-gray-900 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos los Proveedores</option>
                @foreach($proveedores as $proveedor)
                    <option value="{{ $proveedor->id }}" {{ request('proveedor') == $proveedor->id ? 'selected' : '' }}>
                        {{ $proveedor->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-all duration-300">Filtrar</button>
        </form>
    </div>

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
                    <th class="px-6 py-3 text-left text-gray-900 dark:text-gray-300">Proveedor</th>
                    <th class="px-6 py-3 text-left text-gray-900 dark:text-gray-300">Contacto</th>
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
                        @if ($producto->imagenes->count() > 3)
                            <span class="text-sm text-gray-500 dark:text-gray-400">+{{ $producto->imagenes->count() - 3 }}</span>
                        @endif
                    </td>

                    <td class="px-6 py-4 text-gray-900 dark:text-gray-300">{{ $producto->nombre }}</td>

                    <td class="px-6 py-4 text-gray-900 dark:text-gray-300">
                        <a href="#" class="open-desc-modal text-blue-500 hover:underline" data-description="{{ $producto->descripcion }}">Ver descripción</a>
                    </td>

                    <td class="px-6 py-4 text-gray-900 dark:text-gray-300">${{ number_format($producto->precio, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-gray-900 dark:text-gray-300">{{ $producto->categoria->nombre ?? 'Sin categoría' }}</td>
                    <td class="px-6 py-4 text-gray-900 dark:text-gray-300">{{ $producto->stock }}</td>
                    <td class="px-6 py-4 text-gray-900 dark:text-gray-300">
                        {{ $producto->creador ? $producto->creador->name : 'Sin proveedor' }}
                    </td>
                    <td class="px-6 py-4 text-gray-900 dark:text-gray-300">
                        @if ($producto->contacto_whatsapp)
                            <a href="https://wa.me/{{ $producto->contacto_whatsapp }}" target="_blank" class="text-blue-500 hover:underline">WhatsApp</a>
                        @else
                            <span class="text-gray-500">Sin contacto</span>
                        @endif
                    </td>
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

<!-- Modal para mostrar la descripción completa -->
<div id="descModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 modal-hidden">
    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-11/12 max-w-lg mx-auto mt-20 transform translate-y-20 opacity-0 transition-transform duration-300 ease-out" id="descModalContent">
        <button id="closeDescModal" class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center">&times;</button>
        <h2 class="text-lg font-bold mb-4 text-gray-900 dark:text-white">Descripción del Producto</h2>
        <p id="descContent" class="text-gray-700 dark:text-gray-300"></p>
    </div>
</div>

<!-- Modal para mostrar la imagen -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 modal-hidden">
    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden w-11/12 max-w-3xl mx-auto mt-20 transform translate-y-20 opacity-0 transition-transform duration-300 ease-out" id="imageModalContent">
        <button id="closeModal" class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center">&times;</button>
        <img id="modalImage" src="" alt="Imagen del producto" class="w-full object-contain p-4 no-select">
    </div>
</div>

<a href="#" id="backToTopButton" class="fixed bottom-4 right-4 bg-blue-600 hover:bg-blue-800 text-white rounded-full p-3 shadow-lg transition-all duration-300">
    &#8679;
</a>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const descModal = document.getElementById('descModal');
        const descModalContent = document.getElementById('descModalContent');
        const openDescModalButtons = document.querySelectorAll('.open-desc-modal');
        const descContent = document.getElementById('descContent');
        const closeDescModalButton = document.getElementById('closeDescModal');

        const imageModal = document.getElementById('imageModal');
        const imageModalContent = document.getElementById('imageModalContent');
        const openModalButtons = document.querySelectorAll('.open-modal');
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
        openModalButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                modalImage.src = button.getAttribute('data-image-url');
                imageModal.classList.remove('modal-hidden');
                imageModalContent.classList.add('translate-y-0', 'opacity-100');
            });
        });

        closeModalButton.addEventListener('click', function () {
            imageModal.classList.add('modal-hidden');
            imageModalContent.classList.remove('translate-y-0', 'opacity-100');
        });

        imageModal.addEventListener('click', function (e) {
            if (e.target === imageModal) {
                imageModal.classList.add('modal-hidden');
                imageModalContent.classList.remove('translate-y-0', 'opacity-100');
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
