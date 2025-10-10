<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Dropdown from '@/components/Dropdown.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import HeroIcon from '@/components/icons/HeroIcon.vue';
import ClientDetailsModal from '@/components/clients/ClientDetailsModal.vue';
import Pagination from '@/components/Pagination.vue';
import Badge from '@/components/Badge.vue';

const props = defineProps({
  clients: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const user = computed(() => page.props.auth?.user || null);
const isAdmin = computed(() => user.value?.role === 'admin');
const canCreate = computed(() => isAdmin.value || !!user.value?.permissions?.clients?.create);
const canUpdate = computed(() => isAdmin.value || !!user.value?.permissions?.clients?.update);
const canDelete = computed(() => isAdmin.value || !!user.value?.permissions?.clients?.delete);

const search = ref(props.filters.search || '');
const personType = ref(props.filters.person_type || '');
const status = ref(props.filters.status || '');

const filtering = ref(false);
const submitFilters = () => {
  filtering.value = true;
  router.get('/admin/clients', { search: search.value, person_type: personType.value, status: status.value }, { preserveState: true, replace: true, onFinish: () => filtering.value = false });
};
const resetFilters = () => { search.value = ''; personType.value = ''; status.value = ''; submitFilters(); };

const deleteState = ref({ open: false, processing: false, client: null });
const confirmDelete = (client) => { deleteState.value = { open: true, processing: false, client }; };
const performDelete = async () => {
  if (!deleteState.value.client) return;
  deleteState.value.processing = true;
  try { await router.delete(`/admin/clients/${deleteState.value.client.id}`); }
  finally { deleteState.value.processing = false; deleteState.value.open = false; deleteState.value.client = null; }
};

const details = ref({ open: false, clientId: null });
const openDetails = (client) => { details.value.clientId = client.id; details.value.open = true; };
</script>

<template>
  <AdminLayout>
    <Head title="Clientes" />

    <section class="card space-y-8">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900 flex items-center gap-2">
            <HeroIcon name="identification" outline class="h-7 w-7 text-slate-700" />
            Clientes
          </h1>
          <p class="mt-2 text-sm text-slate-500">Gerencie os cadastros de clientes existentes ou adicione novos registros.</p>
        </div>
  <Link v-if="canCreate" class="btn-primary" :href="route('clients.create')">Novo cliente</Link>
      </div>

      <form @submit.prevent="submitFilters" class="space-y-4">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <label class="form-label">
            Buscar por nome ou documento
            <input type="text" v-model="search" placeholder="Digite para buscar" class="form-input" />
          </label>
          <label class="form-label">
            Tipo de pessoa
            <select v-model="personType" class="form-select">
              <option value="">Todos</option>
              <option value="individual">Pessoa Física</option>
              <option value="company">Pessoa Jurídica</option>
            </select>
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
              <th>Tipo</th>
              <th>Documento</th>
              <th>Status</th>
              <th class="w-24">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="c in clients.data" :key="c.id">
              <td>
                <button v-if="isAdmin || user?.permissions?.clients?.view" type="button" class="link" @click="openDetails(c)">{{ c.name }}</button>
                <span v-else class="text-slate-900">{{ c.name }}</span>
              </td>
              <td>{{ c.person_type === 'company' ? 'Jurídica' : 'Física' }}</td>
              <td>{{ c.formatted_document }}</td>
              <td class="table-actions">
                <Badge :variant="c.status === 'active' ? 'success' : 'danger'">
                  {{ c.status === 'active' ? 'Ativo' : 'Inativo' }}
                </Badge>
              </td>
              <td class="whitespace-nowrap">
                <Dropdown>
                  <template #trigger="{ toggle }">
                    <button type="button" class="menu-trigger" @click="toggle" aria-label="Abrir menu de ações">
                      <HeroIcon name="ellipsis-horizontal" class="h-5 w-5" />
                    </button>
                  </template>
                  <template #default="{ close }">
                    <template v-if="canUpdate || canDelete">
                      <Link v-if="canUpdate" class="menu-panel-link" :href="`/admin/clients/${c.id}/edit`">
                        <HeroIcon name="pencil" class="h-4 w-4" />
                        <span>Editar</span>
                      </Link>
                      <button v-if="canDelete" type="button" class="menu-panel-link text-rose-600 hover:text-rose-700" @click="confirmDelete(c); close()">
                        <HeroIcon name="trash" class="h-4 w-4" />
                        <span>Excluir</span>
                      </button>
                    </template>
                    <span v-else class="menu-panel-link pointer-events-none text-slate-400">Nenhuma ação disponível</span>
                  </template>
                </Dropdown>
              </td>
            </tr>
            <tr v-if="!clients.data || clients.data.length === 0">
              <td colspan="5" class="table-empty text-center">Nenhum cliente cadastrado até o momento.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination v-if="clients && clients.total" :paginator="clients" />
    </section>

    <ClientDetailsModal v-model="details.open" :client-id="details.clientId" />
    <ConfirmModal v-model="deleteState.open"
                  :processing="deleteState.processing"
                  title="Excluir cliente"
                  :message="deleteState.client ? `Deseja realmente remover ${deleteState.client.name}?` : ''"
                  confirm-text="Excluir"
                  variant="danger"
                  @confirm="performDelete" />
  </AdminLayout>
</template>

<style scoped>
.table-wrapper { overflow:auto }
.table { width:100%; border-collapse:separate; border-spacing:0; }
.table th, .table td { padding:.75rem; border-bottom:1px solid #e2e8f0; }
.table thead th { font-size:.875rem; font-weight:700; color:#334155 }
/* Usa estilos globais definidos em resources/css/app.css para menu-trigger e menu-panel-link */
</style>

