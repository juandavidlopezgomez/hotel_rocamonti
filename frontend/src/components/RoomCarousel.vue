<template>
  <div class="room-carousel">
    <div class="carousel-container">
      <!-- Imágenes -->
      <div class="carousel-images">
        <transition-group name="fade" tag="div">
          <img
            v-for="(imagen, index) in imagenes"
            :key="imagen"
            v-show="index === currentIndex"
            :src="imagen"
            :alt="`${roomName} - Imagen ${index + 1}`"
            class="carousel-image"
            @error="handleImageError"
          />
        </transition-group>
      </div>

      <!-- Flechas de navegación -->
      <button
        v-if="imagenes.length > 1"
        class="carousel-arrow carousel-arrow-left"
        @click="prev"
        aria-label="Imagen anterior"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="15 18 9 12 15 6"></polyline>
        </svg>
      </button>

      <button
        v-if="imagenes.length > 1"
        class="carousel-arrow carousel-arrow-right"
        @click="next"
        aria-label="Imagen siguiente"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="9 18 15 12 9 6"></polyline>
        </svg>
      </button>

      <!-- Indicadores -->
      <div v-if="imagenes.length > 1" class="carousel-indicators">
        <button
          v-for="(imagen, index) in imagenes"
          :key="index"
          :class="['indicator', { active: index === currentIndex }]"
          @click="goTo(index)"
          :aria-label="`Ir a imagen ${index + 1}`"
        ></button>
      </div>

      <!-- Badge de contador -->
      <div v-if="imagenes.length > 1" class="carousel-counter">
        {{ currentIndex + 1 }} / {{ imagenes.length }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'

const props = defineProps({
  habitacionId: {
    type: Number,
    required: true
  },
  roomName: {
    type: String,
    default: 'Habitación'
  },
  autoplay: {
    type: Boolean,
    default: true
  },
  interval: {
    type: Number,
    default: 4000 // 4 segundos
  }
})

const currentIndex = ref(0)
let autoplayInterval = null

// Generar rutas de imágenes basadas en el ID de habitación
const imagenes = computed(() => {
  const letras = ['a', 'b', 'c', 'd']
  return letras.map(letra =>
    `/images/habitaciones/habitacion-${props.habitacionId}/${props.habitacionId}${letra}.jpg`
  )
})

const next = () => {
  currentIndex.value = (currentIndex.value + 1) % imagenes.value.length
  resetAutoplay()
}

const prev = () => {
  currentIndex.value = (currentIndex.value - 1 + imagenes.value.length) % imagenes.value.length
  resetAutoplay()
}

const goTo = (index) => {
  currentIndex.value = index
  resetAutoplay()
}

const handleImageError = (event) => {
  // Si la imagen no existe, usar placeholder
  event.target.src = '/images/placeholder-room.jpg'
}

const startAutoplay = () => {
  if (props.autoplay && imagenes.value.length > 1) {
    autoplayInterval = setInterval(() => {
      next()
    }, props.interval)
  }
}

const stopAutoplay = () => {
  if (autoplayInterval) {
    clearInterval(autoplayInterval)
    autoplayInterval = null
  }
}

const resetAutoplay = () => {
  stopAutoplay()
  startAutoplay()
}

onMounted(() => {
  startAutoplay()
})

onUnmounted(() => {
  stopAutoplay()
})
</script>

<style scoped>
.room-carousel {
  width: 100%;
  height: 100%;
  position: relative;
}

.carousel-container {
  position: relative;
  width: 100%;
  height: 100%;
  overflow: hidden;
  border-radius: 16px;
  background: #f3f4f6;
}

.carousel-images {
  position: relative;
  width: 100%;
  height: 100%;
}

.carousel-image {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Transiciones */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}

.fade-enter-from {
  opacity: 0;
}

.fade-leave-to {
  opacity: 0;
}

/* Flechas de navegación */
.carousel-arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(8px);
  border: none;
  color: white;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s;
  z-index: 10;
  opacity: 0;
}

.carousel-container:hover .carousel-arrow {
  opacity: 1;
}

.carousel-arrow:hover {
  background: rgba(0, 0, 0, 0.7);
  transform: translateY(-50%) scale(1.1);
}

.carousel-arrow:active {
  transform: translateY(-50%) scale(0.95);
}

.carousel-arrow-left {
  left: 16px;
}

.carousel-arrow-right {
  right: 16px;
}

/* Indicadores */
.carousel-indicators {
  position: absolute;
  bottom: 16px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 8px;
  z-index: 10;
}

.indicator {
  width: 40px;
  height: 4px;
  background: rgba(255, 255, 255, 0.5);
  border: none;
  border-radius: 2px;
  cursor: pointer;
  transition: all 0.3s;
  padding: 0;
}

.indicator:hover {
  background: rgba(255, 255, 255, 0.8);
}

.indicator.active {
  background: white;
  width: 48px;
}

/* Contador */
.carousel-counter {
  position: absolute;
  top: 16px;
  right: 16px;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(8px);
  color: white;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 600;
  z-index: 10;
}

/* Responsive */
@media (max-width: 768px) {
  .carousel-arrow {
    width: 40px;
    height: 40px;
  }

  .carousel-arrow-left {
    left: 8px;
  }

  .carousel-arrow-right {
    right: 8px;
  }

  .indicator {
    width: 32px;
    height: 3px;
  }

  .indicator.active {
    width: 40px;
  }

  .carousel-counter {
    font-size: 0.75rem;
    padding: 4px 10px;
  }
}

/* Animación de carga */
@keyframes shimmer {
  0% {
    background-position: -1000px 0;
  }
  100% {
    background-position: 1000px 0;
  }
}

.carousel-container::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, #f0f0f0 0px, #f8f8f8 40px, #f0f0f0 80px);
  background-size: 1000px;
  animation: shimmer 2s infinite;
  z-index: 1;
  pointer-events: none;
  opacity: 0;
  transition: opacity 0.3s;
}

.carousel-image:not([src]) ~ .carousel-container::before {
  opacity: 1;
}
</style>
