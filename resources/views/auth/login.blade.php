<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
      x-init="$watch('darkMode', v => localStorage.setItem('darkMode', v))"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Asvack') }}</title>

    {{-- Evitar caché agresivo en Safari para páginas guest --}}
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    {{-- Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js --}}
    <script src="https://unpkg.com/alpinejs" defer></script>

    {{-- Forzar dark mode antes del render --}}
    <script>
        (function () {
            try {
                if (localStorage.getItem('darkMode') === 'true') {
                    document.documentElement.classList.add('dark');
                }
            } catch (_) {}
        })();
    </script>

    {{-- Pequeña transición sin bloquear render --}}
    <style>
        body { opacity: 1; transition: opacity .2s ease-in-out; }
        body.loading { opacity: 0; overflow: hidden; }
        body.loaded { opacity: 1; overflow: auto; }
    </style>
</head>
<body class="loading">
    <x-guest-layout>
        {{-- Toggle modo oscuro/claro --}}
        <div x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
             x-init="document.documentElement.classList.toggle('dark', darkMode)"
             @click="
                darkMode = !darkMode;
                localStorage.setItem('darkMode', darkMode);
                document.documentElement.classList.toggle('dark', darkMode);
             "
             class="flex justify-end mb-4">
            <div class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only" :checked="darkMode">
                <div class="w-16 h-8 bg-gray-300 dark:bg-gray-700 rounded-full shadow-inner transition-all duration-300"></div>
                <div class="absolute left-1 top-1 w-6 h-6 border border-gray-300 dark:border-yellow-500 rounded-full shadow-md transform transition-transform duration-300 grid place-items-center"
                     :class="{ 'translate-x-8': darkMode }">
                    {{-- Sol --}}
                    <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5" />
                    </svg>
                    {{-- Luna --}}
                    <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 1021 12.79z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Estado de sesión --}}
        <x-auth-session-status class="mb-4" :status="session('status')" />

        {{-- Anti doble click + 419 protection --}}
        <form method="POST"
              action="{{ route('login') }}"
              x-data="{ submitting: false }"
              @submit.prevent="
                  if (submitting) return false;
                  submitting = true;
                  const btn = $el.querySelector('button[type=submit]');
                  btn.setAttribute('disabled', true);
                  btn.classList.add('opacity-60', 'cursor-not-allowed');
                  $el.submit();
              ">
            @csrf

            {{-- Email --}}
            <div>
                <x-input-label for="email" :value="'Correo electrónico'" />
                <x-text-input id="email"
                              class="block mt-1 w-full"
                              type="email"
                              name="email"
                              :value="old('email')"
                              required
                              autofocus
                              autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            {{-- Password --}}
            <div class="mt-4">
                <x-input-label for="password" :value="'Contraseña'" />
                <x-text-input id="password"
                              class="block mt-1 w-full"
                              type="password"
                              name="password"
                              required
                              autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            {{-- Remember --}}
            <div class="block mt-4">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox"
                           class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
                           name="remember">
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Recuérdame</span>
                </label>
            </div>

            {{-- Botón --}}
            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                       href="{{ route('password.request') }}">
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif

                <x-primary-button type="submit" class="ms-3">
                    <span x-show="!submitting">Iniciar sesión</span>
                    <span x-show="submitting">Entrando…</span>
                </x-primary-button>
            </div>
        </form>
    </x-guest-layout>

    {{-- Mostrar UI apenas cargue el DOM --}}
    <script>
        (function () {
            function ready() {
                document.body.classList.remove('loading');
                document.body.classList.add('loaded');
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', ready, { once: true });
            } else {
                requestAnimationFrame(ready);
            }
            setTimeout(ready, 600);
        })();
    </script>
</body>
</html>
