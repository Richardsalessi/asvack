@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-8 bg-white dark:bg-gray-800 shadow rounded-lg">
        <h1 class="text-3xl font-semibold text-gray-800 dark:text-white mb-6">Cliente Dashboard</h1>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Bienvenido, cliente Cristian.</p>
        @role('client')
            <div class="bg-yellow-100 text-yellow-700 dark:bg-yellow-800 dark:text-yellow-200 p-4 rounded-md">
                <p class="font-medium">Puedes solicitar cotizaciones y ver las respuestas de los proveedores.</p>
            </div>
        @endrole
    </div>
@endsection
