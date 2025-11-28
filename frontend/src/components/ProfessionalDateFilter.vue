<template>
  <div class="professional-date-filter">
    <div class="filter-card">
      <!-- Header -->
      <div class="filter-header">
        <div class="header-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
          </svg>
        </div>
        <div class="header-content">
          <h3>Buscar Disponibilidad</h3>
          <p>Selecciona las fechas de tu estadía</p>
        </div>
      </div>

      <!-- Filtros principales -->
      <div class="filter-grid">
        <!-- Fecha Entrada -->
        <div class="filter-item">
          <label for="fecha-entrada">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
            Entrada
          </label>
          <input
            id="fecha-entrada"
            type="date"
            v-model="filtros.fechaEntrada"
            :min="minDate"
            class="date-input"
            @change="handleFechaEntradaChange"
          />
          <div v-if="filtros.fechaEntrada" class="date-display">
            {{ formatDateDisplay(filtros.fechaEntrada) }}
          </div>
        </div>

        <!-- Fecha Salida -->
        <div class="filter-item">
          <label for="fecha-salida">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            Salida
          </label>
          <input
            id="fecha-salida"
            type="date"
            v-model="filtros.fechaSalida"
            :min="minDateSalida"
            class="date-input"
            :disabled="!filtros.fechaEntrada"
          />
          <div v-if="filtros.fechaSalida" class="date-display">
            {{ formatDateDisplay(filtros.fechaSalida) }}
          </div>
        </div>

        <!-- Huéspedes -->
        <div class="filter-item filter-item-guests">
          <label>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
              <circle cx="9" cy="7" r="4"></circle>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            Huéspedes
          </label>

          <div class="guests-selector">
            <!-- Adultos -->
            <div class="guest-control">
              <span class="guest-label">Adultos</span>
              <div class="guest-counter">
                <button
                  type="button"
                  @click="decrementAdults"
                  :disabled="filtros.adultos <= 1"
                  class="counter-btn"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                  </svg>
                </button>
                <span class="counter-value">{{ filtros.adultos }}</span>
                <button
                  type="button"
                  @click="incrementAdults"
                  :disabled="filtros.adultos >= 10"
                  class="counter-btn"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Niños -->
            <div class="guest-control">
              <span class="guest-label">Niños</span>
              <div class="guest-counter">
                <button
                  type="button"
                  @click="decrementChildren"
                  :disabled="filtros.ninos <= 0"
                  class="counter-btn"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                  </svg>
                </button>
                <span class="counter-value">{{ filtros.ninos }}</span>
                <button
                  type="button"
                  @click="incrementChildren"
                  :disabled="filtros.ninos >= 10"
                  class="counter-btn"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Info de estadía -->
      <div v-if="duracionEstadia" class="stay-info">
        <div class="stay-info-item">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>
          </svg>
          <span><strong>{{ duracionEstadia }}</strong> {{ duracionEstadia === 1 ? 'noche' : 'noches' }}</span>
        </div>
        <div class="stay-info-item">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
          <span><strong>{{ totalHuespedes }}</strong> {{ totalHuespedes === 1 ? 'huésped' : 'huéspedes' }}</span>
        </div>
      </div>

      <!-- Botón de búsqueda -->
      <button
        type="button"
        @click="buscar"
        :disabled="!canSearch"
        class="search-button"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"></circle>
          <path d="m21 21-4.35-4.35"></path>
        </svg>
        <span>Buscar Habitaciones</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const emit = defineEmits(['search'])

const filtros = ref({
  fechaEntrada: '',
  fechaSalida: '',
  adultos: 2,
  ninos: 0
})

// Fecha mínima (hoy)
const minDate = computed(() => {
  const today = new Date()
  return today.toISOString().split('T')[0]
})

// Fecha mínima de salida (día siguiente a entrada)
const minDateSalida = computed(() => {
  if (!filtros.value.fechaEntrada) return minDate.value

  const entrada = new Date(filtros.value.fechaEntrada)
  entrada.setDate(entrada.getDate() + 1)
  return entrada.toISOString().split('T')[0]
})

// Duración de la estadía
const duracionEstadia = computed(() => {
  if (!filtros.value.fechaEntrada || !filtros.value.fechaSalida) return null

  const entrada = new Date(filtros.value.fechaEntrada)
  const salida = new Date(filtros.value.fechaSalida)
  const diff = Math.ceil((salida - entrada) / (1000 * 60 * 60 * 24))

  return diff > 0 ? diff : null
})

// Total de huéspedes
const totalHuespedes = computed(() => {
  return filtros.value.adultos + filtros.value.ninos
})

// Verificar si se puede buscar
const canSearch = computed(() => {
  return filtros.value.fechaEntrada && filtros.value.fechaSalida && duracionEstadia.value > 0
})

// Formatear fecha para mostrar
const formatDateDisplay = (dateString) => {
  const date = new Date(dateString)
  const options = { weekday: 'short', day: 'numeric', month: 'short' }
  return date.toLocaleDateString('es-ES', options)
}

// Ajustar fecha de salida si es antes de entrada
const handleFechaEntradaChange = () => {
  if (filtros.value.fechaSalida) {
    const entrada = new Date(filtros.value.fechaEntrada)
    const salida = new Date(filtros.value.fechaSalida)

    if (salida <= entrada) {
      const newSalida = new Date(entrada)
      newSalida.setDate(newSalida.getDate() + 1)
      filtros.value.fechaSalida = newSalida.toISOString().split('T')[0]
    }
  }
}

// Control de huéspedes
const incrementAdults = () => {
  if (filtros.value.adultos < 10) filtros.value.adultos++
}

const decrementAdults = () => {
  if (filtros.value.adultos > 1) filtros.value.adultos--
}

const incrementChildren = () => {
  if (filtros.value.ninos < 10) filtros.value.ninos++
}

const decrementChildren = () => {
  if (filtros.value.ninos > 0) filtros.value.ninos--
}

// Buscar
const buscar = () => {
  if (canSearch.value) {
    emit('search', { ...filtros.value })
  }
}
</script>

<style scoped>
.professional-date-filter {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
}

.filter-card {
  background: white;
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

/* Header */
.filter-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid #f3f4f6;
}

.header-icon {
  width: 56px;
  height: 56px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.header-content h3 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 0.25rem 0;
}

.header-content p {
  font-size: 0.9rem;
  color: #6b7280;
  margin: 0;
}

/* Grid de filtros */
.filter-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1.5fr;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.filter-item {
  display: flex;
  flex-direction: column;
}

.filter-item label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.75rem;
}

.filter-item label svg {
  color: #667eea;
}

.date-input {
  width: 100%;
  padding: 0.875rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  font-size: 1rem;
  transition: all 0.3s;
  cursor: pointer;
}

.date-input:hover {
  border-color: #667eea;
}

.date-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.date-input:disabled {
  background: #f9fafb;
  cursor: not-allowed;
  opacity: 0.6;
}

.date-display {
  margin-top: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #667eea;
}

/* Selector de huéspedes */
.guests-selector {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 12px;
}

.guest-control {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.guest-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
}

.guest-counter {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.counter-btn {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: 2px solid #e5e7eb;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s;
  color: #667eea;
}

.counter-btn:hover:not(:disabled) {
  border-color: #667eea;
  background: #f3f4f6;
  transform: scale(1.05);
}

.counter-btn:active:not(:disabled) {
  transform: scale(0.95);
}

.counter-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.counter-value {
  min-width: 24px;
  text-align: center;
  font-weight: 700;
  font-size: 1.125rem;
  color: #1f2937;
}

/* Info de estadía */
.stay-info {
  display: flex;
  gap: 2rem;
  padding: 1rem;
  background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
  border-radius: 12px;
  margin-bottom: 1.5rem;
}

.stay-info-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #374151;
}

.stay-info-item svg {
  color: #667eea;
}

/* Botón de búsqueda */
.search-button {
  width: 100%;
  padding: 1rem 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1.125rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.search-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
}

.search-button:active:not(:disabled) {
  transform: translateY(0);
}

.search-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

/* Responsive */
@media (max-width: 1024px) {
  .filter-grid {
    grid-template-columns: 1fr 1fr;
  }

  .filter-item-guests {
    grid-column: 1 / -1;
  }
}

@media (max-width: 768px) {
  .filter-card {
    padding: 1.5rem;
  }

  .filter-grid {
    grid-template-columns: 1fr;
  }

  .filter-header {
    flex-direction: column;
    text-align: center;
  }

  .stay-info {
    flex-direction: column;
    gap: 0.75rem;
  }
}
</style>
