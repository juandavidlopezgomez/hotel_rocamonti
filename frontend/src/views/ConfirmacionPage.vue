<template>
  <div class="confirmacion-page">
    <div class="container">
      <div v-if="loading" class="loading">
        <div class="loading-spinner"></div>
        <p>Cargando información de tu reserva...</p>
      </div>

      <div v-else-if="error" class="error-container">
        <div class="error-icon">⚠️</div>
        <h2>Error al cargar la reserva</h2>
        <p>{{ error }}</p>
        <button @click="router.push('/')" class="btn-primary">Volver al inicio</button>
      </div>

      <div v-else-if="reserva" class="confirmation-content">
        <div class="success-icon">✓</div>
        <h1 class="title">¡Su reserva ha sido adquirida con éxito!</h1>
        <p class="subtitle">Pago exitoso - Proceso finalizado</p>

        <div class="reservation-card">
          <div class="card-header">
            <h2>Detalles de tu Reserva</h2>
            <div class="reservation-number">
              <span class="label">Número de Reserva:</span>
              <span class="value">#{{ reserva.idReserva }}</span>
            </div>
          </div>

          <div class="card-body">
            <div class="detail-row">
              <span class="label">Estado:</span>
              <span class="value" :class="'status-' + reserva.estado">
                {{ estadoTexto }}
              </span>
            </div>

            <div class="detail-row">
              <span class="label">Cliente:</span>
              <span class="value">{{ reserva.cliente.nombre || reserva.cliente.Nombre }} {{ reserva.cliente.apellido || reserva.cliente.Apellido }}</span>
            </div>

            <div class="detail-row">
              <span class="label">Cédula:</span>
              <span class="value">{{ reserva.cliente.cedula || reserva.cliente.Cedula }}</span>
            </div>

            <div class="detail-row">
              <span class="label">Email:</span>
              <span class="value">{{ reserva.cliente.email || reserva.cliente.Email }}</span>
            </div>

            <div class="detail-row">
              <span class="label">Celular:</span>
              <span class="value">{{ reserva.cliente.telefono || reserva.cliente.Celular }}</span>
            </div>

            <hr class="divider" />

            <div v-if="reserva.detalle_reservas && reserva.detalle_reservas.length > 0">
              <h3>Habitación</h3>
              <div
                v-for="detalle in reserva.detalle_reservas"
                :key="detalle.idDetalleReserva"
                class="room-details"
              >
                <div class="detail-row">
                  <span class="label">Habitación:</span>
                  <span class="value">{{ detalle.habitacion.numeroHabitacion }}</span>
                </div>
                <div class="detail-row">
                  <span class="label">Tipo:</span>
                  <span class="value">{{ detalle.habitacion.tipo_habitacion?.Nombre }}</span>
                </div>
                <div class="detail-row">
                  <span class="label">Piso:</span>
                  <span class="value">{{ detalle.habitacion.Piso }}</span>
                </div>
                <div class="detail-row">
                  <span class="label">Personas:</span>
                  <span class="value">{{ detalle.totalPersonas }}</span>
                </div>
              </div>
            </div>

            <hr class="divider" />

            <div class="detail-row">
              <span class="label">Fecha de entrada:</span>
              <span class="value">{{ formatDate(reserva.fechaInicio) }}</span>
            </div>

            <div class="detail-row">
              <span class="label">Fecha de salida:</span>
              <span class="value">{{ formatDate(reserva.fechaFin) }}</span>
            </div>

            <div class="detail-row">
              <span class="label">Número de noches:</span>
              <span class="value">{{ reserva.numero_noches }}</span>
            </div>

            <div class="detail-row">
              <span class="label">Check-in:</span>
              <span class="value">{{ reserva.hora_check_in }}</span>
            </div>

            <div class="detail-row">
              <span class="label">Check-out:</span>
              <span class="value">{{ reserva.hora_check_out }}</span>
            </div>

            <hr class="divider" />

            <div class="detail-row total">
              <span class="label">Total pagado:</span>
              <span class="value">{{ formatCurrency(reserva.precioTotal) }}</span>
            </div>

            <div v-if="reserva.pago" class="payment-info">
              <h3>Información del Pago</h3>
              <div class="detail-row">
                <span class="label">Estado del pago:</span>
                <span class="value" :class="'status-' + reserva.pago.estado">
                  {{ reserva.pago.estado }}
                </span>
              </div>
              <div v-if="reserva.pago.codigoTransaccion" class="detail-row">
                <span class="label">Código de transacción:</span>
                <span class="value">{{ reserva.pago.codigoTransaccion }}</span>
              </div>
            </div>
          </div>

          <div class="card-footer">
            <button @click="descargarConfirmacion" class="btn-download">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
              </svg>
              Descargar Confirmación (PDF)
            </button>
            <button @click="router.push('/')" class="btn-primary">Volver al Inicio</button>
          </div>
        </div>

        <div class="info-message">
          <p>
            Se ha enviado un correo de confirmación a <strong>{{ reserva.cliente.email || reserva.cliente.Email }}</strong>
            con todos los detalles de tu reserva.
          </p>
          <p>¡Esperamos verte pronto en Hotel Rocamonti!</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useBookingStore } from '../stores/bookingStore'
import { formatCurrency } from '../utils/currency'
import dayjs from 'dayjs'
import api from '../services/api'
import { generarPDFReserva } from '../utils/pdfGenerator'

const route = useRoute()
const router = useRouter()
const bookingStore = useBookingStore()

const loading = ref(true)
const error = ref(null)
const reserva = ref(null)

const estadoTexto = computed(() => {
  const estados = {
    pendiente: 'Pendiente',
    confirmada: 'Confirmada',
    pagada: 'Pagada',
    cancelada: 'Cancelada',
    completada: 'Completada'
  }
  return estados[reserva.value?.estado] || reserva.value?.estado
})

function formatDate(dateString) {
  return dayjs(dateString).format('DD/MM/YYYY')
}

async function descargarConfirmacion() {
  try {
    if (!reserva.value || !reserva.value.idReserva) {
      alert('No se puede descargar el comprobante. Datos de reserva incompletos.')
      return
    }

    // Obtener servicios adicionales si existen
    const servicios = reserva.value.serviciosAdicionales || []

    // Generar PDF con el nuevo generador profesional
    const pdf = await generarPDFReserva(reserva.value, servicios)

    // Descargar el PDF
    const reservaId = reserva.value.idReserva.toString().split('-')[0]
    pdf.save(`Confirmacion-Reserva-${reservaId}-HotelRocamonti.pdf`)

  } catch (error) {
    console.error('Error al descargar PDF:', error)
    alert('Error al generar el PDF. Por favor intenta nuevamente.')
  }
}

onMounted(async () => {
  try {
    loading.value = true
    error.value = null

    // Verificar si viene desde Wompi (con el parámetro id)
    const wompiId = route.query.id

    // Si viene de Wompi, procesar el pago
    if (wompiId) {
      console.log('✅ Regresando de Wompi con ID:', wompiId)

      // Cargar datos de la reserva desde localStorage
      const reservaData = localStorage.getItem('reservaData')

      if (!reservaData) {
        throw new Error('No se encontraron datos de la reserva. Por favor, intenta realizar la reserva nuevamente.')
      }

      const reservaTemp = JSON.parse(reservaData)
      console.log('📦 Datos de reserva cargados:', reservaTemp)

      // SIMPLIFICADO: Confirmar la reserva directamente sin verificar estado en Wompi
      console.log('💾 Confirmando reserva en el backend...')

      // Preparar datos EXACTAMENTE como los necesita la API
      const datosReserva = {
        transaction_id: wompiId,
        tipo_habitacion_id: parseInt(reservaTemp.tipo.id),
        fecha_entrada: reservaTemp.filtros.fechaEntrada,
        fecha_salida: reservaTemp.filtros.fechaSalida,
        num_adultos: parseInt(reservaTemp.filtros.adultos),
        num_ninos: parseInt(reservaTemp.filtros.ninos || 0),
        precio_total: parseFloat(reservaTemp.precioTotal.replace('COP', '').replace(/\./g, '').replace(/,/g, '').trim()),
        cliente: {
          cedula: String(reservaTemp.cliente.cedula),
          nombre: String(reservaTemp.cliente.nombre),
          apellido: String(reservaTemp.cliente.apellido),
          email: String(reservaTemp.cliente.email),
          telefono: String(reservaTemp.cliente.telefono || reservaTemp.cliente.celular || ''),
          celular: String(reservaTemp.cliente.telefono || reservaTemp.cliente.celular || '')
        }
      }

      console.log('📤 Datos a enviar:', datosReserva)

      const response = await api.post('/payments/confirmar-reserva', datosReserva)

      console.log('📡 Respuesta del servidor:', response.data)

      const result = response.data

      if (!result.success) {
        console.error('❌ Error en respuesta:', result)
        throw new Error(result.message || result.error || 'Error al confirmar la reserva')
      }

      console.log('🎉 ¡Reserva confirmada exitosamente!', result)

      // Crear objeto de reserva para mostrar
      reserva.value = {
        idReserva: result.data.reserva_id,
        estado: 'pagada',
        cliente: {
          nombre: reservaTemp.cliente.nombre,
          apellido: reservaTemp.cliente.apellido,
          cedula: reservaTemp.cliente.cedula,
          email: reservaTemp.cliente.email,
          telefono: reservaTemp.cliente.telefono || reservaTemp.cliente.celular || ''
        },
        fechaInicio: reservaTemp.filtros.fechaEntrada,
        fechaFin: reservaTemp.filtros.fechaSalida,
        numero_noches: reservaTemp.noches,
        hora_check_in: reservaTemp.cliente.horaLlegada,
        hora_check_out: reservaTemp.cliente.horaSalida,
        precioTotal: reservaTemp.precioTotal,
        detalle_reservas: [{
          idDetalleReserva: 1,
          totalPersonas: reservaTemp.filtros.adultos,
          habitacion: {
            numeroHabitacion: result.data.habitacion,
            Piso: 'Asignado',
            tipo_habitacion: {
              Nombre: reservaTemp.tipo.nombre
            }
          }
        }],
        pago: {
          estado: 'aprobado',
          codigoTransaccion: wompiId
        }
      }

      // Limpiar localStorage después de confirmar
      localStorage.removeItem('reservaData')

      loading.value = false
      return
    }

    // Si no viene de Wompi, intentar cargar por ID de ruta
    const reservaId = route.params.id
    if (!reservaId) {
      throw new Error('No se especificó ID de reserva')
    }

    const data = await bookingStore.getBookingDetails(reservaId)
    reserva.value = data
  } catch (err) {
    console.error('❌ Error en confirmación:', err)
    console.error('Error completo:', {
      message: err.message,
      response: err.response?.data,
      status: err.response?.status
    })

    // Mostrar error detallado
    let errorMsg = 'Error al cargar la reserva'

    if (err.response?.data) {
      // Error del servidor con detalles
      const serverError = err.response.data
      errorMsg = serverError.message || serverError.error || 'Error del servidor'

      // Si hay datos recibidos, mostrarlos en consola
      if (serverError.datos_recibidos) {
        console.error('🔍 Datos que se enviaron:', serverError.datos_recibidos)
      }
    } else if (err.message) {
      errorMsg = err.message
    }

    error.value = errorMsg
    alert('⚠️ ERROR:\n\n' + errorMsg + '\n\nRevisa la consola (F12) para más detalles.')
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.confirmacion-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  padding: 2rem 0;
}

.container {
  max-width: 900px;
  margin: 0 auto;
  padding: 0 1rem;
}

.loading {
  text-align: center;
  padding: 4rem 0;
}

.loading-spinner {
  width: 60px;
  height: 60px;
  border: 6px solid #e5e7eb;
  border-top-color: #1e40af;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.error-container {
  text-align: center;
  background: white;
  padding: 3rem;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.error-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.error-container h2 {
  color: #ef4444;
  margin-bottom: 1rem;
}

.confirmation-content {
  text-align: center;
}

.success-icon {
  width: 80px;
  height: 80px;
  background: #10b981;
  color: white;
  font-size: 3rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
  animation: scaleIn 0.5s ease-out;
}

@keyframes scaleIn {
  from {
    transform: scale(0);
  }
  to {
    transform: scale(1);
  }
}

.title {
  color: #10b981;
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
  font-weight: 700;
}

.subtitle {
  color: #059669;
  font-size: 1.2rem;
  margin-bottom: 2rem;
  font-weight: 600;
}

.reservation-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  text-align: left;
  margin-bottom: 2rem;
}

.card-header {
  background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
  color: white;
  padding: 1.5rem;
}

.card-header h2 {
  margin: 0 0 1rem 0;
  font-size: 1.5rem;
}

.reservation-number {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(255, 255, 255, 0.2);
  padding: 0.75rem;
  border-radius: 8px;
}

.reservation-number .label {
  font-weight: 600;
}

.reservation-number .value {
  font-size: 1.2rem;
  font-weight: bold;
}

.card-body {
  padding: 2rem;
}

.card-body h3 {
  color: #1e40af;
  margin: 1.5rem 0 1rem 0;
  font-size: 1.2rem;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 0.75rem 0;
  border-bottom: 1px solid #f3f4f6;
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-row .label {
  color: #6b7280;
  font-weight: 600;
}

.detail-row .value {
  color: #374151;
  font-weight: 500;
  text-align: right;
}

.detail-row.total {
  font-size: 1.3rem;
  padding-top: 1rem;
  border-top: 2px solid #e5e7eb;
  border-bottom: none;
}

.detail-row.total .label,
.detail-row.total .value {
  color: #1e40af;
  font-weight: bold;
}

.status-pendiente {
  color: #f59e0b;
  font-weight: bold;
}

.status-confirmada {
  color: #3b82f6;
  font-weight: bold;
}

.status-pagada {
  color: #10b981;
  font-weight: bold;
}

.status-aprobado {
  color: #10b981;
  font-weight: bold;
}

.status-cancelada,
.status-rechazado {
  color: #ef4444;
  font-weight: bold;
}

.divider {
  border: none;
  border-top: 2px solid #f3f4f6;
  margin: 1.5rem 0;
}

.room-details {
  background: #f9fafb;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.payment-info {
  background: #f0f9ff;
  padding: 1rem;
  border-radius: 8px;
  margin-top: 1rem;
}

.card-footer {
  padding: 1.5rem;
  background: #f9fafb;
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.btn-primary,
.btn-secondary,
.btn-download {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-primary {
  background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);
}

.btn-secondary {
  background: #e5e7eb;
  color: #374151;
}

.btn-secondary:hover {
  background: #d1d5db;
}

.btn-download {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-download:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
}

.btn-download svg {
  animation: bounce 2s infinite;
}

@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-4px);
  }
}

.info-message {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.info-message p {
  color: #374151;
  line-height: 1.6;
  margin-bottom: 0.75rem;
}

.info-message p:last-child {
  margin-bottom: 0;
  font-weight: 600;
  color: #1e40af;
}

@media (max-width: 768px) {
  .title {
    font-size: 2rem;
  }

  .card-footer {
    flex-direction: column;
  }

  .btn-primary,
  .btn-secondary {
    width: 100%;
  }

  .detail-row {
    flex-direction: column;
    gap: 0.25rem;
  }

  .detail-row .value {
    text-align: left;
  }
}
</style>
