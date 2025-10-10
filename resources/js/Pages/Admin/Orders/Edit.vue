<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, nextTick, onMounted, watch } from 'vue';
import axios from 'axios';
import HeroIcon from '@/components/icons/HeroIcon.vue';
import Modal from '@/components/Modal.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import { useToasts } from '@/components/toast/useToasts';
import Badge from '@/components/Badge.vue';
import Button from '@/components/Button.vue';
import InputText from '@/components/InputText.vue';
import InputNumber from '@/components/InputNumber.vue';
import InputSelect from '@/components/InputSelect.vue';
import { formatCurrency, formatQuantity } from '@/utils/formatters';

const props = defineProps({
  order: { type: Object, required: true },
  products: { type: Array, required: true },
  clients: { type: Array, required: true },
  addresses: { type: Array, default: () => [] },
  recentOrders: { type: Array, default: () => [] },
});

const productInput = ref('');
const quantityInput = ref(1);
const originalQuantity = ref(0);

// Sistema de toasts
const { error: toastError, success: toastSuccess } = useToasts();









// Sem modo de edição separado; usamos InputNumber formatado diretamente

// Persistência da quantidade no commit (blur) vindo do InputNumber
const commitItemQuantity = async (index, value) => {
  const item = items.value[index];
  let newQuantity = Number(value);
  if (!isFinite(newQuantity) || newQuantity <= 0) newQuantity = 0.01;
  newQuantity = Math.round(newQuantity * 100) / 100;
  if (Math.abs(newQuantity - originalQuantity.value) > 0.001) {
    // Atualização otimista local; total é derivado reativamente no template
    item.quantity = newQuantity;
    try {
      const response = await axios.patch(`/admin/orders/${props.order.id}/items/${item.id}`, { quantity: item.quantity });
      item.quantity = response.data.item.quantity;
      toastSuccess(`Quantidade de ${item.name} atualizada para ${formatQuantity(item.quantity)}`);
    } catch (error) {
      console.error('Erro ao atualizar quantidade:', error);
      toastError('Erro ao atualizar quantidade do item');
      item.quantity = originalQuantity.value;
    }
  }
};




const quantityInputRef = ref(null);
const productInputRef = ref(null);
const clientInput = ref('');
const clientInputRef = ref(null);
const paymentMethodRef = ref(null);
const items = ref(props.order.items || []);
// Ensure all quantity values are numbers
items.value = items.value.map(item => ({
  ...item,
  quantity: Number(item.quantity) || 0,
  unit_price: Number(item.unit_price) || 0,
  total: Number(item.total) || 0,
}));
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

const addItem = async () => {
  if (!selectedProduct.value || quantityInput.value <= 0) return;

  const price = Number(selectedProduct.value.price);
  const quantity = Number(quantityInput.value);

  try {
    const response = await axios.post(`/admin/orders/${props.order.id}/items`, {
      product_id: selectedProduct.value.id,
      quantity: quantity,
    });

    // Check if item already exists locally
    const existingIndex = items.value.findIndex(i => i.product_id === selectedProduct.value.id);
    if (existingIndex >= 0) {
      // Update existing item
      items.value[existingIndex] = {
        ...response.data.item,
        quantity: Number(response.data.item.quantity) || 0,
        unit_price: Number(response.data.item.unit_price) || 0,
        total: Number(response.data.item.total) || 0,
      };
      toastSuccess(`Quantidade de ${selectedProduct.value.name} atualizada para ${response.data.item.quantity}`);
    } else {
      // Add new item
      const newItem = {
        ...response.data.item,
        quantity: Number(response.data.item.quantity) || 0,
        unit_price: Number(response.data.item.unit_price) || 0,
        total: Number(response.data.item.total) || 0,
      };
      items.value.push(newItem);
      toastSuccess(`${selectedProduct.value.name} adicionado ao pedido`);
    }

    // Update order total
    // total is computed, it will update automatically when items change

    productInput.value = '';
    quantityInput.value = 1;
    selectedProduct.value = null;
    nextTick(() => productInputRef.value.focus());
  } catch (error) {
    console.error('Erro ao adicionar item:', error);
    toastError('Erro ao adicionar item ao pedido');
  }
};

const removeItem = (index) => {
  confirmDelete(items.value[index], index);
};

const updateItemQuantity = async (index, newQuantity) => {
  let numericValue;
  if (typeof newQuantity === 'string') {
    const cleaned = newQuantity
      .toString()
      .trim()
      .replace(/\./g, '')
      .replace(',', '.');
    numericValue = parseFloat(cleaned);
  } else {
    numericValue = Number(newQuantity);
  }

  if (!isFinite(numericValue) || numericValue <= 0) {
    items.value[index].quantity = 0.01;
  } else {
    items.value[index].quantity = Math.round(numericValue * 100) / 100;
  }

  items.value[index].total = items.value[index].quantity * items.value[index].unit_price;

  // Persist to database immediately
  try {
    const response = await axios.patch(`/admin/orders/${props.order.id}/items/${items.value[index].id}`, {
      quantity: items.value[index].quantity,
    });

    // Update local data with server response
    items.value[index].quantity = response.data.item.quantity;
    items.value[index].total = response.data.item.total;
    // Update order total - computed will update automatically

    // Só mostrar toast se a quantidade foi alterada
    if (items.value[index].quantity !== originalQuantity.value) {
      toastSuccess(`Quantidade de ${items.value[index].name} atualizada para ${formatQuantity(items.value[index].quantity)}`);
    }
  } catch (error) {
    console.error('Erro ao atualizar quantidade:', error);
    toastError('Erro ao atualizar quantidade do item');
    // Revert local changes on error
    items.value[index].quantity = originalQuantity.value;
    items.value[index].total = items.value[index].quantity * items.value[index].unit_price;
  }
};

const deleteState = ref({ open: false, processing: false, item: null, index: null });
const confirmDelete = (item, index) => {
  deleteState.value = { open: true, processing: false, item, index };
};
const performDelete = async () => {
  if (deleteState.value.index === null) return;

  deleteState.value.processing = true;
  try {
    const response = await axios.delete(`/admin/orders/${props.order.id}/items/${deleteState.value.item.id}`);

    // Remove from local array
    items.value.splice(deleteState.value.index, 1);
    // Update order total
    // total is computed, it will update automatically when items change

    deleteState.value.processing = false;
    deleteState.value.open = false;
    deleteState.value.item = null;
    deleteState.value.index = null;
    toastSuccess(`${deleteState.value.item?.name || 'Item'} removido do pedido`);
  } catch (error) {
    console.error('Erro ao remover item:', error);
    toastError('Erro ao remover item do pedido');
    deleteState.value.processing = false;
  }
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



// Modal for customer/payment
const modalOpen = ref(false);
const selectedClient = ref(null);
const paymentMethod = ref('');
const deliveryType = ref('pickup'); // pickup or delivery
const selectedAddressId = ref(null);
const orderStatus = ref('pending');

const openModal = () => {
  // Initialize modal values with current order data
  const client = props.clients.find(c => c.id === props.order.client_id);
  selectedClient.value = client ? { id: client.id, name: client.name } : null;
  clientInput.value = selectedClient.value?.name || '';

  orderStatus.value = props.order.status || 'pending';
  paymentMethod.value = props.order.payment_method || '';
  deliveryType.value = props.order.delivery_type || 'pickup';

  // Set the address ID directly
  selectedAddressId.value = props.order.address_id || null;

  modalOpen.value = true;
};

const saveOrder = () => {
  // Validação: verificar se há itens no pedido
  if (items.value.length === 0) {
    toastError('Adicione pelo menos um produto ao pedido antes de salvar.');
    return;
  }

  const data = {
    client_id: selectedClient.value?.id || null,
    status: orderStatus.value,
    payment_method: paymentMethod.value,
    delivery_type: deliveryType.value,
    address_id: deliveryType.value === 'pickup' ? null : selectedAddressId.value,
    // Items are already managed individually, no need to send them
  };

  router.put(`/admin/orders/${props.order.id}`, data, {
    onSuccess: () => {
      modalOpen.value = false;
    },
  });
};

const goToNewOrder = () => {
  router.visit('/admin/orders/create');
};

onMounted(() => {
  productInputRef.value.focus();

  // Keyboard shortcuts
  document.addEventListener('keydown', (e) => {
    if (e.key === 'F2') {
      e.preventDefault();
      openModal();
    }
    if (e.key === 'F3') {
      e.preventDefault();
      goToNewOrder();
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
  if (newType === 'delivery' && clientAddresses.value.length > 0 && !selectedAddressId.value) {
    selectedAddressId.value = clientAddresses.value[0].id;
  } else if (newType === 'pickup') {
    selectedAddressId.value = null;
  }
});

// Watch for client changes to clear selected address
watch(selectedClient, () => {
  selectedAddressId.value = null;
  // If delivery is selected and client has addresses, select first one
  if (deliveryType.value === 'delivery' && clientAddresses.value.length > 0) {
    selectedAddressId.value = clientAddresses.value[0].id;
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
    <Head title="Editar pedido" />

    <section class="space-y-8">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl font-semibold text-slate-900 flex items-center gap-2">
            Editar Pedido #{{ order.id }}
          </h1>
          <p class="mt-2 text-sm text-slate-500">Edite os itens do pedido e finalize com cliente e pagamento.</p>
        </div>
        <div class="flex gap-3">
          <Button @click="openModal" variant="primary">
            <HeroIcon name="user-plus" class="h-5 w-5" />
            Salvar alterações (F2)
          </Button>
          <Button @click="goToNewOrder" variant="outline">
            <HeroIcon name="plus" class="h-5 w-5" />
            Novo pedido (F3)
          </Button>
        </div>
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
    <Modal v-model="modalOpen" title="Salvar Alterações" size="md" :lockScroll="true">
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
          Status do Pedido
          <InputSelect v-model="orderStatus" :options="[
            { value: 'pending', label: 'Pendente' },
            { value: 'confirmed', label: 'Confirmado' },
            { value: 'shipped', label: 'Enviado' },
            { value: 'delivered', label: 'Entregue' },
            { value: 'cancelled', label: 'Cancelado' }
          ]" />
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
            <InputSelect v-model="selectedAddressId" :options="clientAddresses.map(address => ({
              value: address.id,
              label: `${address.description} - ${address.address}, ${address.address_number} - ${address.city}/${address.state}`
            }))" :disabled="deliveryType === 'pickup'" />
          </label>
        </div>
      </div>

      <template #footer="{ close }">
        <Button @click="close" variant="outline">Cancelar</Button>
        <Button @click="saveOrder" variant="primary">Salvar Alterações</Button>
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
