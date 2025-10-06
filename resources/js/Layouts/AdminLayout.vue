<script setup>
import { computed, ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { route } from '@/ziggy-client';
import Dropdown from '@/components/Dropdown.vue';
import HeroIcon from '@/components/icons/HeroIcon.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import { router } from '@inertiajs/vue3';
import ToastContainer from '@/components/toast/ToastContainer.vue';
import { useToasts } from '@/components/toast/useToasts';

const page = usePage();
const user = computed(() => page.props.auth?.user || null);
const isAdmin = computed(() => user.value?.role === 'admin');
const canViewClients = computed(() => isAdmin.value || !!user.value?.permissions?.clients?.view);

// Logout modal state and action
const logoutOpen = ref(false);
const isLoggingOut = ref(false);
const doLogout = async () => {
  if (isLoggingOut.value) return;
  isLoggingOut.value = true;
  try {
    await router.post('/admin/logout');
  } finally {
    isLoggingOut.value = false;
    logoutOpen.value = false;
  }
};

const { success, error } = useToasts();
let lastFlash = '';
watch(() => page.props.flash, (f) => {
  const key = JSON.stringify(f||{});
  if (key === lastFlash) return;
  lastFlash = key;
  if (!f) return;
  if (f.status) success(f.status);
  if (f.success) success(f.success);
  if (f.error) error(f.error);
}, { deep: true, immediate: true });
</script>

<template>
  <div class="min-h-screen bg-white text-slate-900">
    <header class="bg-slate-900 text-white">
      <nav class="container-default flex flex-col gap-4 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-6">
          <Link class="text-lg font-semibold tracking-tight text-white transition hover:text-blue-200" :href="route('dashboard')">
            {{ $page.props.app?.name ?? 'Example App' }}
          </Link>

          <div class="flex flex-wrap items-center gap-3 text-sm font-semibold text-slate-200 sm:gap-4">
            <Link class="group transition hover:text-white" :href="route('dashboard')">
              <span class="inline-flex items-center gap-2">
                <HeroIcon name="chart-bar" class="h-4 w-4 transition-colors group-hover:text-white" />
                <span>Dashboard</span>
              </span>
            </Link>
            <Link v-if="isAdmin" class="group transition hover:text-white" :href="route('users.index')">
              <span class="inline-flex items-center gap-2">
                <HeroIcon name="users" class="h-4 w-4 transition-colors group-hover:text-white" />
                <span>Usuários</span>
              </span>
            </Link>
            <Link v-if="canViewClients" class="group transition hover:text-white" :href="route('clients.index')">
              <span class="inline-flex items-center gap-2">
                <HeroIcon name="identification" class="h-4 w-4 transition-colors group-hover:text-white" />
                <span>Clientes</span>
              </span>
            </Link>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 sm:justify-end">
          <template v-if="user">
            <Dropdown panelClass="user-dropdown" openClass="is-open">
              <template #trigger="{ toggle }">
                <button type="button" class="user-toggle" @click="toggle">
                  {{ (user?.name || 'U').toString().trim().charAt(0).toUpperCase() }}
                </button>
              </template>
              <template #default>
                <Link class="dropdown-link" :href="route('profile.edit')">
                  <HeroIcon name="user-circle" class="h-5 w-5" />
                  <span>Meu perfil</span>
                </Link>
                <button type="button" class="dropdown-link-danger" @click="logoutOpen = true">
                  <HeroIcon name="arrow-left-end-on-rectangle" class="h-5 w-5" />
                  <span>Sair</span>
                </button>
              </template>
            </Dropdown>
            <ConfirmModal v-model="logoutOpen"
                          title="Encerrar sessão"
                          message="Tem certeza que deseja sair da sua conta?"
                          confirm-text="Sair"
                          :processing="isLoggingOut"
                          @confirm="doLogout"/>
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
  <ToastContainer />
</template>

<style scoped>
.container-default { max-width: 72rem; margin: 0 auto; padding-left: 1rem; padding-right: 1rem; }
.btn-inverse { display:inline-flex; align-items:center; gap:.5rem; padding:.5rem .75rem; border-radius:.5rem; background:#fff; color:#0f172a; font-weight:600; }
.btn-ghost { display:inline-flex; align-items:center; gap:.5rem; padding:.5rem .75rem; border-radius:.5rem; border:1px solid #cbd5e1; color:#0f172a; }
</style>
