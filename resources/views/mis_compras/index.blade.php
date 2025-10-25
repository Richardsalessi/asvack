@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 sm:px-6 py-8 text-zinc-900 dark:text-zinc-100">
    <h1 class="text-2xl sm:text-3xl font-semibold mb-4">Mis compras</h1>

    {{-- Mensajes --}}
    @if(session('success'))
        <div class="mb-4 rounded-md bg-emerald-100 text-emerald-800 px-4 py-2">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="mb-4 rounded-md bg-blue-100 text-blue-800 px-4 py-2">{{ session('info') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-md bg-red-100 text-red-800 px-4 py-2">{{ session('error') }}</div>
    @endif

    {{-- FILTRO --}}
    <form method="GET" class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <label for="estado" class="text-sm font-medium opacity-80">Filtrar por estado:</label>
            <select name="estado" id="estado"
                    class="mt-1 sm:mt-0 sm:ml-2 px-3 py-2 border border-zinc-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-sm"
                    onchange="this.form.submit()">
                <option value="">Todas</option>
                <option value="pagada" {{ request('estado') === 'pagada' ? 'selected' : '' }}>Pagadas</option>
                <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendientes</option>
                <option value="rechazada" {{ request('estado') === 'rechazada' ? 'selected' : '' }}>Rechazadas</option>
                <option value="cancelada" {{ request('estado') === 'cancelada' ? 'selected' : '' }}>Canceladas</option>
            </select>
        </div>
        @if(request('estado'))
            <a href="{{ route('ordenes.index') }}"
               class="text-sm text-emerald-600 hover:underline">Limpiar filtro</a>
        @endif
    </form>

    {{-- TABLA --}}
    <div class="rounded-2xl border bg-white dark:bg-zinc-800 dark:border-zinc-700 border-zinc-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-700">
            <h2 class="text-lg font-semibold">Órdenes</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-900/40">
                <tr class="text-left">
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">Fecha</th>
                    <th class="px-4 py-3">Producto</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Pago</th>
                    <th class="px-4 py-3">Envío</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($ordenes as $o)
                    @php
                        $urlify = function ($src) {
                            if (!$src) return null;
                            if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://') || str_starts_with($src, 'data:')) return $src;
                            if (str_starts_with($src, '/storage/') || str_starts_with($src, 'storage/')) return str_starts_with($src, '/') ? $src : '/'.$src;
                            if (str_starts_with($src, 'img/') || str_starts_with($src, 'images/') || str_starts_with($src, 'assets/') || str_starts_with($src, 'uploads/')) return asset($src);
                            try { return \Storage::url($src); } catch (\Throwable $e) { return asset($src); }
                        };

                        $env       = $o->envioRegistro;
                        $firstDet  = $o->detalles->first();
                        $product   = $firstDet?->producto;

                        $imgsRaw = $product?->imagenes
                                    ? $product->imagenes->map(fn($im) => $im->url ?? $im->ruta ?? $im->path ?? null)->filter()->values()->all()
                                    : [];
                        if (empty($imgsRaw)) {
                            $fallbacks = array_filter([$product?->imagen_url ?? null, $product?->imagen ?? null]);
                            $imgsRaw   = array_values(array_unique($fallbacks));
                        }
                        $imgs = array_values(array_filter(array_map($urlify, $imgsRaw)));
                        $placeholder = 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80"><rect width="100%" height="100%" fill="#e5e7eb"/><text x="50%" y="52%" dominant-baseline="middle" text-anchor="middle" fill="#6b7280" font-family="Arial" font-size="10">Sin imagen</text></svg>');
                        $firstImg  = $imgs[0] ?? $placeholder;
                        $firstName = $product?->nombre ?? ($firstDet?->nombre_producto ?? '—');
                        $resto     = max(($o->detalles->count() ?? 0) - 1, 0);
                    @endphp

                    <tr>
                        <td class="px-4 py-3 font-medium">#{{ $o->id }}</td>
                        <td class="px-4 py-3">{{ $o->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ $firstImg }}" alt="Producto"
                                     class="w-10 h-10 rounded-md object-cover border border-zinc-200 dark:border-zinc-700"
                                     onerror="this.src='{{ $placeholder }}'">
                                <div class="text-sm">
                                    {{ \Illuminate\Support\Str::limit($firstName, 42) }}
                                    @if($resto > 0)
                                        <span class="opacity-60">(+{{ $resto }})</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 font-semibold">${{ number_format((float)($o->total ?? 0), 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs sm:text-sm font-semibold
                                @class([
                                    'bg-yellow-100 text-yellow-800'   => $o->estado === 'pendiente',
                                    'bg-emerald-100 text-emerald-800' => $o->estado === 'pagada',
                                    'bg-red-100 text-red-800'         => in_array($o->estado, ['rechazada','cancelada']),
                                    'bg-zinc-100 text-zinc-800'       => !in_array($o->estado, ['pendiente','pagada','rechazada','cancelada']),
                                ])">
                                {{ ucfirst($o->estado ?? '—') }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs sm:text-sm font-semibold
                                @class([
                                    'bg-zinc-100 text-zinc-800'       => !($env?->estado_envio),
                                    'bg-blue-100 text-blue-800'       => $env?->estado_envio === 'en_transito',
                                    'bg-emerald-100 text-emerald-800' => $env?->estado_envio === 'entregado',
                                    'bg-yellow-100 text-yellow-800'   => $env?->estado_envio === 'pendiente',
                                    'bg-red-100 text-red-800'         => $env?->estado_envio === 'devuelto',
                                ])">
                                {{ $env?->estado_envio ? str_replace('_', ' ', ucfirst($env->estado_envio)) : '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('ordenes.show', $o) }}"
                               class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md bg-emerald-600 text-white hover:bg-emerald-500 transition">
                               Ver detalle
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center opacity-70">No tienes compras todavía.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t border-zinc-200 dark:border-zinc-700">
            {{ $ordenes->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
