<script setup>
import { computed, getCurrentInstance } from 'vue';
import Dropdown from '@/components/Dropdown.vue';
import Button from '@/components/Button.vue';
import HeroIcon from '@/components/icons/HeroIcon.vue';

const props = defineProps({
  columns: {
    type: Array,
    required: true,
    validator: (columns) => columns.every(col => col.header && (col.key || col.component))
  },
  data: {
    type: Array,
    default: () => []
  },
  actions: {
    type: [Array, Function],
    default: () => []
  },
  emptyMessage: {
    type: String,
    default: 'Nenhum registro encontrado.'
  },
  rowKey: {
    type: String,
    default: 'id'
  }
});

const emit = defineEmits(['action']);

const instance = getCurrentInstance();
const route = instance.appContext.config.globalProperties.route;

const colspan = computed(() => props.columns.length + ((typeof props.actions === 'function' || props.actions.length > 0) ? 1 : 0));

const handleAction = (action, item) => {
  emit('action', { action, item });
};
</script>

<template>
  <div class="relative overflow-x-auto">
    <table class="min-w-full border-separate table">
      <thead>
        <tr>
          <th
            v-for="column in columns"
            :key="column.key || column.header"
            :class="['border-b-2 border-slate-200 px-3 py-3 text-left text-sm font-semibold text-slate-600', column.class]"
          >
            {{ column.header }}
          </th>
          <th
            v-if="typeof actions === 'function' || actions.length > 0"
            class="w-24 whitespace-nowrap border-b-2 border-slate-200 px-3 py-3 text-left text-sm font-semibold text-slate-600"
          >
            Ações
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in data" :key="item[rowKey]">
          <td
            v-for="column in columns"
            :key="column.key || column.header"
            :class="['border-b border-slate-200 px-3 py-3 text-sm text-slate-800', column.class]"
          >
            <component
              v-if="column.component"
              :is="column.component"
              v-bind="column.props ? column.props(item) : {}"
            >
              {{ column.formatter ? column.formatter(item[column.key], item) : item[column.key] }}
            </component>
            <template v-else>
              {{ column.formatter ? column.formatter(item[column.key], item) : item[column.key] }}
            </template>
          </td>
          <td v-if="typeof actions === 'function' || actions.length > 0" class="whitespace-nowrap border-b border-slate-200 px-3 py-3 text-sm text-slate-800">
            <Dropdown v-if="typeof actions === 'function' ? actions(item, route).length > 0 : actions.length > 0">
              <template #trigger="{ toggle }">
                <Button variant="ghost" size="sm" @click="toggle" aria-label="Abrir menu de ações">
                  <HeroIcon name="ellipsis-horizontal" class="h-5 w-5" />
                </Button>
              </template>
              <template #default="{ close }">
                <template v-for="action in (typeof actions === 'function' ? actions(item, route) : actions)" :key="action.key">
                  <component
                    v-if="action.component"
                    :is="action.component"
                    v-bind="action.props ? action.props(item, route) : {}"
                    @click="handleAction(action, item); close()"
                  >
                    <HeroIcon v-if="action.icon" :name="action.icon" class="h-4 w-4" />
                    <span>{{ action.label }}</span>
                  </component>
                  <button
                    v-else
                    type="button"
                    :class="action.class || 'menu-panel-link'"
                    @click="handleAction(action, item); close()"
                  >
                    <HeroIcon v-if="action.icon" :name="action.icon" class="h-4 w-4" />
                    <span>{{ action.label }}</span>
                  </button>
                </template>
              </template>
            </Dropdown>
          </td>
        </tr>
        <tr v-if="!data || data.length === 0">
          <td :colspan="colspan" class="px-4 py-6 text-center text-sm text-slate-500">{{ emptyMessage }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.table { border-collapse: separate; border-spacing: 0; }
</style>
