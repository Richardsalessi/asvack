<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="
    if (darkMode) { 
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
    $watch('darkMode', val => localStorage.setItem('darkMode', val));
    window.scrollTo(0, 0);  // Desplazar siempre hacia la parte superior al recargar la página
" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

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
            overflow-x: hidden; /* Quitar el scroll horizontal */
        }
        body.loading {
            visibility: hidden;
            opacity: 0;
            overflow: hidden;
        }
        body.loaded {
            visibility: visible;
            opacity: 1;
            overflow-y: auto;
        }

        #scrollToTopBtn {
            position: fixed;
            bottom: 40px;
            right: 40px;
            width: 45px;
            height: 45px;
            background-color: #4F46E5;
            color: white;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.06);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease-in-out;
            z-index: 50;
        }

        #scrollToTopBtn.show {
            opacity: 1;
            pointer-events: auto;
        }

        #scrollToTopBtn:hover {
            background-color: #4338CA;
        }

        /* Custom scrollbar styling */
        ::-webkit-scrollbar {
            width: 5px; /* Hacer el scroll más estrecho */
        }

        ::-webkit-scrollbar-track {
            background: #1F2937; /* Color de fondo del track */
        }

        ::-webkit-scrollbar-thumb {
            background: #4F46E5; /* Color del scroll */
            border-radius: 4px; /* Redondeo para darle un aspecto más suave */
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #4338CA; /* Color más oscuro cuando se interactúa */
        }
    </style>
</head>
<body x-data="$store.theme" x-bind:class="{ 'dark': darkMode }" class="font-sans antialiased transition-colors duration-300 bg-gray-100 dark:bg-gray-900 loading">
    <!-- Preloader opcional -->
    <div id="preloader" class="fixed inset-0 bg-gray-100 dark:bg-gray-900 z-50 flex items-center justify-center">
        <div class="loader">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
        </div>
    </div>

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 transition-all duration-300 overflow-hidden">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white dark:bg-gray-800 shadow dark:shadow-gray-700 transition-all duration-300">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 text-gray-900 dark:text-gray-100">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main class="bg-gray-100 dark:bg-gray-900 transition-all duration-300">
            @yield('content')
        </main>
    </div>

    <!-- Botón de subir -->
    <button id="scrollToTopBtn" onclick="scrollToTop()">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
        </svg>
    </button>

    <!-- Definir el comportamiento de Alpine.js después de que la página se cargue -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                darkMode: localStorage.getItem('darkMode') === 'true',

                toggle() {
                    this.darkMode = !this.darkMode;
                    localStorage.setItem('darkMode', this.darkMode);
                    document.documentElement.classList.toggle('dark', this.darkMode);
                }
            });
        });

        // Remover la clase de carga después de que la página se haya cargado completamente
        window.addEventListener('load', () => {
            // Remover el preloader y permitir que el contenido se vea
            const preloader = document.getElementById('preloader');
            if (preloader) {
                preloader.style.transition = 'opacity 0.5s ease';
                preloader.style.opacity = '0';
                setTimeout(() => {
                    preloader.style.display = 'none';
                    document.documentElement.style.visibility = 'visible';
                    window.scrollTo(0, 0); // Mover la vista a la parte superior al cargar
                }, 500);
            }
            document.body.classList.remove('loading');
            document.body.classList.add('loaded');
        });

        // Mostrar u ocultar el botón de "ir arriba"
        window.onscroll = function() {
            const scrollToTopBtn = document.getElementById('scrollToTopBtn');
            if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
                scrollToTopBtn.classList.add('show');
            } else {
                scrollToTopBtn.classList.remove('show');
            }
        };

        // Función para desplazar hacia arriba suavemente
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    </script>
</body>
</html>
