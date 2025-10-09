<script setup>
import Switch from '@/components/ui/Switch.vue';
import Dropdown from '@/components/Dropdown.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import HeroIcon from '@/components/icons/HeroIcon.vue';
import { useToasts } from '@/components/toast/useToasts.js';
import { ref, computed, nextTick } from 'vue';

const props = defineProps({
  form: { type: Object, required: true },
  states: { type: Array, required: true },
  submitLabel: { type: String, default: 'Salvar' },
  cancelHref: { type: String, required: true },
});

const emit = defineEmits(['submit']);

// Sistema de toasts
const { error: toastError } = useToasts();

// Estado para edição inline de endereços
const editingAddressIndex = ref(-1);
const showAddForm = ref(false);
const newAddress = ref({
  description: '',
  postal_code: '',
  address: '',
  address_number: '',
  address_complement: '',
  neighborhood: '',
  city: '',
  state: '',
  status: 'active'
});
const addressErrors = ref({});

// Estado para confirmação de exclusão de endereço
const deleteAddressState = ref({ open: false, processing: false, addressIndex: null });

// Inicializar endereços se não existir
if (!props.form.addresses) {
  props.form.addresses = [];
}

// Função auxiliar para focar no primeiro campo do formulário de endereço
const focusFirstAddressField = () => {
  nextTick(() => {
    const descriptionField = document.querySelector('input[placeholder="Ex: Sede, Filial Centro"]');
    if (descriptionField) {
      descriptionField.focus();
    }
  });
};

const digitsOnly = (v = '') => String(v).replace(/\D+/g, '');
const applyMask = (value, pattern) => {
  let index = 0;
  const numbers = digitsOnly(value);
  return pattern
    .replace(/#/g, () => numbers[index++] ?? '')
    .replace(/([-/\\.() ])+$/, '');
};
const formatDocument = () => {
  const digits = digitsOnly(props.form.document);
  props.form.document = props.form.person_type === 'company'
    ? applyMask(digits, '##.###.###/####-##')
    : applyMask(digits, '###.###.###-##');
};
const formatPhone = (key) => {
  const digits = digitsOnly(props.form[key]);
  const pattern = digits.length > 10 ? '(##) #####-####' : '(##) ####-####';
  props.form[key] = applyMask(digits, pattern);
};
const formatPostalCode = (value) => {
  const digits = digitsOnly(value);
  return applyMask(digits, '#####-###');
};

// Métodos para gerenciar endereços
const addAddress = () => {
  // Validação dos campos obrigatórios
  const errors = {};

  if (!newAddress.value.description || newAddress.value.description.trim().length < 4) {
    errors.description = 'Descrição é obrigatória e deve ter pelo menos 4 caracteres';
  }

  if (!newAddress.value.postal_code || digitsOnly(newAddress.value.postal_code).length !== 8) {
    errors.postal_code = 'CEP é obrigatório e deve ter 8 dígitos';
  }

  if (!newAddress.value.address) {
    errors.address = 'Logradouro é obrigatório';
  }

  if (!newAddress.value.address_number) {
    errors.address_number = 'Número é obrigatório';
  }

  if (!newAddress.value.neighborhood) {
    errors.neighborhood = 'Bairro é obrigatório';
  }

  if (!newAddress.value.city) {
    errors.city = 'Cidade é obrigatória';
  }

  if (!newAddress.value.state) {
    errors.state = 'Estado é obrigatório';
  }

  if (!newAddress.value.status) {
    errors.status = 'Status é obrigatório';
  }

  // Se há erros, não adiciona o endereço
  if (Object.keys(errors).length > 0) {
    addressErrors.value = errors;
    return;
  }

  // Limpa erros anteriores
  addressErrors.value = {};

  if (editingAddressIndex.value >= 0) {
    // Salvar edição
    props.form.addresses[editingAddressIndex.value] = { ...newAddress.value };
    editingAddressIndex.value = -1;
  } else {
    // Adicionar novo
    props.form.addresses.push({ ...newAddress.value, id: Date.now() }); // ID temporário
  }
  resetNewAddress();
  showAddForm.value = false;
};

const editAddress = (index) => {
  editingAddressIndex.value = index;
  newAddress.value = { ...props.form.addresses[index] };
  addressErrors.value = {}; // Limpa erros ao editar
  showAddForm.value = true;
  focusFirstAddressField();
};

const cancelEdit = () => {
  editingAddressIndex.value = -1;
  showAddForm.value = false;
  resetNewAddress();
};

const removeAddress = (index) => {
  props.form.addresses.splice(index, 1);
  if (editingAddressIndex.value === index) {
    cancelEdit();
  }
  // Não mostrar automaticamente o formulário quando remove endereços
};

const confirmDeleteAddress = (index) => {
  deleteAddressState.value = { open: true, processing: false, addressIndex: index };
};

const performDeleteAddress = () => {
  if (deleteAddressState.value.addressIndex === null) return;

  deleteAddressState.value.processing = true;
  try {
    const index = deleteAddressState.value.addressIndex;
    props.form.addresses.splice(index, 1);
    if (editingAddressIndex.value === index) {
      cancelEdit();
    }
    // Não mostrar automaticamente o formulário quando remove o último endereço
    // O usuário pode usar o botão "Adicionar novo endereço"
  } finally {
    deleteAddressState.value.processing = false;
    deleteAddressState.value.open = false;
    deleteAddressState.value.addressIndex = null;
  }
};

const resetNewAddress = () => {
  newAddress.value = {
    description: '',
    postal_code: '',
    address: '',
    address_number: '',
    address_complement: '',
    neighborhood: '',
    city: '',
    state: '',
    status: 'active'
  };
  addressErrors.value = {}; // Limpa erros ao resetar
};

// Buscar endereço via CEP
const fetchAddress = async () => {
  const cep = digitsOnly(newAddress.value.postal_code);

  if (!cep || cep.length !== 8) return;

  try {
    const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
    const data = await response.json();

    if (data.erro) {
      toastError('CEP não encontrado', { title: 'Erro na busca' });
      return;
    }

    newAddress.value.address = data.logradouro || '';
    newAddress.value.neighborhood = data.bairro || '';
    newAddress.value.city = data.localidade || '';
    newAddress.value.state = data.uf || '';
  } catch (error) {
    console.error('Erro ao buscar CEP:', error);
    toastError('Erro ao consultar CEP. Tente novamente.', { title: 'Erro na integração' });
  }
};

const onSubmit = () => emit('submit');

// Computed para verificar se há erros nos endereços
const hasAddressErrors = computed(() => {
  if (!props.form.errors) return false;

  return props.form.addresses?.some((address, index) => {
    const addressErrors = Object.keys(props.form.errors).filter(key => key.startsWith(`addresses.${index}.`));
    return addressErrors.length > 0;
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
        Tipo de pessoa
        <select v-model="form.person_type" class="form-select" required @change="formatDocument">
          <option value="individual">Pessoa Física</option>
          <option value="company">Pessoa Jurídica</option>
        </select>
        <span v-if="form.errors.person_type" class="text-sm font-medium text-rose-600">{{ form.errors.person_type }}</span>
      </label>

      <label class="form-label">
        CPF/CNPJ
        <input type="text" v-model="form.document" required class="form-input" @input="formatDocument" />
        <span v-if="form.errors.document" class="text-sm font-medium text-rose-600">{{ form.errors.document }}</span>
      </label>

      <div class="switch-field sm:col-span-2 lg:col-span-3">
        <span class="switch-label">Status do cliente</span>
        <Switch v-model="form.status" true-value="active" false-value="inactive" />
        <span class="switch-status" :class="{ 'inactive': form.status !== 'active' }">
          {{ form.status === 'active' ? 'Ativo' : 'Inativo' }}
        </span>
      </div>
      <span v-if="form.errors.status" class="text-sm font-medium text-rose-600 sm:col-span-2 lg:col-span-3">{{ form.errors.status }}</span>
    </div>

        <label class="form-label">
      Observações
      <textarea v-model="form.observations" rows="4" class="form-textarea" />
      <span v-if="form.errors.observations" class="text-sm font-medium text-rose-600">{{ form.errors.observations }}</span>
    </label>

    <fieldset class="space-y-3">
      <legend class="text-sm font-semibold text-slate-700">Endereços</legend>

      <!-- Formulário inline para adicionar/editar endereço -->
      <div v-if="showAddForm" class="border border-slate-200 rounded-lg p-4 bg-slate-50">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <label class="form-label">
            Descrição
            <input type="text" v-model="newAddress.description" placeholder="Ex: Sede, Filial Centro" required class="form-input" />
            <span v-if="addressErrors.description" class="text-sm font-medium text-rose-600">{{ addressErrors.description }}</span>
          </label>
          <label class="form-label">
            CEP
            <input type="text" v-model="newAddress.postal_code" required class="form-input" @input="newAddress.postal_code = formatPostalCode(newAddress.postal_code)" @blur="fetchAddress" />
            <span v-if="addressErrors.postal_code" class="text-sm font-medium text-rose-600">{{ addressErrors.postal_code }}</span>
          </label>
          <label class="form-label">
            Logradouro
            <input type="text" v-model="newAddress.address" class="form-input" />
            <span v-if="addressErrors.address" class="text-sm font-medium text-rose-600">{{ addressErrors.address }}</span>
          </label>
          <label class="form-label">
            Número
            <input type="text" v-model="newAddress.address_number" class="form-input" />
            <span v-if="addressErrors.address_number" class="text-sm font-medium text-rose-600">{{ addressErrors.address_number }}</span>
          </label>
          <label class="form-label">
            Complemento
            <input type="text" v-model="newAddress.address_complement" class="form-input" />
          </label>
          <label class="form-label">
            Bairro
            <input type="text" v-model="newAddress.neighborhood" class="form-input" />
            <span v-if="addressErrors.neighborhood" class="text-sm font-medium text-rose-600">{{ addressErrors.neighborhood }}</span>
          </label>
          <label class="form-label">
            Cidade
            <input type="text" v-model="newAddress.city" readonly class="form-input" />
            <span v-if="addressErrors.city" class="text-sm font-medium text-rose-600">{{ addressErrors.city }}</span>
            <span class="text-xs text-slate-500">Preenchido automaticamente via CEP</span>
          </label>
          <label class="form-label">
            Estado (UF)
            <input type="text" v-model="newAddress.state" readonly class="form-input" />
            <span v-if="addressErrors.state" class="text-sm font-medium text-rose-600">{{ addressErrors.state }}</span>
            <span class="text-xs text-slate-500">Preenchido automaticamente via CEP</span>
          </label>
          <div class="switch-field lg:col-span-3">
            <span class="switch-label">Status do endereço</span>
            <Switch v-model="newAddress.status" true-value="active" false-value="inactive" />
            <span class="switch-status" :class="{ 'inactive': newAddress.status !== 'active' }">
              {{ newAddress.status === 'active' ? 'Ativo' : 'Inativo' }}
            </span>
          </div>
          <div class="flex items-end gap-2 lg:col-span-3">
            <button type="button" @click="addAddress" class="btn-primary text-sm">
              {{ editingAddressIndex >= 0 ? 'Salvar' : 'Adicionar' }}
            </button>
            <button type="button" @click="cancelEdit" class="btn-ghost text-sm">
              Cancelar
            </button>
          </div>
        </div>
      </div>

      <!-- Tabela de endereços -->
      <div class="table-wrapper">
        <table class="table">
          <thead>
            <tr>
              <th>Descrição</th>
              <th>Endereço</th>
              <th>Status</th>
              <th class="w-24">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(address, index) in form.addresses" :key="address.id || index">
              <td>
                {{ address.description || 'Sem descrição' }}
              </td>
              <td>
                {{ address.address }}, {{ address.address_number }}
                <span v-if="address.address_complement"> - {{ address.address_complement }}</span>
                <br>
                <span class="text-slate-500">{{ address.neighborhood }}, {{ address.city }}/{{ address.state }}</span>
              </td>
              <td class="table-actions">
                <span :class="address.status === 'active' ? 'badge-success' : 'badge-danger'">
                  {{ address.status === 'active' ? 'Ativo' : 'Inativo' }}
                </span>
              </td>
              <td class="whitespace-nowrap">
                <Dropdown>
                  <template #trigger="{ toggle }">
                    <button type="button" class="menu-trigger" @click="toggle" aria-label="Abrir menu de ações">
                      <HeroIcon name="ellipsis-horizontal" class="h-5 w-5" />
                    </button>
                  </template>
                  <template #default="{ close }">
                    <button type="button" class="menu-panel-link" @click="editAddress(index); close()">
                      <HeroIcon name="pencil" class="h-4 w-4" />
                      <span>Editar</span>
                    </button>
                    <button type="button" class="menu-panel-link text-rose-600 hover:text-rose-700" @click="confirmDeleteAddress(index); close()">
                      <HeroIcon name="trash" class="h-4 w-4" />
                      <span>Excluir</span>
                    </button>
                  </template>
                </Dropdown>
              </td>
            </tr>
            <tr v-if="!form.addresses || form.addresses.length === 0">
              <td colspan="4" class="table-empty">Nenhum endereço cadastrado para este cliente.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Botão para adicionar novo endereço -->
      <div v-if="!showAddForm" class="flex justify-center pt-4">
        <button type="button" @click="showAddForm = true; focusFirstAddressField()" class="btn-ghost text-sm">
          Adicionar novo endereço
        </button>
      </div>

      <span v-if="hasAddressErrors" class="text-sm font-medium text-rose-600">Verifique os erros nos endereços.</span>
    </fieldset>

    <fieldset class="space-y-3">
      <legend class="text-sm font-semibold text-slate-700">Contato</legend>
      <div class="grid gap-4 sm:grid-cols-2" id="contact_fields">
        <label class="form-label">
          Nome do contato
          <input type="text" v-model="form.contact_name" :required="form.person_type === 'company'" :disabled="form.person_type === 'individual'" class="form-input" />
          <span v-if="form.errors.contact_name" class="text-sm font-medium text-rose-600">{{ form.errors.contact_name }}</span>
        </label>
        <label class="form-label">
          Telefone principal
          <input type="text" v-model="form.contact_phone_primary" :required="form.person_type === 'company'" class="form-input" @input="formatPhone('contact_phone_primary')" />
          <span v-if="form.errors.contact_phone_primary" class="text-sm font-medium text-rose-600">{{ form.errors.contact_phone_primary }}</span>
        </label>
        <label class="form-label">
          Telefone secundário
          <input type="text" v-model="form.contact_phone_secondary" :required="form.person_type === 'company'" class="form-input" @input="formatPhone('contact_phone_secondary')" />
          <span v-if="form.errors.contact_phone_secondary" class="text-sm font-medium text-rose-600">{{ form.errors.contact_phone_secondary }}</span>
        </label>
        <label class="form-label">
          E-mail
          <input type="email" v-model="form.contact_email" :required="form.person_type === 'company'" class="form-input" />
          <span v-if="form.errors.contact_email" class="text-sm font-medium text-rose-600">{{ form.errors.contact_email }}</span>
        </label>
      </div>
    </fieldset>

    <div class="flex flex-wrap gap-3">
      <button type="submit" class="btn-primary" :disabled="form.processing">{{ submitLabel }}</button>
      <a class="btn-ghost" :href="cancelHref">Cancelar</a>
    </div>
  </form>

  <ConfirmModal v-model="deleteAddressState.open"
                :processing="deleteAddressState.processing"
                title="Excluir endereço"
                message="Deseja realmente remover este endereço?"
                confirm-text="Excluir"
                variant="danger"
                @confirm="performDeleteAddress" />
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

