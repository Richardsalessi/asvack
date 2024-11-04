@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4 max-w-md bg-white dark:bg-gray-800 shadow rounded-lg">
    <h1 class="text-3xl font-semibold text-gray-800 dark:text-white mb-6">Editar Perfil</h1>

    @if (session('status') === 'profile-updated')
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            Perfil actualizado exitosamente.
        </div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="mb-4">
            <label for="name" class="block text-gray-700 dark:text-gray-200 mb-1">Nombre:</label>
            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" 
                    class="w-full p-2 border rounded-lg focus:ring focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
            @error('name')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="email" class="block text-gray-700 dark:text-gray-200 mb-1">Email:</label>
            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" 
                    class="w-full p-2 border rounded-lg focus:ring focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
            @error('email')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4 relative">
            <label for="password" class="block text-gray-700 dark:text-gray-200 mb-1">Nueva Contraseña (Opcional):</label>
            <div class="relative">
                <input type="password" name="password" id="password" 
                        class="w-full p-2 border rounded-lg focus:ring focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <button type="button" onclick="togglePasswordVisibility('password', 'togglePasswordIcon')" class="absolute inset-y-0 right-3 flex items-center text-gray-600 dark:text-gray-300">
                    <i id="togglePasswordIcon" class="fas fa-eye"></i>
                </button>
            </div>
            @error('password')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4 relative">
            <label for="password_confirmation" class="block text-gray-700 dark:text-gray-200 mb-1">Confirmar Nueva Contraseña:</label>
            <div class="relative">
                <input type="password" name="password_confirmation" id="password_confirmation" 
                        class="w-full p-2 border rounded-lg focus:ring focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <button type="button" onclick="togglePasswordVisibility('password_confirmation', 'toggleConfirmPasswordIcon')" class="absolute inset-y-0 right-3 flex items-center text-gray-600 dark:text-gray-300">
                    <i id="toggleConfirmPasswordIcon" class="fas fa-eye"></i>
                </button>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300">
                Guardar Cambios
            </button>
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
