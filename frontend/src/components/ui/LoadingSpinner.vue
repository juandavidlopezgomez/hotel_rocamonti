<template>
  <div :class="containerClasses">
    <div v-if="type === 'spinner'" :class="spinnerClasses">
      <svg
        class="animate-spin"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
      >
        <circle
          class="opacity-25"
          cx="12"
          cy="12"
          r="10"
          stroke="currentColor"
          stroke-width="4"
        ></circle>
        <path
          class="opacity-75"
          fill="currentColor"
          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
        ></path>
      </svg>
    </div>

    <div v-else-if="type === 'dots'" class="flex space-x-2">
      <div
        v-for="i in 3"
        :key="i"
        :class="dotClasses"
        :style="{ animationDelay: `${(i - 1) * 0.15}s` }"
      ></div>
    </div>

    <div v-else-if="type === 'pulse'" :class="pulseClasses"></div>

    <p v-if="text" :class="textClasses">{{ text }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'spinner',
    validator: (value) => ['spinner', 'dots', 'pulse'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value)
  },
  color: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'white', 'gray'].includes(value)
  },
  text: {
    type: String,
    default: ''
  },
  centered: {
    type: Boolean,
    default: false
  },
  fullscreen: {
    type: Boolean,
    default: false
  }
})

const containerClasses = computed(() => {
  let classes = 'flex flex-col items-center gap-3'

  if (props.fullscreen) {
    classes += ' fixed inset-0 bg-white bg-opacity-90 z-50 justify-center'
  } else if (props.centered) {
    classes += ' justify-center'
  }

  return classes
})

const spinnerClasses = computed(() => {
  const sizeClasses = {
    sm: 'h-6 w-6',
    md: 'h-10 w-10',
    lg: 'h-16 w-16',
    xl: 'h-24 w-24'
  }

  const colorClasses = {
    primary: 'text-primary-600',
    white: 'text-white',
    gray: 'text-gray-600'
  }

  return `${sizeClasses[props.size]} ${colorClasses[props.color]}`
})

const dotClasses = computed(() => {
  const sizeClasses = {
    sm: 'h-2 w-2',
    md: 'h-3 w-3',
    lg: 'h-4 w-4',
    xl: 'h-6 w-6'
  }

  const colorClasses = {
    primary: 'bg-primary-600',
    white: 'bg-white',
    gray: 'bg-gray-600'
  }

  return `${sizeClasses[props.size]} ${colorClasses[props.color]} rounded-full animate-bounce`
})

const pulseClasses = computed(() => {
  const sizeClasses = {
    sm: 'h-6 w-6',
    md: 'h-10 w-10',
    lg: 'h-16 w-16',
    xl: 'h-24 w-24'
  }

  const colorClasses = {
    primary: 'bg-primary-600',
    white: 'bg-white',
    gray: 'bg-gray-600'
  }

  return `${sizeClasses[props.size]} ${colorClasses[props.color]} rounded-full animate-pulse`
})

const textClasses = computed(() => {
  const colorClasses = {
    primary: 'text-gray-700',
    white: 'text-white',
    gray: 'text-gray-600'
  }

  return `text-sm font-medium ${colorClasses[props.color]}`
})
</script>
