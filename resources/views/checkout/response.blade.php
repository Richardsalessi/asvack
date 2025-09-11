@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-3xl py-8 text-zinc-900 dark:text-zinc-100">

    <h1 class="text-2xl font-semibold mb-4">Resultado del pago</h1>

    @php
        $estado = strtoupper($data['x_response'] ?? 'PENDIENTE');
        $clase  = match ($estado) {
            'ACEPTADA'                  => 'bg-green-600',
            'RECHAZADA', 'CANCELADA'    => 'bg-red-600',
            default                     => 'bg-yellow-600',
        };
    @endphp

    <div class="rounded-xl p-4 text-white {{ $clase }} mb-6">
        <p class="text-lg">
            Estado reportado por ePayco: <strong>{{ $data['x_response'] ?? 'Pendiente' }}</strong>
        </p>
        <p class="text-sm opacity-90">
            Transacción: {{ $data['x_transaction_id'] ?? 'N/D' }} ·
            Ref. ePayco: {{ $data['x_ref_payco'] ?? 'N/D' }}
        </p>
    </div>

    @if(!empty($orden))
        <div class="rounded-xl border border-zinc-300 dark:border-zinc-700
                    bg-white dark:bg-zinc-900/40 p-5 mb-6">
            <h2 class="text-xl font-medium mb-2">Resumen de tu orden #{{ $orden->id }}</h2>
            <p class="mb-2">Estado en nuestra tienda: <strong>{{ ucfirst($orden->estado) }}</strong></p>
            <p class="mb-4">Total: <strong>${{ number_format($orden->total, 0, ',', '.') }}</strong></p>

            <div class="space-y-2">
                @foreach($orden->detalles as $item)
                    <div class="flex justify-between border-b border-zinc-200 dark:border-zinc-700 py-2">
                        <span>{{ $item->producto->nombre ?? 'Producto' }} × {{ $item->cantidad }}</span>
                        <span>${{ number_format($item->precio * $item->cantidad, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 flex gap-3">
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
        <div class="rounded-xl border border-zinc-300 dark:border-zinc-700
                    bg-white dark:bg-zinc-900/40 p-5 mb-6">
            <p>No encontramos la orden asociada (x_extra1: {{ $data['x_extra1'] ?? 'N/D' }}).</p>
            <p class="text-sm opacity-80">Si ya realizaste el pago, revisa tu historial en <em>Mis compras</em>.</p>
        </div>
    @endif

    <p class="text-xs opacity-70">
        * El estado final lo confirma el <strong>webhook</strong>. Si ves alguna inconsistencia,
        actualiza la página en un minuto o revisa <a class="underline" href="{{ route('ordenes.index') }}">Mis compras</a>.
    </p>
</div>
@endsection
