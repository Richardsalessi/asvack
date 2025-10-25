<!-- NAVIGATION BAR -->
<nav x-data="{ open: false, userMenu: false }"
     class="bg-white dark:bg-gray-900 shadow-lg border-b border-gray-300 dark:border-gray-700 fixed top-0 left-0 w-full z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20 items-center">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ route('home') }}">
                    <img x-show="!darkMode" src="{{ asset('images/logo-negro.webp') }}" alt="Logo Claro" class="h-16 w-auto">
                    <img x-show="darkMode" src="{{ asset('images/logo-blanco.webp') }}" alt="Logo Oscuro" class="h-16 w-auto">
                </a>
            </div>

            <!-- Botón de Modo Oscuro/Claro -->
            <div class="flex items-center space-x-6">
                <div class="relative inline-flex items-center cursor-pointer" @click.stop="$store.theme?.toggle?.()">
                    <input type="checkbox" class="sr-only">
                    <div class="w-16 h-8 bg-gray-300 dark:bg-gray-700 rounded-full shadow-inner transition-all duration-300"></div>
                    <div class="absolute left-1 top-1 w-6 h-6 border border-gray-300 dark:border-yellow-500 rounded-full shadow-md transform transition-transform duration-300 flex items-center justify-center" :class="{ 'translate-x-8': darkMode }">
                        <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="5" />
                        </svg>
                        <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 1021 12.79z" />
                        </svg>
                    </div>
                </div>
                <span class="ml-3 text-sm font-medium text-gray-900 dark:text-white transition-all duration-300 select-none">
                    <span x-show="!darkMode">Modo Claro</span>
                    <span x-show="darkMode">Modo Oscuro</span>
                </span>
            </div>

            <!-- Menú Desktop -->
            <div class="hidden md:flex space-x-8 items-center">
                <a href="{{ route('home') }}" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">
                    Home
                </a>
                <a href="{{ route('catalogo') }}" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">
                    Catálogo
                </a>

                @auth
                    <a href="{{ route('ordenes.index') }}" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">
                        Mis compras
                    </a>
                    @can('admin-access')
                        <a href="{{ route('admin.dashboard') }}" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">
                            Dashboard Admin
                        </a>
                    @endcan
                @endauth

                @guest
                    <div class="flex space-x-4 items-center">
                        <a href="{{ route('login') }}" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">Iniciar sesión</a>
                        <a href="{{ route('register') }}" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">Registrarse</a>
                    </div>
                @endguest

                @auth
                    <!-- Menú usuario -->
                    <div class="relative">
                        <button @click="userMenu = !userMenu"
                                class="flex items-center text-sm font-medium focus:outline-none transition-all duration-300 text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">
                            <span>{{ Auth::user()->name }}</span>
                            <svg class="ml-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20" stroke="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a 1 1 0 01-1.414 0l-4-4a 1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div x-show="userMenu" @click.outside="userMenu = false"
                             class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg py-1 z-50">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-600 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Perfil</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-600 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    Cerrar sesión
                                </button>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>

            <!-- Carrito (derecha) -->
            @auth
                <a href="{{ route('carrito') }}" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h18l-1.2 7H4.2L3 3z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 20a2 2 0 100-4 2 2 0 000 4zM17 20a2 2 0 100-4 2 2 0 000 4z" />
                    </svg>
                    <span class="text-sm">Carrito</span>
                    <span id="cart-count" class="bg-red-500 text-white rounded-full px-2 py-1 text-xs font-bold">
                        {{ session('cart_count', session('carrito') ? collect(session('carrito'))->sum('cantidad') : 0) }}
                    </span>
                </a>
            @endauth

            <!-- Botón Menú Hamburguesa (móvil) -->
            <button @click="open = true" class="md:hidden p-2 text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Menú Móvil -->
    <div x-show="open" class="md:hidden absolute top-0 left-0 w-full bg-white dark:bg-gray-900 shadow-lg border-b border-gray-300 dark:border-gray-700 p-4">
        <!-- Botón de Cerrar -->
        <div class="flex justify-end">
            <button @click="open = false" class="p-2 text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Enlaces Menú Móvil -->
        <div class="flex flex-col space-y-4">
            <x-nav-link :href="route('home')" :active="request()->routeIs('home')" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">Home</x-nav-link>
            <x-nav-link :href="route('catalogo')" :active="request()->routeIs('catalogo')" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">Catálogo</x-nav-link>

            @auth
                <x-nav-link :href="route('ordenes.index')" :active="request()->routeIs('ordenes.index')" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">
                    Mis compras
                </x-nav-link>
                @can('admin-access')
                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">
                        Dashboard Admin
                    </x-nav-link>
                @endcan
            @endauth

            @guest
                <div class="flex flex-col space-y-2">
                    <x-nav-link :href="route('login')" :active="request()->routeIs('login')" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">
                        {{ __('Iniciar sesión') }}
                    </x-nav-link>
                    <x-nav-link :href="route('register')" :active="request()->routeIs('register')" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">
                        {{ __('Registrarse') }}
                    </x-nav-link>
                </div>
            @endguest

            @auth
                <x-nav-link :href="route('profile.edit')" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">Perfil</x-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">
                        Cerrar sesión
                    </x-nav-link>
                </form>
            @endauth
        </div>
    </div>
</nav>

<main class="mt-32">
    <!-- Contenido principal -->
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Eliminar producto del carrito (AJAX)
        const deleteButtons = document.querySelectorAll('.delete-button');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();

                const productId = button.getAttribute('data-id');

                fetch(`/carrito/eliminar/${productId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const productItem = document.getElementById(`cart-item-${productId}`);
                        if (productItem) productItem.remove();

                        showToast();
                        updateCartCount(data.cart_count ?? 0);
                        updateTotal(data.total ?? 0);
                    } else {
                        alert('Error al eliminar el producto del carrito');
                    }
                })
                .catch(console.error);
            });
        });

        function showToast() {
            const toast = document.getElementById('toast');
            if (!toast) return;
            toast.classList.remove('opacity-0');
            toast.classList.add('opacity-100');
            setTimeout(() => {
                toast.classList.remove('opacity-100');
                toast.classList.add('opacity-0');
            }, 3000);
        }

        function updateCartCount(count) {
            const cartCount = document.querySelector('#cart-count');
            if (cartCount) cartCount.innerText = count;
        }

        function updateTotal(total) {
            const totalElement = document.querySelector('.cart-total');
            if (totalElement) {
                try {
                    totalElement.innerText = '$' + Number(total).toFixed(2);
                } catch (_) {
                    totalElement.innerText = '$' + total;
                }
            }
        }
    });
</script>
