@extends('layouts.app')

@section('content')
<div class="container mx-auto p-8">
    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Comprar {{ $producto->nombre }}</h2>

    <form action="{{ route('compra.procesar', $producto->id) }}" method="POST">
        @csrf

        <!-- Nombre y email del cliente, prellenados -->
        <div class="mb-4">
            <label for="nombre_cliente" class="block text-gray-700 dark:text-gray-200">Nombre:</label>
            <input type="text" name="nombre_cliente" id="nombre_cliente" 
                   value="{{ auth()->user()->name }}" 
                   class="w-full p-2 border rounded-lg dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                   required readonly>
        </div>
        <div class="mb-4">
            <label for="email_cliente" class="block text-gray-700 dark:text-gray-200">Email:</label>
            <input type="email" name="email_cliente" id="email_cliente" 
                   value="{{ auth()->user()->email }}" 
                   class="w-full p-2 border rounded-lg dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                   required readonly>
        </div>

        <!-- Teléfono -->
        <div class="mb-4">
            <label for="telefono" class="block text-gray-700 dark:text-gray-200">Teléfono:</label>
            <div class="flex">
                <span class="inline-flex items-center px-3 border border-r-0 rounded-l-lg bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                    +57
                </span>
                <input type="tel" name="telefono" id="telefono" 
                      class="w-full p-2 border rounded-r-lg dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                      placeholder="Ingrese su número sin el código de país" 
                      required>
            </div>
        </div>

        <!-- Ciudad (Lista desplegable) -->
        <div class="mb-4">
            <label for="ciudad" class="block text-gray-700 dark:text-gray-200">Ciudad:</label>
            <select name="ciudad" id="ciudad" class="w-full p-2 border rounded-lg dark:bg-gray-800 dark:border-gray-600 dark:text-white" required>
                <option value="" disabled selected>Seleccione su ciudad</option>
                <option value="Bogotá">Bogotá</option>
                <option value="Medellín">Medellín</option>
                <option value="Cali">Cali</option>
                <option value="Barranquilla">Barranquilla</option>
                <option value="Cartagena">Cartagena</option>
                <option value="Cúcuta">Cúcuta</option>
                <option value="Bucaramanga">Bucaramanga</option>
                <option value="Pereira">Pereira</option>
                <option value="Manizales">Manizales</option>
                <option value="Santa Marta">Santa Marta</option>
                <!-- Agrega aquí todas las ciudades que necesites -->
            </select>
        </div>

        <!-- Barrio -->
        <div class="mb-4">
            <label for="barrio" class="block text-gray-700 dark:text-gray-200">Barrio:</label>
            <input type="text" name="barrio" id="barrio" class="w-full p-2 border rounded-lg dark:bg-gray-800 dark:border-gray-600 dark:text-white" required>
        </div>

        <!-- Dirección -->
        <div class="mb-4">
            <label for="direccion" class="block text-gray-700 dark:text-gray-200">Dirección:</label>
            <input type="text" name="direccion" id="direccion" class="w-full p-2 border rounded-lg dark:bg-gray-800 dark:border-gray-600 dark:text-white" required>
        </div>

        <!-- Cantidad -->
        <div class="mb-4">
            <label for="cantidad" class="block text-gray-700 dark:text-gray-200">Cantidad:</label>
            <input type="number" name="cantidad" id="cantidad" class="w-full p-2 border rounded-lg dark:bg-gray-800 dark:border-gray-600 dark:text-white" min="1" max="{{ $producto->stock }}" required>
        </div>

        <!-- Botón de confirmación -->
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-800 transition-all duration-300">Confirmar Compra</button>
    </form>
</div>
@endsection
