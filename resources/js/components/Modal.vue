<script setup>
import { ref, watch, onMounted, onBeforeUnmount, computed, Teleport, nextTick } from 'vue';
import { registerModal, unregisterModal, getIndex, hasOpenModals } from '@/components/modalStack';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  title: { type: String, default: '' },
  size: { type: String, default: 'md' }, // sm, md, lg, xl
  closeOnEsc: { type: Boolean, default: true },
  closeOnBackdrop: { type: Boolean, default: true },
  zBase: { type: Number, default: 1100 },
  showClose: { type: Boolean, default: true },
});

const emit = defineEmits(['update:modelValue', 'open', 'close']);

const open = ref(props.modelValue);
const id = ref(null);
const container = ref(null);

const index = computed(() => (id.value ? getIndex(id.value) : -1));
const zIndexOverlay = computed(() => props.zBase + (index.value >= 0 ? index.value * 20 : 0));
const zIndexPanel = computed(() => zIndexOverlay.value + 1);

const sizes = {
  sm: 'max-w-md',
  md: 'max-w-lg',
  lg: 'max-w-2xl',
  xl: 'max-w-4xl',
};
const panelSize = computed(() => sizes[props.size] || sizes.md);

watch(() => props.modelValue, async (val) => {
  if (val === open.value) return;
  open.value = val;
  if (val) onOpen(); else onClose();
});

watch(open, (val) => emit('update:modelValue', val));

function onKeydown(e) {
  if (props.closeOnEsc && e.key === 'Escape') {
    e.stopPropagation();
    close();
  }
}

function onBackdrop(e) {
  if (!props.closeOnBackdrop) return;
  if (e.target === e.currentTarget) close();
}

function onOpen() {
  if (!id.value) id.value = registerModal();
  document.addEventListener('keydown', onKeydown, true);
  // Lock body scroll if at least one modal is open
  document.body.style.overflow = 'hidden';
  emit('open');
  nextTick(() => {
    // Focus the first focusable element
    try {
      const el = container.value?.querySelector('[autofocus]') || container.value?.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
      el?.focus?.();
    } catch (_) {}
  });
}

function onClose() {
  document.removeEventListener('keydown', onKeydown, true);
  if (id.value) unregisterModal(id.value);
  id.value = null;
  // Restore body scroll if no modals
  if (!hasOpenModals()) document.body.style.overflow = '';
  emit('close');
}

function close() { open.value = false; }

onMounted(() => { if (open.value) onOpen(); });
onBeforeUnmount(() => { if (open.value) onClose(); });
</script>

<template>
  <Teleport to="body">
    <div v-show="open" class="fixed inset-0 p-4 sm:p-6 md:p-8" :style="{ zIndex: zIndexOverlay }" @click="onBackdrop" aria-modal="true" role="dialog">
      <div class="absolute inset-0 bg-slate-900/50"></div>
      <div class="relative mx-auto w-full" :class="panelSize" :style="{ zIndex: zIndexPanel }">
        <div ref="container" class="rounded-xl border border-slate-200 bg-white shadow-2xl">
          <div class="flex items-start justify-between gap-4 border-b border-slate-200 p-4">
            <h3 class="text-base font-semibold text-slate-900">{{ title }}</h3>
            <button v-if="showClose" type="button" class="rounded-md p-1 text-slate-500 hover:bg-slate-100" @click="close" aria-label="Fechar">
              ✕
            </button>
          </div>
          <div class="p-4">
            <slot />
          </div>
          <div v-if="$slots.footer" class="flex justify-end gap-2 border-t border-slate-200 p-3">
            <slot name="footer" :close="close" />
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
</style>

