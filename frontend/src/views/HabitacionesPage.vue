<template>
  <div class="habitaciones-page">
    <!-- Hero Section -->
    <section class="hero-habitaciones">
      <div class="hero-overlay"></div>
      <div class="hero-content">
        <h1 class="hero-title">Nuestras Habitaciones</h1>
        <p class="hero-subtitle">Espacios diseñados para tu confort y descanso</p>
      </div>
    </section>

    <!-- Habitaciones Grid -->
    <section class="habitaciones-section">
      <div class="container">
        <!-- Filtros -->
        <div class="filtros-container">
          <button
            :class="['filtro-btn', { active: filtroActivo === 'todos' }]"
            @click="filtroActivo = 'todos'"
          >
            Todas las Habitaciones
          </button>
          <button
            :class="['filtro-btn', { active: filtroActivo === 'apartamento' }]"
            @click="filtroActivo = 'apartamento'"
          >
            🏠 Apartamentos
          </button>
          <button
            :class="['filtro-btn', { active: filtroActivo === 'habitacion' }]"
            @click="filtroActivo = 'habitacion'"
          >
            🛏️ Habitaciones
          </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="loading">
          <div class="spinner"></div>
          <p>Cargando habitaciones...</p>
        </div>

        <!-- Grid de Habitaciones -->
        <div v-else class="habitaciones-grid">
          <div
            v-for="habitacion in habitacionesFiltradas"
            :key="habitacion.id"
            class="habitacion-card card"
          >
            <!-- Carrusel de Imágenes -->
            <div class="card-image-wrapper">
              <RoomCarousel :habitacion-id="habitacion.id" />
              <span class="tipo-badge">
                {{ habitacion.es_apartamento ? 'Apartamento' : 'Habitación' }}
              </span>
            </div>

            <!-- Contenido -->
            <div class="card-content">
              <h3 class="habitacion-nombre">{{ habitacion.nombre }}</h3>

              <!-- Detalles -->
              <div class="habitacion-detalles">
                <div class="detalle-item">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                  </svg>
                  <span>{{ habitacion.descripcion_camas }}</span>
                </div>
                <div class="detalle-item">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                  </svg>
                  <span>{{ habitacion.capacidad_adultos }} adulto{{ habitacion.capacidad_adultos > 1 ? 's' : '' }}</span>
                  <span v-if="habitacion.capacidad_ninos > 0"> + {{ habitacion.capacidad_ninos }} niño{{ habitacion.capacidad_ninos > 1 ? 's' : '' }}</span>
                </div>
                <div class="detalle-item">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                  </svg>
                  <span>{{ habitacion.metros_cuadrados }} m²</span>
                </div>
              </div>

              <!-- Amenidades -->
              <div class="amenidades-grid">
                <div
                  v-for="(amenidad, index) in habitacion.amenidades.slice(0, 6)"
                  :key="index"
                  class="amenidad-item"
                >
                  ✓ {{ amenidad }}
                </div>
              </div>

              <!-- Precio y Botón -->
              <div class="card-footer">
                <div class="precio-info">
                  <span class="precio-label">Desde</span>
                  <span class="precio-valor">{{ formatCurrency(habitacion.precio_base) }}</span>
                  <span class="precio-periodo">por noche</span>
                </div>
                <router-link :to="`/reservar?habitacion=${habitacion.id}`" class="btn btn-primary">
                  Reservar
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                  </svg>
                </router-link>
              </div>
            </div>
          </div>
        </div>

        <!-- Sin resultados -->
        <div v-if="!loading && habitacionesFiltradas.length === 0" class="no-results">
          <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
          </svg>
          <h3>No se encontraron habitaciones</h3>
          <p>Intenta cambiar los filtros de búsqueda</p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import RoomCarousel from '../components/RoomCarousel.vue'
import api from '../services/api'
import { formatCurrency } from '../utils/formatters'

const loading = ref(false)
const habitaciones = ref([])
const filtroActivo = ref('todos')

const habitacionesFiltradas = computed(() => {
  if (filtroActivo.value === 'todos') {
    return habitaciones.value
  } else if (filtroActivo.value === 'apartamento') {
    return habitaciones.value.filter(h => h.es_apartamento)
  } else {
    return habitaciones.value.filter(h => !h.es_apartamento)
  }
})

async function cargarHabitaciones() {
  loading.value = true
  try {
    const response = await api.get('')
    if (response.data.success) {
      habitaciones.value = response.data.tipos
    }
  } catch (error) {
    console.error('Error al cargar habitaciones:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  cargarHabitaciones()
})
</script>

<style scoped>
.habitaciones-page {
  min-height: 100vh;
  background: var(--gray-50);
}

/* Hero Section */
.hero-habitaciones {
  position: relative;
  height: 40vh;
  min-height: 300px;
  background-image: url('/images/portada.jpg');
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 80px;
}

.hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: var(--gradient-hero);
  z-index: 1;
}

.hero-content {
  position: relative;
  z-index: 2;
  text-align: center;
  color: var(--white);
  padding: 0 var(--space-xl);
}

.hero-title {
  font-family: var(--font-display);
  font-size: var(--text-5xl);
  font-weight: 700;
  margin-bottom: var(--space-md);
  text-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.hero-subtitle {
  font-size: var(--text-xl);
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  opacity: 0.95;
}

/* Habitaciones Section */
.habitaciones-section {
  padding: var(--space-3xl) 0;
}

.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 var(--space-xl);
}

/* Filtros */
.filtros-container {
  display: flex;
  gap: var(--space-md);
  margin-bottom: var(--space-3xl);
  flex-wrap: wrap;
  justify-content: center;
}

.filtro-btn {
  padding: var(--space-md) var(--space-2xl);
  background: var(--white);
  color: var(--gray-700);
  border: 2px solid var(--gray-300);
  border-radius: var(--radius-full);
  font-weight: 600;
  font-size: var(--text-base);
  cursor: pointer;
  transition: all var(--transition-base) var(--ease);
}

.filtro-btn:hover {
  border-color: var(--primary);
  color: var(--primary);
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.filtro-btn.active {
  background: var(--gradient-primary);
  color: var(--white);
  border-color: var(--primary);
  box-shadow: var(--shadow-primary);
}

/* Loading */
.loading {
  text-align: center;
  padding: var(--space-3xl);
  color: var(--gray-600);
}

.spinner {
  border: 4px solid var(--gray-200);
  border-top: 4px solid var(--primary);
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 1s linear infinite;
  margin: 0 auto var(--space-lg);
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Grid de Habitaciones */
.habitaciones-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
  gap: var(--space-2xl);
}

.habitacion-card {
  display: flex;
  flex-direction: column;
  overflow: hidden;
  transition: all var(--transition-base) var(--ease);
  height: 100%;
}

.habitacion-card:hover {
  transform: translateY(-8px);
  box-shadow: var(--shadow-xl);
}

/* Imagen */
.card-image-wrapper {
  position: relative;
  height: 280px;
  overflow: hidden;
}

.tipo-badge {
  position: absolute;
  top: var(--space-lg);
  right: var(--space-lg);
  padding: var(--space-sm) var(--space-lg);
  background: rgba(255, 255, 255, 0.95);
  color: var(--primary);
  font-weight: 600;
  font-size: var(--text-sm);
  border-radius: var(--radius-full);
  box-shadow: var(--shadow-md);
  z-index: 10;
  backdrop-filter: blur(10px);
}

/* Contenido */
.card-content {
  padding: var(--space-2xl);
  display: flex;
  flex-direction: column;
  flex: 1;
}

.habitacion-nombre {
  font-family: var(--font-display);
  font-size: var(--text-2xl);
  color: var(--gray-900);
  margin-bottom: var(--space-lg);
}

.habitacion-detalles {
  display: flex;
  flex-direction: column;
  gap: var(--space-sm);
  margin-bottom: var(--space-lg);
  padding-bottom: var(--space-lg);
  border-bottom: 1px solid var(--gray-200);
}

.detalle-item {
  display: flex;
  align-items: center;
  gap: var(--space-sm);
  color: var(--gray-600);
  font-size: var(--text-base);
}

.detalle-item svg {
  color: var(--primary);
  flex-shrink: 0;
}

/* Amenidades */
.amenidades-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--space-sm);
  margin-bottom: var(--space-lg);
  padding-bottom: var(--space-lg);
  border-bottom: 1px solid var(--gray-200);
}

.amenidad-item {
  color: var(--success);
  font-size: var(--text-sm);
  display: flex;
  align-items: center;
  gap: var(--space-xs);
}

/* Footer de la Card */
.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: var(--space-lg);
  margin-top: auto;
  padding-top: var(--space-lg);
  border-top: 1px solid var(--gray-200);
}

.precio-info {
  display: flex;
  flex-direction: column;
  gap: var(--space-xs);
}

.precio-label {
  font-size: var(--text-sm);
  color: var(--gray-500);
}

.precio-valor {
  font-size: var(--text-3xl);
  font-weight: 700;
  color: var(--primary);
  line-height: 1;
}

.precio-periodo {
  font-size: var(--text-sm);
  color: var(--gray-600);
}

/* Sin resultados */
.no-results {
  text-align: center;
  padding: var(--space-3xl);
  color: var(--gray-500);
  grid-column: 1 / -1;
}

.no-results svg {
  margin-bottom: var(--space-lg);
  opacity: 0.3;
}

.no-results h3 {
  font-size: var(--text-2xl);
  color: var(--gray-700);
  margin-bottom: var(--space-md);
}

/* Responsive */
@media (max-width: 768px) {
  .hero-habitaciones {
    height: 30vh;
    min-height: 250px;
  }

  .hero-title {
    font-size: var(--text-3xl);
  }

  .hero-subtitle {
    font-size: var(--text-base);
  }

  .habitaciones-grid {
    grid-template-columns: 1fr;
  }

  .filtros-container {
    flex-direction: column;
  }

  .filtro-btn {
    width: 100%;
  }

  .card-footer {
    flex-direction: column;
    align-items: stretch;
  }

  .precio-info {
    text-align: center;
  }
}
</style>
