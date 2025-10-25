@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 sm:px-6 py-8 text-zinc-900 dark:text-zinc-100">

    @if(session('success'))
        <div class="mb-4 rounded-md bg-emerald-100 text-emerald-800 px-4 py-2">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="mb-4 rounded-md bg-blue-100 text-blue-800 px-4 py-2">{{ session('info') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-md bg-red-100 text-red-800 px-4 py-2">{{ session('error') }}</div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h1 class="text-2xl sm:text-3xl font-semibold">Orden #{{ $orden->id }}</h1>
        <a href="{{ route('ordenes.index') }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-md bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600">
           ← Volver a Mis compras
        </a>
    </div>

    @php
        // Badge de Estado de pago (cliente)
        $pagoClass = match ($orden->estado) {
            'pagada'                 => 'bg-emerald-100 text-emerald-800',
            'pendiente'              => 'bg-yellow-100 text-yellow-800',
            'rechazada', 'cancelada' => 'bg-red-100 text-red-800',
            default                  => 'bg-zinc-100 text-zinc-800',
        };
    @endphp

    <div class="text-base sm:text-lg opacity-80 mb-6 leading-relaxed flex flex-wrap items-center gap-2">
        <span><span class="font-semibold">Creada:</span> {{ $orden->created_at?->format('d/m/Y H:i') }}</span>
        <span class="mx-1 opacity-40">•</span>

        <span class="font-semibold">Estado pago:</span>
        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold {{ $pagoClass }}">
            {{ ucfirst($orden->estado ?? '—') }}
        </span>

        <span class="mx-1 opacity-40">•</span>
        <span>
            <span class="font-semibold">Total:</span>
            <span class="font-medium text-zinc-900 dark:text-zinc-100">
                ${{ number_format((float)($orden->total ?? 0), 0, ',', '.') }}
            </span>
        </span>
    </div>

    @php $env = $orden->envioRegistro; @endphp
    @if($env?->estado_envio)
        <div class="mb-6 inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-base font-semibold
            @class([
                'bg-yellow-100 text-yellow-800'   => $env->estado_envio === 'pendiente',
                'bg-blue-100 text-blue-800'       => $env->estado_envio === 'en_transito',
                'bg-emerald-100 text-emerald-800' => $env->estado_envio === 'entregado',
                'bg-red-100 text-red-800'         => $env->estado_envio === 'devuelto',
                'bg-zinc-100 text-zinc-800'       => !in_array($env->estado_envio, ['pendiente','en_transito','entregado','devuelto']),
            ])">
            Estado de envío: {{ str_replace('_',' ', ucfirst($env->estado_envio)) }}
            @if($env->transportadora || $env->numero_guia)
                <span class="opacity-60">•</span>
                <span>{{ $env->transportadora ?: '—' }} {{ $env->numero_guia ? '· #'.$env->numero_guia : '' }}</span>
            @endif
        </div>
    @endif

    <div class="rounded-xl border bg-white dark:bg-zinc-800 dark:border-zinc-700 border-zinc-200 overflow-hidden mb-6">
        <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-700">
            <h2 class="text-lg font-semibold">Productos</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-900/40">
                <tr class="text-left">
                    <th class="px-4 py-3">Producto</th>
                    <th class="px-4 py-3 text-center">Cant.</th>
                    <th class="px-4 py-3 text-right">Precio</th>
                    <th class="px-4 py-3 text-right">Subtotal</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @foreach(($orden->detalles ?? []) as $d)
                    @php
                        $urlify = function ($src) {
                            if (!$src) return null;
                            if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://') || str_starts_with($src, 'data:')) return $src;
                            if (str_starts_with($src, '/storage/') || str_starts_with($src, 'storage/')) return str_starts_with($src, '/') ? $src : '/'.$src;
                            if (str_starts_with($src, 'img/') || str_starts_with($src, 'images/') || str_starts_with($src, 'assets/') || str_starts_with($src, 'uploads/')) return asset($src);
                            try { return \Storage::url($src); } catch (\Throwable $e) { return asset($src); }
                        };

                        $unit  = $d->precio_unitario ?? $d->precio ?? $d->valor_unitario ?? 0;
                        $cant  = (int)($d->cantidad ?? 1);
                        $linea = $d->subtotal ?? ($unit * $cant);

                        $p       = $d->producto;
                        $imgsRaw = $p?->imagenes
                                    ? $p->imagenes->map(fn($im) => $im->url ?? $im->ruta ?? $im->path ?? null)->filter()->values()->all()
                                    : [];
                        if (empty($imgsRaw)) {
                            $fallbacks = array_filter([$p?->imagen_url ?? null, $p?->imagen ?? null, $d->imagenes_sesion[0] ?? null]);
                            $imgsRaw   = array_values(array_unique($fallbacks));
                        }
                        $imgs = array_values(array_filter(array_map($urlify, $imgsRaw)));

                        $placeholder = 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80"><rect width="100%" height="100%" fill="#e5e7eb"/><text x="50%" y="52%" dominant-baseline="middle" text-anchor="middle" fill="#6b7280" font-family="Arial" font-size="10">Sin imagen</text></svg>');
                        $img = $imgs[0] ?? $placeholder;
                        $nombre = $p?->nombre ?? ($d->nombre_producto ?? 'Producto');
                    @endphp
                    <tr>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ $img }}" alt="{{ $nombre }}"
                                     class="w-12 h-12 rounded-md object-cover border border-zinc-200 dark:border-zinc-700"
                                     onerror="this.src='{{ $placeholder }}'">
                                <span class="font-medium">{{ $nombre }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">{{ $cant }}</td>
                        <td class="px-4 py-3 text-right">${{ number_format((float)$unit, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">${{ number_format((float)$linea, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t border-zinc-200 dark:border-zinc-700 text-right">
            <div>Subtotal: ${{ number_format((float)($orden->subtotal ?? 0), 0, ',', '.') }}</div>
            <div>Envío: ${{ number_format((float)($orden->envio ?? 0), 0, ',', '.') }}</div>
            <div class="text-lg font-semibold">Total: ${{ number_format((float)($orden->total ?? 0), 0, ',', '.') }}</div>
        </div>
    </div>

    @php $dir = data_get($orden->datos_envio, 'envio'); @endphp
    @if($dir)
        <div class="rounded-xl border bg-white dark:bg-zinc-800 dark:border-zinc-700 border-zinc-200 p-4 mb-6">
            <h2 class="text-lg font-semibold mb-2">Dirección de envío</h2>
            <div class="text-sm">
                {{ trim(($dir['nombre'] ?? '').' '.($dir['apellidos'] ?? '')) }}<br>
                {{ $dir['direccion'] ?? '' }}<br>
                {{ trim(($dir['ciudad'] ?? '').', '.($dir['departamento'] ?? '')) }}
            </div>
        </div>
    @endif

</div>
@endsection
