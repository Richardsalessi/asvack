@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100 mb-6">Lista de Categorías</h1>
    <a href="{{ route('admin.categorias.create') }}" class="bg-green-500 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded transition duration-300 mb-4 inline-block">Añadir Categoría</a>
    
    <div class="overflow-x-auto mt-4">
        <table class="table-auto w-full">
            <thead>
                <tr class="bg-gray-200 dark:bg-gray-700 text-left">
                    <th class="px-4 py-2 text-gray-800 dark:text-gray-200">Nombre</th>
                    <th class="px-4 py-2 text-gray-800 dark:text-gray-200">Descripción</th>
                    <th class="px-4 py-2 text-gray-800 dark:text-gray-200">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800">
                @foreach ($categorias as $categoria)
                <tr class="border-b border-gray-300 dark:border-gray-700">
                    <td class="border px-4 py-2 text-gray-800 dark:text-gray-200">{{ $categoria->nombre }}</td>
                    <td class="border px-4 py-2 text-gray-800 dark:text-gray-200">{{ $categoria->descripcion }}</td>
                    <td class="border px-4 py-2 flex space-x-2">
                        <a href="{{ route('admin.categorias.edit', $categoria) }}" class="bg-blue-500 hover:bg-blue-700 text-white px-2 py-1 rounded transition duration-300">Editar</a>
                        <form action="{{ route('admin.categorias.destroy', $categoria) }}" method="POST" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white px-2 py-1 rounded transition duration-300" onclick="return confirm('¿Estás seguro de eliminar esta categoría?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Botón de Volver Arriba -->
<button id="scrollToTopBtn" class="opacity-0 pointer-events-none fixed bottom-80 right-25 flex items-center justify-center w-14 h-14 bg-blue-600 text-white rounded-full shadow-lg transition-opacity duration-300">
    ↑
</button>

<!-- Script para el botón -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const scrollToTopBtn = document.getElementById("scrollToTopBtn");

        window.addEventListener("scroll", function () {
            if (window.scrollY > 200) {
                scrollToTopBtn.classList.remove("opacity-0", "pointer-events-none");
                scrollToTopBtn.classList.add("opacity-100");
            } else {
                scrollToTopBtn.classList.add("opacity-0", "pointer-events-none");
            }
        });

        scrollToTopBtn.addEventListener("click", function () {
            window.scrollTo({ top: 0, behavior: "smooth" });
        });
    });
</script>

<!-- Estilos del botón -->
<style>
    #scrollToTopBtn {
        position: fixed;
        bottom: 80px; /* Ajustado para que no interfiera con otros elementos */
        right: 25px;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background-color: #4338ca;
        color: white;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        transition: opacity 0.3s ease-in-out, transform 0.2s;
        font-size: 24px;
        z-index: 1000;
        pointer-events: auto;
    }

    #scrollToTopBtn:hover {
        background-color: #3730a3;
        transform: scale(1.1);
    }

    #scrollToTopBtn:active {
        transform: scale(0.9);
    }
</style>
@endsection
