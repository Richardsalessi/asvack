@extends('layouts.app')

@section('content')
@php
    // Datos base que puedan venir por query o por la vista
    $data     = isset($data) && is_array($data) ? $data : request()->all();
    $refPayco = $ref_payco
        ?? ($data['x_ref_payco'] ?? null)
        ?? request('ref_payco');

    // Si hay modelo, carga perezosa de imágenes de productos
    if (!empty($orden)) {
        try { $orden->loadMissing('detalles.producto.imagenes'); } catch (\Throwable $e) {}
    }

    // Normaliza el estado que devuelve ePayco en el redirect
    $estadoRaw = strtoupper((string)($data['x_response'] ?? request('x_response') ?? 'PENDIENTE'));
    $estadoMostrar = match ($estadoRaw) {
        'APROBADA', 'ACEPTADA', 'APROBADO', 'APPROVED' => 'ACEPTADA',
        'RECHAZADA', 'RECHAZADO', 'DECLINED'          => 'RECHAZADA',
        'CANCELADA', 'CANCELADO', 'CANCELED'          => 'CANCELADA',
        default                                        => 'PENDIENTE',
    };

    $clase = [
        'ACEPTADA'  => 'bg-green-600',
        'RECHAZADA' => 'bg-red-600',
        'CANCELADA' => 'bg-red-600',
        'PENDIENTE' => 'bg-yellow-600',
    ][$estadoMostrar] ?? 'bg-gray-600';

    // Helper para normalizar URLs de imágenes
    $urlify = function ($src) {
        if (!$src) return null;
        if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://') || str_starts_with($src, 'data:')) return $src;
        if (str_starts_with($src, '/storage/')) return $src;
        try { return \Storage::url($src); } catch (\Throwable $e) { return asset($src); }
    };
@endphp

<div class="container mx-auto max-w-3xl py-8 text-zinc-900 dark:text-zinc-100">
    <h1 class="text-2xl font-semibold mb-4">Resultado del pago</h1>

    <div class="rounded-xl p-4 text-white {{ $clase }} mb-6">
        <p class="text-lg">
            Estado reportado por ePayco: <strong>{{ $estadoMostrar }}</strong>
        </p>
        <p class="text-sm opacity-90">
            Transacción: {{ $data['x_transaction_id'] ?? 'N/D' }} ·
            Ref. ePayco: {{ $refPayco ?: 'N/D' }}
        </p>
    </div>

    @if(!empty($orden))
        <div class="rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900/40 p-5 mb-6">
            <h2 class="text-xl font-medium mb-2">Resumen de tu orden #{{ $orden->id }}</h2>
            <p class="mb-2">Estado en nuestra tienda: <strong>{{ ucfirst($orden->estado) }}</strong></p>
            <p class="mb-4">Total: <strong>${{ number_format($orden->total, 0, ',', '.') }}</strong></p>

            <div class="space-y-3">
                @foreach(($orden->detalles ?? []) as $item)
                    @php
                        $p = $item->producto ?? null;

                        // 1) relación imagenes
                        $imgsRaw = [];
                        if ($p && $p->relationLoaded('imagenes')) {
                            $imgsRaw = $p->imagenes
                                ->map(fn($im) => $im->url ?? $im->ruta ?? $im->path ?? null)
                                ->filter()->values()->all();
                        }
                        // 2) fallbacks directos
                        $fallbacks = array_filter([$p?->imagen_url ?? null, $p?->imagen ?? null]);
                        $imgsRaw = array_values(array_unique(array_merge($imgsRaw, $fallbacks)));

                        // 3) normaliza
                        $imgs = array_values(array_filter(array_map($urlify, $imgsRaw)));
                        $first = $imgs[0] ?? null;
                    @endphp

                    <div class="flex items-center gap-4 border-b border-zinc-200 dark:border-zinc-700 pb-3 last:border-0">
                        <div class="w-14 h-14 rounded-lg overflow-hidden ring-1 ring-zinc-200 dark:ring-zinc-700 bg-zinc-100 dark:bg-zinc-900 shrink-0">
                            @if($first)
                                <img src="{{ $first }}" alt="{{ $p?->nombre ?? 'Producto' }}" class="w-full h-full object-cover" onerror="this.style.display='none'">
                            @else
                                <div class="w-full h-full grid place-items-center text-[10px] opacity-60">Sin imagen</div>
                            @endif
                        </div>

                        <div class="min-w-0">
                            <p class="font-medium truncate">{{ $p?->nombre ?? 'Producto' }}</p>
                            <p class="text-sm opacity-70">
                                Cantidad: {{ $item->cantidad }} ·
                                Precio: ${{ number_format($item->precio ?? $item->precio_unitario ?? 0, 0, ',', '.') }}
                            </p>

                            @if(count($imgs) > 1)
                                <div class="flex gap-1 mt-1">
                                    @foreach($imgs as $iSrc)
                                        <img src="{{ $iSrc }}" class="w-7 h-7 object-cover rounded border border-zinc-200 dark:border-zinc-700" alt="img" onerror="this.style.display='none'">
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="ml-auto font-semibold">
                            ${{ number_format(($item->precio ?? $item->precio_unitario ?? 0) * $item->cantidad, 0, ',', '.') }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 flex gap-3 flex-wrap">
                <a href="{{ route('ordenes.show', $orden) }}"
                   class="inline-block rounded-lg px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white">
                    Ver detalle de la compra
                </a>
                <a href="{{ route('catalogo') }}"
                   class="inline-block rounded-lg px-4 py-2 bg-zinc-700 hover:bg-zinc-600 text-white">
                    Seguir comprando
                </a>
            </div>
        </div>
    @else
        <div class="rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900/40 p-5 mb-6">
            <p class="mb-1">No encontramos la orden asociada.</p>
            <p class="text-sm opacity-80">
                Ref. ePayco: <strong>{{ $refPayco ?: 'N/D' }}</strong>.
                Si ya realizaste el pago, revisa tu historial en <em>Mis compras</em>.
            </p>
        </div>
    @endif

    <p class="text-xs opacity-70">
        * El estado final lo confirma el <strong>webhook</strong>. Si ves alguna inconsistencia,
        actualiza esta página en un minuto o revisa <a class="underline" href="{{ route('ordenes.index') }}">Mis compras</a>.
    </p>
</div>

{{-- Auto-refresh si todo sigue pendiente (esperando webhook) --}}
@if(($orden && $orden->estado === 'pendiente') || $estadoMostrar === 'PENDIENTE')
<script>
    (function(){
        // Reintenta hasta 5 veces cada 8s (~40s) para dar tiempo al webhook
        let tries = 0, max = 5, dot = 0;
        const tick = () => {
            tries++;
            if (tries <= max) location.reload();
        };
        // Indicador simple
        const div = document.createElement('div');
        div.style.cssText = 'position:fixed;bottom:14px;left:50%;transform:translateX(-50%);font-size:12px;opacity:.7';
        div.textContent = 'Sincronizando con el banco…';
        document.body.appendChild(div);
        setInterval(() => { dot = (dot+1)%4; div.textContent = 'Sincronizando con el banco' + '.'.repeat(dot); }, 800);
        setTimeout(tick, 8000);
    })();
</script>
@endif
@endsection
