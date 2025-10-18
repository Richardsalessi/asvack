@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 text-zinc-900 dark:text-zinc-100">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">
            {{ $tarifa->exists ? 'Editar tarifa' : 'Nueva tarifa' }}
        </h1>

        <a href="{{ route('admin.tarifas.index') }}"
           class="inline-flex items-center px-3 py-2 rounded-md bg-zinc-200 hover:bg-zinc-300 text-zinc-900 dark:bg-zinc-700 dark:hover:bg-zinc-600 dark:text-zinc-100 transition">
            Volver
        </a>
    </div>

    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 sm:p-6 max-w-2xl">
        <form method="POST"
              action="{{ $tarifa->exists ? route('admin.tarifas.update', $tarifa) : route('admin.tarifas.store') }}"
              class="grid grid-cols-1 gap-5">
            @csrf
            @if($tarifa->exists) @method('PUT') @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm opacity-80">Ciudad</label>
                    <input
                        name="ciudad"
                        value="{{ old('ciudad', $tarifa->ciudad) }}"
                        required
                        class="w-full rounded-md border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-emerald-400 dark:focus:ring-emerald-600"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="mb-1 block text-sm opacity-80">Costo (COP)</label>
                    <input
                        type="number" min="0" step="1"
                        name="costo"
                        value="{{ old('costo', $tarifa->costo ?? 0) }}"
                        required
                        class="w-full rounded-md border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-emerald-400 dark:focus:ring-emerald-600"
                    >
                </div>

                <div>
                    <label class="mb-1 block text-sm opacity-80">Tiempo estimado (opcional)</label>
                    <input
                        name="tiempo_estimado"
                        value="{{ old('tiempo_estimado', $tarifa->tiempo_estimado) }}"
                        class="w-full rounded-md border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-emerald-400 dark:focus:ring-emerald-600"
                    >
                </div>
            </div>

            <label class="inline-flex items-center gap-2 select-none">
                <input
                    type="checkbox"
                    name="activo"
                    value="1"
                    @checked(old('activo', $tarifa->activo ?? true))
                    class="rounded border-zinc-300 dark:border-zinc-600 text-emerald-600 focus:ring-emerald-500"
                >
                <span>Activo</span>
            </label>

            <div class="flex flex-col sm:flex-row sm:items-center gap-3 pt-2">
                <button
                    class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-emerald-600 hover:bg-emerald-500 text-white transition">
                    Guardar
                </button>
                <a href="{{ route('admin.tarifas.index') }}"
                   class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-zinc-200 hover:bg-zinc-300 text-zinc-900 dark:bg-zinc-700 dark:hover:bg-zinc-600 dark:text-zinc-100 transition">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
