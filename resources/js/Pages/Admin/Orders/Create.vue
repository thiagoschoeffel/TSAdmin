<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, nextTick, onMounted, watch } from 'vue';
import HeroIcon from '@/components/icons/HeroIcon.vue';
import Modal from '@/components/Modal.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import { useToasts } from '@/components/toast/useToasts';

const props = defineProps({
  products: { type: Array, required: true },
  clients: { type: Array, required: true },
  addresses: { type: Array, default: () => [] },
  recentOrders: { type: Array, default: () => [] },
});

const productInput = ref('');
const quantityInput = ref(1);
const editingQuantityIndex = ref(-1);
const rawQuantityDigits = ref('');
const originalQuantity = ref(0);
// Máscara para campo Quantidade (Adicionar Produto)
const isEditingProductQuantity = ref(false);
const productRawQuantityDigits = ref('');

// Sistema de toasts
const { error: toastError, success: toastSuccess } = useToasts();

// Utilitário: formata uma string de dígitos (centavos) para PT-BR com 2 casas
const formatFromDigitString = (digits) => {
  let value = String(digits || '').replace(/\D/g, '');
  if (value === '') return '';
  value = value.slice(0, 8);
  while (value.length < 2) value = '0' + value;
  const cents = value.slice(-2);
  const units = value.slice(0, -2) || '0';
  const numericValue = parseFloat(units + '.' + cents);
  return numericValue.toLocaleString('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

// Método para formatar quantidade em tempo real
const formatQuantityInput = (event) => {
  let value = event.target.value.replace(/\D/g, ''); // Remove tudo exceto dígitos
  if (value === '') {
    rawQuantityDigits.value = '';
    return '';
  }

  // Garante no máximo 8 dígitos (até 99999.99)
  value = value.slice(0, 8);

  // Adiciona zeros à esquerda para garantir pelo menos 2 dígitos (centavos)
  while (value.length < 2) {
    value = '0' + value;
  }

  // Separa centavos
  const cents = value.slice(-2);
  const units = value.slice(0, -2) || '0';
  const numericValue = parseFloat(units + '.' + cents);

  // Formata para BRL sem moeda
  const formatted = numericValue.toLocaleString('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });

  rawQuantityDigits.value = value;
  return formatted;
};

// Método para formatar quantidade sempre (mesmo quando não está editando)
const formatQuantityDisplay = (quantity) => {
  if (quantity === null || quantity === undefined) return '0,00';
  const numericValue = parseFloat(quantity);
  if (isNaN(numericValue)) return '0,00';
  return numericValue.toLocaleString('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

// Método para formatar valores monetários em PT-BR
const formatCurrency = (value) => {
  if (value === null || value === undefined) return 'R$ 0,00';
  const numericValue = parseFloat(value);
  if (isNaN(numericValue)) return 'R$ 0,00';
  return numericValue.toLocaleString('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  });
};

// Método para iniciar edição da quantidade
const startEditingQuantity = (index) => {
  editingQuantityIndex.value = index;
  const item = items.value[index];
  // Converte para centavos e garante pelo menos 2 dígitos
  rawQuantityDigits.value = Math.round(item.quantity * 100).toString().padStart(2, '0');
  originalQuantity.value = item.quantity;
  nextTick(() => {
    const input = document.querySelector(`input[data-quantity-index="${index}"]`);
    if (input) {
      input.focus();
      input.select();
    }
  });
};

// Método para parar edição da quantidade
const stopEditingQuantity = (index) => {
  editingQuantityIndex.value = -1;
  const item = items.value[index];
  // Atualizar o total baseado na quantidade atual
  item.total = item.quantity * item.unit_price;

  // Só mostrar toast se a quantidade foi alterada
  if (item.quantity !== originalQuantity.value) {
    toastSuccess(`Quantidade de ${item.name} atualizada para ${formatQuantityDisplay(item.quantity)}`);
  }
};

// Keydown para edição de quantidade nos itens (setas ±0,01, Enter/Escape)
const handleItemQuantityKeydown = (e, index) => {
  if (e.key === 'Enter' || e.key === 'Escape') {
    e.target.blur();
    return;
  }
  if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
    if (editingQuantityIndex.value === index) {
      e.preventDefault();
      let cents = parseInt(rawQuantityDigits.value || '0', 10);
      if (Number.isNaN(cents)) cents = 0;
      const delta = e.key === 'ArrowUp' ? 1 : -1;
      cents = Math.max(1, cents + delta);
      rawQuantityDigits.value = String(cents);
      const formatted = formatFromDigitString(rawQuantityDigits.value);
      e.target.value = formatted;
      updateItemQuantity(index, formatted);
    }
  }
};
// Início/fim da edição da quantidade do formulário Adicionar Produto
const startEditingProductQuantity = () => {
  isEditingProductQuantity.value = true;
  productRawQuantityDigits.value = Math.round((Number(quantityInput.value) || 0) * 100)
    .toString()
    .padStart(2, '0');
  nextTick(() => {
    quantityInputRef.value?.focus?.();
    quantityInputRef.value?.select?.();
  });
};
const stopEditingProductQuantity = () => {
  isEditingProductQuantity.value = false;
};
// Formatação de entrada para o campo de quantidade do formulário
const formatProductQuantityInput = (event) => {
  let value = String(event.target.value ?? '').replace(/\D/g, '');
  if (value === '') {
    productRawQuantityDigits.value = '';
    return '';
  }
  value = value.slice(0, 8);
  while (value.length < 2) value = '0' + value;
  const formatted = formatFromDigitString(value);
  productRawQuantityDigits.value = value;
  return formatted;
};

// Handler para @input do campo Quantidade (Adicionar Produto)
const onProductQuantityInput = (e) => {
  const formatted = formatProductQuantityInput(e);
  e.target.value = formatted;
  const cleaned = formatted.replace(/\./g, '').replace(',', '.');
  const numeric = parseFloat(cleaned);
  quantityInput.value = isNaN(numeric) || numeric <= 0 ? 0.01 : Math.round(numeric * 100) / 100;
};
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
  return items.value.reduce((sum, item) => sum + Number(item.total || 0), 0);
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

const updateItemQuantity = (index, newQuantity) => {
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
};

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
    toastError(`${itemName} removido do pedido`);
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

const handleQuantityKeydown = (e) => {
  if (e.key === 'Enter') {
    e.preventDefault();
    // Garante que o valor está atualizado antes de adicionar
    const currentValue = e.target.value;
    if (currentValue) {
      const cleaned = currentValue.replace(/\./g, '').replace(',', '.');
      const numeric = parseFloat(cleaned);
      if (!isNaN(numeric) && numeric > 0) {
        quantityInput.value = Math.round(numeric * 100) / 100;
      }
    }
    addItem();
  } else if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
    // Incrementa/decrementa 0,01 para o campo Quantidade (Adicionar Produto)
    e.preventDefault();
    // Base em centavos
    let cents = parseInt(productRawQuantityDigits.value || '0', 10);
    if (Number.isNaN(cents)) cents = 0;
    const delta = e.key === 'ArrowUp' ? 1 : -1;
    cents = Math.max(1, cents + delta);
    productRawQuantityDigits.value = String(cents);
    const formatted = formatFromDigitString(productRawQuantityDigits.value);
    // Atualiza exibição e estado numérico
    if (e.target) e.target.value = formatted;
    const numeric = parseFloat(formatted.replace(/\./g, '').replace(',', '.'));
    quantityInput.value = isNaN(numeric) || numeric <= 0 ? 0.01 : Math.round(numeric * 100) / 100;
  }
};

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
    address_id: deliveryType.value === 'pickup' ? null : (selectedAddress.value?.id || null),
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
    selectedAddress.value = clientAddresses.value[0];
  } else if (newType === 'pickup') {
    selectedAddress.value = null;
  }
});

// Watch for client changes to clear selected address
watch(selectedClient, () => {
  selectedAddress.value = null;
  // If delivery is selected and client has addresses, select first one
  if (deliveryType.value === 'delivery' && clientAddresses.value.length > 0) {
    selectedAddress.value = clientAddresses.value[0];
  }
});

const getStatusClass = (status) => {
  const classes = {
    pending: 'badge-warning',
    confirmed: 'badge-info',
    completed: 'badge-success',
    shipped: 'badge-primary',
    delivered: 'badge-success',
    cancelled: 'badge-danger',
  };
  return classes[status] || 'badge-secondary';
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
        <button @click="openModal" class="btn-primary">
          <HeroIcon name="user-plus" class="h-5 w-5" />
          Finalizar pedido (F2)
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left: Product input -->
        <div class="lg:col-span-2 space-y-6">
          <div class="card">
            <h2 class="text-xl font-semibold mb-6">Adicionar Produto</h2>

            <div class="space-y-4">
              <label class="form-label">
                Produto
                <input
                  ref="productInputRef"
                  v-model="productInput"
                  @input="handleProductInput"
                  @change="handleProductInput"
                  @keydown="handleProductKeydown"
                  type="text"
                  list="products"
                  placeholder="Digite o nome ou código do produto..."
                  class="form-input text-lg py-3"
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
                  <input
                    ref="quantityInputRef"
                    :value="isEditingProductQuantity ? formatFromDigitString(productRawQuantityDigits) : formatQuantityDisplay(quantityInput)"
                    @input="onProductQuantityInput"
                    @focus="startEditingProductQuantity"
                    @blur="stopEditingProductQuantity"
                    @keydown="handleQuantityKeydown"
                    type="text"
                    inputmode="decimal"
                    class="form-input text-lg py-3"
                  />
                </label>
                <div class="flex items-end">
                  <button @click="addItem" class="btn-primary w-full py-3 text-lg">
                    <HeroIcon name="plus" class="h-5 w-5" />
                    Adicionar (Enter)
                  </button>
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
                    <input
                      :data-quantity-index="index"
                      :value="editingQuantityIndex === index ? formatQuantityInput({ target: { value: rawQuantityDigits ?? '' } }) : formatQuantityDisplay(item.quantity)"
                      @input="(e) => {
                        if (editingQuantityIndex === index) {
                          const formatted = formatQuantityInput(e);
                          e.target.value = formatted;
                          updateItemQuantity(index, formatted);
                        }
                      }"
                      @focus="startEditingQuantity(index)"
                      @blur="stopEditingQuantity(index)"
                      @keydown="(e) => handleItemQuantityKeydown(e, index)"
                      type="text"
                      class="form-input w-20 text-center py-2"
                    />
                  </div>
                </div>
                <div class="flex items-center gap-4">
                  <span class="font-semibold text-slate-900">{{ formatCurrency(item.total) }}</span>
                  <button @click="removeItem(index)" class="btn-outline-danger">
                    <HeroIcon name="trash" class="h-5 w-5" />
                  </button>
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
                <span class="font-semibold">{{ formatQuantityDisplay(totalItemsQuantity) }}</span>
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
                    <span :class="getStatusClass(order.status)">
                      {{ getStatusLabel(order.status) }}
                    </span>
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
          <input
            ref="clientInputRef"
            v-model="clientInput"
            @input="handleClientInput"
            @change="handleClientInput"
            @keydown="handleClientKeydown"
            type="text"
            list="clients"
            placeholder="Digite o nome do cliente..."
            class="form-input"
          />
          <datalist id="clients">
            <option v-for="client in clientSuggestions" :key="client.id" :value="client.name">
              {{ client.name }}
            </option>
          </datalist>
        </label>

        <label class="form-label">
          Forma de Pagamento
          <select ref="paymentMethodRef" v-model="paymentMethod" @keydown="handlePaymentMethodKeydown" class="form-select">
            <option value="">Selecionar...</option>
            <option value="cash">Dinheiro</option>
            <option value="card">Cartão</option>
            <option value="pix">PIX</option>
          </select>
        </label>

        <div>
          <label class="form-label mb-2">Tipo de Entrega</label>
          <div class="flex">
            <button
              @click="deliveryType = 'pickup'"
              :class="deliveryType === 'pickup' ? 'btn-primary' : 'btn-outline'"
              type="button"
              data-delivery-type="pickup"
              class="flex-1 rounded-r-none border-r-0 hover:translate-y-0 hover:shadow-none"
            >
              Retirada em balcão
            </button>
            <button
              @click="deliveryType = 'delivery'"
              :class="deliveryType === 'delivery' ? 'btn-primary' : 'btn-outline'"
              type="button"
              data-delivery-type="delivery"
              class="flex-1 rounded-l-none hover:translate-y-0 hover:shadow-none"
            >
              Entrega
            </button>
          </div>
        </div>

        <div>
          <label class="form-label">
            Endereço de Entrega
            <select v-model="selectedAddress" class="form-select" :disabled="deliveryType === 'pickup'">
              <option value="">Selecionar endereço...</option>
              <option v-for="address in clientAddresses" :key="address.id" :value="address">
                {{ address.description }} - {{ address.address }}, {{ address.address_number }} - {{ address.city }}/{{ address.state }}
              </option>
            </select>
          </label>
        </div>
      </div>

      <template #footer="{ close }">
        <button @click="close" class="btn-ghost">Cancelar</button>
        <button @click="finalizeOrder" class="btn-primary">Finalizar Pedido</button>
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
.badge-success { display:inline-flex; align-items:center; gap:0.375rem; border-radius:9999px; background:#ecfdf5; padding:0.25rem 0.75rem; font-size:0.75rem; font-weight:600; color:#047857; }
.badge-danger { display:inline-flex; align-items:center; gap:0.375rem; border-radius:9999px; background:#fef2f2; padding:0.25rem 0.75rem; font-size:0.75rem; font-weight:600; color:#b91c1c; }
.badge-warning { display:inline-flex; align-items:center; gap:0.375rem; border-radius:9999px; background:#fffbeb; padding:0.25rem 0.75rem; font-size:0.75rem; font-weight:600; color:#92400e; }
.badge-info { display:inline-flex; align-items:center; gap:0.375rem; border-radius:9999px; background:#eff6ff; padding:0.25rem 0.75rem; font-size:0.75rem; font-weight:600; color:#1e40af; }
.badge-primary { display:inline-flex; align-items:center; gap:0.375rem; border-radius:9999px; background:#f0f9ff; padding:0.25rem 0.75rem; font-size:0.75rem; font-weight:600; color:#0369a1; }
.badge-secondary { display:inline-flex; align-items:center; gap:0.375rem; border-radius:9999px; background:#f1f5f9; padding:0.25rem 0.75rem; font-size:0.75rem; font-weight:600; color:#475569; }
</style>
