<template>
  <div class="chart-donut">
    <svg :width="size" :height="size" :viewBox="`0 0 ${size} ${size}`">
      <g :transform="`translate(${size / 2}, ${size / 2})`">
        <!-- Background circle -->
        <circle
          :r="radius"
          fill="none"
          :stroke="backgroundColor"
          :stroke-width="strokeWidth"
        />

        <!-- Data segments -->
        <path
          v-for="(segment, index) in segments"
          :key="index"
          :d="segment.path"
          :fill="segment.color"
          class="segment"
          :class="{ active: hoveredIndex === index }"
          @mouseover="hoveredIndex = index"
          @mouseleave="hoveredIndex = null"
        >
          <title>{{ segment.label }}: {{ segment.value }} ({{ segment.percentage }}%)</title>
        </path>

        <!-- Center circle (donut hole) -->
        <circle
          :r="innerRadius"
          fill="white"
        />

        <!-- Center text -->
        <text
          text-anchor="middle"
          dy="-10"
          class="center-value"
        >
          {{ totalValue }}
        </text>
        <text
          text-anchor="middle"
          dy="15"
          class="center-label"
        >
          {{ centerLabel }}
        </text>
      </g>
    </svg>

    <!-- Legend -->
    <div class="legend">
      <div
        v-for="(item, index) in data"
        :key="index"
        class="legend-item"
        :class="{ active: hoveredIndex === index }"
        @mouseover="hoveredIndex = index"
        @mouseleave="hoveredIndex = null"
      >
        <div class="legend-color" :style="{ background: colors[index % colors.length] }"></div>
        <div class="legend-text">
          <div class="legend-label">{{ item.label }}</div>
          <div class="legend-value">{{ item.value }}</div>
        </div>
        <div class="legend-percentage">{{ calculatePercentage(item.value) }}%</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  data: {
    type: Array,
    required: true,
    // Expected format: [{ label: 'Category', value: 100 }, ...]
  },
  colors: {
    type: Array,
    default: () => ['#667eea', '#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#8b5cf6']
  },
  size: {
    type: Number,
    default: 300
  },
  strokeWidth: {
    type: Number,
    default: 50
  },
  backgroundColor: {
    type: String,
    default: '#f3f4f6'
  },
  centerLabel: {
    type: String,
    default: 'Total'
  }
})

const hoveredIndex = ref(null)

const radius = computed(() => (props.size / 2) - (props.strokeWidth / 2) - 10)
const innerRadius = computed(() => radius.value - props.strokeWidth)

const totalValue = computed(() =>
  props.data.reduce((sum, item) => sum + item.value, 0)
)

const segments = computed(() => {
  let currentAngle = -Math.PI / 2 // Start at top

  return props.data.map((item, index) => {
    const percentage = (item.value / totalValue.value) * 100
    const angle = (percentage / 100) * 2 * Math.PI

    const startAngle = currentAngle
    const endAngle = currentAngle + angle

    const startX = radius.value * Math.cos(startAngle)
    const startY = radius.value * Math.sin(startAngle)
    const endX = radius.value * Math.cos(endAngle)
    const endY = radius.value * Math.sin(endAngle)

    const largeArcFlag = angle > Math.PI ? 1 : 0

    // Create donut segment path
    const innerStartX = innerRadius.value * Math.cos(startAngle)
    const innerStartY = innerRadius.value * Math.sin(startAngle)
    const innerEndX = innerRadius.value * Math.cos(endAngle)
    const innerEndY = innerRadius.value * Math.sin(endAngle)

    const path = [
      `M ${startX} ${startY}`,
      `A ${radius.value} ${radius.value} 0 ${largeArcFlag} 1 ${endX} ${endY}`,
      `L ${innerEndX} ${innerEndY}`,
      `A ${innerRadius.value} ${innerRadius.value} 0 ${largeArcFlag} 0 ${innerStartX} ${innerStartY}`,
      'Z'
    ].join(' ')

    currentAngle = endAngle

    return {
      path,
      color: props.colors[index % props.colors.length],
      label: item.label,
      value: item.value,
      percentage: percentage.toFixed(1)
    }
  })
})

function calculatePercentage(value) {
  return ((value / totalValue.value) * 100).toFixed(1)
}
</script>

<style scoped>
.chart-donut {
  display: flex;
  align-items: center;
  gap: 2rem;
}

svg {
  flex-shrink: 0;
}

.segment {
  transition: all 0.3s;
  cursor: pointer;
  animation: segmentGrow 0.8s ease-out forwards;
  transform-origin: center;
}

.segment:hover,
.segment.active {
  opacity: 0.8;
  filter: brightness(1.1) drop-shadow(0 4px 8px rgba(0,0,0,0.2));
}

@keyframes segmentGrow {
  from {
    opacity: 0;
    transform: scale(0);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.center-value {
  font-size: 32px;
  font-weight: 700;
  fill: #1f2937;
  animation: countUp 1s ease-out;
}

.center-label {
  font-size: 14px;
  fill: #6b7280;
  font-weight: 500;
}

@keyframes countUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.legend {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  border-radius: 8px;
  transition: all 0.3s;
  cursor: pointer;
}

.legend-item:hover,
.legend-item.active {
  background: #f9fafb;
  transform: translateX(4px);
}

.legend-color {
  width: 16px;
  height: 16px;
  border-radius: 4px;
  flex-shrink: 0;
}

.legend-text {
  flex: 1;
}

.legend-label {
  font-size: 14px;
  font-weight: 600;
  color: #374151;
}

.legend-value {
  font-size: 12px;
  color: #6b7280;
}

.legend-percentage {
  font-size: 18px;
  font-weight: 700;
  color: #667eea;
}
</style>
