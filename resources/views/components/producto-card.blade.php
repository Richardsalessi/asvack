<div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-105 flex flex-col text-center h-full">

  {{-- Carrusel / Imagen --}}
  <div class="w-full mb-4 select-none">
    @php
      $imgs  = $producto->imagenes ?? collect();
      $count = $imgs->count();
    @endphp

    @if($count > 0)
      <div class="relative h-64 w-full group rounded-md bg-white/40 dark:bg-black/20 overflow-hidden" data-slider>
        {{-- Slides --}}
        @foreach($imgs as $i => $img)
          <img
            src="data:image/jpeg;base64,{{ $img->contenido }}"
            alt="Imagen de {{ $producto->nombre }}"
            class="slide {{ $i === 0 ? '' : 'hidden' }} absolute inset-0 w-full h-full object-contain cursor-pointer"
            draggable="false">
        @endforeach

        @if($count > 1)
          {{-- Controles --}}
          <button type="button"
            class="prev absolute left-2 top-1/2 -translate-y-1/2 rounded-full w-8 h-8 bg-black/60 text-white text-lg grid place-items-center opacity-0 group-hover:opacity-100 transition"
            aria-label="Anterior">‹</button>

          <button type="button"
            class="next absolute right-2 top-1/2 -translate-y-1/2 rounded-full w-8 h-8 bg-black/60 text-white text-lg grid place-items-center opacity-0 group-hover:opacity-100 transition"
            aria-label="Siguiente">›</button>

          {{-- Dots --}}
          <div class="dots absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-1">
            @for($d=0; $d < $count; $d++)
              <span class="dot w-2.5 h-2.5 rounded-full {{ $d === 0 ? 'bg-white' : 'bg-white/50' }}"></span>
            @endfor
          </div>
        @endif
      </div>

      @if($count > 1)
        {{-- Miniaturas clickeables --}}
        <div class="mt-3 flex gap-2 justify-center overflow-x-auto px-1">
          @foreach($imgs as $i => $img)
            <img
              src="data:image/jpeg;base64,{{ $img->contenido }}"
              alt="Miniatura {{ $i+1 }} de {{ $producto->nombre }}"
              class="thumb w-14 h-14 object-cover rounded-md border {{ $i === 0 ? 'ring-2 ring-blue-500 border-transparent' : 'border-gray-300 dark:border-gray-700' }} cursor-pointer shrink-0"
              data-index="{{ $i }}"
              draggable="false">
          @endforeach
        </div>
      @endif
    @else
      {{-- Fallback sin imágenes --}}
      <div class="relative h-64 w-full rounded-md bg-white/40 dark:bg-black/20 overflow-hidden grid place-items-center">
        <img src="{{ asset('storage/placeholder.png') }}" alt="Imagen de {{ $producto->nombre }}" class="max-h-full object-contain" draggable="false">
      </div>
    @endif
  </div>

  {{-- Contenido --}}
  <div class="flex flex-col justify-start min-h-[230px]">
    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-1">{{ $producto->nombre }}</h2>
    <p class="text-lg font-bold text-gray-900 dark:text-white mb-1">Especificaciones técnicas:</p>
    <p class="text-gray-900 dark:text-white text-sm line-clamp-4 leading-relaxed">{{ $producto->descripcion }}</p>
  </div>

  {{-- Precio y stock --}}
  <div class="mt-4">
    <p class="text-gray-900 dark:text-white font-bold text-lg"><strong>Precio:</strong> ${{ number_format($producto->precio, 0, ',', '.') }}</p>
    <p class="text-gray-900 dark:text-white mb-2"><strong>Unidades disponibles:</strong> {{ $producto->stock }}</p>
  </div>

  {{-- Agregar al carrito --}}
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

{{-- JS del carrusel + miniaturas + auto-rotación + apertura modal --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
  // Configuración
  const ROTATE_MS = 4000; // tiempo entre imágenes

  document.querySelectorAll('[data-slider]').forEach((slider) => {
    const slides = slider.querySelectorAll('.slide');
    const dots   = slider.querySelectorAll('.dot');
    const prev   = slider.querySelector('.prev');
    const next   = slider.querySelector('.next');
    const thumbs = slider.parentElement.querySelectorAll('.thumb');

    if (!slides.length) return;

    let idx = 0;
    let timer = null;

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

    const setIdx = (i) => { idx = i; };
    const getIdx = () => idx;

    const go = (delta) => {
      idx = (idx + delta + slides.length) % slides.length;
      paint(idx);
    };

    const startAuto = () => {
      stopAuto();
      if (slides.length > 1) {
        timer = setInterval(() => go(1), ROTATE_MS);
      }
    };
    const stopAuto = () => { if (timer) { clearInterval(timer); timer = null; } };

    // Hover pausa/reanuda
    slider.addEventListener('mouseenter', stopAuto);
    slider.addEventListener('mouseleave', startAuto);

    // Controles
    prev?.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); go(-1); });
    next?.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); go(1); });

    // Dots
    dots.forEach((d, k) => d.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); idx = k; paint(idx); }));

    // Miniaturas (solo cambian la imagen, NO abren modal)
    thumbs.forEach((t) => {
      t.addEventListener('click', (e) => {
        e.preventDefault(); e.stopPropagation();
        idx = parseInt(t.dataset.index, 10) || 0;
        paint(idx);              // cambia la imagen del carrusel
        // sin modal aquí
      });
    });


    // Click en imagen grande -> abrir modal sincronizado
    slider.addEventListener('click', (e) => {
      const img = e.target.closest('img.slide');
      if (!img) return;
      if (window.__openImageFromSlider) {
        window.__openImageFromSlider({ slides, paint, getIdx, setIdx, startAuto, stopAuto }, idx);
      }
    });

    // Init
    paint(0);
    startAuto();
  });
});
</script>
