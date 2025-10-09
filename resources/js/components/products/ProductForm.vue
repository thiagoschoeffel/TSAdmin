<script setup>
import Switch from '@/components/ui/Switch.vue';
import Dropdown from '@/components/Dropdown.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import HeroIcon from '@/components/icons/HeroIcon.vue';
import { useToasts } from '@/components/toast/useToasts.js';
import { usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, nextTick } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const isAdmin = computed(() => user.value?.role === 'admin');
const canCreateProducts = computed(() => isAdmin.value || !!user.value?.permissions?.products?.create);
const canUpdateProducts = computed(() => isAdmin.value || !!user.value?.permissions?.products?.update);

const props = defineProps({
  form: { type: Object, required: true },
  products: { type: Array, required: true },
  submitLabel: { type: String, default: 'Salvar' },
  cancelHref: { type: String, required: true },
  isEditing: { type: Boolean, default: false },
  productId: { type: [Number, String, null], default: null },
});

const emit = defineEmits(['submit']);

// Sistema de toasts
const { error: toastError, success: toastSuccess } = useToasts();

// Estado para edição inline de componentes
const editingComponentIndex = ref(-1);
const showAddForm = ref(false);
const newComponent = ref({
  id: '',
  quantity: '',
});
const componentErrors = ref({});

// Estado para confirmação de exclusão de componente
const deleteComponentState = ref({ open: false, processing: false, componentIndex: null });

// Estado para o valor formatado do preço
const priceInput = ref('');

// Variável para armazenar os dígitos digitados
const rawPriceDigits = ref('');

// Método para formatar preço em tempo real
const formatPriceInput = (event) => {
  const input = event.target;
  const value = input.value;

  // Se o valor já está formatado (contém R$), não processa
  if (value.includes('R$')) {
    return;
  }

  // Remove tudo exceto dígitos
  const digits = value.replace(/\D/g, '');

  // Atualiza os dígitos crus
  rawPriceDigits.value = digits;

  if (digits === '') {
    input.value = '';
    props.form.price = '';
    return;
  }

  // Trata como reais (inteiro)
  const numericValue = parseFloat(digits);
  const formatted = numericValue.toLocaleString('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  });

  input.value = formatted;
  props.form.price = numericValue;
};

// Método para inicializar o input de preço
const initializePriceInput = () => {
  if (props.form.price) {
    const numericValue = parseFloat(props.form.price);
    priceInput.value = numericValue.toLocaleString('pt-BR', {
      style: 'currency',
      currency: 'BRL',
    });
    rawPriceDigits.value = numericValue.toString();
  } else {
    priceInput.value = '';
    rawPriceDigits.value = '';
  }
};

// Inicializar quando o componente for montado
onMounted(() => {
  initializePriceInput();
});

// Computed para produtos disponíveis (excluindo o produto atual e componentes já adicionados)
const availableProducts = computed(() => {
  const addedIds = props.form.components.map(c => c.id);
  if (props.productId) {
    addedIds.push(props.productId);
  }
  return props.products.filter(p => !addedIds.includes(p.id));
});

// Computed para verificar se há ciclos
const hasCycle = (componentId, currentProductId = props.productId) => {
  if (!currentProductId) return false;
  const component = props.products.find(p => p.id == componentId);
  if (!component) return false;
  // Verificar se o componente tem o produto atual em seus componentes
  return component.components?.some(c => c.id == currentProductId) || false;
};

// Métodos para gerenciar componentes
const addComponent = () => {
  // Validação dos campos obrigatórios
  const errors = {};

  if (!newComponent.value.id) {
    errors.id = 'Produto é obrigatório';
  }

  if (!newComponent.value.quantity || parseFloat(newComponent.value.quantity) <= 0) {
    errors.quantity = 'Quantidade é obrigatória e deve ser maior que zero';
  }

  // Verificar ciclo
  if (newComponent.value.id && hasCycle(newComponent.value.id)) {
    errors.id = 'Este produto não pode ser adicionado devido a dependências circulares';
  }

  // Se há erros, mostrar toast e não adiciona o componente
  if (Object.keys(errors).length > 0) {
    componentErrors.value = errors;
    toastError('Verifique os campos obrigatórios do componente.');
    return;
  }

  // Limpa erros anteriores
  componentErrors.value = {};

  if (editingComponentIndex.value >= 0) {
    // Salvar edição
    props.form.components[editingComponentIndex.value] = { ...newComponent.value };
    editingComponentIndex.value = -1;
    toastSuccess('Componente atualizado com sucesso!');
  } else {
    // Adicionar novo
    props.form.components.push({ ...newComponent.value, id: parseInt(newComponent.value.id) });
    toastSuccess('Componente adicionado com sucesso!');
  }
  resetNewComponent();
  showAddForm.value = false;
};

const editComponent = (index) => {
  editingComponentIndex.value = index;
  newComponent.value = { ...props.form.components[index] };
  componentErrors.value = {}; // Limpa erros ao editar
  showAddForm.value = true;
};

const cancelEdit = () => {
  editingComponentIndex.value = -1;
  showAddForm.value = false;
  resetNewComponent();
};

const removeComponent = (index) => {
  confirmDeleteComponent(index);
};

const confirmDeleteComponent = (index) => {
  deleteComponentState.value = { open: true, processing: false, componentIndex: index };
};

const performDeleteComponent = () => {
  if (deleteComponentState.value.componentIndex === null) return;

  const index = deleteComponentState.value.componentIndex;
  props.form.components.splice(index, 1);
  if (editingComponentIndex.value === index) {
    cancelEdit();
  }
  toastSuccess('Componente removido com sucesso!');

  deleteComponentState.value = { open: false, processing: false, componentIndex: null };
};

const resetNewComponent = () => {
  newComponent.value = {
    id: '',
    quantity: '',
  };
  componentErrors.value = {}; // Limpa erros ao resetar
};

const onSubmit = () => emit('submit');

// Computed para verificar se há erros nos componentes
const hasComponentErrors = computed(() => {
  if (!props.form.errors) return false;

  return props.form.components?.some((component, index) => {
    const componentErrors = Object.keys(props.form.errors).filter(key => key.startsWith(`components.${index}.`));
    return componentErrors.length > 0;
  });
});
</script>

<template>
  <form @submit.prevent="onSubmit" class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <label class="form-label">
        Nome
        <input type="text" v-model="form.name" required class="form-input" />
        <span v-if="form.errors.name" class="text-sm font-medium text-rose-600">{{ form.errors.name }}</span>
      </label>

      <label class="form-label">
        Preço
        <input type="text" :value="priceInput" @input="formatPriceInput" required class="form-input" placeholder="R$ 0,00" />
        <span v-if="form.errors.price" class="text-sm font-medium text-rose-600">{{ form.errors.price }}</span>
      </label>

      <div class="switch-field sm:col-span-2 lg:col-span-3">
        <span class="switch-label">Status do produto</span>
        <Switch v-model="form.status" true-value="active" false-value="inactive" />
        <span class="switch-status" :class="{ 'inactive': form.status !== 'active' }">
          {{ form.status === 'active' ? 'Ativo' : 'Inativo' }}
        </span>
      </div>
      <span v-if="form.errors.status" class="text-sm font-medium text-rose-600 sm:col-span-2 lg:col-span-3">{{ form.errors.status }}</span>
    </div>

    <label class="form-label">
      Descrição
      <textarea v-model="form.description" rows="4" class="form-textarea" />
      <span v-if="form.errors.description" class="text-sm font-medium text-rose-600">{{ form.errors.description }}</span>
    </label>

    <fieldset class="space-y-3">
      <legend class="text-sm font-semibold text-slate-700">Componentes</legend>

      <!-- Formulário inline para adicionar/editar componente -->
      <div v-if="showAddForm" class="border border-slate-200 rounded-lg p-4 bg-slate-50">
        <div class="grid gap-4 sm:grid-cols-2">
          <label class="form-label">
            Produto
            <select v-model="newComponent.id" required class="form-select">
              <option value="">Selecione um produto</option>
              <option v-for="product in availableProducts" :key="product.id" :value="product.id">
                {{ product.name }} - R$ {{ Number(product.price).toFixed(2) }}
              </option>
            </select>
            <span v-if="componentErrors.id" class="text-sm font-medium text-rose-600">{{ componentErrors.id }}</span>
          </label>
          <label class="form-label">
            Quantidade
            <input type="number" v-model="newComponent.quantity" step="0.01" min="0.01" required class="form-input" />
            <span v-if="componentErrors.quantity" class="text-sm font-medium text-rose-600">{{ componentErrors.quantity }}</span>
          </label>
          <div class="flex items-end gap-2 sm:col-span-2">
            <button type="button" @click="addComponent" class="btn-primary text-sm">
              {{ editingComponentIndex >= 0 ? 'Salvar' : 'Adicionar' }}
            </button>
            <button type="button" @click="cancelEdit" class="btn-ghost text-sm">
              Cancelar
            </button>
          </div>
        </div>
      </div>

      <!-- Tabela de componentes -->
      <div class="table-wrapper">
        <table class="table">
          <thead>
            <tr>
              <th>Produto</th>
              <th>Quantidade</th>
              <th>Preço Unitário</th>
              <th>Total</th>
              <th class="w-24">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(component, index) in form.components" :key="index">
              <td>
                {{ products.find(p => p.id == component.id)?.name || 'Produto não encontrado' }}
              </td>
              <td>{{ component.quantity }}</td>
              <td>R$ {{ Number(products.find(p => p.id == component.id)?.price || 0).toFixed(2) }}</td>
              <td>R$ {{ (Number(component.quantity) * Number(products.find(p => p.id == component.id)?.price || 0)).toFixed(2) }}</td>
              <td class="whitespace-nowrap">
                <Dropdown>
                  <template #trigger="{ toggle }">
                    <button type="button" class="menu-trigger" @click="toggle" aria-label="Abrir menu de ações">
                      <HeroIcon name="ellipsis-horizontal" class="h-5 w-5" />
                    </button>
                  </template>
                  <template #default="{ close }">
                    <button type="button" class="menu-panel-link" @click="editComponent(index); close()">
                      <HeroIcon name="pencil" class="h-4 w-4" />
                      <span>Editar</span>
                    </button>
                    <button type="button" class="menu-panel-link text-rose-600 hover:text-rose-700" @click="removeComponent(index); close()">
                      <HeroIcon name="trash" class="h-4 w-4" />
                      <span>Excluir</span>
                    </button>
                  </template>
                </Dropdown>
              </td>
            </tr>
            <tr v-if="!form.components || form.components.length === 0">
              <td colspan="5" class="table-empty">Nenhum componente adicionado.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Botão para adicionar novo componente -->
      <div v-if="!showAddForm" class="flex justify-center pt-4">
        <button type="button" @click="showAddForm = true" class="btn-ghost text-sm">
          Adicionar componente
        </button>
      </div>

      <span v-if="hasComponentErrors" class="text-sm font-medium text-rose-600">Verifique os erros nos componentes.</span>
    </fieldset>

    <div class="flex flex-wrap gap-3">
      <button type="submit" class="btn-primary" :disabled="form.processing">{{ submitLabel }}</button>
      <a class="btn-ghost" :href="cancelHref">Cancelar</a>
    </div>
  </form>

  <ConfirmModal v-model="deleteComponentState.open"
                :processing="deleteComponentState.processing"
                title="Excluir componente"
                message="Deseja realmente remover este componente?"
                confirm-text="Excluir"
                variant="danger"
                @confirm="performDeleteComponent" />
</template>

<style scoped>
.form-label { display:flex; flex-direction:column; gap:.5rem; font-weight:600; color:#334155 }
.form-input { border:1px solid #cbd5e1; border-radius:.5rem; padding:.5rem .75rem; }
.form-input:disabled { background-color: #f3f4f6; color: #6b7280; cursor: not-allowed; }
.form-input[readonly] { background-color: #e2e8f0; color: #475569; cursor: not-allowed; }
.form-select { border:1px solid #cbd5e1; border-radius:.5rem; padding:.5rem .75rem; }
.form-select:disabled { background-color: #f3f4f6; color: #6b7280; cursor: not-allowed; }
.form-textarea { border:1px solid #cbd5e1; border-radius:.5rem; padding:.5rem .75rem; }
.btn-primary { display:inline-flex; align-items:center; gap:.5rem; padding:.5rem .75rem; border-radius:.5rem; background:#2563eb; color:#fff; font-weight:600; }
.btn-ghost { display:inline-flex; align-items:center; gap:.5rem; padding:.5rem .75rem; border-radius:.5rem; border:1px solid #cbd5e1; color:#0f172a; }
</style>
