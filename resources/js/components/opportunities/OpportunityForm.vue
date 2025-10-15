<script setup>
import Button from '@/components/Button.vue';
import InputText from '@/components/InputText.vue';
import InputSelect from '@/components/InputSelect.vue';
import InputTextarea from '@/components/InputTextarea.vue';
import HeroIcon from '@/components/icons/HeroIcon.vue';

const props = defineProps({
  form: { type: Object, required: true },
  leads: { type: Array, required: true },
  clients: { type: Array, required: true },
  products: { type: Array, required: true },
  submitLabel: { type: String, default: 'Salvar' },
  cancelHref: { type: String, required: true },
});

const emit = defineEmits(['submit']);

const onSubmit = () => emit('submit');

const addItem = () => {
  props.form.items.push({
    product_id: '',
    quantity: 1,
    unit_price: '',
    subtotal: '',
  });
};

const removeItem = (index) => {
  props.form.items.splice(index, 1);
};

const updateSubtotal = (index) => {
  const item = props.form.items[index];
  item.subtotal = (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
};
</script>

<template>
  <form @submit.prevent="onSubmit" class="space-y-6">
    <div class="grid gap-6 sm:grid-cols-2">
      <label class="form-label">
        Lead *
        <InputSelect v-model="form.lead_id" :options="[
          { value: '', label: 'Selecione o Lead' },
          ...leads.map(lead => ({ value: lead.id, label: lead.name }))
        ]" :error="!!form.errors.lead_id" />
        <span v-if="form.errors.lead_id" class="text-sm font-medium text-rose-600">{{ form.errors.lead_id }}</span>
      </label>
      <label class="form-label">
        Cliente *
        <InputSelect v-model="form.client_id" :options="[
          { value: '', label: 'Selecione o Cliente' },
          ...clients.map(client => ({ value: client.id, label: client.name }))
        ]" :error="!!form.errors.client_id" />
        <span v-if="form.errors.client_id" class="text-sm font-medium text-rose-600">{{ form.errors.client_id }}</span>
      </label>
      <label class="form-label">
        Título *
        <InputText v-model="form.title" placeholder="Título da oportunidade" required :error="!!form.errors.title" />
        <span v-if="form.errors.title" class="text-sm font-medium text-rose-600">{{ form.errors.title }}</span>
      </label>
      <label class="form-label">
        Etapa *
        <InputSelect v-model="form.stage" :options="[
          { value: 'new', label: 'Novo' },
          { value: 'contact', label: 'Contato' },
          { value: 'proposal', label: 'Proposta' },
          { value: 'negotiation', label: 'Negociação' },
          { value: 'won', label: 'Ganho' },
          { value: 'lost', label: 'Perdido' }
        ]" :error="!!form.errors.stage" />
        <span v-if="form.errors.stage" class="text-sm font-medium text-rose-600">{{ form.errors.stage }}</span>
      </label>
      <label class="form-label">
        Probabilidade (%)
        <InputText v-model="form.probability" type="number" min="0" max="100" placeholder="0" :error="!!form.errors.probability" />
        <span v-if="form.errors.probability" class="text-sm font-medium text-rose-600">{{ form.errors.probability }}</span>
      </label>
      <label class="form-label">
        Valor Estimado
        <InputText v-model="form.expected_value" type="number" min="0" step="0.01" placeholder="0.00" :error="!!form.errors.expected_value" />
        <span v-if="form.errors.expected_value" class="text-sm font-medium text-rose-600">{{ form.errors.expected_value }}</span>
      </label>
      <label class="form-label">
        Data Prevista de Fechamento
        <InputText v-model="form.expected_close_date" type="date" :error="!!form.errors.expected_close_date" />
        <span v-if="form.errors.expected_close_date" class="text-sm font-medium text-rose-600">{{ form.errors.expected_close_date }}</span>
      </label>
      <label class="form-label">
        Status *
        <InputSelect v-model="form.status" :options="[
          { value: 'active', label: 'Ativa' },
          { value: 'inactive', label: 'Inativa' }
        ]" :error="!!form.errors.status" />
        <span v-if="form.errors.status" class="text-sm font-medium text-rose-600">{{ form.errors.status }}</span>
      </label>
    </div>

    <label class="form-label">
      Descrição
      <InputTextarea v-model="form.description" :error="!!form.errors.description" />
      <span v-if="form.errors.description" class="text-sm font-medium text-rose-600">{{ form.errors.description }}</span>
    </label>

    <!-- Itens da oportunidade -->
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold">Itens</h3>
        <Button type="button" variant="outline" @click="addItem">
          <HeroIcon name="plus" class="h-4 w-4" />
          Adicionar Item
        </Button>
      </div>

      <div v-for="(item, idx) in form.items" :key="idx" class="border border-slate-200 rounded-lg p-4 space-y-4">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <label class="form-label">
            Produto
            <InputSelect v-model="item.product_id" :options="[
              { value: '', label: 'Selecione' },
              ...products.map(product => ({ value: product.id, label: product.name }))
            ]" :error="!!(form.errors && form.errors[`items.${idx}.product_id`])" />
            <span v-if="form.errors && form.errors[`items.${idx}.product_id`]" class="text-sm font-medium text-rose-600">{{ form.errors[`items.${idx}.product_id`] }}</span>
          </label>
          <label class="form-label">
            Quantidade
            <InputText v-model="item.quantity" type="number" min="0.01" step="0.01" @input="updateSubtotal(idx)" :error="!!(form.errors && form.errors[`items.${idx}.quantity`])" />
            <span v-if="form.errors && form.errors[`items.${idx}.quantity`]" class="text-sm font-medium text-rose-600">{{ form.errors[`items.${idx}.quantity`] }}</span>
          </label>
          <label class="form-label">
            Preço Unitário
            <InputText v-model="item.unit_price" type="number" min="0" step="0.01" @input="updateSubtotal(idx)" :error="!!(form.errors && form.errors[`items.${idx}.unit_price`])" />
            <span v-if="form.errors && form.errors[`items.${idx}.unit_price`]" class="text-sm font-medium text-rose-600">{{ form.errors[`items.${idx}.unit_price`] }}</span>
          </label>
          <label class="form-label">
            Subtotal
            <InputText v-model="item.subtotal" readonly class="bg-slate-50" />
          </label>
        </div>
        <div class="flex justify-end">
          <Button type="button" variant="danger" size="sm" @click="removeItem(idx)">
            <HeroIcon name="trash" class="h-4 w-4" />
            Remover
          </Button>
        </div>
      </div>

      <p v-if="form.items.length === 0" class="text-sm text-slate-500">Nenhum item adicionado ainda.</p>
    </div>

    <div class="flex flex-wrap gap-3">
      <Button type="submit" variant="primary" :loading="form.processing">
        <HeroIcon name="check" class="h-5 w-5" />
        <span v-if="!form.processing">{{ submitLabel }}</span>
        <span v-else>Salvando…</span>
      </Button>
      <Button type="button" variant="ghost" :href="cancelHref">Cancelar</Button>
    </div>
  </form>
</template>
