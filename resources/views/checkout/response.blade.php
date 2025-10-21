@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-10 text-zinc-900 dark:text-zinc-100 max-w-4xl">

    <h1 class="text-2xl sm:text-3xl font-semibold mb-6">Resultado del pago</h1>

    {{-- Banner de estado (usa lo que determinó el controlador) --}}
    @php
        $estado = $estadoMostrable ?? 'pendiente'; // pagada | rechazada | pendiente
        $banner = match($estado) {
            'pagada'    => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/40', 'text' => 'text-emerald-800 dark:text-emerald-200', 'label' => 'Pago aprobado'],
            'rechazada' => ['bg' => 'bg-red-100 dark:bg-red-900/40',       'text' => 'text-red-800 dark:text-red-200',       'label' => 'Pago rechazado'],
            default     => ['bg' => 'bg-amber-100 dark:bg-amber-900/40',   'text' => 'text-amber-800 dark:text-amber-200',   'label' => 'Pago pendiente'],
        };
    @endphp
    <div class="rounded-lg px-4 py-3 mb-6 {{ $banner['bg'] }} {{ $banner['text'] }}">
        <div class="font-medium">{{ $banner['label'] }}</div>

        @if(!empty($gateway['message']))
            <div class="text-sm opacity-90 mt-0.5">{{ $gateway['message'] }}</div>
        @elseif($estado === 'pendiente')
            <div class="text-sm opacity-90 mt-0.5">Estamos sincronizando con el banco. Si ves inconsistencia, actualiza en unos segundos.</div>
        @endif

        <div class="text-xs opacity-75 mt-1">
            @if(!empty($gateway['ref'])) Ref. ePayco: {{ $gateway['ref'] }} @endif
            @if(!empty($gateway['amount'])) · Monto reportado: ${{ number_format($gateway['amount'] ?? 0, 0, ',', '.') }} {{ $gateway['currency'] ?? '' }} @endif
        </div>
    </div>

    {{-- Card resumen --}}
    <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
        <div class="px-5 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div class="font-semibold">Resumen de tu orden
                    @if($orden) <span class="opacity-70">#{{ $orden->id }}</span> @endif
                </div>
                @if(!empty($gateway['ref']))
                    <div class="text-xs opacity-70">Ref. ePayco: {{ $gateway['ref'] }}</div>
                @endif
            </div>
        </div>

        <div class="p-5 space-y-4">
            @if($orden && $orden->relationLoaded('detalles') ? $orden->detalles->count() : $orden?->detalles()->count())
                @foreach($orden->detalles as $det)
                    <div class="flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <div class="font-medium truncate">
                                {{ $det->producto->nombre ?? 'Producto' }}
                            </div>
                            <div class="text-sm opacity-70">
                                Cantidad: {{ $det->cantidad }} · Precio: ${{ number_format($det->precio_unitario, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="font-semibold shrink-0">
                            ${{ number_format($det->precio_unitario * $det->cantidad, 0, ',', '.') }}
                        </div>
                    </div>
                @endforeach
            @endif

            <div class="border-t border-dashed pt-4 mt-2 border-zinc-200 dark:border-zinc-700">
                <div class="flex justify-between text-sm mb-2">
                    <span class="opacity-80">Subtotal</span>
                    <span>${{ number_format($orden->subtotal ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm mb-2">
                    <span class="opacity-80">Envío</span>
                    <span>
                        @if(!is_null($orden?->envio))
                            ${{ number_format($orden->envio, 0, ',', '.') }}
                        @else
                            —
                        @endif
                    </span>
                </div>
                <div class="flex justify-between text-base font-semibold">
                    <span>Total</span>
                    <span>${{ number_format($orden->total ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="px-5 py-4 border-t border-zinc-200 dark:border-zinc-700 flex flex-wrap gap-3">
            @if($orden && auth()->check() && $orden->user_id === auth()->id())
                <a href="{{ route('ordenes.show', $orden) }}"
                   class="inline-flex items-center px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-500 text-white transition">
                    Ver detalle de mi compra
                </a>
            @endif

            <a href="{{ route('catalogo') }}"
               class="inline-flex items-center px-4 py-2 rounded-md bg-zinc-200 hover:bg-zinc-300 text-zinc-900 dark:bg-zinc-700 dark:hover:bg-zinc-600 dark:text-zinc-100 transition">
                Seguir comprando
            </a>
        </div>
    </div>

    @if(($estado ?? 'pendiente') === 'pendiente')
        <p class="text-xs opacity-70 mt-3 text-center">Sincronizando con el banco…</p>
        <script>
            // Auto-refresh suave si sigue pendiente (cada 8s por ~1 min)
            (function(){
                let tries = 0;
                const maxTries = 7;
                const timer = setInterval(function(){
                    tries++;
                    if (tries >= maxTries) { clearInterval(timer); return; }
                    location.reload();
                }, 8000);
            })();
        </script>
    @endif
</div>
@endsection
