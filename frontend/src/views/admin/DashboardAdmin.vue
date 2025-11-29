<template>
  <div class="dashboard-admin">
    <nav class="admin-navbar">
      <div class="nav-brand">
        <h1>🏨 Hotel Rocamonti - Admin</h1>
      </div>
      <div class="nav-user">
        <span>Hola, {{ adminUser }}</span>
        <button @click="handleLogout" class="btn-logout">Cerrar Sesión</button>
      </div>
    </nav>

    <div class="dashboard-container">
      <aside class="sidebar">
        <nav class="sidebar-nav">
          <button
            v-for="item in menuItems"
            :key="item.id"
            :class="['nav-item', { active: activeView === item.id }]"
            @click="activeView = item.id"
          >
            {{ item.icon }} {{ item.label }}
          </button>
        </nav>
      </aside>

      <main class="main-content">
        <!-- Vista de Dashboard -->
        <div v-if="activeView === 'stats'" class="view-content dashboard-view">
          <div class="dashboard-header">
            <div>
              <h2>📊 Dashboard</h2>
              <p class="dashboard-subtitle">Vista general del hotel</p>
            </div>
            <div class="date-info">
              <span class="date-badge">{{ fechaHoy }}</span>
            </div>
          </div>

          <!-- Estadísticas principales -->
          <div class="stats-grid-modern">
            <div class="stat-card-modern stat-primary">
              <div class="stat-header">
                <span class="stat-label">Reservas Totales</span>
                <span class="stat-icon-modern">📅</span>
              </div>
              <div class="stat-value">{{ stats.totalReservas || 0 }}</div>
              <div class="stat-footer">
                <span class="stat-trend positive">↑ {{ stats.reservasMes || 0 }} este mes</span>
              </div>
            </div>

            <div class="stat-card-modern stat-success">
              <div class="stat-header">
                <span class="stat-label">Ingresos Totales</span>
                <span class="stat-icon-modern">💰</span>
              </div>
              <div class="stat-value">${{ formatPrice(stats.ingresosTotales || 0) }}</div>
              <div class="stat-footer">
                <span class="stat-trend positive">↑ ${{ formatPrice(stats.ingresosMes || 0) }} este mes</span>
              </div>
            </div>

            <div class="stat-card-modern stat-info">
              <div class="stat-header">
                <span class="stat-label">Ocupación</span>
                <span class="stat-icon-modern">🛏️</span>
              </div>
              <div class="stat-value">{{ stats.tasaOcupacion || 0 }}%</div>
              <div class="stat-footer">
                <span>{{ stats.ocupadas || 0 }} de {{ stats.totalHabitaciones || 0 }} habitaciones</span>
              </div>
            </div>

            <div class="stat-card-modern stat-warning">
              <div class="stat-header">
                <span class="stat-label">Próximas Llegadas</span>
                <span class="stat-icon-modern">🔔</span>
              </div>
              <div class="stat-value">{{ proximasReservas.length }}</div>
              <div class="stat-footer">
                <span>En los próximos 7 días</span>
              </div>
            </div>
          </div>

          <!-- Próximas Reservas -->
          <div class="dashboard-section">
            <div class="section-header">
              <h3>🔔 Próximas Llegadas</h3>
            </div>

            <div v-if="loadingProximas" class="loading-mini">Cargando...</div>

            <div v-else-if="proximasReservas.length === 0" class="empty-mini">
              No hay reservas próximas en los siguientes 7 días
            </div>

            <div v-else class="proximas-list">
              <div v-for="reserva in proximasReservas" :key="reserva.id" class="proxima-card">
                <div class="proxima-date">
                  <div class="date-day">{{ getDia(reserva.fecha_entrada) }}</div>
                  <div class="date-month">{{ getMes(reserva.fecha_entrada) }}</div>
                </div>
                <div class="proxima-info">
                  <h4>{{ reserva.cliente_nombre }}</h4>
                  <p>{{ reserva.tipo_nombre }} • {{ calcularNoches(reserva) }} noches</p>
                </div>
                <div class="proxima-badge">
                  <span :class="['badge-dias', getDiasRestantes(reserva.fecha_entrada) <= 1 ? 'urgente' : '']">
                    {{ getDiasRestantes(reserva.fecha_entrada) === 0 ? 'Hoy' : `En ${getDiasRestantes(reserva.fecha_entrada)} días` }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Gráfico de Ocupación -->
          <div class="dashboard-row">
            <div class="dashboard-section chart-container">
              <div class="section-header">
                <h3>📈 Ocupación de los Últimos 7 Días</h3>
              </div>
              <div v-if="ocupacionChartData.length > 0">
                <ChartBar
                  :data="ocupacionChartData"
                  :colors="['#667eea', '#764ba2', '#f59e0b', '#10b981']"
                  :width="700"
                  :height="300"
                />
              </div>
              <div v-else class="loading-mini">Cargando gráfico...</div>
            </div>

            <div class="dashboard-section">
              <div class="section-header">
                <h3>🏆 Habitaciones Más Reservadas</h3>
              </div>
              <div v-if="topHabitacionesDonutData.length > 0">
                <ChartDonut
                  :data="topHabitacionesDonutData"
                  :colors="['#667eea', '#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#8b5cf6']"
                  :size="300"
                  :stroke-width="50"
                  center-label="Total"
                />
              </div>
              <div v-else class="loading-mini">Cargando gráfico...</div>
            </div>
          </div>
        </div>

        <!-- Vista de Reservas -->
        <div v-if="activeView === 'reservas'" class="view-content">
          <div class="view-header">
            <h2>📅 Gestión de Reservas</h2>
            <button @click="cargarReservas" class="btn-refresh">🔄 Actualizar</button>
          </div>

          <div v-if="loadingReservas" class="loading-state">
            Cargando reservas...
          </div>

          <div v-else-if="reservas.length === 0" class="empty-state">
            No hay reservas registradas
          </div>

          <div v-else class="table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Cliente</th>
                  <th>Habitación</th>
                  <th>Entrada</th>
                  <th>Salida</th>
                  <th>Estado</th>
                  <th>Precio</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="reserva in reservas" :key="reserva.id">
                  <td>#{{ reserva.id }}</td>
                  <td>{{ reserva.cliente_nombre }}</td>
                  <td>Hab. {{ reserva.habitacion_id }}</td>
                  <td>{{ formatDate(reserva.fecha_entrada) }}</td>
                  <td>{{ formatDate(reserva.fecha_salida) }}</td>
                  <td>
                    <span :class="['badge', 'badge-' + reserva.estado]">
                      {{ reserva.estado }}
                    </span>
                  </td>
                  <td>${{ formatPrice(reserva.precio_total) }}</td>
                  <td>
                    <div class="action-buttons">
                      <button @click="verDetalleReserva(reserva)" class="btn-sm btn-info">Ver</button>
                      <button @click="editarReserva(reserva)" class="btn-sm btn-edit" v-if="reserva.estado !== 'cancelada'">Editar</button>
                      <button @click="cancelarReserva(reserva)" class="btn-sm btn-cancel" v-if="reserva.estado !== 'cancelada'">Cancelar</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Modal de Edición de Reserva -->
          <div v-if="mostrarModalEdicion" class="modal-overlay" @click.self="cerrarModalEdicion">
            <div class="modal-content">
              <div class="modal-header">
                <h3>Editar Reserva #{{ reservaEditando?.id }}</h3>
                <button @click="cerrarModalEdicion" class="btn-close">✕</button>
              </div>

              <div class="modal-body">
                <div class="form-group">
                  <label>Fecha de Entrada:</label>
                  <input type="date" v-model="formEdicion.fecha_entrada" class="form-control">
                </div>

                <div class="form-group">
                  <label>Fecha de Salida:</label>
                  <input type="date" v-model="formEdicion.fecha_salida" class="form-control">
                </div>

                <div class="form-group">
                  <label>Número de Adultos:</label>
                  <input type="number" v-model="formEdicion.num_adultos" min="1" class="form-control">
                </div>

                <div class="form-group">
                  <label>Número de Niños:</label>
                  <input type="number" v-model="formEdicion.num_ninos" min="0" class="form-control">
                </div>

                <div class="form-group">
                  <label>Precio Total:</label>
                  <input type="number" v-model="formEdicion.precio_total" step="0.01" class="form-control">
                </div>

                <div class="form-group">
                  <label>Estado:</label>
                  <select v-model="formEdicion.estado" class="form-control">
                    <option value="pendiente">Pendiente</option>
                    <option value="pagada">Pagada</option>
                    <option value="cancelada">Cancelada</option>
                  </select>
                </div>
              </div>

              <div class="modal-footer">
                <button @click="cerrarModalEdicion" class="btn-secondary">Cancelar</button>
                <button @click="guardarEdicion" class="btn-primary">Guardar Cambios</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Vista de Habitaciones -->
        <div v-if="activeView === 'habitaciones'" class="view-content">
          <div class="view-header">
            <h2>🛏️ Gestión de Habitaciones</h2>
            <button @click="cargarHabitaciones" class="btn-refresh">🔄 Actualizar</button>
          </div>

          <div v-if="loadingHabitaciones" class="loading-state">
            Cargando habitaciones...
          </div>

          <div v-else class="table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Tipo</th>
                  <th>Estado</th>
                  <th>Capacidad</th>
                  <th>Precio Base</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="hab in habitaciones" :key="hab.id">
                  <td>#{{ hab.id }}</td>
                  <td>{{ hab.nombre }}</td>
                  <td>
                    <span :class="['badge', 'badge-' + hab.estado]">
                      {{ hab.estado }}
                    </span>
                  </td>
                  <td>{{ hab.capacidad_adultos }} adultos</td>
                  <td>${{ formatPrice(hab.precio_base) }}</td>
                  <td>
                    <button @click="cambiarEstado(hab)" class="btn-sm">
                      {{ hab.estado === 'disponible' ? 'Ocupar' : 'Liberar' }}
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Vista de Clientes -->
        <div v-if="activeView === 'clientes'" class="view-content">
          <div class="view-header">
            <h2>👥 Clientes Registrados</h2>
            <button @click="cargarClientes" class="btn-refresh">🔄 Actualizar</button>
          </div>

          <div v-if="loadingClientes" class="loading-state">
            Cargando clientes...
          </div>

          <div v-else class="table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Cédula</th>
                  <th>Nombre Completo</th>
                  <th>Email</th>
                  <th>Teléfono</th>
                  <th>Reservas</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="cliente in clientes" :key="cliente.cedula">
                  <td>{{ cliente.cedula }}</td>
                  <td>{{ cliente.nombre }} {{ cliente.apellido }}</td>
                  <td>{{ cliente.email }}</td>
                  <td>{{ cliente.telefono }}</td>
                  <td>{{ cliente.total_reservas || 0 }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Vista de Reportes -->
        <div v-if="activeView === 'reportes'" class="view-content">
          <div class="view-header">
            <h2>📈 Reportes y Análisis</h2>
          </div>

          <div class="reportes-container">
            <!-- Filtros de reporte -->
            <div class="reportes-filters">
              <h3>Generar Reporte</h3>
              <div class="filter-grid">
                <div class="filter-item">
                  <label>Tipo de Reporte</label>
                  <select v-model="reportes.tipoReporte" class="form-control">
                    <option value="reservas">Reservas</option>
                    <option value="ingresos">Ingresos</option>
                    <option value="ocupacion">Ocupación</option>
                    <option value="clientes">Clientes Frecuentes</option>
                  </select>
                </div>
                <div class="filter-item">
                  <label>Fecha Inicio</label>
                  <input type="date" v-model="reportes.fechaInicio" class="form-control">
                </div>
                <div class="filter-item">
                  <label>Fecha Fin</label>
                  <input type="date" v-model="reportes.fechaFin" class="form-control">
                </div>
                <div class="filter-item filter-buttons">
                  <button @click="generarReporte" class="btn-primary" :disabled="loadingReporte">
                    <svg v-if="!loadingReporte" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                      <polyline points="7 10 12 15 17 10"></polyline>
                      <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    <span>{{ loadingReporte ? 'Generando...' : 'Generar Reporte' }}</span>
                  </button>
                  <button @click="exportarReporte" class="btn-secondary" :disabled="!reporteGenerado">
                    📄 Exportar PDF
                  </button>
                </div>
              </div>
            </div>

            <!-- Resultado del reporte -->
            <div v-if="reporteGenerado" class="reporte-resultado">
              <div class="reporte-header">
                <h3>{{ reporteGenerado.titulo }}</h3>
                <p class="reporte-periodo">{{ reporteGenerado.periodo }}</p>
              </div>

              <!-- Resumen del reporte -->
              <div class="reporte-resumen">
                <div v-for="(item, key) in reporteGenerado.resumen" :key="key" class="resumen-card">
                  <div class="resumen-label">{{ item.label }}</div>
                  <div class="resumen-value">{{ item.value }}</div>
                </div>
              </div>

              <!-- Tabla de datos -->
              <div class="reporte-tabla">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th v-for="col in reporteGenerado.columnas" :key="col.key">{{ col.label }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(fila, index) in reporteGenerado.datos" :key="index">
                      <td v-for="col in reporteGenerado.columnas" :key="col.key">
                        {{ formatearCelda(fila[col.key], col.type) }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Gráficos si aplica -->
              <div v-if="reporteGenerado.grafico" class="reporte-grafico">
                <h4>{{ reporteGenerado.grafico.titulo }}</h4>
                <div class="chart-bar-wrapper">
                  <div v-for="(item, index) in reporteGenerado.grafico.datos" :key="index" class="chart-bar-item">
                    <div class="chart-bar-column">
                      <div class="chart-bar-fill" :style="{ height: item.porcentaje + '%' }">
                        <span class="chart-bar-value">{{ item.valor }}</span>
                      </div>
                    </div>
                    <div class="chart-bar-label">{{ item.label }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Estado vacío -->
            <div v-else-if="!loadingReporte" class="reporte-empty">
              <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
              </svg>
              <h3>Selecciona filtros para generar un reporte</h3>
              <p>Elige el tipo de reporte y el rango de fechas para comenzar</p>
            </div>
          </div>
        </div>

        <!-- Vista de Calendario -->
        <div v-if="activeView === 'calendario'" class="view-content">
          <div class="view-header">
            <h2>📆 Calendario de Disponibilidad</h2>
            <button @click="cargarCalendario" class="btn-refresh">🔄 Actualizar</button>
          </div>

          <div v-if="loadingCalendario" class="loading-state">
            Cargando calendario...
          </div>

          <div v-else class="calendar-container">
            <!-- Navegación del mes -->
            <div class="calendar-header">
              <button @click="changeMonth(-1)" class="btn-month">← Anterior</button>
              <h3 class="month-title">{{ getMonthName(currentMonth) }} {{ currentYear }}</h3>
              <button @click="changeMonth(1)" class="btn-month">Siguiente →</button>
            </div>

            <!-- Leyenda -->
            <div class="calendar-legend">
              <div class="legend-item">
                <span class="legend-color disponible"></span>
                <span>Disponible</span>
              </div>
              <div class="legend-item">
                <span class="legend-color ocupada"></span>
                <span>Ocupada/Reservada</span>
              </div>
            </div>

            <!-- Calendario por tipo de habitación -->
            <div v-for="habitacion in habitaciones" :key="habitacion.id" class="habitacion-calendar">
              <h4 class="habitacion-title">{{ habitacion.nombre }}</h4>

              <div class="calendar-grid">
                <!-- Días de la semana -->
                <div class="calendar-days-header">
                  <div class="day-header">Dom</div>
                  <div class="day-header">Lun</div>
                  <div class="day-header">Mar</div>
                  <div class="day-header">Mié</div>
                  <div class="day-header">Jue</div>
                  <div class="day-header">Vie</div>
                  <div class="day-header">Sáb</div>
                </div>

                <!-- Días del mes -->
                <div class="calendar-days">
                  <!-- Espacios vacíos antes del primer día -->
                  <div v-for="blank in getFirstDayOfMonth(currentMonth, currentYear)"
                       :key="'blank-' + blank"
                       class="calendar-day blank">
                  </div>

                  <!-- Días del mes -->
                  <div v-for="day in getDaysInMonth(currentMonth, currentYear)"
                       :key="day"
                       :class="['calendar-day', {
                         'disponible': !isDateReserved(new Date(currentYear, currentMonth, day), habitacion.id),
                         'ocupada': isDateReserved(new Date(currentYear, currentMonth, day), habitacion.id)
                       }]"
                       @click="verDetalleReserva(getReservationForDate(new Date(currentYear, currentMonth, day), habitacion.id))">
                    <span class="day-number">{{ day }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import ChartLine from '@/components/admin/ChartLine.vue'
import ChartDonut from '@/components/admin/ChartDonut.vue'
import ChartBar from '@/components/admin/ChartBar.vue'
import {
  cargarProximasReservas as cargarProximasReservasHelper,
  cargarOcupacionSemanal as cargarOcupacionSemanalHelper,
  cargarTopHabitaciones as cargarTopHabitacionesHelper,
  generarReporte as generarReporteHelper,
  exportarReporte as exportarReporteHelper,
  getDia,
  getMes,
  getDiasRestantes,
  calcularNoches,
  formatearCelda
} from './dashboard-functions.js'

const router = useRouter()
const adminUser = ref(localStorage.getItem('admin_user') || 'Admin')

const activeView = ref('stats')
const menuItems = [
  { id: 'stats', label: 'Dashboard', icon: '📊' },
  { id: 'reservas', label: 'Reservas', icon: '📅' },
  { id: 'reportes', label: 'Reportes', icon: '📈' },
  { id: 'habitaciones', label: 'Habitaciones', icon: '🛏️' },
  { id: 'clientes', label: 'Clientes', icon: '👥' },
  { id: 'calendario', label: 'Calendario', icon: '📆' }
]

const stats = ref({
  totalHabitaciones: 0,
  disponibles: 0,
  ocupadas: 0,
  totalReservas: 0,
  reservasMes: 0,
  ingresosTotales: 0,
  ingresosMes: 0,
  tasaOcupacion: 0
})

// Variables para dashboard mejorado
const fechaHoy = ref(new Date().toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }))
const proximasReservas = ref([])
const loadingProximas = ref(false)
const ocupacionSemanal = ref([])
const topHabitaciones = ref([])

// Variables para reportes
const reportes = ref({
  fechaInicio: '',
  fechaFin: '',
  tipoReporte: 'reservas'
})
const reporteGenerado = ref(null)
const loadingReporte = ref(false)

const reservas = ref([])
const loadingReservas = ref(false)

const habitaciones = ref([])
const loadingHabitaciones = ref(false)

const clientes = ref([])
const loadingClientes = ref(false)

const calendario = ref([])
const loadingCalendario = ref(false)
const currentMonth = ref(new Date().getMonth())
const currentYear = ref(new Date().getFullYear())

// Variables para edición de reservas
const mostrarModalEdicion = ref(false)
const reservaEditando = ref(null)
const formEdicion = ref({
  fecha_entrada: '',
  fecha_salida: '',
  num_adultos: 1,
  num_ninos: 0,
  precio_total: 0,
  estado: 'pendiente'
})

// Wrapper functions para helpers
const cargarProximasReservas = () => cargarProximasReservasHelper(api, proximasReservas, loadingProximas)
const cargarOcupacionSemanal = () => cargarOcupacionSemanalHelper(api, ocupacionSemanal)
const cargarTopHabitaciones = () => cargarTopHabitacionesHelper(api, topHabitaciones)
const generarReporte = () => generarReporteHelper(api, reportes, reporteGenerado, loadingReporte)
const exportarReporte = () => exportarReporteHelper(reporteGenerado)

// Computed properties para transformar datos para gráficos
const ocupacionChartData = computed(() => {
  return ocupacionSemanal.value.map(dia => ({
    label: dia.dia,
    value: dia.reservas || 0
  }))
})

const topHabitacionesDonutData = computed(() => {
  return topHabitaciones.value.map(tipo => ({
    label: tipo.nombre,
    value: tipo.total
  }))
})

const handleLogout = () => {
  localStorage.removeItem('admin_token')
  localStorage.removeItem('admin_user')
  router.push('/admin/login')
}

const cargarEstadisticas = async () => {
  try {
    const response = await api.get('/admin/stats')
    if (response.data.success) {
      stats.value = response.data.stats
    }
  } catch (error) {
    console.error('Error cargando estadísticas:', error)
  }
}

const cargarReservas = async () => {
  loadingReservas.value = true
  try {
    const response = await api.get('/admin/reservas')
    if (response.data.success) {
      reservas.value = response.data.reservas
    }
  } catch (error) {
    console.error('Error cargando reservas:', error)
  } finally {
    loadingReservas.value = false
  }
}

const cargarHabitaciones = async () => {
  loadingHabitaciones.value = true
  try {
    const response = await api.get('/admin/habitaciones')
    if (response.data.success) {
      habitaciones.value = response.data.habitaciones
    }
  } catch (error) {
    console.error('Error cargando habitaciones:', error)
  } finally {
    loadingHabitaciones.value = false
  }
}

const cargarClientes = async () => {
  loadingClientes.value = true
  try {
    const response = await api.get('/admin/clientes')
    if (response.data.success) {
      clientes.value = response.data.clientes
    }
  } catch (error) {
    console.error('Error cargando clientes:', error)
  } finally {
    loadingClientes.value = false
  }
}

const cargarCalendario = async () => {
  loadingCalendario.value = true
  try {
    const response = await api.get('/admin/calendario')
    if (response.data.success) {
      calendario.value = response.data.calendario
    }
  } catch (error) {
    console.error('Error cargando calendario:', error)
  } finally {
    loadingCalendario.value = false
  }
}

// Funciones del calendario
const getDaysInMonth = (month, year) => {
  return new Date(year, month + 1, 0).getDate()
}

const getFirstDayOfMonth = (month, year) => {
  return new Date(year, month, 1).getDay()
}

const isDateReserved = (date, tipoId) => {
  return calendario.value.some(reserva => {
    const entrada = new Date(reserva.fecha_entrada)
    const salida = new Date(reserva.fecha_salida)
    return reserva.tipo_habitacion_id == tipoId && date >= entrada && date < salida
  })
}

const getReservationForDate = (date, tipoId) => {
  return calendario.value.find(reserva => {
    const entrada = new Date(reserva.fecha_entrada)
    const salida = new Date(reserva.fecha_salida)
    return reserva.tipo_habitacion_id == tipoId && date >= entrada && date < salida
  })
}

const changeMonth = (direction) => {
  currentMonth.value += direction
  if (currentMonth.value > 11) {
    currentMonth.value = 0
    currentYear.value++
  } else if (currentMonth.value < 0) {
    currentMonth.value = 11
    currentYear.value--
  }
}

const getMonthName = (month) => {
  const months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                  'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
  return months[month]
}

const cambiarEstado = async (habitacion) => {
  const nuevoEstado = habitacion.estado === 'disponible' ? 'ocupada' : 'disponible'
  const confirmar = confirm(`¿Cambiar estado de la habitación #${habitacion.id} a ${nuevoEstado}?`)

  if (confirmar) {
    try {
      const response = await api.post('/admin/habitaciones/cambiar-estado', {
        habitacion_id: habitacion.id,
        estado: nuevoEstado
      })

      if (response.data.success) {
        habitacion.estado = nuevoEstado
        alert('Estado actualizado correctamente')
      }
    } catch (error) {
      console.error('Error cambiando estado:', error)
      alert('Error al cambiar el estado')
    }
  }
}

const verDetalleReserva = (reserva) => {
  if (!reserva) return

  const detalles = `
Detalle de Reserva #${reserva.id}

Cliente: ${reserva.cliente_nombre}
Tipo de Habitación: ${reserva.tipo_nombre}
Fecha Entrada: ${formatDate(reserva.fecha_entrada)}
Fecha Salida: ${formatDate(reserva.fecha_salida)}
Estado: ${reserva.estado}
Precio Total: $${formatPrice(reserva.precio_total)}
  `.trim()

  alert(detalles)
}

const editarReserva = (reserva) => {
  reservaEditando.value = reserva
  formEdicion.value = {
    fecha_entrada: reserva.fecha_entrada,
    fecha_salida: reserva.fecha_salida,
    num_adultos: reserva.num_adultos,
    num_ninos: reserva.num_ninos,
    precio_total: reserva.precio_total,
    estado: reserva.estado
  }
  mostrarModalEdicion.value = true
}

const cerrarModalEdicion = () => {
  mostrarModalEdicion.value = false
  reservaEditando.value = null
  formEdicion.value = {
    fecha_entrada: '',
    fecha_salida: '',
    num_adultos: 1,
    num_ninos: 0,
    precio_total: 0,
    estado: 'pendiente'
  }
}

const guardarEdicion = async () => {
  if (!reservaEditando.value) return

  try {
    const response = await api.post('/hotel-api-emergencia.php', {
      action: 'admin_modificar_reserva',
      reserva_id: reservaEditando.value.id,
      ...formEdicion.value
    })

    if (response.data.success) {
      alert('Reserva actualizada exitosamente')
      cerrarModalEdicion()
      cargarReservas()
      if (activeView.value === 'calendario') {
        cargarCalendario()
      }
    } else {
      alert('Error al actualizar reserva: ' + (response.data.error || 'Error desconocido'))
    }
  } catch (error) {
    console.error('Error actualizando reserva:', error)
    alert('Error al actualizar la reserva')
  }
}

const cancelarReserva = async (reserva) => {
  const confirmar = confirm(
    `¿Está seguro de que desea cancelar la reserva #${reserva.id}?\n\n` +
    `Cliente: ${reserva.cliente_nombre}\n` +
    `Fecha: ${formatDate(reserva.fecha_entrada)} - ${formatDate(reserva.fecha_salida)}\n\n` +
    `Esta acción no se puede deshacer.`
  )

  if (!confirmar) return

  try {
    const response = await api.post('/hotel-api-emergencia.php', {
      action: 'admin_cancelar_reserva',
      reserva_id: reserva.id
    })

    if (response.data.success) {
      alert('Reserva cancelada exitosamente')
      cargarReservas()
      if (activeView.value === 'calendario') {
        cargarCalendario()
      }
    } else {
      alert('Error al cancelar reserva: ' + (response.data.error || 'Error desconocido'))
    }
  } catch (error) {
    console.error('Error cancelando reserva:', error)
    alert('Error al cancelar la reserva')
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-CO')
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('es-CO').format(price)
}

// Observar cambios en la vista activa y auto-cargar datos
watch(activeView, (newView) => {
  switch (newView) {
    case 'stats':
      // Recargar datos del dashboard
      cargarEstadisticas()
      cargarProximasReservas()
      cargarOcupacionSemanal()
      cargarTopHabitaciones()
      break
    case 'reservas':
      cargarReservas()
      break
    case 'habitaciones':
      cargarHabitaciones()
      break
    case 'clientes':
      cargarClientes()
      break
    case 'calendario':
      cargarHabitaciones()
      cargarCalendario()
      break
  }
})

onMounted(async () => {
  // Verificar autenticación
  const token = localStorage.getItem('admin_token')
  if (!token) {
    router.push('/admin/login')
    return
  }

  // Auto-cargar todos los datos del dashboard al iniciar
  await Promise.all([
    cargarEstadisticas(),
    cargarProximasReservas(),
    cargarOcupacionSemanal(),
    cargarTopHabitaciones()
  ])
})
</script>

<style scoped>
@import './dashboard-styles.css';

.dashboard-admin {
  min-height: 100vh;
  background: #f3f4f6;
}

.admin-navbar {
  background: white;
  padding: 1rem 2rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.nav-brand h1 {
  font-size: 1.5rem;
  color: #667eea;
}

.nav-user {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.btn-logout {
  background: #ef4444;
  color: white;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9rem;
}

.dashboard-container {
  display: flex;
  min-height: calc(100vh - 80px);
}

.sidebar {
  width: 250px;
  background: white;
  padding: 2rem 0;
  box-shadow: 2px 0 4px rgba(0,0,0,0.05);
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
}

.nav-item {
  padding: 1rem 2rem;
  border: none;
  background: none;
  text-align: left;
  cursor: pointer;
  font-size: 1rem;
  color: #6b7280;
  transition: all 0.2s;
  border-left: 3px solid transparent;
}

.nav-item:hover {
  background: #f3f4f6;
  color: #667eea;
}

.nav-item.active {
  background: #eef2ff;
  color: #667eea;
  border-left-color: #667eea;
  font-weight: 600;
}

.main-content {
  flex: 1;
  padding: 2rem;
}

.view-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.btn-refresh {
  background: #667eea;
  color: white;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  display: flex;
  gap: 1rem;
  align-items: center;
}

.stat-icon {
  font-size: 2.5rem;
}

.stat-info h3 {
  font-size: 2rem;
  color: #667eea;
  margin: 0;
}

.stat-info p {
  margin: 0.25rem 0 0;
  color: #6b7280;
  font-size: 0.9rem;
}

.table-container {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th,
.data-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
}

.data-table th {
  background: #f9fafb;
  font-weight: 600;
  color: #374151;
}

.data-table tr:hover {
  background: #f9fafb;
}

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 600;
}

.badge-pagada {
  background: #d1fae5;
  color: #065f46;
}

.badge-pendiente {
  background: #fef3c7;
  color: #92400e;
}

.badge-cancelada {
  background: #fee2e2;
  color: #991b1b;
}

.badge-disponible {
  background: #d1fae5;
  color: #065f46;
}

.badge-ocupada {
  background: #fee2e2;
  color: #991b1b;
}

.btn-sm {
  padding: 0.4rem 0.8rem;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.85rem;
}

.loading-state,
.empty-state {
  text-align: center;
  padding: 3rem;
  color: #6b7280;
  background: white;
  border-radius: 12px;
}

.chart-section {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.text-muted {
  color: #6b7280;
  font-size: 0.9rem;
}

/* Estilos del Calendario */
.calendar-container {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #e5e7eb;
}

.month-title {
  font-size: 1.5rem;
  color: #667eea;
  margin: 0;
}

.btn-month {
  background: #667eea;
  color: white;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.2s;
}

.btn-month:hover {
  background: #5568d3;
}

.calendar-legend {
  display: flex;
  gap: 2rem;
  margin-bottom: 2rem;
  justify-content: center;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.legend-color {
  width: 30px;
  height: 30px;
  border-radius: 4px;
  border: 1px solid #ddd;
}

.legend-color.disponible {
  background: #10b981;
}

.legend-color.ocupada {
  background: #ef4444;
}

.habitacion-calendar {
  margin-bottom: 3rem;
}

.habitacion-title {
  font-size: 1.2rem;
  color: #374151;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #667eea;
}

.calendar-grid {
  margin-top: 1rem;
}

.calendar-days-header {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.day-header {
  text-align: center;
  font-weight: 600;
  color: #6b7280;
  padding: 0.5rem;
  font-size: 0.9rem;
}

.calendar-days {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 0.5rem;
}

.calendar-day {
  aspect-ratio: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
  min-height: 60px;
}

.calendar-day.blank {
  background: transparent;
  cursor: default;
}

.calendar-day.disponible {
  background: #10b981;
  color: white;
  font-weight: 600;
}

.calendar-day.disponible:hover {
  background: #059669;
  transform: scale(1.05);
  box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
}

.calendar-day.ocupada {
  background: #ef4444;
  color: white;
  font-weight: 600;
}

.calendar-day.ocupada:hover {
  background: #dc2626;
  transform: scale(1.05);
  box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
}

.day-number {
  font-size: 1rem;
}

@media (max-width: 768px) {
  .calendar-days-header,
  .calendar-days {
    grid-template-columns: repeat(7, 1fr);
    gap: 0.25rem;
  }

  .calendar-day {
    min-height: 50px;
  }

  .day-number {
    font-size: 0.85rem;
  }

  .calendar-header {
    flex-direction: column;
    gap: 1rem;
  }
}

/* Estilos para Botones de Acción */
.action-buttons {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.btn-info {
  background: #3b82f6;
}

.btn-info:hover {
  background: #2563eb;
}

.btn-edit {
  background: #f59e0b;
}

.btn-edit:hover {
  background: #d97706;
}

.btn-cancel {
  background: #ef4444;
}

.btn-cancel:hover {
  background: #dc2626;
}

/* Estilos del Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  animation: fadeIn 0.2s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.modal-content {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
  from {
    transform: translateY(20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
  margin: 0;
  color: #667eea;
  font-size: 1.5rem;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #6b7280;
  cursor: pointer;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: all 0.2s;
}

.btn-close:hover {
  background: #f3f4f6;
  color: #374151;
}

.modal-body {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: #374151;
  font-weight: 600;
  font-size: 0.95rem;
}

.form-control {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.2s;
  box-sizing: border-box;
}

.form-control:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1.5rem;
  border-top: 1px solid #e5e7eb;
}

.btn-primary {
  background: #667eea;
  color: white;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-primary:hover {
  background: #5568d3;
  transform: translateY(-1px);
  box-shadow: 0 4px 6px rgba(102, 126, 234, 0.3);
}

.btn-secondary {
  background: #e5e7eb;
  color: #374151;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-secondary:hover {
  background: #d1d5db;
}

@media (max-width: 640px) {
  .modal-content {
    width: 95%;
    max-height: 95vh;
  }

  .modal-header,
  .modal-body,
  .modal-footer {
    padding: 1rem;
  }

  .modal-footer {
    flex-direction: column;
  }

  .btn-primary,
  .btn-secondary {
    width: 100%;
  }
}
</style>
