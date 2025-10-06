<script setup>
import { ref, watch } from 'vue';
import Modal from '@/components/Modal.vue';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  userId: { type: [Number, String, null], default: null },
});

const emit = defineEmits(['update:modelValue']);

const open = ref(props.modelValue);
const loading = ref(false);
const error = ref(false);
const html = ref('');

watch(() => props.modelValue, (v) => { open.value = v; if (v) tryFetch(); });
watch(open, (v) => emit('update:modelValue', v));
watch(() => props.userId, () => { if (open.value) tryFetch(); });

async function tryFetch() {
  if (!props.userId) return;
  loading.value = true;
  error.value = false;
  html.value = '';
  try {
    const res = await fetch(`/admin/users/${props.userId}/modal`, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    if (!res.ok) throw new Error('failed');
    const data = await res.json();
    if (!data || typeof data.html !== 'string') throw new Error('invalid');
    html.value = data.html;
  } catch (_) {
    error.value = true;
  } finally {
    loading.value = false;
  }
}

function retry() { tryFetch(); }
</script>

<template>
  <Modal v-model="open" title="Detalhes do usuário" size="lg">
    <div v-if="loading" class="space-y-6" aria-hidden="true">
      <div class="space-y-3">
        <div class="skeleton h-6 w-48 rounded-md"></div>
        <div class="skeleton h-4 w-64 rounded-md"></div>
      </div>
      <div class="space-y-4">
        <div class="skeleton h-5 w-40 rounded-md"></div>
        <div class="grid gap-4 sm:grid-cols-2">
          <div class="space-y-3">
            <div class="skeleton h-4 w-24 rounded-md"></div>
            <div class="skeleton h-4 w-32 rounded-md"></div>
          </div>
          <div class="space-y-3">
            <div class="skeleton h-4 w-24 rounded-md"></div>
            <div class="skeleton h-4 w-28 rounded-md"></div>
          </div>
          <div class="space-y-3">
            <div class="skeleton h-4 w-24 rounded-md"></div>
            <div class="skeleton h-4 w-28 rounded-md"></div>
          </div>
        </div>
      </div>
      <div class="space-y-4">
        <div class="skeleton h-5 w-32 rounded-md"></div>
        <div class="grid gap-4 sm:grid-cols-2">
          <div class="space-y-3">
            <div class="skeleton h-4 w-28 rounded-md"></div>
            <div class="skeleton h-4 w-36 rounded-md"></div>
          </div>
          <div class="space-y-3">
            <div class="skeleton h-4 w-28 rounded-md"></div>
            <div class="skeleton h-4 w-32 rounded-md"></div>
          </div>
        </div>
      </div>
      <span class="sr-only">Carregando detalhes do usuário...</span>
    </div>

    <div v-else-if="error" class="flex flex-col items-center justify-center gap-3 text-center text-sm text-slate-500">
      <p class="text-sm text-rose-600">Não foi possível carregar os detalhes do usuário.</p>
      <button type="button" class="btn-secondary" @click="retry">Tentar novamente</button>
    </div>

    <div v-else v-html="html" class="space-y-6"></div>
  </Modal>
</template>

