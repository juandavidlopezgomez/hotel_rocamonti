<template>
  <div class="chart-line">
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

      <!-- Line path -->
      <path
        :d="linePath"
        fill="none"
        :stroke="color"
        stroke-width="3"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="line-path"
      />

      <!-- Area under line -->
      <path
        :d="areaPath"
        :fill="`url(#gradient-${id})`"
        opacity="0.2"
        class="area-path"
      />

      <!-- Data points -->
      <g class="data-points">
        <circle
          v-for="(point, index) in points"
          :key="index"
          :cx="point.x"
          :cy="point.y"
          r="5"
          :fill="color"
          class="data-point"
          @mouseover="showTooltip(index, $event)"
          @mouseleave="hideTooltip"
        />
      </g>

      <!-- X axis labels -->
      <g class="x-labels">
        <text
          v-for="(label, index) in data"
          :key="'label-' + index"
          :x="padding + (chartWidth / (data.length - 1)) * index"
          :y="height - padding + 20"
          text-anchor="middle"
          class="axis-label"
        >
          {{ label.label }}
        </text>
      </g>

      <!-- Gradient definition -->
      <defs>
        <linearGradient :id="`gradient-${id}`" x1="0%" y1="0%" x2="0%" y2="100%">
          <stop offset="0%" :stop-color="color" stop-opacity="0.4"/>
          <stop offset="100%" :stop-color="color" stop-opacity="0"/>
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
    required: true,
    // Expected format: [{ label: 'Jan', value: 120 }, ...]
  },
  color: {
    type: String,
    default: '#667eea'
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
const padding = 40
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

const points = computed(() => {
  return props.data.map((item, index) => {
    const x = padding + (chartWidth / (props.data.length - 1)) * index
    const y = padding + chartHeight - (item.value / maxValue.value) * chartHeight
    return { x, y, label: item.label, value: item.value }
  })
})

const linePath = computed(() => {
  if (points.value.length === 0) return ''
  return points.value.reduce((path, point, index) => {
    if (index === 0) {
      return `M ${point.x} ${point.y}`
    }
    // Smooth curve
    const prevPoint = points.value[index - 1]
    const cpX = (prevPoint.x + point.x) / 2
    return `${path} Q ${cpX} ${prevPoint.y}, ${cpX} ${(prevPoint.y + point.y) / 2} Q ${cpX} ${point.y}, ${point.x} ${point.y}`
  }, '')
})

const areaPath = computed(() => {
  if (points.value.length === 0) return ''
  const line = linePath.value
  const firstPoint = points.value[0]
  const lastPoint = points.value[points.value.length - 1]
  return `${line} L ${lastPoint.x} ${padding + chartHeight} L ${firstPoint.x} ${padding + chartHeight} Z`
})

function showTooltip(index, event) {
  const point = points.value[index]
  const rect = event.target.getBoundingClientRect()
  tooltip.value = {
    show: true,
    x: rect.left + window.scrollX,
    y: rect.top + window.scrollY - 60,
    label: point.label,
    value: point.value
  }
}

function hideTooltip() {
  tooltip.value.show = false
}
</script>

<style scoped>
.chart-line {
  position: relative;
  width: 100%;
  height: 100%;
}

svg {
  width: 100%;
  height: auto;
}

.line-path {
  animation: drawLine 1.5s ease-out forwards;
  stroke-dasharray: 1000;
  stroke-dashoffset: 1000;
}

@keyframes drawLine {
  to {
    stroke-dashoffset: 0;
  }
}

.area-path {
  animation: fadeIn 1s ease-out 0.5s forwards;
  opacity: 0;
}

@keyframes fadeIn {
  to {
    opacity: 0.2;
  }
}

.data-point {
  cursor: pointer;
  transition: all 0.3s;
  animation: popIn 0.5s ease-out forwards;
  transform-origin: center;
}

.data-point:hover {
  r: 8;
  filter: drop-shadow(0 0 8px currentColor);
}

@keyframes popIn {
  0% {
    opacity: 0;
    transform: scale(0);
  }
  50% {
    transform: scale(1.2);
  }
  100% {
    opacity: 1;
    transform: scale(1);
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
  animation: tooltipFade 0.2s ease-out;
}

@keyframes tooltipFade {
  from {
    opacity: 0;
    transform: translateY(5px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
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
