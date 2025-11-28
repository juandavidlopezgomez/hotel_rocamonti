<template>
  <div :class="['logo-rocamonti', variant]" :style="{ width: width, height: height }">
    <svg viewBox="0 0 200 60" fill="none" xmlns="http://www.w3.org/2000/svg">
      <!-- Ícono de montaña/roca estilizada -->
      <g class="logo-icon">
        <!-- Montañas -->
        <path
          d="M20 40 L30 25 L35 32 L40 20 L50 40 Z"
          :fill="iconColor"
          opacity="0.9"
        />
        <path
          d="M25 40 L35 28 L40 35 L45 25 L55 40 Z"
          :fill="iconColor"
          opacity="0.7"
        />

        <!-- Sol/Luna -->
        <circle
          cx="15"
          cy="22"
          r="4"
          :fill="accentColor"
          opacity="0.8"
        />

        <!-- Base -->
        <rect
          x="10"
          y="40"
          width="50"
          height="3"
          :fill="iconColor"
          opacity="0.5"
        />
      </g>

      <!-- Texto "ROCAMONTI" -->
      <g class="logo-text">
        <text
          x="65"
          y="30"
          :fill="textColor"
          font-family="'Georgia', serif"
          font-size="18"
          font-weight="700"
          letter-spacing="1"
        >
          ROCAMONTI
        </text>

        <!-- Subtítulo -->
        <text
          x="65"
          y="42"
          :fill="textColor"
          font-family="'Arial', sans-serif"
          font-size="8"
          font-weight="400"
          letter-spacing="2"
          opacity="0.7"
        >
          HOTEL & SPA
        </text>

        <!-- Línea decorativa -->
        <line
          x1="65"
          y1="35"
          x2="195"
          y2="35"
          :stroke="accentColor"
          stroke-width="0.5"
          opacity="0.4"
        />
      </g>

      <!-- Estrellas (calificación) -->
      <g class="logo-stars" v-if="showStars">
        <path
          v-for="(star, index) in 5"
          :key="index"
          :d="starPath"
          :transform="`translate(${165 + index * 6}, 45) scale(0.4)`"
          :fill="accentColor"
        />
      </g>
    </svg>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'light', // 'light', 'dark', 'color'
    validator: (value) => ['light', 'dark', 'color'].includes(value)
  },
  width: {
    type: String,
    default: '200px'
  },
  height: {
    type: String,
    default: '60px'
  },
  showStars: {
    type: Boolean,
    default: true
  }
})

// Colores según variante
const iconColor = computed(() => {
  switch (props.variant) {
    case 'light':
      return '#1f2937' // Gris oscuro
    case 'dark':
      return '#ffffff' // Blanco
    case 'color':
      return '#667eea' // Púrpura
    default:
      return '#1f2937'
  }
})

const textColor = computed(() => {
  switch (props.variant) {
    case 'light':
      return '#1f2937'
    case 'dark':
      return '#ffffff'
    case 'color':
      return '#1f2937'
    default:
      return '#1f2937'
  }
})

const accentColor = computed(() => {
  switch (props.variant) {
    case 'light':
      return '#667eea' // Púrpura
    case 'dark':
      return '#f59e0b' // Dorado
    case 'color':
      return '#f59e0b' // Dorado
    default:
      return '#667eea'
  }
})

// Path de estrella
const starPath = 'M12 2 L15.09 8.26 L22 9.27 L17 14.14 L18.18 21.02 L12 17.77 L5.82 21.02 L7 14.14 L2 9.27 L8.91 8.26 Z'
</script>

<style scoped>
.logo-rocamonti {
  display: inline-block;
}

.logo-rocamonti svg {
  width: 100%;
  height: 100%;
}

/* Variantes */
.logo-rocamonti.light .logo-icon,
.logo-rocamonti.light .logo-text {
  filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1));
}

.logo-rocamonti.dark .logo-icon,
.logo-rocamonti.dark .logo-text {
  filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.3));
}

.logo-rocamonti.color .logo-icon {
  filter: drop-shadow(0 2px 4px rgba(102, 126, 234, 0.2));
}

/* Animación hover */
.logo-rocamonti:hover .logo-icon {
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-3px);
  }
}

.logo-rocamonti:hover .logo-stars path {
  animation: sparkle 1s ease-in-out infinite;
  animation-delay: calc(var(--i) * 0.1s);
}

@keyframes sparkle {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.5;
    transform: scale(0.8);
  }
}

/* Responsive */
@media (max-width: 768px) {
  .logo-rocamonti {
    max-width: 150px;
  }
}
</style>
