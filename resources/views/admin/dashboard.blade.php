@extends('layouts.app')

@section('content')
@php
    $stats = $stats ?? [];
    $tarifasActivas = $stats['tarifas_activas'] ?? \App\Models\TarifaEnvio::where('activo', 1)->count();
@endphp

{{-- Refuerzo anti-azul global (último recurso) --}}
<style>
  .force-red-card{
    border-color:#ef4444!important;      /* red-500 */
    outline:none!important;
    box-shadow:none!important;            /* por si algún ring usa box-shadow */
  }
</style>

<div class="container mx-auto p-8 bg-white dark:bg-gray-800 shadow rounded-lg text-zinc-900 dark:text-zinc-100">
    <h1 class="text-3xl font-semibold text-zinc-900 dark:text-white mb-2">Dashboard Administrador</h1>
    <p class="text-zinc-600 dark:text-zinc-300 mb-6">Bienvenido, {{ Auth::user()->name }}.</p>

    @role('admin')
        {{-- Aviso de rol --}}
        <div class="rounded-md mb-6 px-4 py-3 bg-emerald-50 text-emerald-900 border border-emerald-200
                    dark:bg-emerald-900/40 dark:text-emerald-100 dark:border-emerald-800">
            <p class="font-medium">Tienes acceso total al sistema.</p>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
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
            <div class="p-4 rounded-xl border border-zinc-200 bg-zinc-50 dark:bg-zinc-900 dark:border-zinc-700">
                <div class="text-xs uppercase tracking-wide opacity-70">Tarifas activas</div>
                <div class="text-2xl font-semibold mt-1">{{ $tarifasActivas }}</div>
            </div>
        </div>

        {{-- Accesos rápidos --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 items-stretch">

            {{-- Productos --}}
            <div class="p-6 rounded-xl border border-blue-200 bg-blue-50 dark:bg-blue-900/25 dark:border-blue-900/60 shadow-sm h-full flex flex-col">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-grid place-items-center w-10 h-10 rounded-full bg-blue-500/10 text-blue-700 dark:text-blue-300">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M21 8.5V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8.5l9-5 9 5ZM12 4.8 6.2 8h11.6L12 4.8Z"/><path d="M12 22V9"/>
                        </svg>
                    </span>
                    <h2 class="text-xl font-bold text-blue-900 dark:text-blue-100">Administrar Productos</h2>
                </div>
                <p class="text-blue-900/80 dark:text-blue-200/80 mb-4">Gestiona todos los productos disponibles.</p>
                <button onclick="window.location='{{ route('admin.productos.index') }}'"
                    class="mt-auto w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-500 text-white font-medium px-4 py-2 rounded-md shadow transition focus:ring-2 focus:ring-blue-300 dark:focus:ring-blue-500">
                    Ir a Productos
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M13 5l7 7-7 7v-4H4v-6h9V5z"/>
                    </svg>
                </button>
            </div>

            {{-- Categorías --}}
            <div class="p-6 rounded-xl border border-yellow-200 bg-yellow-50 dark:bg-yellow-900/25 dark:border-yellow-900/60 shadow-sm h-full flex flex-col">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-grid place-items-center w-10 h-10 rounded-full bg-yellow-500/10 text-yellow-700 dark:text-yellow-300">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M10.59 2.59 3 10.17V21h10.83l7.58-7.59a2 2 0 0 0 0-2.83L13.41 2.59a2 2 0 0 0-2.82 0ZM7 14a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/>
                        </svg>
                    </span>
                    <h2 class="text-xl font-bold text-yellow-900 dark:text-yellow-100">Administrar Categorías</h2>
                </div>
                <p class="text-yellow-900/80 dark:text-yellow-200/80 mb-4">Gestiona las categorías de los productos.</p>
                <button onclick="window.location='{{ route('admin.categorias.index') }}'"
                    class="mt-auto w-full flex items-center justify-center gap-2 bg-yellow-600 hover:bg-yellow-500 text-white font-medium px-4 py-2 rounded-md shadow transition focus:ring-2 focus:ring-yellow-300 dark:focus:ring-yellow-500">
                    Ir a Categorías
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M13 5l7 7-7 7v-4H4v-6h9V5z"/>
                    </svg>
                </button>
            </div>

            {{-- Pedidos & Envíos --}}
            <div class="p-6 rounded-xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/25 dark:border-emerald-900/60 shadow-sm h-full flex flex-col">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-grid place-items-center w-10 h-10 rounded-full bg-emerald-500/10 text-emerald-700 dark:text-emerald-300">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3 7a2 2 0 0 1 2-2h9v9H3V7Zm11 2h3.59L20 11.41V14h-6V9Zm-8 9a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm10 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/>
                        </svg>
                    </span>
                    <h2 class="text-xl font-bold text-emerald-900 dark:text-emerald-100">Pedidos & Envíos</h2>
                </div>
                <p class="text-emerald-900/80 dark:text-emerald-200/80 mb-4">
                    Revisa órdenes pagadas, asigna transportadora/guía y actualiza el estado del envío.
                </p>
                <button onclick="window.location='{{ route('ordenes.admin') }}'"
                    class="mt-auto w-full flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white font-medium px-4 py-2 rounded-md shadow transition focus:ring-2 focus:ring-emerald-300 dark:focus:ring-emerald-500">
                    Ir a Pedidos & Envíos
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M13 5l7 7-7 7v-4H4v-6h9V5z"/>
                    </svg>
                </button>
            </div>

            {{-- TARIFAS DE ENVÍO — borde igual que los demás --}}
            <div class="p-6 rounded-xl border border-red-200 bg-red-50 
                        dark:bg-red-900/25 dark:border-red-900/60 
                        shadow-sm h-full flex flex-col">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-grid place-items-center w-10 h-10 rounded-full 
                                bg-red-500/10 text-red-700 dark:text-red-200">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                            <circle cx="12" cy="12" r="10"/>
                        </svg>
                    </span>
                    <h2 class="text-xl font-bold text-red-900 dark:text-red-100">
                        Tarifas de Envío
                    </h2>
                </div>

                <p class="text-red-900/80 dark:text-red-200/80 mb-4">
                    Define y ajusta el costo por ciudad/barrio que verá el cliente al pagar.
                </p>

                <button onclick="window.location='{{ route('admin.tarifas.index') }}'"
                    class="mt-auto w-full flex items-center justify-center gap-2 
                            bg-red-600 hover:bg-red-500 text-white font-medium 
                            px-4 py-2 rounded-md shadow transition 
                            focus:ring-2 focus:ring-red-300 dark:focus:ring-red-500">
                    Ir a Tarifas de Envío
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M13 5l7 7-7 7v-4H4v-6h9V5z"/>
                    </svg>
                </button>
            </div>


        </div>
    @endrole
</div>
@endsection
