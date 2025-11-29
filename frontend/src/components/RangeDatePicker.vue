<template>
  <div class="range-date-picker">
    <!-- Input visual que abre el calendario -->
    <div class="date-input-wrapper" @click="toggleCalendar">
      <div class="date-input">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
          <line x1="16" y1="2" x2="16" y2="6"></line>
          <line x1="8" y1="2" x2="8" y2="6"></line>
          <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
        <span v-if="!fechaEntrada && !fechaSalida" class="placeholder">
          Selecciona fechas de entrada y salida
        </span>
        <span v-else class="selected-dates">
          <span class="date-value">{{ formatearFecha(fechaEntrada) }}</span>
          <span class="separator">→</span>
          <span class="date-value">{{ formatearFecha(fechaSalida) }}</span>
        </span>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="chevron">
          <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
      </div>
    </div>

    <!-- Calendario desplegable -->
    <div v-if="mostrarCalendario" class="calendar-dropdown" @click.stop>
      <div class="calendar-header">
        <button type="button" @click="mesAnterior" class="nav-button">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="15 18 9 12 15 6"></polyline>
          </svg>
        </button>
        <h3>{{ nombreMes }} {{ año }}</h3>
        <button type="button" @click="mesSiguiente" class="nav-button">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="9 18 15 12 9 6"></polyline>
          </svg>
        </button>
      </div>

      <!-- Leyenda de colores -->
      <div class="calendar-legend">
        <div class="legend-item">
          <span class="legend-dot disponible"></span>
          <span>Puedes reservar</span>
        </div>
        <div class="legend-item">
          <span class="legend-dot ocupado"></span>
          <span>Ocupado</span>
        </div>
      </div>

      <!-- Días de la semana -->
      <div class="calendar-weekdays">
        <div v-for="dia in diasSemana" :key="dia" class="weekday">{{ dia }}</div>
      </div>

      <!-- Grid de días -->
      <div class="calendar-grid">
        <div
          v-for="(dia, index) in diasMes"
          :key="index"
          :class="getClaseDia(dia)"
          @click="seleccionarDia(dia)"
        >
          <span class="day-number">{{ dia.numero }}</span>
        </div>
      </div>

      <!-- Botones de acción -->
      <div class="calendar-footer">
        <button type="button" @click="limpiarSeleccion" class="btn-clear">
          Limpiar
        </button>
        <button type="button" @click="aplicarFechas" class="btn-apply" :disabled="!fechaEntradaTemp || !fechaSalidaTemp">
          Aplicar
        </button>
      </div>

      <!-- Loading indicator -->
      <div v-if="cargandoDisponibilidad" class="loading-overlay">
        <div class="spinner"></div>
        <p>Verificando disponibilidad...</p>
      </div>
    </div>

    <!-- Overlay para cerrar al hacer click fuera -->
    <div v-if="mostrarCalendario" class="calendar-overlay" @click="cerrarCalendario"></div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import api from '@/services/api'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({ fechaEntrada: null, fechaSalida: null })
  },
  tipoHabitacionId: {
    type: Number,
    default: null
  }
})

const emit = defineEmits(['update:modelValue'])

// Estado del calendario
const mostrarCalendario = ref(false)
const mes = ref(new Date().getMonth())
const año = ref(new Date().getFullYear())
const fechaEntradaTemp = ref(props.modelValue.fechaEntrada)
const fechaSalidaTemp = ref(props.modelValue.fechaSalida)
const fechaEntrada = ref(props.modelValue.fechaEntrada)
const fechaSalida = ref(props.modelValue.fechaSalida)
const fechasOcupadas = ref([])
const cargandoDisponibilidad = ref(false)

const diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']
const nombresMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']

const nombreMes = computed(() => nombresMeses[mes.value])

// Generar días del mes
const diasMes = computed(() => {
  const primerDia = new Date(año.value, mes.value, 1)
  const ultimoDia = new Date(año.value, mes.value + 1, 0)
  const diasEnMes = ultimoDia.getDate()
  const primerDiaSemana = primerDia.getDay()

  const dias = []

  // Días vacíos antes del primer día
  for (let i = 0; i < primerDiaSemana; i++) {
    dias.push({ numero: null, fecha: null, vacio: true })
  }

  // Días del mes
  for (let i = 1; i <= diasEnMes; i++) {
    const fecha = new Date(año.value, mes.value, i)
    dias.push({
      numero: i,
      fecha: fecha,
      vacio: false
    })
  }

  return dias
})

// Cargar fechas ocupadas cuando cambia el tipo de habitación o el mes
watch([() => props.tipoHabitacionId, mes, año], async () => {
  if (props.tipoHabitacionId) {
    await cargarDisponibilidad()
  }
}, { immediate: true })

async function cargarDisponibilidad() {
  if (!props.tipoHabitacionId) return

  cargandoDisponibilidad.value = true
  try {
    const response = await api.get('/hotel-api-emergencia.php', {
      params: {
        action: 'occupied_dates',
        tipo_id: props.tipoHabitacionId
      }
    })

    if (response.data.success) {
      fechasOcupadas.value = response.data.fechas.map(f => f.fecha)
    }
  } catch (error) {
    console.error('Error cargando disponibilidad:', error)
  } finally {
    cargandoDisponibilidad.value = false
  }
}

function toggleCalendar() {
  mostrarCalendario.value = !mostrarCalendario.value
}

function cerrarCalendario() {
  mostrarCalendario.value = false
}

function mesAnterior() {
  if (mes.value === 0) {
    mes.value = 11
    año.value--
  } else {
    mes.value--
  }
}

function mesSiguiente() {
  if (mes.value === 11) {
    mes.value = 0
    año.value++
  } else {
    mes.value++
  }
}

function getClaseDia(dia) {
  if (dia.vacio) return 'calendar-day empty'

  const clases = ['calendar-day']
  const hoy = new Date()
  hoy.setHours(0, 0, 0, 0)

  // No permitir fechas pasadas
  if (dia.fecha < hoy) {
    clases.push('pasado')
    return clases.join(' ')
  }

  const fechaStr = formatearFechaISO(dia.fecha)

  // Verificar si está ocupada
  const estaOcupada = fechasOcupadas.value.includes(fechaStr)
  if (estaOcupada) {
    clases.push('ocupado')
  } else {
    clases.push('disponible')
  }

  // Verificar si está seleccionada
  if (fechaEntradaTemp.value && formatearFechaISO(new Date(fechaEntradaTemp.value)) === fechaStr) {
    clases.push('seleccionado inicio')
  }
  if (fechaSalidaTemp.value && formatearFechaISO(new Date(fechaSalidaTemp.value)) === fechaStr) {
    clases.push('seleccionado fin')
  }

  // Verificar si está en el rango
  if (fechaEntradaTemp.value && fechaSalidaTemp.value) {
    const entrada = new Date(fechaEntradaTemp.value)
    const salida = new Date(fechaSalidaTemp.value)
    if (dia.fecha > entrada && dia.fecha < salida) {
      clases.push('en-rango')
    }
  }

  return clases.join(' ')
}

function seleccionarDia(dia) {
  if (dia.vacio) return

  const hoy = new Date()
  hoy.setHours(0, 0, 0, 0)

  // No permitir fechas pasadas
  if (dia.fecha < hoy) return

  const fechaStr = formatearFechaISO(dia.fecha)

  // No permitir seleccionar días ocupados
  if (fechasOcupadas.value.includes(fechaStr)) {
    return
  }

  // Lógica de selección de rango
  if (!fechaEntradaTemp.value || (fechaEntradaTemp.value && fechaSalidaTemp.value)) {
    // Primera selección o reiniciar
    fechaEntradaTemp.value = fechaStr
    fechaSalidaTemp.value = null
  } else {
    // Segunda selección
    const entrada = new Date(fechaEntradaTemp.value)
    if (dia.fecha < entrada) {
      // Si selecciona una fecha anterior, la pone como entrada
      fechaSalidaTemp.value = fechaEntradaTemp.value
      fechaEntradaTemp.value = fechaStr
    } else {
      // Fecha de salida
      fechaSalidaTemp.value = fechaStr

      // Verificar que no haya días ocupados en el rango
      const fechasEnRango = obtenerFechasEnRango(new Date(fechaEntradaTemp.value), dia.fecha)
      const hayOcupadas = fechasEnRango.some(f => fechasOcupadas.value.includes(f))

      if (hayOcupadas) {
        alert('No se puede seleccionar este rango porque contiene fechas ocupadas')
        fechaSalidaTemp.value = null
      }
    }
  }
}

function obtenerFechasEnRango(inicio, fin) {
  const fechas = []
  const actual = new Date(inicio)
  actual.setDate(actual.getDate() + 1) // Empezar del día siguiente

  while (actual < fin) {
    fechas.push(formatearFechaISO(actual))
    actual.setDate(actual.getDate() + 1)
  }

  return fechas
}

function limpiarSeleccion() {
  fechaEntradaTemp.value = null
  fechaSalidaTemp.value = null
}

function aplicarFechas() {
  if (fechaEntradaTemp.value && fechaSalidaTemp.value) {
    fechaEntrada.value = fechaEntradaTemp.value
    fechaSalida.value = fechaSalidaTemp.value

    emit('update:modelValue', {
      fechaEntrada: fechaEntradaTemp.value,
      fechaSalida: fechaSalidaTemp.value
    })

    cerrarCalendario()
  }
}

function formatearFecha(fecha) {
  if (!fecha) return ''
  const d = new Date(fecha)
  return d.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' })
}

function formatearFechaISO(fecha) {
  const año = fecha.getFullYear()
  const mes = String(fecha.getMonth() + 1).padStart(2, '0')
  const dia = String(fecha.getDate()).padStart(2, '0')
  return `${año}-${mes}-${dia}`
}
</script>

<style scoped>
.range-date-picker {
  position: relative;
  width: 100%;
}

.date-input-wrapper {
  cursor: pointer;
}

.date-input {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  transition: all 0.3s;
  min-height: 48px;
}

.date-input:hover {
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.date-input svg:first-child {
  color: #667eea;
  flex-shrink: 0;
}

.placeholder {
  color: #9ca3af;
  flex: 1;
  font-size: 14px;
}

.selected-dates {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 1;
  font-weight: 500;
  color: #374151;
}

.date-value {
  color: #667eea;
}

.separator {
  color: #9ca3af;
  font-size: 18px;
}

.chevron {
  color: #9ca3af;
  flex-shrink: 0;
}

.calendar-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.3);
  z-index: 999;
}

.calendar-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  padding: 20px;
  z-index: 1000;
  min-width: 320px;
  animation: slideDown 0.2s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 2px solid #f3f4f6;
}

.calendar-header h3 {
  font-size: 18px;
  font-weight: 600;
  color: #374151;
  margin: 0;
}

.nav-button {
  background: none;
  border: none;
  padding: 8px;
  cursor: pointer;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  color: #6b7280;
}

.nav-button:hover {
  background: #f3f4f6;
  color: #667eea;
}

.calendar-legend {
  display: flex;
  gap: 16px;
  margin-bottom: 16px;
  flex-wrap: wrap;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #6b7280;
}

.legend-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  border: 2px solid #e5e7eb;
}

.legend-dot.disponible {
  background: #10b981;
  border-color: #10b981;
}

.legend-dot.ocupado {
  background: #ef4444;
  border-color: #ef4444;
}

.legend-dot.seleccionado {
  background: #667eea;
  border-color: #667eea;
}

.calendar-weekdays {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 4px;
  margin-bottom: 8px;
}

.weekday {
  text-align: center;
  font-size: 12px;
  font-weight: 600;
  color: #6b7280;
  padding: 8px 0;
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 4px;
}

.calendar-day {
  aspect-ratio: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
}

.calendar-day.empty {
  cursor: default;
  visibility: hidden;
}

.calendar-day.pasado {
  color: #d1d5db;
  cursor: not-allowed;
  background: #f9fafb;
}

.calendar-day.disponible {
  background: #10b981;
  color: white;
  font-weight: 600;
}

.calendar-day.disponible:hover {
  background: #059669;
  color: white;
  transform: scale(1.1);
  box-shadow: 0 4px 8px rgba(16, 185, 129, 0.4);
}

.calendar-day.ocupado {
  background: #ef4444;
  color: white;
  cursor: not-allowed;
  font-weight: 600;
}

.calendar-day.seleccionado {
  background: #667eea !important;
  color: white !important;
  font-weight: 700;
}

.calendar-day.en-rango {
  background: #eef2ff;
  color: #667eea;
}

.calendar-footer {
  display: flex;
  gap: 12px;
  margin-top: 16px;
  padding-top: 16px;
  border-top: 2px solid #f3f4f6;
}

.btn-clear,
.btn-apply {
  flex: 1;
  padding: 10px 16px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
  font-size: 14px;
}

.btn-clear {
  background: #f3f4f6;
  color: #6b7280;
}

.btn-clear:hover {
  background: #e5e7eb;
}

.btn-apply {
  background: #667eea;
  color: white;
}

.btn-apply:hover:not(:disabled) {
  background: #5568d3;
  transform: translateY(-1px);
  box-shadow: 0 4px 6px rgba(102, 126, 234, 0.3);
}

.btn-apply:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.95);
  border-radius: 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f4f6;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.loading-overlay p {
  color: #6b7280;
  font-size: 14px;
  font-weight: 500;
}

@media (max-width: 640px) {
  .calendar-dropdown {
    left: 50%;
    transform: translateX(-50%);
    width: calc(100vw - 40px);
    max-width: 380px;
  }
}
</style>
