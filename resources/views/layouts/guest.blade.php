<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Título con el nombre de la aplicación -->
    <title>{{ config('app.name', 'Asvack') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="
    $watch('darkMode', val => localStorage.setItem('darkMode', val));
">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-8 sm:pt-0 bg-gray-100 dark:bg-gray-900">
        <!-- Contenedor del logo y el nombre -->
        <div class="flex flex-col items-center mb-6 space-y-2">
            <!-- Logo que cambia según el tema -->
            <a href="/" class="flex items-center">
                <img src="{{ asset('images/logo_negro.png') }}" alt="Logo claro" class="w-24 h-24 dark:hidden">
                <img src="{{ asset('images/logo_blanco.png') }}" alt="Logo oscuro" class="w-24 h-24 hidden dark:block">
            </a>
            <!-- Nombre de la aplicación -->
            <span class="text-3xl font-bold text-gray-900 dark:text-gray-100 tracking-wide">
                {{ config('app.name', 'Asvack') }}
            </span>
        </div>

        <!-- Formulario de inicio de sesión -->
        <div class="w-full sm:max-w-md px-6 py-8 bg-white dark:bg-gray-800 shadow-lg rounded-lg">
            {{ $slot }}
        </div>
    </div>

    <!-- JavaScript para alternar el tema -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('theme', () => ({
                darkMode: localStorage.getItem('darkMode') === 'true',
                toggleTheme() {
                    this.darkMode = !this.darkMode;
                    localStorage.setItem('darkMode', this.darkMode);
                }
            }));
        });
    </script>
</body>
</html>
