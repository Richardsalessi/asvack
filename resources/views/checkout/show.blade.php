    @extends('layouts.app')

    @section('content')
    <div class="container mx-auto p-6">

        {{-- Mensajes de éxito / error --}}
        @if(session('success'))
            <div class="mb-4 rounded-md bg-green-100 text-green-800 px-4 py-2">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-md bg-red-100 text-red-800 px-4 py-2">
                {{ session('error') }}
            </div>
        @endif

        <h1 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Revisión de tu compra</h1>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
            @foreach($carrito as $id => $p)
                <div class="flex justify-between items-center py-3 border-b border-gray-200 dark:border-gray-700 last:border-b-0">
                    <div>
                        <div class="font-semibold text-gray-900 dark:text-white">{{ $p['nombre'] ?? 'Producto #'.$id }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-300">
                            Precio: ${{ number_format($p['precio'], 0, ',', '.') }} —
                            Cantidad: {{ $p['cantidad'] }}
                        </div>
                    </div>
                    <div class="font-semibold text-gray-900 dark:text-white">
                        ${{ number_format($p['precio'] * $p['cantidad'], 0, ',', '.') }}
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex flex-col items-end gap-1 mb-6">
            <div class="text-gray-700 dark:text-gray-200">Subtotal: <strong>${{ number_format($subtotal, 0, ',', '.') }}</strong></div>
            <div class="text-gray-700 dark:text-gray-200">Envío: <strong>${{ number_format($envio, 0, ',', '.') }}</strong></div>
            <div class="text-lg text-gray-900 dark:text-white">Total: <strong>${{ number_format($total, 0, ',', '.') }}</strong></div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('carrito') }}" class="px-4 py-2 rounded-md bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white">
                Volver al carrito
            </a>

            <!-- En el siguiente paso este form creará la orden y te mandará a ePayco -->
            <form method="POST" action="{{ route('checkout.create') }}">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">
                    Confirmar y continuar al pago
                </button>
            </form>
        </div>
    </div>
    @endsection
