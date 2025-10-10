<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/components/Button.vue';
import InputText from '@/components/InputText.vue';
import InputSelect from '@/components/InputSelect.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Dropdown from '@/components/Dropdown.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import HeroIcon from '@/components/icons/HeroIcon.vue';
import UserDetailsModal from '@/components/users/UserDetailsModal.vue';
import Pagination from '@/components/Pagination.vue';
import Badge from '@/components/Badge.vue';

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
          <h1 class="text-2xl font-semibold text-slate-900 flex items-center gap-2">
            <HeroIcon name="users" outline class="h-7 w-7 text-slate-700" />
            Usuários
          </h1>
          <p class="mt-2 text-sm text-slate-500">Gerencie os usuários do sistema ou cadastre novos membros.</p>
        </div>
        <Button variant="primary" :href="route('users.create')">Novo usuário</Button>
      </div>

      <form @submit.prevent="submitFilters" class="space-y-4">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <label class="form-label">
            Buscar por nome ou e-mail
            <InputText
              v-model="search"
              type="text"
              placeholder="Digite para buscar"
            />
          </label>
          <label class="form-label">
            Status
            <InputSelect
              v-model="status"
              :options="[
                { value: '', label: 'Todos' },
                { value: 'active', label: 'Ativos' },
                { value: 'inactive', label: 'Inativos' }
              ]"
            />
          </label>
        </div>
        <div class="flex flex-wrap gap-3">
          <Button variant="primary" :loading="filtering" type="submit">
            <HeroIcon name="funnel" class="h-5 w-5" />
            <span v-if="!filtering">Filtrar</span>
            <span v-else>Filtrando…</span>
          </Button>
          <Button variant="ghost" @click="resetFilters">Limpar filtros</Button>
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
              <td>
                {{ u.email }}
                <Badge :variant="u.email_verified_at ? 'success' : 'danger'" class="ml-2">
                  {{ u.email_verified_at ? 'Verificado' : 'Não verificado' }}
                </Badge>
              </td>
              <td>{{ u.role === 'admin' ? 'Administrador' : 'Usuário comum' }}</td>
              <td class="table-actions">
                <Badge :variant="u.status === 'active' ? 'success' : 'danger'">
                  {{ u.status === 'active' ? 'Ativo' : 'Inativo' }}
                </Badge>
              </td>
              <td class="whitespace-nowrap">
                <Dropdown>
                  <template #trigger="{ toggle }">
                    <Button variant="ghost" size="sm" @click="toggle" aria-label="Abrir menu de ações">
                      <HeroIcon name="ellipsis-horizontal" class="h-5 w-5" />
                    </Button>
                  </template>
                  <template #default="{ close }">
                    <template v-if="u.id !== meId">
                      <Link class="menu-panel-link" :href="`/admin/users/${u.id}/edit`">
                        <HeroIcon name="pencil" class="h-4 w-4" />
                        <span>Editar</span>
                      </Link>
                      <button type="button" class="menu-panel-link text-rose-600 hover:text-rose-700" @click="confirmDelete(u); close()">
                        <HeroIcon name="trash" class="h-4 w-4" />
                        <span>Excluir</span>
                      </button>
                    </template>
                    <template v-else>
                      <Link class="menu-panel-link" :href="route('profile.edit')">
                        <HeroIcon name="user-circle" class="h-4 w-4" />
                        <span>Meu perfil</span>
                      </Link>
                    </template>
                  </template>
                </Dropdown>
              </td>
            </tr>
            <tr v-if="!users.data || users.data.length === 0">
              <td colspan="5" class="table-empty text-center">Nenhum usuário encontrado.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination v-if="users && users.total" :paginator="users" />
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
