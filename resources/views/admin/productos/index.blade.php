@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-4xl font-bold mb-6 text-gray-900 dark:text-white">Lista de Productos</h1>
    <a href="{{ route('admin.productos.create') }}" class="bg-green-500 hover:bg-green-700 text-white px-6 py-3 rounded mb-4 inline-block transition-all duration-300">Añadir Producto</a>
    <div class="overflow-x-auto mt-4">
        <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <thead class="bg-gray-100 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-gray-900 dark:text-gray-300">Imágenes</th>
                    <th class="px-6 py-3 text-left text-gray-900 dark:text-gray-300">Nombre</th>
                    <th class="px-6 py-3 text-left text-gray-900 dark:text-gray-300">Descripción</th>
                    <th class="px-6 py-3 text-left text-gray-900 dark:text-gray-300">Precio</th>
                    <th class="px-6 py-3 text-left text-gray-900 dark:text-gray-300">Categoría</th>
                    <th class="px-6 py-3 text-left text-gray-900 dark:text-gray-300">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($productos as $producto)
                <tr class="border-b border-gray-200 dark:border-gray-700">
                    <td class="px-6 py-4 flex gap-2 items-center">
                        @if ($producto->imagenes->isNotEmpty())
                            @foreach ($producto->imagenes as $imagen)
                                <a href="#" class="open-modal" data-image-url="data:image/jpeg;base64,{{ $imagen->contenido }}">
                                    <img src="data:image/jpeg;base64,{{ $imagen->contenido }}" alt="Imagen de {{ $producto->nombre }}" class="h-20 w-20 object-cover rounded-lg shadow-md hover:shadow-lg transition-all duration-300">
                                </a>
                            @endforeach
                        @else
                            <div class="h-20 w-20 flex items-center justify-center bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 rounded-lg shadow-md border border-gray-400 dark:border-gray-600 text-center p-2">
                                <span class="text-sm font-semibold text-white leading-tight">Sin imagen<br>disponible</span>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-900 dark:text-gray-300">{{ $producto->nombre }}</td>
                    <td class="px-6 py-4 text-gray-900 dark:text-gray-300">{{ $producto->descripcion }}</td>
                    <td class="px-6 py-4 text-gray-900 dark:text-gray-300">${{ number_format($producto->precio, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-gray-900 dark:text-gray-300">{{ $producto->categoria->nombre }}</td>
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

<!-- Modal para mostrar la imagen -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 items-center justify-center z-50 hidden no-select">
    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden w-11/12 max-w-3xl mx-auto mt-20">
        <button id="closeModal" class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center">&times;</button>
        <img id="modalImage" src="" alt="Imagen del producto" class="w-full object-contain p-4">
    </div>
</div>

<!-- Botón para subir rápidamente -->
<a href="#" id="backToTopButton" class="fixed bottom-4 right-4 bg-blue-600 hover:bg-blue-800 text-white rounded-full p-3 shadow-lg transition-all duration-300">
    &#8679;
</a>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Modal para imágenes
        const openModalButtons = document.querySelectorAll('.open-modal');
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');
        const closeModalButton = document.getElementById('closeModal');

        openModalButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const imageUrl = button.getAttribute('data-image-url');
                modalImage.src = imageUrl;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });
        });

        closeModalButton.addEventListener('click', function () {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });

        // Cerrar modal al hacer clic fuera del contenedor de la imagen
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        });

        // Botón para volver arriba
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
</style>
@endsection
