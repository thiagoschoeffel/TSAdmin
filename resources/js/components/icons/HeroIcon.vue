<script setup>
import { computed } from 'vue';
import {
  ChartBarIcon,
  UsersIcon,
  IdentificationIcon,
  UserCircleIcon,
  ArrowLeftEndOnRectangleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  name: { type: String, required: true },
  class: { type: String, default: 'h-4 w-4' },
});

const map = {
  'chart-bar': ChartBarIcon,
  'users': UsersIcon,
  'identification': IdentificationIcon,
  'user-circle': UserCircleIcon,
  'arrow-left-end-on-rectangle': ArrowLeftEndOnRectangleIcon,
};

const Icon = computed(() => map[props.name] || null);
</script>

<template>
  <component :is="Icon" :class="props.class" v-if="Icon" />
</template>
