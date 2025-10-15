<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import Dropdown from './Dropdown.vue'
import InputSelect from './InputSelect.vue'
import Button from './Button.vue'
import { ChevronRightIcon, CalendarDaysIcon, ClockIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: {
    type: [String, Object],
    default: null
  },
  range: { type: Boolean, default: false },
  withTime: { type: Boolean, default: false },
  size: { type: String, default: 'md', validator: v => ['sm','md','lg'].includes(v) },
  placeholder: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  readonly: { type: Boolean, default: false },
  error: { type: Boolean, default: false },
  success: { type: Boolean, default: false },
  clearable: { type: Boolean, default: true },
  minDate: { type: String, default: null }, // YYYY-MM-DD (ou YYYY-MM-DD HH:mm)
  maxDate: { type: String, default: null },
  class: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue', 'change'])

// Helpers de data simples, sem libs externas
const pad2 = (n) => String(n).padStart(2, '0')
const toYMD = (d) => `${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}`
const toYMDHM = (d) => `${toYMD(d)} ${pad2(d.getHours())}:${pad2(d.getMinutes())}`
const parseDate = (s) => {
  if (!s) return null
  if (typeof s !== 'string') return null
  // suporta 'YYYY-MM-DD' e 'YYYY-MM-DD HH:mm'
  const [datePart, timePart] = s.trim().split(' ')
  const [y,m,dd] = (datePart || '').split('-').map(Number)
  if (!y || !m || !dd) return null
  let hh = 0, mm = 0
  if (timePart) {
    const [h,mi] = timePart.split(':').map(Number)
    if (Number.isFinite(h)) hh = h
    if (Number.isFinite(mi)) mm = mi
  }
  const d = new Date(y, (m-1), dd, hh, mm, 0, 0)
  if (isNaN(d.getTime())) return null
  return d
}
const startOfDay = (d) => new Date(d.getFullYear(), d.getMonth(), d.getDate(), 0,0,0,0)
const endOfDay = (d) => new Date(d.getFullYear(), d.getMonth(), d.getDate(), 23,59,0,0)
const fmt2 = (n) => pad2(Number(n) || 0)
const cellKey = (cell, suffix='') => cell.placeholder ? `ph${suffix}-${cell.pi}` : `${toYMD(cell.d)}${suffix}`

const minD = computed(() => parseDate(props.minDate))
const maxD = computed(() => parseDate(props.maxDate))
const isBefore = (a, b) => a.getTime() < b.getTime()
const isAfter = (a, b) => a.getTime() > b.getTime()
const clampDate = (d) => {
  if (!d) return d
  if (minD.value && isBefore(d, minD.value)) return new Date(minD.value)
  if (maxD.value && isAfter(d, maxD.value)) return new Date(maxD.value)
  return d
}

// Estado interno de seleção (Dropdown controla abrir/fechar)

// Visualização de mês(es)
const today = new Date()
const viewMonth = ref(new Date(today.getFullYear(), today.getMonth(), 1)) // primeiro mês

// Seleções
const singleDate = ref(null) // Date | null
const singleH = ref(0)
const singleM = ref(0)

const rangeStart = ref(null) // Date | null
const rangeEnd = ref(null)
const hoverDate = ref(null)
const startH = ref(0), startM = ref(0)
const endH = ref(0), endM = ref(0)

// Inicializar com modelValue
onMounted(() => syncFromModel())
watch(() => props.modelValue, () => syncFromModel())

function syncFromModel() {
  if (props.range) {
    const startS = props.modelValue?.start || null
    const endS = props.modelValue?.end || null
    const sd = parseDate(startS)
    const ed = parseDate(endS)
    rangeStart.value = sd
    rangeEnd.value = ed
    if (sd) { startH.value = sd.getHours(); startM.value = sd.getMinutes() }
    if (ed) { endH.value = ed.getHours(); endM.value = ed.getMinutes() }
    // Centraliza visualização
    if (sd) viewMonth.value = new Date(sd.getFullYear(), sd.getMonth(), 1)
    else if (ed) viewMonth.value = new Date(ed.getFullYear(), ed.getMonth(), 1)
  } else {
    const sd = parseDate(props.modelValue)
    singleDate.value = sd
    if (sd) { singleH.value = sd.getHours(); singleM.value = sd.getMinutes() }
    if (sd) viewMonth.value = new Date(sd.getFullYear(), sd.getMonth(), 1)
  }
}

// Classe do input (replica padrão dos inputs)
const inputClasses = computed(() => {
  const base = [
    'border','border-slate-300','rounded-lg','transition-colors','duration-200',
  ]
  if (props.disabled) base.push('bg-gray-100')
  else base.push('bg-white')
  if (!props.disabled) base.push('focus:outline-none','focus:ring-2','focus:ring-blue-500','focus:border-blue-500')
  if (props.size === 'lg') base.push('px-6','py-3','text-base')
  else if (props.size === 'sm') base.push('px-3','py-1.5','text-xs')
  else base.push('px-4','py-2','text-sm')
  if (props.disabled) base.push('cursor-not-allowed','text-slate-500')
  if (props.error) base.push('border-red-500','focus:border-red-500','focus:ring-red-500')
  else if (props.success) base.push('border-green-500','focus:border-green-500','focus:ring-green-500')
  // espaço para ícones/botões internos
  base.push('pr-9', 'w-full')
  return base.join(' ')
})
const finalClasses = computed(() => [inputClasses.value, props.class || ''].filter(Boolean).join(' '))

// Labels/formatos
const weekDays = ['D','S','T','Q','Q','S','S']
const monthNames = ['janeiro','fevereiro','março','abril','maio','junho','julho','agosto','setembro','outubro','novembro','dezembro']

const formatDateBR = (d) => `${pad2(d.getDate())}/${pad2(d.getMonth()+1)}/${d.getFullYear()}`
const formatTimeBR = (h,m) => `${pad2(h)}:${pad2(m)}`

const displayValue = computed(() => {
  if (props.range) {
    if (!rangeStart.value && !rangeEnd.value) return ''
    const left = rangeStart.value ? (props.withTime ? `${formatDateBR(rangeStart.value)} ${formatTimeBR(startH.value, startM.value)}` : formatDateBR(rangeStart.value)) : ''
    const right = rangeEnd.value ? (props.withTime ? `${formatDateBR(rangeEnd.value)} ${formatTimeBR(endH.value, endM.value)}` : formatDateBR(rangeEnd.value)) : ''
    return [left, right].filter(Boolean).join(' — ')
  } else {
    if (!singleDate.value) return ''
    return props.withTime
      ? `${formatDateBR(singleDate.value)} ${formatTimeBR(singleH.value, singleM.value)}`
      : `${formatDateBR(singleDate.value)}`
  }
})

// Dias do mês, com placeholders iniciais para alinhar a semana
function monthDays(firstOfMonth) {
  const first = new Date(firstOfMonth.getFullYear(), firstOfMonth.getMonth(), 1)
  const startWeekday = first.getDay() // 0-dom .. 6-sab
  const daysInMonth = new Date(first.getFullYear(), first.getMonth()+1, 0).getDate()
  const cells = []
  // placeholders (sem exibir números de meses adjacentes)
  for (let i = 0; i < startWeekday; i++) {
    cells.push({ placeholder: true, pi: i })
  }
  // dias do mês corrente
  for (let day = 1; day <= daysInMonth; day++) {
    const d = new Date(first.getFullYear(), first.getMonth(), day)
    const isToday = toYMD(d) === toYMD(today)
    let disabled = false
    if (minD.value && isBefore(d, startOfDay(minD.value))) disabled = true
    if (maxD.value && isAfter(d, endOfDay(maxD.value))) disabled = true
    cells.push({ d, isToday, disabled, placeholder: false })
  }
  return cells
}

const monthA = computed(() => monthDays(viewMonth.value))
const nextMonth = computed(() => new Date(viewMonth.value.getFullYear(), viewMonth.value.getMonth()+1, 1))
const monthB = computed(() => props.range ? monthDays(nextMonth.value) : [])
const gridClass = computed(() => ['grid gap-3', props.range ? 'grid-cols-2' : 'grid-cols-1'].join(' '))
const hours = Array.from({ length: 24 }, (_, i) => i)
const minutes = Array.from({ length: 60 }, (_, i) => i)
const hourOptions = computed(() => hours.map(h => ({ value: h, label: fmt2(h) })))
const minuteOptions = computed(() => minutes.map(m => ({ value: m, label: fmt2(m) })))

function prevMonth() { viewMonth.value = new Date(viewMonth.value.getFullYear(), viewMonth.value.getMonth()-1, 1) }
function nextMonthFn() { viewMonth.value = new Date(viewMonth.value.getFullYear(), viewMonth.value.getMonth()+1, 1) }

// Lógica de seleção
function onSelectDay(day) {
  if (day.disabled) return
  if (props.range) {
    if (!rangeStart.value || (rangeStart.value && rangeEnd.value)) {
      rangeStart.value = startOfDay(day.d)
      startH.value = props.withTime ? startH.value : 0
      startM.value = props.withTime ? startM.value : 0
      rangeEnd.value = null
      hoverDate.value = null
      return
    }
    // definindo fim
    const candidateEnd = startOfDay(day.d)
    if (candidateEnd.getTime() < rangeStart.value.getTime()) {
      // inverte
      rangeEnd.value = rangeStart.value
      endH.value = startH.value
      endM.value = startM.value
      rangeStart.value = candidateEnd
      startH.value = props.withTime ? startH.value : 0
      startM.value = props.withTime ? startM.value : 0
    } else {
      rangeEnd.value = candidateEnd
      endH.value = props.withTime ? endH.value : 0
      endM.value = props.withTime ? endM.value : 0
    }
    if (!props.withTime) {
      // emitir automaticamente quando não há tempo; fechamento via template
      commitRange()
    }
  } else {
    singleDate.value = startOfDay(day.d)
    if (!props.withTime) {
      // emite imediatamente; fechamento via template
      emit('update:modelValue', toYMD(singleDate.value))
      emit('change', toYMD(singleDate.value))
    }
  }
}

function inSelectedRange(d) {
  if (!props.range) return false
  const s = rangeStart.value, e = rangeEnd.value
  if (!s && !e) return false
  const time = startOfDay(d).getTime()
  if (s && e) return time >= startOfDay(s).getTime() && time <= startOfDay(e).getTime()
  if (s && hoverDate.value) {
    const start = Math.min(startOfDay(s).getTime(), startOfDay(hoverDate.value).getTime())
    const end = Math.max(startOfDay(s).getTime(), startOfDay(hoverDate.value).getTime())
    return time >= start && time <= end
  }
  return false
}

// Commit/emit
function commitSingle() {
  if (!singleDate.value) return
  const d = new Date(singleDate.value)
  d.setHours(props.withTime ? Number(singleH.value)||0 : 0, props.withTime ? Number(singleM.value)||0 : 0, 0, 0)
  const out = props.withTime ? toYMDHM(d) : toYMD(d)
  emit('update:modelValue', out)
  emit('change', out)
}

function commitRange() {
  if (!rangeStart.value || !rangeEnd.value) return
  const s = new Date(rangeStart.value)
  const e = new Date(rangeEnd.value)
  if (props.withTime) {
    s.setHours(Number(startH.value)||0, Number(startM.value)||0, 0, 0)
    e.setHours(Number(endH.value)||0, Number(endM.value)||0, 0, 0)
  } else {
    s.setHours(0,0,0,0)
    e.setHours(0,0,0,0)
  }
  const out = props.withTime
    ? { start: toYMDHM(s), end: toYMDHM(e) }
    : { start: toYMD(s), end: toYMD(e) }
  emit('update:modelValue', out)
  emit('change', out)
}

function clearValue() {
  if (props.range) {
    rangeStart.value = null
    rangeEnd.value = null
    hoverDate.value = null
    emit('update:modelValue', { start: null, end: null })
    emit('change', { start: null, end: null })
  } else {
    singleDate.value = null
    emit('update:modelValue', null)
    emit('change', null)
  }
}

const autoCloseOnSelect = computed(() => !props.range && !props.withTime)
const effectivePlaceholder = computed(() => {
  if (props.placeholder) return props.placeholder
  return props.range ? (props.withTime ? 'Selecionar período e horário' : 'Selecionar período') : (props.withTime ? 'Selecionar data e horário' : 'Selecionar data')
})

function openPicker() { /* no-op: Dropdown gerencia a abertura */ }

// Para navegação por hover no range
function onHoverDay(day) { if (props.range) hoverDate.value = day?.d || null }

// Clique em um dia: aplica seleção e decide fechamento do painel
function handleDayClick(cell, close) {
  onSelectDay(cell)
  if (!props.range && !props.withTime) {
    close && close()
    return
  }
  if (props.range && !props.withTime && rangeStart.value && rangeEnd.value) {
    close && close()
  }
}

function applyRange(close) {
  commitRange()
  close && close()
}

function applySingle(close) {
  commitSingle()
  close && close()
}
</script>

<template>
  <div class="block w-full">
    <Dropdown class="block w-full" :portal="true" :minWidth="props.range ? 560 : 280" :panelClass="'menu-panel'" :openClass="'is-open'">
      <!-- Trigger (slot nomeado) -->
      <template #trigger="{ toggle }">
        <div class="relative inline-flex w-full" @click="!props.disabled && !props.readonly && (openPicker(), toggle())" :aria-disabled="props.disabled">
          <input
            :value="displayValue"
            :placeholder="effectivePlaceholder"
            :disabled="props.disabled"
            :readonly="true"
            :class="finalClasses"
          />
          <CalendarDaysIcon class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-500" />
          <button v-if="clearable && !props.disabled && displayValue" type="button" class="absolute right-8 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" @click.stop="clearValue">
            <XMarkIcon class="h-4 w-4" />
          </button>
        </div>
      </template>
      <!-- Painel (slot padrão) -->
      <template #default="{ close }">
      <div class="p-3" @mouseleave="onHoverDay(null)">
        <!-- Cabeçalho superior: meses e setas -->
        <div class="pb-2">
          <template v-if="props.range">
            <div class="grid grid-cols-2 items-center gap-3">
              <div class="px-1 font-semibold text-slate-700">
                {{ monthNames[viewMonth.getMonth()] }} de {{ viewMonth.getFullYear() }}
              </div>
              <div class="flex items-center justify-between">
                <div class="px-1 font-semibold text-slate-700">
                  {{ monthNames[nextMonth.getMonth()] }} de {{ nextMonth.getFullYear() }}
                </div>
                <div class="flex items-center gap-1">
                  <button class="inline-flex h-8 w-8 items-center justify-center rounded-md hover:bg-slate-100" @click.stop="prevMonth" :disabled="props.disabled">
                    <ChevronRightIcon class="h-4 w-4 rotate-180" />
                  </button>
                  <button class="inline-flex h-8 w-8 items-center justify-center rounded-md hover:bg-slate-100" @click.stop="nextMonthFn" :disabled="props.disabled">
                    <ChevronRightIcon class="h-4 w-4" />
                  </button>
                </div>
              </div>
            </div>
          </template>
          <template v-else>
            <div class="flex items-center justify-between">
              <div class="px-1 font-semibold text-slate-700">
                {{ monthNames[viewMonth.getMonth()] }} de {{ viewMonth.getFullYear() }}
              </div>
              <div class="flex items-center gap-1">
                <button class="inline-flex h-8 w-8 items-center justify-center rounded-md hover:bg-slate-100" @click.stop="prevMonth" :disabled="props.disabled">
                  <ChevronRightIcon class="h-4 w-4 rotate-180" />
                </button>
                <button class="inline-flex h-8 w-8 items-center justify-center rounded-md hover:bg-slate-100" @click.stop="nextMonthFn" :disabled="props.disabled">
                  <ChevronRightIcon class="h-4 w-4" />
                </button>
              </div>
            </div>
          </template>
        </div>

        <div :class="gridClass">
          <!-- Calendário A -->
          <div>
            <div class="grid grid-cols-7 gap-1 px-1 pb-1 text-center text-xs font-semibold text-slate-500">
              <div v-for="w in weekDays" :key="w">{{ w }}</div>
            </div>
            <div class="grid grid-cols-7 gap-1 px-1">
              <button
                v-for="cell in monthA"
                :key="cellKey(cell,'-a')"
                type="button"
                class="h-9 w-full rounded-md text-sm flex items-center justify-center"
                :class="[
                  cell.placeholder ? 'text-transparent pointer-events-none' : 'text-slate-800',
                  cell.isToday && !cell.placeholder ? 'ring-1 ring-blue-400' : '',
                  (props.range && !cell.placeholder && inSelectedRange(cell.d)) ? 'bg-blue-100' : '',
                ]"
                :disabled="cell.disabled || cell.placeholder"
                @mouseenter="onHoverDay(cell)"
                @click.stop="handleDayClick(cell, close)"
              >
                {{ cell.placeholder ? '\u00A0' : cell.d.getDate() }}
              </button>
            </div>

            <!-- Tempo single ou início do range -->
            <div v-if="props.withTime && (!props.range || (props.range && rangeStart))" class="mt-2 flex items-center gap-2 text-sm">
              <ClockIcon class="h-4 w-4 text-slate-500" />
              <template v-if="!props.range">
                <div class="w-18"><InputSelect v-model="singleH" :options="hourOptions" size="sm" :optionValue="'value'" :optionLabel="'label'" :placeholder="null" /></div>
                <span>:</span>
                <div class="w-18"><InputSelect v-model="singleM" :options="minuteOptions" size="sm" :optionValue="'value'" :optionLabel="'label'" :placeholder="null" /></div>
              </template>
              <template v-else>
                <div class="text-slate-500">Início</div>
                <div class="w-18"><InputSelect v-model="startH" :options="hourOptions" size="sm" :optionValue="'value'" :optionLabel="'label'" :placeholder="null" /></div>
                <span>:</span>
                <div class="w-18"><InputSelect v-model="startM" :options="minuteOptions" size="sm" :optionValue="'value'" :optionLabel="'label'" :placeholder="null" /></div>
              </template>
            </div>
          </div>

          <!-- Calendário B (apenas range) -->
          <div v-if="props.range">
            <div class="grid grid-cols-7 gap-1 px-1 pb-1 text-center text-xs font-semibold text-slate-500">
              <div v-for="w in weekDays" :key="'b'+w">{{ w }}</div>
            </div>
            <div class="grid grid-cols-7 gap-1 px-1">
              <button
                v-for="cell in monthB"
                :key="cellKey(cell,'-b')"
                type="button"
                class="h-9 w-full rounded-md text-sm flex items-center justify-center"
                :class="[
                  cell.placeholder ? 'text-transparent pointer-events-none' : 'text-slate-800',
                  cell.isToday && !cell.placeholder ? 'ring-1 ring-blue-400' : '',
                  (!cell.placeholder && inSelectedRange(cell.d)) ? 'bg-blue-100' : '',
                ]"
                :disabled="cell.disabled || cell.placeholder"
                @mouseenter="onHoverDay(cell)"
                @click.stop="handleDayClick(cell, close)"
              >
                {{ cell.placeholder ? '\u00A0' : cell.d.getDate() }}
              </button>
            </div>

            <div v-if="props.withTime && rangeEnd" class="mt-2 flex items-center gap-2 text-sm">
              <ClockIcon class="h-4 w-4 text-slate-500" />
              <div class="text-slate-500">Fim</div>
              <div class="w-18"><InputSelect v-model="endH" :options="hourOptions" size="sm" :optionValue="'value'" :optionLabel="'label'" :placeholder="null" /></div>
              <span>:</span>
              <div class="w-18"><InputSelect v-model="endM" :options="minuteOptions" size="sm" :optionValue="'value'" :optionLabel="'label'" :placeholder="null" /></div>
            </div>
          </div>
        </div>

        <!-- Ações -->
        <div v-if="props.withTime || props.range" class="mt-3 flex items-center justify-end gap-2">
          <Button v-if="clearable" variant="ghost" size="sm" @click.stop="clearValue">Limpar</Button>
          <Button v-if="props.range && !props.withTime" variant="primary" size="sm" :disabled="!rangeStart || !rangeEnd" @click.stop="applyRange(close)">Aplicar</Button>
          <Button v-else-if="props.range && props.withTime" variant="primary" size="sm" :disabled="!rangeStart || !rangeEnd" @click.stop="applyRange(close)">Aplicar</Button>
          <Button v-else-if="!props.range && props.withTime" variant="primary" size="sm" :disabled="!singleDate" @click.stop="applySingle(close)">Aplicar</Button>
        </div>
      </div>
      </template>
    </Dropdown>
  </div>
</template>

<style scoped>
/* Estilos adicionais pontuais para a grade podem ser adicionados se necessário */
</style>
