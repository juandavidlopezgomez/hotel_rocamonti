import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

export const useReportesStore = defineStore('reportes', () => {
  // State
  const loading = ref(false)
  const error = ref(null)
  const reporteActual = ref(null)

  // Actions
  async function generarReporteReservas(params) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post(`${API_URL}/admin/reportes/reservas`, params)

      if (response.data.success) {
        reporteActual.value = response.data.reporte
        return { success: true, data: response.data.reporte }
      } else {
        error.value = response.data.message || 'Error al generar reporte'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function generarReporteIngresos(params) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post(`${API_URL}/admin/reportes/ingresos`, params)

      if (response.data.success) {
        reporteActual.value = response.data.reporte
        return { success: true, data: response.data.reporte }
      } else {
        error.value = response.data.message || 'Error al generar reporte'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function generarReporteOcupacion(params) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post(`${API_URL}/admin/reportes/ocupacion`, params)

      if (response.data.success) {
        reporteActual.value = response.data.reporte
        return { success: true, data: response.data.reporte }
      } else {
        error.value = response.data.message || 'Error al generar reporte'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function generarReporteClientes(params) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post(`${API_URL}/admin/reportes/clientes`, params)

      if (response.data.success) {
        reporteActual.value = response.data.reporte
        return { success: true, data: response.data.reporte }
      } else {
        error.value = response.data.message || 'Error al generar reporte'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function exportarPDF(tipo, params) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post(
        `${API_URL}/admin/reportes/${tipo}/pdf`,
        params,
        { responseType: 'blob' }
      )

      // Crear un blob URL y descargar el archivo
      const blob = new Blob([response.data], { type: 'application/pdf' })
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', `reporte_${tipo}_${new Date().getTime()}.pdf`)
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      window.URL.revokeObjectURL(url)

      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al exportar PDF'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function exportarExcel(tipo, params) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post(
        `${API_URL}/admin/reportes/${tipo}/excel`,
        params,
        { responseType: 'blob' }
      )

      // Crear un blob URL y descargar el archivo
      const blob = new Blob([response.data], {
        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      })
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', `reporte_${tipo}_${new Date().getTime()}.xlsx`)
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      window.URL.revokeObjectURL(url)

      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al exportar Excel'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function obtenerEstadisticasDashboard(periodo = '30days') {
    loading.value = true
    error.value = null

    try {
      const response = await axios.get(`${API_URL}/admin/stats`, {
        params: { periodo }
      })

      if (response.data.success) {
        return { success: true, data: response.data.data }
      } else {
        error.value = response.data.message || 'Error al obtener estadísticas'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function obtenerOcupacionSemanal() {
    try {
      const response = await axios.get(`${API_URL}/admin/ocupacion-semanal`)

      if (response.data.success) {
        return { success: true, data: response.data.data }
      }
    } catch (err) {
      console.error('Error al obtener ocupación semanal:', err)
      return { success: false }
    }
  }

  async function obtenerIngresosMensuales(meses = 6) {
    try {
      const response = await axios.get(`${API_URL}/admin/ingresos-mensuales`, {
        params: { meses }
      })

      if (response.data.success) {
        return { success: true, data: response.data.data }
      }
    } catch (err) {
      console.error('Error al obtener ingresos mensuales:', err)
      return { success: false }
    }
  }

  async function obtenerTopHabitaciones(limit = 5) {
    try {
      const response = await axios.get(`${API_URL}/admin/top-habitaciones`, {
        params: { limit }
      })

      if (response.data.success) {
        return { success: true, data: response.data.data }
      }
    } catch (err) {
      console.error('Error al obtener top habitaciones:', err)
      return { success: false }
    }
  }

  return {
    // State
    loading,
    error,
    reporteActual,

    // Actions
    generarReporteReservas,
    generarReporteIngresos,
    generarReporteOcupacion,
    generarReporteClientes,
    exportarPDF,
    exportarExcel,
    obtenerEstadisticasDashboard,
    obtenerOcupacionSemanal,
    obtenerIngresosMensuales,
    obtenerTopHabitaciones
  }
})
