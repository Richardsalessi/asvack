@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 text-zinc-900 dark:text-zinc-100">
    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
        <h1 class="text-2xl font-semibold">Tarifas de envío</h1>

        <a href="{{ route('admin.tarifas.create') }}"
           class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-emerald-600 hover:bg-emerald-500 text-white transition">
            Nueva tarifa
        </a>
    </div>

    {{-- Buscador --}}
    <form method="GET" class="mb-6">
        <div class="relative w-full sm:max-w-md">
            <input
                name="q"
                value="{{ $q }}"
                placeholder="Buscar ciudad"
                class="w-full rounded-md border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 placeholder-zinc-400 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400 dark:focus:ring-emerald-600"
            >
            @if(request('q'))
                <a href="{{ route('admin.tarifas.index') }}"
                   class="absolute right-2 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-sm">
                    Limpiar
                </a>
            @endif
        </div>
    </form>

    {{-- Tabla --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-zinc-600 dark:text-zinc-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Ciudad</th>
                        <th class="px-4 py-3 text-right font-medium">Costo</th>
                        <th class="px-4 py-3 text-left font-medium">ETA</th>
                        <th class="px-4 py-3 text-center font-medium">Activo</th>
                        <th class="px-4 py-3 text-right font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($tarifas as $t)
                        <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition">
                            <td class="px-4 py-3">{{ $t->ciudad }}</td>
                            <td class="px-4 py-3 text-right">${{ number_format($t->costo, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">{{ $t->tiempo_estimado ?: '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($t->activo)
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">
                                        Sí
                                    </span>
                                @else
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                        No
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.tarifas.edit', $t) }}"
                                       class="inline-flex items-center px-3 py-1.5 rounded-md bg-blue-600 hover:bg-blue-500 text-white transition">
                                        Editar
                                    </a>

                                    <form method="POST" action="{{ route('admin.tarifas.destroy', $t) }}"
                                          onsubmit="return confirm('¿Eliminar tarifa?')">
                                        @csrf @method('DELETE')
                                        <button
                                            class="inline-flex items-center px-3 py-1.5 rounded-md bg-red-600 hover:bg-red-500 text-white transition">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-zinc-500 dark:text-zinc-400">
                                Sin tarifas
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Paginación --}}
    <div class="mt-6">
        {{ $tarifas->withQueryString()->links() }}
    </div>
</div>
@endsection
