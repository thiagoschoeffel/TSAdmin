<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number, Boolean],
    default: ''
  },
  options: {
    type: Array,
    default: () => []
  },
  placeholder: {
    type: String,
    default: 'Selecione...'
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  error: {
    type: Boolean,
    default: false
  },
  success: {
    type: Boolean,
    default: false
  },
  // Option properties
  optionValue: {
    type: String,
    default: 'value'
  },
  optionLabel: {
    type: String,
    default: 'label'
  },
  class: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue', 'change', 'blur', 'focus'])

const selectClasses = computed(() => {
  const baseClasses = ['form-select']

  // Default size matching Button md size for consistency
  baseClasses.push('px-4', 'py-2', 'text-sm')

  // Disabled state
  if (props.disabled) {
    baseClasses.push('bg-gray-100', 'cursor-not-allowed', 'text-slate-500')
  }

  // State variations
  if (props.error) {
    baseClasses.push('border-red-500', 'focus:border-red-500', 'focus:ring-red-500')
  } else if (props.success) {
    baseClasses.push('border-green-500', 'focus:border-green-500', 'focus:ring-green-500')
  }

  return baseClasses.join(' ')
})

const finalClasses = computed(() => {
  const classes = [selectClasses.value]
  if (props.class) {
    classes.push(props.class)
  }
  return classes.join(' ')
})

const handleChange = (event) => {
  const value = event.target.value
  // Convert to appropriate type if needed
  let finalValue = value
  if (value === '') {
    finalValue = null
  } else if (typeof props.modelValue === 'number') {
    finalValue = Number(value)
  } else if (typeof props.modelValue === 'boolean') {
    finalValue = value === 'true'
  }

  emit('update:modelValue', finalValue)
  emit('change', event)
}

const handleBlur = (event) => {
  emit('blur', event)
}

const handleFocus = (event) => {
  emit('focus', event)
}

const getOptionValue = (option) => {
  if (typeof option === 'string' || typeof option === 'number') {
    return option
  }
  return option[props.optionValue]
}

const getOptionLabel = (option) => {
  if (typeof option === 'string' || typeof option === 'number') {
    return option
  }
  return option[props.optionLabel]
}
</script>

<template>
  <select
    :value="modelValue"
    :required="required"
    :disabled="disabled"
    :class="finalClasses"
    @change="handleChange"
    @blur="handleBlur"
    @focus="handleFocus"
  >
    <option value="">{{ placeholder }}</option>
    <option
      v-for="option in options"
      :key="getOptionValue(option)"
      :value="getOptionValue(option)"
    >
      {{ getOptionLabel(option) }}
    </option>
  </select>
</template>

<style scoped>
/* Additional styles can be added here if needed */
</style>
