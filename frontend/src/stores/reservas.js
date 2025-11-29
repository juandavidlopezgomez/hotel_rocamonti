import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

export const useReservasStore = defineStore('reservas', () => {
  // State
  const reservas = ref([])
  const reservaActual = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const filters = ref({
    search: '',
    estado: '',
    fechaInicio: null,
    fechaFin: null,
    habitacion: null
  })
  const pagination = ref({
    currentPage: 1,
    perPage: 10,
    total: 0
  })

  // Getters
  const reservasFiltradas = computed(() => {
    let filtered = reservas.value

    // Filtro por búsqueda
    if (filters.value.search) {
      const search = filters.value.search.toLowerCase()
      filtered = filtered.filter(r =>
        r.cliente_nombre?.toLowerCase().includes(search) ||
        r.cliente_cedula?.includes(search) ||
        r.habitacion?.toLowerCase().includes(search) ||
        r.id?.toString().includes(search)
      )
    }

    // Filtro por estado
    if (filters.value.estado) {
      filtered = filtered.filter(r => r.estado === filters.value.estado)
    }

    // Filtro por fecha
    if (filters.value.fechaInicio) {
      filtered = filtered.filter(r => new Date(r.fecha_entrada) >= new Date(filters.value.fechaInicio))
    }

    if (filters.value.fechaFin) {
      filtered = filtered.filter(r => new Date(r.fecha_salida) <= new Date(filters.value.fechaFin))
    }

    // Filtro por habitación
    if (filters.value.habitacion) {
      filtered = filtered.filter(r => r.habitacion_id === filters.value.habitacion)
    }

    return filtered
  })

  const reservasPaginadas = computed(() => {
    const start = (pagination.value.currentPage - 1) * pagination.value.perPage
    const end = start + pagination.value.perPage
    return reservasFiltradas.value.slice(start, end)
  })

  const estadisticas = computed(() => {
    return {
      total: reservas.value.length,
      pendientes: reservas.value.filter(r => r.estado === 'pendiente').length,
      pagadas: reservas.value.filter(r => r.estado === 'pagada').length,
      enCurso: reservas.value.filter(r => r.estado === 'en_curso').length,
      completadas: reservas.value.filter(r => r.estado === 'completada').length,
      canceladas: reservas.value.filter(r => r.estado === 'cancelada').length,
      ingresosTotales: reservas.value
        .filter(r => r.estado === 'pagada' || r.estado === 'completada')
        .reduce((sum, r) => sum + parseFloat(r.precio_total || 0), 0)
    }
  })

  const proximasLlegadas = computed(() => {
    const hoy = new Date()
    hoy.setHours(0, 0, 0, 0)

    return reservas.value
      .filter(r => {
        const entrada = new Date(r.fecha_entrada)
        entrada.setHours(0, 0, 0, 0)
        return entrada.getTime() === hoy.getTime() && r.estado !== 'cancelada'
      })
      .sort((a, b) => new Date(a.fecha_entrada) - new Date(b.fecha_entrada))
  })

  const proximasSalidas = computed(() => {
    const hoy = new Date()
    hoy.setHours(0, 0, 0, 0)

    return reservas.value
      .filter(r => {
        const salida = new Date(r.fecha_salida)
        salida.setHours(0, 0, 0, 0)
        return salida.getTime() === hoy.getTime() && r.estado !== 'cancelada'
      })
      .sort((a, b) => new Date(a.fecha_salida) - new Date(b.fecha_salida))
  })

  // Actions
  async function fetchReservas(params = {}) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.get(`${API_URL}/admin/reservas`, { params })

      if (response.data.success) {
        reservas.value = response.data.reservas || response.data.data || []

        if (response.data.pagination) {
          pagination.value = {
            ...pagination.value,
            ...response.data.pagination
          }
        }

        return { success: true }
      } else {
        error.value = response.data.message || 'Error al cargar reservas'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function fetchReserva(id) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.get(`${API_URL}/admin/reservas/${id}`)

      if (response.data.success) {
        reservaActual.value = response.data.reserva || response.data.data
        return { success: true, data: reservaActual.value }
      } else {
        error.value = response.data.message || 'Error al cargar reserva'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function crearReserva(data) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post(`${API_URL}/admin/reservas`, data)

      if (response.data.success) {
        const nuevaReserva = response.data.reserva || response.data.data
        reservas.value.unshift(nuevaReserva)
        return { success: true, data: nuevaReserva }
      } else {
        error.value = response.data.message || 'Error al crear reserva'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.response?.data?.errors || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function actualizarReserva(id, data) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.put(`${API_URL}/admin/reservas/${id}`, data)

      if (response.data.success) {
        const reservaActualizada = response.data.reserva || response.data.data
        const index = reservas.value.findIndex(r => r.id === id)

        if (index !== -1) {
          reservas.value[index] = reservaActualizada
        }

        if (reservaActual.value?.id === id) {
          reservaActual.value = reservaActualizada
        }

        return { success: true, data: reservaActualizada }
      } else {
        error.value = response.data.message || 'Error al actualizar reserva'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.response?.data?.errors || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function cancelarReserva(id, motivo = null) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post(`${API_URL}/admin/reservas/${id}/cancelar`, { motivo })

      if (response.data.success) {
        const index = reservas.value.findIndex(r => r.id === id)

        if (index !== -1) {
          reservas.value[index].estado = 'cancelada'
        }

        if (reservaActual.value?.id === id) {
          reservaActual.value.estado = 'cancelada'
        }

        return { success: true }
      } else {
        error.value = response.data.message || 'Error al cancelar reserva'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function checkIn(id) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post(`${API_URL}/admin/reservas/${id}/checkin`)

      if (response.data.success) {
        const index = reservas.value.findIndex(r => r.id === id)

        if (index !== -1) {
          reservas.value[index].estado = 'en_curso'
          reservas.value[index].checkin_at = new Date().toISOString()
        }

        return { success: true }
      } else {
        error.value = response.data.message || 'Error al hacer check-in'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function checkOut(id, consumosAdicionales = 0) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post(`${API_URL}/admin/reservas/${id}/checkout`, {
        consumos_adicionales: consumosAdicionales
      })

      if (response.data.success) {
        const index = reservas.value.findIndex(r => r.id === id)

        if (index !== -1) {
          reservas.value[index].estado = 'completada'
          reservas.value[index].checkout_at = new Date().toISOString()
          reservas.value[index].consumos_adicionales = consumosAdicionales
        }

        return { success: true, data: response.data }
      } else {
        error.value = response.data.message || 'Error al hacer check-out'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  function setFilters(newFilters) {
    filters.value = { ...filters.value, ...newFilters }
    pagination.value.currentPage = 1
  }

  function resetFilters() {
    filters.value = {
      search: '',
      estado: '',
      fechaInicio: null,
      fechaFin: null,
      habitacion: null
    }
    pagination.value.currentPage = 1
  }

  function setPage(page) {
    pagination.value.currentPage = page
  }

  function setPerPage(perPage) {
    pagination.value.perPage = perPage
    pagination.value.currentPage = 1
  }

  return {
    // State
    reservas,
    reservaActual,
    loading,
    error,
    filters,
    pagination,

    // Getters
    reservasFiltradas,
    reservasPaginadas,
    estadisticas,
    proximasLlegadas,
    proximasSalidas,

    // Actions
    fetchReservas,
    fetchReserva,
    crearReserva,
    actualizarReserva,
    cancelarReserva,
    checkIn,
    checkOut,
    setFilters,
    resetFilters,
    setPage,
    setPerPage
  }
})
