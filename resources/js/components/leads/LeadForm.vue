<script setup>
import Button from '@/components/Button.vue';
import InputText from '@/components/InputText.vue';
import InputSelect from '@/components/InputSelect.vue';
import InputTextarea from '@/components/InputTextarea.vue';
import InputDatePicker from '@/components/InputDatePicker.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import HeroIcon from '@/components/icons/HeroIcon.vue';
import DataTable from '@/components/DataTable.vue';
import { formatPhone } from '@/utils/masks.js';
import axios from 'axios';
import { ref, computed, nextTick } from 'vue';
import { useToasts } from '@/components/toast/useToasts.js';

const { error: toastError, success: toastSuccess } = useToasts();

const props = defineProps({
  form: { type: Object, required: true },
  submitLabel: { type: String, default: 'Salvar' },
  cancelHref: { type: String, required: true },
  isEditing: { type: Boolean, default: false },
  leadId: { type: [Number, String, null], default: null },
});

const emit = defineEmits(['submit']);

// Estado para edição inline de interações
const editingInteractionIndex = ref(-1);
const showAddForm = ref(false);
const newInteraction = ref({
  type: '',
  interacted_at: '',
  description: ''
});
const interactionErrors = ref({});

// Estado para confirmação de exclusão de interação
const deleteInteractionState = ref({ open: false, processing: false, interactionIndex: null });

// Inicializar interações se não existir
if (!props.form.interactions) {
  props.form.interactions = [];
}

// Computed para data/hora atual
const currentDateTime = computed(() => {
  const now = new Date();
  return now.toISOString().slice(0, 16).replace('T', ' '); // Formato YYYY-MM-DD HH:MM
});

// Função para obter data/hora atual fresca
const getCurrentDateTime = () => {
  const now = new Date();
  return now.toISOString().slice(0, 16).replace('T', ' '); // Formato YYYY-MM-DD HH:MM
};

const interactionColumns = [
  {
    header: 'Tipo',
    key: 'type_label'
  },
  {
    header: 'Data/Hora',
    key: 'interacted_at',
    formatter: (value) => {
      if (!value) return '—';
      const d = new Date(value);
      if (Number.isNaN(d.getTime())) return String(value);
      return d.toLocaleString('pt-BR', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit', hour12: false,
      });
    }
  },
  {
    header: 'Descrição',
    key: 'description'
  },
  {
    header: 'Registrado por',
    key: 'created_by'
  }
];

const interactionActions = computed(() => [
  {
    key: 'edit',
    label: 'Editar',
    icon: 'pencil'
  },
  {
    key: 'delete',
    label: 'Excluir',
    icon: 'trash',
    class: 'text-rose-600 hover:text-rose-700'
  }
]);

// Funções para gerenciar interações
const addInteraction = async () => {
  interactionErrors.value = {};

  // Validação básica
  if (!newInteraction.value.type) {
    interactionErrors.value.type = 'Tipo é obrigatório';
    return;
  }
  if (!newInteraction.value.interacted_at) {
    interactionErrors.value.interacted_at = 'Data e hora são obrigatórios';
    return;
  }
  if (!newInteraction.value.description.trim()) {
    interactionErrors.value.description = 'Descrição é obrigatória';
    return;
  }

  if (props.isEditing) {
    // Modo edição: salvar diretamente no banco
    await saveInteractionToDatabase();
  } else {
    // Modo criação: adicionar à lista em memória
    if (editingInteractionIndex.value >= 0) {
      // Salvar edição em memória
      props.form.interactions[editingInteractionIndex.value] = { ...newInteraction.value };
      editingInteractionIndex.value = -1;
      toastSuccess('Interação atualizada com sucesso!');
    } else {
      // Adicionar novo
      props.form.interactions.push({
        ...newInteraction.value,
        id: Date.now(), // ID temporário para frontend
        type_label: getTypeLabel(newInteraction.value.type),
        created_by: 'Você' // Placeholder
      });
      toastSuccess('Interação adicionada com sucesso!');
    }
    resetNewInteraction();
    showAddForm.value = false;
  }
};

const editInteraction = (index) => {
  // No modo edição, só permite editar interações que já existem no backend (id real)
  if (props.isEditing && (!props.form.interactions[index].id || String(props.form.interactions[index].id).length > 10)) {
    toastError('Salve o lead antes de editar esta interação.');
    return;
  }
  const interaction = props.form.interactions[index];
  editingInteractionIndex.value = index;
  newInteraction.value = {
    type: interaction.type,
    interacted_at: interaction.interacted_at ? new Date(interaction.interacted_at).toISOString().slice(0, 16).replace('T', ' ') : '',
    description: interaction.description
  };
  showAddForm.value = true;
  focusFirstInteractionField();
};

const saveInteractionToDatabase = async () => {
  try {
    const interactionData = {
      type: newInteraction.value.type,
      description: newInteraction.value.description,
      interacted_at: newInteraction.value.interacted_at
    };
    let result;

    if (editingInteractionIndex.value >= 0) {
      // Editando interação existente
      const interactionId = props.form.interactions[editingInteractionIndex.value].id;
      const { data } = await axios.patch(`/admin/leads/${props.leadId}/interactions/${interactionId}`, interactionData);
      result = data;
    } else {
      // Criando nova interação
      const { data } = await axios.post(`/admin/leads/${props.leadId}/interactions`, interactionData);
      result = data;
    }

    if (editingInteractionIndex.value >= 0) {
      props.form.interactions[editingInteractionIndex.value] = result.interaction;
      editingInteractionIndex.value = -1;
      toastSuccess('Interação atualizada com sucesso!');
    } else {
      props.form.interactions.unshift(result.interaction);
      toastSuccess('Interação adicionada com sucesso!');
    }

    resetNewInteraction();
    showAddForm.value = false;
  } catch (error) {
    console.error('Erro ao salvar interação:', error);
    toastError(error.response?.data?.message || error.message || 'Erro ao salvar interação. Tente novamente.');
  }
};

const confirmDeleteInteraction = (index) => {
  deleteInteractionState.value = { open: true, processing: false, interactionIndex: index };
};

const performDeleteInteraction = async () => {
  if (deleteInteractionState.value.interactionIndex === null) return;

  deleteInteractionState.value.processing = true;
  try {
    const index = deleteInteractionState.value.interactionIndex;

    // Verificar se o array existe
    if (!props.form.interactions || !Array.isArray(props.form.interactions)) {
      toastError('Lista de interações não encontrada.');
      return;
    }

    // Verificar se o índice é válido
    if (index < 0 || index >= props.form.interactions.length) {
      toastError('Interação não encontrada. Tente novamente.');
      return;
    }

    const interaction = props.form.interactions[index];

    // Verificar se a interação existe
    if (!interaction) {
      toastError('Interação não encontrada. Tente novamente.');
      return;
    }

    // Só deleta do backend se o id for real (não temporário)
    const isRealId = interaction.id && String(interaction.id).length < 10 && Number.isInteger(Number(interaction.id));
    if (props.isEditing && isRealId) {
      // Modo edição: deletar do banco
      await deleteInteractionFromDatabase(interaction.id);
    }

    // Remover da lista (tanto em edição quanto criação)
    props.form.interactions.splice(index, 1);
    if (editingInteractionIndex.value === index) {
      cancelEdit();
    }

    if (!props.isEditing || !isRealId) {
      toastSuccess('Interação removida com sucesso!');
    }
  } catch (error) {
    // Se houve erro na deleção do banco, não remove da lista
    console.error('Erro ao deletar interação:', error);
    toastError('Erro ao remover interação. Tente novamente.');
  } finally {
    deleteInteractionState.value.processing = false;
    deleteInteractionState.value.open = false;
    deleteInteractionState.value.interactionIndex = null;
  }
};

const deleteInteractionFromDatabase = async (interactionId) => {
  try {
    await axios.delete(`/admin/leads/${props.leadId}/interactions/${interactionId}`);
    toastSuccess('Interação excluída com sucesso!');
  } catch (error) {
    console.error('Erro ao excluir interação:', error);
    // Se a interação não existe (404), trata como sucesso (já foi removida)
    if (error.response?.status === 404) {
      toastSuccess('Interação excluída com sucesso!');
      return;
    }
    toastError(error.response?.data?.message || error.message || 'Erro ao excluir interação. Tente novamente.');
    throw error; // Re-throw para impedir a remoção da lista se falhou no banco
  }
};

const deleteInteraction = async (index) => {
  if (props.isEditing) {
    // Modo edição: confirmar exclusão que será feita no banco
    confirmDeleteInteraction(index);
  } else {
    // Modo criação: remover da lista em memória
    props.form.interactions.splice(index, 1);
    if (editingInteractionIndex.value === index) {
      cancelEdit();
    }
  }
};

const cancelEdit = () => {
  editingInteractionIndex.value = -1;
  newInteraction.value = {
    type: '',
    interacted_at: getCurrentDateTime(),
    description: ''
  };
  showAddForm.value = false;
  interactionErrors.value = {};
};

const resetNewInteraction = () => {
  newInteraction.value = {
    type: '',
    interacted_at: getCurrentDateTime(),
    description: ''
  };
  interactionErrors.value = {};
};

const focusFirstInteractionField = () => {
  nextTick(() => {
    const firstField = document.querySelector('#interaction-form select');
    if (firstField) firstField.focus();
  });
};

const getTypeLabel = (type) => {
  const types = {
    phone_call: 'Ligação Telefônica',
    email: 'E-mail',
    meeting: 'Reunião',
    whatsapp: 'WhatsApp',
    visit: 'Visita',
    other: 'Outro'
  };
  return types[type] || type;
};

const handleInteractionAction = ({ action, item }) => {
  // Encontrar o índice do item no array
  const index = props.form.interactions.indexOf(item);

  if (action.key === 'edit') {
    editInteraction(index);
  } else if (action.key === 'delete') {
    deleteInteraction(index);
  }
};

// Inicializar data/hora atual
newInteraction.value.interacted_at = getCurrentDateTime();

const onSubmit = () => emit('submit');

const formatPhoneField = () => {
  props.form.phone = formatPhone(props.form.phone);
};
</script>

<template>
  <form @submit.prevent="onSubmit" class="space-y-6">
    <div class="grid gap-6 sm:grid-cols-2">
      <label class="form-label">
        Nome *
        <InputText v-model="form.name" required :error="!!form.errors.name" />
        <span v-if="form.errors.name" class="text-sm font-medium text-rose-600">{{ form.errors.name }}</span>
      </label>
      <label class="form-label">
        Email
        <InputText v-model="form.email" type="email" :error="!!form.errors.email" />
        <span v-if="form.errors.email" class="text-sm font-medium text-rose-600">{{ form.errors.email }}</span>
      </label>
      <label class="form-label">
        Telefone
        <InputText v-model="form.phone" placeholder="(11) 99999-9999" :error="!!form.errors.phone" @input="formatPhoneField" maxlength="15" />
        <span v-if="form.errors.phone" class="text-sm font-medium text-rose-600">{{ form.errors.phone }}</span>
      </label>
      <label class="form-label">
        Empresa
        <InputText v-model="form.company" :error="!!form.errors.company" />
        <span v-if="form.errors.company" class="text-sm font-medium text-rose-600">{{ form.errors.company }}</span>
      </label>
      <label class="form-label">
        Origem *
        <InputSelect v-model="form.source" :options="[
          { value: 'site', label: 'Site' },
          { value: 'indicacao', label: 'Indicação' },
          { value: 'evento', label: 'Evento' },
          { value: 'manual', label: 'Manual' }
        ]" :error="!!form.errors.source" />
        <span v-if="form.errors.source" class="text-sm font-medium text-rose-600">{{ form.errors.source }}</span>
      </label>
      <label class="form-label">
        Status *
        <InputSelect v-model="form.status" :options="[
          { value: 'new', label: 'Novo' },
          { value: 'in_contact', label: 'Em contato' },
          { value: 'qualified', label: 'Qualificado' },
          { value: 'discarded', label: 'Descartado' }
        ]" :error="!!form.errors.status" />
        <span v-if="form.errors.status" class="text-sm font-medium text-rose-600">{{ form.errors.status }}</span>
      </label>
    </div>

    <fieldset class="space-y-3">
      <legend class="text-sm font-semibold text-slate-700">Histórico de Interações</legend>

      <!-- Formulário inline para adicionar/editar interação -->
      <div v-if="showAddForm" class="border border-slate-200 rounded-lg p-4 bg-slate-50">
        <div id="interaction-form" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <label class="form-label">
            Tipo *
            <InputSelect v-model="newInteraction.type" :options="[
              { value: 'phone_call', label: 'Ligação Telefônica' },
              { value: 'email', label: 'E-mail' },
              { value: 'meeting', label: 'Reunião' },
              { value: 'whatsapp', label: 'WhatsApp' },
              { value: 'visit', label: 'Visita' },
              { value: 'other', label: 'Outro' }
            ]" required :error="!!interactionErrors.type" />
            <span v-if="interactionErrors.type" class="text-sm font-medium text-rose-600">{{ interactionErrors.type }}</span>
          </label>
          <label class="form-label">
            Data e Hora *
            <InputDatePicker v-model="newInteraction.interacted_at" :withTime="true" :error="!!interactionErrors.interacted_at" />
            <span v-if="interactionErrors.interacted_at" class="text-sm font-medium text-rose-600">{{ interactionErrors.interacted_at }}</span>
          </label>
          <label class="form-label sm:col-span-2 lg:col-span-3">
            Descrição *
            <InputTextarea v-model="newInteraction.description" required :error="!!interactionErrors.description" />
            <span v-if="interactionErrors.description" class="text-sm font-medium text-rose-600">{{ interactionErrors.description }}</span>
          </label>
          <div class="flex items-end gap-2 lg:col-span-3">
            <Button type="button" @click="addInteraction" variant="primary" size="sm">
              {{ editingInteractionIndex >= 0 ? 'Salvar' : 'Adicionar' }}
            </Button>
            <Button type="button" @click="cancelEdit" variant="ghost" size="sm">
              Cancelar
            </Button>
          </div>
        </div>
      </div>

      <!-- Tabela de interações -->
      <DataTable
        :columns="interactionColumns"
        :data="form.interactions || []"
        :actions="interactionActions"
        empty-message="Nenhuma interação registrada para este lead."
        @action="handleInteractionAction"
      />

      <!-- Botão para adicionar nova interação -->
      <div v-if="!showAddForm" class="flex justify-center pt-4">
        <Button type="button" @click="showAddForm = true; newInteraction.interacted_at = getCurrentDateTime(); focusFirstInteractionField()" variant="ghost" size="sm">
          Adicionar nova interação
        </Button>
      </div>
    </fieldset>

    <div class="flex flex-wrap gap-3">
      <Button type="submit" variant="primary" :loading="form.processing">
        <HeroIcon name="check" class="h-5 w-5" />
        <span v-if="!form.processing">{{ submitLabel }}</span>
        <span v-else>Salvando…</span>
      </Button>
      <Button type="button" variant="ghost" :href="cancelHref">Cancelar</Button>
    </div>
  </form>

  <ConfirmModal v-model="deleteInteractionState.open"
                :processing="deleteInteractionState.processing"
                title="Excluir interação"
                message="Deseja realmente remover esta interação?"
                confirm-text="Excluir"
                variant="danger"
                @confirm="performDeleteInteraction" />
</template>

<style scoped>
.form-label { display:flex; flex-direction:column; gap:.5rem; font-weight:600; color:#334155 }
</style>
