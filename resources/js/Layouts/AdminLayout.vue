<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user || null);
const isAdmin = computed(() => user.value?.role === 'admin');
const canViewClients = computed(() => isAdmin.value || !!user.value?.permissions?.clients?.view);
</script>

<template>
  <div class="min-h-screen bg-white text-slate-900">
    <header class="bg-slate-900 text-white">
      <nav class="container-default flex flex-col gap-4 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-6">
          <Link class="text-lg font-semibold tracking-tight text-white transition hover:text-blue-200" href="/admin/dashboard">
            {{ $page.props.app?.name ?? 'Example App' }}
          </Link>

          <div class="flex flex-wrap items-center gap-3 text-sm font-semibold text-slate-200 sm:gap-4">
            <Link class="group transition hover:text-white" href="/admin/dashboard">Dashboard</Link>
            <Link v-if="isAdmin" class="group transition hover:text-white" href="/admin/users">Usuários</Link>
            <Link v-if="canViewClients" class="group transition hover:text-white" href="/admin/clients">Clientes</Link>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 sm:justify-end">
          <template v-if="user">
            <Link class="btn-inverse" href="/admin/profile">Meu perfil</Link>
            <Link class="btn-ghost" href="/admin/logout" method="post" as="button">Sair</Link>
          </template>
        </div>
      </nav>
    </header>

    <main class="container-default py-10">
      <slot />
    </main>

    <footer class="container-default py-8 text-center text-sm text-slate-500">
      &copy; {{ new Date().getFullYear() }} {{ $page.props.app?.name ?? 'Example App' }}. Todos os direitos reservados.
    </footer>
  </div>
</template>

<style scoped>
.container-default { max-width: 72rem; margin: 0 auto; padding-left: 1rem; padding-right: 1rem; }
.btn-inverse { display:inline-flex; align-items:center; gap:.5rem; padding:.5rem .75rem; border-radius:.5rem; background:#fff; color:#0f172a; font-weight:600; }
.btn-ghost { display:inline-flex; align-items:center; gap:.5rem; padding:.5rem .75rem; border-radius:.5rem; border:1px solid #cbd5e1; color:#0f172a; }
</style>

