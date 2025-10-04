<script setup>
import { ref, watch } from 'vue';
import Modal from '@/components/Modal.vue';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  title: { type: String, default: 'Confirmar ação' },
  message: { type: String, default: 'Deseja prosseguir?' },
  confirmText: { type: String, default: 'Confirmar' },
  cancelText: { type: String, default: 'Cancelar' },
  variant: { type: String, default: 'primary' }, // primary | danger
  processing: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel']);
const open = ref(props.modelValue);

watch(() => props.modelValue, (v) => open.value = v);
watch(open, (v) => emit('update:modelValue', v));

const onConfirm = () => emit('confirm');
const onCancel = () => { open.value = false; emit('cancel'); };
</script>

<template>
  <Modal v-model="open" :title="title" size="sm">
    <p class="text-slate-700">{{ message }}</p>
    <template #footer>
      <button type="button" class="btn-ghost" :disabled="processing" @click="onCancel">{{ cancelText }}</button>
      <button type="button" :disabled="processing" :class="['btn-primary', variant === 'danger' ? 'bg-rose-600 hover:bg-rose-700' : '']" @click="onConfirm">
        <span v-if="!processing">{{ confirmText }}</span>
        <span v-else>Processando…</span>
      </button>
    </template>
  </Modal>
</template>

<style scoped>
.btn-ghost { display:inline-flex; align-items:center; gap:.5rem; padding:.5rem .75rem; border-radius:.5rem; border:1px solid #cbd5e1; color:#0f172a; }
.btn-primary { display:inline-flex; align-items:center; gap:.5rem; padding:.5rem .75rem; border-radius:.5rem; background:#2563eb; color:#fff; font-weight:600; }
</style>

