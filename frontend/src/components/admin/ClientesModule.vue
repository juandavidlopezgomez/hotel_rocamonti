<template>
  <div class="module-container">
    <div class="module-header">
      <div>
        <h1 class="module-title">Gestión de Clientes</h1>
        <p class="module-subtitle">Administra y visualiza la información de tus huéspedes</p>
      </div>
      <button @click="cargarClientes" class="btn-refresh">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="23 4 23 10 17 10"></polyline>
          <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
        </svg>
        Actualizar
      </button>
    </div>

    <!-- Estadísticas -->
    <div class="stats-grid">
      <div class="stat-card blue">
        <div class="stat-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
          </svg>
        </div>
        <div class="stat-content">
          <span class="stat-label">Total Clientes</span>
          <span class="stat-value">{{ clientes.length }}</span>
        </div>
      </div>

      <div class="stat-card green">
        <div class="stat-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
          </svg>
        </div>
        <div class="stat-content">
          <span class="stat-label">Con Reservas</span>
          <span class="stat-value">{{ clientesConReservas }}</span>
        </div>
      </div>

      <div class="stat-card purple">
        <div class="stat-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
          </svg>
        </div>
        <div class="stat-content">
          <span class="stat-label">Ingresos Totales</span>
          <span class="stat-value">${{ totalIngresos.toLocaleString('es-CO') }}</span>
        </div>
      </div>

      <div class="stat-card orange">
        <div class="stat-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
          </svg>
        </div>
        <div class="stat-content">
          <span class="stat-label">Cliente VIP</span>
          <span class="stat-value" v-if="topCliente">{{ topCliente.nombre }}</span>
        </div>
      </div>
    </div>

    <!-- Buscador -->
    <div class="search-bar">
      <div class="search-box large">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"></circle>
          <path d="m21 21-4.35-4.35"></path>
        </svg>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Buscar por nombre, cédula, email o teléfono..."
        />
      </div>
    </div>

    <!-- Tabla de clientes -->
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Cliente</th>
            <th>Cédula</th>
            <th>Email</th>
            <th>Teléfono</th>
            <th>Reservas</th>
            <th>Total Gastado</th>
            <th>Última Reserva</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="8" class="text-center">
              <div class="loading-spinner"></div>
              Cargando clientes...
            </td>
          </tr>
          <tr v-else-if="clientesFiltrados.length === 0">
            <td colspan="8" class="text-center">No se encontraron clientes</td>
          </tr>
          <tr v-else v-for="cliente in clientesFiltrados" :key="cliente.cedula" class="hover-row">
            <td>
              <div class="client-info">
                <div class="client-avatar">
                  {{ cliente.nombre.charAt(0).toUpperCase() }}
                </div>
                <div class="client-details">
                  <div class="client-name">{{ cliente.nombre }} {{ cliente.apellido }}</div>
                  <div class="client-since">Cliente desde {{ formatDate(cliente.fecha_registro) }}</div>
                </div>
              </div>
            </td>
            <td>{{ cliente.cedula }}</td>
            <td>
              <a :href="`mailto:${cliente.email}`" class="email-link">{{ cliente.email }}</a>
            </td>
            <td>{{ cliente.telefono }}</td>
            <td>
              <span class="badge badge-info">{{ cliente.total_reservas }}</span>
            </td>
            <td class="price">${{ Number(cliente.total_gastado).toLocaleString('es-CO') }}</td>
            <td>
              <div v-if="cliente.ultima_reserva" class="last-reservation">
                {{ formatDate(cliente.ultima_reserva.fecha_entrada) }}
              </div>
              <span v-else class="text-muted">Sin reservas</span>
            </td>
            <td>
              <div class="action-buttons">
                <button @click="verDetalles(cliente)" class="btn-action btn-view" title="Ver detalles">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                  </svg>
                </button>
                <button @click="editarCliente(cliente)" class="btn-action btn-edit" title="Editar">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal de detalles -->
    <div v-if="modalDetalles" class="modal-overlay" @click="cerrarModalDetalles">
      <div class="modal-content modal-large" @click.stop>
        <div class="modal-header">
          <h2>Perfil del Cliente</h2>
          <button @click="cerrarModalDetalles" class="btn-close">×</button>
        </div>
        <div class="modal-body" v-if="clienteSeleccionado">
          <div class="client-profile">
            <div class="profile-header">
              <div class="profile-avatar">
                {{ clienteSeleccionado.nombre.charAt(0).toUpperCase() }}
              </div>
              <div class="profile-info">
                <h3>{{ clienteSeleccionado.nombre }} {{ clienteSeleccionado.apellido }}</h3>
                <p>{{ clienteSeleccionado.email }}</p>
                <p>{{ clienteSeleccionado.telefono }}</p>
              </div>
              <div class="profile-stats">
                <div class="profile-stat">
                  <span class="stat-value">{{ clienteSeleccionado.total_reservas }}</span>
                  <span class="stat-label">Reservas</span>
                </div>
                <div class="profile-stat">
                  <span class="stat-value">${{ Number(clienteSeleccionado.total_gastado).toLocaleString('es-CO') }}</span>
                  <span class="stat-label">Total Gastado</span>
                </div>
              </div>
            </div>

            <div class="reservations-history" v-if="clienteSeleccionado.reservas && clienteSeleccionado.reservas.length > 0">
              <h4>Historial de Reservas</h4>
              <div class="timeline">
                <div v-for="reserva in clienteSeleccionado.reservas" :key="reserva.id" class="timeline-item">
                  <div class="timeline-marker"></div>
                  <div class="timeline-content">
                    <div class="reservation-card">
                      <div class="reservation-header">
                        <span class="reservation-type">{{ reserva.tipo_habitacion }}</span>
                        <span :class="['badge', `badge-${reserva.estado}`]">{{ reserva.estado }}</span>
                      </div>
                      <div class="reservation-details">
                        <div class="detail-item">
                          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                          </svg>
                          {{ formatDate(reserva.fecha_entrada) }} - {{ formatDate(reserva.fecha_salida) }}
                        </div>
                        <div class="detail-item">
                          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                          </svg>
                          {{ reserva.num_adultos }} adultos, {{ reserva.num_ninos }} niños
                        </div>
                        <div class="detail-item price">
                          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                          </svg>
                          ${{ Number(reserva.precio_total).toLocaleString('es-CO') }}
                        </div>
                      </div>
                      <div v-if="reserva.servicios && reserva.servicios.length > 0" class="reservation-services">
                        <span v-for="servicio in reserva.servicios" :key="servicio" class="service-tag">
                          {{ servicio }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="empty-state">
              <p>Este cliente aún no tiene reservas registradas</p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="cerrarModalDetalles" class="btn-primary">Cerrar</button>
        </div>
      </div>
    </div>

    <!-- Modal de edición -->
    <div v-if="modalEditar" class="modal-overlay" @click="cerrarModalEditar">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>Editar Cliente</h2>
          <button @click="cerrarModalEditar" class="btn-close">×</button>
        </div>
        <div class="modal-body" v-if="clienteEditando">
          <div class="form-grid">
            <div class="form-group">
              <label>Nombre</label>
              <input v-model="clienteEditando.nombre" type="text" class="form-input" />
            </div>
            <div class="form-group">
              <label>Apellido</label>
              <input v-model="clienteEditando.apellido" type="text" class="form-input" />
            </div>
            <div class="form-group">
              <label>Email</label>
              <input v-model="clienteEditando.email" type="email" class="form-input" />
            </div>
            <div class="form-group">
              <label>Teléfono</label>
              <input v-model="clienteEditando.telefono" type="text" class="form-input" />
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="cerrarModalEditar" class="btn-secondary">Cancelar</button>
          <button @click="guardarCliente" class="btn-primary">Guardar Cambios</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'

// Estado
const clientes = ref([])
const loading = ref(false)
const searchQuery = ref('')
const modalDetalles = ref(false)
const modalEditar = ref(false)
const clienteSeleccionado = ref(null)
const clienteEditando = ref(null)

// Computed
const clientesFiltrados = computed(() => {
  if (!searchQuery.value) return clientes.value

  const query = searchQuery.value.toLowerCase()
  return clientes.value.filter(c =>
    c.nombre.toLowerCase().includes(query) ||
    c.apellido.toLowerCase().includes(query) ||
    c.cedula.includes(query) ||
    c.email.toLowerCase().includes(query) ||
    c.telefono.includes(query)
  )
})

const clientesConReservas = computed(() =>
  clientes.value.filter(c => c.total_reservas > 0).length
)

const totalIngresos = computed(() =>
  clientes.value.reduce((sum, c) => sum + Number(c.total_gastado), 0)
)

const topCliente = computed(() => {
  if (clientes.value.length === 0) return null
  return [...clientes.value].sort((a, b) => b.total_gastado - a.total_gastado)[0]
})

// Funciones
async function cargarClientes() {
  loading.value = true
  try {
    const response = await api.get('/admin/clientes')
    if (response.data.success) {
      clientes.value = response.data.clientes
    }
  } catch (error) {
    console.error('Error al cargar clientes:', error)
    alert('Error al cargar los clientes')
  } finally {
    loading.value = false
  }
}

async function verDetalles(cliente) {
  try {
    const response = await api.get(`/admin/clientes/${cliente.cedula}`)
    if (response.data.success) {
      clienteSeleccionado.value = response.data.cliente
      modalDetalles.value = true
    }
  } catch (error) {
    console.error('Error al cargar detalles:', error)
    alert('Error al cargar los detalles del cliente')
  }
}

function cerrarModalDetalles() {
  modalDetalles.value = false
  clienteSeleccionado.value = null
}

function editarCliente(cliente) {
  clienteEditando.value = { ...cliente }
  modalEditar.value = true
}

function cerrarModalEditar() {
  modalEditar.value = false
  clienteEditando.value = null
}

async function guardarCliente() {
  try {
    const response = await api.put(`/admin/clientes/${clienteEditando.value.cedula}`, {
      nombre: clienteEditando.value.nombre,
      apellido: clienteEditando.value.apellido,
      email: clienteEditando.value.email,
      telefono: clienteEditando.value.telefono
    })

    if (response.data.success) {
      alert('Cliente actualizado correctamente')
      cerrarModalEditar()
      await cargarClientes()
    }
  } catch (error) {
    console.error('Error al actualizar cliente:', error)
    alert('Error al actualizar el cliente')
  }
}

function formatDate(date) {
  if (!date) return ''
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Lifecycle
onMounted(() => {
  cargarClientes()
})
</script>

<style scoped>
/* Los estilos se comparten con el archivo global */
</style>
