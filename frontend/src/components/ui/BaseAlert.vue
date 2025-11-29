<template>
  <Transition
    enter-active-class="transition-all duration-300 ease-out"
    enter-from-class="opacity-0 translate-y-2"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition-all duration-200 ease-in"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 translate-y-2"
  >
    <div
      v-if="modelValue"
      :class="[
        'rounded-lg p-4 mb-4',
        variantClasses,
        customClass
      ]"
      role="alert"
    >
      <div class="flex items-start">
        <!-- Icono -->
        <div class="flex-shrink-0">
          <component :is="iconComponent" :class="['w-5 h-5', iconColorClass]" />
        </div>

        <!-- Contenido -->
        <div class="ml-3 flex-1">
          <!-- Título -->
          <h3 v-if="title" :class="['text-sm font-medium', titleColorClass]">
            {{ title }}
          </h3>

          <!-- Mensaje -->
          <div :class="['text-sm', title ? 'mt-1' : '', messageColorClass]">
            <slot>{{ message }}</slot>
          </div>

          <!-- Acciones -->
          <div v-if="$slots.actions" class="mt-3">
            <slot name="actions"></slot>
          </div>
        </div>

        <!-- Botón cerrar -->
        <div v-if="closable" class="ml-auto pl-3 flex-shrink-0">
          <button
            type="button"
            :class="['inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2', closeButtonClasses]"
            @click="handleClose"
          >
            <span class="sr-only">Cerrar</span>
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { computed } from 'vue'
import {
  CheckCircleIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  XCircleIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: true
  },
  variant: {
    type: String,
    default: 'info',
    validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
  },
  title: {
    type: String,
    default: ''
  },
  message: {
    type: String,
    default: ''
  },
  closable: {
    type: Boolean,
    default: true
  },
  autoDismiss: {
    type: Number,
    default: 0
  },
  customClass: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue', 'close'])

// Clases por variante
const variantClasses = computed(() => {
  const variants = {
    success: 'bg-green-50 border border-green-200',
    error: 'bg-red-50 border border-red-200',
    warning: 'bg-yellow-50 border border-yellow-200',
    info: 'bg-blue-50 border border-blue-200'
  }
  return variants[props.variant]
})

const iconColorClass = computed(() => {
  const colors = {
    success: 'text-green-600',
    error: 'text-red-600',
    warning: 'text-yellow-600',
    info: 'text-blue-600'
  }
  return colors[props.variant]
})

const titleColorClass = computed(() => {
  const colors = {
    success: 'text-green-800',
    error: 'text-red-800',
    warning: 'text-yellow-800',
    info: 'text-blue-800'
  }
  return colors[props.variant]
})

const messageColorClass = computed(() => {
  const colors = {
    success: 'text-green-700',
    error: 'text-red-700',
    warning: 'text-yellow-700',
    info: 'text-blue-700'
  }
  return colors[props.variant]
})

const closeButtonClasses = computed(() => {
  const classes = {
    success: 'text-green-500 hover:bg-green-100 focus:ring-green-600',
    error: 'text-red-500 hover:bg-red-100 focus:ring-red-600',
    warning: 'text-yellow-500 hover:bg-yellow-100 focus:ring-yellow-600',
    info: 'text-blue-500 hover:bg-blue-100 focus:ring-blue-600'
  }
  return classes[props.variant]
})

const iconComponent = computed(() => {
  const icons = {
    success: CheckCircleIcon,
    error: XCircleIcon,
    warning: ExclamationTriangleIcon,
    info: InformationCircleIcon
  }
  return icons[props.variant]
})

// Auto dismiss
if (props.autoDismiss > 0) {
  setTimeout(() => {
    handleClose()
  }, props.autoDismiss)
}

const handleClose = () => {
  emit('update:modelValue', false)
  emit('close')
}
</script>
