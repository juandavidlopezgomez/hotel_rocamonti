<template>
  <div class="reserva-page">
    <div class="container">
      <h1 class="title">Reserva tu Estadía</h1>

      <!-- Indicador de pasos mejorado -->
      <div class="steps-indicator">
        <div
          v-for="(step, index) in steps"
          :key="index"
          class="step-item"
          :class="{ active: currentStep === index + 1, completed: currentStep > index + 1 }"
        >
          <div class="step-circle">
            <svg v-if="currentStep > index + 1" class="check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <span v-else>{{ index + 1 }}</span>
          </div>
          <div class="step-label">{{ step }}</div>
        </div>
      </div>

      <!-- PASO 1: Selección de fechas -->
      <div v-if="currentStep === 1" class="step-content">
        <div class="step-header">
          <h2>Selecciona tus Fechas</h2>
          <p>Elige las fechas de tu estadía</p>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label>
              <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
              </svg>
              Fecha de entrada
            </label>
            <input
              v-model="searchForm.fechaInicio"
              type="date"
              :min="minDate"
              class="form-control"
            />
          </div>

          <div class="form-group">
            <label>
              <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
              </svg>
              Fecha de salida
            </label>
            <input
              v-model="searchForm.fechaFin"
              type="date"
              :min="minDateFin"
              class="form-control"
            />
          </div>

          <div class="form-group">
            <label>
              <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
              </svg>
              Número de huéspedes
            </label>
            <input v-model.number="searchForm.personas" type="number" min="1" max="10" class="form-control" />
          </div>
        </div>

        <button @click="buscarHabitaciones" :disabled="!canSearch || loading" class="btn-primary">
          <svg v-if="!loading" class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.35-4.35"></path>
          </svg>
          {{ loading ? 'Buscando...' : 'Buscar Habitaciones Disponibles' }}
        </button>
        <p v-if="error" class="error">{{ error }}</p>
      </div>

      <!-- PASO 2: Mostrar habitaciones disponibles -->
      <div v-if="currentStep === 2" class="step-content">
        <div class="step-header">
          <h2>Elige tu Habitación</h2>
          <p>Selecciona el tipo de habitación que mejor se adapte a tus necesidades</p>
        </div>

        <p v-if="availableRooms.length === 0" class="no-results">
          <svg class="no-results-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
          </svg>
          No hay habitaciones disponibles para las fechas seleccionadas.
        </p>

        <div class="rooms-grid-booking">
          <div v-for="tipo in availableRooms" :key="tipo.tipo.idTipoHabitacion" class="booking-room-card">
            <div class="booking-room-image">
              <img
                :src="tipo.habitaciones_disponibles[0]?.imagenes[0] || 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=500&h=300&fit=crop'"
                :alt="tipo.tipo.Nombre"
              />
              <!-- Indicador de disponibilidad grande -->
              <div class="availability-badge" :class="tipo.cantidad_disponibles > 0 ? 'available' : 'unavailable'">
                <div class="availability-icon">
                  <svg v-if="tipo.cantidad_disponibles > 0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <polyline points="20 6 9 17 4 12"></polyline>
                  </svg>
                  <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                  </svg>
                </div>
                <span class="availability-text">
                  {{ tipo.cantidad_disponibles > 0 ? 'DISPONIBLE' : 'OCUPADO' }}
                </span>
              </div>
            </div>

            <div class="booking-room-content">
              <div class="booking-room-header">
                <h3 class="room-title">{{ tipo.tipo.Nombre }}</h3>
                <div class="room-price-main">
                  <span class="price-value">{{ formatCurrency(tipo.tipo.PrecioBase) }}</span>
                  <span class="price-label">por noche</span>
                </div>
              </div>

              <p class="room-description-text">{{ tipo.tipo.Descripcion }}</p>

              <div class="booking-room-features">
                <div class="feature-item">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                  </svg>
                  <span>{{ tipo.tipo.capacidad }} personas</span>
                </div>
                <div class="feature-item">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                  </svg>
                  <span>{{ tipo.cantidad_disponibles }} disponible{{ tipo.cantidad_disponibles !== 1 ? 's' : '' }}</span>
                </div>
              </div>

              <div class="booking-room-footer">
                <div class="stay-total">
                  <span class="stay-nights">{{ numberOfNights }} {{ numberOfNights === 1 ? 'noche' : 'noches' }}</span>
                  <span class="stay-price">{{ formatCurrency(tipo.tipo.PrecioBase * numberOfNights) }}</span>
                </div>
                <button
                  @click="seleccionarTipo(tipo)"
                  class="btn-booking-select"
                  :disabled="tipo.cantidad_disponibles === 0"
                >
                  <span>Ver habitaciones</span>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
        <button @click="currentStep = 1" class="btn-secondary">
          <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <polyline points="15 18 9 12 15 6"></polyline>
          </svg>
          Volver
        </button>
      </div>

      <!-- PASO 3: Seleccionar habitación específica -->
      <div v-if="currentStep === 3" class="step-content">
        <div class="step-header">
          <h2>Elige una Habitación Específica</h2>
          <p>Todas las habitaciones de este tipo están disponibles para tus fechas</p>
        </div>

        <div class="rooms-grid">
          <div
            v-for="habitacion in tipoSeleccionado.habitaciones_disponibles"
            :key="habitacion.idHabitacion"
            class="room-card"
          >
            <div class="room-image-container">
              <img
                :src="habitacion.imagenes[0] || 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=500&h=300&fit=crop'"
                :alt="habitacion.numeroHabitacion"
                class="room-image"
              />
              <div class="room-badge-number">{{ habitacion.numeroHabitacion }}</div>
            </div>
            <div class="room-info">
              <h3>Habitación {{ habitacion.numeroHabitacion }}</h3>

              <div class="room-details">
                <div class="detail">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                  </svg>
                  Piso {{ habitacion.Piso }}
                </div>
                <div class="detail status-available">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <polyline points="20 6 9 17 4 12"></polyline>
                  </svg>
                  Disponible
                </div>
              </div>

              <div class="room-pricing">
                <div class="price-breakdown">
                  <div class="price-row">
                    <span>{{ formatCurrency(habitacion.precio) }} × {{ numberOfNights }} noches</span>
                  </div>
                  <div class="price-total">
                    <span>Total</span>
                    <span class="total-amount">{{ formatCurrency(habitacion.precio * numberOfNights) }}</span>
                  </div>
                </div>
              </div>

              <button @click="seleccionarHabitacion(habitacion)" class="btn-select">
                Continuar con esta habitación
              </button>
            </div>
          </div>
        </div>
        <button @click="currentStep = 2" class="btn-secondary">
          <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <polyline points="15 18 9 12 15 6"></polyline>
          </svg>
          Volver
        </button>
      </div>

      <!-- PASO 4: Formulario de cliente mejorado -->
      <div v-if="currentStep === 4" class="step-content">
        <div class="step-header">
          <h2>Completa tus Datos</h2>
          <p>Ingresa la información del huésped principal</p>
        </div>

        <form @submit.prevent="continuarAServicios" class="guest-form">
          <div class="form-section">
            <h3 class="section-title">Información Personal</h3>
            <div class="form-grid">
              <div class="form-group">
                <label>
                  <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                  </svg>
                  Cédula *
                </label>
                <input v-model="guestForm.cedula" type="text" required class="form-control" placeholder="1234567890" />
              </div>

              <div class="form-group">
                <label>
                  <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                  </svg>
                  Nombre Completo *
                </label>
                <input v-model="guestForm.nombre" type="text" required class="form-control" placeholder="Ej: Juan" />
              </div>

              <div class="form-group">
                <label>
                  <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                  </svg>
                  Apellido *
                </label>
                <input v-model="guestForm.apellido" type="text" required class="form-control" placeholder="Ej: Pérez" />
              </div>

              <div class="form-group">
                <label>
                  <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                  </svg>
                  Correo Electrónico *
                </label>
                <input v-model="guestForm.email" type="email" required class="form-control" placeholder="correo@ejemplo.com" />
              </div>

              <div class="form-group">
                <label>
                  <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                  </svg>
                  Celular *
                </label>
                <input v-model="guestForm.celular" type="tel" required class="form-control" placeholder="3001234567" />
              </div>

              <div class="form-group">
                <label>
                  <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                  </svg>
                  Teléfono Fijo (Opcional)
                </label>
                <input v-model="guestForm.telefono" type="tel" class="form-control" placeholder="6012345678" />
              </div>

              <div class="form-group full-width">
                <label>
                  <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                  </svg>
                  Dirección (Opcional)
                </label>
                <input v-model="guestForm.direccion" type="text" class="form-control" placeholder="Calle 123 #45-67" />
              </div>

              <div class="form-group">
                <label>
                  <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                  </svg>
                  Ciudad (Opcional)
                </label>
                <input v-model="guestForm.ciudad" type="text" class="form-control" placeholder="Ej: Bogotá" />
              </div>

              <div class="form-group">
                <label>
                  <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                  </svg>
                  País (Opcional)
                </label>
                <input v-model="guestForm.pais" type="text" class="form-control" placeholder="Ej: Colombia" />
              </div>
            </div>
          </div>

          <div class="form-actions">
            <button type="button" @click="currentStep = 3" class="btn-secondary">
              <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <polyline points="15 18 9 12 15 6"></polyline>
              </svg>
              Volver
            </button>
            <button type="submit" class="btn-primary">
              Continuar a Servicios Adicionales
              <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </button>
          </div>
        </form>
        <p v-if="error" class="error">{{ error }}</p>
      </div>

      <!-- PASO 4.5: Servicios Adicionales (NUEVO) -->
      <div v-if="currentStep === 5" class="step-content">
        <div class="step-header">
          <h2>Servicios Adicionales</h2>
          <p>Personaliza tu estadía con servicios extras</p>
        </div>

        <div class="services-grid">
          <div
            v-for="servicio in serviciosAdicionales"
            :key="servicio.id"
            class="service-card"
            :class="{ selected: servicio.selected }"
            @click="toggleServicio(servicio)"
          >
            <div class="service-icon">
              <div v-html="servicio.icon"></div>
            </div>
            <div class="service-info">
              <h3>{{ servicio.nombre }}</h3>
              <p>{{ servicio.descripcion }}</p>
              <div class="service-price">{{ formatCurrency(servicio.precio) }}</div>
            </div>
            <div class="service-checkbox">
              <input type="checkbox" :checked="servicio.selected" @click.stop="toggleServicio(servicio)" />
            </div>
          </div>
        </div>

        <div class="services-summary">
          <div class="summary-row">
            <span>Subtotal habitación</span>
            <span>{{ formatCurrency(totalPrice) }}</span>
          </div>
          <div class="summary-row" v-if="totalServiciosAdicionales > 0">
            <span>Servicios adicionales</span>
            <span>{{ formatCurrency(totalServiciosAdicionales) }}</span>
          </div>
          <div class="summary-total">
            <span>Total a pagar</span>
            <span>{{ formatCurrency(totalConServicios) }}</span>
          </div>
        </div>

        <div class="form-actions">
          <button @click="currentStep = 4" class="btn-secondary">
            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            Volver
          </button>
          <button @click="crearReserva" :disabled="loading" class="btn-primary">
            {{ loading ? 'Procesando...' : 'Continuar al Pago' }}
            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
          </button>
        </div>
      </div>

      <!-- PASO 5: Pago con Wompi -->
      <div v-if="currentStep === 6" class="step-content">
        <div class="step-header">
          <h2>Realiza el Pago</h2>
          <p>Último paso para confirmar tu reserva</p>
        </div>

        <div class="payment-summary">
          <h3>Resumen de tu Reserva</h3>

          <div class="summary-section">
            <div class="summary-item">
              <span class="item-label">Habitación</span>
              <span class="item-value">{{ selectedRoom?.numeroHabitacion }} - {{ tipoSeleccionado?.tipo.Nombre }}</span>
            </div>
            <div class="summary-item">
              <span class="item-label">Fechas</span>
              <span class="item-value">{{ formatDate(searchForm.fechaInicio) }} - {{ formatDate(searchForm.fechaFin) }}</span>
            </div>
            <div class="summary-item">
              <span class="item-label">Noches</span>
              <span class="item-value">{{ numberOfNights }}</span>
            </div>
            <div class="summary-item">
              <span class="item-label">Huéspedes</span>
              <span class="item-value">{{ searchForm.personas }} {{ searchForm.personas === 1 ? 'persona' : 'personas' }}</span>
            </div>
          </div>

          <div v-if="serviciosSeleccionados.length > 0" class="summary-section">
            <h4>Servicios Adicionales</h4>
            <div v-for="servicio in serviciosSeleccionados" :key="servicio.id" class="summary-item">
              <span class="item-label">{{ servicio.nombre }}</span>
              <span class="item-value">{{ formatCurrency(servicio.precio) }}</span>
            </div>
          </div>

          <div class="summary-total-section">
            <div class="summary-total-item">
              <span>Total a Pagar</span>
              <span class="total-amount">{{ formatCurrency(totalConServicios) }}</span>
            </div>
          </div>
        </div>

        <div class="payment-actions">
          <button @click="iniciarPago" class="btn-primary btn-large">
            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
              <line x1="1" y1="10" x2="23" y2="10"></line>
            </svg>
            Pagar con Wompi
          </button>
          <button @click="currentStep = 5" class="btn-secondary">
            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            Volver
          </button>
        </div>
      </div>

      <!-- PASO 6: Procesando pago -->
      <div v-if="currentStep === 7" class="step-content">
        <div class="loading-container">
          <div class="loading-spinner"></div>
          <h2>Procesando pago...</h2>
          <p>Por favor espera mientras confirmamos tu pago.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useBookingStore } from '../stores/bookingStore'
import { formatCurrency } from '../utils/currency'
import dayjs from 'dayjs'

const router = useRouter()
const bookingStore = useBookingStore()

const steps = ['Fechas', 'Habitación', 'Habitación Específica', 'Datos', 'Servicios', 'Pago']
const currentStep = ref(1)
const loading = ref(false)
const error = ref(null)

const searchForm = ref({
  fechaInicio: '',
  fechaFin: '',
  personas: 2
})

const guestForm = ref({
  cedula: '',
  nombre: '',
  apellido: '',
  email: '',
  celular: '',
  telefono: '',
  direccion: '',
  ciudad: '',
  pais: 'Colombia',
  aceptaTerminos: false
})

const serviciosAdicionales = ref([
  {
    id: 1,
    nombre: 'Recogida de Huéspedes',
    descripcion: 'Transporte desde el aeropuerto o terminal',
    precio: 50000,
    selected: false,
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M5 17H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-1"></path><polygon points="12 15 17 21 7 21 12 15"></polygon></svg>'
  },
  {
    id: 2,
    nombre: 'Paseo a la Laguna',
    descripcion: 'Tour guiado a la hermosa Laguna de Tota',
    precio: 80000,
    selected: false,
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path></svg>'
  },
  {
    id: 3,
    nombre: 'Parqueadero Adicional',
    descripcion: 'Espacio extra de parqueadero cubierto',
    precio: 20000,
    selected: false,
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>'
  },
  {
    id: 4,
    nombre: 'Cuna para Bebé',
    descripcion: 'Cuna cómoda y segura para tu bebé',
    precio: 30000,
    selected: false,
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>'
  }
])

const tipoSeleccionado = ref(null)

const minDate = computed(() => dayjs().format('YYYY-MM-DD'))
const minDateFin = computed(() => {
  if (searchForm.value.fechaInicio) {
    return dayjs(searchForm.value.fechaInicio).add(1, 'day').format('YYYY-MM-DD')
  }
  return minDate.value
})

const canSearch = computed(() => {
  return (
    searchForm.value.fechaInicio &&
    searchForm.value.fechaFin &&
    searchForm.value.personas > 0 &&
    dayjs(searchForm.value.fechaFin).isAfter(dayjs(searchForm.value.fechaInicio))
  )
})

const availableRooms = computed(() => bookingStore.availableRooms)
const selectedRoom = computed(() => bookingStore.selectedRoom)
const numberOfNights = computed(() => bookingStore.numberOfNights)
const totalPrice = computed(() => bookingStore.totalPrice)

const serviciosSeleccionados = computed(() => {
  return serviciosAdicionales.value.filter(s => s.selected)
})

const totalServiciosAdicionales = computed(() => {
  return serviciosSeleccionados.value.reduce((sum, s) => sum + s.precio, 0)
})

const totalConServicios = computed(() => {
  return totalPrice.value + totalServiciosAdicionales.value
})

async function buscarHabitaciones() {
  try {
    loading.value = true
    error.value = null
    await bookingStore.searchAvailableRooms(searchForm.value, searchForm.value.personas)
    currentStep.value = 2
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al buscar habitaciones'
  } finally {
    loading.value = false
  }
}

function seleccionarTipo(tipo) {
  tipoSeleccionado.value = tipo
  currentStep.value = 3
}

function seleccionarHabitacion(habitacion) {
  bookingStore.selectRoom(habitacion)
  currentStep.value = 4
}

function continuarAServicios() {
  currentStep.value = 5
}

function toggleServicio(servicio) {
  servicio.selected = !servicio.selected
}

async function crearReserva() {
  try {
    loading.value = true
    error.value = null

    // Agregar servicios adicionales al formulario
    const reservaData = {
      ...guestForm.value,
      serviciosAdicionales: serviciosSeleccionados.value.map(s => ({
        id: s.id,
        nombre: s.nombre,
        precio: s.precio
      }))
    }

    await bookingStore.createBooking(reservaData, searchForm.value.personas)
    currentStep.value = 6
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al crear la reserva'
  } finally {
    loading.value = false
  }
}

function iniciarPago() {
  const publicKey = 'pub_prod_GMN9W6Y3sXbp7uE5RXo4cDmPUzkR89y5'
  const amountInCents = totalConServicios.value * 100

  const checkout = new window.WidgetCheckout({
    currency: 'COP',
    amountInCents: amountInCents,
    reference: 'RESERVA-' + bookingStore.currentBooking?.idReserva,
    publicKey: publicKey,
    redirectUrl: window.location.origin + '/reserva/confirmacion/' + bookingStore.currentBooking?.idReserva
  })

  checkout.open((result) => {
    if (result.transaction && result.transaction.status === 'APPROVED') {
      router.push('/reserva/confirmacion/' + bookingStore.currentBooking?.idReserva)
    } else {
      currentStep.value = 7
    }
  })
}

function formatDate(dateString) {
  return dayjs(dateString).format('DD/MM/YYYY')
}

onMounted(() => {
  const script = document.createElement('script')
  script.src = 'https://checkout.wompi.co/widget.js'
  script.async = true
  document.head.appendChild(script)
})
</script>

<style scoped>
.reserva-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem 0;
  padding-top: 100px;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.title {
  text-align: center;
  color: white;
  font-size: 2.5rem;
  margin-bottom: 2rem;
  font-weight: 700;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

/* Steps Indicator Mejorado */
.steps-indicator {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
  margin-bottom: 3rem;
  flex-wrap: wrap;
}

.step-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  flex: 1;
  max-width: 150px;
}

.step-circle {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  color: white;
  transition: all 0.3s ease;
  border: 3px solid transparent;
}

.step-item.active .step-circle {
  background: white;
  color: #667eea;
  transform: scale(1.1);
  box-shadow: 0 4px 15px rgba(255, 255, 255, 0.4);
  border-color: white;
}

.step-item.completed .step-circle {
  background: #10b981;
  border-color: #10b981;
  box-shadow: 0 2px 10px rgba(16, 185, 129, 0.4);
}

.check-icon {
  width: 24px;
  height: 24px;
  stroke-width: 3;
}

.step-label {
  color: white;
  font-size: 0.75rem;
  font-weight: 500;
  text-align: center;
  opacity: 0.8;
}

.step-item.active .step-label,
.step-item.completed .step-label {
  opacity: 1;
  font-weight: 600;
}

/* Step Content */
.step-content {
  background: white;
  border-radius: 20px;
  padding: 2.5rem;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  animation: slideUp 0.4s ease;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.step-header {
  text-align: center;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid #f3f4f6;
}

.step-header h2 {
  color: #1f2937;
  margin-bottom: 0.5rem;
  font-size: 1.875rem;
  font-weight: 700;
}

.step-header p {
  color: #6b7280;
  font-size: 1rem;
}

/* Form Styles */
.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.form-group {
  margin-bottom: 0;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-group label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
  font-weight: 600;
  color: #374151;
  font-size: 0.9rem;
}

.label-icon {
  width: 18px;
  height: 18px;
  stroke-width: 2;
  color: #667eea;
}

.form-control {
  width: 100%;
  padding: 0.875rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  font-size: 1rem;
  transition: all 0.3s ease;
  background: #f9fafb;
}

.form-control:focus {
  outline: none;
  border-color: #667eea;
  background: white;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

/* Form Section */
.form-section {
  margin-bottom: 2rem;
}

.section-title {
  color: #1f2937;
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid #f3f4f6;
}

/* Services Grid */
.services-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.service-card {
  background: #f9fafb;
  border: 2px solid #e5e7eb;
  border-radius: 16px;
  padding: 1.5rem;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  gap: 1rem;
  align-items: start;
}

.service-card:hover {
  border-color: #667eea;
  background: white;
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.15);
}

.service-card.selected {
  border-color: #667eea;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
}

.service-icon {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.service-icon svg {
  width: 28px;
  height: 28px;
  stroke: white;
  stroke-width: 2;
}

.service-info {
  flex: 1;
}

.service-info h3 {
  color: #1f2937;
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.service-info p {
  color: #6b7280;
  font-size: 0.875rem;
  margin-bottom: 0.75rem;
}

.service-price {
  color: #667eea;
  font-weight: 700;
  font-size: 1.1rem;
}

.service-checkbox input[type="checkbox"] {
  width: 24px;
  height: 24px;
  cursor: pointer;
  accent-color: #667eea;
}

/* Services Summary */
.services-summary {
  background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
  border-radius: 16px;
  padding: 1.5rem;
  margin-bottom: 2rem;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 0.75rem 0;
  color: #374151;
  font-size: 1rem;
}

.summary-total {
  display: flex;
  justify-content: space-between;
  padding: 1rem 0;
  margin-top: 1rem;
  border-top: 2px solid #e5e7eb;
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
}

/* Rooms Grid - Estilo Booking.com */
.rooms-grid-booking {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.booking-room-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.2s ease;
  border: 1px solid #e5e7eb;
  display: flex;
  flex-direction: row;
  min-height: 220px;
}

.booking-room-card:hover {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
  transform: translateY(-2px);
}

.booking-room-image {
  position: relative;
  width: 35%;
  min-width: 280px;
  flex-shrink: 0;
}

.booking-room-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Indicador de disponibilidad GRANDE */
.availability-badge {
  position: absolute;
  top: 16px;
  right: 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  border-radius: 12px;
  font-weight: 700;
  font-size: 0.875rem;
  letter-spacing: 0.5px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  backdrop-filter: blur(8px);
  animation: pulse-badge 2s ease-in-out infinite;
}

.availability-badge.available {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.availability-badge.unavailable {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
}

.availability-icon {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.25);
  border-radius: 50%;
  padding: 6px;
}

.availability-icon svg {
  width: 100%;
  height: 100%;
  stroke: white;
}

.availability-text {
  font-size: 0.75rem;
  font-weight: 800;
  text-align: center;
}

@keyframes pulse-badge {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}

.booking-room-content {
  flex: 1;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.booking-room-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  gap: 1rem;
}

.room-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
  line-height: 1.3;
}

.room-price-main {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  flex-shrink: 0;
}

.price-value {
  font-size: 1.75rem;
  font-weight: 800;
  color: #0066FF;
  line-height: 1;
}

.price-label {
  font-size: 0.875rem;
  color: #6b7280;
  margin-top: 4px;
}

.room-description-text {
  color: #6b7280;
  font-size: 0.95rem;
  line-height: 1.5;
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.booking-room-features {
  display: flex;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.feature-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #374151;
  font-size: 0.95rem;
}

.feature-item svg {
  width: 20px;
  height: 20px;
  color: #667eea;
  flex-shrink: 0;
}

.booking-room-footer {
  margin-top: auto;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.stay-total {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.stay-nights {
  font-size: 0.875rem;
  color: #6b7280;
}

.stay-price {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
}

.btn-booking-select {
  background: linear-gradient(135deg, #0066FF 0%, #0052CC 100%);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.btn-booking-select:hover:not(:disabled) {
  background: linear-gradient(135deg, #0052CC 0%, #003D99 100%);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 102, 255, 0.3);
}

.btn-booking-select:disabled {
  background: #d1d5db;
  cursor: not-allowed;
  opacity: 0.6;
}

.btn-booking-select svg {
  width: 18px;
  height: 18px;
}

/* Responsive */
@media (max-width: 768px) {
  .booking-room-card {
    flex-direction: column;
  }

  .booking-room-image {
    width: 100%;
    min-width: 100%;
    height: 200px;
  }

  .booking-room-header {
    flex-direction: column;
    align-items: start;
  }

  .booking-room-footer {
    flex-direction: column;
    align-items: stretch;
  }

  .btn-booking-select {
    justify-content: center;
  }
}

.room-image-container {
  position: relative;
  overflow: hidden;
}

.room-image {
  width: 100%;
  height: 220px;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.room-card:hover .room-image {
  transform: scale(1.05);
}

.room-badge {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: rgba(16, 185, 129, 0.95);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  backdrop-filter: blur(10px);
}

.room-badge-number {
  position: absolute;
  top: 1rem;
  left: 1rem;
  background: rgba(102, 126, 234, 0.95);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  backdrop-filter: blur(10px);
}

.room-info {
  padding: 1.5rem;
}

.room-info h3 {
  color: #1f2937;
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 0.75rem;
}

.room-description {
  color: #6b7280;
  font-size: 0.9rem;
  margin-bottom: 1rem;
  line-height: 1.5;
}

.room-features,
.room-details {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  margin-bottom: 1rem;
}

.feature,
.detail {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #374151;
  font-size: 0.9rem;
}

.feature svg,
.detail svg {
  width: 18px;
  height: 18px;
  stroke-width: 2;
  color: #667eea;
}

.status-available {
  color: #10b981;
  font-weight: 600;
}

.status-available svg {
  color: #10b981;
}

.room-pricing {
  background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
  padding: 1rem;
  border-radius: 12px;
  margin-bottom: 1rem;
}

.price-per-night {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.price-label {
  color: #6b7280;
  font-size: 0.85rem;
}

.price-amount {
  color: #667eea;
  font-size: 1.5rem;
  font-weight: 700;
}

.price-breakdown {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.price-row {
  color: #6b7280;
  font-size: 0.9rem;
}

.price-total {
  display: flex;
  justify-content: space-between;
  padding-top: 0.75rem;
  border-top: 2px solid #e5e7eb;
  font-weight: 600;
  color: #1f2937;
}

.total-amount {
  color: #667eea;
  font-size: 1.25rem;
  font-weight: 700;
}

/* Buttons */
.btn-primary,
.btn-secondary,
.btn-select {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.875rem 1.75rem;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.btn-secondary {
  background: #f3f4f6;
  color: #374151;
}

.btn-secondary:hover {
  background: #e5e7eb;
  transform: translateY(-2px);
}

.btn-select {
  width: 100%;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
}

.btn-select:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
}

.btn-large {
  padding: 1.125rem 2rem;
  font-size: 1.125rem;
}

.btn-icon {
  width: 20px;
  height: 20px;
  stroke-width: 2;
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 2px solid #f3f4f6;
}

/* Payment Summary */
.payment-summary {
  background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
  padding: 2rem;
  border-radius: 16px;
  margin-bottom: 2rem;
}

.payment-summary h3 {
  color: #1f2937;
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
}

.payment-summary h4 {
  color: #374151;
  font-size: 1.125rem;
  font-weight: 600;
  margin: 1.5rem 0 1rem 0;
  padding-top: 1rem;
  border-top: 2px solid #e5e7eb;
}

.summary-section {
  margin-bottom: 1rem;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  padding: 0.75rem 0;
  color: #374151;
}

.item-label {
  color: #6b7280;
  font-weight: 500;
}

.item-value {
  color: #1f2937;
  font-weight: 600;
  text-align: right;
}

.summary-total-section {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 3px solid #e5e7eb;
}

.summary-total-item {
  display: flex;
  justify-content: space-between;
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
}

.summary-total-item .total-amount {
  color: #667eea;
}

.payment-actions {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

/* Loading */
.loading-container {
  text-align: center;
  padding: 3rem 0;
}

.loading-spinner {
  width: 60px;
  height: 60px;
  border: 6px solid #f3f4f6;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 2rem;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.loading-container h2 {
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.loading-container p {
  color: #6b7280;
}

/* Error & No Results */
.error {
  color: #ef4444;
  margin-top: 1rem;
  padding: 1rem 1.25rem;
  background: #fee2e2;
  border-radius: 12px;
  border-left: 4px solid #ef4444;
  font-weight: 500;
}

.no-results {
  text-align: center;
  color: #6b7280;
  padding: 3rem 2rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.no-results-icon {
  width: 60px;
  height: 60px;
  stroke-width: 2;
  color: #9ca3af;
}

/* Responsive */
@media (max-width: 768px) {
  .title {
    font-size: 1.875rem;
  }

  .step-content {
    padding: 1.5rem;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .rooms-grid {
    grid-template-columns: 1fr;
  }

  .services-grid {
    grid-template-columns: 1fr;
  }

  .form-actions,
  .payment-actions {
    flex-direction: column;
  }

  .btn-primary,
  .btn-secondary {
    width: 100%;
  }

  .step-label {
    display: none;
  }

  .steps-indicator {
    gap: 0.25rem;
  }

  .step-circle {
    width: 40px;
    height: 40px;
  }
}
</style>
