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
  ShoppingBagIcon,
  InformationCircleIcon,
  PrinterIcon,
  ChatBubbleLeftRightIcon,
  DocumentCurrencyDollarIcon,
  EnvelopeIcon,
  UserGroupIcon,
  PhoneIcon,
  HomeIcon,
  DevicePhoneMobileIcon,
  ChatBubbleOvalLeftEllipsisIcon,
  RectangleGroupIcon,
  CpuChipIcon,
  PauseCircleIcon,
  SwatchIcon,
  TagIcon,
  ClipboardDocumentListIcon,
  ArrowsRightLeftIcon,
  BuildingOfficeIcon,
  FolderIcon,
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
  'shopping-bag': ShoppingBagIcon,
  'information-circle': InformationCircleIcon,
  'printer': PrinterIcon,
  'chat-bubble-left-right': ChatBubbleLeftRightIcon,
  'document-currency-dollar': DocumentCurrencyDollarIcon,
  'envelope': EnvelopeIcon,
  'user-group': UserGroupIcon,
  'phone': PhoneIcon,
  'home': HomeIcon,
  'device-phone-mobile': DevicePhoneMobileIcon,
  'chat-bubble-oval-left-ellipsis': ChatBubbleOvalLeftEllipsisIcon,
  'rectangle-group': RectangleGroupIcon,
  'cpu-chip': CpuChipIcon,
  'pause-circle': PauseCircleIcon,
  'swatch': SwatchIcon,
  'tag': TagIcon,
  'clipboard-document-list': ClipboardDocumentListIcon,
  'arrows-right-left': ArrowsRightLeftIcon,
  'office-building': BuildingOfficeIcon,
  'folder': FolderIcon,
};

const Icon = computed(() => map[props.name] || null);
</script>

<template>
  <component :is="Icon" :class="props.class" v-if="Icon" />
</template>
