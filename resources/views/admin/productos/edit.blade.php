@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4 max-w-3xl h-screen bg-white dark:bg-gray-800 shadow rounded-lg overflow-y-auto">
    <h1 class="text-3xl font-semibold text-gray-800 dark:text-white mb-4">Editar Producto</h1>

    <form action="{{ route('admin.productos.update', $producto->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="nombre" class="block text-gray-700 dark:text-gray-200 mb-1">Nombre del Producto:</label>
            <input type="text" name="nombre" id="nombre" value="{{ $producto->nombre }}" class="w-full p-2 border rounded-lg focus:ring focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
        </div>

        <div class="mb-4">
            <label for="descripcion" class="block text-gray-700 dark:text-gray-200 mb-1">Descripción:</label>
            <textarea name="descripcion" id="descripcion" rows="3" class="w-full p-2 border rounded-lg focus:ring focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white resize-none" required>{{ $producto->descripcion }}</textarea>
        </div>

        <div class="mb-4">
            <label for="precio" class="block text-gray-700 dark:text-gray-200 mb-1">Precio:</label>
            <input type="text" name="precio" id="precio" value="{{ number_format($producto->precio, 0, ',', '.') }}" class="w-full p-2 border rounded-lg focus:ring focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required autocomplete="off">
        </div>

        <div class="mb-4">
            <label for="categoria_id" class="block text-gray-700 dark:text-gray-200 mb-1">Categoría:</label>
            <select name="categoria_id" id="categoria_id" class="w-full p-2 border rounded-lg focus:ring focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}" {{ $producto->categoria_id == $categoria->id ? 'selected' : '' }}>{{ $categoria->nombre }}</option>
                @endforeach
            </select>
        </div>

        <!-- Campo de Stock -->
        <div class="mb-4">
            <label for="stock" class="block text-gray-700 dark:text-gray-200 mb-1">Stock:</label>
            <input type="number" name="stock" id="stock" value="{{ $producto->stock }}" 
                   class="w-full p-2 border rounded-lg focus:ring focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                   required min="0" oninput="this.value = Math.max(this.value, 0)">
        </div>

        <!-- Campo de Prefijo y Contacto de WhatsApp -->
        <div class="mb-4">
            <label for="contacto_whatsapp" class="block text-gray-700 dark:text-gray-200 mb-1">Contacto de WhatsApp:</label>
            <div class="flex">
                <select name="prefijo" class="p-2 border rounded-l-lg focus:ring focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="+57" selected>+57</option>
                    <!-- Agrega más opciones si es necesario -->
                </select>
                <input type="text" name="contacto_whatsapp" id="contacto_whatsapp" value="{{ str_replace('+57', '', $producto->contacto_whatsapp) }}" class="w-full p-2 border rounded-r-lg focus:ring focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
            </div>
        </div>

        <!-- Sección para imágenes ya subidas y nuevas imágenes -->
        <div class="mb-4">
            <label class="block text-gray-700 dark:text-gray-200 mb-1">Imágenes:</label>
            <div class="flex flex-wrap gap-4 items-center" id="imagenes-container">
                @foreach($producto->imagenes as $imagen)
                    <div class="relative w-32 h-40 border rounded-lg dark:bg-gray-700 dark:border-gray-600 overflow-hidden flex flex-col justify-between items-center p-1" data-imagen-id="{{ $imagen->id }}">
                        <a href="#" class="open-modal cursor-pointer" data-image-url="data:image/jpeg;base64,{{ $imagen->contenido }}">
                            <img src="data:image/jpeg;base64,{{ $imagen->contenido }}" alt="Imagen del producto" class="object-contain w-full h-24 rounded-lg shadow-md hover:shadow-lg transition-all duration-300">
                        </a>
                        <span class="text-xs text-center text-gray-600 dark:text-gray-300 mt-0">{{ basename($imagen->ruta) }}</span>
                        <button type="button" class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center eliminar-imagen hover:bg-red-800 transition-all duration-300 shadow-lg">
                            &times;
                        </button>
                        <input type="hidden" name="eliminar_imagenes[]" value="" disabled>
                    </div>
                @endforeach
                <div class="flex gap-4 items-center" id="preview-imagenes"></div>
            </div>
        </div>

        <div class="flex gap-4 items-center mb-6">
            <label for="imagenes" class="block cursor-pointer bg-blue-500 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-300 text-center w-auto">
                <input type="file" name="imagenes[]" id="imagenes" multiple class="hidden">
                <span>Elegir archivos</span>
            </label>
        </div>

        <div class="flex gap-4 justify-end">
            <button type="submit" class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300">
                Actualizar Producto
            </button>
            <a href="{{ route('admin.productos.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300">
                Cancelar
            </a>
        </div>
    </form>
</div>

<a href="#" id="backToTopButton" class="fixed bottom-4 right-4 bg-blue-600 hover:bg-blue-800 text-white rounded-full p-3 shadow-lg transition-all duration-300">
    &#8679;
</a>

<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 items-center justify-center z-50 hidden no-select">
    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden w-11/12 max-w-3xl mx-auto mt-20">
        <button id="closeModal" class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center">&times;</button>
        <img id="modalImage" src="" alt="Imagen del producto" class="w-full object-contain p-4">
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
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

        document.querySelectorAll('.eliminar-imagen').forEach(button => {
            button.addEventListener('click', function () {
                const imagenDiv = this.closest('div[data-imagen-id]');
                const eliminarInput = imagenDiv.querySelector('input[name="eliminar_imagenes[]"]');
                eliminarInput.disabled = false;
                eliminarInput.value = imagenDiv.getAttribute('data-imagen-id');
                imagenDiv.classList.add('hidden');
            });
        });

        const imagenesInput = document.getElementById('imagenes');
        const previewContainer = document.getElementById('preview-imagenes');
        let dataTransfer = new DataTransfer();

        imagenesInput.addEventListener('change', function () {
            Array.from(this.files).forEach((file) => {
                dataTransfer.items.add(file);
                const reader = new FileReader();

                reader.onload = function (e) {
                    const imagenDiv = document.createElement('div');
                    imagenDiv.classList.add('relative', 'w-32', 'h-40', 'border', 'rounded-lg', 'dark:bg-gray-700', 'dark:border-gray-600', 'overflow-hidden', 'flex', 'flex-col', 'justify-between', 'items-center', 'p-1');

                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.classList.add('object-contain', 'w-full', 'h-24', 'rounded-lg', 'shadow-md', 'hover:shadow-lg', 'transition-all', 'duration-300', 'cursor-pointer');
                    img.addEventListener('click', function () {
                        modalImage.src = e.target.result;
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                    });

                    const fileName = document.createElement('span');
                    fileName.classList.add('text-xs', 'text-center', 'text-gray-600', 'dark:text-gray-300');
                    fileName.innerText = file.name;

                    const deleteButton = document.createElement('button');
                    deleteButton.type = 'button';
                    deleteButton.classList.add('absolute', 'top-1', 'right-1', 'bg-red-600', 'text-white', 'rounded-full', 'w-6', 'h-6', 'flex', 'items-center', 'justify-center', 'hover:bg-red-800', 'transition-all', 'duration-300', 'shadow-lg');
                    deleteButton.innerHTML = '&times;';
                    deleteButton.addEventListener('click', function () {
                        imagenDiv.remove();
                        const updatedDataTransfer = new DataTransfer();
                        Array.from(dataTransfer.files).forEach((existingFile) => {
                            if (existingFile !== file) {
                                updatedDataTransfer.items.add(existingFile);
                            }
                        });
                        dataTransfer = updatedDataTransfer;
                        imagenesInput.files = dataTransfer.files;
                    });

                    imagenDiv.appendChild(img);
                    imagenDiv.appendChild(fileName);
                    imagenDiv.appendChild(deleteButton);
                    previewContainer.appendChild(imagenDiv);
                };

                reader.readAsDataURL(file);
            });

            imagenesInput.files = dataTransfer.files;
        });

        const precioInput = document.getElementById('precio');
        precioInput.addEventListener('blur', function () {
            let value = this.value.replace(/\D/g, '');
            value = Math.min(Math.max(value, 0), 100000000);
            this.value = new Intl.NumberFormat('es-CO', { minimumFractionDigits: 0 }).format(value);
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
    .no-select { user-select: none; }
    #backToTopButton { display: none; }
    .cursor-pointer { cursor: pointer; }
</style>
@endsection
