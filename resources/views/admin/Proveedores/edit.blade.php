@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-4xl font-bold mb-6 text-gray-900 dark:text-white">Editar Proveedor</h1>
    
    <form action="{{ route('admin.proveedores.update', $proveedor) }}" method="POST" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
        @csrf
        @method('PUT')

        <!-- Mensajes de error globales -->
        @if ($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-4">
            <label for="name" class="block text-gray-700 dark:text-gray-300 font-bold mb-2">Nombre</label>
            <input type="text" name="name" id="name" value="{{ $proveedor->name }}" class="w-full px-4 py-2 border rounded-lg text-gray-900 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="email" class="block text-gray-700 dark:text-gray-300 font-bold mb-2">Email</label>
            <input type="email" name="email" id="email" value="{{ $proveedor->email }}" class="w-full px-4 py-2 border rounded-lg text-gray-900 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4 relative">
            <label for="password" class="block text-gray-700 dark:text-gray-300 font-bold mb-2">Contraseña (dejar en blanco para no cambiar)</label>
            <div class="relative">
                <input type="password" name="password" id="password" class="w-full px-4 py-2 pr-12 border rounded-lg text-gray-900 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="button" onclick="togglePasswordVisibility('password', 'toggleIcon')" class="absolute inset-y-0 right-3 flex items-center text-gray-600 dark:text-gray-300">
                    <i id="toggleIcon" class="fas fa-eye"></i>
                </button>
                @error('password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="block text-gray-700 dark:text-gray-300 font-bold mb-2">Confirmar Contraseña</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="w-full px-4 py-2 border rounded-lg text-gray-900 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('password_confirmation')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end gap-4">
            <a href="{{ route('admin.proveedores.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white px-6 py-3 rounded-lg transition-all duration-300">Cancelar</a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-all duration-300">Actualizar Proveedor</button>
        </div>
    </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
<script>
    function togglePasswordVisibility(inputId, iconId) {
        const passwordInput = document.getElementById(inputId);
        const toggleIcon = document.getElementById(iconId);

        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = "password";
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }
</script>
@endsection
