<script setup>
import { defineAsyncComponent } from 'vue';
const TimelineScroll = defineAsyncComponent(() => import('@/components/timeline/TimelineScroll.vue'));
import Button from '@/components/Button.vue';
import InputText from '@/components/InputText.vue';
import InputSelect from '@/components/InputSelect.vue';
import InputTextarea from '@/components/InputTextarea.vue';
import InputDatePicker from '@/components/InputDatePicker.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import HeroIcon from '@/components/icons/HeroIcon.vue';
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

// Computed para interações ordenadas (mais novo primeiro)
const sortedInteractions = computed(() => {
  if (!props.form.interactions) return [];
  return [...props.form.interactions].sort((a, b) => {
    const dateA = new Date(a.interacted_at || a.created_at);
    const dateB = new Date(b.interacted_at || b.created_at);
    return dateB.getTime() - dateA.getTime(); // Mais novo primeiro
  });
});

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
    message: 'Mensagem',
    visit: 'Visita',
    other: 'Outro'
  };
  return types[type] || type;
};

const getTypeIcon = (type) => {
  const icons = {
    phone_call: 'phone',
    email: 'envelope',
    meeting: 'user-group',
    message: 'device-phone-mobile',
    visit: 'home',
    other: 'chat-bubble-oval-left-ellipsis'
  };
  return icons[type] || 'chat-bubble-oval-left-ellipsis';
};

const getTypeIconClass = (type) => {
  const classes = {
    phone_call: 'text-green-600',
    email: 'text-red-600',
    meeting: 'text-purple-600',
    message: 'text-blue-600',
    visit: 'text-orange-600',
    other: 'text-slate-500'
  };
  return classes[type] || 'text-slate-500';
};

const formatInteractionDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  if (Number.isNaN(date.getTime())) return dateString;

  const now = new Date();
  const diffInHours = (now - date) / (1000 * 60 * 60);

  if (diffInHours < 24) {
    // Menos de 24 horas - mostrar "há X horas" ou "há X minutos"
    if (diffInHours < 1) {
      const diffInMinutes = Math.floor((now - date) / (1000 * 60));
      return diffInMinutes <= 1 ? 'agora mesmo' : `há ${diffInMinutes}min`;
    }
    const hours = Math.floor(diffInHours);
    return hours === 1 ? 'há 1h' : `há ${hours}h`;
  } else if (diffInHours < 24 * 7) {
    // Menos de 7 dias - mostrar dia da semana
    const days = ['dom', 'seg', 'ter', 'qua', 'qui', 'sex', 'sáb'];
    return days[date.getDay()];
  } else {
    // Mais antigo - mostrar data
    return date.toLocaleDateString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
    });
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
  <form @submit.prevent="onSubmit" class="space-y-6 w-full">
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

  <fieldset class="space-y-3 max-w-full w-full min-w-0 box-border">
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
              { value: 'message', label: 'Mensagem' },
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

      <!-- Linha do tempo horizontal de interações -->
      <div v-if="(form.interactions || []).length > 0" class="mt-4 min-w-0">
        <TimelineScroll aria-label="Linha do tempo de interações">
          <div
            v-for="(interaction, index) in sortedInteractions"
            :key="interaction.id || index"
            class="relative flex flex-col items-center flex-shrink-0 min-w-72 max-w-80"
          >
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 w-full transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 group">
              <div class="flex justify-between items-center mb-3">
                <div class="flex items-center gap-2">
                  <HeroIcon :name="getTypeIcon(interaction.type)" class="w-5 h-5" :class="getTypeIconClass(interaction.type)" />
                  <span class="font-semibold text-sm text-slate-700">{{ interaction.type_label || getTypeLabel(interaction.type) }}</span>
                </div>
                <div class="flex gap-1 opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                  <button
                    type="button"
                    @click="editInteraction(props.form.interactions.indexOf(interaction))"
                    class="p-1.5 rounded-md hover:bg-blue-50 hover:text-blue-600 transition-colors duration-200"
                    title="Editar interação"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </button>
                  <button
                    type="button"
                    @click="deleteInteraction(props.form.interactions.indexOf(interaction))"
                    class="p-1.5 rounded-md hover:bg-red-50 hover:text-red-600 transition-colors duration-200"
                    title="Excluir interação"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </div>
              </div>
              <div class="mb-3">
                <p class="text-sm leading-relaxed text-slate-600 m-0">{{ interaction.description }}</p>
              </div>
              <div class="border-t border-slate-100 pt-3">
                <div class="flex justify-between items-center text-xs text-slate-500">
                  <span class="font-medium">{{ formatInteractionDate(interaction.interacted_at) }}</span>
                  <span class="italic">{{ interaction.created_by || 'Você' }}</span>
                </div>
              </div>
            </div>
          </div>
        </TimelineScroll>
      </div>

      <div v-else class="mt-4">
        <div class="timeline-container">
          <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
            <HeroIcon name="chat-bubble-left-right" class="w-12 h-12 text-slate-300" />
            <p class="mt-4 text-sm text-slate-500">Nenhuma interação registrada para este lead.</p>
          </div>
        </div>
      </div>      <!-- Botão para adicionar nova interação -->
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
