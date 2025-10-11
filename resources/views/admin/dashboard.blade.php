@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-8 bg-white dark:bg-gray-800 shadow rounded-lg">
        <h1 class="text-3xl font-semibold text-gray-800 dark:text-white mb-2">Dashboard Administrador</h1>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Bienvenido, {{ Auth::user()->name }}.</p>

        @role('admin')
            {{-- Aviso de rol --}}
            <div class="bg-green-100 text-green-800 dark:bg-green-900/60 dark:text-green-200 p-4 rounded-md mb-6">
                <p class="font-medium">Tienes acceso total al sistema.</p>
            </div>

            {{-- Resumen (opcional) de pedidos: usa $stats si el controlador lo envía --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="p-4 rounded-xl border border-zinc-200 bg-zinc-50 dark:bg-zinc-900 dark:border-zinc-700">
                    <div class="text-xs uppercase tracking-wide opacity-70">Pendientes</div>
                    <div class="text-2xl font-semibold mt-1">{{ $stats['pendientes'] ?? '—' }}</div>
                </div>
                <div class="p-4 rounded-xl border border-zinc-200 bg-zinc-50 dark:bg-zinc-900 dark:border-zinc-700">
                    <div class="text-xs uppercase tracking-wide opacity-70">Pagados</div>
                    <div class="text-2xl font-semibold mt-1">{{ $stats['pagados'] ?? '—' }}</div>
                </div>
                <div class="p-4 rounded-xl border border-zinc-200 bg-zinc-50 dark:bg-zinc-900 dark:border-zinc-700">
                    <div class="text-xs uppercase tracking-wide opacity-70">En tránsito</div>
                    <div class="text-2xl font-semibold mt-1">{{ $stats['en_transito'] ?? '—' }}</div>
                </div>
                <div class="p-4 rounded-xl border border-zinc-200 bg-zinc-50 dark:bg-zinc-900 dark:border-zinc-700">
                    <div class="text-xs uppercase tracking-wide opacity-70">Entregados</div>
                    <div class="text-2xl font-semibold mt-1">{{ $stats['entregados'] ?? '—' }}</div>
                </div>
            </div>

            {{-- Accesos rápidos --}}
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                {{-- Productos --}}
                <div class="p-6 rounded-xl border border-blue-200 bg-blue-50 dark:bg-blue-900/40 dark:border-blue-800 shadow-sm">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="inline-flex w-10 h-10 rounded-full bg-blue-500/10 text-blue-600 dark:text-blue-300 grid place-items-center">
                            {{-- icono caja --}}
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M21 8.5V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8.5l9-5 9 5ZM12 4.8 6.2 8h11.6L12 4.8Z"/><path d="M12 22V9"/>
                            </svg>
                        </span>
                        <h2 class="text-xl font-bold text-blue-900 dark:text-blue-100">Administrar Productos</h2>
                    </div>
                    <p class="text-blue-900/80 dark:text-blue-200/80 mb-4">Gestiona todos los productos disponibles en la plataforma.</p>
                    <a href="{{ route('admin.productos.index') }}"
                       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-md transition">
                        Ir a Productos
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M13 5l7 7-7 7v-4H4v-6h9V5z"/></svg>
                    </a>
                </div>

                {{-- Categorías --}}
                <div class="p-6 rounded-xl border border-yellow-200 bg-yellow-50 dark:bg-yellow-900/40 dark:border-yellow-800 shadow-sm">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="inline-flex w-10 h-10 rounded-full bg-yellow-500/10 text-yellow-700 dark:text-yellow-300 grid place-items-center">
                            {{-- icono etiqueta --}}
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M10.59 2.59 3 10.17V21h10.83l7.58-7.59a2 2 0 0 0 0-2.83L13.41 2.59a2 2 0 0 0-2.82 0ZM7 14a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/>
                            </svg>
                        </span>
                        <h2 class="text-xl font-bold text-yellow-900 dark:text-yellow-100">Administrar Categorías</h2>
                    </div>
                    <p class="text-yellow-900/80 dark:text-yellow-200/80 mb-4">Gestiona las categorías de los productos.</p>
                    <a href="{{ route('admin.categorias.index') }}"
                       class="inline-flex items-center gap-2 bg-yellow-600 hover:bg-yellow-500 text-white px-4 py-2 rounded-md transition">
                        Ir a Categorías
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M13 5l7 7-7 7v-4H4v-6h9V5z"/></svg>
                    </a>
                </div>

                {{-- Pedidos & Envíos --}}
                <div class="p-6 rounded-xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/40 dark:border-emerald-800 shadow-sm">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="inline-flex w-10 h-10 rounded-full bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 grid place-items-center">
                            {{-- icono camión --}}
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M3 7a2 2 0 0 1 2-2h9v9H3V7Zm11 2h3.59L20 11.41V14h-6V9Zm-8 9a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm10 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/>
                            </svg>
                        </span>
                        <h2 class="text-xl font-bold text-emerald-900 dark:text-emerald-100">Pedidos & Envíos</h2>
                    </div>
                    <p class="text-emerald-900/80 dark:text-emerald-200/80 mb-4">
                        Revisa órdenes pagadas, asigna transportadora/guía y actualiza el estado del envío.
                    </p>
                    <a href="{{ route('ordenes.admin') }}"
                       class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-md transition">
                        Ir a Pedidos & Envíos
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M13 5l7 7-7 7v-4H4v-6h9V5z"/></svg>
                    </a>
                </div>
            </div>
        @endrole
    </div>
@endsection
