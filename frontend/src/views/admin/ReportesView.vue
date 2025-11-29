<template>
  <div class="reportes-container">
    <!-- Header -->
    <div class="dashboard-header">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Reportes y Análisis</h1>
        <p class="dashboard-subtitle">Genera reportes detallados del negocio.</p>
      </div>
      <div class="date-badge">{{ new Date().toLocaleDateString('es-ES', { day: '2-digit', month: 'long', year: 'numeric' }) }}</div>
    </div>

    <!-- Tabs -->
    <div class="mb-6">
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            @click="tabActivo = tab.key"
            :class="[
              tab.key === tabActivo
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
            ]"
          >
            {{ tab.label }}
          </button>
        </nav>
      </div>
    </div>

    <!-- Contenido de los tabs -->
    <div class="mt-6">
      <!-- Reporte de Reservas -->
      <div v-show="tabActivo === 'reservas'">
        <div class="reportes-filters">
          <div class="filter-grid">
            <div class="filter-item">
              <label for="reservas-inicio">Fecha Inicio</label>
              <input v-model="filtrosReservas.fecha_inicio" type="date" id="reservas-inicio" class="w-full p-2 border rounded-md">
            </div>
            <div class="filter-item">
              <label for="reservas-fin">Fecha Fin</label>
              <input v-model="filtrosReservas.fecha_fin" type="date" id="reservas-fin" class="w-full p-2 border rounded-md">
            </div>
            <div class="filter-buttons">
              <button @click="generarReporteReservas" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                Generar Reporte
              </button>
              <button @click="exportarPDF('reservas')" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300">
                PDF
              </button>
            </div>
          </div>
        </div>

        <div v-if="reporteReservas" class="stats-grid-modern mt-6">
            <div class="stat-card-modern stat-primary">
                <div class="stat-header">
                    <span class="stat-label">Total Reservas</span>
                </div>
                <p class="stat-value">{{ reporteReservas.resumen?.total_reservas }}</p>
            </div>
            <div class="stat-card-modern stat-success">
                <div class="stat-header">
                    <span class="stat-label">Ingresos</span>
                </div>
                <p class="stat-value">${{ reporteReservas.resumen?.ingresos_totales?.toLocaleString() }}</p>
            </div>
            <div class="stat-card-modern stat-warning">
                <div class="stat-header">
                    <span class="stat-label">Pendientes</span>
                </div>
                <p class="stat-value">${{ reporteReservas.resumen?.ingresos_pendientes?.toLocaleString() }}</p>
            </div>
            <div class="stat-card-modern stat-danger">
                <div class="stat-header">
                    <span class="stat-label">Canceladas</span>
                </div>
                <p class="stat-value">{{ reporteReservas.resumen?.por_estado?.cancelada || 0 }}</p>
            </div>
        </div>
      </div>

      <!-- El resto de los tabs irían aquí, siguiendo un patrón similar -->

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useReportesStore } from '@/stores/reportes'

const reportesStore = useReportesStore()

const tabActivo = ref('reservas')
const tabs = [
  { key: 'reservas', label: 'Reservas' },
  { key: 'ingresos', label: 'Ingresos' },
  { key: 'ocupacion', label: 'Ocupación' },
  { key: 'clientes', label: 'Clientes' }
]

const filtrosReservas = ref({ fecha_inicio: '', fecha_fin: '' })
const filtrosIngresos = ref({ fecha_inicio: '', fecha_fin: '' })
const filtrosOcupacion = ref({ fecha_inicio: '', fecha_fin: '' })

const reporteReservas = ref(null)
const reporteIngresos = ref(null)
const reporteOcupacion = ref(null)
const reporteClientes = ref(null)

async function generarReporteReservas() {
  reporteReservas.value = await reportesStore.generarReporteReservas(filtrosReservas.value)
}

async function generarReporteIngresos() {
  reporteIngresos.value = await reportesStore.generarReporteIngresos(filtrosIngresos.value)
}

async function generarReporteOcupacion() {
  reporteOcupacion.value = await reportesStore.generarReporteOcupacion(filtrosOcupacion.value)
}

async function generarReporteClientes() {
  reporteClientes.value = await reportesStore.generarReporteClientes()
}

async function exportarPDF(tipo) {
  await reportesStore.exportarPDF(tipo, getFiltros(tipo))
}

async function exportarExcel(tipo) {
  await reportesStore.exportarExcel(tipo)
}

function getFiltros(tipo) {
  const filtrosMap = {
    reservas: filtrosReservas.value,
    ingresos: filtrosIngresos.value,
    ocupacion: filtrosOcupacion.value
  }
  return filtrosMap[tipo] || {}
}
</script>

<style scoped>
@import './dashboard-styles.css';

.stat-card-modern.stat-danger {
  border-left-color: #ef4444;
}
</style>