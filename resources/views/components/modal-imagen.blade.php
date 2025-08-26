<!-- Modal para mostrar la imagen ampliada -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 items-center justify-center z-50" style="display:none;">
  <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden w-11/12 max-w-3xl mx-auto mt-20" style="user-select:none;">
    <button id="closeModal" class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center z-30" style="user-select:none;">&times;</button>
    <img id="modalImage" src="" alt="Imagen ampliada del producto" class="w-full object-contain p-4 opacity-0 transition-opacity duration-300 z-10">
    <button id="modalPrev" class="absolute top-1/2 left-4 -translate-y-1/2 bg-gray-700 text-white rounded-full w-8 h-8 grid place-items-center z-20" style="display:none;user-select:none;">&#8249;</button>
    <button id="modalNext" class="absolute top-1/2 right-4 -translate-y-1/2 bg-gray-700 text-white rounded-full w-8 h-8 grid place-items-center z-20" style="display:none;user-select:none;">&#8250;</button>
  </div>
</div>

<!-- (Opcional) TOAST global si lo usas -->
<div id="toast-global" class="fixed bottom-5 right-5 text-white p-4 rounded-md shadow-lg opacity-0 transition-opacity duration-300 z-50 flex items-center gap-2" style="min-width:300px;">
  <span id="toast-icon">✅</span>
  <span id="toast-text">Mensaje</span>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const modal      = document.getElementById('imageModal');
  const modalImage = document.getElementById('modalImage');
  const btnClose   = document.getElementById('closeModal');
  const btnPrev    = document.getElementById('modalPrev');
  const btnNext    = document.getElementById('modalNext');

  // Contexto del carrusel activo (se lo pasa cada tarjeta al abrir el modal)
  let currentCtx   = null;   // {slides, paint(i), getIdx(), setIdx(i), startAuto(), stopAuto()}
  let currentIndex = 0;

  const showModal = (src) => {
    modalImage.style.opacity = 0;
    modalImage.src = src || '';
    modal.style.display = 'flex';
    requestAnimationFrame(() => modalImage.style.opacity = 1);
  };

  const hideModal = () => {
    modal.style.display = 'none';
    modalImage.src = '';
    // reanudar auto-rotación del carrusel
    if (currentCtx && typeof currentCtx.startAuto === 'function') currentCtx.startAuto();
    currentCtx = null;
  };

  const updateArrows = () => {
    const count = currentCtx?.slides?.length || 0;
    const show  = count > 1;
    btnPrev.style.display = show ? 'grid' : 'none';
    btnNext.style.display = show ? 'grid' : 'none';
  };

  const showByIndex = (i) => {
    if (!currentCtx || !currentCtx.slides?.length) return;
    const n = currentCtx.slides.length;
    currentIndex = (i + n) % n;
    // Sincroniza carrusel visual
    currentCtx.setIdx(currentIndex);
    currentCtx.paint(currentIndex);
    // Muestra en modal
    showModal(currentCtx.slides[currentIndex].src);
    updateArrows();
  };

  // Controles modal
  btnClose.addEventListener('click', hideModal);
  modal.addEventListener('click', (e) => { if (e.target === modal) hideModal(); });
  document.addEventListener('keydown', (e) => {
    if (modal.style.display !== 'flex') return;
    if (e.key === 'Escape') hideModal();
    if (e.key === 'ArrowLeft')  showByIndex(currentIndex - 1);
    if (e.key === 'ArrowRight') showByIndex(currentIndex + 1);
  });
  btnPrev.addEventListener('click', (e) => { e.stopPropagation(); showByIndex(currentIndex - 1); });
  btnNext.addEventListener('click', (e) => { e.stopPropagation(); showByIndex(currentIndex + 1); });

  // Apertura directa de imagen suelta (fuera de carruseles) con data-open-image
  document.body.addEventListener('click', (e) => {
    const t = e.target.closest('[data-open-image]');
    if (!t) return;
    currentCtx = null; // modo imagen única
    updateArrows();
    showModal(t.getAttribute('data-open-image'));
  });

  // Exponer un helper global para abrir el modal desde cualquier carrusel
  window.__openImageFromSlider = (ctx, startIndex = 0) => {
    // pausar auto-rotado del carrusel
    if (ctx && typeof ctx.stopAuto === 'function') ctx.stopAuto();
    currentCtx   = ctx;
    currentIndex = startIndex;
    showByIndex(currentIndex);
  };
});
</script>
