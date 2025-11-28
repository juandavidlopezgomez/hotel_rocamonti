<template>
  <div class="admin-layout">
    <!-- Sidebar Ultra Profesional -->
    <aside class="modern-sidebar" :class="{ collapsed: sidebarCollapsed }">
      <!-- Logo y Header -->
      <div class="sidebar-brand">
        <div class="brand-logo" v-if="!sidebarCollapsed">
          <div class="logo-circle">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
              <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
          </div>
          <div class="brand-text">
            <h1>ROCAMONTI</h1>
            <p>Hotel Management</p>
          </div>
        </div>
        <div class="logo-mini" v-else>
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
          </svg>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="modern-nav">
        <div class="nav-section">
          <span class="nav-section-title" v-if="!sidebarCollapsed">MENÚ PRINCIPAL</span>
          <button
            v-for="item in menuItems"
            :key="item.id"
            :class="['modern-nav-item', { active: activeView === item.id }]"
            @click="activeView = item.id"
          >
            <span class="nav-item-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path v-if="item.id === 'dashboard'" d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <rect v-if="item.id === 'reservations'" x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <path v-if="item.id === 'rooms'" d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                <path v-if="item.id === 'clients'" d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle v-if="item.id === 'clients'" cx="9" cy="7" r="4"/>
                <path v-if="item.id === 'clients'" d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path v-if="item.id === 'clients'" d="M16 3.13a4 4 0 0 1 0 7.75"/>
                <polyline v-if="item.id === 'reports'" points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                <circle v-if="item.id === 'settings'" cx="12" cy="12" r="3"/>
                <path v-if="item.id === 'settings'" d="M12 1v6m0 6v6M5.64 5.64l4.24 4.24m4.24 4.24l4.24 4.24M1 12h6m6 0h6M5.64 18.36l4.24-4.24m4.24-4.24l4.24-4.24"/>
              </svg>
            </span>
            <span class="nav-item-label" v-if="!sidebarCollapsed">{{ item.label }}</span>
            <span class="nav-item-badge" v-if="item.badge && !sidebarCollapsed">{{ item.badge }}</span>
            <div class="nav-item-indicator"></div>
          </button>
        </div>
      </nav>

      <!-- Footer del Sidebar -->
      <div class="modern-sidebar-footer">
        <div class="user-profile" v-if="!sidebarCollapsed">
          <div class="user-avatar-modern">
            <div class="avatar-gradient">A</div>
            <div class="status-indicator"></div>
          </div>
          <div class="user-info-modern">
            <span class="user-name-modern">Administrador</span>
            <span class="user-role-modern">Panel de Control</span>
          </div>
        </div>
        <button @click="handleLogout" class="logout-btn-modern" :title="sidebarCollapsed ? 'Cerrar Sesión' : ''">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
          <span v-if="!sidebarCollapsed">Salir</span>
        </button>
      </div>

      <!-- Toggle Button -->
      <button @click="toggleSidebar" class="sidebar-toggle-modern">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline v-if="!sidebarCollapsed" points="15 18 9 12 15 6"/>
          <polyline v-else points="9 18 15 12 9 6"/>
        </svg>
      </button>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <!-- Top Bar -->
      <div class="topbar">
        <div class="topbar-left">
          <h1 class="page-title">{{ currentPageTitle }}</h1>
          <p class="page-subtitle">{{ fechaHoy }}</p>
        </div>
        <div class="topbar-right">
          <button @click="generarReportePDF" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
              <polyline points="7 10 12 15 17 10"></polyline>
              <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            Generar Reporte PDF
          </button>
        </div>
      </div>

      <!-- Dashboard View -->
      <div v-if="activeView === 'dashboard'" class="dashboard-content">
        <!-- Stats Cards -->
        <div class="stats-grid">
          <div class="stat-card" v-for="stat in statsCards" :key="stat.id" :class="stat.color">
            <div class="stat-icon-wrapper">
              <div class="stat-icon" v-html="stat.icon"></div>
            </div>
            <div class="stat-content">
              <p class="stat-label">{{ stat.label }}</p>
              <h2 class="stat-value">
                <CountUp :end-val="stat.value" :prefix="stat.prefix" :suffix="stat.suffix" />
              </h2>
              <p class="stat-change" :class="stat.changeType">
                <span v-html="stat.changeIcon"></span>
                {{ stat.change }}
              </p>
            </div>
          </div>
        </div>

        <!-- Charts Row -->
        <div class="charts-row">
          <div class="chart-card chart-large">
            <div class="chart-header">
              <h3>📈 Ingresos Mensuales</h3>
              <select v-model="chartPeriod" class="chart-select">
                <option value="6">Últimos 6 meses</option>
                <option value="12">Último año</option>
              </select>
            </div>
            <div class="chart-container">
              <Line :data="lineChartData" :options="lineChartOptions" />
            </div>
          </div>

          <div class="chart-card">
            <div class="chart-header">
              <h3>🏨 Ocupación</h3>
            </div>
            <div class="chart-container">
              <Doughnut :data="doughnutChartData" :options="doughnutChartOptions" />
            </div>
          </div>
        </div>

        <!-- Bar Chart -->
        <div class="chart-card">
          <div class="chart-header">
            <h3>📊 Reservas por Tipo de Habitación</h3>
          </div>
          <div class="chart-container">
            <Bar :data="barChartData" :options="barChartOptions" />
          </div>
        </div>

        <!-- Recent Bookings Table -->
        <div class="table-card">
          <div class="table-header">
            <h3>🔔 Reservas Recientes</h3>
            <button @click="activeView = 'reservations'" class="btn-secondary">Ver todas</button>
          </div>
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Cliente</th>
                  <th>Habitación</th>
                  <th>Entrada</th>
                  <th>Salida</th>
                  <th>Total</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="reserva in recentReservations" :key="reserva.id">
                  <td>#{{ reserva.id }}</td>
                  <td>
                    <div class="client-info">
                      <strong>{{ reserva.cliente_nombre }}</strong>
                      <small>{{ reserva.cliente_email }}</small>
                    </div>
                  </td>
                  <td>{{ reserva.habitacion }}</td>
                  <td>{{ formatDate(reserva.fecha_entrada) }}</td>
                  <td>{{ formatDate(reserva.fecha_salida) }}</td>
                  <td class="price">${{ formatPrice(reserva.precio_total) }}</td>
                  <td>
                    <span :class="['badge', `badge-${reserva.estado}`]">{{ reserva.estado }}</span>
                  </td>
                  <td>
                    <div class="action-buttons">
                      <button class="btn-icon" title="Ver detalles">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                          <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Reservations View -->
      <div v-if="activeView === 'reservations'" class="reservations-content">
        <div class="filters-bar">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Buscar por cliente, habitación..."
            class="search-input"
          />
          <select v-model="filterStatus" class="filter-select">
            <option value="">Todos los estados</option>
            <option value="pagada">Pagadas</option>
            <option value="pendiente">Pendientes</option>
            <option value="cancelada">Canceladas</option>
          </select>
        </div>

        <div class="table-card">
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Cliente</th>
                  <th>Cédula</th>
                  <th>Habitación</th>
                  <th>Check-in</th>
                  <th>Check-out</th>
                  <th>Personas</th>
                  <th>Total</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="reserva in filteredReservations" :key="reserva.id">
                  <td>#{{ reserva.id }}</td>
                  <td>{{ reserva.cliente_nombre }} {{ reserva.cliente_apellido }}</td>
                  <td>{{ reserva.cliente_cedula }}</td>
                  <td>{{ reserva.habitacion }}</td>
                  <td>{{ formatDate(reserva.fecha_entrada) }}</td>
                  <td>{{ formatDate(reserva.fecha_salida) }}</td>
                  <td>{{ reserva.num_adultos }} A / {{ reserva.num_ninos }} N</td>
                  <td class="price">${{ formatPrice(reserva.precio_total) }}</td>
                  <td>
                    <span :class="['badge', `badge-${reserva.estado}`]">{{ reserva.estado }}</span>
                  </td>
                  <td>
                    <div class="action-buttons">
                      <button class="btn-icon btn-edit" title="Editar" @click="abrirModalEditar(reserva)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                          <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                      </button>
                      <button v-if="reserva.estado !== 'cancelada'" class="btn-icon btn-delete" title="Eliminar" @click="eliminarReserva(reserva.id)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <polyline points="3 6 5 6 21 6"></polyline>
                          <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Habitaciones View -->
      <div v-if="activeView === 'rooms'">
        <HabitacionesModule />
      </div>

      <!-- Clientes View -->
      <div v-if="activeView === 'clients'">
        <ClientesModule />
      </div>

      <!-- Reportes View -->
      <div v-if="activeView === 'reports'">
        <ReportesModule />
      </div>
    </main>

    <!-- Modal de Edición de Reserva -->
    <div v-if="modalEditar" class="modal-overlay" @click="cerrarModalEditar">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>Editar Reserva #{{ reservaEditando?.id }}</h2>
          <button @click="cerrarModalEditar" class="btn-close">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>

        <div class="modal-body" v-if="reservaEditando">
          <div class="form-grid">
            <div class="form-group">
              <label>Cliente</label>
              <input type="text" :value="reservaEditando.cliente_nombre" disabled class="form-input disabled" />
            </div>

            <div class="form-group">
              <label>Habitación</label>
              <input type="text" :value="reservaEditando.tipo_nombre" disabled class="form-input disabled" />
            </div>

            <div class="form-group">
              <label>Fecha de Entrada</label>
              <input type="date" v-model="reservaEditando.fecha_entrada" class="form-input" />
            </div>

            <div class="form-group">
              <label>Fecha de Salida</label>
              <input type="date" v-model="reservaEditando.fecha_salida" class="form-input" />
            </div>

            <div class="form-group">
              <label>Adultos</label>
              <input type="number" v-model.number="reservaEditando.num_adultos" min="1" class="form-input" />
            </div>

            <div class="form-group">
              <label>Niños</label>
              <input type="number" v-model.number="reservaEditando.num_ninos" min="0" class="form-input" />
            </div>

            <div class="form-group full-width">
              <label>Precio Total (COP)</label>
              <input type="number" v-model.number="reservaEditando.precio_total" min="0" class="form-input" />
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button @click="cerrarModalEditar" class="btn-secondary">Cancelar</button>
          <button @click="guardarEdicionReserva" class="btn-primary">Guardar Cambios</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import LogoRocamonti from '../../components/LogoRocamonti.vue'
import CountUp from '../../components/CountUp.vue'
import HabitacionesModule from '../../components/admin/HabitacionesModule.vue'
import ClientesModule from '../../components/admin/ClientesModule.vue'
import ReportesModule from '../../components/admin/ReportesModule.vue'
import { Line, Doughnut, Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'
import jsPDF from 'jspdf'
import 'jspdf-autotable'
import axios from 'axios'
import '@/assets/admin-styles.css'
import '@/assets/modern-sidebar.css'

const API_URL = 'http://localhost:8000/api'

// Registrar componentes de Chart.js
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

const router = useRouter()

// Estado
const sidebarCollapsed = ref(false)
const activeView = ref('dashboard')
const chartPeriod = ref('6')
const searchQuery = ref('')
const filterStatus = ref('')
const loading = ref(false)
const loadingReservations = ref(false)

// Datos de ejemplo (conectar con API real)
const statsCards = ref([
  {
    id: 1,
    label: 'Ingresos del Mes',
    value: 15750000,
    prefix: '$',
    suffix: '',
    icon: '💰',
    color: 'primary',
    change: '+12.5% vs mes anterior',
    changeType: 'positive',
    changeIcon: '↑'
  },
  {
    id: 2,
    label: 'Reservas Activas',
    value: 24,
    prefix: '',
    suffix: '',
    icon: '📅',
    color: 'success',
    change: '+5 esta semana',
    changeType: 'positive',
    changeIcon: '↑'
  },
  {
    id: 3,
    label: 'Ocupación',
    value: 78,
    prefix: '',
    suffix: '%',
    icon: '🏨',
    color: 'info',
    change: '14 de 18 habitaciones',
    changeType: 'neutral',
    changeIcon: ''
  },
  {
    id: 4,
    label: 'Llegadas Hoy',
    value: 6,
    prefix: '',
    suffix: '',
    icon: '✈️',
    color: 'warning',
    change: '3 salidas programadas',
    changeType: 'neutral',
    changeIcon: ''
  }
])

const recentReservations = ref([
  {
    id: 1,
    cliente_nombre: 'Juan Pérez',
    cliente_email: 'juan@email.com',
    cliente_cedula: '123456789',
    cliente_apellido: 'Pérez',
    habitacion: 'Suite Deluxe #201',
    fecha_entrada: '2025-11-18',
    fecha_salida: '2025-11-22',
    num_adultos: 2,
    num_ninos: 1,
    precio_total: 1200000,
    estado: 'pagada'
  },
  {
    id: 2,
    cliente_nombre: 'María García',
    cliente_email: 'maria@email.com',
    cliente_cedula: '987654321',
    cliente_apellido: 'García',
    habitacion: 'Habitación Familiar #105',
    fecha_entrada: '2025-11-19',
    fecha_salida: '2025-11-21',
    num_adultos: 2,
    num_ninos: 2,
    precio_total: 800000,
    estado: 'pagada'
  },
  {
    id: 3,
    cliente_nombre: 'Carlos Rodríguez',
    cliente_email: 'carlos@email.com',
    cliente_cedula: '456789123',
    cliente_apellido: 'Rodríguez',
    habitacion: 'Apartamento #301',
    fecha_entrada: '2025-11-20',
    fecha_salida: '2025-11-25',
    num_adultos: 4,
    num_ninos: 0,
    precio_total: 2500000,
    estado: 'pendiente'
  }
])

const allReservations = ref([...recentReservations.value])

const menuItems = [
  { id: 'dashboard', label: 'Dashboard', icon: '📊' },
  { id: 'reservations', label: 'Reservas', icon: '📅', badge: allReservations.value.length },
  { id: 'rooms', label: 'Habitaciones', icon: '🏨' },
  { id: 'clients', label: 'Clientes', icon: '👥' },
  { id: 'reports', label: 'Reportes', icon: '📈' },
  { id: 'settings', label: 'Configuración', icon: '⚙️' }
]

const fechaHoy = computed(() => {
  const fecha = new Date()
  const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
  return fecha.toLocaleDateString('es-ES', opciones)
})

const currentPageTitle = computed(() => {
  const item = menuItems.find(i => i.id === activeView.value)
  return item ? item.label : 'Dashboard'
})

const filteredReservations = computed(() => {
  let filtered = allReservations.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(r =>
      r.cliente_nombre.toLowerCase().includes(query) ||
      r.cliente_apellido.toLowerCase().includes(query) ||
      r.habitacion.toLowerCase().includes(query) ||
      r.cliente_cedula.includes(query)
    )
  }

  if (filterStatus.value) {
    filtered = filtered.filter(r => r.estado === filterStatus.value)
  }

  return filtered
})

// Chart Data
const lineChartData = computed(() => ({
  labels: ['Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre'],
  datasets: [
    {
      label: 'Ingresos',
      data: [12000000, 14500000, 11000000, 15000000, 13500000, 15750000],
      borderColor: '#1e40af',
      backgroundColor: 'rgba(30, 64, 175, 0.1)',
      fill: true,
      tension: 0.4,
      borderWidth: 3,
      pointRadius: 5,
      pointHoverRadius: 7
    }
  ]
}))

const lineChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      padding: 12,
      titleColor: '#fff',
      bodyColor: '#fff',
      callbacks: {
        label: function(context) {
          return 'Ingresos: $' + context.parsed.y.toLocaleString('es-CO')
        }
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        callback: function(value) {
          return '$' + (value / 1000000) + 'M'
        }
      },
      grid: {
        color: 'rgba(0, 0, 0, 0.05)'
      }
    },
    x: {
      grid: {
        display: false
      }
    }
  }
}

const doughnutChartData = computed(() => ({
  labels: ['Ocupadas', 'Disponibles'],
  datasets: [
    {
      data: [14, 4],
      backgroundColor: ['#1e40af', '#e5e7eb'],
      borderWidth: 0
    }
  ]
}))

const doughnutChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        padding: 20,
        usePointStyle: true,
        font: {
          size: 12
        }
      }
    }
  },
  cutout: '70%'
}

const barChartData = computed(() => ({
  labels: ['Suite Deluxe', 'Habitación Familiar', 'Apartamento', 'Habitación Doble', 'Suite Junior'],
  datasets: [
    {
      label: 'Reservas',
      data: [12, 19, 8, 15, 10],
      backgroundColor: [
        'rgba(30, 64, 175, 0.8)',
        'rgba(16, 185, 129, 0.8)',
        'rgba(245, 158, 11, 0.8)',
        'rgba(139, 92, 246, 0.8)',
        'rgba(239, 68, 68, 0.8)'
      ],
      borderRadius: 8
    }
  ]
}))

const barChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        color: 'rgba(0, 0, 0, 0.05)'
      }
    },
    x: {
      grid: {
        display: false
      }
    }
  }
}

function toggleSidebar() {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

function handleLogout() {
  localStorage.removeItem('admin_token')
  router.push('/admin/login')
}

function formatDate(dateString) {
  const fecha = new Date(dateString)
  return fecha.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' })
}

function formatPrice(price) {
  return price.toLocaleString('es-CO')
}

async function cancelarReserva(id) {
  if (!confirm('¿Estás seguro de cancelar esta reserva?')) {
    return
  }

  try {
    const response = await axios.post(`${API_URL}/reservas/${id}/cancelar`)

    if (response.data.success) {
      // Actualizar la reserva localmente
      const reserva = allReservations.value.find(r => r.id === id)
      if (reserva) {
        reserva.estado = 'cancelada'
      }

      alert('Reserva cancelada exitosamente')
      await cargarReservas() // Recargar reservas
    }
  } catch (error) {
    console.error('Error al cancelar reserva:', error)
    alert('Error al cancelar la reserva. Por favor, intenta nuevamente.')
  }
}

function generarReportePDF() {
  const doc = new jsPDF()
  const pageWidth = doc.internal.pageSize.width
  const pageHeight = doc.internal.pageSize.height

  // === ENCABEZADO CON LOGO Y TÍTULO ===
  // Fondo del encabezado
  doc.setFillColor(30, 64, 175)
  doc.rect(0, 0, pageWidth, 45, 'F')

  // Logo y nombre del hotel (texto estilizado)
  doc.setFontSize(24)
  doc.setTextColor(255, 255, 255)
  doc.setFont(undefined, 'bold')
  doc.text('🏨 HOTEL ROCAMONTI', pageWidth / 2, 20, { align: 'center' })

  doc.setFontSize(10)
  doc.setFont(undefined, 'normal')
  doc.text('Tu refugio junto a la laguna', pageWidth / 2, 28, { align: 'center' })

  // Información del reporte
  doc.setFontSize(14)
  doc.setFont(undefined, 'bold')
  doc.text('REPORTE DE RESERVAS', pageWidth / 2, 38, { align: 'center' })

  // Fecha y período
  doc.setTextColor(0, 0, 0)
  doc.setFontSize(10)
  doc.setFont(undefined, 'normal')
  const hoy = new Date()
  const hace15Dias = new Date(hoy.getTime() - 15 * 24 * 60 * 60 * 1000)
  doc.text(`Período: ${formatDate(hace15Dias.toISOString())} - ${formatDate(hoy.toISOString())}`, 20, 55)
  doc.text(`Generado: ${hoy.toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })}`, pageWidth - 20, 55, { align: 'right' })

  // === RESUMEN EJECUTIVO ===
  let currentY = 70

  // Cuadro de resumen
  doc.setFillColor(245, 247, 250)
  doc.roundedRect(15, currentY, pageWidth - 30, 40, 3, 3, 'F')

  doc.setFontSize(12)
  doc.setFont(undefined, 'bold')
  doc.setTextColor(30, 64, 175)
  doc.text('RESUMEN EJECUTIVO', 20, currentY + 8)

  doc.setFontSize(10)
  doc.setTextColor(0, 0, 0)
  doc.setFont(undefined, 'normal')

  const totalReservas = allReservations.value.length
  const reservasPagadas = allReservations.value.filter(r => r.estado === 'pagada').length
  const reservasPendientes = allReservations.value.filter(r => r.estado === 'pendiente').length
  const reservasCanceladas = allReservations.value.filter(r => r.estado === 'cancelada').length
  const ingresosTotales = allReservations.value.filter(r => r.estado === 'pagada').reduce((sum, r) => sum + r.precio_total, 0)
  const ingresosPendientes = allReservations.value.filter(r => r.estado === 'pendiente').reduce((sum, r) => sum + r.precio_total, 0)

  const col1X = 25
  const col2X = 115

  doc.text(`• Total de Reservas:`, col1X, currentY + 18)
  doc.setFont(undefined, 'bold')
  doc.text(`${totalReservas}`, col1X + 60, currentY + 18)

  doc.setFont(undefined, 'normal')
  doc.text(`• Reservas Pagadas:`, col1X, currentY + 25)
  doc.setTextColor(2, 132, 199) // Azul
  doc.setFont(undefined, 'bold')
  doc.text(`${reservasPagadas}`, col1X + 60, currentY + 25)

  doc.setTextColor(0, 0, 0)
  doc.setFont(undefined, 'normal')
  doc.text(`• Reservas Pendientes:`, col1X, currentY + 32)
  doc.setTextColor(6, 182, 212) // Azul cyan
  doc.setFont(undefined, 'bold')
  doc.text(`${reservasPendientes}`, col1X + 60, currentY + 32)

  doc.setTextColor(0, 0, 0)
  doc.setFont(undefined, 'normal')
  doc.text(`• Ingresos Confirmados:`, col2X, currentY + 18)
  doc.setTextColor(2, 132, 199) // Azul
  doc.setFont(undefined, 'bold')
  doc.text(`$${formatPrice(ingresosTotales)}`, col2X + 60, currentY + 18)

  doc.setTextColor(0, 0, 0)
  doc.setFont(undefined, 'normal')
  doc.text(`• Ingresos Pendientes:`, col2X, currentY + 25)
  doc.setTextColor(6, 182, 212) // Azul cyan
  doc.setFont(undefined, 'bold')
  doc.text(`$${formatPrice(ingresosPendientes)}`, col2X + 60, currentY + 25)

  doc.setTextColor(0, 0, 0)
  doc.setFont(undefined, 'normal')
  doc.text(`• Reservas Canceladas:`, col2X, currentY + 32)
  doc.setTextColor(100, 116, 139) // Gris azulado
  doc.setFont(undefined, 'bold')
  doc.text(`${reservasCanceladas}`, col2X + 60, currentY + 32)

  // === TABLA DE RESERVAS DETALLADA ===
  doc.setTextColor(0, 0, 0)
  currentY += 50

  doc.setFontSize(12)
  doc.setFont(undefined, 'bold')
  doc.setTextColor(30, 64, 175)
  doc.text('DETALLE DE RESERVAS', 20, currentY)

  const tableData = allReservations.value.map(r => [
    `#${r.id}`,
    `${r.cliente_nombre} ${r.cliente_apellido}`,
    r.habitacion,
    formatDate(r.fecha_entrada),
    formatDate(r.fecha_salida),
    `${r.num_adultos}A ${r.num_ninos}N`,
    `$${formatPrice(r.precio_total)}`,
    r.estado.toUpperCase()
  ])

  doc.autoTable({
    startY: currentY + 5,
    head: [['ID', 'Cliente', 'Habitación', 'Check-in', 'Check-out', 'Pax', 'Total', 'Estado']],
    body: tableData,
    theme: 'striped',
    headStyles: {
      fillColor: [30, 64, 175],
      textColor: [255, 255, 255],
      fontSize: 9,
      fontStyle: 'bold',
      halign: 'center'
    },
    bodyStyles: {
      fontSize: 8,
      cellPadding: 3
    },
    alternateRowStyles: {
      fillColor: [250, 251, 252]
    },
    columnStyles: {
      0: { cellWidth: 15, halign: 'center' },
      1: { cellWidth: 40 },
      2: { cellWidth: 35 },
      3: { cellWidth: 22, halign: 'center' },
      4: { cellWidth: 22, halign: 'center' },
      5: { cellWidth: 15, halign: 'center' },
      6: { cellWidth: 25, halign: 'right', fontStyle: 'bold' },
      7: { cellWidth: 20, halign: 'center' }
    },
    didParseCell: function(data) {
      // Colorear estados con tonos azules
      if (data.column.index === 7 && data.section === 'body') {
        const estado = data.cell.text[0]
        if (estado === 'PAGADA') {
          data.cell.styles.textColor = [2, 132, 199] // Azul fuerte
          data.cell.styles.fontStyle = 'bold'
        } else if (estado === 'PENDIENTE') {
          data.cell.styles.textColor = [6, 182, 212] // Azul cyan
          data.cell.styles.fontStyle = 'bold'
        } else if (estado === 'CANCELADA') {
          data.cell.styles.textColor = [100, 116, 139] // Gris azulado
          data.cell.styles.fontStyle = 'bold'
        }
      }
    }
  })

  // === PIE DE PÁGINA ===
  const pageCount = doc.internal.getNumberOfPages()
  for (let i = 1; i <= pageCount; i++) {
    doc.setPage(i)

    // Línea superior del footer
    doc.setDrawColor(30, 64, 175)
    doc.setLineWidth(0.5)
    doc.line(20, pageHeight - 20, pageWidth - 20, pageHeight - 20)

    // Información del footer
    doc.setFontSize(8)
    doc.setTextColor(100, 100, 100)
    doc.setFont(undefined, 'normal')
    doc.text('Hotel Rocamonti - Sistema de Gestión Administrativa', 20, pageHeight - 14)
    doc.text(`Página ${i} de ${pageCount}`, pageWidth / 2, pageHeight - 14, { align: 'center' })
    doc.text(`Generado: ${hoy.toLocaleDateString('es-ES')}`, pageWidth - 20, pageHeight - 14, { align: 'right' })
  }

  // Guardar PDF
  const fechaFormateada = hoy.toISOString().split('T')[0]
  const horaFormateada = `${String(hoy.getHours()).padStart(2, '0')}${String(hoy.getMinutes()).padStart(2, '0')}`
  const nombreArchivo = `Reporte_Rocamonti_${fechaFormateada}_${horaFormateada}.pdf`

  doc.save(nombreArchivo)

  // Notificación profesional
  console.log(`✅ Reporte PDF generado: ${nombreArchivo}`)

  // Crear notificación visual temporal
  const notificacion = document.createElement('div')
  notificacion.innerHTML = `
    <div style="
      position: fixed;
      top: 80px;
      right: 20px;
      background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
      color: white;
      padding: 1rem 1.5rem;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(30, 64, 175, 0.3);
      z-index: 10000;
      animation: slideInRight 0.3s ease;
      font-family: system-ui, -apple-system, sans-serif;
    ">
      <div style="display: flex; align-items: center; gap: 0.75rem;">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0;">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
          <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <div>
          <div style="font-weight: 600; margin-bottom: 0.25rem;">¡Reporte generado!</div>
          <div style="font-size: 0.875rem; opacity: 0.9;">${nombreArchivo}</div>
        </div>
      </div>
    </div>
  `

  document.body.appendChild(notificacion)

  setTimeout(() => {
    notificacion.style.animation = 'slideOutRight 0.3s ease'
    setTimeout(() => notificacion.remove(), 300)
  }, 4000)
}

// Funciones para cargar datos de la API
async function cargarEstadisticas() {
  try {
    loading.value = true
    const response = await axios.get(`${API_URL}/admin/stats`)

    if (response.data.success) {
      const stats = response.data.data

      // Actualizar tarjetas de estadísticas
      statsCards.value[0].value = stats.ingresosMes || 0
      statsCards.value[0].change = `+${((stats.ingresosMes / stats.ingresosMesAnterior - 1) * 100).toFixed(1)}% vs mes anterior`

      statsCards.value[1].value = stats.reservasActivas || 0
      statsCards.value[1].change = `+${stats.reservasEstaSemana || 0} esta semana`

      statsCards.value[2].value = stats.tasaOcupacion || 0
      statsCards.value[2].change = `${stats.habitacionesOcupadas || 0} de ${stats.totalHabitaciones || 0} habitaciones`

      statsCards.value[3].value = stats.llegadasHoy || 0
      statsCards.value[3].change = `${stats.salidasHoy || 0} salidas programadas`
    }
  } catch (error) {
    console.error('Error al cargar estadísticas:', error)
  } finally {
    loading.value = false
  }
}

async function cargarReservas() {
  try {
    loadingReservations.value = true
    // Usar endpoint de admin PHP que funciona
    const response = await axios.get('http://172.17.192.1/hotel-api-emergencia.php', {
      params: { action: 'admin_reservas' }
    })

    if (response.data.success) {
      allReservations.value = response.data.reservas.map(reserva => ({
        id: reserva.id,
        cliente_nombre: reserva.cliente_nombre || 'N/A',
        cliente_apellido: '',
        cliente_email: reserva.cliente_email || 'N/A',
        cliente_telefono: reserva.cliente_telefono || 'N/A',
        cliente_cedula: reserva.cliente_cedula,
        habitacion: reserva.numero_habitacion || reserva.tipo_nombre || `Reserva #${reserva.id}`,
        tipo_nombre: reserva.tipo_nombre || 'N/A',
        fecha_entrada: reserva.fecha_entrada,
        fecha_salida: reserva.fecha_salida,
        num_adultos: reserva.num_adultos,
        num_ninos: reserva.num_ninos,
        precio_total: parseFloat(reserva.precio_total),
        estado: reserva.estado,
        created_at: reserva.created_at
      }))

      // Actualizar reservas recientes (últimas 10)
      recentReservations.value = allReservations.value.slice(0, 10)

      console.log('✅ Reservas cargadas desde admin:', allReservations.value.length)
    }
  } catch (error) {
    console.error('❌ Error al cargar reservas:', error)
  } finally {
    loadingReservations.value = false
  }
}

// Función para eliminar reserva
async function eliminarReserva(reservaId) {
  if (!confirm('¿Estás seguro de que deseas eliminar esta reserva?')) {
    return
  }

  try {
    const response = await axios.post('http://172.17.192.1/hotel-api-emergencia.php', {
      action: 'admin_cancelar_reserva',
      reserva_id: reservaId
    })

    if (response.data.success) {
      mostrarNotificacion('Reserva eliminada exitosamente', 'success')
      await cargarReservas() // Recargar lista
    } else {
      mostrarNotificacion('Error al eliminar reserva: ' + response.data.error, 'error')
    }
  } catch (error) {
    console.error('Error al eliminar reserva:', error)
    mostrarNotificacion('Error al eliminar reserva', 'error')
  }
}

// Función para editar reserva
const reservaEditando = ref(null)
const modalEditar = ref(false)

function abrirModalEditar(reserva) {
  reservaEditando.value = { ...reserva }
  modalEditar.value = true
}

function cerrarModalEditar() {
  reservaEditando.value = null
  modalEditar.value = false
}

async function guardarEdicionReserva() {
  if (!reservaEditando.value) return

  try {
    const response = await axios.post('http://172.17.192.1/hotel-api-emergencia.php', {
      action: 'admin_modificar_reserva',
      reserva_id: reservaEditando.value.id,
      fecha_entrada: reservaEditando.value.fecha_entrada,
      fecha_salida: reservaEditando.value.fecha_salida,
      num_adultos: reservaEditando.value.num_adultos,
      num_ninos: reservaEditando.value.num_ninos,
      precio_total: reservaEditando.value.precio_total
    })

    if (response.data.success) {
      mostrarNotificacion('Reserva actualizada exitosamente', 'success')
      cerrarModalEditar()
      await cargarReservas() // Recargar lista
    } else {
      mostrarNotificacion('Error al actualizar reserva: ' + response.data.error, 'error')
    }
  } catch (error) {
    console.error('Error al actualizar reserva:', error)
    mostrarNotificacion('Error al actualizar reserva', 'error')
  }
}

async function cargarHabitaciones() {
  try {
    const response = await axios.get(`${API_URL}/room-types`)

    if (response.data.success) {
      const tipos = response.data.tipos

      // Actualizar gráfica de barras con tipos de habitación reales
      barChartData.value.labels = tipos.map(t => t.nombre.substring(0, 20))
      barChartData.value.datasets[0].data = tipos.map(t => Math.floor(Math.random() * 20) + 5) // Simulado por ahora
    }
  } catch (error) {
    console.error('Error al cargar habitaciones:', error)
  }
}

function mostrarNotificacion(mensaje, tipo = 'success') {
  const notificacion = document.createElement('div')
  const bgColor = tipo === 'success' ? 'linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%)' : 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)'
  const icon = tipo === 'success'
    ? '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline>'
    : '<circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line>'

  notificacion.innerHTML = `
    <div style="
      position: fixed;
      top: 80px;
      right: 20px;
      background: ${bgColor};
      color: white;
      padding: 1rem 1.5rem;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(30, 64, 175, 0.3);
      z-index: 10000;
      animation: slideInRight 0.3s ease;
      font-family: system-ui, -apple-system, sans-serif;
    ">
      <div style="display: flex; align-items: center; gap: 0.75rem;">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          ${icon}
        </svg>
        <div>
          <div style="font-weight: 600;">${mensaje}</div>
        </div>
      </div>
    </div>
  `

  document.body.appendChild(notificacion)

  setTimeout(() => {
    notificacion.style.animation = 'slideOutRight 0.3s ease'
    setTimeout(() => notificacion.remove(), 300)
  }, 3000)
}

onMounted(async () => {
  await cargarEstadisticas()
  await cargarReservas()
  await cargarHabitaciones()
})
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.admin-layout {
  display: flex;
  min-height: 100vh;
  background: #f5f7fa;
}

/* ========== MODERN SIDEBAR ========== */
.modern-sidebar {
  width: 280px;
  background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
  position: fixed;
  left: 0;
  top: 0;
  bottom: 0;
  display: flex;
  flex-direction: column;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: 1000;
  box-shadow: 4px 0 30px rgba(0, 0, 0, 0.15);
  border-right: 1px solid rgba(255, 255, 255, 0.05);
}

.modern-sidebar.collapsed {
  width: 80px;
}

/* Brand / Logo */
.sidebar-brand {
  padding: 2rem 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.brand-logo {
  display: flex;
  align-items: center;
  gap: 1rem;
  animation: slideIn 0.5s ease;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(-20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.logo-circle {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 16px rgba(59, 130, 246, 0.3);
  flex-shrink: 0;
}

.logo-circle svg {
  color: white;
}

.logo-mini {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  box-shadow: 0 8px 16px rgba(59, 130, 246, 0.3);
}

.logo-mini svg {
  color: white;
}

.brand-text h1 {
  font-size: 1.25rem;
  font-weight: 800;
  color: white;
  margin: 0;
  letter-spacing: 0.5px;
}

.brand-text p {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.6);
  margin: 0;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.sidebar-nav {
  flex: 1;
  padding: 1rem 0;
  overflow-y: auto;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.5rem;
  background: transparent;
  border: none;
  color: rgba(255, 255, 255, 0.8);
  cursor: pointer;
  transition: all 0.3s;
  font-size: 1rem;
  width: 100%;
  text-align: left;
  position: relative;
}

.sidebar.collapsed .nav-item {
  justify-content: center;
  padding: 1rem;
}

.nav-icon {
  font-size: 1.5rem;
  flex-shrink: 0;
}

.nav-label {
  flex: 1;
  white-space: nowrap;
}

.nav-badge {
  background: rgba(255, 255, 255, 0.2);
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.nav-item.active {
  background: rgba(255, 255, 255, 0.15);
  color: white;
  font-weight: 600;
}

.nav-item.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: white;
  border-radius: 0 4px 4px 0;
}

.sidebar-footer {
  padding: 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.25rem;
}

.user-details {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-weight: 600;
  font-size: 0.95rem;
}

.user-role {
  font-size: 0.75rem;
  opacity: 0.8;
}

.btn-logout {
  width: 100%;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  border-radius: 10px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-size: 0.95rem;
  transition: all 0.3s;
}

.btn-logout:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateY(-2px);
}

/* Main Content */
.main-content {
  margin-left: 280px;
  flex: 1;
  transition: margin-left 0.3s ease;
  min-height: 100vh;
}

.sidebar.collapsed ~ .main-content {
  margin-left: 80px;
}

.topbar {
  background: white;
  padding: 1.5rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  position: sticky;
  top: 0;
  z-index: 100;
}

.topbar-left {
  flex: 1;
}

.page-title {
  font-size: 1.75rem;
  color: #1f2937;
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.page-subtitle {
  color: #6b7280;
  font-size: 0.95rem;
}

.btn-primary {
  background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s;
  box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(30, 64, 175, 0.4);
}

.btn-secondary {
  background: #f3f4f6;
  color: #374151;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-secondary:hover {
  background: #e5e7eb;
}

/* Dashboard Content */
.dashboard-content {
  padding: 2rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  gap: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: all 0.3s;
  position: relative;
  overflow: hidden;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #1e40af, #1e3a8a);
}

.stat-card.success::before {
  background: linear-gradient(90deg, #10b981, #059669);
}

.stat-card.info::before {
  background: linear-gradient(90deg, #3b82f6, #2563eb);
}

.stat-card.warning::before {
  background: linear-gradient(90deg, #f59e0b, #d97706);
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

.stat-icon-wrapper {
  width: 64px;
  height: 64px;
  border-radius: 16px;
  background: linear-gradient(135deg, rgba(30, 64, 175, 0.1), rgba(30, 64, 175, 0.05));
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-card.success .stat-icon-wrapper {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));
}

.stat-card.info .stat-icon-wrapper {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.05));
}

.stat-card.warning .stat-icon-wrapper {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(245, 158, 11, 0.05));
}

.stat-icon {
  font-size: 2rem;
}

.stat-content {
  flex: 1;
}

.stat-label {
  color: #6b7280;
  font-size: 0.875rem;
  font-weight: 500;
  margin-bottom: 0.5rem;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.stat-change {
  font-size: 0.875rem;
  color: #6b7280;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.stat-change.positive {
  color: #10b981;
}

.stat-change.negative {
  color: #ef4444;
}

/* Charts */
.charts-row {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.chart-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.chart-header h3 {
  font-size: 1.125rem;
  color: #1f2937;
  font-weight: 700;
}

.chart-select {
  padding: 0.5rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.875rem;
  cursor: pointer;
}

.chart-container {
  height: 300px;
  position: relative;
}

.chart-large .chart-container {
  height: 350px;
}

/* Table */
.table-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  margin-bottom: 2rem;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.table-header h3 {
  font-size: 1.125rem;
  color: #1f2937;
  font-weight: 700;
}

.table-responsive {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table thead {
  background: #f9fafb;
}

.data-table th {
  padding: 1rem;
  text-align: left;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
  border-bottom: 2px solid #e5e7eb;
}

.data-table td {
  padding: 1rem;
  font-size: 0.875rem;
  color: #6b7280;
  border-bottom: 1px solid #f3f4f6;
}

.data-table tbody tr {
  transition: background 0.2s;
}

.data-table tbody tr:hover {
  background: #f9fafb;
}

.client-info {
  display: flex;
  flex-direction: column;
}

.client-info strong {
  color: #1f2937;
  font-weight: 600;
}

.client-info small {
  color: #9ca3af;
  font-size: 0.75rem;
}

.price {
  font-weight: 600;
  color: #10b981;
}

.badge {
  display: inline-block;
  padding: 0.375rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
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

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.btn-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: none;
  background: #f3f4f6;
  color: #6b7280;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.btn-icon:hover {
  background: #1e40af;
  color: white;
  transform: scale(1.1);
}

.btn-icon.btn-view:hover {
  background: #3b82f6;
}

.btn-icon.btn-edit:hover {
  background: #10b981;
}

.btn-icon.btn-delete:hover {
  background: #ef4444;
}

/* Reservations View */
.reservations-content {
  padding: 2rem;
}

.filters-bar {
  background: white;
  padding: 1.5rem;
  border-radius: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  margin-bottom: 1.5rem;
  display: flex;
  gap: 1rem;
}

.search-input {
  flex: 1;
  padding: 0.75rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  font-size: 0.95rem;
}

.search-input:focus {
  outline: none;
  border-color: #1e40af;
  box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
}

.filter-select {
  padding: 0.75rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  font-size: 0.95rem;
  min-width: 200px;
  cursor: pointer;
}

/* Animaciones para notificaciones */
@keyframes slideInRight {
  from {
    transform: translateX(400px);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@keyframes slideOutRight {
  from {
    transform: translateX(0);
    opacity: 1;
  }
  to {
    transform: translateX(400px);
    opacity: 0;
  }
}

@media (max-width: 1024px) {
  .sidebar {
    width: 80px;
  }

  .sidebar .nav-label,
  .sidebar .nav-badge,
  .sidebar .logo-container,
  .sidebar-footer {
    display: none;
  }

  .main-content {
    margin-left: 80px;
  }

  .charts-row {
    grid-template-columns: 1fr;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }

  .topbar {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }

  .filters-bar {
    flex-direction: column;
  }
}
</style>
