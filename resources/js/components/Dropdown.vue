<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  panelClass: { type: String, default: 'menu-panel' },
  openClass: { type: String, default: 'is-open' },
});

const isOpen = ref(false);
const root = ref(null);

const toggle = () => { isOpen.value = !isOpen.value; };
const close = () => { isOpen.value = false; };

const onClickOutside = (e) => {
  if (!root.value) return;
  if (!root.value.contains(e.target)) close();
};
const onEscape = (e) => {
  if (e.key === 'Escape') close();
};

onMounted(() => {
  document.addEventListener('click', onClickOutside);
  document.addEventListener('keydown', onEscape);
});
onBeforeUnmount(() => {
  document.removeEventListener('click', onClickOutside);
  document.removeEventListener('keydown', onEscape);
});
</script>

<template>
  <div ref="root" class="relative inline-block">
    <slot name="trigger" :open="isOpen" :toggle="toggle"></slot>
    <div :class="[panelClass, isOpen ? openClass : 'hidden']">
      <slot :close="close" />
    </div>
  </div>
  
</template>

