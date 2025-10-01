@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-3xl py-8 text-zinc-900 dark:text-zinc-100">

    <h1 class="text-2xl font-semibold mb-2">Pagar con ePayco (modo pruebas)</h1>
    <p class="mb-6 text-sm opacity-80">
        Estás en modo <strong>pruebas</strong>. Usa datos de prueba de ePayco. Al finalizar, ePayco llamará a nuestro
        <strong>webhook</strong> y te redirigirá a la página de <strong>respuesta</strong>.
    </p>

    {{-- Resumen del pedido --}}
    <div class="rounded-2xl border bg-white border-zinc-200 shadow-sm
                dark:bg-zinc-800 dark:border-zinc-700">
        <div class="p-5 border-b border-zinc-200 dark:border-zinc-700">
            <h2 class="text-lg font-semibold">Resumen de tu compra</h2>
        </div>

        <div class="p-5 space-y-5">
            @foreach(($orden->detalles ?? []) as $item)
                @php
                    $p = $item->producto ?? null;

                    // ---- helper URL correcto para public/ y storage/ ----
                    $urlify = function ($src) {
                        if (!$src) return null;
                        if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://') || str_starts_with($src, 'data:')) return $src;
                        if (str_starts_with($src, '/storage/') || str_starts_with($src, 'storage/')) return str_starts_with($src, '/') ? $src : '/'.$src;
                        if (str_starts_with($src, 'img/') || str_starts_with($src, 'images/') || str_starts_with($src, 'assets/') || str_starts_with($src, 'uploads/')) return asset($src);
                        try { return \Storage::url($src); } catch (\Throwable $e) { return asset($src); }
                    };

                    // 1) PRIMERO imágenes que adjuntamos desde la sesión (carrito)
                    $imgsRaw = is_array($item->getAttribute('imagenes_sesion') ?? null)
                        ? $item->getAttribute('imagenes_sesion')
                        : [];

                    // 2) Relación imagenes de BD
                    if (empty($imgsRaw) && $p && $p->relationLoaded('imagenes')) {
                        $imgsRaw = $p->imagenes
                            ->map(fn($im) => $im->url ?? $im->ruta ?? $im->path ?? null)
                            ->filter()->values()->all();
                    }

                    // 3) Fallback columnas directas
                    if (empty($imgsRaw)) {
                        $fallbacks = array_filter([$p?->imagen_url ?? null, $p?->imagen ?? null]);
                        $imgsRaw = array_values(array_unique(array_merge($imgsRaw, $fallbacks)));
                    }

                    // 4) Normaliza
                    $imgs = array_values(array_filter(array_map($urlify, $imgsRaw)));
                    $firstImg = $imgs[0] ?? null;

                    // id único del carrusel para este item
                    $gid = 'gal-'.$loop->index.'-'.($item->id ?? $item->producto_id ?? 'x');
                @endphp

                <div class="flex items-start gap-4">
                    {{-- Imagen principal (se actualiza al hacer clic en una miniatura) --}}
                    <div class="w-16 h-16 rounded-lg overflow-hidden ring-1 ring-zinc-200 dark:ring-zinc-700 bg-zinc-100 dark:bg-zinc-900 shrink-0">
                        @if($firstImg)
                            <img id="{{ $gid }}-main" src="{{ $firstImg }}" alt="{{ $p?->nombre ?? 'Producto' }}"
                                 class="w-full h-full object-cover"
                                 onerror="this.style.display='none'">
                        @else
                            <div class="w-full h-full grid place-items-center text-xs opacity-60">Sin imagen</div>
                        @endif
                    </div>

                    {{-- Info + carrusel de miniaturas --}}
                    <div class="min-w-0 flex-1">
                        <p class="font-medium truncate">{{ $p?->nombre ?? 'Producto' }}</p>
                        <p class="text-sm opacity-70">
                            Cantidad: {{ $item->cantidad }} ·
                            Precio: ${{ number_format($item->precio_unitario ?? $item->precio ?? 0, 0, ',', '.') }}
                        </p>

                        {{-- Carrusel de thumbs (si hay >1) --}}
                        @if(count($imgs) > 1)
                            <div class="relative mt-2" data-gallery="{{ $gid }}">
                                <button type="button" class="gal-btn gal-prev" aria-label="Anterior">‹</button>
                                <div class="gal-track no-scrollbar" data-track>
                                    @foreach($imgs as $k => $src)
                                        <img
                                            src="{{ $src }}"
                                            alt="Imagen {{ $k+1 }}"
                                            class="gal-thumb"
                                            data-src="{{ $src }}"
                                            onerror="this.style.display='none'">
                                    @endforeach
                                </div>
                                <button type="button" class="gal-btn gal-next" aria-label="Siguiente">›</button>
                            </div>
                        @endif
                    </div>

                    <div class="ml-auto font-semibold shrink-0">
                        ${{ number_format(($item->precio_unitario ?? $item->precio ?? 0) * $item->cantidad, 0, ',', '.') }}
                    </div>
                </div>
            @endforeach

            <div class="border-t border-dashed pt-4 mt-2 border-zinc-200 dark:border-zinc-700">
                <div class="flex justify-between text-sm mb-2">
                    <span class="opacity-80">Subtotal</span>
                    <span>${{ number_format($orden->subtotal ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm mb-2">
                    <span class="opacity-80">Envío</span>
                    <span>${{ number_format($orden->envio ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-base font-semibold">
                    <span>Total</span>
                    <span>${{ number_format($orden->total ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="flex items-start gap-3 mt-2">
                <svg class="w-5 h-5 mt-0.5 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2a9.99 9.99 0 1 0 0 20 9.99 9.99 0 0 0 0-20Zm-1 15-4-4 1.41-1.41L11 13.17l5.59-5.59L18 9l-7 8Z"/>
                </svg>
                <p class="text-xs opacity-80">
                    Pagos seguros a través de ePayco. En modo pruebas no se realiza cargo real.
                </p>
            </div>
        </div>

        <div class="p-5 border-t border-zinc-200 dark:border-zinc-700">
            <button id="btn-epayco"
                class="w-full px-5 py-3 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-medium transition">
                Pagar con ePayco
            </button>
        </div>
    </div>

    <p class="text-xs opacity-70 mt-4">
        * Si notas algún valor incorrecto, vuelve al carrito y actualiza tu compra.
    </p>
</div>

{{-- Widget oficial de ePayco --}}
<script src="https://checkout.epayco.co/checkout.js"></script>

{{-- Estilos del carrusel (ligeros) --}}
<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.gal-track{ display:flex; gap:.5rem; overflow-x:auto; scroll-snap-type:x mandatory; padding:0 .75rem; }
.gal-thumb{ width:2.25rem; height:2.25rem; object-fit:cover; border-radius:.375rem; border:1px solid rgba(113,113,122,.4); scroll-snap-align:center; cursor:pointer; }
.gal-btn{ position:absolute; top:50%; transform:translateY(-50%); width:1.75rem; height:1.75rem; border-radius:9999px; display:grid; place-items:center; background:rgba(0,0,0,.55); color:#fff; border:none; }
.gal-prev{ left:0; }
.gal-next{ right:0; }
</style>

<script>
(function () {
    // ====== ePayco handler ======
    var handler = ePayco.checkout.configure({
        key: "{{ $epayco['public_key'] }}",
        test: {{ $epayco['test'] ? 'true' : 'false' }},
        language: "{{ $epayco['lang'] }}",
        external: "true"
    });

    var data = {
        name:        "{{ $epayco['name'] }}",
        description: "{{ $epayco['description'] }}",
        invoice:     "{{ $epayco['invoice'] }}",
        currency:    "{{ $epayco['currency'] }}",
        amount:      "{{ $epayco['amount'] }}",
        tax_base: "0",
        tax: "0",
        country: "CO",
        response:     "{{ $epayco['response_url'] }}",
        confirmation: "{{ $epayco['confirm_url'] }}",
        extra1:       "{{ $epayco['extra1'] }}"
    };

    document.getElementById('btn-epayco').addEventListener('click', function () {
        handler.open(data);
    });

    // ====== Carruseles por producto ======
    document.querySelectorAll('[data-gallery]').forEach(function (wrap) {
        var gid   = wrap.getAttribute('data-gallery');
        var main  = document.getElementById(gid + '-main');
        var track = wrap.querySelector('[data-track]');
        var prev  = wrap.querySelector('.gal-prev');
        var next  = wrap.querySelector('.gal-next');

        // Ocultar flechas si no hay overflow
        var toggleArrows = function () {
            var more = track.scrollWidth > track.clientWidth + 2;
            prev.style.display = more ? 'grid' : 'none';
            next.style.display = more ? 'grid' : 'none';
        };
        toggleArrows();

        // Navegación
        var step = 120;
        prev.addEventListener('click', function(){ track.scrollBy({left:-step, behavior:'smooth'}); });
        next.addEventListener('click', function(){ track.scrollBy({left: step, behavior:'smooth'}); });
        track.addEventListener('scroll', toggleArrows);

        // Click en thumbs => actualiza imagen principal
        track.querySelectorAll('.gal-thumb').forEach(function (thumb) {
            thumb.addEventListener('click', function () {
                var src = this.getAttribute('data-src') || this.getAttribute('src');
                if (main && src) main.src = src;
            });
        });

        // Recalcular al redimensionar
        window.addEventListener('resize', toggleArrows);
    });
})();
</script>
@endsection
