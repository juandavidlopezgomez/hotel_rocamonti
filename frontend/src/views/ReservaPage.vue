<template>
  <div class="reserva-page">
    <div class="container">
      <h1 class="title">Reserva tu estadía</h1>

      <!-- Indicador de pasos -->
      <div class="steps-indicator">
        <div
          v-for="n in 6"
          :key="n"
          class="step"
          :class="{ active: currentStep === n, completed: currentStep > n }"
        >
          {{ n }}
        </div>
      </div>

      <!-- PASO 1: Selección de fechas -->
      <div v-if="currentStep === 1" class="step-content">
        <h2>Paso 1: Selecciona tus fechas</h2>
        <div class="form-group">
          <label>Fecha de entrada</label>
          <input
            v-model="searchForm.fechaInicio"
            type="date"
            :min="minDate"
            class="form-control"
          />
        </div>
        <div class="form-group">
          <label>Fecha de salida</label>
          <input
            v-model="searchForm.fechaFin"
            type="date"
            :min="minDateFin"
            class="form-control"
          />
        </div>
        <div class="form-group">
          <label>Número de personas</label>
          <input v-model.number="searchForm.personas" type="number" min="1" class="form-control" />
        </div>
        <button @click="buscarHabitaciones" :disabled="!canSearch || loading" class="btn-primary">
          {{ loading ? 'Buscando...' : 'Buscar Habitaciones' }}
        </button>
        <p v-if="error" class="error">{{ error }}</p>
      </div>

      <!-- PASO 2: Mostrar habitaciones disponibles -->
      <div v-if="currentStep === 2" class="step-content">
        <h2>Paso 2: Elige tu tipo de habitación</h2>
        <p v-if="availableRooms.length === 0" class="no-results">
          No hay habitaciones disponibles para las fechas seleccionadas.
        </p>
        <div class="rooms-grid">
          <div v-for="tipo in availableRooms" :key="tipo.tipo.idTipoHabitacion" class="room-card">
            <img
              :src="tipo.habitaciones_disponibles[0]?.imagenes[0] || 'https://via.placeholder.com/400x300'"
              :alt="tipo.tipo.Nombre"
              class="room-image"
            />
            <div class="room-info">
              <h3>{{ tipo.tipo.Nombre }}</h3>
              <p class="room-description">{{ tipo.tipo.Descripcion }}</p>
              <p class="room-capacity">Capacidad: {{ tipo.tipo.capacidad }} personas</p>
              <p class="room-price">{{ formatCurrency(tipo.tipo.PrecioBase) }} / noche</p>
              <p class="available-count">{{ tipo.cantidad_disponibles }} habitaciones disponibles</p>
              <button @click="seleccionarTipo(tipo)" class="btn-select">Seleccionar</button>
            </div>
          </div>
        </div>
        <button @click="currentStep = 1" class="btn-secondary">Volver</button>
      </div>

      <!-- PASO 3: Seleccionar habitación específica -->
      <div v-if="currentStep === 3" class="step-content">
        <h2>Paso 3: Elige una habitación específica</h2>
        <div class="rooms-grid">
          <div
            v-for="habitacion in tipoSeleccionado.habitaciones_disponibles"
            :key="habitacion.idHabitacion"
            class="room-card"
          >
            <img
              :src="habitacion.imagenes[0] || 'https://via.placeholder.com/400x300'"
              :alt="habitacion.numeroHabitacion"
              class="room-image"
            />
            <div class="room-info">
              <h3>Habitación {{ habitacion.numeroHabitacion }}</h3>
              <p>Piso: {{ habitacion.Piso }}</p>
              <p>Estado: {{ habitacion.estado }}</p>
              <p class="room-price">{{ formatCurrency(habitacion.precio) }} / noche</p>
              <p class="total-price">
                Total ({{ numberOfNights }} noches): {{ formatCurrency(habitacion.precio * numberOfNights) }}
              </p>
              <button @click="seleccionarHabitacion(habitacion)" class="btn-select">
                Continuar con esta habitación
              </button>
            </div>
          </div>
        </div>
        <button @click="currentStep = 2" class="btn-secondary">Volver</button>
      </div>

      <!-- PASO 4: Formulario de cliente -->
      <div v-if="currentStep === 4" class="step-content">
        <h2>Paso 4: Completa tus datos</h2>
        <form @submit.prevent="crearReserva" class="guest-form">
          <div class="form-group">
            <label>Cédula *</label>
            <input v-model="guestForm.cedula" type="text" required class="form-control" />
          </div>
          <div class="form-group">
            <label>Nombre *</label>
            <input v-model="guestForm.nombre" type="text" required class="form-control" />
          </div>
          <div class="form-group">
            <label>Apellido *</label>
            <input v-model="guestForm.apellido" type="text" required class="form-control" />
          </div>
          <div class="form-group">
            <label>Email *</label>
            <input v-model="guestForm.email" type="email" required class="form-control" />
          </div>
          <div class="form-group">
            <label>Celular *</label>
            <input v-model="guestForm.celular" type="tel" required class="form-control" />
          </div>
          <div class="form-group">
            <label>Teléfono</label>
            <input v-model="guestForm.telefono" type="tel" class="form-control" />
          </div>
          <div class="form-group">
            <label class="checkbox-label">
              <input v-model="guestForm.aceptaTerminos" type="checkbox" required />
              Acepto términos y condiciones
            </label>
          </div>
          <button type="submit" :disabled="!guestForm.aceptaTerminos || loading" class="btn-primary">
            {{ loading ? 'Procesando...' : 'Proceder al Pago' }}
          </button>
          <button type="button" @click="currentStep = 3" class="btn-secondary">Volver</button>
        </form>
        <p v-if="error" class="error">{{ error }}</p>
      </div>

      <!-- PASO 5: Pago con Wompi -->
      <div v-if="currentStep === 5" class="step-content">
        <h2>Paso 5: Realiza el pago</h2>
        <div class="payment-summary">
          <h3>Resumen de tu reserva</h3>
          <p><strong>Habitación:</strong> {{ selectedRoom?.numeroHabitacion }}</p>
          <p><strong>Tipo:</strong> {{ tipoSeleccionado?.tipo.Nombre }}</p>
          <p>
            <strong>Fechas:</strong> {{ formatDate(searchForm.fechaInicio) }} -
            {{ formatDate(searchForm.fechaFin) }}
          </p>
          <p><strong>Noches:</strong> {{ numberOfNights }}</p>
          <p class="total-amount">
            <strong>Total a pagar:</strong> {{ formatCurrency(totalPrice) }}
          </p>
        </div>
        <div class="payment-actions">
          <button @click="iniciarPago" class="btn-primary btn-large">Pagar con Wompi</button>
          <button @click="currentStep = 4" class="btn-secondary">Volver</button>
        </div>
      </div>

      <!-- PASO 6: Procesando pago -->
      <div v-if="currentStep === 6" class="step-content">
        <h2>Procesando pago...</h2>
        <div class="loading-spinner"></div>
        <p>Por favor espera mientras confirmamos tu pago.</p>
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
  aceptaTerminos: false
})

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

async function crearReserva() {
  try {
    loading.value = true
    error.value = null
    await bookingStore.createBooking(guestForm.value, searchForm.value.personas)
    currentStep.value = 5
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al crear la reserva'
  } finally {
    loading.value = false
  }
}

function iniciarPago() {
  const publicKey = 'pub_prod_GMN9W6Y3sXbp7uE5RXo4cDmPUzkR89y5'
  const amountInCents = totalPrice.value * 100

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
      currentStep.value = 6
    }
  })
}

function formatDate(dateString) {
  return dayjs(dateString).format('DD/MM/YYYY')
}

onMounted(() => {
  // Cargar script de Wompi
  const script = document.createElement('script')
  script.src = 'https://checkout.wompi.co/widget.js'
  script.async = true
  document.head.appendChild(script)
})
</script>

<style scoped>
.reserva-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  padding: 2rem 0;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.title {
  text-align: center;
  color: #1e40af;
  font-size: 2.5rem;
  margin-bottom: 2rem;
}

.steps-indicator {
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin-bottom: 2rem;
}

.step {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  color: #6b7280;
  transition: all 0.3s;
}

.step.active {
  background: #1e40af;
  color: white;
  transform: scale(1.2);
}

.step.completed {
  background: #10b981;
  color: white;
}

.step-content {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.step-content h2 {
  color: #1e40af;
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #374151;
}

.form-control {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s;
}

.form-control:focus {
  outline: none;
  border-color: #1e40af;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.checkbox-label input[type='checkbox'] {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.rooms-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.room-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s, box-shadow 0.3s;
}

.room-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
}

.room-image {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.room-info {
  padding: 1.5rem;
}

.room-info h3 {
  color: #1e40af;
  margin-bottom: 0.75rem;
}

.room-description {
  color: #6b7280;
  font-size: 0.9rem;
  margin-bottom: 0.5rem;
}

.room-capacity {
  color: #374151;
  font-size: 0.9rem;
  margin-bottom: 0.5rem;
}

.room-price {
  color: #10b981;
  font-size: 1.2rem;
  font-weight: bold;
  margin: 0.5rem 0;
}

.total-price {
  color: #1e40af;
  font-size: 1.1rem;
  font-weight: bold;
  margin: 0.5rem 0;
}

.available-count {
  color: #6b7280;
  font-size: 0.85rem;
  margin-bottom: 1rem;
}

.btn-primary,
.btn-secondary,
.btn-select {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  margin-right: 1rem;
  margin-top: 1rem;
}

.btn-primary {
  background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: #e5e7eb;
  color: #374151;
}

.btn-secondary:hover {
  background: #d1d5db;
}

.btn-select {
  width: 100%;
  background: #10b981;
  color: white;
  margin: 0;
}

.btn-select:hover {
  background: #059669;
}

.btn-large {
  padding: 1rem 2rem;
  font-size: 1.2rem;
}

.payment-summary {
  background: #f9fafb;
  padding: 1.5rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
}

.payment-summary h3 {
  color: #1e40af;
  margin-bottom: 1rem;
}

.payment-summary p {
  margin: 0.5rem 0;
  color: #374151;
}

.total-amount {
  font-size: 1.5rem;
  color: #1e40af;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 2px solid #e5e7eb;
}

.payment-actions {
  display: flex;
  gap: 1rem;
}

.loading-spinner {
  width: 50px;
  height: 50px;
  border: 5px solid #e5e7eb;
  border-top-color: #1e40af;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 2rem auto;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.error {
  color: #ef4444;
  margin-top: 1rem;
  padding: 0.75rem;
  background: #fee2e2;
  border-radius: 8px;
}

.no-results {
  text-align: center;
  color: #6b7280;
  padding: 2rem;
}

@media (max-width: 768px) {
  .title {
    font-size: 2rem;
  }

  .rooms-grid {
    grid-template-columns: 1fr;
  }

  .payment-actions {
    flex-direction: column;
  }

  .btn-primary,
  .btn-secondary {
    width: 100%;
    margin-right: 0;
  }
}
</style>
