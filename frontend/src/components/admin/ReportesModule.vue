<template>
  <div class="module-container">
    <div class="module-header">
      <div>
        <h1 class="module-title">Generación de Reportes</h1>
        <p class="module-subtitle">Genera reportes detallados en formato PDF</p>
      </div>
    </div>

    <div class="reports-grid">
      <!-- Reporte de Reservas -->
      <div class="report-card">
        <div class="report-icon blue">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
          </svg>
        </div>
        <h3>Reporte de Reservas</h3>
        <p>Genera un reporte detallado de todas las reservas en un período específico</p>

        <div class="form-group">
          <label>Fecha Inicio</label>
          <input v-model="reportes.reservas.fecha_inicio" type="date" class="form-input" />
        </div>
        <div class="form-group">
          <label>Fecha Fin</label>
          <input v-model="reportes.reservas.fecha_fin" type="date" class="form-input" />
        </div>

        <button @click="generarReporte('reservas')" :disabled="loading.reservas" class="btn-generate">
          <svg v-if="!loading.reservas" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
          </svg>
          <span v-if="loading.reservas" class="loading-spinner small"></span>
          {{ loading.reservas ? 'Generando...' : 'Generar PDF' }}
        </button>
      </div>

      <!-- Reporte de Ingresos -->
      <div class="report-card">
        <div class="report-icon green">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="1" x2="12" y2="23"></line>
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
          </svg>
        </div>
        <h3>Reporte de Ingresos</h3>
        <p>Análisis completo de ingresos por tipo de habitación y período</p>

        <div class="form-group">
          <label>Fecha Inicio</label>
          <input v-model="reportes.ingresos.fecha_inicio" type="date" class="form-input" />
        </div>
        <div class="form-group">
          <label>Fecha Fin</label>
          <input v-model="reportes.ingresos.fecha_fin" type="date" class="form-input" />
        </div>

        <button @click="generarReporte('ingresos')" :disabled="loading.ingresos" class="btn-generate">
          <svg v-if="!loading.ingresos" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
          </svg>
          <span v-if="loading.ingresos" class="loading-spinner small"></span>
          {{ loading.ingresos ? 'Generando...' : 'Generar PDF' }}
        </button>
      </div>

      <!-- Reporte de Ocupación -->
      <div class="report-card">
        <div class="report-icon purple">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
            <polyline points="9 22 9 12 15 12 15 22"></polyline>
          </svg>
        </div>
        <h3>Reporte de Ocupación</h3>
        <p>Análisis de tasas de ocupación por día y tipo de habitación</p>

        <div class="form-group">
          <label>Fecha Inicio</label>
          <input v-model="reportes.ocupacion.fecha_inicio" type="date" class="form-input" />
        </div>
        <div class="form-group">
          <label>Fecha Fin</label>
          <input v-model="reportes.ocupacion.fecha_fin" type="date" class="form-input" />
        </div>

        <button @click="generarReporte('ocupacion')" :disabled="loading.ocupacion" class="btn-generate">
          <svg v-if="!loading.ocupacion" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
          </svg>
          <span v-if="loading.ocupacion" class="loading-spinner small"></span>
          {{ loading.ocupacion ? 'Generando...' : 'Generar PDF' }}
        </button>
      </div>

      <!-- Reporte de Clientes -->
      <div class="report-card">
        <div class="report-icon orange">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
          </svg>
        </div>
        <h3>Reporte de Clientes</h3>
        <p>Listado completo de clientes con estadísticas de reservas</p>

        <div class="info-box">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12.01" y2="8"></line>
          </svg>
          Este reporte incluye todos los clientes registrados
        </div>

        <button @click="generarReporte('clientes')" :disabled="loading.clientes" class="btn-generate">
          <svg v-if="!loading.clientes" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
          </svg>
          <span v-if="loading.clientes" class="loading-spinner small"></span>
          {{ loading.clientes ? 'Generando...' : 'Generar PDF' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

// Estado
const reportes = ref({
  reservas: {
    fecha_inicio: '',
    fecha_fin: ''
  },
  ingresos: {
    fecha_inicio: '',
    fecha_fin: ''
  },
  ocupacion: {
    fecha_inicio: '',
    fecha_fin: ''
  }
})

const loading = ref({
  reservas: false,
  ingresos: false,
  ocupacion: false,
  clientes: false
})

// Funciones
async function generarReporte(tipo) {
  loading.value[tipo] = true

  try {
    let url = `/admin/reportes/${tipo}`
    let params = { tipo: 'pdf' }

    if (tipo !== 'clientes') {
      const config = reportes.value[tipo]

      if (!config.fecha_inicio || !config.fecha_fin) {
        alert('Por favor selecciona ambas fechas')
        loading.value[tipo] = false
        return
      }

      params.fecha_inicio = config.fecha_inicio
      params.fecha_fin = config.fecha_fin
    }

    const response = await api.get(url, {
      params,
      responseType: 'blob'
    })

    // Crear link de descarga
    const blob = new Blob([response.data], { type: 'application/pdf' })
    const link = document.createElement('a')
    link.href = window.URL.createObjectURL(blob)
    link.download = `reporte_${tipo}_${new Date().getTime()}.pdf`
    link.click()

    alert('Reporte generado exitosamente')
  } catch (error) {
    console.error('Error al generar reporte:', error)
    alert('Error al generar el reporte')
  } finally {
    loading.value[tipo] = false
  }
}

// Lifecycle
onMounted(() => {
  // Establecer fechas por defecto (último mes)
  const hoy = new Date()
  const hace30Dias = new Date()
  hace30Dias.setDate(hoy.getDate() - 30)

  const fechaInicio = hace30Dias.toISOString().split('T')[0]
  const fechaFin = hoy.toISOString().split('T')[0]

  reportes.value.reservas.fecha_inicio = fechaInicio
  reportes.value.reservas.fecha_fin = fechaFin
  reportes.value.ingresos.fecha_inicio = fechaInicio
  reportes.value.ingresos.fecha_fin = fechaFin
  reportes.value.ocupacion.fecha_inicio = fechaInicio
  reportes.value.ocupacion.fecha_fin = fechaFin
})
</script>

<style scoped>
/* Los estilos se comparten con el archivo global */
</style>
