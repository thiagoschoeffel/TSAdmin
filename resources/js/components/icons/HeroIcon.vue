<script setup>
import { computed } from 'vue';
import {
  ChartBarIcon,
  UsersIcon,
  IdentificationIcon,
  UserCircleIcon,
  ArrowLeftEndOnRectangleIcon,
  FunnelIcon,
  EllipsisHorizontalIcon,
  PencilIcon,
  TrashIcon,
  XMarkIcon,
  CubeIcon,
  ChevronDownIcon,
  ChevronRightIcon,
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
  'funnel': FunnelIcon,
  'ellipsis-horizontal': EllipsisHorizontalIcon,
  'pencil': PencilIcon,
  'trash': TrashIcon,
  'x-mark': XMarkIcon,
  'cube': CubeIcon,
  'chevron-down': ChevronDownIcon,
  'chevron-right': ChevronRightIcon,
};

const Icon = computed(() => map[props.name] || null);
</script>

<template>
  <component :is="Icon" :class="props.class" v-if="Icon" />
</template>
