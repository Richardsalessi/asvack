@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4 max-w-3xl h-screen bg-white dark:bg-gray-800 shadow rounded-lg overflow-y-auto">
    <h1 class="text-3xl font-semibold text-gray-800 dark:text-white mb-4">Añadir Producto</h1>

    <form id="formProducto" action="{{ route('admin.productos.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf

        {{-- Nombre --}}
        <div>
            <label for="nombre" class="block text-gray-700 dark:text-gray-200 mb-1">Nombre del Producto:</label>
            <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}"
                   class="w-full p-2 border rounded-lg focus:ring focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
            @error('nombre') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
        </div>

        {{-- Descripción --}}
        <div>
            <label for="descripcion" class="block text-gray-700 dark:text-gray-200 mb-1">Descripción:</label>
            <textarea name="descripcion" id="descripcion" rows="3"
                      class="w-full p-2 border rounded-lg focus:ring focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white resize-none" required>{{ old('descripcion') }}</textarea>
            @error('descripcion') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
        </div>

        {{-- Precio (formateado en UI, limpio al enviar) --}}
        <div>
            <label for="precio_visible" class="block text-gray-700 dark:text-gray-200 mb-1">Precio:</label>
            <input type="text" id="precio_visible" value="{{ old('precio') }}"
                   class="w-full p-2 border rounded-lg focus:ring focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                   required autocomplete="off" inputmode="numeric" placeholder="3.250.000">
            <input type="hidden" name="precio" id="precio" value="{{ old('precio') }}">
            @error('precio') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
        </div>

        {{-- Categoría --}}
        <div>
            <label for="categoria_id" class="block text-gray-700 dark:text-gray-200 mb-1">Categoría:</label>
            <select name="categoria_id" id="categoria_id"
                    class="w-full p-2 border rounded-lg focus:ring focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                        {{ $categoria->nombre }}
                    </option>
                @endforeach
            </select>
            @error('categoria_id') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
        </div>

        {{-- Stock --}}
        <div>
            <label for="stock" class="block text-gray-700 dark:text-gray-200 mb-1">Stock:</label>
            <input type="number" name="stock" id="stock" value="{{ old('stock') }}"
                   class="w-full p-2 border rounded-lg focus:ring focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                   required min="0" oninput="this.value = Math.max(this.value, 0)">
            @error('stock') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
        </div>

        {{-- Imágenes nuevas (carrusel horizontal) --}}
        <div>
            <label class="block text-gray-700 dark:text-gray-200 mb-2">Imágenes:</label>

            <div class="flex gap-4 items-start mb-4 overflow-x-auto no-select snap-x snap-mandatory pb-2" id="preview-imagenes">
                {{-- aquí se inyectan las cards de previsualización --}}
            </div>

            <label for="imagenes" class="inline-flex items-center gap-2 cursor-pointer bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-300">
                <input type="file" name="imagenes[]" id="imagenes" multiple class="hidden" accept="image/*">
                Elegir archivos
            </label>
            @error('imagenes') <div class="text-red-600 text-sm mt-2">{{ $message }}</div> @enderror
        </div>

        {{-- Barra de acciones (fija en móviles) --}}
        <div class="md:static fixed left-0 right-0 bottom-0 md:bottom-auto z-20 bg-gray-900/80 md:bg-transparent backdrop-blur md:backdrop-blur-0 px-4 py-3 md:p-0">
            <div class="flex gap-4 justify-end max-w-3xl mx-auto">
                <button type="submit" class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300">
                    Guardar Producto
                </button>
                <a href="{{ route('admin.productos.index') }}"
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300">
                    Cancelar
                </a>
            </div>
        </div>
    </form>
</div>

{{-- Back to top (subido para no chocar con barra fija) --}}
<a href="#" id="backToTopButton" class="fixed bottom-20 right-4 md:bottom-4 bg-blue-600 hover:bg-blue-800 text-white rounded-full p-3 shadow-lg transition-all duration-300">
    &#8679;
</a>

{{-- Modal imagen --}}
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 items-center justify-center z-50 hidden no-select">
    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden max-w-full max-h-full flex items-center justify-center">
        <button id="closeModal" class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center z-10">&times;</button>
        <img id="modalImage" src="" alt="Imagen del producto" class="max-w-full max-h-screen object-contain">
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Modal
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const closeModalButton = document.getElementById('closeModal');
    const closeModal = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); };
    closeModalButton.addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    // Previews de nuevas imágenes
    const imagenesInput = document.getElementById('imagenes');
    const previewContainer = document.getElementById('preview-imagenes');
    let dataTransfer = new DataTransfer();

    imagenesInput.addEventListener('change', function () {
        Array.from(this.files).forEach((file) => {
            dataTransfer.items.add(file);
            const reader = new FileReader();
            reader.onload = (e) => {
                const card = document.createElement('div');
                card.className = 'relative w-32 h-40 border rounded-lg dark:bg-gray-700 dark:border-gray-600 overflow-hidden flex flex-col justify-between items-center p-1 shrink-0 snap-start';

                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'object-contain w-full h-24 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 cursor-pointer';
                img.addEventListener('click', () => {
                    modalImage.src = e.target.result;
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                });

                const fileName = document.createElement('span');
                fileName.className = 'text-[11px] text-center text-gray-600 dark:text-gray-300 mt-0 truncate w-full';
                fileName.textContent = file.name;

                const del = document.createElement('button');
                del.type = 'button';
                del.className = 'absolute top-1 right-1 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-800 transition-all duration-300 shadow-lg';
                del.innerHTML = '&times;';
                del.addEventListener('click', () => {
                    card.remove();
                    const updated = new DataTransfer();
                    Array.from(dataTransfer.files).forEach(f => { if (f !== file) updated.items.add(f); });
                    dataTransfer = updated;
                    imagenesInput.files = dataTransfer.files;
                });

                card.append(img, fileName, del);
                previewContainer.appendChild(card);
            };
            reader.readAsDataURL(file);
        });
        imagenesInput.files = dataTransfer.files;
    });

    // Precio visible vs enviado
    const precioVisible = document.getElementById('precio_visible');
    const precioHidden  = document.getElementById('precio');
    const form = document.getElementById('formProducto');

    const formatear = () => {
        let v = (precioVisible.value || '').replace(/\D/g, '');
        if (!v) v = '0';
        v = Math.min(Math.max(parseInt(v), 0), 100000000);
        precioVisible.value = new Intl.NumberFormat('es-CO').format(v);
        precioHidden.value  = String(v);
    };
    precioVisible.addEventListener('input', () => { precioVisible.value = precioVisible.value.replace(/[^\d.]/g, ''); });
    precioVisible.addEventListener('blur', formatear);
    form.addEventListener('submit', formatear);

    // Back to top
    const back = document.getElementById('backToTopButton');
    back.addEventListener('click', e => { e.preventDefault(); window.scrollTo({ top: 0, behavior: 'smooth' }); });
    window.addEventListener('scroll', () => {
        if (window.scrollY > 200) back.classList.remove('hidden'); else back.classList.add('hidden');
    });
});
</script>

<style>
    .no-select { user-select: none; }
    #backToTopButton { display: none; }
    .cursor-pointer { cursor: pointer; }
</style>
@endsection
