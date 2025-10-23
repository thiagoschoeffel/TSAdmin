<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import InputDatePicker from '@/components/InputDatePicker.vue';
import Button from '@/components/Button.vue';
import { ref, computed, getCurrentInstance } from 'vue';

const props = defineProps({
  filters: { type: Object, default: () => ({}) },
  summary: { type: Object, required: true },
  siloLoads: { type: Array, default: () => [] },
});

const instance = getCurrentInstance();
const route = instance.appContext.config.globalProperties.route;

const period = ref({ start: props.filters?.from || null, end: props.filters?.to || null });
const loading = ref(false);
const summary = ref(props.summary);
const silos = ref(props.siloLoads);

const fetchData = async () => {
  loading.value = true;
  try {
    const q = new URLSearchParams();
    if (period.value.start) q.set('from', period.value.start);
    if (period.value.end) q.set('to', period.value.end);
    const [sumRes, siloRes] = await Promise.all([
      fetch(route('inventory.summary') + (q.toString() ? ('?' + q.toString()) : '')),
      fetch(route('inventory.silos.load')),
    ]);
    summary.value = await sumRes.json();
    const siloData = await siloRes.json();
    silos.value = siloData.data || [];
  } finally {
    loading.value = false;
  }
};

const fmt = (n, d=2) => new Intl.NumberFormat('pt-BR', { minimumFractionDigits: d, maximumFractionDigits: d }).format(Number(n||0));

const totalSilos = computed(() => silos.value.length);
const totalMateriaisEmSilos = computed(() => silos.value.reduce((acc, s) => acc + (s.materials?.length || 0), 0));
</script>

<template>
  <Head title="Estoque - Resumo" />
  <AdminLayout>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-semibold tracking-tight">Estoque - Resumo</h1>
      <div class="flex items-center gap-2">
        <InputDatePicker v-model="period.start" placeholder="De" />
        <InputDatePicker v-model="period.end" placeholder="Até" />
        <Button :disabled="loading" @click="fetchData">Atualizar</Button>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div class="rounded-lg border bg-white p-4">
        <div class="text-slate-500 text-sm">Entrada MP (kg)</div>
        <div class="text-2xl font-bold">{{ fmt(summary.raw_material_input_kg) }}</div>
      </div>
      <div class="rounded-lg border bg-white p-4">
        <div class="text-slate-500 text-sm">Consumo MP (kg)</div>
        <div class="text-2xl font-bold">{{ fmt(summary.raw_material_consumed_kg) }}</div>
      </div>
      <div class="rounded-lg border bg-white p-4">
        <div class="text-slate-500 text-sm">Produção Blocos (kg)</div>
        <div class="text-2xl font-bold">{{ fmt(summary.blocks_produced_kg) }}</div>
      </div>
      <div class="rounded-lg border bg-white p-4">
        <div class="text-slate-500 text-sm">Produção Moldados (kg)</div>
        <div class="text-2xl font-bold">{{ fmt(summary.molded_produced_kg) }}</div>
      </div>
      <div class="rounded-lg border bg-white p-4">
        <div class="text-slate-500 text-sm">Perda Blocos (kg)</div>
        <div class="text-2xl font-bold">{{ fmt(summary.block_loss_kg) }}</div>
      </div>
      <div class="rounded-lg border bg-white p-4">
        <div class="text-slate-500 text-sm">Silos / Materiais</div>
        <div class="text-2xl font-bold">{{ totalSilos }} / {{ totalMateriaisEmSilos }}</div>
      </div>
    </div>

    <div class="mt-8">
      <h2 class="text-lg font-semibold mb-3">Cargas por Silo</h2>
      <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div v-for="s in silos" :key="s.silo_id" class="rounded-lg border bg-white p-4">
          <div class="font-semibold mb-2">{{ s.silo_name }}</div>
          <div v-if="!s.materials || s.materials.length === 0" class="text-slate-500 text-sm">Sem carga registrada</div>
          <ul v-else class="space-y-1">
            <li v-for="m in s.materials" :key="m.raw_material_id" class="flex items-center justify-between text-sm">
              <span>{{ m.raw_material_name }}</span>
              <span class="font-mono">{{ fmt(m.balance_kg) }} kg</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </AdminLayout>
  
</template>

