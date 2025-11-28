<template>
  <div class="w-full">
    <label
      v-if="label"
      :for="inputId"
      class="block text-sm font-medium text-gray-700 mb-1.5"
    >
      {{ label }}
      <span v-if="required" class="text-danger-500">*</span>
    </label>

    <div class="relative">
      <div
        v-if="prefixIcon"
        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
      >
        <component :is="prefixIcon" class="h-5 w-5 text-gray-400" />
      </div>

      <input
        :id="inputId"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :min="min"
        :max="max"
        :step="step"
        :class="inputClasses"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
      />

      <div
        v-if="suffixIcon || showClearButton"
        class="absolute inset-y-0 right-0 pr-3 flex items-center"
      >
        <button
          v-if="showClearButton && modelValue"
          type="button"
          class="text-gray-400 hover:text-gray-600 transition-colors"
          @click="handleClear"
        >
          <XMarkIcon class="h-5 w-5" />
        </button>
        <component
          v-else-if="suffixIcon"
          :is="suffixIcon"
          class="h-5 w-5 text-gray-400"
        />
      </div>
    </div>

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
import { computed, ref } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  type: {
    type: String,
    default: 'text'
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
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
  readonly: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  prefixIcon: {
    type: Object,
    default: null
  },
  suffixIcon: {
    type: Object,
    default: null
  },
  showClearButton: {
    type: Boolean,
    default: false
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  min: {
    type: [String, Number],
    default: undefined
  },
  max: {
    type: [String, Number],
    default: undefined
  },
  step: {
    type: [String, Number],
    default: undefined
  }
})

const emit = defineEmits(['update:modelValue', 'blur', 'focus', 'clear'])

const inputId = ref(`input-${Math.random().toString(36).substr(2, 9)}`)

const inputClasses = computed(() => {
  const baseClasses = 'block w-full rounded-lg border transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-0 disabled:opacity-50 disabled:cursor-not-allowed'

  const sizeClasses = {
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-4 py-2 text-base',
    lg: 'px-5 py-3 text-lg'
  }

  const stateClasses = props.error
    ? 'border-danger-300 text-danger-900 focus:ring-danger-500 focus:border-danger-500'
    : 'border-gray-300 focus:ring-primary-500 focus:border-primary-500'

  const paddingLeft = props.prefixIcon ? 'pl-10' : ''
  const paddingRight = props.suffixIcon || props.showClearButton ? 'pr-10' : ''

  return `${baseClasses} ${sizeClasses[props.size]} ${stateClasses} ${paddingLeft} ${paddingRight}`
})

const handleInput = (event) => {
  emit('update:modelValue', event.target.value)
}

const handleBlur = (event) => {
  emit('blur', event)
}

const handleFocus = (event) => {
  emit('focus', event)
}

const handleClear = () => {
  emit('update:modelValue', '')
  emit('clear')
}
</script>
