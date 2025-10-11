@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 sm:px-6 py-8 text-zinc-900 dark:text-zinc-100">

    {{-- Header --}}
    <div class="mb-4">
        <h1 class="text-2xl sm:text-3xl font-semibold">Pedidos & Envíos</h1>

        {{-- Filtros activos (chips) --}}
        @php
            $chips = [];
            if (!empty($q))             $chips['q'] = "#/ref/guía: $q";
            if (!empty($estado))       $chips['estado'] = "Pago: ".ucfirst($estado);
            if (!empty($estadoEnvio))  $chips['estado_envio'] = "Envío: ".str_replace('_',' ', ucfirst($estadoEnvio));
            if (!empty($desde))        $chips['desde'] = "Desde: $desde";
            if (!empty($hasta))        $chips['hasta'] = "Hasta: $hasta";
        @endphp
        @if(count($chips))
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach($chips as $k => $label)
                    <span class="inline-flex items-center gap-2 px-2 py-1 text-xs rounded-full bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                        {{ $label }}
                    </span>
                @endforeach
                <a href="{{ route('ordenes.admin') }}"
                   class="text-xs underline opacity-80 hover:opacity-100">Limpiar</a>
            </div>
        @endif
    </div>

    {{-- Tarjetas resumen --}}
    @isset($stats)
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="rounded-xl border bg-white dark:bg-zinc-800 dark:border-zinc-700 border-zinc-200 p-4">
            <div class="text-xs opacity-70">Pendientes</div>
            <div class="text-2xl font-semibold">{{ $stats['pendientes'] ?? 0 }}</div>
        </div>
        <div class="rounded-xl border bg-white dark:bg-zinc-800 dark:border-zinc-700 border-zinc-200 p-4">
            <div class="text-xs opacity-70">Pagadas</div>
            <div class="text-2xl font-semibold">{{ $stats['pagadas'] ?? 0 }}</div>
        </div>
        <div class="rounded-xl border bg-white dark:bg-zinc-800 dark:border-zinc-700 border-zinc-200 p-4">
            <div class="text-xs opacity-70">En tránsito</div>
            <div class="text-2xl font-semibold">{{ $stats['en_transito'] ?? 0 }}</div>
        </div>
        <div class="rounded-xl border bg-white dark:bg-zinc-800 dark:border-zinc-700 border-zinc-200 p-4">
            <div class="text-xs opacity-70">Entregados</div>
            <div class="text-2xl font-semibold">{{ $stats['entregados'] ?? 0 }}</div>
        </div>
    </div>
    @endisset

    {{-- Tabla --}}
    <div class="rounded-2xl border bg-white dark:bg-zinc-800 dark:border-zinc-700 border-zinc-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
            <h2 class="text-lg font-semibold">Órdenes recientes</h2>

            {{-- Botones de acciones (ahora aquí) --}}
            <div class="flex items-center gap-2">
                <button type="button" id="btnOpenFilters"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-md bg-emerald-600 text-white hover:bg-emerald-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M3 5h18v2H3V5Zm4 6h10v2H7v-2Zm-2 6h14v2H5v-2Z"/></svg>
                    Filtros
                </button>
                <a href="{{ route('ordenes.admin') }}"
                   class="px-3 py-2 rounded-md bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600">
                    Limpiar
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-900/40">
                    <tr class="text-left">
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Estado pago</th>
                        <th class="px-4 py-3">Estado envío</th>
                        <th class="px-4 py-3">Ref. pago</th>
                        <th class="px-4 py-3">Transp./Guía</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($ordenes as $orden)
                        @php $env = $orden->envioRegistro; @endphp
                        <tr>
                            <td class="px-4 py-3 font-medium">#{{ $orden->id }}</td>
                            <td class="px-4 py-3">
                                {{ $orden->user?->name ?? '—' }}
                                <div class="text-xs opacity-70">{{ $orden->created_at?->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-4 py-3 font-semibold">
                                ${{ number_format($orden->total ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs
                                    @class([
                                        'bg-yellow-100 text-yellow-800' => $orden->estado === 'pendiente',
                                        'bg-emerald-100 text-emerald-800' => $orden->estado === 'pagada',
                                        'bg-red-100 text-red-800' => in_array($orden->estado, ['rechazada','cancelada']),
                                        'bg-zinc-100 text-zinc-800' => !in_array($orden->estado, ['pendiente','pagada','rechazada','cancelada']),
                                    ])
                                ">{{ ucfirst($orden->estado ?? '—') }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs
                                    @class([
                                        'bg-zinc-100 text-zinc-800' => !($env?->estado_envio),
                                        'bg-blue-100 text-blue-800' => $env?->estado_envio === 'en_transito',
                                        'bg-emerald-100 text-emerald-800' => $env?->estado_envio === 'entregado',
                                        'bg-yellow-100 text-yellow-800' => $env?->estado_envio === 'pendiente',
                                        'bg-red-100 text-red-800' => $env?->estado_envio === 'devuelto',
                                    ])
                                ">{{ $env?->estado_envio ? str_replace('_',' ', ucfirst($env->estado_envio)) : '—' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @php $ref = $orden->ultimo_invoice ?? $orden->ref_epayco ?? null; @endphp
                                <div class="flex items-center gap-2">
                                    <span class="text-xs select-all">{{ $ref ?? '—' }}</span>
                                    @if($ref)
                                        <button type="button" class="text-xs px-2 py-1 rounded border border-zinc-300 dark:border-zinc-600 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                                onclick="navigator.clipboard?.writeText('{{ $ref }}')">
                                            Copiar
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-xs">
                                    <div class="opacity-80">{{ $env?->transportadora ?? '—' }}</div>
                                    <div class="opacity-60">{{ $env?->numero_guia ?? '' }}</div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('ordenes.show', $orden->id) }}"
                                   class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md bg-emerald-600 text-white hover:bg-emerald-500 transition">
                                    Gestionar
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M13.172 12 8.222 7.05l1.414-1.414L16 12l-6.364 6.364-1.414-1.414z"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center opacity-70">No hay órdenes para mostrar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t border-zinc-200 dark:border-zinc-700">
            {{ $ordenes->links() }}
        </div>
    </div>
</div>

{{-- ============================= --}}
{{-- Modal de filtros (GET) centrado --}}
{{-- ============================= --}}
<div id="filtersModal" class="fixed inset-0 z-50 hidden">
    {{-- Fondo --}}
    <div class="absolute inset-0 bg-black/40" aria-hidden="true"></div>

    {{-- Contenedor centrado (padding evita superposición con el navbar) --}}
    <div class="relative h-full w-full flex items-start md:items-center justify-center p-4 md:p-6">
        <div class="w-full max-w-3xl rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-xl">
            <div class="px-5 py-3 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold">Filtrar pedidos</h3>
                <button type="button" id="btnCloseFilters" class="p-2 rounded hover:bg-zinc-100 dark:hover:bg-zinc-800">✕</button>
            </div>

            <form method="GET" class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="text-xs opacity-70">Buscar</label>
                    <input type="text" name="q" value="{{ $q ?? '' }}"
                           placeholder="#orden, ref, guía, transportadora"
                           class="w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 px-3 py-2">
                </div>

                <div>
                    <label class="text-xs opacity-70">Estado de pago</label>
                    <select name="estado" class="w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 px-3 py-2">
                        <option value="">Todos</option>
                        @foreach(['pendiente','pagada','rechazada','cancelada','enviada','entregada'] as $e)
                            <option value="{{ $e }}" @selected(($estado ?? '') === $e)>{{ ucfirst($e) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs opacity-70">Estado de envío</label>
                    <select name="estado_envio" class="w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 px-3 py-2">
                        <option value="">Todos</option>
                        @foreach(['pendiente'=>'Pendiente','en_transito'=>'En tránsito','entregado'=>'Entregado','devuelto'=>'Devuelto'] as $k => $v)
                            <option value="{{ $k }}" @selected(($estadoEnvio ?? '') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs opacity-70">Desde</label>
                    <input type="date" name="desde" value="{{ $desde ?? '' }}"
                           class="w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 px-3 py-2">
                </div>
                <div>
                    <label class="text-xs opacity-70">Hasta</label>
                    <input type="date" name="hasta" value="{{ $hasta ?? '' }}"
                           class="w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 px-3 py-2">
                </div>

                <div class="sm:col-span-2 flex items-center justify-end gap-2 pt-2">
                    <a href="{{ route('ordenes.admin') }}"
                       class="px-4 py-2 rounded-md bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600">
                        Limpiar
                    </a>
                    <button class="px-4 py-2 rounded-md bg-emerald-600 text-white hover:bg-emerald-500">
                        Aplicar filtros
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- JS para abrir/cerrar modal (centrado) --}}
<script>
    const modal   = document.getElementById('filtersModal');
    const openBtn = document.getElementById('btnOpenFilters');
    const closeBtn= document.getElementById('btnCloseFilters');

    function openModal() {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }
    function closeModal() {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    openBtn?.addEventListener('click', openModal);
    closeBtn?.addEventListener('click', closeModal);

    // Cerrar al hacer click fuera del panel
    modal?.addEventListener('click', (e) => {
        // si el click es en el fondo oscuro, cierra
        if (e.target === modal.querySelector('.absolute.inset-0')) closeModal();
    });
</script>
@endsection
