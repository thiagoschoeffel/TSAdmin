<template>
  <div
    class="relative w-full max-w-full"
    :dir="dir"
    role="region"
    aria-roledescription="timeline"
    :aria-label="ariaLabel"
  >
    <!-- Botão esquerda -->
    <button
      v-if="showLeftArrow"
      type="button"
      class="timeline-arrow left-0"
      :aria-label="ariaPrevLabel"
      role="button"
      tabindex="0"
      :aria-disabled="!showLeftArrow"
      @click="scrollBy(-scrollStep)"
      @keydown.enter.space="scrollBy(-scrollStep)"
      :disabled="!showLeftArrow"
    >
      <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </button>
    <!-- Viewport -->
    <div
      ref="viewport"
      class="timeline-viewport overflow-hidden w-full max-w-full min-w-0"
      @wheel="onWheel"
      @touchstart.passive="onTouchStart"
      @touchmove.passive="onTouchMove"
      @touchend.passive="onTouchEnd"
      @keydown="onKeydown"
      tabindex="0"
      style="outline:none;"
    >
      <div
        ref="track"
        class="timeline-track flex gap-8 py-2 flex-nowrap min-w-0 w-max items-end"
        :style="trackStyle"
      >
        <slot />
      </div>
    </div>
    <!-- Botão direita -->
    <button
      v-if="showRightArrow"
      type="button"
      class="timeline-arrow right-0"
      :aria-label="ariaNextLabel"
      role="button"
      tabindex="0"
      :aria-disabled="!showRightArrow"
      @click="scrollBy(scrollStep)"
      @keydown.enter.space="scrollBy(scrollStep)"
      :disabled="!showRightArrow"
    >
      <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </button>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch, nextTick } from 'vue';

const props = defineProps({
  ariaLabel: { type: String, default: 'Linha do tempo de interações' },
  ariaPrevLabel: { type: String, default: 'Rolar para a esquerda' },
  ariaNextLabel: { type: String, default: 'Rolar para a direita' },
  scrollStep: { type: Number, default: 320 },
  dir: { type: String, default: 'ltr' },
});

const viewport = ref(null);
const track = ref(null);
const isDragging = ref(false);
const dragStartX = ref(0);
const scrollStart = ref(0);
const animationFrame = ref(null);
const lastTouchX = ref(0);
const velocity = ref(0);
const inertiaId = ref(null);

const showLeftArrow = ref(false);
const showRightArrow = ref(false);

const trackStyle = computed(() => ({
  'scroll-snap-type': 'x mandatory',
}));

function updateArrows() {
  if (!viewport.value) return;
  const { scrollLeft, scrollWidth, clientWidth } = viewport.value;

  // Margem para evitar flickering e garantir que as setas desapareçam no final
  const margin = 20;

  // Mostra seta esquerda se não está no início
  showLeftArrow.value = scrollLeft > margin;

  // Mostra seta direita se não está no final
  showRightArrow.value = scrollLeft + clientWidth < scrollWidth - margin;
}

function scrollBy(delta) {
  if (!viewport.value) return;
  viewport.value.scrollBy({ left: delta, behavior: 'smooth' });
  // Atualiza as setas imediatamente e novamente após a animação
  updateArrows();
  setTimeout(updateArrows, 350);
}

function onWheel(e) {
  if (!viewport.value) return;

  // Previne o scroll da página quando o mouse está na timeline
  e.preventDefault();
  e.stopPropagation();

  if (Math.abs(e.deltaX) > Math.abs(e.deltaY)) {
    viewport.value.scrollLeft += e.deltaX;
  } else {
    viewport.value.scrollLeft += e.deltaY * 1.2;
  }
  updateArrows();
}

function onKeydown(e) {
  if (["ArrowLeft", "ArrowRight", "Home", "End"].includes(e.key)) {
    e.preventDefault();
    if (e.key === "ArrowLeft") scrollBy(-props.scrollStep);
    if (e.key === "ArrowRight") scrollBy(props.scrollStep);
    if (e.key === "Home") scrollToEdge('start');
    if (e.key === "End") scrollToEdge('end');
  }
}

function scrollToEdge(edge) {
  if (!viewport.value) return;
  if (edge === 'start') viewport.value.scrollTo({ left: 0, behavior: 'smooth' });
  if (edge === 'end') viewport.value.scrollTo({ left: viewport.value.scrollWidth, behavior: 'smooth' });
  // Atualiza as setas imediatamente e novamente após a animação
  updateArrows();
  setTimeout(updateArrows, 350);
}

// Touch/drag
function onTouchStart(e) {
  if (!viewport.value) return;
  isDragging.value = true;
  dragStartX.value = e.touches[0].clientX;
  scrollStart.value = viewport.value.scrollLeft;
  lastTouchX.value = dragStartX.value;
  velocity.value = 0;
  if (inertiaId.value) cancelAnimationFrame(inertiaId.value);
}
function onTouchMove(e) {
  if (!isDragging.value || !viewport.value) return;
  const x = e.touches[0].clientX;
  const dx = dragStartX.value - x;
  viewport.value.scrollLeft = scrollStart.value + dx;
  velocity.value = x - lastTouchX.value;
  lastTouchX.value = x;
  updateArrows();
}
function onTouchEnd() {
  isDragging.value = false;
  // Inércia simples
  if (Math.abs(velocity.value) > 2) {
    let v = -velocity.value * 2;
    function inertia() {
      if (!viewport.value) return;
      viewport.value.scrollLeft += v;
      v *= 0.92;
      if (Math.abs(v) > 1) {
        inertiaId.value = requestAnimationFrame(inertia);
      } else {
        updateArrows();
      }
    }
    inertia();
  } else {
    updateArrows();
  }
}

function onResize() {
  updateArrows();
}

onMounted(() => {
  nextTick(updateArrows);
  window.addEventListener('resize', onResize, { passive: true });
});
onUnmounted(() => {
  window.removeEventListener('resize', onResize);
  if (inertiaId.value) cancelAnimationFrame(inertiaId.value);
});

watch(() => [props.dir], () => nextTick(updateArrows));
</script>

<style scoped>
.timeline-viewport {
  scrollbar-width: none;
  -ms-overflow-style: none;
}
.timeline-viewport::-webkit-scrollbar {
  display: none;
}
.timeline-arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  z-index: 10;
  background-color: rgba(255, 255, 255, 0.8);
  border-radius: 9999px;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  padding: 0.25rem;
  border: 1px solid #e2e8f0;
  transition: opacity 0.15s ease-in-out;
}
.timeline-arrow:disabled {
  opacity: 0.4;
  pointer-events: none;
}
.timeline-arrow.left-0 { left: 0.25rem; }
.timeline-arrow.right-0 { right: 0.25rem; }

/* Linha conectora horizontal contínua */
.timeline-track::after {
  content: '';
  position: absolute;
  bottom: 5px; /* Centro da linha alinhado com centro dos marcadores */
  left: 0;
  right: 0;
  height: 2px;
  background-color: #e2e8f0;
  z-index: 1;
}

/* Marcadores e traços verticais para cada item */
.timeline-track > * {
  position: relative;
  padding-bottom: 1.5rem; /* Mais espaço entre card e linha */
}

.timeline-track > *::after {
  content: '';
  position: absolute;
  bottom: 0px; /* Começa no topo da linha */
  left: 50%;
  transform: translateX(-50%);
  width: 2px;
  height: 1rem; /* Traço vertical mais longo */
  background-color: #e2e8f0;
  z-index: 2;
}

.timeline-track > *::before {
  content: '';
  position: absolute;
  bottom: -5px; /* Marcador mais abaixo, sobre a linha horizontal */
  left: 50%;
  transform: translateX(-50%);
  width: 8px;
  height: 8px;
  background-color: #3b82f6;
  border: 2px solid white;
  border-radius: 50%;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  z-index: 4; /* Acima de tudo */
}
</style>
