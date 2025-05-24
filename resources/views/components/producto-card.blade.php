<div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-105 flex flex-col text-center h-full">

    <!-- Imagen -->
    <div class="h-64 w-full mb-4 overflow-hidden relative flex items-center justify-center cursor-pointer" onclick="abrirModalImagen(this)">
        @if(!empty($producto->imagenes) && count($producto->imagenes) > 0)
            <img src="data:image/png;base64,{{ $producto->imagenes[0]->contenido }}" alt="Imagen de {{ $producto->nombre }}" class="object-contain max-h-full producto-modal-trigger" style="user-select: none;">
        @else
            <img src="{{ asset('storage/placeholder.png') }}" alt="Imagen de {{ $producto->nombre }}" class="object-contain max-h-full producto-modal-trigger" style="user-select: none;">
        @endif
    </div>

    <!-- Contenido con altura fija para alinear -->
    <div class="flex flex-col justify-start min-h-[230px]">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-1">{{ $producto->nombre }}</h2>
        <p class="text-lg font-bold text-gray-900 dark:text-white mb-1">Especificaciones técnicas:</p>
        <p class="text-gray-900 dark:text-white text-sm line-clamp-4 leading-relaxed">
            {{ $producto->descripcion }}
        </p>
    </div>

    <!-- Precio y stock -->
    <div class="mt-4">
        <p class="text-gray-900 dark:text-white font-bold text-lg"><strong>Precio:</strong> ${{ number_format($producto->precio, 0, ',', '.') }}</p>
        <p class="text-gray-900 dark:text-white mb-2"><strong>Unidades disponibles:</strong> {{ $producto->stock }}</p>
    </div>

    <!-- Agregar al carrito -->
    <form action="{{ route('carrito.agregar', $producto->id) }}" method="POST" class="add-to-cart-form mt-auto">
        @csrf
        <label class="block text-sm font-semibold text-gray-900 dark:text-white">Cantidad</label>
        <input type="number" name="cantidad" value="1" min="1" max="{{ $producto->stock }}"
               class="w-16 p-2 border rounded-md text-center cantidad-input bg-white text-black dark:bg-gray-800 dark:text-white dark:border-gray-700" required>

        @auth
            <button type="submit" class="btn-agregar-carrito">Agregar al carrito</button>
        @else
            <button type="button" onclick="window.location.href='{{ route('login') }}'" class="btn-agregar-carrito">Inicia sesión para comprar</button>
        @endauth
    </form>
</div>

<script>
    // ...otros scripts que ya tienes (como scrollToTop, toast, etc.)

    function abrirModalImagen(elemento) {
        const src = elemento.querySelector('img').src;
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImage');

        modalImg.src = src;
        modal.style.display = 'flex';
        modalImg.classList.remove('opacity-0');
        modalImg.classList.add('opacity-100');
    }

    // Cerrar modal al hacer clic fuera o en el botón X
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImage');
        const closeModal = document.getElementById('closeModal');

        closeModal.addEventListener('click', () => {
            modal.style.display = 'none';
            modalImg.classList.remove('opacity-100');
            modalImg.classList.add('opacity-0');
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
                modalImg.classList.remove('opacity-100');
                modalImg.classList.add('opacity-0');
            }
        });
    });

    // Auto-rotation for product images
        function startAutoRotate(sliderImages) {
            stopAutoRotate();
            autoRotateInterval = setInterval(() => {
                changeImage('next', sliderImages);
            }, 3000);
        }

        function stopAutoRotate() {
            clearInterval(autoRotateInterval);
        }

    sliders.forEach(slider => {
            let currentIndex = 0;
            const images = slider.querySelectorAll('.slider-image');
            const prevButton = slider.querySelector('.prev-button');
            const nextButton = slider.querySelector('.next-button');

            if (images.length > 1) {
                startAutoRotate(images);

                nextButton?.addEventListener('click', () => {
                    changeImage('next', images);
                });

                prevButton?.addEventListener('click', () => {
                    changeImage('prev', images);
                });
            }

            slider.addEventListener('click', function (event) {
                if (!event.target.classList.contains('prev-button') && !event.target.classList.contains('next-button')) {
                    currentSlider = slider;
                    currentSliderImages = images;
                    const visibleImage = Array.from(images).findIndex(image => image.classList.contains('opacity-100'));
                    currentModalIndex = visibleImage !== -1 ? visibleImage : 0;

                    showImageInModal(currentModalIndex);

                    stopAutoRotate();

                    if (currentSliderImages.length > 1) {
                        modalPrevButton.style.display = 'block';
                        modalNextButton.style.display = 'block';
                    } else {
                        modalPrevButton.style.display = 'none';
                        modalNextButton.style.display = 'none';
                    }

                    modal.style.display = 'flex';
                }
            });
        });

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

            if (modal.style.display === 'flex') {
                showImageInModal(currentModalIndex);
            }
        }

        closeModalButton.addEventListener('click', function () {
            modal.style.display = 'none';
            startAutoRotate(currentSliderImages);
        });

        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.style.display = 'none';
                startAutoRotate(currentSliderImages);
            }
        });

        modalNextButton.addEventListener('click', function () {
            changeImage('next', currentSliderImages);
        });

        modalPrevButton.addEventListener('click', function () {
            changeImage('prev', currentSliderImages);
        });

        function showImageInModal(index) {
            modalImage.classList.remove('opacity-100');
            modalImage.classList.add('opacity-0');
            modalImage.src = currentSliderImages[index].src;
            setTimeout(() => {
                modalImage.classList.remove('opacity-0');
                modalImage.classList.add('opacity-100');
            }, 10);
        }
</script>
