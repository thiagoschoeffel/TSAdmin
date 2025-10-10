<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import HeroIcon from '@/components/icons/HeroIcon.vue';
import Dropdown from '@/components/Dropdown.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import Pagination from '@/components/Pagination.vue';
import ProductDetailsModal from '@/components/products/ProductDetailsModal.vue';
import Badge from '@/components/Badge.vue';

const props = defineProps({
  products: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const user = computed(() => page.props.auth?.user || null);
const isAdmin = computed(() => user.value?.role === 'admin');
const canCreate = computed(() => isAdmin.value || !!user.value?.permissions?.products?.create);
const canUpdate = computed(() => isAdmin.value || !!user.value?.permissions?.products?.update);
const canDelete = computed(() => isAdmin.value || !!user.value?.permissions?.products?.delete);
const canView = computed(() => isAdmin.value || !!user.value?.permissions?.products?.view);

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

const filtering = ref(false);
const submitFilters = () => {
  filtering.value = true;
  router.get('/admin/products', { search: search.value, status: status.value }, { preserveState: true, replace: true, onFinish: () => filtering.value = false });
};
const resetFilters = () => { search.value = ''; status.value = ''; submitFilters(); };

// Estado para confirmação de exclusão
const deleteState = ref({ open: false, processing: false, product: null });

const confirmDelete = (product) => {
  deleteState.value = { open: true, processing: false, product };
};

const performDelete = async () => {
  if (!deleteState.value.product) return;

  deleteState.value.processing = true;
  try {
    await router.delete(`/admin/products/${deleteState.value.product.id}`, {
      onSuccess: () => {
        deleteState.value = { open: false, processing: false, product: null };
      },
      onError: () => {
        deleteState.value.processing = false;
      }
    });
  } catch (error) {
    deleteState.value.processing = false;
    console.error('Erro ao excluir produto:', error);
  }
};

// Estado para modal de detalhes
const details = ref({ open: false, productId: null });
const openDetails = (product) => { details.value.productId = product.id; details.value.open = true; };
</script>

<template>
  <AdminLayout>
    <Head title="Produtos" />
    <section class="card space-y-8">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900 flex items-center gap-2">
            <HeroIcon name="cube" outline class="h-7 w-7 text-slate-700" />
            Produtos
          </h1>
          <p class="mt-2 text-sm text-slate-500">Gerencie os produtos cadastrados e suas composições.</p>
        </div>
        <Link v-if="canCreate" class="btn-primary" :href="route('products.create')">Novo produto</Link>
      </div>

      <form @submit.prevent="submitFilters" class="space-y-4">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <label class="form-label">
            Buscar por nome ou descrição
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
              <th>Descrição</th>
              <th>Preço</th>
              <th>Status</th>
              <th class="w-24 whitespace-nowrap">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in products.data" :key="p.id">
              <td>
                <button v-if="canView" type="button" class="link" @click="openDetails(p)">{{ p.name }}</button>
                <span v-else class="text-slate-900">{{ p.name }}</span>
              </td>
              <td>{{ p.description }}</td>
              <td>R$ {{ Number(p.price).toFixed(2) }}</td>
              <td class="table-actions">
                <Badge :variant="p.status === 'active' ? 'success' : 'danger'">
                  {{ p.status === 'active' ? 'Ativo' : 'Inativo' }}
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
                      <Link v-if="canUpdate" class="menu-panel-link" :href="route('products.edit', p.id)">
                        <HeroIcon name="pencil" class="h-4 w-4" />
                        <span>Editar</span>
                      </Link>
                      <button v-if="canDelete" type="button" class="menu-panel-link text-rose-600 hover:text-rose-700" @click="confirmDelete(p); close()">
                        <HeroIcon name="trash" class="h-4 w-4" />
                        <span>Excluir</span>
                      </button>
                    </template>
                    <span v-else class="menu-panel-link pointer-events-none text-slate-400">Nenhuma ação disponível</span>
                  </template>
                </Dropdown>
              </td>
            </tr>
            <tr v-if="!products.data || products.data.length === 0">
              <td colspan="5" class="table-empty text-center">Nenhum produto encontrado.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination v-if="products && products.total" :paginator="products" />
    </section>

    <ConfirmModal v-model="deleteState.open"
                  :processing="deleteState.processing"
                  title="Excluir produto"
                  :message="deleteState.product ? `Deseja realmente remover ${deleteState.product.name}?` : ''"
                  confirm-text="Excluir"
                  variant="danger"
                  @confirm="performDelete" />

    <ProductDetailsModal v-model="details.open" :product-id="details.productId" />
  </AdminLayout>
</template>

<style scoped>
.table-wrapper { overflow:auto }
.table { width:100%; border-collapse:separate; border-spacing:0; }
.table th, .table td { padding:.75rem; border-bottom:1px solid #e2e8f0; }
.table thead th { font-size:.875rem; font-weight:700; color:#334155 }
</style>
