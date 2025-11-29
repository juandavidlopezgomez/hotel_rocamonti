<template>
  <div class="reservar-page">
    <!-- Header profesional con logo -->
    <header class="header-principal">
      <div class="container-header">
        <div class="logo-section">
          <h1 class="logo-hotel">🏨 Hotel Rocamonti</h1>
          <p class="tagline">Tu refugio junto a la laguna</p>
        </div>
        <nav class="nav-links">
          <a href="/">Inicio</a>
          <a href="/reservar" class="active">Reservar</a>
          <a href="#contacto">Contacto</a>
        </nav>
      </div>
    </header>

    <!-- Filtro sticky profesional -->
    <div class="filtro-sticky">
      <div class="container">
        <div class="filtro-card">
          <h2>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="16" y1="2" x2="16" y2="6"></line>
              <line x1="8" y1="2" x2="8" y2="6"></line>
              <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            Busca tu estadía perfecta
          </h2>
          <div class="filtro-grid">
            <div class="filtro-item filtro-fechas">
              <label>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                  <line x1="16" y1="2" x2="16" y2="6"></line>
                  <line x1="8" y1="2" x2="8" y2="6"></line>
                  <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                Selecciona tus fechas
              </label>
              <RangeDatePicker
                v-model="fechasSeleccionadas"
                :tipo-habitacion-id="tipoSeleccionadoParaCalendario"
                @update:modelValue="actualizarFechas"
              />
            </div>
            <div class="filtro-item filtro-personas" @click="togglePersonasDropdown">
              <label>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                  <circle cx="9" cy="7" r="4"></circle>
                  <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                  <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                Huéspedes
              </label>
              <div class="personas-select">
                {{ filtros.adultos }} adulto{{ filtros.adultos > 1 ? 's' : '' }} · {{ filtros.ninos }} niño{{ filtros.ninos !== 1 ? 's' : '' }}
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
              </div>
              <div v-if="showPersonasDropdown" class="personas-dropdown" @click.stop>
                <div class="personas-row">
                  <div>
                    <strong>Adultos</strong>
                    <small>Mayores de 18 años</small>
                  </div>
                  <div class="counter">
                    <button type="button" @click="filtros.adultos = Math.max(1, filtros.adultos - 1)">−</button>
                    <span>{{ filtros.adultos }}</span>
                    <button type="button" @click="filtros.adultos = Math.min(10, filtros.adultos + 1)">+</button>
                  </div>
                </div>
                <div class="personas-row">
                  <div>
                    <strong>Niños</strong>
                    <small>Menores de 18 años</small>
                  </div>
                  <div class="counter">
                    <button type="button" @click="filtros.ninos = Math.max(0, filtros.ninos - 1)">−</button>
                    <span>{{ filtros.ninos }}</span>
                    <button type="button" @click="filtros.ninos = Math.min(8, filtros.ninos + 1)">+</button>
                  </div>
                </div>
              </div>
            </div>
            <button class="btn-buscar" @click="aplicarFiltros">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
              </svg>
              Buscar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Filtros de tipo -->
    <div class="container mt-4">
      <div class="filtros-tipo">
        <button
          :class="['filtro-btn', { active: !filtroApartamentos && !filtroHabitaciones }]"
          @click="resetFiltrosTipo"
        >
          Todas las opciones
        </button>
        <button
          :class="['filtro-btn', { active: filtroApartamentos }]"
          @click="filtroApartamentos = !filtroApartamentos; filtroHabitaciones = false"
        >
          🏠 Apartamentos
        </button>
        <button
          :class="['filtro-btn', { active: filtroHabitaciones }]"
          @click="filtroHabitaciones = !filtroHabitaciones; filtroApartamentos = false"
        >
          🛏️ Habitaciones
        </button>
      </div>

      <!-- Mensaje de resultados -->
      <div v-if="!loading && tipos.length > 0" class="resultados-info">
        <h3>
          {{ tiposFiltrados.length }} {{ tiposFiltrados.length === 1 ? 'alojamiento encontrado' : 'alojamientos encontrados' }}
        </h3>
        <p v-if="noches > 0">Para {{ noches }} noche{{ noches > 1 ? 's' : '' }}</p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="loading">
        <div class="spinner"></div>
        <p>Buscando las mejores opciones para ti...</p>
      </div>

      <!-- Lista de habitaciones con tarjetas modernas -->
      <div v-else class="habitaciones-grid">
        <div v-for="tipo in tiposFiltrados" :key="tipo.id" class="habitacion-card">
          <!-- Carrusel de imágenes profesional -->
          <div class="card-image-carousel">
            <RoomCarousel :habitacion-id="tipo.id" />
            <div class="tipo-badge-overlay">
              <span class="tipo-badge">{{ tipo.es_apartamento ? 'Apartamento' : 'Habitación' }}</span>
            </div>
          </div>

          <!-- Contenido de la tarjeta -->
          <div class="card-body">
            <h3 class="card-title">{{ tipo.nombre }}</h3>

            <div class="card-details">
              <div class="detail-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                  <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
                {{ tipo.descripcion_camas }}
              </div>
              <div class="detail-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                  <circle cx="9" cy="7" r="4"></circle>
                  <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                  <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                {{ tipo.capacidad_adultos }} adulto{{ tipo.capacidad_adultos > 1 ? 's' : '' }}
                <span v-if="tipo.capacidad_ninos > 0"> + {{ tipo.capacidad_ninos }} niño{{ tipo.capacidad_ninos > 1 ? 's' : '' }}</span>
              </div>
              <div class="detail-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                  <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                {{ tipo.metros_cuadrados }} m²
              </div>
            </div>

            <!-- Amenidades -->
            <div class="amenidades-list">
              <div v-for="(amenidad, index) in tipo.amenidades.slice(0, 4)" :key="index" class="amenidad-item">
                ✓ {{ amenidad }}
              </div>
            </div>

            <!-- Botón para ver disponibilidad -->
            <button
              class="btn-ver-disponibilidad"
              @click.stop="toggleCalendario(tipo.id)"
              type="button"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
              </svg>
              {{ calendarioExpandido[tipo.id] ? 'Ocultar' : 'Ver' }} disponibilidad del calendario
            </button>

            <!-- Calendario expandible -->
            <div v-if="calendarioExpandido[tipo.id]" class="calendario-container">
              <CalendarioReservas
                v-model="filtros"
                :tipo-habitacion-id="tipo.id"
                @update:modelValue="actualizarFechasDesdeCalendario"
              />
            </div>

            <!-- Beneficios -->
            <div class="beneficios">
              <span class="beneficio-badge success">✓ Desayuno incluido</span>
              <span class="beneficio-badge success">✓ Cancelación gratis</span>
            </div>

            <!-- Precio y selección -->
            <div class="card-footer">
              <div class="precio-section">
                <div class="precio-calculado">
                  <div class="precio-total">{{ calcularPrecio(tipo) }}</div>
                  <div v-if="mostrarPrecios" class="precio-desglose">
                    Para {{ noches }} noche{{ noches > 1 ? 's' : '' }}
                    <br>
                    <small>Incluye impuestos y cargos</small>
                  </div>
                  <div v-else class="precio-desglose">
                    Precio por noche
                  </div>
                </div>
              </div>

              <button
                class="btn-reservar"
                @click="reservar(tipo)"
              >
                Reservar ahora
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="5" y1="12" x2="19" y2="12"></line>
                  <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <div v-if="tiposFiltrados.length === 0" class="no-results">
          <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.35-4.35"></path>
          </svg>
          <h3>No se encontraron alojamientos</h3>
          <p>Intenta cambiar tus filtros o fechas de búsqueda</p>
        </div>
      </div>
    </div>

    <!-- Modal de datos del cliente -->
    <div v-if="showModal" class="modal-overlay" @click="cerrarModal">
      <div class="modal-content modern-modal" @click.stop>
        <button class="btn-cerrar" @click="cerrarModal">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>

        <div class="modal-header">
          <h2>
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
            Completa tu reserva
          </h2>
          <p>Estás a solo unos pasos de confirmar tu estadía</p>
        </div>

        <!-- Resumen de la reserva -->
        <div class="resumen-reserva-modal">
          <h3>📋 Resumen de tu reserva</h3>
          <div class="resumen-grid">
            <div class="resumen-item">
              <strong>Alojamiento:</strong>
              <span>{{ tipoSeleccionado.nombre }}</span>
            </div>
            <div class="resumen-item">
              <strong>Tipo de cama:</strong>
              <span>{{ tipoSeleccionado.descripcion_camas }}</span>
            </div>
            <div class="resumen-item">
              <strong>Cantidad:</strong>
              <span>{{ tipoSeleccionado.cantidad_seleccionada }} habitación(es)</span>
            </div>
          </div>
        </div>

        <!-- Formulario de datos personales -->
        <form @submit.prevent="irAResumen" class="form-reserva-modern">
          <div v-if="mostrarPrecios" class="info-noches-precio">
            <div class="fechas-info">
              <strong>📅 {{ formatearFecha(filtros.fechaEntrada) }} - {{ formatearFecha(filtros.fechaSalida) }}</strong>
              <div class="noches-detalle">{{ noches }} noche{{ noches > 1 ? 's' : '' }}</div>
            </div>
            <div class="precio-info">
              <strong>Total: {{ calcularPrecioTotal() }}</strong>
            </div>
          </div>

          <h3 class="mt-3">👤 Información personal</h3>

          <div class="form-row">
            <div class="form-group">
              <label>Cédula / Documento *</label>
              <input v-model="datosCliente.cedula" type="text" placeholder="Ej: 1234567890" required />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Nombre *</label>
              <input v-model="datosCliente.nombre" type="text" placeholder="Tu nombre" required />
            </div>
            <div class="form-group">
              <label>Apellido *</label>
              <input v-model="datosCliente.apellido" type="text" placeholder="Tu apellido" required />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Email *</label>
              <input v-model="datosCliente.email" type="email" placeholder="tu@email.com" required />
            </div>
            <div class="form-group">
              <label>Teléfono *</label>
              <input v-model="datosCliente.telefono" type="tel" placeholder="+57 300 123 4567" required />
            </div>
          </div>

          <h3 class="mt-3">🕐 Horarios de llegada y salida</h3>

          <div class="form-row">
            <div class="form-group">
              <label>Hora de llegada (Check-in) *</label>
              <select v-model="datosCliente.horaLlegada" required>
                <option value="">Selecciona una hora</option>
                <option value="08:00">08:00 AM</option>
                <option value="09:00">09:00 AM</option>
                <option value="10:00">10:00 AM</option>
                <option value="11:00">11:00 AM</option>
                <option value="12:00">12:00 PM</option>
                <option value="13:00">01:00 PM</option>
                <option value="14:00">02:00 PM (Check-in estándar)</option>
                <option value="15:00">03:00 PM</option>
                <option value="16:00">04:00 PM</option>
                <option value="17:00">05:00 PM</option>
                <option value="18:00">06:00 PM</option>
                <option value="19:00">07:00 PM</option>
                <option value="20:00">08:00 PM</option>
              </select>
            </div>
            <div class="form-group">
              <label>Hora de salida (Check-out) *</label>
              <select v-model="datosCliente.horaSalida" required>
                <option value="">Selecciona una hora</option>
                <option value="06:00">06:00 AM</option>
                <option value="07:00">07:00 AM</option>
                <option value="08:00">08:00 AM</option>
                <option value="09:00">09:00 AM</option>
                <option value="10:00">10:00 AM</option>
                <option value="11:00">11:00 AM</option>
                <option value="12:00">12:00 PM (Check-out estándar)</option>
                <option value="13:00">01:00 PM</option>
                <option value="14:00">02:00 PM</option>
              </select>
            </div>
          </div>

          <div class="form-group checkbox-modern">
            <label class="checkbox-label">
              <input v-model="datosCliente.aceptaTerminos" type="checkbox" required />
              <span class="checkbox-custom"></span>
              <span class="checkbox-text">
                Acepto los <a href="#" @click.prevent>términos y condiciones</a> y la
                <a href="#" @click.prevent>política de privacidad</a>
              </span>
            </label>
          </div>

          <button type="submit" class="btn-continuar">
            Ver resumen y proceder al pago
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="5" y1="12" x2="19" y2="12"></line>
              <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
          </button>
        </form>
      </div>
    </div>

    <!-- Modal de alerta bonito -->
    <div v-if="showAlertModal" class="modal-overlay" @click="cerrarAlerta">
      <div class="alert-modal" @click.stop>
        <div class="alert-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
          </svg>
        </div>
        <h3 class="alert-title">{{ alertTitle }}</h3>
        <p class="alert-message">{{ alertMessage }}</p>
        <button @click="cerrarAlerta" class="btn-alert-ok">Entendido</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import { formatCurrency } from '../utils/formatters'
import { useBookingStore } from '../stores/bookingStore'
import CalendarioReservas from '../components/CalendarioReservas.vue'
import RangeDatePicker from '../components/RangeDatePicker.vue'
import RoomCarousel from '../components/RoomCarousel.vue'

const router = useRouter()
const bookingStore = useBookingStore()
const loading = ref(false)
const tipos = ref([])
const showPersonasDropdown = ref(false)
const tipoSeleccionadoParaCalendario = ref(null)
const fechasSeleccionadas = ref({
  fechaEntrada: null,
  fechaSalida: null
})
const filtroApartamentos = ref(false)
const filtroHabitaciones = ref(false)
const showModal = ref(false)
const tipoSeleccionado = ref(null)
const showAlertModal = ref(false)
const alertMessage = ref('')
const alertTitle = ref('Atención')
const calendarioExpandido = ref({})

const datosCliente = ref({
  cedula: '',
  nombre: '',
  apellido: '',
  email: '',
  telefono: '',
  horaLlegada: '',
  horaSalida: '',
  aceptaTerminos: false
})

const filtros = ref({
  fechaEntrada: '',
  fechaSalida: '',
  adultos: 2,
  ninos: 0
})

// Fecha mínima (hoy)
const hoy = new Date().toISOString().split('T')[0]

// Fecha mínima de salida (un día después de la entrada)
const minFechaSalida = computed(() => {
  if (!filtros.value.fechaEntrada) return hoy
  const fecha = new Date(filtros.value.fechaEntrada)
  fecha.setDate(fecha.getDate() + 1)
  return fecha.toISOString().split('T')[0]
})

// Calcular número de noches
const noches = computed(() => {
  if (!filtros.value.fechaEntrada || !filtros.value.fechaSalida) return 0
  const entrada = new Date(filtros.value.fechaEntrada)
  const salida = new Date(filtros.value.fechaSalida)
  const diff = salida - entrada
  return Math.ceil(diff / (1000 * 60 * 60 * 24))
})

// Mostrar precios solo cuando hay fechas válidas
const mostrarPrecios = computed(() => {
  return filtros.value.fechaEntrada && filtros.value.fechaSalida && noches.value > 0
})


const tiposFiltrados = computed(() => {
  let resultado = tipos.value

  if (filtroApartamentos.value && !filtroHabitaciones.value) {
    resultado = resultado.filter((t) => t.es_apartamento)
  } else if (filtroHabitaciones.value && !filtroApartamentos.value) {
    resultado = resultado.filter((t) => !t.es_apartamento)
  }

  return resultado
})

function resetFiltrosTipo() {
  filtroApartamentos.value = false
  filtroHabitaciones.value = false
}

function togglePersonasDropdown() {
  showPersonasDropdown.value = !showPersonasDropdown.value
}

function toggleCalendario(tipoId) {
  calendarioExpandido.value[tipoId] = !calendarioExpandido.value[tipoId]
}

function actualizarFechasDesdeCalendario(fechas) {
  filtros.value.fechaEntrada = fechas.fechaEntrada
  filtros.value.fechaSalida = fechas.fechaSalida
  if (fechas.fechaEntrada && fechas.fechaSalida) {
    cargarHabitaciones()
  }
}

function actualizarFechas(fechas) {
  filtros.value.fechaEntrada = fechas.fechaEntrada
  filtros.value.fechaSalida = fechas.fechaSalida
  fechasSeleccionadas.value = fechas

  // Actualizar bookingStore
  bookingStore.selectedDates.fechaInicio = fechas.fechaEntrada
  bookingStore.selectedDates.fechaFin = fechas.fechaSalida

  console.log('📅 Fechas actualizadas:', {
    filtros: filtros.value,
    bookingStore: bookingStore.selectedDates
  })

  // Cargar habitaciones automáticamente cuando se seleccionan ambas fechas
  if (fechas.fechaEntrada && fechas.fechaSalida) {
    cargarHabitaciones()
  }
}

async function cargarHabitaciones() {
  loading.value = true
  console.log('🔄 INICIANDO CARGA DE HABITACIONES...')
  console.log('URL Base:', 'http://localhost:8000/api')

  try {
    const params = {}
    if (filtros.value.fechaEntrada) params.fecha_entrada = filtros.value.fechaEntrada
    if (filtros.value.fechaSalida) params.fecha_salida = filtros.value.fechaSalida
    if (filtros.value.adultos) params.num_adultos = filtros.value.adultos

    console.log('📤 Parámetros:', params)
    console.log('🌐 Haciendo petición a: /room-types')

    const response = await api.get('/room-types', { params })

    console.log('✅ RESPUESTA RECIBIDA:', response)
    console.log('📦 Data:', response.data)
    console.log('📊 Headers:', response.headers)

    if (response.data.success) {
      tipos.value = response.data.tipos.map((t) => ({
        ...t,
        cantidad_seleccionada: 0
      }))
      console.log('✅ HABITACIONES CARGADAS:', tipos.value.length)
      alert('✅ ÉXITO: ' + tipos.value.length + ' habitaciones cargadas')
    } else {
      console.error('⚠️ Response success = false')
      alert('⚠️ El servidor respondió pero success=false')
    }
  } catch (error) {
    console.error('❌ ERROR COMPLETO:', error)
    console.error('❌ Mensaje:', error.message)
    console.error('❌ Response:', error.response)
    console.error('❌ Request:', error.request)
    console.error('❌ Config:', error.config)

    let errorMsg = 'Error: ' + error.message
    if (error.response) {
      errorMsg += '\nStatus: ' + error.response.status
      errorMsg += '\nData: ' + JSON.stringify(error.response.data)
    } else if (error.request) {
      errorMsg += '\nNo se recibió respuesta del servidor'
      errorMsg += '\nVerifica que el servidor esté corriendo en puerto 8000'
    }

    alert('❌ ERROR AL CARGAR HABITACIONES:\n\n' + errorMsg)
    mostrarAlerta('Error al cargar habitaciones. Por favor, intenta nuevamente.', 'Error')
  } finally {
    loading.value = false
    console.log('🏁 CARGA FINALIZADA')
  }
}

function aplicarFiltros() {
  cargarHabitaciones()
}

function calcularPrecio(tipo) {
  if (!mostrarPrecios.value) return formatCurrency(tipo.precio_base)
  const total = tipo.precio_base * noches.value
  return formatCurrency(total)
}

function mostrarAlerta(mensaje, titulo = 'Atención') {
  alertTitle.value = titulo
  alertMessage.value = mensaje
  showAlertModal.value = true
}

function cerrarAlerta() {
  showAlertModal.value = false
  alertMessage.value = ''
}

async function reservar(tipo) {
  // Validar que haya fechas seleccionadas
  if (!filtros.value.fechaEntrada || !filtros.value.fechaSalida) {
    mostrarAlerta('Por favor selecciona las fechas de entrada y salida en el filtro de búsqueda')
    return
  }

  if (noches.value <= 0) {
    mostrarAlerta('Por favor selecciona fechas válidas')
    return
  }

  // Verificar disponibilidad antes de mostrar modal
  try {
    const response = await api.get(`/rooms/${tipo.id}/occupied-dates`)

    if (response.data.success) {
      const fechasOcupadas = response.data.fechas || []

      // Verificar si hay fechas ocupadas en el rango seleccionado
      const entrada = new Date(filtros.value.fechaEntrada)
      const salida = new Date(filtros.value.fechaSalida)

      for (let d = new Date(entrada); d < salida; d.setDate(d.getDate() + 1)) {
        const fechaStr = d.toISOString().split('T')[0]
        const estaOcupada = fechasOcupadas.find(f => f.fecha === fechaStr && f.completamente_ocupada)

        if (estaOcupada) {
          mostrarAlerta(
            `Lo sentimos, ${tipo.nombre} no está disponible para las fechas seleccionadas. Por favor elige otras fechas o revisa el calendario de disponibilidad.`,
            'Habitación No Disponible'
          )
          return
        }
      }
    }
  } catch (error) {
    console.error('Error verificando disponibilidad:', error)
  }

  // Por defecto reserva 1 habitación
  tipo.cantidad_seleccionada = 1
  tipoSeleccionado.value = tipo
  showModal.value = true
}

function cerrarModal() {
  showModal.value = false
}

function calcularPrecioTotal() {
  if (!tipoSeleccionado.value || noches.value === 0) {
    return formatCurrency(
      tipoSeleccionado.value.precio_base * tipoSeleccionado.value.cantidad_seleccionada
    )
  }

  const total =
    tipoSeleccionado.value.precio_base * noches.value * tipoSeleccionado.value.cantidad_seleccionada

  return formatCurrency(total)
}


function formatearFecha(fecha) {
  if (!fecha) return ''
  const d = new Date(fecha)
  const dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']
  const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic']
  return `${dias[d.getDay()]}, ${d.getDate()} ${meses[d.getMonth()]}`
}

function irAResumen() {
  // Validar que haya fechas seleccionadas
  if (!filtros.value.fechaEntrada || !filtros.value.fechaSalida) {
    mostrarAlerta('Por favor selecciona las fechas de entrada y salida')
    return
  }

  // Guardar datos en localStorage o store
  const reservaData = {
    tipo: tipoSeleccionado.value,
    filtros: {
      fechaEntrada: filtros.value.fechaEntrada,
      fechaSalida: filtros.value.fechaSalida,
      adultos: filtros.value.adultos,
      ninos: filtros.value.ninos
    },
    cliente: datosCliente.value,
    precioTotal: calcularPrecioTotal(),
    noches: noches.value
  }

  localStorage.setItem('reservaData', JSON.stringify(reservaData))

  // Redirigir a página de resumen
  router.push('/resumen')
}

// Watch para búsqueda automática cuando cambien las fechas
watch([() => filtros.value.fechaEntrada, () => filtros.value.fechaSalida], () => {
  if (filtros.value.fechaEntrada && filtros.value.fechaSalida && noches.value > 0) {
    cargarHabitaciones()
  }
})

onMounted(() => {
  cargarHabitaciones()
})
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.reservar-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

/* Header profesional */
.header-principal {
  background: linear-gradient(135deg, #0071c2 0%, #005a9c 100%);
  color: white;
  padding: 1.5rem 0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.container-header {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo-section {
  display: flex;
  flex-direction: column;
}

.logo-hotel {
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.tagline {
  font-size: 0.9rem;
  opacity: 0.9;
  margin-top: 0.25rem;
}

.nav-links {
  display: flex;
  gap: 2rem;
}

.nav-links a {
  color: white;
  text-decoration: none;
  font-weight: 500;
  transition: opacity 0.3s;
  padding: 0.5rem 1rem;
  border-radius: 4px;
}

.nav-links a:hover,
.nav-links a.active {
  background: rgba(255, 255, 255, 0.2);
}

/* Filtro sticky mejorado */
.filtro-sticky {
  background: white;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
  padding: 2rem 0;
  position: relative;
  z-index: 10;
  border-bottom: 3px solid #0071c2;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.filtro-card {
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.filtro-card h2 {
  color: #0071c2;
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1.5rem;
}

.filtro-grid {
  display: grid;
  grid-template-columns: 2fr 1.5fr auto;
  gap: 1.5rem;
  align-items: end;
}

.filtro-fechas {
  position: relative;
  z-index: 100;
}

.filtro-item {
  position: relative;
}

.filtro-item label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.5rem;
}

.filtro-item input[type='date'],
.filtro-item select {
  width: 100%;
  padding: 0.875rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.3s;
  background: white;
}

.filtro-item input[type='date']:focus,
.filtro-item select:focus {
  outline: none;
  border-color: #0071c2;
  box-shadow: 0 0 0 3px rgba(0, 113, 194, 0.1);
}

.filtro-personas {
  cursor: pointer;
}

.personas-select {
  padding: 0.875rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  cursor: pointer;
  background: white;
  transition: all 0.3s;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.personas-select:hover {
  border-color: #0071c2;
}

.personas-dropdown {
  position: absolute;
  top: calc(100% + 0.5rem);
  left: 0;
  right: 0;
  background: white;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  z-index: 200;
}

.personas-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.personas-row:last-child {
  margin-bottom: 0;
}

.personas-row > div:first-child {
  display: flex;
  flex-direction: column;
}

.personas-row strong {
  color: #333;
  font-size: 1rem;
}

.personas-row small {
  color: #666;
  font-size: 0.875rem;
}

.counter {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.counter button {
  width: 40px;
  height: 40px;
  border: 2px solid #0071c2;
  background: white;
  color: #0071c2;
  border-radius: 50%;
  cursor: pointer;
  font-size: 1.25rem;
  font-weight: 600;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.counter button:hover {
  background: #0071c2;
  color: white;
  transform: scale(1.1);
}

.counter span {
  font-weight: 600;
  min-width: 30px;
  text-align: center;
}

.btn-buscar {
  padding: 0.875rem 2.5rem;
  background: linear-gradient(135deg, #0071c2 0%, #005a9c 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  box-shadow: 0 4px 12px rgba(0, 113, 194, 0.3);
}

.btn-buscar:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 113, 194, 0.4);
}

/* Filtros de tipo */
.mt-4 {
  margin-top: 2rem;
}

.mt-3 {
  margin-top: 1.5rem;
}

.filtros-tipo {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
}

.filtro-btn {
  padding: 0.75rem 1.5rem;
  background: white;
  border: 2px solid #e0e0e0;
  border-radius: 50px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s;
  font-size: 1rem;
}

.filtro-btn:hover {
  border-color: #0071c2;
  background: #f0f8ff;
}

.filtro-btn.active {
  background: #0071c2;
  color: white;
  border-color: #0071c2;
}

/* Resultados info */
.resultados-info {
  margin-bottom: 2rem;
}

.resultados-info h3 {
  color: #333;
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
}

.resultados-info p {
  color: #666;
  font-size: 1rem;
}

/* Loading */
.loading {
  text-align: center;
  padding: 4rem;
  color: #666;
}

.spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #0071c2;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Grid de habitaciones - Tarjetas modernas */
.habitaciones-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.habitacion-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: all 0.3s;
  display: flex;
  flex-direction: column;
}

.habitacion-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
}

.card-image-carousel {
  height: 240px;
  position: relative;
  overflow: hidden;
}

.tipo-badge-overlay {
  position: absolute;
  top: 1rem;
  right: 1rem;
  z-index: 10;
  pointer-events: none;
}

.tipo-badge {
  background: rgba(255, 255, 255, 0.95);
  color: var(--primary);
  padding: 0.5rem 1rem;
  border-radius: var(--radius-full);
  font-weight: 600;
  font-size: 0.8rem;
  box-shadow: var(--shadow-md);
  backdrop-filter: blur(10px);
}

.card-body {
  padding: 1rem;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.card-title {
  color: #0071c2;
  font-size: 1.1rem;
  margin-bottom: 0.75rem;
  font-weight: 700;
  line-height: 1.3;
}

.card-details {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #666;
  font-size: 0.85rem;
}

.detail-item svg {
  color: #0071c2;
  flex-shrink: 0;
}

.amenidades-list {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.4rem;
  margin-bottom: 0.75rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid #e0e0e0;
}

.amenidad-item {
  color: #008009;
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

/* Botón para ver disponibilidad */
.btn-ver-disponibilidad {
  width: 100%;
  padding: 0.75rem;
  margin: 0.75rem 0;
  background: linear-gradient(135deg, #f0f7ff 0%, #e3f2fd 100%);
  border: 1px solid #90caf9;
  border-radius: 8px;
  color: #0066cc;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn-ver-disponibilidad:hover {
  background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
  border-color: #64b5f6;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 102, 204, 0.2);
}

.btn-ver-disponibilidad svg {
  flex-shrink: 0;
}

/* Contenedor del calendario */
.calendario-container {
  margin: 1rem 0;
  padding: 1rem;
  background: #fafafa;
  border-radius: 8px;
  animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    max-height: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    max-height: 1000px;
    transform: translateY(0);
  }
}

.beneficios {
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
  margin-bottom: 0.75rem;
}

.beneficio-badge {
  padding: 0.3rem 0.6rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 500;
}

.beneficio-badge.success {
  background: #e8f5e9;
  color: #2e7d32;
}

.card-footer {
  margin-top: auto;
  padding-top: 0.75rem;
  border-top: 1px solid #f0f0f0;
}

.precio-section {
  margin-bottom: 0.75rem;
}

.precio-calculado {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.precio-total {
  font-size: 1.75rem;
  font-weight: 700;
  color: #0071c2;
}

.precio-desglose {
  text-align: right;
  font-size: 0.8rem;
  color: #666;
}

.btn-reservar {
  width: 100%;
  padding: 0.875rem 1.25rem;
  background: linear-gradient(135deg, #0071c2 0%, #005a9c 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  box-shadow: 0 2px 8px rgba(0, 113, 194, 0.3);
}

.btn-reservar:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 113, 194, 0.4);
}

/* No results */
.no-results {
  text-align: center;
  padding: 4rem;
  color: #999;
  grid-column: 1 / -1;
}

.no-results svg {
  margin-bottom: 1rem;
  opacity: 0.5;
}

.no-results h3 {
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
}

/* Modal moderno */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  padding: 1rem;
  overflow-y: auto;
}

.modern-modal {
  background: white;
  border-radius: 16px;
  max-width: 700px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-content {
  padding: 2.5rem;
}

.btn-cerrar {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: #f5f5f5;
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
  z-index: 10;
}

.btn-cerrar:hover {
  background: #e0e0e0;
  transform: rotate(90deg);
}

.modal-header {
  margin-bottom: 2rem;
}

.modal-header h2 {
  color: #0071c2;
  font-size: 2rem;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.modal-header p {
  color: #666;
  font-size: 1rem;
}

.resumen-reserva-modal {
  background: linear-gradient(135deg, #f0f8ff 0%, #e3f2fd 100%);
  padding: 1.5rem;
  border-radius: 12px;
  margin-bottom: 2rem;
  border: 2px solid #0071c2;
}

.resumen-reserva-modal h3 {
  color: #0071c2;
  font-size: 1.25rem;
  margin-bottom: 1rem;
}

.resumen-grid {
  display: grid;
  gap: 1rem;
}

.resumen-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid rgba(0, 113, 194, 0.2);
}

.resumen-item.total {
  border-bottom: none;
  padding-top: 0.75rem;
  border-top: 2px solid rgba(0, 113, 194, 0.3);
  margin-top: 0.5rem;
}

.resumen-item strong {
  color: #333;
  font-weight: 600;
}

.resumen-item span {
  color: #666;
  text-align: right;
}

.precio-destacado {
  font-size: 1.75rem;
  font-weight: 700;
  color: #0071c2 !important;
}

.form-reserva-modern {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-reserva-modern h3 {
  color: #333;
  font-size: 1.25rem;
  margin-bottom: -0.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.info-noches-precio {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: linear-gradient(135deg, #e3f2fd 0%, #f0f8ff 100%);
  border-radius: 8px;
  border: 2px solid #0071c2;
  margin-top: 1rem;
  margin-bottom: 1rem;
}

.noches-info {
  color: #0071c2;
  font-size: 1rem;
}

.fechas-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  color: #0071c2;
}

.noches-detalle {
  font-size: 0.875rem;
  font-weight: normal;
}

.precio-info {
  color: #0071c2;
  font-size: 1.25rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-row:has(.form-group:only-child) {
  grid-template-columns: 1fr;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #333;
  font-size: 0.95rem;
}

.form-group input,
.form-group select {
  padding: 0.875rem;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.3s;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #0071c2;
  box-shadow: 0 0 0 3px rgba(0, 113, 194, 0.1);
}

.checkbox-modern {
  margin-top: 1rem;
}

.checkbox-label {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  cursor: pointer;
  position: relative;
}

.checkbox-label input[type='checkbox'] {
  position: absolute;
  opacity: 0;
}

.checkbox-custom {
  width: 24px;
  height: 24px;
  border: 2px solid #e0e0e0;
  border-radius: 6px;
  flex-shrink: 0;
  transition: all 0.3s;
  position: relative;
}

.checkbox-label input[type='checkbox']:checked + .checkbox-custom {
  background: #0071c2;
  border-color: #0071c2;
}

.checkbox-label input[type='checkbox']:checked + .checkbox-custom::after {
  content: '✓';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: white;
  font-weight: 700;
}

.checkbox-text {
  color: #666;
  font-size: 0.95rem;
  line-height: 1.5;
}

.checkbox-text a {
  color: #0071c2;
  text-decoration: none;
  font-weight: 600;
}

.checkbox-text a:hover {
  text-decoration: underline;
}

.btn-continuar {
  padding: 1.25rem 2rem;
  background: linear-gradient(135deg, #0071c2 0%, #005a9c 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1.125rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  transition: all 0.3s;
  box-shadow: 0 4px 16px rgba(0, 113, 194, 0.3);
  margin-top: 1rem;
}

.btn-continuar:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 113, 194, 0.4);
}

/* Responsive */
@media (max-width: 968px) {
  .filtro-grid {
    grid-template-columns: 1fr;
  }

  .container-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }

  .nav-links {
    gap: 1rem;
  }

  .habitaciones-grid {
    grid-template-columns: 1fr;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .filtros-tipo {
    flex-wrap: wrap;
  }
}

@media (max-width: 640px) {
  .modal-content {
    padding: 1.5rem;
  }

  .logo-hotel {
    font-size: 1.5rem;
  }

  .precio-total {
    font-size: 1.5rem;
  }
}

/* Modal de alerta bonito */
.alert-modal {
  background: white;
  border-radius: 16px;
  padding: 2.5rem;
  max-width: 450px;
  width: 90%;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  text-align: center;
  animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-50px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.alert-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 1.5rem;
  background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: pulse 2s infinite;
}

.alert-icon svg {
  color: white;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
    box-shadow: 0 0 0 0 rgba(255, 152, 0, 0.7);
  }
  50% {
    transform: scale(1.05);
    box-shadow: 0 0 0 10px rgba(255, 152, 0, 0);
  }
}

.alert-title {
  color: #333;
  font-size: 1.75rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

.alert-message {
  color: #666;
  font-size: 1.125rem;
  line-height: 1.6;
  margin-bottom: 2rem;
}

.btn-alert-ok {
  padding: 1rem 3rem;
  background: linear-gradient(135deg, #0071c2 0%, #005a9c 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1.125rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 4px 12px rgba(0, 113, 194, 0.3);
  min-width: 150px;
}

.btn-alert-ok:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 113, 194, 0.4);
}

.btn-alert-ok:active {
  transform: translateY(0);
}
</style>
