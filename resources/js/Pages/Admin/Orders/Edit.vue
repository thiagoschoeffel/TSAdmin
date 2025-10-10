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

const props = defineProps({
  order: { type: Object, required: true },
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

  // Formata para PT-BR sem moeda
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
const stopEditingQuantity = async (index) => {
  const item = items.value[index];
  const currentQuantity = item.quantity;

  // Parse the formatted value from rawQuantityDigits
  let newQuantity;
  if (rawQuantityDigits.value && rawQuantityDigits.value.trim() !== '') {
    const cents = parseInt(rawQuantityDigits.value, 10);
    if (!isNaN(cents) && cents > 0) {
      newQuantity = cents / 100;
    } else {
      newQuantity = currentQuantity;
    }
  } else {
    newQuantity = currentQuantity;
  }

  // Ensure newQuantity is valid
  if (!isFinite(newQuantity) || newQuantity <= 0) {
    newQuantity = 0.01;
  }

  // Round to 2 decimal places
  newQuantity = Math.round(newQuantity * 100) / 100;

  // Only update if quantity actually changed
  if (Math.abs(newQuantity - originalQuantity.value) > 0.001) { // Use small epsilon for floating point comparison
    editingQuantityIndex.value = -1;

    // Update local item
    item.quantity = newQuantity;
    item.total = item.quantity * item.unit_price;

    // Persist to database
    try {
      const response = await axios.patch(`/admin/orders/${props.order.id}/items/${item.id}`, {
        quantity: item.quantity,
      });

      // Update local data with server response
      item.quantity = response.data.item.quantity;
      item.total = response.data.item.total;

      toastSuccess(`Quantidade de ${item.name} atualizada para ${formatQuantityDisplay(item.quantity)}`);
    } catch (error) {
      console.error('Erro ao atualizar quantidade:', error);
      toastError('Erro ao atualizar quantidade do item');
      // Revert local changes on error
      item.quantity = originalQuantity.value;
      item.total = item.quantity * item.unit_price;
    }
  } else {
    editingQuantityIndex.value = -1;
  }
};

// Keydown para edição de quantidade nos itens (setas ±0,01, Enter/Escape)
const handleItemQuantityKeydown = (e, index) => {
  if (e.key === 'Enter') {
    e.preventDefault();
    e.target.blur(); // This will trigger stopEditingQuantity
    return;
  }
  if (e.key === 'Escape') {
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
  return items.value.reduce((sum, item) => sum + Number(item.total || 0), 0);
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
      toastSuccess(`Quantidade de ${items.value[index].name} atualizada para ${formatQuantityDisplay(items.value[index].quantity)}`);
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
                  <Button @click="addItem" variant="primary" size="lg" class="w-full">
                    <HeroIcon name="plus" class="h-5 w-5" />
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
                    <input
                      :data-quantity-index="index"
                      :value="editingQuantityIndex === index ? formatQuantityInput({ target: { value: rawQuantityDigits ?? '' } }) : formatQuantityDisplay(item.quantity)"
                      @input="(e) => {
                        if (editingQuantityIndex === index) {
                          const formatted = formatQuantityInput(e);
                          e.target.value = formatted;
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
          Status do Pedido
          <select v-model="orderStatus" class="form-select">
            <option value="pending">Pendente</option>
            <option value="confirmed">Confirmado</option>
            <option value="shipped">Enviado</option>
            <option value="delivered">Entregue</option>
            <option value="cancelled">Cancelado</option>
          </select>
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
            <select v-model="selectedAddressId" class="form-select" :disabled="deliveryType === 'pickup'">
              <option value="">Selecionar endereço...</option>
              <option v-for="address in clientAddresses" :key="address.id" :value="address.id">
                {{ address.description }} - {{ address.address }}, {{ address.address_number }} - {{ address.city }}/{{ address.state }}
              </option>
            </select>
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
