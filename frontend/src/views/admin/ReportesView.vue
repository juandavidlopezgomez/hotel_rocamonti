<template>
  <div class="p-6">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900">Reportes y Análisis</h1>
      <p class="mt-2 text-sm text-gray-600">Genera reportes detallados del negocio</p>
    </div>

    <!-- Tabs -->
    <BaseTabs v-model="tabActivo" :tabs="tabs" class="mb-6">
      <!-- Reporte de Reservas -->
      <template #reservas>
        <div class="space-y-6">
          <!-- Filtros de fecha -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-white p-4 rounded-lg shadow">
            <BaseInput
              v-model="filtrosReservas.fecha_inicio"
              type="date"
              label="Fecha Inicio"
            />
            <BaseInput
              v-model="filtrosReservas.fecha_fin"
              type="date"
              label="Fecha Fin"
            />
            <div class="flex items-end gap-2">
              <BaseButton @click="generarReporteReservas" variant="primary" class="flex-1">
                Generar Reporte
              </BaseButton>
              <BaseButton @click="exportarPDF('reservas')" variant="ghost">
                PDF
              </BaseButton>
            </div>
          </div>

          <!-- Resultados -->
          <div v-if="reporteReservas" class="space-y-4">
            <!-- Resumen -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <BaseCard>
                <div class="text-center">
                  <p class="text-3xl font-bold">{{ reporteReservas.resumen?.total_reservas }}</p>
                  <p class="text-sm text-gray-600">Total Reservas</p>
                </div>
              </BaseCard>
              <BaseCard>
                <div class="text-center">
                  <p class="text-3xl font-bold text-green-600">${{ reporteReservas.resumen?.ingresos_totales?.toLocaleString() }}</p>
                  <p class="text-sm text-gray-600">Ingresos</p>
                </div>
              </BaseCard>
              <BaseCard>
                <div class="text-center">
                  <p class="text-3xl font-bold text-yellow-600">${{ reporteReservas.resumen?.ingresos_pendientes?.toLocaleString() }}</p>
                  <p class="text-sm text-gray-600">Pendientes</p>
                </div>
              </BaseCard>
              <BaseCard>
                <div class="text-center">
                  <p class="text-3xl font-bold text-red-600">{{ reporteReservas.resumen?.por_estado?.cancelada || 0 }}</p>
                  <p class="text-sm text-gray-600">Canceladas</p>
                </div>
              </BaseCard>
            </div>
          </div>
        </div>
      </template>

      <!-- Reporte de Ingresos -->
      <template #ingresos>
        <div class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-white p-4 rounded-lg shadow">
            <BaseInput
              v-model="filtrosIngresos.fecha_inicio"
              type="date"
              label="Fecha Inicio"
            />
            <BaseInput
              v-model="filtrosIngresos.fecha_fin"
              type="date"
              label="Fecha Fin"
            />
            <div class="flex items-end gap-2">
              <BaseButton @click="generarReporteIngresos" variant="primary" class="flex-1">
                Generar Reporte
              </BaseButton>
              <BaseButton @click="exportarPDF('ingresos')" variant="ghost">
                PDF
              </BaseButton>
            </div>
          </div>

          <div v-if="reporteIngresos" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <BaseCard>
                <div>
                  <p class="text-sm text-gray-600">Periodo Actual</p>
                  <p class="text-3xl font-bold text-green-600">${{ reporteIngresos.ingresos?.periodo_actual?.toLocaleString() }}</p>
                </div>
              </BaseCard>
              <BaseCard>
                <div>
                  <p class="text-sm text-gray-600">Periodo Anterior</p>
                  <p class="text-2xl font-medium">${{ reporteIngresos.ingresos?.periodo_anterior?.toLocaleString() }}</p>
                </div>
              </BaseCard>
              <BaseCard>
                <div>
                  <p class="text-sm text-gray-600">Variación</p>
                  <p :class="[
                    'text-3xl font-bold',
                    reporteIngresos.ingresos?.variacion_porcentual >= 0 ? 'text-green-600' : 'text-red-600'
                  ]">
                    {{ reporteIngresos.ingresos?.variacion_porcentual > 0 ? '+' : '' }}{{ reporteIngresos.ingresos?.variacion_porcentual }}%
                  </p>
                </div>
              </BaseCard>
            </div>
          </div>
        </div>
      </template>

      <!-- Reporte de Ocupación -->
      <template #ocupacion>
        <div class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-white p-4 rounded-lg shadow">
            <BaseInput
              v-model="filtrosOcupacion.fecha_inicio"
              type="date"
              label="Fecha Inicio"
            />
            <BaseInput
              v-model="filtrosOcupacion.fecha_fin"
              type="date"
              label="Fecha Fin"
            />
            <div class="flex items-end gap-2">
              <BaseButton @click="generarReporteOcupacion" variant="primary" class="flex-1">
                Generar Reporte
              </BaseButton>
              <BaseButton @click="exportarPDF('ocupacion')" variant="ghost">
                PDF
              </BaseButton>
            </div>
          </div>

          <div v-if="reporteOcupacion" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <BaseCard>
                <div class="text-center">
                  <p class="text-4xl font-bold text-blue-600">{{ reporteOcupacion.ocupacion?.tasa_ocupacion }}%</p>
                  <p class="text-sm text-gray-600">Tasa de Ocupación</p>
                </div>
              </BaseCard>
              <BaseCard>
                <div class="text-center">
                  <p class="text-3xl font-bold text-green-600">${{ reporteOcupacion.ocupacion?.revpar?.toLocaleString() }}</p>
                  <p class="text-sm text-gray-600">RevPAR</p>
                </div>
              </BaseCard>
              <BaseCard>
                <div class="text-center">
                  <p class="text-3xl font-bold">${{ reporteOcupacion.ocupacion?.adr?.toLocaleString() }}</p>
                  <p class="text-sm text-gray-600">ADR</p>
                </div>
              </BaseCard>
            </div>
          </div>
        </div>
      </template>

      <!-- Reporte de Clientes -->
      <template #clientes>
        <div class="space-y-6">
          <div class="flex justify-end gap-2">
            <BaseButton @click="generarReporteClientes" variant="primary">
              Generar Reporte
            </BaseButton>
            <BaseButton @click="exportarExcel('clientes')" variant="ghost">
              Excel
            </BaseButton>
          </div>

          <div v-if="reporteClientes" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <BaseCard>
                <div class="text-center">
                  <p class="text-3xl font-bold">{{ reporteClientes.clientes?.total }}</p>
                  <p class="text-sm text-gray-600">Total Clientes</p>
                </div>
              </BaseCard>
              <BaseCard>
                <div class="text-center">
                  <p class="text-3xl font-bold text-blue-600">{{ reporteClientes.clientes?.con_reservas }}</p>
                  <p class="text-sm text-gray-600">Con Reservas</p>
                </div>
              </BaseCard>
              <BaseCard>
                <div class="text-center">
                  <p class="text-3xl font-bold text-green-600">{{ reporteClientes.clientes?.nuevos_periodo }}</p>
                  <p class="text-sm text-gray-600">Nuevos</p>
                </div>
              </BaseCard>
            </div>
          </div>
        </div>
      </template>
    </BaseTabs>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useReportesStore } from '@/stores/reportes'
import BaseTabs from '@/components/ui/BaseTabs.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseCard from '@/components/ui/BaseCard.vue'

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
