<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, nextTick, onMounted, watch } from 'vue';
import HeroIcon from '@/components/icons/HeroIcon.vue';
import Modal from '@/components/Modal.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import Button from '@/components/Button.vue';
import InputText from '@/components/InputText.vue';
import InputSelect from '@/components/InputSelect.vue';
import InputNumber from '@/components/InputNumber.vue';
import { useToasts } from '@/components/toast/useToasts';
import Badge from '@/components/Badge.vue';
import { formatCurrency, formatQuantity } from '@/utils/formatters';

const props = defineProps({
  products: { type: Array, required: true },
  clients: { type: Array, required: true },
  addresses: { type: Array, default: () => [] },
  recentOrders: { type: Array, default: () => [] },
});

const productInput = ref('');
const quantityInput = ref(1);
const originalQuantity = ref(0);
// Máscara para campo Quantidade (Adicionar Produto)
// Removido: máscara e estado de edição
// const isEditingProductQuantity = ref(false);
// const productRawQuantityDigits = ref('');

// Sistema de toasts
const { error: toastError, success: toastSuccess } = useToasts();

// Persist quantity on commit from InputNumber
const commitItemQuantity = (index, value) => {
  const item = items.value[index];
  const prev = Number(item.quantity) || 0;
  let next = Number(value);
  if (!isFinite(next) || next <= 0) next = 0.01;
  next = Number(next.toFixed(2));
  if (prev !== next) {
    item.quantity = next;
    toastSuccess(`Quantidade de ${item.name} atualizada para ${formatQuantity(item.quantity)}`);
  }
};
// Removido: formatador manual de quantidade do formulário
const quantityInputRef = ref(null);
const productInputRef = ref(null);
const clientInput = ref('');
const clientInputRef = ref(null);
const paymentMethodRef = ref(null);
const items = ref([]);
const selectedProduct = ref(null);

const productSuggestions = computed(() => {
  if (!productInput.value) return [];
  const query = productInput.value.toLowerCase();
  return props.products.filter(p =>
    p.name.toLowerCase().includes(query) ||
    (p.code && p.code.toLowerCase().includes(query))
  ).slice(0, 10);
});

const clientSuggestions = computed(() => {
  if (!clientInput.value) return [];
  const query = clientInput.value.toLowerCase();
  return props.clients.filter(c =>
    c.name.toLowerCase().includes(query)
  ).slice(0, 10);
});

// Update selectedProduct when input changes
const handleProductInput = () => {
  // Try to find exact match
  const exactMatch = props.products.find(p =>
    p.name.toLowerCase() === productInput.value.toLowerCase() ||
    (p.code && p.code.toLowerCase() === productInput.value.toLowerCase())
  );
  selectedProduct.value = exactMatch || null;
};

const handleClientInput = () => {
  // Try to find exact match
  const exactMatch = props.clients.find(c =>
    c.name.toLowerCase() === clientInput.value.toLowerCase()
  );
  selectedClient.value = exactMatch || null;
};

const clientAddresses = computed(() => {
  if (!selectedClient.value) return [];
  return props.addresses.filter(addr => addr.client_id === selectedClient.value.id);
});

const total = computed(() => {
  return items.value.reduce((sum, item) => sum + Number(item.quantity || 0) * Number(item.unit_price || 0), 0);
});

const totalItemsQuantity = computed(() => {
  return items.value.reduce((sum, item) => sum + Number(item.quantity || 0), 0);
});

const addItem = () => {
  if (!selectedProduct.value || quantityInput.value <= 0) return;

  const price = Number(selectedProduct.value.price);
  const quantity = Number(quantityInput.value);

  const existing = items.value.find(i => i.product_id === selectedProduct.value.id);
  if (existing) {
    existing.quantity += quantity;
    existing.total = existing.quantity * existing.unit_price;
    toastSuccess(`Quantidade de ${selectedProduct.value.name} atualizada para ${existing.quantity}`);
  } else {
    items.value.push({
      product_id: selectedProduct.value.id,
      name: selectedProduct.value.name,
      unit_price: price,
      quantity: quantity,
      total: price * quantity,
    });
    toastSuccess(`${selectedProduct.value.name} adicionado ao pedido`);
  }

  productInput.value = '';
  quantityInput.value = 1;
  selectedProduct.value = null;
  nextTick(() => productInputRef.value.focus());
};

const removeItem = (index) => {
  confirmDelete(items.value[index], index);
};

// No longer needed: totals are computed in template and quantities are updated via v-model

const deleteState = ref({ open: false, processing: false, item: null, index: null });
const confirmDelete = (item, index) => {
  deleteState.value = { open: true, processing: false, item, index };
};
const performDelete = () => {
  if (deleteState.value.index === null) return;
  deleteState.value.processing = true;
  // Simulate async operation
  setTimeout(() => {
    const itemName = deleteState.value.item?.name || 'Item';
    items.value.splice(deleteState.value.index, 1);
    deleteState.value.processing = false;
    deleteState.value.open = false;
    deleteState.value.item = null;
    deleteState.value.index = null;
    toastSuccess(`${itemName} removido do pedido`);
  }, 100);
};

const selectProduct = (product) => {
  selectedProduct.value = product;
  productInput.value = product.name;
  nextTick(() => {
    quantityInputRef.value?.focus();
  });
};

const selectClient = (client) => {
  selectedClient.value = client;
  clientInput.value = client.name;
};

const handleProductKeydown = (e) => {
  if (e.key === 'Enter') {
    e.preventDefault();
    if (selectedProduct.value) {
      // Product already selected, go to quantity
      quantityInputRef.value?.focus();
    } else if (productSuggestions.value.length > 0) {
      // Select the first suggestion and go to quantity
      selectProduct(productSuggestions.value[0]);
      // Close datalist dropdown
      e.target.blur();
      nextTick(() => {
        quantityInputRef.value?.focus();
      });
    } else {
      // No suggestions available, stay in product input
      // Close datalist dropdown but keep focus
      e.target.blur();
      nextTick(() => {
        productInputRef.value?.focus();
      });
    }
  }
};

const handleClientKeydown = (e) => {
  if (e.key === 'Enter') {
    e.preventDefault();
    if (selectedClient.value) {
      // Client already selected, go to payment method
      paymentMethodRef.value?.focus();
    } else if (clientSuggestions.value.length > 0) {
      // Select the first suggestion
      selectClient(clientSuggestions.value[0]);
      // Close datalist dropdown
      e.target.blur();
      nextTick(() => {
        paymentMethodRef.value?.focus();
      });
    } else {
      // No suggestions available, stay in client input
      // Close datalist dropdown but keep focus
      e.target.blur();
      nextTick(() => {
        clientInputRef.value?.focus();
      });
    }
  }
};

const handlePaymentMethodKeydown = (e) => {
  if (e.key === 'Enter') {
    e.preventDefault();
    // Focus no primeiro botão do tipo de entrega
    const deliveryButton = document.querySelector('button[data-delivery-type="pickup"]');
    if (deliveryButton) {
      deliveryButton.focus();
    }
  }
};

// Removido: keydown manual; InputNumber emite @enter

// Modal for customer/payment
const modalOpen = ref(false);
const selectedClient = ref(null);
const paymentMethod = ref('');
const deliveryType = ref('pickup'); // pickup or delivery
const selectedAddress = ref(null);

const openModal = () => {
  modalOpen.value = true;
};

const finalizeOrder = () => {
  // Validação: verificar se há itens no pedido
  if (items.value.length === 0) {
    toastError('Adicione pelo menos um produto ao pedido antes de finalizar.');
    return;
  }

  const data = {
    client_id: selectedClient.value?.id || null,
    items: items.value.map(item => ({
      product_id: item.product_id,
      quantity: item.quantity,
    })),
    payment_method: paymentMethod.value,
    delivery_type: deliveryType.value,
    address_id: deliveryType.value === 'pickup' ? null : selectedAddress.value,
  };

  router.post('/admin/orders', data, {
    onSuccess: () => {
      // Limpar formulário para novo pedido
      items.value = [];
      selectedClient.value = null;
      clientInput.value = '';
      paymentMethod.value = '';
      deliveryType.value = 'pickup';
      selectedAddress.value = null;
      productInput.value = '';
      quantityInput.value = 1;
      selectedProduct.value = null;

      modalOpen.value = false;

      // Focar no campo produto para começar novo pedido
      nextTick(() => {
        productInputRef.value?.focus();
      });
    },
  });
};

onMounted(() => {
  productInputRef.value.focus();

  // Keyboard shortcuts
  document.addEventListener('keydown', (e) => {
    if (e.key === 'F2') {
      e.preventDefault();
      openModal();
    }
  });
});

// Watch for modal opening to focus client input
watch(modalOpen, (isOpen) => {
  if (isOpen) {
    // Wait for modal animation and rendering
    setTimeout(() => {
      clientInputRef.value?.focus();
    }, 150);
  } else {
    // Modal was closed, focus back to product input
    nextTick(() => {
      productInputRef.value?.focus();
    });
  }
});

// Watch for delivery type changes to auto-select first address
watch(deliveryType, (newType) => {
  if (newType === 'delivery' && clientAddresses.value.length > 0) {
    selectedAddress.value = clientAddresses.value[0].id;
  } else if (newType === 'pickup') {
    selectedAddress.value = null;
  }
});

// Watch for client changes to clear selected address
watch(selectedClient, () => {
  selectedAddress.value = null;
  // If delivery is selected and client has addresses, select first one
  if (deliveryType.value === 'delivery' && clientAddresses.value.length > 0) {
    selectedAddress.value = clientAddresses.value[0].id;
  }
});

const getStatusVariant = (status) => {
  const variants = {
    pending: 'warning',
    confirmed: 'info',
    completed: 'success',
    shipped: 'primary',
    delivered: 'success',
    cancelled: 'danger',
  };
  return variants[status] || 'secondary';
};

const getStatusLabel = (status) => {
  const labels = {
    pending: 'Pendente',
    confirmed: 'Confirmado',
    completed: 'Concluído',
    shipped: 'Enviado',
    delivered: 'Entregue',
    cancelled: 'Cancelado',
  };
  return labels[status] || status;
};
</script>

<template>
  <AdminLayout>
    <Head title="Novo pedido" />

    <section class="space-y-8">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl font-semibold text-slate-900 flex items-center gap-2">
            Novo Pedido
          </h1>
          <p class="mt-2 text-sm text-slate-500">Crie um novo pedido adicionando produtos e finalizando com cliente e pagamento.</p>
        </div>
        <Button @click="openModal" variant="primary">
          <HeroIcon name="user-plus" class="h-5 w-5" />
          Finalizar pedido (F2)
        </Button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left: Product input -->
        <div class="lg:col-span-2 space-y-6">
          <div class="card">
            <h2 class="text-xl font-semibold mb-6">Adicionar Produto</h2>

            <div class="space-y-4">
              <label class="form-label">
                Produto
                <InputText
                  ref="productInputRef"
                  v-model="productInput"
                  @input="handleProductInput"
                  @change="handleProductInput"
                  @keydown="handleProductKeydown"
                  type="text"
                  list="products"
                  placeholder="Digite o nome ou código do produto..."
                  size="lg"
                />
                <datalist id="products">
                  <option v-for="product in productSuggestions" :key="product.id" :value="product.name">
                    {{ product.name }} ({{ product.code || 'Sem código' }})
                  </option>
                </datalist>
              </label>

              <div class="grid grid-cols-2 gap-4">
                <label class="form-label">
                  Quantidade
                  <InputNumber
                    ref="quantityInputRef"
                    v-model="quantityInput"
                    :formatted="true"
                    :precision="2"
                    :min="0.01"
                    :step="0.01"
                    size="lg"
                    @enter="addItem"
                  />
                </label>
                <div class="flex items-end">
                  <Button @click="addItem" variant="primary" size="lg" class="w-full">
                    Adicionar (Enter)
                  </Button>
                </div>
              </div>
            </div>
          </div>

          <!-- Items list -->
          <div class="card">
            <h2 class="text-xl font-semibold mb-6">Itens do Pedido</h2>
            <div class="space-y-0">
              <div v-for="(item, index) in items" :key="index" class="flex items-center justify-between p-4" :class="{ 'border-t border-slate-200': index > 0, 'bg-slate-50': index % 2 === 0 }">
                <div class="flex-1">
                  <h3 class="font-medium text-slate-900 mb-3">{{ item.name }}</h3>
                    <div class="flex items-center gap-2 text-sm text-slate-600">
                    <span>{{ formatCurrency(item.unit_price) }}</span>
                    <span>x</span>
                    <InputNumber
                      :data-quantity-index="index"
                      v-model="items[index].quantity"
                      :formatted="true"
                      @focus="() => { originalQuantity = items[index].quantity }"
                      @commit="(val) => commitItemQuantity(index, val)"
                      size="sm"
                      class="w-20"
                      :precision="2"
                      :min="0.01"
                      :step="0.01"
                    />
                  </div>
                </div>
                <div class="flex items-center gap-4">
                  <span class="font-semibold text-slate-900">{{ formatCurrency(item.quantity * item.unit_price) }}</span>
                  <Button @click="removeItem(index)" variant="outline-danger" size="sm">
                    <HeroIcon name="trash" class="h-5 w-5" />
                  </Button>
                </div>
              </div>
              <div v-if="items.length === 0" class="text-center py-8 text-slate-500">
                Nenhum item adicionado ainda.
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Total -->
        <div class="space-y-6">
          <div class="card">
            <h2 class="text-xl font-semibold mb-6">Resumo</h2>
            <div class="space-y-4">
              <div class="flex justify-between items-center">
                <span class="text-slate-600">Total de itens:</span>
                <span class="font-semibold">{{ formatQuantity(totalItemsQuantity) }}</span>
              </div>
              <div class="flex justify-between items-center text-xl font-bold text-slate-900 border-t border-slate-200 pt-4">
                <span>Total:</span>
                <span>{{ formatCurrency(total) }}</span>
              </div>
            </div>
          </div>

          <!-- Recent Orders -->
          <div>
            <div>
              <h2 class="text-xl font-semibold">Últimos Pedidos</h2>
              <p class="mt-2 text-sm text-slate-500">Visualize os pedidos mais recentes criados no sistema.</p>
            </div>
            <div class="space-y-4 mt-6">
              <div v-for="order in recentOrders" :key="order.id" @click="router.visit(`/admin/orders/${order.id}/edit`)" class="p-8 border border-slate-200 rounded-lg bg-white shadow-sm cursor-pointer hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <p class="text-xs text-slate-500 mb-2">Pedido #{{ order.id }}</p>
                    <h3 class="font-medium text-slate-900">{{ order.client.name }}</h3>
                    <p class="text-xs text-slate-600 mt-1">{{ order.ordered_at || order.created_at }}</p>
                  </div>
                  <div class="flex flex-col items-end gap-0.5">
                    <Badge :variant="getStatusVariant(order.status)">
                      {{ getStatusLabel(order.status) }}
                    </Badge>
                    <div class="text-right mt-2">
                      <div class="font-semibold text-slate-900">{{ formatCurrency(order.total) }}</div>
                      <div class="text-xs text-slate-500">{{ order.user.name }}</div>
                    </div>
                  </div>
                </div>
              </div>
              <div v-if="recentOrders.length === 0" class="text-center py-4 text-slate-500">
                Nenhum pedido recente.
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Customer Modal -->
    <Modal v-model="modalOpen" title="Finalizar Pedido" size="md" :lockScroll="true">
      <div class="space-y-4">
        <label class="form-label">
          Cliente
          <InputText
            ref="clientInputRef"
            v-model="clientInput"
            @input="handleClientInput"
            @change="handleClientInput"
            @keydown="handleClientKeydown"
            type="text"
            list="clients"
            placeholder="Digite o nome do cliente..."
          />
          <datalist id="clients">
            <option v-for="client in clientSuggestions" :key="client.id" :value="client.name">
              {{ client.name }}
            </option>
          </datalist>
        </label>

        <label class="form-label">
          Forma de Pagamento
          <InputSelect ref="paymentMethodRef" v-model="paymentMethod" @keydown="handlePaymentMethodKeydown" :options="[
            { value: 'cash', label: 'Dinheiro' },
            { value: 'card', label: 'Cartão' },
            { value: 'pix', label: 'PIX' }
          ]" />
        </label>

        <div>
          <label class="form-label mb-2">Tipo de Entrega</label>
          <div class="flex">
            <Button
              @click="deliveryType = 'pickup'"
              :variant="deliveryType === 'pickup' ? 'primary' : 'outline'"
              type="button"
              data-delivery-type="pickup"
              class="flex-1 rounded-r-none border-r-0 hover:translate-y-0 hover:shadow-none"
            >
              Retirada em balcão
            </Button>
            <Button
              @click="deliveryType = 'delivery'"
              :variant="deliveryType === 'delivery' ? 'primary' : 'outline'"
              type="button"
              data-delivery-type="delivery"
              class="flex-1 rounded-l-none hover:translate-y-0 hover:shadow-none"
            >
              Entrega
            </Button>
          </div>
        </div>

        <div>
          <label class="form-label">
            Endereço de Entrega
            <InputSelect v-model="selectedAddress" :options="clientAddresses.map(address => ({
              value: address.id,
              label: `${address.description} - ${address.address}, ${address.address_number} - ${address.city}/${address.state}`
            }))" :disabled="deliveryType === 'pickup'" />
          </label>
        </div>
      </div>

      <template #footer="{ close }">
        <Button @click="close" variant="outline">Cancelar</Button>
        <Button @click="finalizeOrder" variant="primary">Finalizar Pedido</Button>
      </template>
    </Modal>

    <ConfirmModal v-model="deleteState.open"
                  :processing="deleteState.processing"
                  title="Remover item"
                  :message="deleteState.item ? `Deseja realmente remover ${deleteState.item.name} do pedido?` : ''"
                  confirm-text="Remover"
                  variant="danger"
                  @confirm="performDelete" />
  </AdminLayout>
</template>

<style scoped>
</style>
