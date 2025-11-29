<template>
  <div class="resumen-page">
    <!-- Header -->
    <header class="header-principal">
      <div class="container-header">
        <div class="logo-section">
          <h1 class="logo-hotel">🏨 Hotel Rocamonti</h1>
          <p class="tagline">Tu refugio junto a la laguna</p>
        </div>
        <nav class="nav-links">
          <a href="/">Inicio</a>
          <a href="/reservar">Reservar</a>
          <a href="#contacto">Contacto</a>
        </nav>
      </div>
    </header>

    <!-- Contenido principal -->
    <div class="container main-content">
      <!-- Progress bar -->
      <div class="progress-steps">
        <div class="step completed">
          <div class="step-circle">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
          </div>
          <span>Selección</span>
        </div>
        <div class="step completed">
          <div class="step-circle">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
          </div>
          <span>Datos</span>
        </div>
        <div class="step active">
          <div class="step-circle">3</div>
          <span>Resumen</span>
        </div>
        <div class="step">
          <div class="step-circle">4</div>
          <span>Pago</span>
        </div>
      </div>

      <!-- Mensaje de carga -->
      <div v-if="!reserva" class="loading-container">
        <div class="spinner"></div>
        <p>Cargando información de tu reserva...</p>
      </div>

      <div v-else class="contenido-grid">
        <!-- Columna izquierda - Detalles de la reserva -->
        <div class="columna-detalles">
          <div class="card">
            <div class="card-header">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
              </svg>
              <h2>Detalles de tu alojamiento</h2>
            </div>
            <div class="card-body">
              <div class="alojamiento-destacado">
                <h3>{{ reserva.tipo.nombre }}</h3>
                <span class="badge-tipo">{{ reserva.tipo.es_apartamento ? 'Apartamento' : 'Habitación' }}</span>
              </div>

              <div class="detalle-lista">
                <div class="detalle-item">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                  </svg>
                  <div>
                    <strong>Tipo de camas</strong>
                    <span>{{ reserva.tipo.descripcion_camas }}</span>
                  </div>
                </div>

                <div class="detalle-item">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                  </svg>
                  <div>
                    <strong>Capacidad</strong>
                    <span>{{ reserva.tipo.capacidad_adultos }} adulto{{ reserva.tipo.capacidad_adultos > 1 ? 's' : '' }}
                      <span v-if="reserva.tipo.capacidad_ninos > 0"> + {{ reserva.tipo.capacidad_ninos }} niño{{ reserva.tipo.capacidad_ninos > 1 ? 's' : '' }}</span>
                    </span>
                  </div>
                </div>

                <div class="detalle-item">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                  </svg>
                  <div>
                    <strong>Tamaño</strong>
                    <span>{{ reserva.tipo.metros_cuadrados }} m²</span>
                  </div>
                </div>

                <div class="detalle-item">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                  </svg>
                  <div>
                    <strong>Cantidad de habitaciones</strong>
                    <span>{{ reserva.tipo.cantidad_seleccionada }} habitación{{ reserva.tipo.cantidad_seleccionada > 1 ? 'es' : '' }}</span>
                  </div>
                </div>
              </div>

              <div class="amenidades-completas">
                <h4>
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                  </svg>
                  Amenidades incluidas
                </h4>
                <div class="amenidades-grid">
                  <div v-for="(amenidad, index) in reserva.tipo.amenidades" :key="index" class="amenidad">
                    ✓ {{ amenidad }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
              </svg>
              <h2>Fechas de tu estadía</h2>
            </div>
            <div class="card-body">
              <div class="fechas-grid">
                <div class="fecha-item">
                  <div class="fecha-label">Check-in</div>
                  <div class="fecha-valor">{{ formatearFechaCompleta(reserva.filtros.fechaEntrada) }}</div>
                  <div class="hora-valor">A partir de las {{ reserva.cliente.horaLlegada }}</div>
                </div>
                <div class="divider-vertical">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                  </svg>
                </div>
                <div class="fecha-item">
                  <div class="fecha-label">Check-out</div>
                  <div class="fecha-valor">{{ formatearFechaCompleta(reserva.filtros.fechaSalida) }}</div>
                  <div class="hora-valor">Hasta las {{ reserva.cliente.horaSalida }}</div>
                </div>
              </div>
              <div class="duracion-estadia">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"></circle>
                  <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <strong>Duración total:</strong> {{ reserva.noches }} noche{{ reserva.noches > 1 ? 's' : '' }}
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
              <h2>Información del huésped</h2>
            </div>
            <div class="card-body">
              <div class="info-grid">
                <div class="info-item">
                  <span class="info-label">Nombre completo</span>
                  <span class="info-valor">{{ reserva.cliente.nombre }} {{ reserva.cliente.apellido }}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Documento</span>
                  <span class="info-valor">{{ reserva.cliente.cedula }}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Email</span>
                  <span class="info-valor">{{ reserva.cliente.email }}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Teléfono</span>
                  <span class="info-valor">{{ reserva.cliente.telefono }}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Huéspedes</span>
                  <span class="info-valor">{{ reserva.filtros.adultos }} adulto{{ reserva.filtros.adultos > 1 ? 's' : '' }}{{ reserva.filtros.ninos > 0 ? ', ' + reserva.filtros.ninos + ' niño' + (reserva.filtros.ninos > 1 ? 's' : '') : '' }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Columna derecha - Resumen de precio y pago -->
        <div class="columna-precio">
          <div class="card sticky-card">
            <div class="card-header">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="1" x2="12" y2="23"></line>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
              </svg>
              <h2>Resumen de precios</h2>
            </div>
            <div class="card-body">
              <div class="precio-desglose">
                <div class="precio-item-titulo">
                  <span class="nombre-habitacion">{{ reserva.tipo.nombre }}</span>
                </div>
                <div class="precio-item">
                  <span class="descripcion">{{ formatCurrency(reserva.tipo.precio_base) }} × {{ reserva.noches }} noche{{ reserva.noches > 1 ? 's' : '' }} × {{ reserva.tipo.cantidad_seleccionada }} habitación{{ reserva.tipo.cantidad_seleccionada > 1 ? 'es' : '' }}</span>
                  <span class="valor">{{ formatCurrency(calcularSubtotal()) }}</span>
                </div>
                <div class="precio-item">
                  <span class="descripcion">Impuestos y cargos</span>
                  <span class="valor-incluido">Incluidos</span>
                </div>
              </div>

              <div class="precio-total-box">
                <div class="precio-total-label">Precio total</div>
                <div class="precio-total-valor">{{ reserva.precioTotal }}</div>
              </div>

              <div class="beneficios-lista">
                <div class="beneficio">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                  </svg>
                  <span>Desayuno incluido</span>
                </div>
                <div class="beneficio">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                  </svg>
                  <span>Cancelación gratis hasta 24h antes</span>
                </div>
                <div class="beneficio">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                  </svg>
                  <span>Sin prepago - Paga en el hotel</span>
                </div>
              </div>

              <div class="test-mode-banner">
                🧪 <strong>Modo Prueba</strong> - Usa tarjeta: 4242 4242 4242 4242
              </div>

              <button @click="pagoSimulado" class="btn-pagar-simulado" :disabled="cargandoPago">
                <svg v-if="!cargandoPago" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"></circle>
                  <line x1="12" y1="8" x2="12" y2="12"></line>
                  <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <div v-if="cargandoPago" class="spinner-btn"></div>
                {{ cargandoPago ? 'Procesando...' : '💰 Simular Pago Exitoso (Desarrollo)' }}
              </button>

              <button @click="abrirPasarelaPago" class="btn-pagar" :disabled="cargandoPago">
                <svg v-if="!cargandoPago" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                  <line x1="1" y1="10" x2="23" y2="10"></line>
                </svg>
                <div v-if="cargandoPago" class="spinner-btn"></div>
                {{ cargandoPago ? 'Cargando...' : 'Pagar con Wompi (Requiere claves reales)' }}
              </button>

              <div v-if="errorPago" class="error-pago">
                {{ errorPago }}
              </div>

              <div class="nota-seguridad">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <span>Tu pago está protegido y encriptado</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Botones de acción inferiores -->
      <div class="acciones-footer">
        <button @click="volver" class="btn-volver">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
          </svg>
          Modificar reserva
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { formatCurrency } from '../utils/formatters'
import api from '../services/api'

const router = useRouter()
const reserva = ref(null)
const cargandoPago = ref(false)
const errorPago = ref(null)
const wompiCheckout = ref(null)
const wompiConfig = ref(null)

onMounted(async () => {
  // Cargar datos de la reserva desde localStorage
  const reservaData = localStorage.getItem('reservaData')

  if (!reservaData) {
    // Si no hay datos, redirigir a la página de reservas
    router.push('/reservar')
    return
  }

  reserva.value = JSON.parse(reservaData)

  // Cargar configuración de Wompi desde el backend
  try {
    const response = await api.get('/payments/wompi/config')
    if (response.data.success) {
      wompiConfig.value = response.data
      console.log('Configuración de Wompi cargada:', response.data)
    }
  } catch (error) {
    console.error('Error al cargar configuración de Wompi:', error)
  }
})

function formatearFechaCompleta(fecha) {
  if (!fecha) return ''
  const d = new Date(fecha)
  const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
  const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
  return `${dias[d.getDay()]}, ${d.getDate()} de ${meses[d.getMonth()]} ${d.getFullYear()}`
}

function calcularSubtotal() {
  if (!reserva.value) return 0
  return reserva.value.tipo.precio_base * reserva.value.noches * reserva.value.tipo.cantidad_seleccionada
}

function volver() {
  router.push('/reservar')
}

// Función de pago simulado para desarrollo
async function pagoSimulado() {
  try {
    cargandoPago.value = true
    errorPago.value = null

    console.log('💰 INICIANDO PAGO SIMULADO...')

    // Generar transaction_id simulado
    const transactionId = 'SIM-' + Date.now()

    // Calcular precio total
    const precioStr = reserva.value.precioTotal.replace('COP', '').replace(/\./g, '').trim()
    const precioTotal = parseInt(precioStr)

    console.log('📦 Datos de la reserva:', {
      transaction_id: transactionId,
      tipo_habitacion_id: reserva.value.tipo.id,
      precio_total: precioTotal
    })

    // Enviar reserva directamente al backend
    const response = await api.post('/payments/confirmar', {
      transaction_id: transactionId,
      tipo_habitacion_id: reserva.value.tipo.id,
      fecha_entrada: reserva.value.fechaEntrada,
      fecha_salida: reserva.value.fechaSalida,
      num_adultos: reserva.value.adultos,
      num_ninos: reserva.value.ninos,
      precio_total: precioTotal,
      cliente: {
        cedula: reserva.value.cliente.cedula,
        nombre: reserva.value.cliente.nombre,
        apellido: reserva.value.cliente.apellido,
        email: reserva.value.cliente.email,
        celular: reserva.value.cliente.telefono
      }
    })

    console.log('✅ Respuesta del backend:', response.data)

    if (response.data.success) {
      console.log('🎉 PAGO SIMULADO EXITOSO')
      // Redirigir a página de confirmación
      router.push({
        name: 'confirmacion',
        params: { reservaId: response.data.data.reserva_id }
      })
    } else {
      errorPago.value = response.data.message || 'Error al procesar el pago simulado'
    }
  } catch (error) {
    console.error('❌ Error en pago simulado:', error)
    errorPago.value = error.response?.data?.message || 'Error al procesar el pago simulado'
  } finally {
    cargandoPago.value = false
  }
}

async function abrirPasarelaPago() {
  try {
    cargandoPago.value = true
    errorPago.value = null

    // Calcular monto en centavos
    const precioStr = reserva.value.precioTotal.replace('COP', '').replace(/\./g, '').trim()
    const montoEnCentavos = parseInt(precioStr) * 100

    // Generar referencia única
    const reference = `HOTEL-${Date.now()}-${Math.random().toString(36).substring(2, 9).toUpperCase()}`

    console.log('🔄 Solicitando firma al backend...', { reference, montoEnCentavos })

    // LLAMAR AL BACKEND para obtener la firma de integridad
    const response = await api.post('/payments/wompi/create', {
      amount_in_cents: montoEnCentavos,
      currency: 'COP',
      reference: reference,
      customer_email: reserva.value.cliente.email
    })

    console.log('✅ Respuesta del backend:', response.data)

    if (!response.data.success) {
      throw new Error(response.data.message || 'Error al preparar el pago')
    }

    const paymentData = response.data.data

    console.log('📦 Datos recibidos del backend:', {
      public_key: paymentData.public_key,
      amount_in_cents: paymentData.amount_in_cents,
      reference: paymentData.reference,
      integrity_signature: paymentData.integrity_signature ? 'OK' : 'MISSING'
    })

    // VALIDAR que tengamos todos los datos necesarios
    if (!paymentData.public_key) {
      throw new Error('No se recibió la clave pública de Wompi del backend')
    }

    if (!paymentData.integrity_signature) {
      throw new Error('No se recibió la firma de integridad del backend')
    }

    // Cargar el script de Wompi si no está cargado
    if (!window.WidgetCheckout) {
      await cargarScriptWompi()
    }

    // Configuración del widget CON la firma del backend
    const wompiCheckoutConfig = {
      currency: paymentData.currency,
      amountInCents: paymentData.amount_in_cents,
      reference: paymentData.reference,
      publicKey: paymentData.public_key,
      signature: {
        integrity: paymentData.integrity_signature
      },
      redirectUrl: paymentData.redirect_url
    }

    // Agregar datos del cliente
    if (paymentData.customer_email) {
      wompiCheckoutConfig.customerData = {
        email: paymentData.customer_email,
        fullName: `${reserva.value.cliente.nombre} ${reserva.value.cliente.apellido}`
      }
    }

    console.log('🎨 Configuración del widget Wompi:', {
      currency: wompiCheckoutConfig.currency,
      amountInCents: wompiCheckoutConfig.amountInCents,
      reference: wompiCheckoutConfig.reference,
      publicKey: wompiCheckoutConfig.publicKey ? wompiCheckoutConfig.publicKey.substring(0, 20) + '...' : 'UNDEFINED',
      signature: {
        integrity: wompiCheckoutConfig.signature?.integrity ? wompiCheckoutConfig.signature.integrity.substring(0, 20) + '...' : 'UNDEFINED'
      },
      redirectUrl: wompiCheckoutConfig.redirectUrl
    })

    // Crear instancia del widget
    wompiCheckout.value = new window.WidgetCheckout(wompiCheckoutConfig)

    cargandoPago.value = false

    // Abrir el modal de pago
    wompiCheckout.value.open(function(result) {
      console.log('💳 Resultado de Wompi:', result)

      if (!result || !result.transaction) {
        console.log('❌ Usuario cerró el widget sin completar')
        return
      }

      const transaction = result.transaction

      if (transaction.status === 'APPROVED') {
        console.log('✅ Pago APROBADO')
        router.push('/confirmacion?id=' + transaction.id + '&status=APPROVED')
      } else if (transaction.status === 'DECLINED') {
        console.log('❌ Pago RECHAZADO')
        errorPago.value = 'El pago fue rechazado. Por favor intenta con otro método de pago.'
      } else if (transaction.status === 'PENDING') {
        console.log('⏳ Pago PENDIENTE')
        router.push('/confirmacion?id=' + transaction.id + '&status=PENDING')
      }
    })

  } catch (err) {
    console.error('❌ Error al abrir pasarela de pago:', err)
    errorPago.value = err.response?.data?.message || err.message || 'Error al cargar la pasarela de pago. Por favor intenta nuevamente.'
    cargandoPago.value = false
  }
}

function cargarScriptWompi() {
  return new Promise((resolve, reject) => {
    // Verificar si ya existe el script
    if (document.querySelector('script[src="https://checkout.wompi.co/widget.js"]')) {
      resolve()
      return
    }

    const script = document.createElement('script')
    script.src = 'https://checkout.wompi.co/widget.js'
    script.type = 'text/javascript'

    script.onload = () => {
      console.log('Script de Wompi cargado')
      resolve()
    }

    script.onerror = () => {
      reject(new Error('Error al cargar el script de Wompi'))
    }

    document.head.appendChild(script)
  })
}
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.resumen-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

/* Header */
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

.nav-links a:hover {
  background: rgba(255, 255, 255, 0.2);
}

/* Container */
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.main-content {
  padding: 2rem 1rem;
}

/* Loading */
.loading-container {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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

.loading-container p {
  color: #666;
  font-size: 1rem;
}

/* Progress Steps */
.progress-steps {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 2rem;
  margin-bottom: 3rem;
  padding: 2rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  position: relative;
}

.step:not(:last-child)::after {
  content: '';
  position: absolute;
  top: 20px;
  left: calc(100% + 1rem);
  width: 2rem;
  height: 2px;
  background: #e0e0e0;
}

.step.completed:not(:last-child)::after,
.step.active:not(:last-child)::after {
  background: #0071c2;
}

.step-circle {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 2px solid #e0e0e0;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  color: #999;
  transition: all 0.3s;
}

.step.completed .step-circle {
  background: #0071c2;
  border-color: #0071c2;
  color: white;
}

.step.active .step-circle {
  background: #0071c2;
  border-color: #0071c2;
  color: white;
  transform: scale(1.1);
}

.step span {
  font-size: 0.875rem;
  color: #666;
  font-weight: 500;
}

.step.completed span,
.step.active span {
  color: #0071c2;
  font-weight: 600;
}

/* Grid Layout */
.contenido-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 2rem;
  align-items: start;
}

/* Cards */
.card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  margin-bottom: 2rem;
}

.card-header {
  background: linear-gradient(135deg, #0071c2 0%, #005a9c 100%);
  color: white;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.card-header h2 {
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0;
}

.card-body {
  padding: 1.5rem;
}

/* Alojamiento destacado */
.alojamiento-destacado {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 1.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid #f0f0f0;
}

.alojamiento-destacado h3 {
  color: #0071c2;
  font-size: 1.5rem;
  flex: 1;
}

.badge-tipo {
  background: #e3f2fd;
  color: #0071c2;
  padding: 0.5rem 1rem;
  border-radius: 50px;
  font-size: 0.875rem;
  font-weight: 600;
}

/* Detalle lista */
.detalle-lista {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.detalle-item {
  display: flex;
  align-items: start;
  gap: 1rem;
}

.detalle-item svg {
  color: #0071c2;
  flex-shrink: 0;
  margin-top: 0.25rem;
}

.detalle-item > div {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.detalle-item strong {
  color: #333;
  font-size: 0.95rem;
}

.detalle-item span {
  color: #666;
  font-size: 0.9rem;
}

/* Amenidades */
.amenidades-completas {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 8px;
}

.amenidades-completas h4 {
  color: #333;
  font-size: 1rem;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.amenidades-completas h4 svg {
  color: #0071c2;
}

.amenidades-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 0.75rem;
}

.amenidad {
  color: #008009;
  font-size: 0.9rem;
}

/* Fechas */
.fechas-grid {
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  gap: 2rem;
  align-items: center;
  margin-bottom: 1.5rem;
}

.fecha-item {
  text-align: center;
}

.fecha-label {
  font-weight: 600;
  color: #666;
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.fecha-valor {
  font-size: 1.125rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.25rem;
}

.hora-valor {
  font-size: 0.875rem;
  color: #0071c2;
  font-weight: 500;
}

.divider-vertical {
  display: flex;
  align-items: center;
  justify-content: center;
}

.divider-vertical svg {
  color: #0071c2;
}

.duracion-estadia {
  background: #e3f2fd;
  padding: 1rem;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  color: #0071c2;
  font-size: 1rem;
}

.duracion-estadia svg {
  flex-shrink: 0;
}

/* Info grid */
.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.info-label {
  font-size: 0.875rem;
  color: #666;
  font-weight: 500;
}

.info-valor {
  font-size: 1rem;
  color: #333;
  font-weight: 600;
}

/* Columna precio */
.columna-precio {
  position: relative;
}

.sticky-card {
  position: sticky;
  top: 1rem;
}

.precio-desglose {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding-bottom: 1.5rem;
  margin-bottom: 1.5rem;
  border-bottom: 2px solid #f0f0f0;
}

.precio-item-titulo {
  margin-bottom: 0.5rem;
}

.nombre-habitacion {
  font-size: 1.1rem;
  font-weight: 600;
  color: #0071c2;
}

.precio-item {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  padding: 0.75rem 0;
}

.precio-item .descripcion {
  color: #666;
  font-size: 0.9rem;
  line-height: 1.5;
  flex: 1;
}

.precio-item .valor {
  font-weight: 600;
  color: #333;
  font-size: 1rem;
  white-space: nowrap;
}

.precio-item .valor-incluido {
  color: #008009;
  font-weight: 500;
  font-size: 0.9rem;
  white-space: nowrap;
}

.precio-total-box {
  background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
  padding: 1.5rem;
  border-radius: 8px;
  text-align: center;
  margin-bottom: 1.5rem;
}

.precio-total-label {
  font-size: 0.875rem;
  color: #0071c2;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.5rem;
}

.precio-total-valor {
  font-size: 2.5rem;
  font-weight: 700;
  color: #0071c2;
  margin-bottom: 0.5rem;
}

.precio-total-info {
  font-size: 0.875rem;
  color: #666;
}

.beneficios-lista {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #f0f0f0;
}

.test-mode-banner {
  background: #fff3cd;
  border: 2px solid #ffc107;
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 1rem;
  text-align: center;
  color: #856404;
  font-size: 0.875rem;
}

.beneficio {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #008009;
  font-size: 0.9rem;
}

.beneficio svg {
  flex-shrink: 0;
}

.btn-pagar {
  width: 100%;
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
  margin-top: 1.5rem;
  margin-bottom: 1rem;
}

.btn-pagar:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 113, 194, 0.4);
}

.btn-pagar:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.spinner-btn {
  border: 3px solid rgba(255, 255, 255, 0.3);
  border-top: 3px solid white;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-pago {
  background: #ffebee;
  color: #c62828;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  text-align: center;
  font-size: 0.9rem;
  border: 1px solid #ef9a9a;
}

.nota-seguridad {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #666;
  padding: 0.75rem;
  background: #f8f9fa;
  border-radius: 6px;
}

.nota-seguridad svg {
  color: #0071c2;
  flex-shrink: 0;
}

/* Políticas */
.mt-3 {
  margin-top: 1rem;
}

.politicas h4 {
  color: #333;
  font-size: 1rem;
  margin-bottom: 1rem;
}

.politicas ul {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.politicas li {
  font-size: 0.875rem;
  color: #666;
  padding-left: 1.5rem;
  position: relative;
}

.politicas li::before {
  content: '•';
  position: absolute;
  left: 0;
  color: #0071c2;
  font-weight: 700;
  font-size: 1.25rem;
}

/* Acciones footer */
.acciones-footer {
  display: flex;
  justify-content: center;
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 2px solid rgba(255, 255, 255, 0.3);
}

.btn-volver {
  padding: 1rem 2rem;
  background: white;
  color: #0071c2;
  border: 2px solid #0071c2;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  transition: all 0.3s;
}

.btn-volver:hover {
  background: #0071c2;
  color: white;
  transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 968px) {
  .contenido-grid {
    grid-template-columns: 1fr;
  }

  .sticky-card {
    position: static;
  }

  .progress-steps {
    gap: 1rem;
    padding: 1rem;
  }

  .step:not(:last-child)::after {
    width: 1rem;
    left: calc(100% + 0.5rem);
  }

  .fechas-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .divider-vertical {
    transform: rotate(90deg);
  }

  .container-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
}

@media (max-width: 640px) {
  .step span {
    display: none;
  }

  .precio-total-valor {
    font-size: 2rem;
  }

  .logo-hotel {
    font-size: 1.5rem;
  }
}
</style>
