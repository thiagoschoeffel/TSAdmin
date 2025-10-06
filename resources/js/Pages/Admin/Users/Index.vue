<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Dropdown from '@/components/Dropdown.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import HeroIcon from '@/components/icons/HeroIcon.vue';
import UserDetailsModal from '@/components/users/UserDetailsModal.vue';

const props = defineProps({
  users: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const meId = computed(() => page.props.auth?.user?.id);

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

const filtering = ref(false);
const submitFilters = () => {
  filtering.value = true;
  router.get('/admin/users', { search: search.value, status: status.value }, { preserveState: true, replace: true, onFinish: () => filtering.value = false });
};

const resetFilters = () => {
  search.value = '';
  status.value = '';
  submitFilters();
};

const deleteState = ref({ open: false, processing: false, user: null });
const confirmDelete = (user) => { deleteState.value = { open: true, processing: false, user }; };
const performDelete = async () => {
  if (!deleteState.value.user) return;
  deleteState.value.processing = true;
  try {
    await router.delete(`/admin/users/${deleteState.value.user.id}`);
  } finally {
    deleteState.value.processing = false;
    deleteState.value.open = false;
    deleteState.value.user = null;
  }
};

const details = ref({ open: false, userId: null });
const openDetails = (user) => {
  details.value.userId = user.id;
  details.value.open = true;
};
</script>

<template>
  <AdminLayout>
    <Head title="Usuários" />

    <section class="card space-y-8">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900">Usuários</h1>
          <p class="mt-2 text-sm text-slate-500">Gerencie os usuários do sistema ou cadastre novos membros.</p>
        </div>
        <Link class="btn-primary" href="/admin/users/create">Novo usuário</Link>
      </div>

      <form @submit.prevent="submitFilters" class="space-y-4">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <label class="form-label">
            Buscar por nome ou e-mail
            <input type="text" v-model="search" placeholder="Digite para buscar" class="form-input" />
          </label>
          <label class="form-label">
            Status
            <select v-model="status" class="form-select">
              <option value="">Todos</option>
              <option value="active">Ativos</option>
              <option value="inactive">Inativos</option>
            </select>
          </label>
        </div>
        <div class="flex flex-wrap gap-3">
          <button type="submit" class="btn-primary" :disabled="filtering">
            <HeroIcon name="funnel" class="h-5 w-5" />
            <span v-if="!filtering">Filtrar</span>
            <span v-else>Filtrando…</span>
          </button>
          <button type="button" class="btn-ghost" @click="resetFilters">Limpar filtros</button>
        </div>
      </form>

      

      <div class="table-wrapper">
        <table class="table">
          <thead>
            <tr>
              <th>Nome</th>
              <th>E-mail</th>
              <th>Perfil</th>
              <th>Status</th>
              <th class="w-24 whitespace-nowrap">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="u in users.data" :key="u.id">
              <td>
                <button type="button" class="link" @click="openDetails(u)">{{ u.name }}</button>
              </td>
              <td>{{ u.email }}</td>
              <td>{{ u.role === 'admin' ? 'Administrador' : 'Usuário comum' }}</td>
              <td class="table-actions">
                <span :class="u.status === 'active' ? 'badge-success' : 'badge-danger'">
                  {{ u.status === 'active' ? 'Ativo' : 'Inativo' }}
                </span>
              </td>
              <td class="whitespace-nowrap">
                <Dropdown>
                  <template #trigger="{ toggle }">
                    <button type="button" class="menu-trigger" @click="toggle" aria-label="Abrir menu de ações">
                      <HeroIcon name="ellipsis-horizontal" class="h-5 w-5" />
                    </button>
                  </template>
                  <template v-if="u.id !== meId">
                    <Link class="menu-panel-link" :href="`/admin/users/${u.id}/edit`">
                      <HeroIcon name="pencil" class="h-4 w-4" />
                      <span>Editar</span>
                    </Link>
                    <button type="button" class="menu-panel-link text-rose-600 hover:text-rose-700" @click="confirmDelete(u)">
                      <HeroIcon name="trash" class="h-4 w-4" />
                      <span>Excluir</span>
                    </button>
                  </template>
                  <template v-else>
                    <Link class="menu-panel-link" href="/admin/profile">
                      <HeroIcon name="user-circle" class="h-4 w-4" />
                      <span>Meu perfil</span>
                    </Link>
                  </template>
                </Dropdown>
              </td>
            </tr>
            <tr v-if="!users.data || users.data.length === 0">
              <td colspan="5" class="table-empty">Nenhum usuário encontrado.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <nav v-if="users.links && users.links.length" class="mt-6 flex flex-wrap gap-2">
        <template v-for="link in users.links" :key="link.url + link.label">
          <span v-if="!link.url" class="px-3 py-2 text-sm text-slate-400" v-html="link.label" />
          <Link v-else :href="link.url" class="px-3 py-2 text-sm font-semibold text-slate-600 bg-white hover:text-slate-800 hover:bg-slate-100 rounded transition" :class="{ 'text-blue-700 border border-blue-200': link.active }" v-html="link.label" preserve-scroll />
        </template>
      </nav>
    </section>

    <ConfirmModal v-model="deleteState.open"
                  :processing="deleteState.processing"
                  :title="`Excluir usuário`"
                  :message="deleteState.user ? `Tem certeza que deseja remover ${deleteState.user.name}?` : ''"
                  confirm-text="Excluir"
                  variant="danger"
                  @confirm="performDelete" />
  </AdminLayout>

  <UserDetailsModal v-model="details.open" :user-id="details.userId" />
</template>

<style scoped>
.table-wrapper { overflow:auto }
.table { width:100%; border-collapse:separate; border-spacing:0; }
.table th, .table td { padding:.75rem; border-bottom:1px solid #e2e8f0; text-align:left; }
.table thead th { font-size:.875rem; font-weight:700; color:#334155 }
.table-empty { text-align:center; color:#64748b; padding:1rem }
.badge-success { display:inline-flex; align-items:center; gap:.375rem; background:#ecfeff; color:#047857; font-weight:700; padding:.125rem .5rem; border-radius:.375rem; }
.badge-danger { display:inline-flex; align-items:center; gap:.375rem; background:#fff1f2; color:#b91c1c; font-weight:700; padding:.125rem .5rem; border-radius:.375rem; }
/* Usa estilos globais definidos em resources/css/app.css para menu-trigger e menu-panel-link */
</style>
