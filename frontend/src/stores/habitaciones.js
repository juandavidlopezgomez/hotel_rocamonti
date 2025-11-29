import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

export const useHabitacionesStore = defineStore('habitaciones', () => {
  // State
  const habitaciones = ref([])
  const tiposHabitacion = ref([])
  const habitacionActual = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const filters = ref({
    search: '',
    estado: '',
    tipo: '',
    piso: null
  })

  // Getters
  const habitacionesFiltradas = computed(() => {
    let filtered = habitaciones.value

    if (filters.value.search) {
      const search = filters.value.search.toLowerCase()
      filtered = filtered.filter(h =>
        h.numero_habitacion?.toLowerCase().includes(search) ||
        h.tipo_nombre?.toLowerCase().includes(search)
      )
    }

    if (filters.value.estado) {
      filtered = filtered.filter(h => h.estado === filters.value.estado)
    }

    if (filters.value.tipo) {
      filtered = filtered.filter(h => h.tipo_habitacion_id === filters.value.tipo)
    }

    if (filters.value.piso) {
      filtered = filtered.filter(h => h.piso === filters.value.piso)
    }

    return filtered
  })

  const estadisticas = computed(() => {
    const total = habitaciones.value.length
    const disponibles = habitaciones.value.filter(h => h.estado === 'disponible').length
    const ocupadas = habitaciones.value.filter(h => h.estado === 'ocupada').length
    const mantenimiento = habitaciones.value.filter(h => h.estado === 'mantenimiento').length
    const limpieza = habitaciones.value.filter(h => h.estado === 'limpieza').length
    const bloqueadas = habitaciones.value.filter(h => h.estado === 'bloqueada').length

    return {
      total,
      disponibles,
      ocupadas,
      mantenimiento,
      limpieza,
      bloqueadas,
      tasaOcupacion: total > 0 ? Math.round((ocupadas / total) * 100) : 0
    }
  })

  const habitacionesPorPiso = computed(() => {
    return habitaciones.value.reduce((acc, h) => {
      const piso = h.piso || 1
      if (!acc[piso]) {
        acc[piso] = []
      }
      acc[piso].push(h)
      return acc
    }, {})
  })

  const habitacionesDisponibles = computed(() => {
    return habitaciones.value.filter(h => h.estado === 'disponible')
  })

  const habitacionesPorLimpiar = computed(() => {
    return habitaciones.value.filter(h => h.estado === 'limpieza')
  })

  // Actions
  async function fetchHabitaciones(params = {}) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.get(`${API_URL}/admin/habitaciones`, { params })

      if (response.data.success) {
        habitaciones.value = response.data.habitaciones || response.data.data || []
        return { success: true }
      } else {
        error.value = response.data.message || 'Error al cargar habitaciones'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function fetchTiposHabitacion() {
    try {
      const response = await axios.get(`${API_URL}/room-types`)

      if (response.data.success) {
        tiposHabitacion.value = response.data.tipos || response.data.data || []
        return { success: true }
      }
    } catch (err) {
      console.error('Error al cargar tipos de habitación:', err)
      return { success: false }
    }
  }

  async function fetchHabitacion(id) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.get(`${API_URL}/admin/habitaciones/${id}`)

      if (response.data.success) {
        habitacionActual.value = response.data.habitacion || response.data.data
        return { success: true, data: habitacionActual.value }
      } else {
        error.value = response.data.message || 'Error al cargar habitación'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function cambiarEstado(id, nuevoEstado, notas = null) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.put(`${API_URL}/admin/habitaciones/${id}/estado`, {
        estado: nuevoEstado,
        notas
      })

      if (response.data.success) {
        const index = habitaciones.value.findIndex(h => h.id === id)

        if (index !== -1) {
          habitaciones.value[index].estado = nuevoEstado
          if (notas) {
            habitaciones.value[index].notas_estado = notas
          }
        }

        if (habitacionActual.value?.id === id) {
          habitacionActual.value.estado = nuevoEstado
        }

        return { success: true }
      } else {
        error.value = response.data.message || 'Error al cambiar estado'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function crearHabitacion(data) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post(`${API_URL}/admin/habitaciones`, data)

      if (response.data.success) {
        const nuevaHabitacion = response.data.habitacion || response.data.data
        habitaciones.value.push(nuevaHabitacion)
        return { success: true, data: nuevaHabitacion }
      } else {
        error.value = response.data.message || 'Error al crear habitación'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.response?.data?.errors || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function actualizarHabitacion(id, data) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.put(`${API_URL}/admin/habitaciones/${id}`, data)

      if (response.data.success) {
        const habitacionActualizada = response.data.habitacion || response.data.data
        const index = habitaciones.value.findIndex(h => h.id === id)

        if (index !== -1) {
          habitaciones.value[index] = habitacionActualizada
        }

        if (habitacionActual.value?.id === id) {
          habitacionActual.value = habitacionActualizada
        }

        return { success: true, data: habitacionActualizada }
      } else {
        error.value = response.data.message || 'Error al actualizar habitación'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.response?.data?.errors || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function bloquearHabitacion(id, fechaInicio, fechaFin, motivo) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post(`${API_URL}/admin/habitaciones/${id}/bloquear`, {
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin,
        motivo
      })

      if (response.data.success) {
        const index = habitaciones.value.findIndex(h => h.id === id)

        if (index !== -1) {
          habitaciones.value[index].estado = 'bloqueada'
          habitaciones.value[index].bloqueo_activo = true
        }

        return { success: true }
      } else {
        error.value = response.data.message || 'Error al bloquear habitación'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function desbloquearHabitacion(id) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post(`${API_URL}/admin/habitaciones/${id}/desbloquear`)

      if (response.data.success) {
        const index = habitaciones.value.findIndex(h => h.id === id)

        if (index !== -1) {
          habitaciones.value[index].estado = 'disponible'
          habitaciones.value[index].bloqueo_activo = false
        }

        return { success: true }
      } else {
        error.value = response.data.message || 'Error al desbloquear habitación'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function registrarMantenimiento(id, data) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post(`${API_URL}/admin/habitaciones/${id}/mantenimiento`, data)

      if (response.data.success) {
        return { success: true, data: response.data.mantenimiento }
      } else {
        error.value = response.data.message || 'Error al registrar mantenimiento'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function actualizarPrecio(tipoId, data) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.put(`${API_URL}/admin/habitaciones/tipos/${tipoId}/precio`, data)

      if (response.data.success) {
        // Actualizar precio en tiposHabitacion
        const index = tiposHabitacion.value.findIndex(t => t.id === tipoId)
        if (index !== -1) {
          tiposHabitacion.value[index] = {
            ...tiposHabitacion.value[index],
            ...data
          }
        }

        return { success: true }
      } else {
        error.value = response.data.message || 'Error al actualizar precio'
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
  }

  function resetFilters() {
    filters.value = {
      search: '',
      estado: '',
      tipo: '',
      piso: null
    }
  }

  return {
    // State
    habitaciones,
    tiposHabitacion,
    habitacionActual,
    loading,
    error,
    filters,

    // Getters
    habitacionesFiltradas,
    estadisticas,
    habitacionesPorPiso,
    habitacionesDisponibles,
    habitacionesPorLimpiar,

    // Actions
    fetchHabitaciones,
    fetchTiposHabitacion,
    fetchHabitacion,
    cambiarEstado,
    crearHabitacion,
    actualizarHabitacion,
    bloquearHabitacion,
    desbloquearHabitacion,
    registrarMantenimiento,
    actualizarPrecio,
    setFilters,
    resetFilters
  }
})
