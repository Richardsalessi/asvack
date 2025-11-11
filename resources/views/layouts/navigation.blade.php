<!-- NAVIGATION BAR -->
<nav x-data="{ open:false, userMenu:false }"
     class="bg-white dark:bg-gray-900 shadow-lg border-b border-gray-300 dark:border-gray-700 fixed top-0 left-0 w-full z-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between h-20 items-center">
      <!-- Logo con dimensiones + prioridad (evita CLS y mejora LCP) -->
      <a href="{{ route('home') }}" class="block shrink-0" aria-label="Ir al inicio">
        <img x-show="!$store.theme.darkMode" src="{{ asset('images/logo-negro.webp') }}" alt="Asvack"
             class="h-16 w-auto" width="160" height="64" fetchpriority="high" decoding="async">
        <img x-show="$store.theme.darkMode" src="{{ asset('images/logo-blanco.webp') }}" alt="Asvack"
             class="h-16 w-auto" width="160" height="64" fetchpriority="high" decoding="async">
      </a>

      <!-- Toggle Modo Oscuro/Claro (usa el store global y persiste) -->
      <div class="flex items-center gap-3">
        <button type="button"
                @click="$store.theme.toggle()"
                class="relative inline-flex items-center w-16 h-8 rounded-full bg-gray-300 dark:bg-gray-700 shadow-inner focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors"
                :aria-pressed="$store.theme.darkMode.toString()" aria-label="Cambiar tema">
          <span class="absolute left-1 top-1 w-6 h-6 rounded-full border border-gray-300 dark:border-yellow-500 shadow-md grid place-items-center transform transition-transform"
                :class="{'translate-x-8': $store.theme.darkMode}">
            <svg x-show="!$store.theme.darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/></svg>
            <svg x-show="$store.theme.darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 1021 12.79z"/></svg>
          </span>
        </button>
        <span class="text-sm font-medium text-gray-900 dark:text-white select-none">
          <span x-show="!$store.theme.darkMode">Modo Claro</span>
          <span x-show="$store.theme.darkMode">Modo Oscuro</span>
        </span>
      </div>

      <!-- Menú Desktop -->
      <div class="hidden md:flex items-center gap-8">
        <a href="{{ route('home') }}" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">Home</a>
        <a href="{{ route('catalogo') }}" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">Catálogo</a>

        @auth
          <a href="{{ route('ordenes.index') }}" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">Mis compras</a>
          @can('admin-access')
            <a href="{{ route('admin.dashboard') }}" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">Dashboard Admin</a>
          @endcan
        @endauth

        @guest
          <div class="flex items-center gap-4">
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

      <!-- Carrito (SIEMPRE visible en navbar en móvil y desktop cuando está autenticado) -->
      @auth
        <a href="{{ route('carrito') }}"
           class="flex items-center gap-1 text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white mr-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h18l-1.2 7H4.2L3 3z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 20a2 2 0 100-4 2 2 0 000 4zM17 20a2 2 0 100-4 2 2 0 000 4z" />
          </svg>
          <!-- Texto solo en desktop para no llenar espacio en móvil -->
          <span class="hidden md:inline text-sm">Carrito</span>
          <span id="cart-count" class="bg-red-500 text-white rounded-full px-2 py-1 text-xs font-bold">
            {{ session('cart_count', session('carrito') ? collect(session('carrito'))->sum('cantidad') : 0) }}
          </span>
        </a>
      @endauth

      <!-- Botón Menú Hamburguesa (móvil) -->
      <button @click="open = true" @keydown.escape="open = false"
              class="md:hidden p-2 text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white focus:outline-none"
              aria-label="Abrir menú" :aria-expanded="open.toString()" aria-controls="mobile-menu">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16m-7 6h7"/>
        </svg>
      </button>
    </div>
  </div>

  <!-- Menú Móvil -->
  <div id="mobile-menu" x-cloak x-show="open"
       x-transition.opacity.duration.150ms
       class="md:hidden absolute top-0 left-0 w-full bg-white dark:bg-gray-900 shadow-lg border-b border-gray-300 dark:border-gray-700 p-4 cv-auto"
       :inert="open ? null : true">
    <div class="flex justify-end">
      <button @click="open = false" class="p-2 text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white focus:outline-none" aria-label="Cerrar menú">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <!-- Enlaces Menú Móvil -->
    <div class="flex flex-col gap-4">
      <x-nav-link :href="route('home')" :active="request()->routeIs('home')" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">Home</x-nav-link>
      <x-nav-link :href="route('catalogo')" :active="request()->routeIs('catalogo')" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">Catálogo</x-nav-link>

      @auth
        <!-- Carrito también accesible desde el menú móvil -->
        <x-nav-link :href="route('carrito')" :active="request()->routeIs('carrito')" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">
          Carrito
        </x-nav-link>

        <x-nav-link :href="route('ordenes.index')" :active="request()->routeIs('ordenes.index')" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">Mis compras</x-nav-link>
        @can('admin-access')
          <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:bg-gray-700">Dashboard Admin</x-nav-link>
        @endcan
      @endauth

      @guest
        <div class="flex flex-col gap-2">
          <x-nav-link :href="route('login')" :active="request()->routeIs('login')" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">{{ __('Iniciar sesión') }}</x-nav-link>
          <x-nav-link :href="route('register')" :active="request()->routeIs('register')" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">{{ __('Registrarse') }}</x-nav-link>
        </div>
      @endguest

      @auth
        <x-nav-link :href="route('profile.edit')" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">Perfil</x-nav-link>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <x-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">Cerrar sesión</x-nav-link>
        </form>
      @endauth
    </div>
  </div>
</nav>

<!-- JS del carrito: sólo si hay botones (no bloquea carga de otras páginas) -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const anyDelete = document.querySelector('.delete-button');
    if (!anyDelete) return;

    document.querySelectorAll('.delete-button').forEach(button => {
      button.addEventListener('click', function (event) {
        event.preventDefault();
        const productId = button.getAttribute('data-id');
        fetch(`/carrito/eliminar/${productId}`, {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
        })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            const item = document.getElementById(`cart-item-${productId}`);
            if (item) item.remove();
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
      const t = document.getElementById('toast');
      if (!t) return;
      t.classList.remove('opacity-0'); t.classList.add('opacity-100');
      setTimeout(() => { t.classList.remove('opacity-100'); t.classList.add('opacity-0'); }, 3000);
    }
    function updateCartCount(c) { const n = document.getElementById('cart-count'); if (n) n.innerText = c; }
    function updateTotal(total) {
      const el = document.querySelector('.cart-total');
      if (!el) return;
      try { el.innerText = '$' + Number(total).toFixed(2); } catch { el.innerText = '$' + total; }
    }
  });
</script>
