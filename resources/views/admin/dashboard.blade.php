@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-8 bg-white dark:bg-gray-800 shadow rounded-lg">
        <h1 class="text-3xl font-semibold text-gray-800 dark:text-white mb-6">Admin Dashboard</h1>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Bienvenido, administrador Richards.</p>
        @role('admin')
            <div class="bg-green-100 text-green-700 dark:bg-green-800 dark:text-green-200 p-4 rounded-md">
                <p class="font-medium">Tienes acceso total al sistema.</p>
            </div>
        @endrole
    </div>
@endsection
