<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script src="https://unpkg.com/alpinejs" defer></script>

    <!-- Aplicar el tema desde el principio para evitar FOUC -->
    <script>
        (function() {
            const darkMode = localStorage.getItem('darkMode') === 'true';
            if (darkMode) {
                document.documentElement.classList.add('dark');
            }
            // Evitar mostrar contenido sin estilizar
            document.documentElement.style.visibility = 'hidden';
        })();
    </script>

    <!-- Estilo para ocultar el contenido hasta que la página esté lista -->
    <style>
        body {
            transition: opacity 0.3s ease-in-out;
        }
        body.loading {
            visibility: hidden;
            opacity: 0;
            overflow: hidden;
        }
        body.loaded {
            visibility: visible;
            opacity: 1;
            overflow: auto;
        }
    </style>
</head>
<body class="loading">
    <x-guest-layout>
        <!-- Botón de Modo Oscuro/Claro -->
        <div x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" 
            x-init="
                if (darkMode) { 
                    document.documentElement.classList.add('dark'); 
                } else {
                    document.documentElement.classList.remove('dark'); 
                }
            "
            @click="
                darkMode = !darkMode; 
                localStorage.setItem('darkMode', darkMode);
                if (darkMode) { 
                    document.documentElement.classList.add('dark'); 
                } else {
                    document.documentElement.classList.remove('dark'); 
                }
            " class="flex justify-end mb-4">
            
            <div class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only" :checked="darkMode">
                <div class="w-16 h-8 bg-gray-300 dark:bg-gray-700 rounded-full shadow-inner transition-all duration-300"></div>
                <div class="absolute left-1 top-1 w-6 h-6 border border-gray-300 dark:border-yellow-500 rounded-full shadow-md transform transition-transform duration-300 flex items-center justify-center" :class="{ 'translate-x-8': darkMode }">
                    <!-- Icono de Sol -->
                    <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5" />
                        <line x1="12" y1="1" x2="12" y2="3" />
                        <line x1="12" y1="21" x2="12" y2="23" />
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
                        <line x1="1" y1="12" x2="3" y2="12" />
                        <line x1="21" y1="12" x2="23" y2="12" />
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
                    </svg>
                    <!-- Icono de Luna -->
                    <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 1021 12.79z" />
                    </svg>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name -->
            <div>
                <x-input-label for="name" :value="__('Name')" />
                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Email Address -->
            <div class="mt-4">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" />

                <x-text-input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="new-password" />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

                <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />

                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>

                <x-primary-button class="ms-4">
                    {{ __('Register') }}
                </x-primary-button>
            </div>
        </form>
    </x-guest-layout>

    <script>
        window.addEventListener('load', () => {
            document.body.classList.remove('loading');
            document.body.classList.add('loaded');
            document.documentElement.style.visibility = 'visible';
        });
    </script>
</body>
</html>
