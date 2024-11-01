<nav x-data="{ open: false }" class="bg-white dark:bg-gray-900 shadow-lg transition-all duration-300 border-b border-gray-300 dark:border-gray-700 fixed top-0 left-0 w-full z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}">
                        <!-- Logo para el modo claro -->
                        <img x-show="!darkMode" src="{{ asset('images/Logo official 01 black.png') }}" alt="Logo Claro" class="block h-16 w-auto">
                        <!-- Logo para el modo oscuro -->
                        <img x-show="darkMode" src="{{ asset('images/Logo official png white.png') }}" alt="Logo Oscuro" class="block h-16 w-auto">
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex items-center">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')" class="text-gray-800 dark:text-white hover:text-indigo-500 dark:hover:text-indigo-300">
                        {{ __('Home') }}
                    </x-nav-link>

                    @auth
                        <!-- Botón para ir al dashboard correspondiente según el rol -->
                        @role('admin')
                            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" class="text-gray-800 dark:text-white hover:text-indigo-500 dark:hover:text-indigo-300">
                                {{ __('Dashboard Admin') }}
                            </x-nav-link>
                        @elserole('provider')
                            <x-nav-link :href="route('provider.dashboard')" :active="request()->routeIs('provider.dashboard')" class="text-gray-800 dark:text-white hover:text-indigo-500 dark:hover:text-indigo-300">
                                {{ __('Dashboard Proveedor') }}
                            </x-nav-link>
                        @endrole
                    @endauth
                </div>
            </div>

            <!-- Botones de Iniciar Sesión y Registro o Menú de Usuario -->
            <div class="hidden sm:flex sm:items-center sm:ml-6 space-x-6">
                <!-- Botón de Modo Oscuro/Claro -->
                <div class="relative inline-flex items-center cursor-pointer" @click.stop="$store.theme.toggle()">
                    <input type="checkbox" class="sr-only">
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
                <span class="ml-3 text-sm font-medium text-gray-900 dark:text-white transition-all duration-300 select-none">
                    <span x-show="!darkMode">Modo Claro</span>
                    <span x-show="darkMode">Modo Oscuro</span>
                </span>

                @guest
                    <!-- Mostrar el botón de Iniciar Sesión solo en welcome_auth -->
                    @if(Request::is('welcome_auth'))
                        <a href="{{ route('login') }}" class="text-gray-900 dark:text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-300">
                            Iniciar Sesión
                        </a>
                    @endif
                @else
                    <!-- Dropdown -->
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="flex items-center text-sm font-medium text-gray-900 dark:text-white hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition-all duration-300">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ml-1">
                                    <svg class="fill-current h-5 w-5 transition-all transform hover:rotate-180" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a 1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')" class="text-gray-800 dark:text-white">
                                {{ __('Perfil') }}
                            </x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" class="text-gray-800 dark:text-white"
                                        onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                    {{ __('Cerrar sesión') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @endauth
            </div>
        </div>
    </div>
</nav>

<!-- Ajuste del margen superior en el contenido principal -->
<main class="mt-32">
    <!-- Aquí va el contenido principal de la página -->
</main>
