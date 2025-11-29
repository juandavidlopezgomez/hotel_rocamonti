<template>
  <div class="calendario-reservas">
    <div class="calendario-header">
      <button @click="mesAnterior" :disabled="!puedeRetroceder">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="15 18 9 12 15 6"></polyline>
        </svg>
      </button>
      <h3>{{ mesActualTexto }}</h3>
      <button @click="mesSiguiente">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="9 18 15 12 9 6"></polyline>
        </svg>
      </button>
    </div>

    <div class="calendario-dias-semana">
      <div v-for="dia in diasSemana" :key="dia" class="dia-semana">{{ dia }}</div>
    </div>

    <div class="calendario-grid">
      <div
        v-for="(dia, index) in diasDelMes"
        :key="index"
        :class="['calendario-dia', {
          'vacio': !dia.numero,
          'pasado': dia.esPasado,
          'ocupado': dia.estaOcupado,
          'disponible': !dia.estaOcupado && dia.numero && !dia.esPasado,
          'seleccionado-entrada': dia.esEntrada,
          'seleccionado-salida': dia.esSalida,
          'en-rango': dia.enRango,
          'hoy': dia.esHoy
        }]"
        @click="seleccionarFecha(dia)"
      >
        <span v-if="dia.numero">{{ dia.numero }}</span>
      </div>
    </div>

    <div class="calendario-leyenda">
      <div class="leyenda-item">
        <span class="color-box disponible"></span>
        <span>Puedes reservar</span>
      </div>
      <div class="leyenda-item">
        <span class="color-box ocupado"></span>
        <span>Ocupado</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import api from '../services/api'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({ fechaEntrada: '', fechaSalida: '' })
  },
  tipoHabitacionId: {
    type: Number,
    default: null
  }
})

const emit = defineEmits(['update:modelValue'])

const fechaActual = ref(new Date())
const seleccionando = ref('entrada') // 'entrada' o 'salida'
const fechasOcupadas = ref([])
const totalHabitaciones = ref(0)

const diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']

const mesActualTexto = computed(() => {
  const meses = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
  ]
  return `${meses[fechaActual.value.getMonth()]} ${fechaActual.value.getFullYear()}`
})

const puedeRetroceder = computed(() => {
  const hoy = new Date()
  const mesActual = new Date(fechaActual.value.getFullYear(), fechaActual.value.getMonth(), 1)
  const mesHoy = new Date(hoy.getFullYear(), hoy.getMonth(), 1)
  return mesActual > mesHoy
})

const diasDelMes = computed(() => {
  const year = fechaActual.value.getFullYear()
  const month = fechaActual.value.getMonth()

  const primerDia = new Date(year, month, 1)
  const ultimoDia = new Date(year, month + 1, 0)

  const dias = []

  // Días vacíos al inicio
  for (let i = 0; i < primerDia.getDay(); i++) {
    dias.push({ numero: null })
  }

  // Días del mes
  const hoy = new Date()
  hoy.setHours(0, 0, 0, 0)

  for (let dia = 1; dia <= ultimoDia.getDate(); dia++) {
    const fecha = new Date(year, month, dia)
    const fechaStr = fecha.toISOString().split('T')[0]

    const fechaOcupada = fechasOcupadas.value.find(f => f.fecha === fechaStr)
    const estaOcupado = fechaOcupada?.completamente_ocupada || false

    const entrada = props.modelValue.fechaEntrada ? new Date(props.modelValue.fechaEntrada) : null
    const salida = props.modelValue.fechaSalida ? new Date(props.modelValue.fechaSalida) : null

    dias.push({
      numero: dia,
      fecha: fechaStr,
      esPasado: fecha < hoy,
      esHoy: fecha.getTime() === hoy.getTime(),
      estaOcupado,
      esEntrada: entrada && fecha.getTime() === entrada.getTime(),
      esSalida: salida && fecha.getTime() === salida.getTime(),
      enRango: entrada && salida && fecha > entrada && fecha < salida
    })
  }

  return dias
})

function mesAnterior() {
  fechaActual.value = new Date(fechaActual.value.getFullYear(), fechaActual.value.getMonth() - 1, 1)
}

function mesSiguiente() {
  fechaActual.value = new Date(fechaActual.value.getFullYear(), fechaActual.value.getMonth() + 1, 1)
}

function seleccionarFecha(dia) {
  if (!dia.numero || dia.esPasado || dia.estaOcupado) return

  const nuevasFechas = { ...props.modelValue }

  if (seleccionando.value === 'entrada' || (nuevasFechas.fechaEntrada && nuevasFechas.fechaSalida)) {
    // Seleccionar fecha de entrada
    nuevasFechas.fechaEntrada = dia.fecha
    nuevasFechas.fechaSalida = ''
    seleccionando.value = 'salida'
  } else {
    // Seleccionar fecha de salida
    const entrada = new Date(nuevasFechas.fechaEntrada)
    const salida = new Date(dia.fecha)

    if (salida > entrada) {
      // Verificar que no haya fechas ocupadas en el rango
      const hayOcupadas = verificarRangoDisponible(nuevasFechas.fechaEntrada, dia.fecha)
      if (hayOcupadas) {
        alert('Hay fechas ocupadas en el rango seleccionado. Por favor, elija otras fechas.')
        return
      }

      nuevasFechas.fechaSalida = dia.fecha
      seleccionando.value = 'entrada'
    } else {
      nuevasFechas.fechaEntrada = dia.fecha
      nuevasFechas.fechaSalida = ''
      seleccionando.value = 'salida'
    }
  }

  emit('update:modelValue', nuevasFechas)
}

function verificarRangoDisponible(fechaEntrada, fechaSalida) {
  const entrada = new Date(fechaEntrada)
  const salida = new Date(fechaSalida)

  for (let d = new Date(entrada); d < salida; d.setDate(d.getDate() + 1)) {
    const fechaStr = d.toISOString().split('T')[0]
    const fechaOcupada = fechasOcupadas.value.find(f => f.fecha === fechaStr)
    if (fechaOcupada?.completamente_ocupada) {
      return true
    }
  }

  return false
}

async function cargarFechasOcupadas() {
  if (!props.tipoHabitacionId) return

  try {
    const response = await api.get(`/rooms/${props.tipoHabitacionId}/occupied-dates`)
    if (response.data.success) {
      fechasOcupadas.value = response.data.fechas
      totalHabitaciones.value = response.data.total_habitaciones
    }
  } catch (error) {
    console.error('Error al cargar fechas ocupadas:', error)
  }
}

watch(() => props.tipoHabitacionId, () => {
  cargarFechasOcupadas()
}, { immediate: true })

onMounted(() => {
  cargarFechasOcupadas()
})
</script>

<style scoped>
.calendario-reservas {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.calendario-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.calendario-header button {
  background: #f0f0f0;
  border: none;
  border-radius: 8px;
  padding: 8px;
  cursor: pointer;
  transition: background 0.2s;
}

.calendario-header button:hover:not(:disabled) {
  background: #e0e0e0;
}

.calendario-header button:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.calendario-header h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
}

.calendario-dias-semana {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 8px;
  margin-bottom: 8px;
}

.dia-semana {
  text-align: center;
  font-weight: 600;
  font-size: 14px;
  color: #666;
  padding: 8px 0;
}

.calendario-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 8px;
}

.calendario-dia {
  aspect-ratio: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
  border: 2px solid transparent;
  background: #f9f9f9;
  font-weight: 500;
}

.calendario-dia.vacio {
  background: transparent;
  cursor: default;
}

.calendario-dia.pasado {
  opacity: 0.3;
  cursor: not-allowed;
}

.calendario-dia.hoy {
  border-color: #0066cc;
  font-weight: 700;
}

.calendario-dia.disponible {
  background: #10b981;
  color: white;
  font-weight: 600;
}

.calendario-dia.disponible:hover {
  background: #059669;
  transform: scale(1.05);
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}

.calendario-dia.ocupado {
  background: #ef4444;
  color: white;
  cursor: not-allowed;
  font-weight: 600;
}

.calendario-dia.ocupado:hover {
  background: #dc2626;
}

.calendario-dia.seleccionado-entrada,
.calendario-dia.seleccionado-salida {
  background: #0066cc;
  color: white;
  font-weight: 700;
}

.calendario-dia.en-rango {
  background: #bbdefb;
  color: #0066cc;
}

.calendario-leyenda {
  display: flex;
  gap: 16px;
  margin-top: 20px;
  flex-wrap: wrap;
}

.leyenda-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
}

.color-box {
  width: 20px;
  height: 20px;
  border-radius: 4px;
}

.color-box.disponible {
  background: #10b981;
}

.color-box.ocupado {
  background: #ef4444;
}
</style>
