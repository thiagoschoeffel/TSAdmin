<script setup>
import Switch from '@/components/ui/Switch.vue';

const props = defineProps({
  form: { type: Object, required: true },
  states: { type: Array, required: true },
  submitLabel: { type: String, default: 'Salvar' },
  cancelHref: { type: String, required: true },
});

const emit = defineEmits(['submit']);

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
const formatPostalCode = () => {
  props.form.postal_code = applyMask(props.form.postal_code, '#####-###');
};
const formatPhone = (key) => {
  const digits = digitsOnly(props.form[key]);
  const pattern = digits.length > 10 ? '(##) #####-####' : '(##) ####-####';
  props.form[key] = applyMask(digits, pattern);
};

const onSubmit = () => emit('submit');
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
      <legend class="text-sm font-semibold text-slate-700">Endereço</legend>
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <label class="form-label">
          CEP
          <input type="text" v-model="form.postal_code" required class="form-input" @input="formatPostalCode" />
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
          <input type="text" v-model="form.city" required class="form-input" />
          <span v-if="form.errors.city" class="text-sm font-medium text-rose-600">{{ form.errors.city }}</span>
        </label>
        <label class="form-label">
          Estado (UF)
          <select v-model="form.state" required class="form-select">
            <option value="">Selecione</option>
            <option v-for="uf in states" :key="uf" :value="uf">{{ uf }}</option>
          </select>
          <span v-if="form.errors.state" class="text-sm font-medium text-rose-600">{{ form.errors.state }}</span>
        </label>
      </div>
    </fieldset>

    <fieldset class="space-y-3">
      <legend class="text-sm font-semibold text-slate-700">Contato</legend>
      <div class="grid gap-4 sm:grid-cols-2" id="contact_fields">
        <label class="form-label">
          Nome do contato
          <input type="text" v-model="form.contact_name" :required="form.person_type === 'company'" class="form-input" />
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
</template>

