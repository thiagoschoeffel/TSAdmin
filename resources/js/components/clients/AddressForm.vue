<script setup>
import Switch from '@/components/ui/Switch.vue';
import Button from '@/components/Button.vue';
import { useToasts } from '@/components/toast/useToasts';

const props = defineProps({
  form: { type: Object, required: true },
  states: { type: Array, required: true },
  submitLabel: { type: String, default: 'Salvar' },
  cancelHref: { type: String, required: true },
});

const emit = defineEmits(['submit']);

const { error } = useToasts();

const digitsOnly = (v = '') => String(v).replace(/\D+/g, '');
const applyMask = (value, pattern) => {
  let index = 0;
  const numbers = digitsOnly(value);
  return pattern
    .replace(/#/g, () => numbers[index++] ?? '')
    .replace(/([-/\\.() ])+$/, '');
};
const formatPostalCode = () => {
  props.form.postal_code = applyMask(props.form.postal_code, '#####-###');
};

const fetchAddress = async () => {
  const cep = digitsOnly(props.form.postal_code);
  if (cep.length !== 8) return;

  try {
    const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
    const data = await response.json();

    if (data.erro) {
      error('CEP não encontrado. Verifique se o CEP é válido.');
    } else {
      props.form.address = data.logradouro || '';
      props.form.neighborhood = data.bairro || '';
      props.form.city = data.localidade || '';
      props.form.state = data.uf || '';
    }
  } catch (err) {
    console.error('Erro ao buscar CEP:', err);
    error('Erro ao buscar CEP. Tente novamente mais tarde.');
  }
};

const onSubmit = () => emit('submit');
</script>

<template>
  <form @submit.prevent="onSubmit" class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <label class="form-label">
        Descrição
        <input type="text" v-model="form.description" required class="form-input" placeholder="Ex: Escritório, Filial Centro" />
        <span v-if="form.errors.description" class="text-sm font-medium text-rose-600">{{ form.errors.description }}</span>
      </label>

      <div class="switch-field sm:col-span-2 lg:col-span-3">
        <span class="switch-label">Status do endereço</span>
        <Switch v-model="form.status" true-value="active" false-value="inactive" />
        <span class="switch-status" :class="{ 'inactive': form.status !== 'active' }">
          {{ form.status === 'active' ? 'Ativo' : 'Inativo' }}
        </span>
      </div>
      <span v-if="form.errors.status" class="text-sm font-medium text-rose-600 sm:col-span-2 lg:col-span-3">{{ form.errors.status }}</span>
    </div>

    <fieldset class="space-y-3">
      <legend class="text-sm font-semibold text-slate-700">Endereço</legend>
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <label class="form-label">
          CEP
          <input type="text" v-model="form.postal_code" required class="form-input" @input="formatPostalCode" @blur="fetchAddress" />
          <span v-if="form.errors.postal_code" class="text-sm font-medium text-rose-600">{{ form.errors.postal_code }}</span>
        </label>
        <label class="form-label">
          Logradouro
          <input type="text" v-model="form.address" required class="form-input" />
          <span v-if="form.errors.address" class="text-sm font-medium text-rose-600">{{ form.errors.address }}</span>
        </label>
        <label class="form-label">
          Número
          <input type="text" v-model="form.address_number" required class="form-input" />
          <span v-if="form.errors.address_number" class="text-sm font-medium text-rose-600">{{ form.errors.address_number }}</span>
        </label>
        <label class="form-label">
          Complemento
          <input type="text" v-model="form.address_complement" class="form-input" />
          <span v-if="form.errors.address_complement" class="text-sm font-medium text-rose-600">{{ form.errors.address_complement }}</span>
        </label>
        <label class="form-label">
          Bairro
          <input type="text" v-model="form.neighborhood" required class="form-input" />
          <span v-if="form.errors.neighborhood" class="text-sm font-medium text-rose-600">{{ form.errors.neighborhood }}</span>
        </label>
        <label class="form-label">
          Cidade
          <input type="text" v-model="form.city" required class="form-input" disabled />
          <span v-if="form.errors.city" class="text-sm font-medium text-rose-600">{{ form.errors.city }}</span>
        </label>
        <label class="form-label">
          Estado (UF)
          <select v-model="form.state" required class="form-select" disabled>
            <option value="">Selecione</option>
            <option v-for="uf in states" :key="uf" :value="uf">{{ uf }}</option>
          </select>
          <span v-if="form.errors.state" class="text-sm font-medium text-rose-600">{{ form.errors.state }}</span>
        </label>
      </div>
    </fieldset>

    <div class="flex flex-wrap gap-3">
      <Button type="submit" variant="primary" :loading="form.processing">{{ submitLabel }}</Button>
      <Button :href="cancelHref" variant="ghost">Cancelar</Button>
    </div>
  </form>
</template>

<style scoped>
.form-label { display:flex; flex-direction:column; gap:.5rem; font-weight:600; color:#334155 }
.form-input { border:1px solid #cbd5e1; border-radius:.5rem; padding:.5rem .75rem; }
.form-input:disabled { background-color: #f3f4f6; color: #6b7280; cursor: not-allowed; }
.form-select { border:1px solid #cbd5e1; border-radius:.5rem; padding:.5rem .75rem; }
.form-select:disabled { background-color: #f3f4f6; color: #6b7280; cursor: not-allowed; }
</style>
