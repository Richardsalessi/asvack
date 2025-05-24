<div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-105 flex flex-col text-center h-full">

    <!-- Imagen -->
    <div class="h-64 w-full mb-4 overflow-hidden relative flex items-center justify-center">
        @if(!empty($producto->imagenes) && count($producto->imagenes) > 0)
            <img src="data:image/png;base64,{{ $producto->imagenes[0]->contenido }}" alt="Imagen de {{ $producto->nombre }}" class="object-contain max-h-full" style="user-select: none;">
        @else
            <img src="{{ asset('storage/placeholder.png') }}" alt="Imagen de {{ $producto->nombre }}" class="object-contain max-h-full" style="user-select: none;">
        @endif
    </div>

    <!-- Contenido con altura fija para alinear -->
    <div class="flex flex-col justify-start min-h-[230px]">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-1">{{ $producto->nombre }}</h2>
        <p class="text-lg font-bold text-gray-900 dark:text-white mb-1">Especificaciones técnicas:</p>
        <p class="text-gray-900 dark:text-white text-sm line-clamp-4 leading-relaxed">
            {{ $producto->descripcion }}
        </p>
    </div>

    <!-- Precio y stock -->
    <div class="mt-4">
        <p class="text-gray-900 dark:text-white font-bold text-lg"><strong>Precio:</strong> ${{ number_format($producto->precio, 0, ',', '.') }}</p>
        <p class="text-gray-900 dark:text-white mb-2"><strong>Unidades disponibles:</strong> {{ $producto->stock }}</p>
    </div>

    <!-- Agregar al carrito -->
    <form action="{{ route('carrito.agregar', $producto->id) }}" method="POST" class="add-to-cart-form mt-auto">
        @csrf
        <label class="block text-sm font-semibold text-gray-900 dark:text-white">Cantidad</label>
        <input type="number" name="cantidad" value="1" min="1" max="{{ $producto->stock }}"
               class="w-16 p-2 border rounded-md text-center cantidad-input bg-white text-black dark:bg-gray-800 dark:text-white dark:border-gray-700" required>

        @auth
            <button type="submit" class="btn-agregar-carrito">Agregar al carrito</button>
        @else
            <button type="button" onclick="window.location.href='{{ route('login') }}'" class="btn-agregar-carrito">Inicia sesión para comprar</button>
        @endauth
    </form>
</div>
