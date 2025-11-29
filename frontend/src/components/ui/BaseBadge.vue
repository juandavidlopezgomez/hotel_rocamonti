<template>
  <span :class="badgeClasses">
    <component
      v-if="icon"
      :is="icon"
      :class="iconClasses"
    />
    <slot></slot>
    <button
      v-if="removable"
      type="button"
      class="ml-1 inline-flex items-center"
      @click="handleRemove"
    >
      <XMarkIcon class="h-3 w-3" />
    </button>
  </span>
</template>

<script setup>
import { computed } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/solid'

const props = defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'success', 'warning', 'danger', 'info', 'gray'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  icon: {
    type: Object,
    default: null
  },
  removable: {
    type: Boolean,
    default: false
  },
  dot: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['remove'])

const badgeClasses = computed(() => {
  const baseClasses = 'inline-flex items-center font-medium rounded-full'

  const variantClasses = {
    primary: 'bg-primary-100 text-primary-700',
    success: 'bg-success-100 text-success-700',
    warning: 'bg-warning-100 text-warning-700',
    danger: 'bg-danger-100 text-danger-700',
    info: 'bg-blue-100 text-blue-700',
    gray: 'bg-gray-100 text-gray-700'
  }

  const sizeClasses = {
    sm: 'px-2 py-0.5 text-xs',
    md: 'px-2.5 py-0.5 text-sm',
    lg: 'px-3 py-1 text-base'
  }

  return `${baseClasses} ${variantClasses[props.variant]} ${sizeClasses[props.size]}`
})

const iconClasses = computed(() => {
  const sizeClasses = {
    sm: 'h-3 w-3',
    md: 'h-4 w-4',
    lg: 'h-5 w-5'
  }

  return `${sizeClasses[props.size]} mr-1`
})

const handleRemove = () => {
  emit('remove')
}
</script>
