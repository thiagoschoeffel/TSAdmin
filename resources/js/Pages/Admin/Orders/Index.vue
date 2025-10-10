<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import HeroIcon from '@/components/icons/HeroIcon.vue';
import Dropdown from '@/components/Dropdown.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import OrderDetailsModal from '@/components/orders/OrderDetailsModal.vue';
import Pagination from '@/components/Pagination.vue';

const props = defineProps({
  orders: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const user = computed(() => page.props.auth?.user || null);
const isAdmin = computed(() => user.value?.role === 'admin');
const canCreate = computed(() => isAdmin.value || !!user.value?.permissions?.orders?.create);
const canUpdate = computed(() => isAdmin.value || !!user.value?.permissions?.orders?.update);
const canDelete = computed(() => isAdmin.value || !!user.value?.permissions?.orders?.delete);
const canView = computed(() => isAdmin.value || !!user.value?.permissions?.orders?.view);

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

const filtering = ref(false);
const submitFilters = () => {
  filtering.value = true;
  router.get('/admin/orders', { search: search.value, status: status.value }, { preserveState: true, replace: true, onFinish: () => filtering.value = false });
};
const resetFilters = () => { search.value = ''; status.value = ''; submitFilters(); };

// Estado para confirmação de exclusão
const deleteState = ref({ open: false, processing: false, order: null });

const confirmDelete = (order) => {
  deleteState.value = { open: true, processing: false, order };
};

const performDelete = async () => {
  if (!deleteState.value.order) return;

  deleteState.value.processing = true;
  try {
    await router.delete(`/admin/orders/${deleteState.value.order.id}`, {
      onSuccess: () => {
        deleteState.value = { open: false, processing: false, order: null };
      },
      onError: () => {
        deleteState.value.processing = false;
      }
    });
  } catch (error) {
    deleteState.value.processing = false;
    console.error('Erro ao excluir pedido:', error);
  }
};

// Estado para modal de detalhes
const details = ref({ open: false, orderId: null });
const openDetails = (order) => { details.value.orderId = order.id; details.value.open = true; };
</script>

<template>
  <AdminLayout>
    <Head title="Pedidos" />

    <section class="card space-y-8">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900 flex items-center gap-2">
            <HeroIcon name="shopping-bag" outline class="h-7 w-7 text-slate-700" />
            Pedidos
          </h1>
          <p class="mt-2 text-sm text-slate-500">Gerencie os pedidos dos clientes.</p>
        </div>
        <Link v-if="canCreate" class="btn-primary" :href="route('orders.create')">Novo pedido</Link>
      </div>

      <form @submit.prevent="submitFilters" class="space-y-4">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <label class="form-label">
            Buscar por cliente
            <input type="text" v-model="search" placeholder="Digite para buscar" class="form-input" />
          </label>
          <label class="form-label">
            Status
            <select v-model="status" class="form-select">
              <option value="">Todos</option>
              <option value="pending">Pendente</option>
              <option value="confirmed">Confirmado</option>
              <option value="shipped">Enviado</option>
              <option value="delivered">Entregue</option>
              <option value="cancelled">Cancelado</option>
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
              <th>ID</th>
              <th>Cliente</th>
              <th>Usuário</th>
              <th>Status</th>
              <th>Total</th>
              <th>Data do pedido</th>
              <th class="w-24 whitespace-nowrap">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="order in orders.data" :key="order.id">
              <td>
                <button v-if="canView" type="button" class="link" @click="openDetails(order)">{{ order.id }}</button>
                <span v-else class="text-slate-900">{{ order.id }}</span>
              </td>
              <td>{{ order.client.name }}</td>
              <td>{{ order.user.name }}</td>
              <td class="table-actions">
                <span :class="getStatusClass(order.status)">
                  {{ getStatusLabel(order.status) }}
                </span>
              </td>
              <td>R$ {{ order.total }}</td>
              <td>{{ order.ordered_at || '-' }}</td>
              <td class="whitespace-nowrap">
                <Dropdown>
                  <template #trigger="{ toggle }">
                    <button type="button" class="menu-trigger" @click="toggle" aria-label="Abrir menu de ações">
                      <HeroIcon name="ellipsis-horizontal" class="h-5 w-5" />
                    </button>
                  </template>
                  <template #default="{ close }">
                    <template v-if="canUpdate || canDelete">
                      <Link v-if="canUpdate" class="menu-panel-link" :href="route('orders.edit', order.id)">
                        <HeroIcon name="pencil" class="h-4 w-4" />
                        <span>Editar</span>
                      </Link>
                      <button v-if="canDelete" type="button" class="menu-panel-link text-rose-600 hover:text-rose-700" @click="confirmDelete(order); close()">
                        <HeroIcon name="trash" class="h-4 w-4" />
                        <span>Excluir</span>
                      </button>
                    </template>
                    <span v-else class="menu-panel-link pointer-events-none text-slate-400">Nenhuma ação disponível</span>
                  </template>
                </Dropdown>
              </td>
            </tr>
            <tr v-if="!orders.data || orders.data.length === 0">
              <td colspan="7" class="table-empty text-center">Nenhum pedido encontrado.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination v-if="orders && orders.total" :paginator="orders" />
    </section>

    <ConfirmModal v-model="deleteState.open"
                  :processing="deleteState.processing"
                  title="Excluir pedido"
                  :message="deleteState.order ? `Deseja realmente remover o pedido #${deleteState.order.id}?` : ''"
                  confirm-text="Excluir"
                  variant="danger"
                  @confirm="performDelete" />

    <OrderDetailsModal v-model="details.open" :order-id="details.orderId" />
  </AdminLayout>
</template>

<script>
export default {
  methods: {
    getStatusClass(status) {
      const classes = {
        pending: 'badge-warning',
        confirmed: 'badge-info',
        completed: 'badge-success',
        shipped: 'badge-primary',
        delivered: 'badge-success',
        cancelled: 'badge-danger',
      };
      return classes[status] || 'badge-secondary';
    },
    getStatusLabel(status) {
      const labels = {
        pending: 'Pendente',
        confirmed: 'Confirmado',
        completed: 'Concluído',
        shipped: 'Enviado',
        delivered: 'Entregue',
        cancelled: 'Cancelado',
      };
      return labels[status] || status;
    },
  },
};
</script>

<style scoped>
.table-wrapper { overflow:auto }
.table { width:100%; border-collapse:separate; border-spacing:0; }
.table th, .table td { padding:.75rem; border-bottom:1px solid #e2e8f0; }
.table thead th { font-size:.875rem; font-weight:700; color:#334155 }
.badge-success { display:inline-flex; align-items:center; gap:0.375rem; border-radius:9999px; background:#ecfdf5; padding:0.25rem 0.75rem; font-size:0.75rem; font-weight:600; color:#047857; }
.badge-danger { display:inline-flex; align-items:center; gap:0.375rem; border-radius:9999px; background:#fef2f2; padding:0.25rem 0.75rem; font-size:0.75rem; font-weight:600; color:#b91c1c; }
.badge-warning { display:inline-flex; align-items:center; gap:0.375rem; border-radius:9999px; background:#fffbeb; padding:0.25rem 0.75rem; font-size:0.75rem; font-weight:600; color:#92400e; }
.badge-info { display:inline-flex; align-items:center; gap:0.375rem; border-radius:9999px; background:#eff6ff; padding:0.25rem 0.75rem; font-size:0.75rem; font-weight:600; color:#1e40af; }
.badge-primary { display:inline-flex; align-items:center; gap:0.375rem; border-radius:9999px; background:#f0f9ff; padding:0.25rem 0.75rem; font-size:0.75rem; font-weight:600; color:#0369a1; }
.badge-primary { display:inline-flex; align-items:center; gap:0.375rem; border-radius:9999px; background:#f0f9ff; padding:0.25rem 0.75rem; font-size:0.75rem; font-weight:600; color:#0369a1; }
</style>
