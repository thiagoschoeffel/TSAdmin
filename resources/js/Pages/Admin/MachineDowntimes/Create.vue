<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/components/Button.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive, getCurrentInstance } from 'vue';
import MachineDowntimeForm from '@/components/machineDowntimes/MachineDowntimeForm.vue';

const props = defineProps({
  machines: { type: Array, default: () => [] },
  reasons: { type: Array, default: () => [] },
});

const instance = getCurrentInstance();
const route = instance.appContext.config.globalProperties.route;

const form = reactive({
  machine_id: '',
  reason_id: '',
  started_at: '',
  ended_at: '',
  notes: '',
  status: 'active',
  errors: {},
});

async function submit() {
  form.errors = {};
  await router.post(route('machine_downtimes.store'), form, {
    onError: (e) => form.errors = e,
  });
}
</script>

<template>
  <AdminLayout>
    <Head title="Cadastrar Parada de Máquina" />

    <section class="card space-y-8">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900">Cadastrar Parada de Máquina</h1>
          <p class="mt-2 text-sm text-slate-500">Preencha os dados para registrar uma nova parada.</p>
        </div>
        <Button as="a" :href="route('machine_downtimes.index')" variant="ghost">Voltar</Button>
      </div>

      <MachineDowntimeForm :form="form" :machines="props.machines" :reasons="props.reasons" :submit-label="'Salvar registro'" :cancel-href="route('machine_downtimes.index')" @submit="submit" />
    </section>
  </AdminLayout>
  
</template>

