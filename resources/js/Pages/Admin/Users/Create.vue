<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import UserForm from '@/components/users/UserForm.vue';

const props = defineProps({
  resources: { type: Object, required: true },
});

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  status: 'active',
  role: 'user',
  permissions: {},
  modules: {},
});

const submit = () => {
  form.post('/admin/users');
};
</script>

<template>
  <AdminLayout>
    <Head title="Novo usuário" />

    <section class="card space-y-8">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900">Novo usuário</h1>
          <p class="mt-2 text-sm text-slate-500">Preencha os dados para cadastrar um novo membro.</p>
        </div>
  <Link class="btn-ghost" :href="route('users.index')">Voltar para lista</Link>
      </div>

      <UserForm :form="form"
                :resources="props.resources"
                :is-edit="false"
                submit-label="Salvar"
                :cancel-href="route('users.index')"
                @submit="submit" />
    </section>
  </AdminLayout>
</template>
