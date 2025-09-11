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

        <div class="p-5 space-y-4">
            @foreach(($orden->detalles ?? []) as $item)
                @php
                    $p = $item->producto ?? null;
                    $img = $p?->imagen ?? $p?->imagen_url ?? null;
                @endphp
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-lg overflow-hidden ring-1 ring-zinc-200 dark:ring-zinc-700 bg-zinc-100 dark:bg-zinc-900 shrink-0">
                        @if($img)
                            <img src="{{ $img }}" alt="{{ $p?->nombre ?? 'Producto' }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full grid place-items-center text-xs opacity-60">
                                Sin imagen
                            </div>
                        @endif
                    </div>

                    <div class="min-w-0">
                        <p class="font-medium truncate">{{ $p?->nombre ?? 'Producto' }}</p>
                        <p class="text-sm opacity-70">
                            Cantidad: {{ $item->cantidad }} ·
                            Precio: ${{ number_format($item->precio_unitario ?? $item->precio ?? 0, 0, ',', '.') }}
                        </p>
                    </div>

                    <div class="ml-auto font-semibold">
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
<script>
(function () {
    // Configurar el widget
    var handler = ePayco.checkout.configure({
        key: "{{ $epayco['public_key'] }}",
        test: {{ $epayco['test'] ? 'true' : 'false' }},
        language: "{{ $epayco['lang'] }}",
        external: "true" // redirige a response al finalizar
    });

    // Datos del pago (monto SIEMPRE desde la orden)
    var data = {
        name:        "{{ $epayco['name'] }}",
        description: "{{ $epayco['description'] }}",
        invoice:     "{{ $epayco['invoice'] }}",
        currency:    "{{ $epayco['currency'] }}",
        amount:      "{{ $epayco['amount'] }}", // p.ej. 200000.00

        // impuestos (0 si no aplican)
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
})();
</script>
@endsection
