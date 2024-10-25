@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-8 bg-white dark:bg-gray-800 shadow rounded-lg">
        <h1 class="text-3xl font-semibold text-gray-800 dark:text-white mb-6">Proveedor Dashboard</h1>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Bienvenido, proveedor Valentina.</p>
        @role('provider')
            <div class="bg-blue-100 text-blue-700 dark:bg-blue-800 dark:text-blue-200 p-4 rounded-md">
                <p class="font-medium">Puedes gestionar tus productos y responder solicitudes de cotizaciones.</p>
            </div>
        @endrole
    </div>
@endsection
