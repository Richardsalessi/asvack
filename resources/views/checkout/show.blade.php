@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">

    {{-- Mensajes de éxito / error --}}
    @if(session('success'))
        <div class="mb-4 rounded-md bg-green-100 text-green-800 px-4 py-2">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-md bg-red-100 text-red-800 px-4 py-2">
            {{ session('error') }}
        </div>
    @endif

    <h1 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Revisión de tu compra</h1>

    @php
        // Helper para normalizar URLs (public/ y storage/)
        $urlify = function ($src) {
            if (!$src) return null;
            if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://') || str_starts_with($src, 'data:')) return $src;
            if (str_starts_with($src, '/storage/') || str_starts_with($src, 'storage/')) return str_starts_with($src, '/') ? $src : '/'.$src;
            if (str_starts_with($src, 'img/') || str_starts_with($src, 'images/') || str_starts_with($src, 'assets/') || str_starts_with($src, 'uploads/')) return asset($src);
            try { return \Storage::url($src); } catch (\Throwable $e) { return asset($src); }
        };
    @endphp

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
        @foreach($carrito as $id => $p)
            @php
                // Del carrito: viene 'imagenes' (array) o 'imagen' simple
                $imgsRaw = is_array($p['imagenes'] ?? null) ? $p['imagenes'] : [];
                if (empty($imgsRaw)) {
                    $fallbacks = array_filter([$p['imagen'] ?? null, $p['imagen_url'] ?? null]);
                    $imgsRaw = array_values(array_unique(array_merge($imgsRaw, $fallbacks)));
                }
                $imgs   = array_values(array_filter(array_map($urlify, $imgsRaw)));
                $first  = $imgs[0] ?? null;

                // id único por fila para enlazar main <-> thumbs
                $gid = 'chk-gal-'.$loop->index.'-'.$id;
            @endphp

            <div class="flex justify-between items-start py-3 border-b border-gray-200 dark:border-gray-700 last:border-b-0">
                <div class="flex items-start gap-3 min-w-0">
                    {{-- Imagen principal --}}
                    <div class="w-16 h-16 rounded-md overflow-hidden ring-1 ring-gray-200 dark:ring-gray-700 bg-gray-100 dark:bg-gray-900 shrink-0">
                        @if($first)
                            <img id="{{ $gid }}-main" src="{{ $first }}" alt="{{ $p['nombre'] ?? 'Producto #'.$id }}"
                                 class="w-full h-full object-cover" onerror="this.style.display='none'">
                        @else
                            <div class="w-full h-full grid place-items-center text-[10px] opacity-60">Sin imagen</div>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="font-semibold text-gray-900 dark:text-white truncate">{{ $p['nombre'] ?? 'Producto #'.$id }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-300">
                            Precio: ${{ number_format($p['precio'], 0, ',', '.') }} —
                            Cantidad: {{ $p['cantidad'] }}
                        </div>

                        {{-- Carrusel de miniaturas si hay varias imágenes --}}
                        @if(count($imgs) > 1)
                            <div class="relative mt-2" data-gallery="{{ $gid }}">
                                <button type="button" class="gal-btn gal-prev" aria-label="Anterior">‹</button>
                                <div class="gal-track no-scrollbar" data-track>
                                    @foreach($imgs as $src)
                                        <img src="{{ $src }}" class="gal-thumb" data-src="{{ $src }}"
                                             alt="thumb" onerror="this.style.display='none'">
                                    @endforeach
                                </div>
                                <button type="button" class="gal-btn gal-next" aria-label="Siguiente">›</button>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="font-semibold text-gray-900 dark:text-white shrink-0">
                    ${{ number_format($p['precio'] * $p['cantidad'], 0, ',', '.') }}
                </div>
            </div>
        @endforeach
    </div>

    {{-- Totales (envío se calcula luego) --}}
    <div class="flex flex-col items-end gap-1 mb-6">
        <div class="text-gray-700 dark:text-gray-200">
            Subtotal: <strong>${{ number_format($subtotal, 0, ',', '.') }}</strong>
        </div>

        @if(is_null($envio))
            <div class="text-gray-700 dark:text-gray-200">
                Envío: <strong class="font-normal opacity-75">Se calculará en el siguiente paso</strong>
            </div>
            <div class="text-lg text-gray-900 dark:text-white">
                Total: <strong>${{ number_format($subtotal, 0, ',', '.') }}</strong>
            </div>
        @else
            <div class="text-gray-700 dark:text-gray-200">
                Envío: <strong>${{ number_format($envio, 0, ',', '.') }}</strong>
            </div>
            <div class="text-lg text-gray-900 dark:text-white">
                Total: <strong>${{ number_format($total, 0, ',', '.') }}</strong>
            </div>
        @endif
    </div>

    <div class="flex items-center gap-3">
        <a href="{{ route('carrito') }}" class="px-4 py-2 rounded-md bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white">
            Volver al carrito
        </a>

        <!-- En el siguiente paso este form creará la orden y te mandará a ePayco -->
        <form method="POST" action="{{ route('checkout.create') }}">
            @csrf
            <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">
                Confirmar y continuar al pago
            </button>
        </form>
    </div>
</div>

{{-- Estilos livianos para el carrusel --}}
<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
.gal-track{ display:flex; gap:.5rem; overflow-x:auto; scroll-snap-type:x mandatory; padding:0 .75rem; }
.gal-thumb{ width:2.25rem; height:2.25rem; object-fit:cover; border-radius:.375rem; border:1px solid rgba(113,113,122,.4); scroll-snap-align:center; cursor:pointer; }
.gal-btn{ position:absolute; top:50%; transform:translateY(-50%); width:1.75rem; height:1.75rem; border-radius:9999px; display:grid; place-items:center; background:rgba(0,0,0,.55); color:#fff; border:none; }
.gal-prev{ left:0; } .gal-next{ right:0; }
</style>

{{-- Script del carrusel (sin librerías) --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-gallery]').forEach(function (wrap) {
        var gid   = wrap.getAttribute('data-gallery');
        var main  = document.getElementById(gid + '-main');
        var track = wrap.querySelector('[data-track]');
        var prev  = wrap.querySelector('.gal-prev');
        var next  = wrap.querySelector('.gal-next');

        var toggleArrows = function () {
            var more = track.scrollWidth > track.clientWidth + 2;
            prev.style.display = more ? 'grid' : 'none';
            next.style.display = more ? 'grid' : 'none';
        };
        toggleArrows();

        var step = 120;
        prev.addEventListener('click', function(){ track.scrollBy({left:-step, behavior:'smooth'}); });
        next.addEventListener('click', function(){ track.scrollBy({left: step, behavior:'smooth'}); });
        track.addEventListener('scroll', toggleArrows);
        window.addEventListener('resize', toggleArrows);

        track.querySelectorAll('.gal-thumb').forEach(function (thumb) {
            thumb.addEventListener('click', function () {
                var src = this.getAttribute('data-src') || this.getAttribute('src');
                if (main && src) main.src = src;
            });
        });
    });
});
</script>
@endsection
