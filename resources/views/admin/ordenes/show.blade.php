@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 sm:px-6 py-8 text-zinc-900 dark:text-zinc-100">

    {{-- Botón de volver --}}
    <div class="mb-4">
        <a href="{{ route('ordenes.admin') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                <path d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
            Volver a Pedidos & Envíos
        </a>
    </div>

    {{-- Banners --}}
    @if(session('success'))
        <div class="mb-4 rounded-md bg-emerald-100 text-emerald-800 px-4 py-2">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-md bg-red-100 text-red-800 px-4 py-2">
            {{ session('error') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-md bg-red-50 text-red-800 px-4 py-3">
            <div class="font-medium mb-1">No pudimos guardar, revisa lo siguiente:</div>
            <ul class="list-disc pl-5 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Encabezado --}}
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-semibold">Orden #{{ $orden->id }}</h1>
        <div class="text-sm opacity-70">
            Creada: {{ $orden->created_at?->format('d/m/Y H:i') }} •
            Estado pago:
            <span class="font-medium">{{ ucfirst($orden->estado ?? '—') }}</span> •
            Total:
            <span class="font-medium">
                ${{ number_format((float)($orden->total ?? 0), 0, ',', '.') }}
            </span>
        </div>

        @php $env = $orden->envioRegistro; @endphp
        @if($env?->estado_envio)
            <div class="mt-3 inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs
                @class([
                    'bg-yellow-100 text-yellow-800' => $env->estado_envio === 'pendiente',
                    'bg-blue-100 text-blue-800'     => $env->estado_envio === 'en_transito',
                    'bg-emerald-100 text-emerald-800'=> $env->estado_envio === 'entregado',
                    'bg-red-100 text-red-800'       => $env->estado_envio === 'devuelto',
                ])
            ">
                Estado de envío: {{ str_replace('_',' ', ucfirst($env->estado_envio)) }}
                @if($env->transportadora || $env->numero_guia)
                    <span class="opacity-70">•</span>
                    <span>{{ $env->transportadora ?: '—' }} {{ $env->numero_guia ? '· #'.$env->numero_guia : '' }}</span>
                @endif
            </div>
        @endif
    </div>

    {{-- Productos --}}
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
                        $unit  = $d->precio_unitario ?? $d->precio ?? $d->valor_unitario ?? 0;
                        $cant  = (int)($d->cantidad ?? 1);
                        $linea = $d->subtotal ?? ($unit * $cant);
                    @endphp
                    <tr>
                        <td class="px-4 py-3">{{ $d->producto->nombre ?? ($d->nombre_producto ?? '—') }}</td>
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

    {{-- Dirección de envío --}}
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

    {{-- ============================= --}}
    {{-- BLOQUE DE ENVÍO (solo admin) --}}
    {{-- ============================= --}}
    @can('admin-access')
    <div class="mt-8 p-6 border rounded-xl bg-white dark:bg-zinc-900 dark:border-zinc-700">
        <h2 class="text-lg font-semibold mb-4">📦 Gestión de Envío</h2>

        {{-- Formulario: datos de envío --}}
        <form method="POST" action="{{ route('admin.envios.configurar', $orden) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            @csrf
            <div>
                <label class="text-sm opacity-80">Transportadora</label>
                <input type="text" name="transportadora"
                       value="{{ old('transportadora', $env->transportadora ?? '') }}"
                       class="w-full mt-1 rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 @error('transportadora') ring-2 ring-red-500 @enderror">
            </div>
            <div>
                <label class="text-sm opacity-80">Número de guía</label>
                <input type="text" name="numero_guia"
                       value="{{ old('numero_guia', $env->numero_guia ?? '') }}"
                       class="w-full mt-1 rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 @error('numero_guia') ring-2 ring-red-500 @enderror">
            </div>
            <div>
                <label class="text-sm opacity-80">Tipo de envío</label>
                @php
                    $tipos = [
                        'pagado_cliente'   => 'Pagado por el cliente',
                        'asumido_empresa'  => 'Asumido por la empresa',
                        'contraentrega'    => 'Contraentrega',
                    ];
                    $tipoSel = old('tipo_envio', $env->tipo_envio ?? 'pagado_cliente');
                @endphp
                <select name="tipo_envio"
                        class="w-full mt-1 rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 @error('tipo_envio') ring-2 ring-red-500 @enderror">
                    @foreach($tipos as $k => $label)
                        <option value="{{ $k }}" @selected($tipoSel === $k)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm opacity-80">Costo de envío (COP)</label>
                <input type="number" name="costo_envio" min="0" step="1"
                       value="{{ old('costo_envio', $env->costo_envio ?? 0) }}"
                       class="w-full mt-1 rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 @error('costo_envio') ring-2 ring-red-500 @enderror">
            </div>
            <div class="md:col-span-2">
                <label class="text-sm opacity-80">Notas internas</label>
                <textarea name="notas" rows="2"
                          class="w-full mt-1 rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 @error('notas') ring-2 ring-red-500 @enderror">{{ old('notas', $env->notas ?? '') }}</textarea>
            </div>

            <div class="md:col-span-2">
                <button type="submit"
                        class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-500 transition">
                    💾 Guardar datos de envío
                </button>
            </div>
        </form>

        {{-- Formulario: cambiar estado del envío --}}
        <form method="POST" action="{{ route('admin.envios.estado', $orden) }}" class="flex flex-wrap items-center gap-3">
            @csrf
            @php $estadoSel = old('estado_envio', $env->estado_envio ?? 'pendiente'); @endphp
            <label class="text-sm opacity-80">Estado actual:</label>
            <select name="estado_envio" class="rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800">
                @foreach(['pendiente' => 'Pendiente', 'en_transito' => 'En tránsito', 'entregado' => 'Entregado', 'devuelto' => 'Devuelto'] as $k => $v)
                    <option value="{{ $k }}" @selected($estadoSel === $k)>{{ $v }}</option>
                @endforeach
            </select>
            <button type="submit"
                    class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-500 transition">
                🚚 Actualizar estado
            </button>
        </form>
    </div>
    @endcan

</div>
@endsection
