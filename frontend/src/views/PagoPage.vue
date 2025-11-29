<template>
  <div class="pago-page">
    <!-- Header -->
    <header class="header-principal">
      <div class="container-header">
        <div class="logo-section">
          <h1 class="logo-hotel">🏨 Hotel Rocamonti</h1>
          <p class="tagline">Tu refugio junto a la laguna</p>
        </div>
      </div>
    </header>

    <div class="container main-content">
      <!-- Progress bar -->
      <div class="progress-steps">
        <div class="step completed">
          <div class="step-circle">✓</div>
          <span>Selección</span>
        </div>
        <div class="step completed">
          <div class="step-circle">✓</div>
          <span>Datos</span>
        </div>
        <div class="step completed">
          <div class="step-circle">✓</div>
          <span>Resumen</span>
        </div>
        <div class="step active">
          <div class="step-circle">4</div>
          <span>Pago</span>
        </div>
      </div>

      <div class="pago-grid">
        <!-- Columna izquierda - Widget de Wompi -->
        <div class="columna-widget">
          <div class="card">
            <div class="card-header">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                <line x1="1" y1="10" x2="23" y2="10"></line>
              </svg>
              <h2>Método de pago</h2>
            </div>
            <div class="card-body">
              <!-- Loading overlay -->
              <div v-if="loading" class="loading">
                <div class="spinner"></div>
                <p>Preparando pasarela de pago segura...</p>
              </div>

              <!-- Error message -->
              <div v-if="error" class="error-box">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"></circle>
                  <line x1="12" y1="8" x2="12" y2="12"></line>
                  <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <h3>Error al cargar el pago</h3>
                <p>{{ error }}</p>
                <button @click="volver" class="btn-volver">Volver al resumen</button>
              </div>

              <!-- Formulario simple de Wompi -->
              <div class="wompi-container" :style="{ display: loading || error ? 'none' : 'block' }">
                <div class="test-mode-banner">
                  🧪 <strong>Modo Prueba Activado</strong> - Usa tarjetas de prueba de Wompi
                </div>

                <div class="widget-info">
                  <div class="icono-pago">💳</div>
                  <h3>Pagar con Wompi</h3>
                  <p>Serás redirigido a la pasarela de pago segura de Wompi</p>

                  <button @click="procesarPago" class="btn-pago-manual" :disabled="!wompiPublicKey || procesando">
                    {{ procesando ? 'Procesando...' : (wompiPublicKey ? 'Proceder al pago' : 'Cargando...') }}
                  </button>
                </div>
              </div>

              <div class="metodos-pago-info">
                <h4>Métodos de pago aceptados:</h4>
                <div class="metodos-iconos">
                  <div class="metodo-icono" title="Tarjetas de crédito">💳</div>
                  <div class="metodo-icono" title="PSE">🏦</div>
                  <div class="metodo-icono" title="Nequi">📱</div>
                  <div class="metodo-icono" title="Bancolombia">💰</div>
                </div>
              </div>

              <div class="seguridad-info">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <div>
                  <strong>Pago 100% seguro</strong>
                  <p>Tus datos están protegidos con encriptación SSL de 256 bits</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Columna derecha - Resumen -->
        <div class="columna-resumen">
          <div class="card sticky-card">
            <div class="card-header">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
              </svg>
              <h2>Resumen de tu reserva</h2>
            </div>
            <div class="card-body" v-if="reserva">
              <div class="resumen-compacto">
                <h3>{{ reserva.tipo.nombre }}</h3>
                <p class="fechas-compactas">
                  {{ formatearFecha(reserva.filtros.fechaEntrada) }} - {{ formatearFecha(reserva.filtros.fechaSalida) }}
                </p>
                <p class="noches-compactas">{{ reserva.noches }} noche{{ reserva.noches > 1 ? 's' : '' }}</p>
              </div>

              <div class="detalles-compactos">
                <div class="detalle-row">
                  <span>Habitaciones:</span>
                  <span>{{ reserva.tipo.cantidad_seleccionada }}</span>
                </div>
                <div class="detalle-row">
                  <span>Huéspedes:</span>
                  <span>{{ reserva.filtros.adultos }} adulto{{ reserva.filtros.adultos > 1 ? 's' : '' }}</span>
                </div>
                <div class="detalle-row">
                  <span>Check-in:</span>
                  <span>{{ reserva.cliente.horaLlegada }}</span>
                </div>
                <div class="detalle-row">
                  <span>Check-out:</span>
                  <span>{{ reserva.cliente.horaSalida }}</span>
                </div>
              </div>

              <div class="precio-final">
                <div class="precio-label">Total a pagar</div>
                <div class="precio-valor">{{ reserva.precioTotal }}</div>
              </div>

              <div class="cliente-info">
                <h4>Reserva a nombre de:</h4>
                <p>{{ reserva.cliente.nombre }} {{ reserva.cliente.apellido }}</p>
                <p class="email-cliente">{{ reserva.cliente.email }}</p>
              </div>
            </div>
          </div>

          <button @click="volver" class="btn-modificar">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="19" y1="12" x2="5" y2="12"></line>
              <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Modificar reserva
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const loading = ref(true)
const error = ref(null)
const reserva = ref(null)
const transactionId = ref(null)
const wompiCheckout = ref(null)
const wompiPublicKey = ref('')
const procesando = ref(false)

onMounted(async () => {
  // Cargar datos de la reserva
  const reservaData = localStorage.getItem('reservaData')

  if (!reservaData) {
    router.push('/reservar')
    return
  }

  reserva.value = JSON.parse(reservaData)

  try {
    // Obtener la configuración de Wompi del backend
    const configResponse = await api.get('/payments/wompi/config')
    if (configResponse.data.success) {
      wompiPublicKey.value = configResponse.data.publicKey
    }
  } catch (err) {
    console.error('Error al obtener config de Wompi:', err)
    // Usar clave por defecto de prueba si falla
    wompiPublicKey.value = 'pub_test_xP11VTEBcbLmeyPFPqPbhwrVWB5JbJVN'
  }

  // MÉTODO SIMPLE: redirección directa a Wompi en modo prueba
  loading.value = false
})

async function procesarPago() {
  procesando.value = true
  error.value = null

  try {
    const amount = calcularMontoEnCentavos()
    const reference = `ROCAMONTI-${Date.now()}`

    console.log('Solicitando firma al backend...')

    // Llamar al backend para obtener la firma
    const response = await api.post('/payments/wompi/create', {
      amount_in_cents: amount,
      currency: 'COP',
      reference: reference,
      customer_email: reserva.value.cliente.email
    })

    console.log('Respuesta del backend:', response.data)

    if (!response.data.success) {
      throw new Error(response.data.message || 'Error al preparar el pago')
    }

    const paymentData = response.data.data

    // Construir URL de Wompi con TODOS los parámetros incluyendo la firma
    const wompiUrl = new URL('https://checkout.wompi.co/p/')
    wompiUrl.searchParams.append('public-key', paymentData.public_key)
    wompiUrl.searchParams.append('currency', paymentData.currency)
    wompiUrl.searchParams.append('amount-in-cents', paymentData.amount_in_cents)
    wompiUrl.searchParams.append('reference', paymentData.reference)
    wompiUrl.searchParams.append('signature:integrity', paymentData.integrity_signature)
    wompiUrl.searchParams.append('redirect-url', paymentData.redirect_url)
    wompiUrl.searchParams.append('customer-data:email', paymentData.customer_email)
    wompiUrl.searchParams.append('customer-data:full-name', `${reserva.value.cliente.nombre} ${reserva.value.cliente.apellido}`)

    console.log('Redirigiendo a Wompi con firma:', paymentData.integrity_signature)

    // Redireccionar a Wompi
    window.location.href = wompiUrl.toString()

  } catch (err) {
    console.error('Error al procesar el pago:', err)
    error.value = err.response?.data?.message || err.message || 'Error al procesar el pago'
    procesando.value = false
  }
}

async function crearTransaccionWompi() {
  try {
    const amount = calcularMontoEnCentavos()
    const reference = `ROCAMONTI-${Date.now()}`

    transactionId.value = reference

    console.log('Llamando al backend para obtener firma...')

    // LLAMAR AL BACKEND para que calcule la firma
    const response = await api.post('/payments/wompi/create', {
      amount_in_cents: amount,
      currency: 'COP',
      reference: reference,
      customer_email: reserva.value.cliente.email
    })

    console.log('Respuesta del backend:', response.data)

    if (!response.data.success) {
      throw new Error(response.data.message || 'Error al preparar el pago')
    }

    // Usar datos con firma del backend
    await cargarWidgetWompi(response.data.data)
  } catch (err) {
    console.error('Error completo:', err)
    throw new Error(err.response?.data?.message || err.message || 'No se pudo crear la transacción')
  }
}

async function cargarWidgetWompi(paymentData) {
  try {
    if (!window.WidgetCheckout) {
      await cargarScriptWompi()
    }

    console.log('=== DATOS DE PAGO ===')
    console.log('Amount:', paymentData.amount_in_cents)
    console.log('Reference:', paymentData.reference)
    console.log('Currency:', paymentData.currency)
    console.log('Public Key:', paymentData.public_key)
    console.log('Signature:', paymentData.integrity_signature)

    // Configuración del widget CON LA FIRMA del backend
    const wompiConfig = {
      currency: paymentData.currency,
      amountInCents: paymentData.amount_in_cents,
      reference: paymentData.reference,
      publicKey: paymentData.public_key,
      signature: {
        integrity: paymentData.integrity_signature
      },
      redirectUrl: paymentData.redirect_url || (window.location.origin + '/confirmacion')
    }

    // Agregar datos del cliente
    if (paymentData.customer_email) {
      wompiConfig.customerData = {
        email: paymentData.customer_email,
        fullName: `${reserva.value.cliente.nombre} ${reserva.value.cliente.apellido}`
      }
    }

    console.log('=== CONFIG FINAL WOMPI ===')
    console.log(JSON.stringify(wompiConfig, null, 2))

    wompiCheckout.value = new window.WidgetCheckout(wompiConfig)

    loading.value = false

    // Abrir el widget
    setTimeout(() => abrirPagoManual(), 500)

  } catch (err) {
    console.error('Error en cargarWidgetWompi:', err)
    error.value = err.message || 'Error al cargar el widget de pago'
    loading.value = false
  }
}

function abrirPagoManual() {
  if (!wompiCheckout.value) {
    error.value = 'El widget de pago no está listo. Por favor recarga la página.'
    return
  }

  // Abrir el modal de pago de Wompi
  wompiCheckout.value.open(function(result) {
    console.log('Resultado de Wompi:', result)
    const transaction = result.transaction

    if (transaction && transaction.status === 'APPROVED') {
      // Redirigir a página de confirmación
      router.push('/confirmacion?transaction_id=' + transaction.id)
    } else if (transaction && transaction.status === 'DECLINED') {
      error.value = 'El pago fue rechazado. Por favor intenta con otro método de pago.'
    } else if (transaction && transaction.status === 'PENDING') {
      router.push('/confirmacion?transaction_id=' + transaction.id + '&status=pending')
    }
  })
}

function cargarScriptWompi() {
  return new Promise((resolve, reject) => {
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

function calcularMontoEnCentavos() {
  // Remover el formato de moneda y convertir a centavos
  const precioStr = reserva.value.precioTotal.replace('COP', '').replace(/\./g, '').replace(/,/g, '').trim()
  const monto = parseInt(precioStr) * 100 // Wompi requiere el monto en centavos
  console.log('Calculando monto:', precioStr, '-> centavos:', monto)
  return monto
}

function formatearFecha(fecha) {
  if (!fecha) return ''
  const d = new Date(fecha)
  const dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']
  const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic']
  return `${dias[d.getDay()]}, ${d.getDate()} ${meses[d.getMonth()]}`
}

function volver() {
  router.push('/resumen')
}

onBeforeUnmount(() => {
  // Limpiar scripts de Wompi si es necesario
  const container = document.getElementById('wompi-widget-container')
  if (container) {
    container.innerHTML = ''
  }
})
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.pago-page {
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
  text-align: center;
}

.logo-section {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.logo-hotel {
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
}

.tagline {
  font-size: 0.9rem;
  opacity: 0.9;
  margin-top: 0.25rem;
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

/* Grid */
.pago-grid {
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
  padding: 2rem;
}

/* Loading */
.loading {
  text-align: center;
  padding: 4rem 2rem;
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

.loading p {
  color: #666;
}

/* Error */
.error-box {
  text-align: center;
  padding: 3rem 2rem;
}

.error-box svg {
  color: #d32f2f;
  margin-bottom: 1rem;
}

.error-box h3 {
  color: #d32f2f;
  margin-bottom: 0.5rem;
}

.error-box p {
  color: #666;
  margin-bottom: 2rem;
}

/* Wompi container */
.wompi-container {
  min-height: 400px;
  padding: 1rem 0;
}

.test-mode-banner {
  background: #fff3cd;
  border: 2px solid #ffc107;
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 2rem;
  text-align: center;
  color: #856404;
  font-size: 0.95rem;
}

.widget-info {
  text-align: center;
  padding: 3rem 2rem;
}

.icono-pago {
  font-size: 4rem;
  margin-bottom: 1.5rem;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

.widget-info h3 {
  color: #0071c2;
  font-size: 1.5rem;
  margin-bottom: 1rem;
}

.widget-info p {
  color: #666;
  margin-bottom: 0.5rem;
  font-size: 1rem;
}

.texto-secundario {
  color: #999 !important;
  font-size: 0.875rem !important;
  margin-top: 1.5rem !important;
  margin-bottom: 1rem !important;
}

.btn-pago-manual {
  background: linear-gradient(135deg, #0071c2 0%, #005a9c 100%);
  color: white;
  border: none;
  padding: 1rem 2.5rem;
  border-radius: 8px;
  font-size: 1.125rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 4px 12px rgba(0, 113, 194, 0.3);
  margin-top: 1rem;
}

.btn-pago-manual:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 113, 194, 0.4);
}

.btn-pago-manual:active {
  transform: translateY(0);
}

.btn-pago-manual:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.metodos-pago-info {
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid #e0e0e0;
}

.metodos-pago-info h4 {
  color: #333;
  font-size: 0.95rem;
  margin-bottom: 1rem;
}

.metodos-iconos {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.metodo-icono {
  font-size: 2rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
  transition: all 0.3s;
  cursor: help;
}

.metodo-icono:hover {
  transform: scale(1.1);
  background: #e3f2fd;
}

.seguridad-info {
  display: flex;
  gap: 1rem;
  align-items: start;
  padding: 1.5rem;
  background: #e8f5e9;
  border-radius: 8px;
  margin-top: 2rem;
}

.seguridad-info svg {
  color: #2e7d32;
  flex-shrink: 0;
  margin-top: 0.25rem;
}

.seguridad-info strong {
  color: #2e7d32;
  display: block;
  margin-bottom: 0.25rem;
}

.seguridad-info p {
  color: #666;
  font-size: 0.875rem;
  margin: 0;
}

/* Resumen compacto */
.resumen-compacto {
  padding-bottom: 1.5rem;
  margin-bottom: 1.5rem;
  border-bottom: 2px solid #f0f0f0;
}

.resumen-compacto h3 {
  color: #0071c2;
  font-size: 1.125rem;
  margin-bottom: 0.5rem;
}

.fechas-compactas {
  color: #666;
  font-size: 0.95rem;
  margin-bottom: 0.25rem;
}

.noches-compactas {
  color: #333;
  font-weight: 600;
  font-size: 0.95rem;
}

.detalles-compactos {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.detalle-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
  color: #666;
}

.detalle-row span:last-child {
  font-weight: 600;
  color: #333;
}

.precio-final {
  background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
  padding: 1.5rem;
  border-radius: 8px;
  text-align: center;
  margin-bottom: 1.5rem;
}

.precio-label {
  font-size: 0.875rem;
  color: #0071c2;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.5rem;
}

.precio-valor {
  font-size: 2rem;
  font-weight: 700;
  color: #0071c2;
}

.cliente-info {
  padding: 1.5rem;
  background: #f8f9fa;
  border-radius: 8px;
}

.cliente-info h4 {
  color: #333;
  font-size: 0.9rem;
  margin-bottom: 0.75rem;
  font-weight: 600;
}

.cliente-info p {
  color: #666;
  font-size: 0.95rem;
  margin-bottom: 0.25rem;
}

.email-cliente {
  color: #0071c2 !important;
  font-weight: 500;
}

.sticky-card {
  position: sticky;
  top: 1rem;
}

.btn-modificar {
  width: 100%;
  padding: 1rem 1.5rem;
  background: white;
  color: #0071c2;
  border: 2px solid #0071c2;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: all 0.3s;
}

.btn-modificar:hover {
  background: #0071c2;
  color: white;
}

.btn-volver {
  padding: 0.875rem 1.5rem;
  background: #0071c2;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-volver:hover {
  background: #005a9c;
}

/* Responsive */
@media (max-width: 968px) {
  .pago-grid {
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
}

@media (max-width: 640px) {
  .step span {
    display: none;
  }

  .logo-hotel {
    font-size: 1.5rem;
  }

  .precio-valor {
    font-size: 1.75rem;
  }

  .metodos-iconos {
    gap: 0.5rem;
  }

  .metodo-icono {
    font-size: 1.5rem;
    padding: 0.75rem;
  }
}
</style>
