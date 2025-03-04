<!-- NAVIGATION BAR -->
<nav x-data="{ open: false, userMenu: false }" class="bg-white dark:bg-gray-900 shadow-lg border-b border-gray-300 dark:border-gray-700 fixed top-0 left-0 w-full z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20 items-center">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ route('home') }}">
                    <img x-show="!darkMode" src="{{ asset('images/Logo official 01 black.png') }}" alt="Logo Claro" class="h-16 w-auto">
                    <img x-show="darkMode" src="{{ asset('images/Logo official png white.png') }}" alt="Logo Oscuro" class="h-16 w-auto">
                </a>
            </div>

            <!-- Menú Normal (Oculto en móviles) -->
            <div class="hidden md:flex space-x-8 items-center">
                <x-nav-link :href="route('home')" :active="request()->routeIs('home')" class="text-gray-600 hover:text-gray-800">Home</x-nav-link>
                <x-nav-link :href="route('catalogo')" :active="request()->routeIs('catalogo')" class="text-gray-600 hover:text-gray-800">Catálogo</x-nav-link>

                @auth
                    @role('admin')
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" class="text-gray-600 hover:text-gray-800">Dashboard Admin</x-nav-link>
                    @endrole
                @endauth

                @auth
                    <!-- Dropdown de Usuario (solo visible en pantallas normales) -->
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button @click="userMenu = !userMenu" class="flex items-center text-sm font-medium focus:outline-none transition-all duration-300"
                                    :class="userMenu ? 'text-gray-800' : 'text-gray-600 hover:text-gray-800'">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ml-1">
                                    <svg class="fill-current h-5 w-5 transition-all transform hover:rotate-180" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a 1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')" class="text-gray-600 hover:text-gray-800">Perfil</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-gray-600 hover:text-gray-800">
                                    Cerrar sesión
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @endauth
            </div>

            <!-- Carrito (fuera del menú hamburguesa) -->
            @auth
                <a href="{{ route('carrito') }}" class="text-gray-600 hover:text-gray-800 flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 hover:text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h18l-1.2 7H4.2L3 3z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 20a2 2 0 100-4 2 2 0 000 4zM17 20a2 2 0 100-4 2 2 0 000 4z" />
                    </svg>
                    <span class="text-sm">Carrito</span>
                    <span id="cart-count" class="bg-red-500 text-white rounded-full px-2 py-1 text-xs font-bold">
                        {{ count(session('carrito') ?? []) }}
                    </span>
                </a>
            @endauth

            <!-- Botón Menú Hamburguesa en móviles -->
            <button @click="open = true" class="md:hidden p-2 text-gray-600 hover:text-gray-800 focus:outline-none">
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
            <button @click="open = false" class="p-2 text-gray-600 hover:text-gray-800 focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Enlaces del Menú -->
        <div class="flex flex-col space-y-4">
            <x-nav-link :href="route('home')" :active="request()->routeIs('home')" class="text-gray-600 hover:text-gray-800">Home</x-nav-link>
            <x-nav-link :href="route('catalogo')" :active="request()->routeIs('catalogo')" class="text-gray-600 hover:text-gray-800">Catálogo</x-nav-link>

            @auth
                @role('admin')
                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" class="text-gray-600 hover:text-gray-800">Dashboard Admin</x-nav-link>
                @endrole
            @endauth

            @auth
                <x-nav-link :href="route('profile.edit')" class="text-gray-600 hover:text-gray-800">Perfil</x-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-gray-600 hover:text-gray-800">
                        Cerrar sesión
                    </x-nav-link>
                </form>
            @endauth
        </div>
    </div>
</nav>

<main class="mt-32">
    <!-- Aquí va el contenido principal de la página -->
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Eliminar producto del carrito usando AJAX
        const deleteButtons = document.querySelectorAll('.delete-button');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();

                const productId = button.getAttribute('data-id');

                fetch(`/carrito/eliminar/${productId}`, { // Aquí faltaba cerrar la cadena correctamente
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Eliminar el producto de la vista
                        const productItem = document.getElementById(`cart-item-${productId}`); // Usando plantilla literal
                        if (productItem) {
                            productItem.remove();
                        }

                        // Mostrar notificación de eliminación
                        showToast();

                        // Actualizar el contador del carrito en el navbar
                        updateCartCount(data.cart_count);

                        // Actualizar el total del carrito
                        updateTotal(data.total);
                    } else {
                        alert('Error al eliminar el producto del carrito');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });

        // Función para mostrar la notificación
        function showToast() {
            const toast = document.getElementById('toast');
            if (toast) {
                toast.classList.remove('opacity-0');
                toast.classList.add('opacity-100');
                setTimeout(() => {
                    toast.classList.remove('opacity-100');
                    toast.classList.add('opacity-0');
                }, 3000);
            }
        }

        // Función para actualizar el contador del carrito en el navbar
        function updateCartCount(count) {
            const cartCount = document.querySelector('#cart-count');
            if (cartCount) {
                cartCount.innerText = count;
            }
        }

        // Función para actualizar el total del carrito
        function updateTotal(total) {
            const totalElement = document.querySelector('.cart-total');
            if (totalElement) {
                totalElement.innerText = '$' + total.toFixed(2);
            }
        }
    });
</script>

