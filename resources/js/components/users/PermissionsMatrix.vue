<script setup>
import { computed, watch, reactive } from 'vue';

const props = defineProps({
  resources: { type: Object, required: true },
  role: { type: String, default: 'user' },
  modelValue: { type: Object, default: () => ({}) }, // permissions
  modules: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['update:modelValue', 'update:modules']);

const isAdmin = computed(() => props.role === 'admin');

const permissions = reactive(JSON.parse(JSON.stringify(props.modelValue || {})));
const modules = reactive(JSON.parse(JSON.stringify(props.modules || {})));

const ensureStructure = () => {
  Object.entries(props.resources || {}).forEach(([key, resource]) => {
    if (!permissions[key]) permissions[key] = {};
    if (modules[key] == null) modules[key] = false;
    const abilities = Object.keys(resource.abilities || {});
    abilities.forEach((a) => {
      if (permissions[key][a] == null) permissions[key][a] = false;
    });
  });
};

ensureStructure();

// Sync out
watch(() => permissions, (v) => emit('update:modelValue', v), { deep: true });
watch(() => modules, (v) => emit('update:modules', v), { deep: true });

// When admin, force-enable all
watch(isAdmin, (val) => {
  if (val) {
    Object.entries(props.resources || {}).forEach(([key, resource]) => {
      modules[key] = true;
      Object.keys(resource.abilities || {}).forEach((a) => {
        permissions[key][a] = true;
      });
    });
  }
});

const toggleModule = (key, on) => {
  modules[key] = !!on;
  if (!on) {
    Object.keys(props.resources?.[key]?.abilities || {}).forEach((a) => {
      permissions[key][a] = false;
    });
  }
};
</script>

<template>
  <div class="space-y-2">
    <span class="text-sm font-semibold text-slate-700">Permissões</span>
    <div class="space-y-4">
      <fieldset v-for="(resource, key) in resources" :key="key" class="rounded-xl border border-slate-200 bg-slate-50 p-4">
        <legend class="flex items-center justify-between gap-4 px-1 text-sm font-semibold text-slate-800">
          <span>{{ resource.label }}</span>
          <label class="inline-flex items-center gap-2 text-xs font-medium text-slate-600">
            <input type="checkbox"
                   :checked="isAdmin ? true : !!modules[key]"
                   :disabled="isAdmin"
                   class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/20"
                   @change="(e) => toggleModule(key, e.target.checked)" />
            <span>Acesso ao módulo</span>
          </label>
        </legend>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
          <label v-for="(label, ability) in resource.abilities" :key="ability" class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
            <input type="checkbox"
                   class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/20"
                   :checked="isAdmin ? true : !!permissions[key][ability]"
                   :disabled="isAdmin || !modules[key]"
                   @change="(e) => permissions[key][ability] = e.target.checked" />
            <span>{{ label }}</span>
          </label>
        </div>
        <p class="mt-2 text-xs text-slate-500" v-if="isAdmin">
          Todas as permissões e módulos estão habilitados para administradores.
        </p>
      </fieldset>
    </div>
  </div>
</template>

