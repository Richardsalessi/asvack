@extends('layouts.app')

@section('content')
<div class="py-8">
    <!-- Título principal -->
    <div class="text-center mb-8">
        <h1 class="text-5xl font-extrabold text-gray-900 dark:text-white mb-4">Bienvenido a Asvack</h1>
        <p class="text-2xl text-gray-600 dark:text-gray-300">Componentes de calidad para todos tus equipos.</p>
    </div>

    <!-- Video como banner extendido al ancho total -->
    <div class="relative w-screen left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] mb-8">
        <video autoplay loop muted class="w-full object-cover h-[500px] rounded-md shadow-lg">
            <source src="{{ asset('storage/Video.mp4') }}" type="video/mp4">
            Tu navegador no soporta la reproducción de videos.
        </video>
    </div>

    <!-- Catálogo de productos -->
    <div class="container mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach ($productos as $producto)
            <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-105">
                <div class="h-64 w-full mb-4 overflow-hidden relative">
                    @if($producto->imagenes->isNotEmpty())
                        <div class="slider relative h-full w-full">
                            @foreach($producto->imagenes as $imagen)
                                <img src="{{ asset('storage/' . $imagen->ruta) }}" alt="Imagen de {{ $producto->nombre }}" class="slider-image object-contain w-full h-full absolute top-0 left-0 opacity-0 transition-opacity duration-1000 cursor-pointer {{ $loop->first ? 'opacity-100' : '' }}" onclick="openModal(this)">
                            @endforeach
                            @if($producto->imagenes->count() > 1)
                                <button class="prev-button absolute top-1/2 left-4 transform -translate-y-1/2 bg-gray-700 text-white rounded-full w-8 h-8 flex items-center justify-center z-20">&#8249;</button>
                                <button class="next-button absolute top-1/2 right-4 transform -translate-y-1/2 bg-gray-700 text-white rounded-full w-8 h-8 flex items-center justify-center z-20">&#8250;</button>
                            @endif
                        </div>
                    @else
                        <!-- En caso de que no haya imágenes disponibles -->
                        <img src="{{ asset('storage/placeholder.png') }}" alt="Imagen de {{ $producto->nombre }}" class="object-contain w-full h-full">
                    @endif
                </div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">{{ $producto->nombre }}</h2>
                <p class="text-gray-500 dark:text-gray-400 mb-2">Categoría: {{ $producto->categoria->nombre }}</p>
                <p class="text-gray-600 dark:text-gray-300">{{ $producto->descripcion }}</p>
                <p class="text-gray-800 dark:text-gray-100 mt-4 font-bold text-lg">${{ number_format($producto->precio, 2) }}</p>
                <div class="mt-4">
                    @guest
                        <a href="{{ route('login') }}" class="inline-block px-6 py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-800 transition-all duration-300">Inicia sesión para solicitar una cotización</a>
                    @endguest
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Modal para mostrar la imagen ampliada -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 items-center justify-center z-50" style="display: none;">
    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden w-11/12 max-w-3xl mx-auto mt-20">
        <button id="closeModal" class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center z-30">&times;</button>
        <img id="modalImage" src="" alt="Imagen ampliada del producto" class="w-full object-contain p-4 opacity-0 transition-opacity duration-1000 z-10">
        <button id="modalPrev" class="absolute top-1/2 left-4 transform -translate-y-1/2 bg-gray-700 text-white rounded-full w-8 h-8 flex items-center justify-center z-20" style="display: none;">&#8249;</button>
        <button id="modalNext" class="absolute top-1/2 right-4 transform -translate-y-1/2 bg-gray-700 text-white rounded-full w-8 h-8 flex items-center justify-center z-20" style="display: none;">&#8250;</button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sliders = document.querySelectorAll('.slider');
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');
        const closeModalButton = document.getElementById('closeModal');
        const modalPrevButton = document.getElementById('modalPrev');
        const modalNextButton = document.getElementById('modalNext');
        let currentModalIndex = 0;
        let currentSliderImages = [];
        let currentSlider = null;

        sliders.forEach(slider => {
            let currentIndex = 0;
            const images = slider.querySelectorAll('.slider-image');
            const prevButton = slider.querySelector('.prev-button');
            const nextButton = slider.querySelector('.next-button');

            if (images.length > 1) {
                // Función para cambiar automáticamente las imágenes cada 3 segundos con animación de desvanecimiento
                setInterval(() => {
                    images[currentIndex].classList.remove('opacity-100');
                    images[currentIndex].classList.add('opacity-0');
                    currentIndex = (currentIndex + 1) % images.length;
                    images[currentIndex].classList.remove('opacity-0');
                    images[currentIndex].classList.add('opacity-100');
                }, 3000);

                // Evento para botón "Siguiente"
                nextButton?.addEventListener('click', () => {
                    changeImage('next', images);
                });

                // Evento para botón "Anterior"
                prevButton?.addEventListener('click', () => {
                    changeImage('prev', images);
                });
            }

            // Función para abrir el modal con la imagen actualmente visible
            slider.addEventListener('click', function () {
                currentSlider = slider;
                currentSliderImages = images;
                currentModalIndex = currentIndex;

                showImageInModal(currentModalIndex);

                // Mostrar u ocultar botones de navegación en el modal según la cantidad de imágenes
                if (currentSliderImages.length > 1) {
                    modalPrevButton.style.display = 'block';
                    modalNextButton.style.display = 'block';
                } else {
                    modalPrevButton.style.display = 'none';
                    modalNextButton.style.display = 'none';
                }

                // Mostrar el modal
                modal.style.display = 'flex';
            });
        });

        // Función para cambiar la imagen en el slider y sincronizar con el modal
        function changeImage(direction, images) {
            images[currentModalIndex].classList.remove('opacity-100');
            images[currentModalIndex].classList.add('opacity-0');

            if (direction === 'next') {
                currentModalIndex = (currentModalIndex + 1) % images.length;
            } else {
                currentModalIndex = (currentModalIndex - 1 + images.length) % images.length;
            }

            images[currentModalIndex].classList.remove('opacity-0');
            images[currentModalIndex].classList.add('opacity-100');

            // Sincronizar el modal si está abierto
            if (modal.style.display === 'flex') {
                showImageInModal(currentModalIndex);
            }
        }

        // Cerrar el modal
        closeModalButton.addEventListener('click', function () {
            modal.style.display = 'none';
        });

        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });

        // Navegar imágenes en el modal y sincronizar con el slider
        modalNextButton.addEventListener('click', function () {
            changeImage('next', currentSliderImages);
        });

        modalPrevButton.addEventListener('click', function () {
            changeImage('prev', currentSliderImages);
        });

        // Función para mostrar la imagen en el modal
        function showImageInModal(index) {
            modalImage.classList.remove('opacity-100');
            modalImage.classList.add('opacity-0');
            modalImage.src = currentSliderImages[index].src;
            setTimeout(() => {
                modalImage.classList.remove('opacity-0');
                modalImage.classList.add('opacity-100');
            }, 10);
        }

        function syncSliderImage(index) {
            currentSliderImages.forEach((img, i) => {
                img.classList.toggle('opacity-100', i === index);
                img.classList.toggle('opacity-0', i !== index);
            });
        }
    });
</script>

@endsection
