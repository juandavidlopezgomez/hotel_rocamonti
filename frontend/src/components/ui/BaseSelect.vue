<template>
  <div class="w-full">
    <label
      v-if="label"
      class="block text-sm font-medium text-gray-700 mb-1.5"
    >
      {{ label }}
      <span v-if="required" class="text-danger-500">*</span>
    </label>

    <Listbox
      :model-value="modelValue"
      :disabled="disabled"
      @update:model-value="handleChange"
    >
      <div class="relative">
        <ListboxButton :class="buttonClasses">
          <span v-if="selectedOption" class="block truncate">
            {{ selectedOption[displayKey] }}
          </span>
          <span v-else class="block truncate text-gray-400">
            {{ placeholder }}
          </span>
          <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
            <ChevronUpDownIcon class="h-5 w-5 text-gray-400" />
          </span>
        </ListboxButton>

        <transition
          leave-active-class="transition duration-100 ease-in"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <ListboxOptions :class="optionsClasses">
            <ListboxOption
              v-for="option in options"
              :key="option[valueKey]"
              v-slot="{ active, selected }"
              :value="option"
              as="template"
            >
              <li
                :class="[
                  active ? 'bg-primary-100 text-primary-900' : 'text-gray-900',
                  'relative cursor-pointer select-none py-2.5 pl-10 pr-4'
                ]"
              >
                <span
                  :class="[
                    selected ? 'font-semibold' : 'font-normal',
                    'block truncate'
                  ]"
                >
                  {{ option[displayKey] }}
                </span>
                <span
                  v-if="selected"
                  class="absolute inset-y-0 left-0 flex items-center pl-3 text-primary-600"
                >
                  <CheckIcon class="h-5 w-5" />
                </span>
              </li>
            </ListboxOption>
          </ListboxOptions>
        </transition>
      </div>
    </Listbox>

    <p
      v-if="error"
      class="mt-1.5 text-sm text-danger-600"
    >
      {{ error }}
    </p>

    <p
      v-else-if="hint"
      class="mt-1.5 text-sm text-gray-500"
    >
      {{ hint }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Listbox, ListboxButton, ListboxOptions, ListboxOption } from '@headlessui/vue'
import { CheckIcon, ChevronUpDownIcon } from '@heroicons/vue/24/solid'

const props = defineProps({
  modelValue: {
    type: [Object, String, Number],
    default: null
  },
  options: {
    type: Array,
    required: true,
    default: () => []
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Seleccionar opción'
  },
  error: {
    type: String,
    default: ''
  },
  hint: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  valueKey: {
    type: String,
    default: 'id'
  },
  displayKey: {
    type: String,
    default: 'name'
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

const selectedOption = computed(() => {
  if (!props.modelValue) return null

  if (typeof props.modelValue === 'object') {
    return props.modelValue
  }

  return props.options.find(option => option[props.valueKey] === props.modelValue)
})

const buttonClasses = computed(() => {
  const baseClasses = 'relative w-full cursor-pointer rounded-lg border bg-white py-2 pl-3 pr-10 text-left transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-0 disabled:opacity-50 disabled:cursor-not-allowed'

  const sizeClasses = {
    sm: 'text-sm',
    md: 'text-base',
    lg: 'text-lg py-3'
  }

  const stateClasses = props.error
    ? 'border-danger-300 focus:ring-danger-500 focus:border-danger-500'
    : 'border-gray-300 focus:ring-primary-500 focus:border-primary-500'

  return `${baseClasses} ${sizeClasses[props.size]} ${stateClasses}`
})

const optionsClasses = computed(() => {
  return 'absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-lg bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm'
})

const handleChange = (value) => {
  emit('update:modelValue', value)
  emit('change', value)
}
</script>
