<template>
  <div class="habitaciones-module">
    <!-- Header con acción y fecha actual -->
    <div class="page-header">
      <div class="header-content">
        <div class="header-text">
          <h1 class="page-title">Gestión de Habitaciones</h1>
          <p class="page-description">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
              <line x1="16" y1="2" x2="16" y2="6"/>
              <line x1="8" y1="2" x2="8" y2="6"/>
              <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            {{ fechaActual }} - Estado actualizado en tiempo real
          </p>
        </div>
        <button @click="cargarHabitaciones" class="btn-refresh" :disabled="loading">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/>
          </svg>
          <span>Actualizar</span>
        </button>
      </div>
    </div>

    <!-- Estadísticas mejoradas -->
    <div class="stats-container">
      <div class="stat-card available">
        <div class="stat-icon-wrapper">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
          </svg>
        </div>
        <div class="stat-info">
          <span class="stat-label">Disponibles</span>
          <span class="stat-value">{{ habitacionesDisponibles }}</span>
        </div>
      </div>

      <div class="stat-card occupied">
        <div class="stat-icon-wrapper">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
          </svg>
        </div>
        <div class="stat-info">
          <span class="stat-label">Ocupadas</span>
          <span class="stat-value">{{ habitacionesOcupadas }}</span>
        </div>
      </div>

      <div class="stat-card maintenance">
        <div class="stat-icon-wrapper">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
          </svg>
        </div>
        <div class="stat-info">
          <span class="stat-label">Mantenimiento</span>
          <span class="stat-value">{{ habitacionesMantenimiento }}</span>
        </div>
      </div>

      <div class="stat-card total">
        <div class="stat-icon-wrapper">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
            <polyline points="9 22 9 12 15 12 15 22"/>
          </svg>
        </div>
        <div class="stat-info">
          <span class="stat-label">Total Habitaciones</span>
          <span class="stat-value">{{ habitaciones.length }}</span>
        </div>
      </div>
    </div>

    <!-- Filtros elegantes -->
    <div class="filters-section">
      <div class="search-container">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"/>
          <path d="m21 21-4.35-4.35"/>
        </svg>
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Buscar habitación por tipo, número o piso..." 
          class="search-input"
        />
      </div>
      
      <div class="filter-buttons">
        <button 
          @click="filtroEstado = ''" 
          :class="['filter-btn', { active: filtroEstado === '' }]"
        >
          Todas
        </button>
        <button 
          @click="filtroEstado = 'disponible'" 
          :class="['filter-btn available', { active: filtroEstado === 'disponible' }]"
        >
          Disponibles
        </button>
        <button 
          @click="filtroEstado = 'ocupada'" 
          :class="['filter-btn occupied', { active: filtroEstado === 'ocupada' }]"
        >
          Ocupadas
        </button>
        <button 
          @click="filtroEstado = 'mantenimiento'" 
          :class="['filter-btn maintenance', { active: filtroEstado === 'mantenimiento' }]"
        >
          Mantenimiento
        </button>
      </div>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="loading-container">
      <div class="loading-spinner-large"></div>
      <p>Cargando habitaciones...</p>
    </div>

    <!-- Grid de habitaciones tipo hotel de lujo -->
    <div v-else class="rooms-grid">
      <div 
        v-for="hab in habitacionesFiltradas" 
        :key="hab.id" 
        :class="['room-card', hab.estado]"
      >
        <!-- Badge de estado -->
        <div :class="['room-status-badge', hab.estado]">
          <div class="status-dot"></div>
          {{ estadoLabel(hab.estado) }}
        </div>

        <!-- Imagen de la habitación (placeholder) -->
        <div class="room-image">
          <div class="room-overlay">
            <span class="room-number">Habitación {{ hab.numero || `#${hab.id}` }}</span>
            <span class="room-floor">Piso {{ hab.piso }}</span>
          </div>
        </div>

        <!-- Información de la habitación -->
        <div class="room-info">
          <h3 class="room-type">{{ hab.tipo.nombre }}</h3>
          
          <div class="room-details">
            <div class="detail-item">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
              </svg>
              <span>{{ hab.tipo.capacidad || 2 }} personas</span>
            </div>
            <div class="detail-item price">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
              </svg>
              <span>${{ Number(hab.tipo.precio_base).toLocaleString('es-CO') }}/noche</span>
            </div>
          </div>

          <!-- Reserva actual -->
          <div v-if="hab.reserva_actual" class="current-reservation">
            <div class="reservation-header">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
              </svg>
              <span>Reserva Activa</span>
            </div>
            <p class="guest-name">{{ hab.reserva_actual.cliente }}</p>
            <p class="reservation-dates">
              {{ formatDate(hab.reserva_actual.fecha_entrada) }} - {{ formatDate(hab.reserva_actual.fecha_salida) }}
            </p>
          </div>
          <div v-else class="no-reservation">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <line x1="12" y1="8" x2="12" y2="12"/>
              <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span>Sin reserva activa</span>
          </div>

          <!-- Acciones -->
          <div class="room-actions">
            <button @click="cambiarEstado(hab)" class="action-btn primary" title="Cambiar estado">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
              </svg>
              <span>Estado</span>
            </button>
            <button @click="editarPrecio(hab)" class="action-btn secondary" title="Editar precio">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
              </svg>
              <span>Precio</span>
            </button>
            <button 
              v-if="hab.estado === 'ocupada' && hab.reserva_actual" 
              @click="verReserva(hab)" 
              class="action-btn info"
              title="Ver detalles de reserva"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
              <span>Ver</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de cambio de estado -->
    <div v-if="modalEstado" class="modal-overlay" @click="cerrarModalEstado">
      <div class="modal-container" @click.stop>
        <div class="modal-header">
          <h2>Cambiar Estado de Habitación</h2>
          <button @click="cerrarModalEstado" class="modal-close">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"/>
              <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
        
        <div class="modal-body">
          <div class="room-preview">
            <span class="preview-label">Habitación</span>
            <span class="preview-value">{{ habitacionEditando?.numero || `#${habitacionEditando?.id}` }} - {{ habitacionEditando?.tipo.nombre }}</span>
          </div>

          <div class="estado-selector">
            <label class="estado-option">
              <input type="radio" v-model="nuevoEstado" value="disponible" />
              <div class="option-card available">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <span class="option-title">Disponible</span>
                <span class="option-desc">Lista para nuevas reservas</span>
              </div>
            </label>

            <label class="estado-option">
              <input type="radio" v-model="nuevoEstado" value="ocupada" />
              <div class="option-card occupied">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                  <circle cx="9" cy="7" r="4"/>
                </svg>
                <span class="option-title">Ocupada</span>
                <span class="option-desc">Huésped actualmente alojado</span>
              </div>
            </label>

            <label class="estado-option">
              <input type="radio" v-model="nuevoEstado" value="mantenimiento" />
              <div class="option-card maintenance">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                </svg>
                <span class="option-title">Mantenimiento</span>
                <span class="option-desc">Requiere limpieza o reparación</span>
              </div>
            </label>
          </div>
        </div>

        <div class="modal-footer">
          <button @click="cerrarModalEstado" class="btn-cancel">Cancelar</button>
          <button @click="guardarEstado" class="btn-save">Guardar Cambios</button>
        </div>
      </div>
    </div>

    <!-- Modal de edición de precio -->
    <div v-if="modalPrecio" class="modal-overlay" @click="cerrarModalPrecio">
      <div class="modal-container" @click.stop>
        <div class="modal-header">
          <h2>Editar Precio del Tipo de Habitación</h2>
          <button @click="cerrarModalPrecio" class="modal-close">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"/>
              <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
        
        <div class="modal-body">
          <div class="alert-info">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <line x1="12" y1="16" x2="12" y2="12"/>
              <line x1="12" y1="8" x2="12.01" y2="8"/>
            </svg>
            <span>Este cambio afectará a todas las habitaciones del tipo: <strong>{{ habitacionEditando?.tipo.nombre }}</strong></span>
          </div>

          <div class="form-group-modern">
            <label>Precio por Noche (COP)</label>
            <div class="input-group">
              <span class="input-prefix">$</span>
              <input
                type="number"
                v-model.number="nuevoPrecio"
                class="input-modern"
                placeholder="Ej: 150000"
                min="0"
                step="1000"
              />
            </div>
            <span class="input-hint">Precio actual: ${{ Number(habitacionEditando?.tipo.precio_base).toLocaleString('es-CO') }}</span>
          </div>
        </div>

        <div class="modal-footer">
          <button @click="cerrarModalPrecio" class="btn-cancel">Cancelar</button>
          <button @click="guardarPrecio" class="btn-save">Actualizar Precio</button>
        </div>
      </div>
    </div>

    <!-- Modal de detalles de reserva -->
    <div v-if="modalReserva" class="modal-overlay" @click="cerrarModalReserva">
      <div class="modal-container large" @click.stop>
        <div class="modal-header">
          <h2>Detalles de la Reserva</h2>
          <button @click="cerrarModalReserva" class="modal-close">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"/>
              <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
        
        <div class="modal-body" v-if="reservaDetalle">
          <div class="reservation-details">
            <div class="detail-section">
              <h3>Información del Huésped</h3>
              <div class="detail-grid">
                <div class="detail-row">
                  <span class="label">Nombre Completo</span>
                  <span class="value">{{ reservaDetalle.cliente.nombre }} {{ reservaDetalle.cliente.apellido }}</span>
                </div>
                <div class="detail-row">
                  <span class="label">Cédula</span>
                  <span class="value">{{ reservaDetalle.cliente.cedula }}</span>
                </div>
                <div class="detail-row">
                  <span class="label">Email</span>
                  <span class="value">{{ reservaDetalle.cliente.email }}</span>
                </div>
                <div class="detail-row">
                  <span class="label">Teléfono</span>
                  <span class="value">{{ reservaDetalle.cliente.telefono }}</span>
                </div>
              </div>
            </div>

            <div class="detail-section">
              <h3>Detalles de la Estadía</h3>
              <div class="detail-grid">
                <div class="detail-row">
                  <span class="label">Check-in</span>
                  <span class="value">{{ formatDate(reservaDetalle.fecha_entrada) }}</span>
                </div>
                <div class="detail-row">
                  <span class="label">Check-out</span>
                  <span class="value">{{ formatDate(reservaDetalle.fecha_salida) }}</span>
                </div>
                <div class="detail-row">
                  <span class="label">Huéspedes</span>
                  <span class="value">{{ reservaDetalle.num_adultos }} adultos, {{ reservaDetalle.num_ninos }} niños</span>
                </div>
                <div class="detail-row highlight">
                  <span class="label">Total a Pagar</span>
                  <span class="value">${{ Number(reservaDetalle.precio_total).toLocaleString('es-CO') }} COP</span>
                </div>
              </div>
            </div>

            <div v-if="reservaDetalle.servicios_adicionales && reservaDetalle.servicios_adicionales.length > 0" class="detail-section">
              <h3>Servicios Adicionales</h3>
              <div class="services-list">
                <div v-for="servicio in reservaDetalle.servicios_adicionales" :key="servicio.nombre" class="service-item">
                  <span class="service-name">{{ servicio.nombre }} (x{{ servicio.cantidad }})</span>
                  <span class="service-price">${{ Number(servicio.precio * servicio.cantidad).toLocaleString('es-CO') }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button @click="cerrarModalReserva" class="btn-save full-width">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'

const habitaciones = ref([])
const loading = ref(false)
const searchQuery = ref('')
const filtroEstado = ref('')

const modalEstado = ref(false)
const modalPrecio = ref(false)
const modalReserva = ref(false)
const habitacionEditando = ref(null)
const nuevoEstado = ref('')
const nuevoPrecio = ref(0)
const reservaDetalle = ref(null)

const habitacionesFiltradas = computed(() => {
  let result = habitaciones.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(h =>
      h.numero?.toLowerCase().includes(query) ||
      h.tipo.nombre.toLowerCase().includes(query) ||
      h.piso.toString().includes(query) ||
      h.id.toString().includes(query)
    )
  }

  if (filtroEstado.value) {
    result = result.filter(h => h.estado === filtroEstado.value)
  }

  return result
})

const habitacionesDisponibles = computed(() =>
  habitaciones.value.filter(h => h.estado === 'disponible').length
)

const habitacionesOcupadas = computed(() =>
  habitaciones.value.filter(h => h.estado === 'ocupada').length
)

const habitacionesMantenimiento = computed(() =>
  habitaciones.value.filter(h => h.estado === 'mantenimiento').length
)

const fechaActual = computed(() => {
  const hoy = new Date()
  const opciones = {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  }
  return hoy.toLocaleDateString('es-ES', opciones)
})

async function cargarHabitaciones() {
  loading.value = true
  try {
    const response = await api.get('/admin/habitaciones')
    if (response.data.success) {
      habitaciones.value = response.data.habitaciones
    }
  } catch (error) {
    console.error('Error al cargar habitaciones:', error)
  } finally {
    loading.value = false
  }
}

function cambiarEstado(habitacion) {
  habitacionEditando.value = habitacion
  nuevoEstado.value = habitacion.estado
  modalEstado.value = true
}

function cerrarModalEstado() {
  modalEstado.value = false
  habitacionEditando.value = null
  nuevoEstado.value = ''
}

async function guardarEstado() {
  try {
    const response = await api.put(`/admin/habitaciones/${habitacionEditando.value.id}/estado`, {
      estado: nuevoEstado.value
    })

    if (response.data.success) {
      cerrarModalEstado()
      await cargarHabitaciones()
    }
  } catch (error) {
    console.error('Error al actualizar estado:', error)
  }
}

function editarPrecio(habitacion) {
  habitacionEditando.value = habitacion
  nuevoPrecio.value = habitacion.tipo.precio_base
  modalPrecio.value = true
}

function cerrarModalPrecio() {
  modalPrecio.value = false
  habitacionEditando.value = null
  nuevoPrecio.value = 0
}

async function guardarPrecio() {
  try {
    const response = await api.put(`/admin/tipos-habitacion/${habitacionEditando.value.tipo.id}/precio`, {
      precio_base: nuevoPrecio.value
    })

    if (response.data.success) {
      cerrarModalPrecio()
      await cargarHabitaciones()
    }
  } catch (error) {
    console.error('Error al actualizar precio:', error)
  }
}

async function verReserva(habitacion) {
  try {
    const response = await api.get(`/admin/habitaciones/${habitacion.id}/reserva`)

    if (response.data.success) {
      reservaDetalle.value = response.data.reserva
      modalReserva.value = true
    }
  } catch (error) {
    console.error('Error al cargar reserva:', error)
  }
}

function cerrarModalReserva() {
  modalReserva.value = false
  reservaDetalle.value = null
}

function estadoLabel(estado) {
  const labels = {
    disponible: 'Disponible',
    ocupada: 'Ocupada',
    mantenimiento: 'Mantenimiento'
  }
  return labels[estado] || estado
}

function formatDate(date) {
  if (!date) return ''
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

onMounted(() => {
  cargarHabitaciones()
})
</script>

<style scoped>
@import '@/assets/habitaciones-premium.css';
</style>
