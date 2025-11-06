<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">

    <title>{{ config('app.name', 'Asvack') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/alpinejs" defer></script>

    <!-- Pintado temprano sin FOUC + tema persistente -->
    <script>
      // función global para establecer tema y persistirlo
      window.__setTheme = function(isDark) {
        try {
          localStorage.setItem('darkMode', isDark ? 'true' : 'false');
        } catch(_) {}
        document.documentElement.classList.toggle('dark', !!isDark);
      };

      // 1) Antes de pintar: aplica clase dark según localStorage (y la quita si es claro)
      (function () {
        try {
          const isDark = localStorage.getItem('darkMode') === 'true';
          document.documentElement.classList.toggle('dark', isDark);
        } catch (_) {}
        // evita FOUC
        document.documentElement.style.visibility = 'hidden';
      })();

      // 2) Sincroniza entre pestañas
      window.addEventListener('storage', (e) => {
        if (e.key === 'darkMode') {
          const isDark = e.newValue === 'true';
          document.documentElement.classList.toggle('dark', isDark);
        }
      });
    </script>

    <style>
        [x-cloak] { display: none !important; }
        body { transition: opacity .3s ease-in-out; overflow-x: hidden; }
        body.loading { visibility: hidden; opacity: 0; overflow: hidden; }
        body.loaded  { visibility: visible; opacity: 1; overflow-y: auto; }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: #1F2937; }
        ::-webkit-scrollbar-thumb { background: #4F46E5; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #4338CA; }
        /* Menú móvil: evita coste de layout cuando está cerrado */
        .cv-auto { content-visibility: auto; contain-intrinsic-size: 0 400px; }
    </style>
</head>
<body class="font-sans antialiased transition-colors duration-300 bg-gray-100 dark:bg-gray-900 loading"
      x-data="$store.theme">

    <!-- Preloader -->
    <div id="preloader" class="fixed inset-0 bg-gray-100 dark:bg-gray-900 z-50 flex items-center justify-center">
      <div class="spinner-border text-primary" role="status"><span class="sr-only">Cargando...</span></div>
    </div>

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 transition-all duration-300 overflow-hidden">
        @include('layouts.navigation')

        <!-- Spacer para reservar el alto del navbar fijo y evitar CLS -->
        <div class="h-20"></div>

        @isset($header)
            <header class="bg-white dark:bg-gray-800 shadow dark:shadow-gray-700 transition-all duration-300">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 text-gray-900 dark:text-gray-100">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main class="bg-gray-100 dark:bg-gray-900 transition-all duration-300">
            @yield('content')
        </main>
    </div>

    <!-- Botón subir -->
    <button id="scrollToTopBtn" onclick="scrollToTop()" class="fixed bottom-10 right-10 w-11 h-11 bg-indigo-600 text-white rounded-2xl grid place-items-center shadow-lg opacity-0 pointer-events-none transition-opacity z-50">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
    </button>

    <script>
      // Alpine Store único para el tema (sin duplicar estados)
      document.addEventListener('alpine:init', () => {
        const initialDark = (localStorage.getItem('darkMode') === 'true');
        Alpine.store('theme', {
          darkMode: initialDark,
          toggle() {
            this.darkMode = !this.darkMode;
            window.__setTheme(this.darkMode);
          }
        });
      });

      // Mostrar tras cargar
      window.addEventListener('load', () => {
        const pre = document.getElementById('preloader');
        if (pre) {
          pre.style.transition = 'opacity .5s ease';
          pre.style.opacity = '0';
          setTimeout(() => {
            pre.style.display = 'none';
            document.documentElement.style.visibility = 'visible';
          }, 500);
        } else {
          document.documentElement.style.visibility = 'visible';
        }
        document.body.classList.remove('loading');
        document.body.classList.add('loaded');
      });

      // Back/Forward cache: re-sincroniza tema al volver de otra pestaña/ruta
      window.addEventListener('pageshow', (e) => {
        if (e.persisted) {
          const isDark = (localStorage.getItem('darkMode') === 'true');
          window.__setTheme(isDark);
          if (window.Alpine?.store('theme')) { window.Alpine.store('theme').darkMode = isDark; }
        }
      });

      // Scroll-to-top
      window.addEventListener('scroll', () => {
        const b = document.getElementById('scrollToTopBtn');
        if (!b) return;
        if (window.scrollY > 100) { b.classList.add('opacity-100'); b.classList.remove('pointer-events-none'); }
        else { b.classList.remove('opacity-100'); b.classList.add('pointer-events-none'); }
      });
      function scrollToTop(){ window.scrollTo({ top:0, behavior:'smooth' }); }
    </script>

    @yield('scripts')
</body>
</html>
