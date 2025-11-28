<template>
  <div class="chart-bar">
    <svg :width="width" :height="height" :viewBox="`0 0 ${width} ${height}`">
      <!-- Grid lines -->
      <g class="grid">
        <line v-for="i in 5" :key="'h-' + i"
          :x1="padding"
          :y1="padding + (chartHeight / 4) * (i - 1)"
          :x2="width - padding"
          :y2="padding + (chartHeight / 4) * (i - 1)"
          stroke="#e5e7eb"
          stroke-width="1"
          stroke-dasharray="4,4" />
      </g>

      <!-- Y axis labels -->
      <g class="y-labels">
        <text
          v-for="i in 5"
          :key="'y-label-' + i"
          :x="padding - 10"
          :y="padding + (chartHeight / 4) * (i - 1) + 5"
          text-anchor="end"
          class="axis-label"
        >
          {{ formatValue((maxValue / 4) * (4 - i + 1)) }}
        </text>
      </g>

      <!-- Bars -->
      <g class="bars">
        <rect
          v-for="(bar, index) in bars"
          :key="index"
          :x="bar.x"
          :y="bar.y"
          :width="bar.width"
          :height="bar.height"
          :fill="`url(#gradient-bar-${id}-${index})`"
          class="bar"
          @mouseover="showTooltip(index, $event)"
          @mouseleave="hideTooltip"
        />
      </g>

      <!-- X axis labels -->
      <g class="x-labels">
        <text
          v-for="(item, index) in data"
          :key="'label-' + index"
          :x="padding + barGap + (barWidth + barGap) * index + barWidth / 2"
          :y="height - padding + 20"
          text-anchor="middle"
          class="axis-label"
        >
          {{ item.label }}
        </text>
      </g>

      <!-- Gradients -->
      <defs>
        <linearGradient
          v-for="(bar, index) in bars"
          :key="'grad-' + index"
          :id="`gradient-bar-${id}-${index}`"
          x1="0%" y1="0%" x2="0%" y2="100%"
        >
          <stop offset="0%" :stop-color="getColor(index)" stop-opacity="1"/>
          <stop offset="100%" :stop-color="getColor(index)" stop-opacity="0.7"/>
        </linearGradient>
      </defs>
    </svg>

    <!-- Tooltip -->
    <div v-if="tooltip.show" class="tooltip" :style="{ left: tooltip.x + 'px', top: tooltip.y + 'px' }">
      <div class="tooltip-label">{{ tooltip.label }}</div>
      <div class="tooltip-value">{{ tooltip.value }}</div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  data: {
    type: Array,
    required: true
  },
  colors: {
    type: Array,
    default: () => ['#667eea', '#764ba2']
  },
  width: {
    type: Number,
    default: 600
  },
  height: {
    type: Number,
    default: 300
  }
})

const id = Math.random().toString(36).substr(2, 9)
const padding = 50
const chartWidth = props.width - (padding * 2)
const chartHeight = props.height - (padding * 2)

const tooltip = ref({
  show: false,
  x: 0,
  y: 0,
  label: '',
  value: ''
})

const maxValue = computed(() => Math.max(...props.data.map(d => d.value), 1))
const barWidth = computed(() => (chartWidth / props.data.length) * 0.6)
const barGap = computed(() => (chartWidth / props.data.length) * 0.2)

const bars = computed(() => {
  return props.data.map((item, index) => {
    const height = (item.value / maxValue.value) * chartHeight
    const x = padding + barGap.value + (barWidth.value + barGap.value) * index
    const y = padding + chartHeight - height

    return {
      x,
      y,
      width: barWidth.value,
      height,
      label: item.label,
      value: item.value
    }
  })
})

function getColor(index) {
  return props.colors[index % props.colors.length]
}

function formatValue(value) {
  if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M'
  if (value >= 1000) return (value / 1000).toFixed(1) + 'K'
  return Math.round(value)
}

function showTooltip(index, event) {
  const bar = bars.value[index]
  const rect = event.target.getBoundingClientRect()
  tooltip.value = {
    show: true,
    x: rect.left + window.scrollX + rect.width / 2,
    y: rect.top + window.scrollY - 10,
    label: bar.label,
    value: bar.value
  }
}

function hideTooltip() {
  tooltip.value.show = false
}
</script>

<style scoped>
.chart-bar {
  position: relative;
  width: 100%;
  height: 100%;
}

svg {
  width: 100%;
  height: auto;
}

.bar {
  cursor: pointer;
  transition: all 0.3s;
  animation: barGrow 0.8s ease-out forwards;
  transform-origin: bottom;
}

.bar:hover {
  filter: brightness(1.1) drop-shadow(0 4px 8px rgba(0,0,0,0.15));
  transform: translateY(-4px);
}

@keyframes barGrow {
  from {
    transform: scaleY(0);
  }
  to {
    transform: scaleY(1);
  }
}

.axis-label {
  font-size: 12px;
  fill: #6b7280;
  font-weight: 500;
}

.tooltip {
  position: fixed;
  background: rgba(0, 0, 0, 0.9);
  color: white;
  padding: 8px 12px;
  border-radius: 8px;
  font-size: 14px;
  pointer-events: none;
  z-index: 1000;
  transform: translate(-50%, -100%);
  animation: tooltipFade 0.2s ease-out;
}

@keyframes tooltipFade {
  from {
    opacity: 0;
    transform: translate(-50%, calc(-100% + 5px));
  }
  to {
    opacity: 1;
    transform: translate(-50%, -100%);
  }
}

.tooltip-label {
  font-weight: 600;
  margin-bottom: 4px;
}

.tooltip-value {
  font-size: 18px;
  font-weight: 700;
}
</style>
