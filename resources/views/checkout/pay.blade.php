@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-6xl px-4 sm:px-6 py-8 text-zinc-900 dark:text-zinc-100">

    <h1 class="text-2xl sm:text-3xl font-semibold mb-2">Pagar con ePayco (modo pruebas)</h1>
    <p class="mb-6 text-sm opacity-80">
        Estás en modo <strong>pruebas</strong>. Usa datos de prueba de ePayco. Al finalizar, ePayco llamará a nuestro
        <strong>webhook</strong> y te redirigirá a la página de <strong>respuesta</strong>.
    </p>

    @if(session('success'))
        <div class="mb-4 rounded-md bg-emerald-100 text-emerald-800 px-4 py-2">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-md bg-red-100 text-red-800 px-4 py-2">
            {{ session('error') }}
        </div>
    @endif

    {{-- Regla de envío --}}
    <div class="mb-6 rounded-xl border bg-white border-zinc-200 shadow-sm
                dark:bg-zinc-800 dark:border-zinc-700 p-4">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 shrink-0 text-emerald-600 mt-0.5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M3 6h13a3 3 0 0 1 3 3v5h1a1 1 0 0 1 1 1v2h-2.18a3 3 0 0 1-5.64 0H9.82a3 3 0 0 1-5.64 0H2v-8a3 3 0 0 1 1-2.236V6Zm2 2a1 1 0 0 0-1 1v7h.18a3 3 0 0 1 5.64 0h3.36a3 3 0 0 1 5.64 0H20v-1h-1a1 1 0 0 1-1-1V9a1 1 0 0 0-1-1H5Z"/>
            </svg>
            <p class="text-sm">
                <strong>¡Envío gratis en compras desde $50.000!</strong> Si el subtotal es menor, el envío cuesta $10.000.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        {{-- Columna: Resumen (sticky en desktop) --}}
        <div class="lg:sticky lg:top-6">
            <div class="rounded-2xl border bg-white border-zinc-200 shadow-sm
                        dark:bg-zinc-800 dark:border-zinc-700">
                <div class="p-5 border-b border-zinc-200 dark:border-zinc-700">
                    <h2 class="text-lg sm:text-xl font-semibold">Resumen de tu compra</h2>
                </div>

                <div class="p-5 space-y-5">
                    @foreach(($orden->detalles ?? []) as $item)
                        @php
                            $p = $item->producto ?? null;

                            $urlify = function ($src) {
                                if (!$src) return null;
                                if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://') || str_starts_with($src, 'data:')) return $src;
                                if (str_starts_with($src, '/storage/') || str_starts_with($src, 'storage/')) return str_starts_with($src, '/') ? $src : '/'.$src;
                                if (str_starts_with($src, 'img/') || str_starts_with($src, 'images/') || str_starts_with($src, 'assets/') || str_starts_with($src, 'uploads/')) return asset($src);
                                try { return \Storage::url($src); } catch (\Throwable $e) { return asset($src); }
                            };

                            $imgsRaw = is_array($item->getAttribute('imagenes_sesion') ?? null)
                                ? $item->getAttribute('imagenes_sesion')
                                : [];

                            if (empty($imgsRaw) && $p && $p->relationLoaded('imagenes')) {
                                $imgsRaw = $p->imagenes
                                    ->map(fn($im) => $im->url ?? $im->ruta ?? $im->path ?? null)
                                    ->filter()->values()->all();
                            }

                            if (empty($imgsRaw)) {
                                $fallbacks = array_filter([$p?->imagen_url ?? null, $p?->imagen ?? null]);
                                $imgsRaw = array_values(array_unique(array_merge($imgsRaw, $fallbacks)));
                            }

                            $imgs = array_values(array_filter(array_map($urlify, $imgsRaw)));
                            $firstImg = $imgs[0] ?? null;

                            $gid = 'gal-'.$loop->index.'-'.($item->id ?? $item->producto_id ?? 'x');
                        @endphp

                        <div class="flex items-start sm:items-center gap-4">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-lg overflow-hidden ring-1 ring-zinc-200 dark:ring-zinc-700 bg-zinc-100 dark:bg-zinc-900 shrink-0">
                                @if($firstImg)
                                    <img id="{{ $gid }}-main" src="{{ $firstImg }}" alt="{{ $p?->nombre ?? 'Producto' }}"
                                         class="w-full h-full object-cover"
                                         onerror="this.style.display='none'">
                                @else
                                    <div class="w-full h-full grid place-items-center text-xs opacity-60">Sin imagen</div>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="font-medium truncate">{{ $p?->nombre ?? 'Producto' }}</p>
                                <p class="text-sm opacity-70">
                                    Cantidad: {{ $item->cantidad }} ·
                                    Precio: ${{ number_format($item->precio_unitario ?? $item->precio ?? 0, 0, ',', '.') }}
                                </p>

                                @if(count($imgs) > 1)
                                    <div class="relative mt-2" data-gallery="{{ $gid }}">
                                        <button type="button" class="gal-btn gal-prev" aria-label="Anterior">‹</button>
                                        <div class="gal-track no-scrollbar" data-track>
                                            @foreach($imgs as $k => $src)
                                                <img src="{{ $src }}" alt="Imagen {{ $k+1 }}" class="gal-thumb" data-src="{{ $src }}" onerror="this.style.display='none'">
                                            @endforeach
                                        </div>
                                        <button type="button" class="gal-btn gal-next" aria-label="Siguiente">›</button>
                                    </div>
                                @endif
                            </div>

                            <div class="ml-auto font-semibold shrink-0 text-right">
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
                        <p class="text-xs opacity-80">Pagos seguros a través de ePayco. En modo pruebas no se realiza cargo real.</p>
                    </div>
                </div>

                {{-- Botón desktop --}}
                <div class="p-5 border-t border-zinc-200 dark:border-zinc-700 hidden lg:block">
                    <button type="button" id="btn-epayco"
                        class="w-full px-5 py-3 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-medium transition
                               {{ !$shippingOK ? 'opacity-60 cursor-not-allowed' : '' }}"
                        {{ !$shippingOK ? 'disabled' : '' }}>
                        Pagar con ePayco
                    </button>
                    @unless($shippingOK)
                        <p class="text-[12px] opacity-70 mt-2">Completa y guarda tus datos de facturación y envío para habilitar el pago.</p>
                    @endunless
                </div>
            </div>
        </div>

        {{-- Columna: Formulario envío/facturación --}}
        <div>
            <div class="rounded-2xl border bg-white border-zinc-200 shadow-sm
                        dark:bg-zinc-800 dark:border-zinc-700">
                <div class="p-5 border-b border-zinc-200 dark:border-zinc-700">
                    <h2 class="text-lg sm:text-xl font-semibold">Datos de facturación y envío</h2>
                </div>

                @php
                    // SIN preselección
                    $prefDepto     = data_get($datosEnvio, 'facturacion.departamento', '');
                    $prefCiudad    = data_get($datosEnvio, 'facturacion.ciudad', '');
                    $prefDeptoEnv  = data_get($datosEnvio, 'envio.departamento', '');
                    $prefCiudadEnv = data_get($datosEnvio, 'envio.ciudad', '');
                @endphp

                <form id="form-envio" method="POST" action="{{ route('checkout.pay.save') }}" class="p-5 space-y-6">
                    @csrf

                    {{-- FACTURACIÓN --}}
                    <div>
                        <h3 class="text-md font-semibold mb-3">Facturación</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="text-sm opacity-80">Nombre *</label>
                                <input type="text" name="facturacion[nombre]" value="{{ old('facturacion.nombre', data_get($datosEnvio,'facturacion.nombre', auth()->user()->name ?? '')) }}"
                                       class="w-full mt-1 rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900" required autocomplete="billing given-name">
                                @error('facturacion.nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm opacity-80">Apellidos *</label>
                                <input type="text" name="facturacion[apellidos]" value="{{ old('facturacion.apellidos', data_get($datosEnvio,'facturacion.apellidos')) }}"
                                       class="w-full mt-1 rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900" required autocomplete="billing family-name">
                                @error('facturacion.apellidos') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm opacity-80">Cédula *</label>
                                <input type="text" name="facturacion[cedula]" value="{{ old('facturacion.cedula', data_get($datosEnvio,'facturacion.cedula')) }}"
                                       class="w-full mt-1 rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900" required inputmode="numeric">
                                @error('facturacion.cedula') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm opacity-80">Teléfono *</label>
                                <input type="tel" name="facturacion[telefono]"
                                       value="{{ old('facturacion.telefono', data_get($datosEnvio,'facturacion.telefono')) }}"
                                       class="w-full mt-1 rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900"
                                       required inputmode="numeric" minlength="10" maxlength="10"
                                       pattern="\d{10}" title="Número celular colombiano (10 dígitos)"
                                       oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)">
                                @error('facturacion.telefono') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-sm opacity-80">Correo electrónico</label>
                                <input type="email" name="facturacion[email]" value="{{ old('facturacion.email', data_get($datosEnvio,'facturacion.email', auth()->user()->email ?? '')) }}"
                                       class="w-full mt-1 rounded-md border-zinc-300 dark-border-zinc-700 dark:bg-zinc-900" autocomplete="email">
                                @error('facturacion.email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-sm opacity-80">Dirección *</label>
                                <input type="text" name="facturacion[direccion]" value="{{ old('facturacion.direccion', data_get($datosEnvio,'facturacion.direccion')) }}"
                                       class="w-full mt-1 rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900" required autocomplete="billing street-address">
                                @error('facturacion.direccion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Departamento / Ciudad (SELECTS) --}}
                            <div>
                                <label class="text-sm opacity-80">Departamento *</label>
                                <select id="fact_departamento" name="facturacion[departamento]"
                                        class="w-full mt-1 rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900"
                                        required data-pref="">
                                    <option value="">Selecciona...</option>
                                </select>
                                @error('facturacion.departamento') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm opacity-80">Ciudad *</label>
                                <select id="fact_ciudad" name="facturacion[ciudad]"
                                        class="w-full mt-1 rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900"
                                        required data-pref="">
                                    <option value="">Selecciona...</option>
                                </select>
                                @error('facturacion.ciudad') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- ENVÍO --}}
                    <div>
                        <div class="flex items-center justify-between">
                            <h3 class="text-md font-semibold mb-3">Envío</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="text-sm opacity-80">Nombre *</label>
                                <input type="text" id="envio_nombre" name="envio[nombre]" value="{{ old('envio.nombre', data_get($datosEnvio,'envio.nombre')) }}"
                                       class="w-full mt-1 rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900" required autocomplete="shipping given-name">
                                @error('envio.nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm opacity-80">Apellidos *</label>
                                <input type="text" id="envio_apellidos" name="envio[apellidos]" value="{{ old('envio.apellidos', data_get($datosEnvio,'envio.apellidos')) }}"
                                       class="w-full mt-1 rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900" required autocomplete="shipping family-name">
                                @error('envio.apellidos') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-sm opacity-80">Dirección *</label>
                                <input type="text" id="envio_direccion" name="envio[direccion]" value="{{ old('envio.direccion', data_get($datosEnvio,'envio.direccion')) }}"
                                       class="w-full mt-1 rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900" required autocomplete="shipping street-address">
                                @error('envio.direccion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Departamento / Ciudad (SELECTS) --}}
                            <div>
                                <label class="text-sm opacity-80">Departamento *</label>
                                <select id="envio_departamento" name="envio[departamento]"
                                        class="w-full mt-1 rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900"
                                        required data-pref="">
                                    <option value="">Selecciona...</option>
                                </select>
                                @error('envio.departamento') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm opacity-80">Ciudad *</label>
                                <select id="envio_ciudad" name="envio[ciudad]"
                                        class="w-full mt-1 rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900"
                                        required data-pref="">
                                    <option value="">Selecciona...</option>
                                </select>
                                @error('envio.ciudad') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="text-sm opacity-80">Notas (opcional)</label>
                                <textarea name="envio[notas]" rows="2"
                                          class="w-full mt-1 rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">{{ old('envio.notas', data_get($datosEnvio,'envio.notas')) }}</textarea>
                                @error('envio.notas') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="pt-1">
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="checkbox" name="acepta_terminos" value="1" class="rounded"
                                   {{ old('acepta_terminos', data_get($datosEnvio,'acepta_terminos')) ? 'checked' : '' }} required>
                            Acepto los <a href="#" class="underline">términos y condiciones</a>.
                        </label>
                        @error('acepta_terminos') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-2 border-t border-dashed border-zinc-200 dark:border-zinc-700">
                        <button id="btn-save-shipping" type="submit"
                            class="mt-4 w-full px-5 py-3 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-medium transition">
                            Guardar datos de envío
                        </button>
                        <p id="save-hint" class="hidden text-xs mt-2 opacity-80">
                            Tus datos se ven completos. Haz clic en <strong>Guardar datos de envío</strong> para habilitar el pago.
                        </p>
                    </div>

                    @if($shippingOK)
                        <p class="text-xs opacity-70 mt-1">Tus datos están guardados. Ya puedes pagar con ePayco.</p>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <p class="text-xs opacity-70 mt-6 mb-24 lg:mb-0">* Si notas algún valor incorrecto, vuelve al carrito y actualiza tu compra.</p>
</div>

{{-- Barra fija inferior (SOLO móvil) --}}
<div class="mobile-paybar md:hidden fixed inset-x-0 bottom-0 z-30">
    <div class="mx-4 mb-4 rounded-xl border bg-white/95 backdrop-blur border-zinc-200 shadow-lg
                dark:bg-zinc-900/90 dark:border-zinc-700 px-4 py-3 flex items-center gap-3">
        <div class="flex-1">
            <div class="text-xs opacity-70 leading-tight">Total</div>
            <div class="text-lg font-semibold">
                ${{ number_format($orden->total ?? 0, 0, ',', '.') }}
            </div>
        </div>
        <button type="button" id="btn-epayco-mobile"
            class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-medium transition
                   {{ !$shippingOK ? 'opacity-60 cursor-not-allowed' : 'hover:bg-emerald-500' }}"
            {{ !$shippingOK ? 'disabled' : '' }}>
            Pagar
        </button>
    </div>
</div>

{{-- Widget oficial de ePayco --}}
<script src="https://checkout.epayco.co/checkout.js"></script>

{{-- Estilos del carrusel y barrita móvil --}}
<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
.gal-track{ display:flex; gap:.5rem; overflow-x:auto; scroll-snap-type:x mandatory; padding:0 .75rem; }
.gal-thumb{ width:2.25rem; height:2.25rem; object-fit:cover; border-radius:.375rem; border:1px solid rgba(113,113,122,.4); scroll-snap-align:center; cursor:pointer; }
.gal-btn{ position:absolute; top:50%; transform:translateY(-50%); width:1.75rem; height:1.75rem; border-radius:9999px; display:grid; place-items:center; background:rgba(0,0,0,.55); color:#fff; border:none; }
.gal-prev{ left:0; } .gal-next{ right:0; }
@media (max-width: 640px){ .gal-thumb{ width:2.5rem; height:2.5rem; } }
@media (min-width: 768px){ .mobile-paybar{ display:none !important; } }
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
        name: "{{ $epayco['name'] }}",
        description: "{{ $epayco['description'] }}",
        invoice: "{{ $epayco['invoice'] }}",
        currency: "{{ $epayco['currency'] }}",
        amount: "{{ $epayco['amount'] }}",
        tax_base: "0",
        tax: "0",
        country: "CO",
        response: "{{ $epayco['response_url'] }}",
        confirmation: "{{ $epayco['confirm_url'] }}",
        extra1: "{{ $epayco['extra1'] }}"
    };

    function openEpayco() { handler.open(data); }

    // ==== Guía cuando el pago está bloqueado ====
    function promptToFillOrSave() {
        var form = document.getElementById('form-envio');
        if (!form) return;

        var firstInvalid = form.querySelector('input:required:invalid, select:required:invalid, textarea:required:invalid, input[name="acepta_terminos"]:not(:checked)');
        if (firstInvalid) {
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstInvalid.classList.add('ring-2','ring-red-500');
            setTimeout(function () { firstInvalid.classList.remove('ring-2','ring-red-500'); }, 1600);
            if (firstInvalid.reportValidity) firstInvalid.reportValidity();
            return;
        }

        var saveBtn  = document.getElementById('btn-save-shipping');
        var saveHint = document.getElementById('save-hint');
        if (saveHint) saveHint.classList.remove('hidden');
        if (saveBtn) {
            saveBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
            saveBtn.classList.add('animate-bounce');
            setTimeout(function(){ saveBtn.classList.remove('animate-bounce'); }, 1500);
        }
    }

    // Click en los botones de pagar
    var btnDesktop = document.getElementById('btn-epayco');
    var btnMobile  = document.getElementById('btn-epayco-mobile');

    function handlePayClick(e) {
        if (e.currentTarget.hasAttribute('disabled')) {
            e.preventDefault();
            promptToFillOrSave();
            return;
        }
        openEpayco();
    }

    if (btnDesktop) btnDesktop.addEventListener('click', handlePayClick);
    if (btnMobile)  btnMobile.addEventListener('click', handlePayClick);

    // ====== Carruseles por producto ======
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

        track.querySelectorAll('.gal-thumb').forEach(function (thumb) {
            thumb.addEventListener('click', function () {
                var src = this.getAttribute('data-src') || this.getAttribute('src');
                if (main && src) main.src = src;
            });
        });

        window.addEventListener('resize', toggleArrows);
    });

    // ====== Departamento / Ciudad dinámicos (sin preselección) ======
    var GEO_URL = "{{ asset('colombia-geo.json') }}";

    function buildGeoMap(geoRaw) {
        var map = {};
        var arr = Array.isArray(geoRaw) ? geoRaw : (geoRaw.departamentos || []);
        arr.forEach(function (d) {
            if (!d || !d.nombre) return;
            map[d.nombre] = Array.isArray(d.ciudades) ? d.ciudades.slice() : [];
        });
        return map;
    }

    function clearOptions(select) {
        while (select.options.length > 1) select.remove(1);
    }

    function populateDepartamentos(select, geoMap, pref) {
        clearOptions(select);
        Object.keys(geoMap).sort().forEach(function(nombre){
            var opt = document.createElement('option');
            opt.value = nombre;
            opt.textContent = nombre;
            select.appendChild(opt);
        });
        if (pref) {
            var prefLower = String(pref).toLowerCase();
            Array.from(select.options).forEach(function(o){
                if (o.value.toLowerCase() === prefLower) o.selected = true;
            });
        }
    }

    function populateCiudades(select, geoMap, departamento, pref) {
        clearOptions(select);
        var cities = geoMap[departamento] || [];
        cities.slice().sort().forEach(function(city){
            var opt = document.createElement('option');
            opt.value = city;
            opt.textContent = city;
            select.appendChild(opt);
        });
        if (pref) {
            var prefLower = String(pref).toLowerCase();
            Array.from(select.options).forEach(function(o){
                if (o.value.toLowerCase() === prefLower) o.selected = true;
            });
        }
    }

    function wireDepCity(depSel, citySel, geoMap){
        depSel.addEventListener('change', function(){
            populateCiudades(citySel, geoMap, depSel.value, citySel.dataset.pref || '');
        });
    }

    // Prefs desde blade (VACÍOS)
    var pref = {
        fact: {
            dep:  document.getElementById('fact_departamento')?.dataset.pref || '',
            city: document.getElementById('fact_ciudad')?.dataset.pref || ''
        },
        env: {
            dep:  document.getElementById('envio_departamento')?.dataset.pref || '',
            city: document.getElementById('envio_ciudad')?.dataset.pref || ''
        }
    };

    var factDep  = document.getElementById('fact_departamento');
    var factCity = document.getElementById('fact_ciudad');
    var envDep   = document.getElementById('envio_departamento');
    var envCity  = document.getElementById('envio_ciudad');

    if (factDep && factCity && envDep && envCity) {
        fetch(GEO_URL, { cache: 'no-store' })
            .then(function(r){ return r.json(); })
            .then(function(geoRaw){
                var geo = buildGeoMap(geoRaw);

                // Facturación
                populateDepartamentos(factDep, geo, pref.fact.dep);
                wireDepCity(factDep, factCity, geo);
                if (factDep.value) populateCiudades(factCity, geo, factDep.value, pref.fact.city);

                // Envío
                populateDepartamentos(envDep, geo, pref.env.dep);
                wireDepCity(envDep, envCity, geo);
                if (envDep.value) populateCiudades(envCity, geo, envDep.value, pref.env.city);
            })
            .catch(function(err){
                console.error('No se pudo cargar colombia-geo.json', err);
            });
    }
})();
</script>
@endsection
