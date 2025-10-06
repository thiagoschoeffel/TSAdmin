<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ClientForm from '@/components/clients/ClientForm.vue';

const props = defineProps({
  states: { type: Array, required: true },
});

const form = useForm({
  name: '',
  person_type: 'individual',
  document: '',
  observations: '',
  postal_code: '',
  address: '',
  address_number: '',
  address_complement: '',
  neighborhood: '',
  city: '',
  state: '',
  contact_name: '',
  contact_phone_primary: '',
  contact_phone_secondary: '',
  contact_email: '',
  status: 'active',
});

const submit = () => {
  form.post('/admin/clients');
};
</script>

<template>
  <AdminLayout>
    <Head title="Novo cliente" />

    <section class="card space-y-8">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900">Cadastrar cliente</h1>
          <p class="mt-2 text-sm text-slate-500">Preencha os dados para registrar um novo cliente.</p>
        </div>
        <Link class="btn-ghost" href="/admin/clients">Voltar para lista</Link>
      </div>

      <ClientForm :form="form" :states="props.states" :submit-label="'Salvar cliente'" cancel-href="/admin/clients" @submit="submit" />
    </section>
  </AdminLayout>
</template>
