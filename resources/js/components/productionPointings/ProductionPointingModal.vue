<script setup>
import { ref, watch, getCurrentInstance, nextTick } from 'vue';
import axios from 'axios';
import Modal from '@/components/Modal.vue';
import InputDatePicker from '@/components/InputDatePicker.vue';
import InputNumber from '@/components/InputNumber.vue';
import InputSelect from '@/components/InputSelect.vue';
import Checkbox from '@/components/ui/Checkbox.vue';
import Button from '@/components/Button.vue';
import DataTable from '@/components/DataTable.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  productionPointingId: { type: [Number, String, null], default: null },
  // Option sources
  blockTypes: { type: Array, default: () => [] },
  moldTypes: { type: Array, default: () => [] },
  operators: { type: Array, default: () => [] },
  silos: { type: Array, default: () => [] },
  // Request info to display
  requestSheetNumber: { type: [Number, String, null], default: null },
});

const emit = defineEmits(['update:modelValue', 'submit']);

const open = ref(props.modelValue);
watch(() => props.modelValue, (v) => { open.value = v; });
watch(open, (v) => emit('update:modelValue', v));

// Ziggy route helper if available
const instance = getCurrentInstance();
const route = instance?.appContext?.config?.globalProperties?.route;

// Tipo selecionado: 'blocks' | 'moldeds'
const productType = ref('blocks');
const blocksFormRef = ref(null);
const moldedFormRef = ref(null);
const sheetBlockRef = ref(null);
const sheetMoldedRef = ref(null);

const form = ref({
  started_at: null,
  ended_at: null,
  sheet_number: null,
  weight: null,
  block_type_id: null,
  height: null,
  length: 4060,
  width: 1020,
  enable_dimension_customization: false,
  operator_ids: [],
  silo_ids: [],
  errors: {},
  processing: false,
});

const blockTypeOptions = () => props.blockTypes.map(b => ({ value: b.id, label: b.name }));
const moldTypeOptions = () => props.moldTypes.map(m => ({ value: m.id, label: m.name }));

// Local list of entries and sequencer
const entries = ref([]);
let seqCounter = 0;
const loadingEntries = ref(false);

const nf0 = new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
const nf2 = new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const nf3 = new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 3, maximumFractionDigits: 3 });

function formatDateTimeBR(value) {
  if (!value) return '-';
  const d = new Date(value.replace(' ', 'T'));
  if (Number.isNaN(d.getTime())) return String(value);
  try {
    return new Intl.DateTimeFormat('pt-BR', {
      day: '2-digit', month: '2-digit', year: 'numeric',
      hour: '2-digit', minute: '2-digit', hour12: false,
    }).format(d);
  } catch (_) {
    const dd = String(d.getDate()).padStart(2, '0');
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const yyyy = d.getFullYear();
    const hh = String(d.getHours()).padStart(2, '0');
    const mi = String(d.getMinutes()).padStart(2, '0');
    return `${dd}/${mm}/${yyyy} ${hh}:${mi}`;
  }
}

const resolveBlockType = (id) => props.blockTypes.find(b => b.id === id) || null;
const namesFromIds = (list, ids) => list.filter(x => ids.includes(x.id)).map(x => x.name);

// Moldados: estado, carregamento, ações, colunas
const formMolded = ref({
  started_at: null,
  ended_at: null,
  sheet_number: null,
  mold_type_id: null,
  quantity: null,
  package_weight: null,
  package_quantity: null,
  // Personalização do fator de perda de peso
  enable_loss_factor_customization: false,
  loss_factor: 0.42,
  operator_ids: [],
  silo_ids: [],
  errors: {},
  processing: false,
});
const entriesMolded = ref([]);
let seqMolded = 0;
const loadingMolded = ref(false);

async function loadEntries() {
  if (!props.productionPointingId) return;
  try {
    loadingEntries.value = true;
    const url = route ? route('production-pointings.block-productions.index', props.productionPointingId) : `/admin/production-pointings/${props.productionPointingId}/block-productions`;
    const { data } = await axios.get(url);
    const list = Array.isArray(data.data) ? data.data : [];
    entries.value = list.map((it, i) => ({ ...it, seq: i + 1 }));
    seqCounter = entries.value.length;
  } catch (_) {
    // ignore; keep empty state
  } finally {
    loadingEntries.value = false;
  }
}

async function loadMoldedEntries() {
  if (!props.productionPointingId) return;
  try {
    loadingMolded.value = true;
    const url = route ? route('production-pointings.molded-productions.index', props.productionPointingId) : `/admin/production-pointings/${props.productionPointingId}/molded-productions`;
    const { data } = await axios.get(url);
    const list = Array.isArray(data.data) ? data.data : [];
    entriesMolded.value = list.map((it, i) => ({ ...it, seq: i + 1 }));
    seqMolded = entriesMolded.value.length;
  } catch (_) {
  } finally {
    loadingMolded.value = false;
  }
}

function nowYMDHM() {
  const d = new Date()
  const pad = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}`
}

function ensureDefaultTimes() {
  if (!form.value.started_at) form.value.started_at = nowYMDHM()
  if (!form.value.ended_at) form.value.ended_at = nowYMDHM()
  if (!formMolded.value.started_at) formMolded.value.started_at = nowYMDHM()
  if (!formMolded.value.ended_at) formMolded.value.ended_at = nowYMDHM()
}

watch(open, (v) => {
  if (v) {
    ensureDefaultTimes();
    loadEntries();
    loadMoldedEntries();
    nextTick(() => {
      setTimeout(() => {
        if (productType.value === 'blocks') sheetBlockRef.value?.focus?.();
        else sheetMoldedRef.value?.focus?.();
      }, 10);
    });
  } else {
    // Resetar estado quando modal for fechado
    resetModalState();
  }
});
watch(() => props.requestSheetNumber, () => { if (open.value) { loadEntries(); loadMoldedEntries(); } });
watch(productType, (val) => {
  if (open.value) {
    if (val === 'blocks') {
      resetBlocksForm();
      nextTick(() => {
        setTimeout(() => {
          sheetBlockRef.value?.focus?.();
        }, 10);
      });
    } else {
      resetMoldedForm();
      nextTick(() => {
        setTimeout(() => {
          sheetMoldedRef.value?.focus?.();
        }, 10);
      });
    }
  }
});
// Sempre que desabilitar a personalização, restaura valores padrão
watch(() => form.value.enable_dimension_customization, (v) => {
  if (!v) {
    form.value.length = 4060;
    form.value.width = 1020;
  }
});
// Moldados: quando desabilitar o fator, restaura 0,42
watch(() => formMolded.value.enable_loss_factor_customization, (v) => {
  if (!v) {
    formMolded.value.loss_factor = 0.42;
  }
});

function focusNext(kind, e) {
  const root = kind === 'blocks' ? blocksFormRef.value : moldedFormRef.value;
  if (!root) return;
  // If Enter pressed while focusing the submit button, trigger save
  const target = e?.target;
  const tag = target?.tagName?.toLowerCase();
  const btnType = target?.getAttribute?.('type');
  if (tag === 'button' && (btnType === 'submit')) {
    if (kind === 'blocks') handleSubmit(); else handleSubmitMolded();
    return;
  }
  const focusable = Array.from(root.querySelectorAll('input, select, textarea, button, [tabindex]'))
    .filter(el => !el.hasAttribute('disabled') && el.tabIndex !== -1 && el.offsetParent !== null);
  const tgt = e?.target || document.activeElement;
  // find index by equality or containment (for wrapped inputs)
  const idx = focusable.findIndex(el => el === tgt || el.contains(tgt));
  const next = focusable[idx + 1] || focusable[0];
  next?.focus?.();
}


function validateBlockForm() {
  const errors = {};
  if (!form.value.started_at) errors.started_at = 'Informe a data de início.';
  if (!form.value.ended_at) errors.ended_at = 'Informe a data de fim.';
  if (!form.value.sheet_number) errors.sheet_number = 'Informe o número da ficha.';
  if (!form.value.weight || Number(form.value.weight) <= 0) errors.weight = 'Informe o peso.';
  if (!form.value.block_type_id) errors.block_type_id = 'Selecione o tipo de bloco.';
  if (!form.value.height || Number(form.value.height) <= 0) errors.height = 'Informe a altura.';
  if (!form.value.operator_ids.length) errors.operator_ids = 'Selecione ao menos um operador.';
  if (!form.value.silo_ids.length) errors.silo_ids = 'Selecione ao menos um silo.';
  return errors;
}

const handleSubmit = async () => {
  form.value.errors = {};
  const errors = validateBlockForm();
  if (Object.keys(errors).length) {
    form.value.errors = errors;
    return;
  }
  // ...existing code...
  const bt = resolveBlockType(form.value.block_type_id);
  const virginPct = bt?.raw_material_percentage != null ? Number(bt.raw_material_percentage) : 0;
  const recycledPct = Math.max(0, 100 - virginPct);
  const totalKg = Number(form.value.weight) || 0;
  const virginKg = totalKg * (virginPct / 100);
  const recycledKg = totalKg * (recycledPct / 100);
  const useCustomDims = !!form.value.enable_dimension_customization;
  const lengthMm = useCustomDims ? (Number(form.value.length) || 0) : 4060;
  const widthMm = useCustomDims ? (Number(form.value.width) || 0) : 1020;
  const heightMm = Number(form.value.height) || 0;
  const m3 = (lengthMm/1000) * (widthMm/1000) * (heightMm/1000);
  const density = m3 > 0 ? totalKg / m3 : null;

  try {
    form.value.processing = true;
    const isEditing = !!editingBlockId.value;
    const url = isEditing
      ? (route ? route('production-pointings.block-productions.update', { productionPointing: props.productionPointingId, blockProduction: editingBlockId.value }) : `/admin/production-pointings/${props.productionPointingId}/block-productions/${editingBlockId.value}`)
      : (route ? route('production-pointings.block-productions.store', props.productionPointingId) : `/admin/production-pointings/${props.productionPointingId}/block-productions`);
    const method = isEditing ? 'patch' : 'post';
    const payload = {
      started_at: form.value.started_at,
      ended_at: form.value.ended_at,
      sheet_number: form.value.sheet_number,
      weight: totalKg,
      block_type_id: form.value.block_type_id,
      length_mm: useCustomDims ? lengthMm : 4060,
      width_mm: useCustomDims ? widthMm : 1020,
      height_mm: heightMm,
      dimension_customization_enabled: useCustomDims,
      operator_ids: form.value.operator_ids,
      silo_ids: form.value.silo_ids,
    };
    const { data } = await axios[method](url, payload);
    if (isEditing) {
      const idx = entries.value.findIndex(e => e.id === editingBlockId.value);
      if (idx !== -1) {
        const updated = { ...entries.value[idx], ...data.blockProduction };
        updated.seq = entries.value[idx].seq;
        entries.value.splice(idx, 1, updated);
      }
      editingBlockId.value = null;
    } else {
      // Append new
      const nextSeq = entries.value.length + 1;
      const entry = { ...data.blockProduction, id: data.blockProduction.id, seq: nextSeq };
      entries.value = [...entries.value, entry];
      seqCounter = nextSeq;
    }
    // Reset minimal fields for next input (keep defaults and times if desired)
    form.value.sheet_number = null;
    form.value.weight = null;
    form.value.block_type_id = null;
    form.value.height = null;
    form.value.operator_ids = [];
    form.value.silo_ids = [];
  } catch (e) {
    form.value.errors = { general: 'Erro inesperado ao salvar.' };
  } finally {
    form.value.processing = false;
  }
};

const columns = [
  { header: 'Sequência', key: 'seq', formatter: (v) => v != null ? nf0.format(v) : '-' },
  { header: 'Início', key: 'started_at', formatter: (v) => formatDateTimeBR(v) },
  { header: 'Fim', key: 'ended_at', formatter: (v) => formatDateTimeBR(v) },
  { header: 'Ficha', key: 'sheet_number', formatter: (v) => v != null ? nf0.format(v) : '-' },
  { header: 'Peso total (kg)', key: 'weight', formatter: (v) => v != null ? nf2.format(v) : '-' },
  { header: 'Peso MP virgem (kg)', key: 'virgin_kg', formatter: (v) => v != null ? nf2.format(v) : '-' },
  { header: 'Peso MP reciclada (kg)', key: 'recycled_kg', formatter: (v) => v != null ? nf2.format(v) : '-' },
  { header: 'Densidade (kg/m³)', key: 'density', formatter: (v) => v != null ? nf2.format(v) : '-' },
  { header: 'Tipo do bloco', key: 'block_type_name' },
  { header: '% MP virgem', key: 'virgin_pct', formatter: (v) => v != null ? `${nf2.format(v)}%` : '-' },
  { header: '% MP reciclada', key: 'recycled_pct', formatter: (v) => v != null ? `${nf2.format(v)}%` : '-' },
  { header: 'Comprimento (mm)', key: 'length_mm', formatter: (v) => v != null ? nf0.format(v) : '-' },
  { header: 'Largura (mm)', key: 'width_mm', formatter: (v) => v != null ? nf0.format(v) : '-' },
  { header: 'Altura (mm)', key: 'height_mm', formatter: (v) => v != null ? nf0.format(v) : '-' },
  { header: 'm³', key: 'm3', formatter: (v) => v != null ? nf3.format(v) : '-' },
  { header: 'Silos', key: 'silo_names', formatter: (v) => Array.isArray(v) ? v.join(', ') : '-' },
  { header: 'Operadores', key: 'operator_names', formatter: (v) => Array.isArray(v) ? v.join(', ') : '-' },
];

// Dropdown actions (Blocks)
const rowActionsBlocks = () => ([
  { key: 'edit-block', label: 'Editar', icon: 'pencil' },
  { key: 'delete-block', label: 'Remover', icon: 'trash', class: 'text-rose-600' },
]);

const confirmDelete = ref({ open: false, processing: false, entry: null });
const editingBlockId = ref(null);

const handleEntryAction = async ({ action, item }) => {
  if (action.key === 'delete-block') {
    confirmDelete.value = { open: true, processing: false, entry: item };
  } else if (action.key === 'edit-block') {
    productType.value = 'blocks';
    editingBlockId.value = item.id;
    // Garantir formato compatível com InputDatePicker (YYYY-MM-DD HH:mm)
    form.value.started_at = item.started_at ? String(item.started_at).replace('T', ' ').substring(0, 16) : nowYMDHM();
    form.value.ended_at = item.ended_at ? String(item.ended_at).replace('T', ' ').substring(0, 16) : nowYMDHM();
    form.value.sheet_number = item.sheet_number ?? null;
    form.value.weight = item.weight ?? null;
    form.value.block_type_id = item.block_type_id ?? null;
    form.value.height = item.height_mm ?? null;
    form.value.length = item.length_mm ?? 4060;
    form.value.width = item.width_mm ?? 1020;
    form.value.enable_dimension_customization = (form.value.length !== 4060 || form.value.width !== 1020);
    form.value.operator_ids = Array.isArray(item.operator_ids) ? [...item.operator_ids] : [];
    form.value.silo_ids = Array.isArray(item.silo_ids) ? [...item.silo_ids] : [];
  nextTick(() => sheetBlockRef.value?.focus?.());
  }
};

const performDelete = async () => {
  if (!confirmDelete.value.entry) return;
  try {
    confirmDelete.value.processing = true;
    const item = confirmDelete.value.entry;
    const url = route ? route('production-pointings.block-productions.destroy', { productionPointing: props.productionPointingId, blockProduction: item.id }) : `/admin/production-pointings/${props.productionPointingId}/block-productions/${item.id}`;
    await axios.delete(url);
    // Remove e refaz a sequência
    const remaining = entries.value.filter(e => e.id !== item.id).map((e, i) => ({ ...e, seq: i + 1 }));
    entries.value = remaining;
    seqCounter = remaining.length;
    confirmDelete.value = { open: false, processing: false, entry: null };
  } catch (e) {
    confirmDelete.value.processing = false;
  }
};

function resetBlocksForm() {
  editingBlockId.value = null;
  form.value.started_at = nowYMDHM();
  form.value.ended_at = nowYMDHM();
  form.value.sheet_number = null;
  form.value.weight = null;
  form.value.block_type_id = null;
  form.value.height = null;
  form.value.length = 4060;
  form.value.width = 1020;
  form.value.enable_dimension_customization = false;
  form.value.operator_ids = [];
  form.value.silo_ids = [];
  form.value.errors = {};
}

function cancelBlocks() {
  resetBlocksForm();
  nextTick(() => sheetBlockRef.value?.focus?.());
}

// Submissão Moldados

function validateMoldedForm() {
  const errors = {};
  if (!formMolded.value.started_at) errors.started_at = 'Informe a data de início.';
  if (!formMolded.value.ended_at) errors.ended_at = 'Informe a data de fim.';
  if (!formMolded.value.sheet_number) errors.sheet_number = 'Informe o número da ficha.';
  if (!formMolded.value.mold_type_id) errors.mold_type_id = 'Selecione o tipo de moldado.';
  if (!formMolded.value.quantity || Number(formMolded.value.quantity) <= 0) errors.quantity = 'Informe a quantidade.';
  if (!formMolded.value.package_weight || Number(formMolded.value.package_weight) <= 0) errors.package_weight = 'Informe o peso médio do pacote.';
  if (!formMolded.value.operator_ids.length) errors.operator_ids = 'Selecione ao menos um operador.';
  if (!formMolded.value.silo_ids.length) errors.silo_ids = 'Selecione ao menos um silo.';
  return errors;
}

const handleSubmitMolded = async () => {
  formMolded.value.errors = {};
  const errors = validateMoldedForm();
  if (Object.keys(errors).length) {
    formMolded.value.errors = errors;
    return;
  }
  // ...existing code...
  try {
    formMolded.value.processing = true;
    const isEditing = !!editingMoldedId.value;
    const url = isEditing
      ? (route ? route('production-pointings.molded-productions.update', { productionPointing: props.productionPointingId, moldedProduction: editingMoldedId.value }) : `/admin/production-pointings/${props.productionPointingId}/molded-productions/${editingMoldedId.value}`)
      : (route ? route('production-pointings.molded-productions.store', props.productionPointingId) : `/admin/production-pointings/${props.productionPointingId}/molded-productions`);
    const payload = {
      started_at: formMolded.value.started_at,
      ended_at: formMolded.value.ended_at,
      sheet_number: formMolded.value.sheet_number,
      mold_type_id: formMolded.value.mold_type_id,
      quantity: formMolded.value.quantity,
      package_weight: formMolded.value.package_weight,
      package_quantity: formMolded.value.package_quantity,
      loss_factor_enabled: !!formMolded.value.enable_loss_factor_customization,
      operator_ids: formMolded.value.operator_ids,
      silo_ids: formMolded.value.silo_ids,
    };
    // Envia o fator apenas quando habilitado; caso contrário o backend usará 0,42
    if (formMolded.value.enable_loss_factor_customization) {
      payload.loss_factor = Number(formMolded.value.loss_factor)
    }
    const method = isEditing ? 'patch' : 'post';
    const { data } = await axios[method](url, payload);
    if (isEditing) {
      const idx = entriesMolded.value.findIndex(e => e.id === editingMoldedId.value);
      if (idx !== -1) {
        const updated = { ...entriesMolded.value[idx], ...data.moldedProduction };
        updated.seq = entriesMolded.value[idx].seq;
        entriesMolded.value.splice(idx, 1, updated);
      }
      editingMoldedId.value = null;
    } else {
      const nextSeq = entriesMolded.value.length + 1;
      const entry = { ...data.moldedProduction, id: data.moldedProduction.id, seq: nextSeq };
      entriesMolded.value = [...entriesMolded.value, entry];
      seqMolded = nextSeq;
    }
    // reset
    formMolded.value.sheet_number = null;
    formMolded.value.mold_type_id = null;
    formMolded.value.quantity = null;
    formMolded.value.package_weight = null;
    formMolded.value.package_quantity = null;
    formMolded.value.operator_ids = [];
    formMolded.value.silo_ids = [];
  } catch (e) {
    formMolded.value.errors = { general: 'Erro ao salvar.' };
  } finally {
    formMolded.value.processing = false;
  }
};

// Dropdown actions (Moldeds)
const rowActionsMolded = () => ([
  { key: 'edit-molded', label: 'Editar', icon: 'pencil' },
  { key: 'delete-molded', label: 'Remover', icon: 'trash', class: 'text-rose-600' },
]);
const confirmDeleteMolded = ref({ open: false, processing: false, entry: null });
const editingMoldedId = ref(null);
const handleEntryActionMolded = ({ action, item }) => {
  if (action.key === 'delete-molded') {
    confirmDeleteMolded.value = { open: true, processing: false, entry: item };
  } else if (action.key === 'edit-molded') {
    productType.value = 'moldeds';
    editingMoldedId.value = item.id;
    // Garantir formato compatível com InputDatePicker (YYYY-MM-DD HH:mm)
    formMolded.value.started_at = item.started_at ? String(item.started_at).replace('T', ' ').substring(0, 16) : nowYMDHM();
    formMolded.value.ended_at = item.ended_at ? String(item.ended_at).replace('T', ' ').substring(0, 16) : nowYMDHM();
    formMolded.value.sheet_number = item.sheet_number ?? null;
    formMolded.value.mold_type_id = item.mold_type_id ?? null;
    formMolded.value.quantity = item.quantity ?? null;
    formMolded.value.package_weight = item.package_weight ?? null;
    formMolded.value.enable_loss_factor_customization = !!item.loss_factor_enabled;
    formMolded.value.loss_factor = typeof item.loss_factor === 'number' ? item.loss_factor : 0.42;
    formMolded.value.operator_ids = Array.isArray(item.operator_ids) ? [...item.operator_ids] : [];
    formMolded.value.silo_ids = Array.isArray(item.silo_ids) ? [...item.silo_ids] : [];
  nextTick(() => sheetMoldedRef.value?.focus?.());
  }
};
const performDeleteMolded = async () => {
  if (!confirmDeleteMolded.value.entry) return;
  try {
    confirmDeleteMolded.value.processing = true;
    const item = confirmDeleteMolded.value.entry;
    const url = route ? route('production-pointings.molded-productions.destroy', { productionPointing: props.productionPointingId, moldedProduction: item.id }) : `/admin/production-pointings/${props.productionPointingId}/molded-productions/${item.id}`;
    await axios.delete(url);
    const remaining = entriesMolded.value.filter(e => e.id !== item.id).map((e, i) => ({ ...e, seq: i + 1 }));
    entriesMolded.value = remaining;
    seqMolded = remaining.length;
    confirmDeleteMolded.value = { open: false, processing: false, entry: null };
  } catch (e) {
    confirmDeleteMolded.value.processing = false;
  }
};

function resetMoldedForm() {
  editingMoldedId.value = null;
  formMolded.value.started_at = nowYMDHM();
  formMolded.value.ended_at = nowYMDHM();
  formMolded.value.sheet_number = null;
  formMolded.value.mold_type_id = null;
  formMolded.value.quantity = null;
  formMolded.value.package_weight = null;
  formMolded.value.package_quantity = null;
  formMolded.value.operator_ids = [];
  formMolded.value.silo_ids = [];
  formMolded.value.enable_loss_factor_customization = false;
  formMolded.value.loss_factor = 0.42;
  formMolded.value.errors = {};
}

function cancelMolded() {
  resetMoldedForm();
  nextTick(() => sheetMoldedRef.value?.focus?.());
}

function resetModalState() {
  // Resetar tipo de produto para blocos
  productType.value = 'blocks';

  // Resetar formulários
  resetBlocksForm();
  resetMoldedForm();

  // Limpar listas de apontamentos
  entries.value = [];
  entriesMolded.value = [];
  seqCounter = 0;
  seqMolded = 0;

  // Limpar estados de loading
  loadingEntries.value = false;
  loadingMolded.value = false;

  // Limpar modais de confirmação
  confirmDelete.value = { open: false, processing: false, entry: null };
  confirmDeleteMolded.value = { open: false, processing: false, entry: null };

  // Limpar IDs de edição
  editingBlockId.value = null;
  editingMoldedId.value = null;
}

// Colunas Moldados
const columnsMolded = [
  { header: 'Tipo do moldado', key: 'mold_type_name' },
  { header: 'Início', key: 'started_at', formatter: (v) => formatDateTimeBR(v) },
  { header: 'Fim', key: 'ended_at', formatter: (v) => formatDateTimeBR(v) },
  { header: 'Ficha', key: 'sheet_number', formatter: (v) => v != null ? nf0.format(v) : '-' },
  { header: 'Quantidade (unid.)', key: 'quantity', formatter: (v) => v != null ? nf0.format(v) : '-' },
  { header: 'Peso médio do pacote (kg)', key: 'package_weight', formatter: (v) => v != null ? nf2.format(v) : '-' },
  { header: 'Peso unitário considerado (kg)', key: 'weight_considered_unit', formatter: (v) => v != null ? nf3.format(v) : '-' },
  { header: 'Peso total considerado (kg)', key: 'total_weight_considered', formatter: (v) => v != null ? nf2.format(v) : '-' },
  { header: 'Silos', key: 'silo_names', formatter: (v) => Array.isArray(v) ? v.join(', ') : '-' },
  { header: 'Operadores', key: 'operator_names', formatter: (v) => Array.isArray(v) ? v.join(', ') : '-' },
];
</script>

<template>
  <Modal
    v-model="open"
    title="Apontamento de produção"
    size="2xl"
    :lockScroll="true"
    :closeOnBackdrop="false"
  >
    <div class="mb-4 rounded-lg border border-slate-200 bg-slate-50 p-3">
      <div class="grid gap-2 sm:grid-cols-2">
        <div class="text-sm text-slate-700"><span class="font-semibold">ID da requisição:</span> {{ productionPointingId ?? '—' }}</div>
        <div class="text-sm text-slate-700"><span class="font-semibold">Ficha da requisição:</span> {{ requestSheetNumber ?? '—' }}</div>
      </div>
    </div>

    <div class="mb-4">
      <label class="form-label mb-2">Tipo de produto</label>
      <div class="flex">
        <Button
          type="button"
          @click="productType = 'blocks'"
          :variant="productType === 'blocks' ? 'primary' : 'outline'"
          class="flex-1 rounded-r-none border-r-0 hover:translate-y-0 hover:shadow-none"
        >
          Blocos
        </Button>
        <Button
          type="button"
          @click="productType = 'moldeds'"
          :variant="productType === 'moldeds' ? 'primary' : 'outline'"
          class="flex-1 rounded-l-none hover:translate-y-0 hover:shadow-none"
        >
          Moldados
        </Button>
      </div>
  </div>

    <form v-if="productType === 'blocks'" ref="blocksFormRef" class="space-y-6" @submit.prevent="handleSubmit" @keydown.enter.prevent="focusNext('blocks', $event)">
      <!-- Toggle de personalização de dimensão -->
      <div class="flex items-center">
        <Checkbox v-model="form.enable_dimension_customization">
          Habilitar personalização de dimensão
        </Checkbox>
      </div>
      <!-- Linha 1: Início, Fim, Ficha -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <label class="form-label">
          Número da ficha *
          <InputNumber ref="sheetBlockRef" v-model="form.sheet_number" :formatted="true" :precision="0" :min="1" :step="1" placeholder="0" required :error="!!form.errors.sheet_number" />
          <span v-if="form.errors.sheet_number" class="text-sm font-medium text-rose-600">{{ form.errors.sheet_number }}</span>
        </label>

        <label class="form-label">
          Início *
          <InputDatePicker v-model="form.started_at" :withTime="true" :allowManualInput="true" required :error="!!form.errors.started_at" />
          <span v-if="form.errors.started_at" class="text-sm font-medium text-rose-600">{{ form.errors.started_at }}</span>
        </label>

        <label class="form-label">
          Fim *
          <InputDatePicker v-model="form.ended_at" :withTime="true" :allowManualInput="true" required :error="!!form.errors.ended_at" />
          <span v-if="form.errors.ended_at" class="text-sm font-medium text-rose-600">{{ form.errors.ended_at }}</span>
        </label>

        <label class="form-label">
          Tipo de bloco *
          <InputSelect v-model="form.block_type_id" :options="blockTypeOptions()" placeholder="Selecione o tipo" required :error="!!form.errors.block_type_id" />
          <span v-if="form.errors.block_type_id" class="text-sm font-medium text-rose-600">{{ form.errors.block_type_id }}</span>
        </label>
      </div>

      <!-- Linha 2: Tipo de bloco, Peso, Comprimento, Largura, Altura -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <label class="form-label">
          Peso (kg) *
          <InputNumber v-model="form.weight" :formatted="true" :precision="2" :min="0.01" :step="0.01" placeholder="0,00" required :error="!!form.errors.weight" />
          <span v-if="form.errors.weight" class="text-sm font-medium text-rose-600">{{ form.errors.weight }}</span>
        </label>

        <label class="form-label">
          Comprimento (mm)
          <InputNumber v-model="form.length" :formatted="true" :precision="0" :min="1" :step="1" placeholder="0" :disabled="!form.enable_dimension_customization" />
        </label>

        <label class="form-label">
          Largura (mm)
          <InputNumber v-model="form.width" :formatted="true" :precision="0" :min="1" :step="1" placeholder="0" :disabled="!form.enable_dimension_customization" />
        </label>

        <label class="form-label">
          Altura (mm) *
          <InputNumber v-model="form.height" :formatted="true" :precision="0" :min="1" :step="1" placeholder="0" required :error="!!form.errors.height" />
          <span v-if="form.errors.height" class="text-sm font-medium text-rose-600">{{ form.errors.height }}</span>
        </label>
      </div>

      <div class="space-y-3">
        <h3 class="text-sm font-semibold text-slate-600">Silos *</h3>
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
          <Checkbox v-for="silo in silos" :key="silo.id" v-model="form.silo_ids" :value="silo.id" class="w-full">
            {{ silo.name }}
          </Checkbox>
          <p v-if="silos.length === 0" class="text-sm text-slate-500 sm:col-span-2 lg:col-span-3">Nenhum silo cadastrado.</p>
        </div>
        <span v-if="form.errors.silo_ids" class="text-sm font-medium text-rose-600">{{ form.errors.silo_ids }}</span>
      </div>

      <div class="space-y-3">
        <h3 class="text-sm font-semibold text-slate-600">Operadores *</h3>
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
          <Checkbox v-for="operator in operators" :key="operator.id" v-model="form.operator_ids" :value="operator.id" class="w-full">
            {{ operator.name }}
          </Checkbox>
          <p v-if="operators.length === 0" class="text-sm text-slate-500 sm:col-span-2 lg:col-span-3">Nenhum operador cadastrado.</p>
        </div>
        <span v-if="form.errors.operator_ids" class="text-sm font-medium text-rose-600">{{ form.errors.operator_ids }}</span>
      </div>

      <div class="flex justify-end gap-2">
        <Button variant="ghost" type="button" @click="cancelBlocks">Cancelar</Button>
        <Button variant="primary" type="submit" :loading="form.processing">Salvar</Button>
      </div>
    </form>

    <div v-if="productType === 'blocks'" class="mt-6">
      <h3 class="text-base font-semibold text-slate-800 mb-2">Apontamentos realizados</h3>
      <div v-if="loadingEntries" class="space-y-2" aria-busy="true" aria-live="polite">
        <div class="skeleton h-6 w-48 rounded-md"></div>
        <div class="skeleton h-10 w-full rounded-md"></div>
        <div class="skeleton h-10 w-full rounded-md"></div>
        <div class="skeleton h-10 w-3/4 rounded-md"></div>
        <span class="sr-only">Carregando apontamentos realizados…</span>
      </div>
      <DataTable
        v-else
        :columns="columns"
        :data="entries"
        :actions="rowActionsBlocks"
        :sticky-actions="true"
        row-key="id"
        empty-message="Nenhum apontamento lançado ainda."
        @action="handleEntryAction"
      />

      <ConfirmModal
        v-model="confirmDelete.open"
        :processing="confirmDelete.processing"
        title="Remover apontamento"
        :message="confirmDelete.entry ? `Deseja realmente remover o apontamento #${confirmDelete.entry.seq}?` : ''"
        confirm-text="Remover"
        variant="danger"
        @confirm="performDelete"
      />
    </div>

    <form v-else ref="moldedFormRef" class="space-y-6" @submit.prevent="handleSubmitMolded" @keydown.enter.prevent="focusNext('moldeds', $event)">
      <!-- Toggle de personalização do fator de perda de peso -->
      <div class="flex items-center">
        <Checkbox v-model="formMolded.enable_loss_factor_customization">
          Habilitar personalização do fator de perda de peso
        </Checkbox>
      </div>

      <!-- Linha 1: Número da ficha, Início, Fim, Tipo de moldado -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <label class="form-label">
          Número da ficha *
          <InputNumber ref="sheetMoldedRef" v-model="formMolded.sheet_number" :formatted="true" :precision="0" :min="1" :step="1" placeholder="0" required :error="!!formMolded.errors.sheet_number" />
          <span v-if="formMolded.errors.sheet_number" class="text-sm font-medium text-rose-600">{{ formMolded.errors.sheet_number }}</span>
        </label>

        <label class="form-label">
          Início *
          <InputDatePicker v-model="formMolded.started_at" :withTime="true" :allowManualInput="true" required :error="!!formMolded.errors.started_at" />
          <span v-if="formMolded.errors.started_at" class="text-sm font-medium text-rose-600">{{ formMolded.errors.started_at }}</span>
        </label>

        <label class="form-label">
          Fim *
          <InputDatePicker v-model="formMolded.ended_at" :withTime="true" :allowManualInput="true" required :error="!!formMolded.errors.ended_at" />
          <span v-if="formMolded.errors.ended_at" class="text-sm font-medium text-rose-600">{{ formMolded.errors.ended_at }}</span>
        </label>

        <label class="form-label">
          Tipo de moldado *
          <InputSelect v-model="formMolded.mold_type_id" :options="moldTypeOptions()" placeholder="Selecione o tipo" required :error="!!formMolded.errors.mold_type_id" />
          <span v-if="formMolded.errors.mold_type_id" class="text-sm font-medium text-rose-600">{{ formMolded.errors.mold_type_id }}</span>
        </label>
      </div>

      <!-- Linha 2: Quantidade, Peso médio do pacote, Fator de perda de peso -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <label class="form-label">
          Quantidade (unid.) *
          <InputNumber v-model="formMolded.quantity" :formatted="true" :precision="0" :min="1" :step="1" placeholder="0" required :error="!!formMolded.errors.quantity" />
          <span v-if="formMolded.errors.quantity" class="text-sm font-medium text-rose-600">{{ formMolded.errors.quantity }}</span>
        </label>

        <label class="form-label">
          Peso médio do pacote (kg) *
          <InputNumber v-model="formMolded.package_weight" :formatted="true" :precision="2" :min="0.01" :step="0.01" placeholder="0,00" required :error="!!formMolded.errors.package_weight" />
          <span v-if="formMolded.errors.package_weight" class="text-sm font-medium text-rose-600">{{ formMolded.errors.package_weight }}</span>
        </label>

        <label class="form-label">
          Fator de perda de peso
          <InputNumber v-model="formMolded.loss_factor" :formatted="true" :precision="2" :min="0" :max="1" :step="0.01" placeholder="0,00" :disabled="!formMolded.enable_loss_factor_customization" />
        </label>
      </div>

      <div class="space-y-3">
        <h3 class="text-sm font-semibold text-slate-600">Silos *</h3>
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
          <Checkbox v-for="silo in silos" :key="silo.id" v-model="formMolded.silo_ids" :value="silo.id" class="w-full">
            {{ silo.name }}
          </Checkbox>
          <p v-if="silos.length === 0" class="text-sm text-slate-500 sm:col-span-2 lg:col-span-3">Nenhum silo cadastrado.</p>
        </div>
        <span v-if="formMolded.errors.silo_ids" class="text-sm font-medium text-rose-600">{{ formMolded.errors.silo_ids }}</span>
      </div>

      <div class="space-y-3">
        <h3 class="text-sm font-semibold text-slate-600">Operadores *</h3>
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
          <Checkbox v-for="operator in operators" :key="operator.id" v-model="formMolded.operator_ids" :value="operator.id" class="w-full">
            {{ operator.name }}
          </Checkbox>
          <p v-if="operators.length === 0" class="text-sm text-slate-500 sm:col-span-2 lg:col-span-3">Nenhum operador cadastrado.</p>
        </div>
        <span v-if="formMolded.errors.operator_ids" class="text-sm font-medium text-rose-600">{{ formMolded.errors.operator_ids }}</span>
      </div>

      <div class="flex justify-end gap-2">
        <Button variant="ghost" type="button" @click="cancelMolded">Cancelar</Button>
        <Button variant="primary" type="submit" :loading="formMolded.processing">Salvar</Button>
      </div>
    </form>

    <div v-if="productType === 'moldeds'" class="mt-6">
      <h3 class="text-base font-semibold text-slate-800 mb-2">Apontamentos realizados</h3>
      <div v-if="loadingMolded" class="space-y-2" aria-busy="true" aria-live="polite">
        <div class="skeleton h-6 w-48 rounded-md"></div>
        <div class="skeleton h-10 w-full rounded-md"></div>
        <div class="skeleton h-10 w-full rounded-md"></div>
        <div class="skeleton h-10 w-3/4 rounded-md"></div>
        <span class="sr-only">Carregando apontamentos realizados…</span>
      </div>
      <DataTable
        v-else
        :columns="columnsMolded"
        :data="entriesMolded"
        :actions="rowActionsMolded"
        :sticky-actions="true"
        row-key="id"
        empty-message="Nenhum apontamento lançado ainda."
        @action="handleEntryActionMolded"
      />

      <ConfirmModal
        v-model="confirmDeleteMolded.open"
        :processing="confirmDeleteMolded.processing"
        title="Remover apontamento"
        :message="confirmDeleteMolded.entry ? `Deseja realmente remover o apontamento #${confirmDeleteMolded.entry.seq}?` : ''"
        confirm-text="Remover"
        variant="danger"
        @confirm="performDeleteMolded"
      />
    </div>
  </Modal>
</template>

<style scoped>
.form-label { display:flex; flex-direction:column; gap:.5rem; font-weight:600; color:#334155 }
</style>
