<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
const page = usePage();
const name = page.props.auth?.user?.name ?? 'usuário';
</script>

<template>
  <AdminLayout>
    <Head title="Dashboard" />
    <section class="card space-y-6">
      <h1 class="text-2xl font-semibold text-slate-900">Olá, {{ name }}!</h1>
      <p class="text-base leading-relaxed text-slate-600">
        Você está na área autenticada do sistema. Utilize este espaço para adicionar os recursos administrativos que preferir.
      </p>
      <div class="flex flex-wrap gap-3">
        <Link class="btn-primary" href="/">Voltar para a home pública</Link>
      </div>
    </section>
  </AdminLayout>
</template>

<style scoped>
.card { border:1px solid #e2e8f0; background:#fff; border-radius:.75rem; padding:1.25rem; }
.btn-primary { display:inline-flex; align-items:center; gap:.5rem; padding:.5rem .75rem; border-radius:.5rem; background:#2563eb; color:#fff; font-weight:600; }
</style>

