<template>
  <span>{{ prefix }}{{ displayValue }}{{ suffix }}</span>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'

const props = defineProps({
  endVal: {
    type: Number,
    required: true
  },
  duration: {
    type: Number,
    default: 2000
  },
  prefix: {
    type: String,
    default: ''
  },
  suffix: {
    type: String,
    default: ''
  }
})

const displayValue = ref(0)

function animateValue(start, end, duration) {
  const range = end - start
  const increment = range / (duration / 16)
  let current = start

  const timer = setInterval(() => {
    current += increment
    if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
      current = end
      clearInterval(timer)
    }
    displayValue.value = Math.floor(current).toLocaleString('es-CO')
  }, 16)
}

onMounted(() => {
  animateValue(0, props.endVal, props.duration)
})

watch(() => props.endVal, (newVal) => {
  animateValue(displayValue.value, newVal, props.duration)
})
</script>
