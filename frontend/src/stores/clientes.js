import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

export const useClientesStore = defineStore('clientes', () => {
  // State
  const clientes = ref([])
  const clienteActual = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const filters = ref({
    search: '',
    tipo: '', // 'normal', 'vip'
    ordenar: 'nombre' // 'nombre', 'reservas', 'gasto'
  })
  const pagination = ref({
    currentPage: 1,
    perPage: 20,
    total: 0
  })

  // Getters
  const clientesFiltrados = computed(() => {
    let filtered = clientes.value

    // Filtro por búsqueda
    if (filters.value.search) {
      const search = filters.value.search.toLowerCase()
      filtered = filtered.filter(c =>
        c.nombre?.toLowerCase().includes(search) ||
        c.apellido?.toLowerCase().includes(search) ||
        c.cedula?.includes(search) ||
        c.email?.toLowerCase().includes(search) ||
        c.telefono?.includes(search)
      )
    }

    // Filtro por tipo
    if (filters.value.tipo === 'vip') {
      filtered = filtered.filter(c => c.es_vip)
    } else if (filters.value.tipo === 'normal') {
      filtered = filtered.filter(c => !c.es_vip)
    }

    // Ordenar
    filtered = [...filtered].sort((a, b) => {
      switch (filters.value.ordenar) {
        case 'nombre':
          return (a.nombre || '').localeCompare(b.nombre || '')
        case 'reservas':
          return (b.total_reservas || 0) - (a.total_reservas || 0)
        case 'gasto':
          return (b.total_gastado || 0) - (a.total_gastado || 0)
        default:
          return 0
      }
    })

    return filtered
  })

  const clientesPaginados = computed(() => {
    const start = (pagination.value.currentPage - 1) * pagination.value.perPage
    const end = start + pagination.value.perPage
    return clientesFiltrados.value.slice(start, end)
  })

  const estadisticas = computed(() => {
    const total = clientes.value.length
    const vip = clientes.value.filter(c => c.es_vip).length
    const normales = total - vip
    const tasaVIP = total > 0 ? Math.round((vip / total) * 100) : 0

    return {
      total,
      vip,
      normales,
      tasaVIP,
      totalGastado: clientes.value.reduce((sum, c) => sum + (parseFloat(c.total_gastado) || 0), 0),
      totalReservas: clientes.value.reduce((sum, c) => sum + (parseInt(c.total_reservas) || 0), 0)
    }
  })

  const clientesVIP = computed(() => {
    return clientes.value.filter(c => c.es_vip)
  })

  const clientesRecientes = computed(() => {
    return [...clientes.value]
      .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
      .slice(0, 10)
  })

  const topClientes = computed(() => {
    return [...clientes.value]
      .sort((a, b) => (b.total_gastado || 0) - (a.total_gastado || 0))
      .slice(0, 10)
  })

  // Actions
  async function fetchClientes(params = {}) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.get(`${API_URL}/admin/clientes`, { params })

      if (response.data.success) {
        clientes.value = response.data.clientes || response.data.data || []

        if (response.data.pagination) {
          pagination.value = {
            ...pagination.value,
            ...response.data.pagination
          }
        }

        return { success: true }
      } else {
        error.value = response.data.message || 'Error al cargar clientes'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function fetchCliente(cedula) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.get(`${API_URL}/admin/clientes/${cedula}`)

      if (response.data.success) {
        clienteActual.value = response.data.cliente || response.data.data
        return { success: true, data: clienteActual.value }
      } else {
        error.value = response.data.message || 'Error al cargar cliente'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function fetchHistorialCliente(cedula) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.get(`${API_URL}/admin/clientes/${cedula}/historial`)

      if (response.data.success) {
        return { success: true, data: response.data.historial || [] }
      } else {
        error.value = response.data.message || 'Error al cargar historial'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function crearCliente(data) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post(`${API_URL}/admin/clientes`, data)

      if (response.data.success) {
        const nuevoCliente = response.data.cliente || response.data.data
        clientes.value.unshift(nuevoCliente)
        return { success: true, data: nuevoCliente }
      } else {
        error.value = response.data.message || 'Error al crear cliente'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.response?.data?.errors || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function actualizarCliente(cedula, data) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.put(`${API_URL}/admin/clientes/${cedula}`, data)

      if (response.data.success) {
        const clienteActualizado = response.data.cliente || response.data.data
        const index = clientes.value.findIndex(c => c.cedula === cedula)

        if (index !== -1) {
          clientes.value[index] = clienteActualizado
        }

        if (clienteActual.value?.cedula === cedula) {
          clienteActual.value = clienteActualizado
        }

        return { success: true, data: clienteActualizado }
      } else {
        error.value = response.data.message || 'Error al actualizar cliente'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.response?.data?.errors || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function actualizarPreferencias(cedula, preferencias) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.put(`${API_URL}/admin/clientes/${cedula}/preferencias`, {
        preferencias
      })

      if (response.data.success) {
        const index = clientes.value.findIndex(c => c.cedula === cedula)

        if (index !== -1) {
          clientes.value[index].preferencias = preferencias
        }

        if (clienteActual.value?.cedula === cedula) {
          clienteActual.value.preferencias = preferencias
        }

        return { success: true }
      } else {
        error.value = response.data.message || 'Error al actualizar preferencias'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function marcarComoVIP(cedula) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post(`${API_URL}/admin/clientes/${cedula}/vip`)

      if (response.data.success) {
        const index = clientes.value.findIndex(c => c.cedula === cedula)

        if (index !== -1) {
          clientes.value[index].es_vip = true
        }

        if (clienteActual.value?.cedula === cedula) {
          clienteActual.value.es_vip = true
        }

        return { success: true }
      } else {
        error.value = response.data.message || 'Error al marcar como VIP'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function eliminarVIP(cedula) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.delete(`${API_URL}/admin/clientes/${cedula}/vip`)

      if (response.data.success) {
        const index = clientes.value.findIndex(c => c.cedula === cedula)

        if (index !== -1) {
          clientes.value[index].es_vip = false
        }

        if (clienteActual.value?.cedula === cedula) {
          clienteActual.value.es_vip = false
        }

        return { success: true }
      } else {
        error.value = response.data.message || 'Error al eliminar VIP'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function agregarNota(cedula, nota) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post(`${API_URL}/admin/clientes/${cedula}/notas`, { nota })

      if (response.data.success) {
        return { success: true, data: response.data.nota }
      } else {
        error.value = response.data.message || 'Error al agregar nota'
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
      tipo: '',
      ordenar: 'nombre'
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
    clientes,
    clienteActual,
    loading,
    error,
    filters,
    pagination,

    // Getters
    clientesFiltrados,
    clientesPaginados,
    estadisticas,
    clientesVIP,
    clientesRecientes,
    topClientes,

    // Actions
    fetchClientes,
    fetchCliente,
    fetchHistorialCliente,
    crearCliente,
    actualizarCliente,
    actualizarPreferencias,
    marcarComoVIP,
    eliminarVIP,
    agregarNota,
    setFilters,
    resetFilters,
    setPage,
    setPerPage
  }
})
