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
      <nav class="sidebar-nav">
        <span class="nav-section-title" v-if="!sidebarCollapsed">MENÚ PRINCIPAL</span>
        <button
          v-for="item in menuItems"
          :key="item.id"
          :class="['nav-item', { active: activeView === item.id }]"
          @click="activeView = item.id"
        >
          <span class="nav-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path v-if="item.id === 'dashboard'" d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
              <rect v-if="item.id === 'reservations'" x="3" y="4" width="18" height="18" rx="2" ry="2"/>
              <line v-if="item.id === 'reservations'" x1="16" y1="2" x2="16" y2="6"/>
              <line v-if="item.id === 'reservations'" x1="8" y1="2" x2="8" y2="6"/>
              <line v-if="item.id === 'reservations'" x1="3" y1="10" x2="21" y2="10"/>
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
          <span class="nav-label" v-if="!sidebarCollapsed">{{ item.label }}</span>
          <span class="nav-badge" v-if="item.badge && !sidebarCollapsed">{{ item.badge }}</span>
        </button>
      </nav>

      <!-- Footer del Sidebar -->
      <div class="sidebar-footer">
        <div class="user-info">
          <div class="user-avatar">A</div>
          <div class="user-details" v-if="!sidebarCollapsed">
            <span class="user-name">Administrador</span>
            <span class="user-role">Panel de Control</span>
          </div>
        </div>
        <button @click="handleLogout" class="btn-logout">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
          <span v-if="!sidebarCollapsed">Cerrar Sesión</span>
        </button>
      </div>
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
          <!-- Botón de reporte eliminado -->
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

      <!-- Reservations View - ULTRA PROFESSIONAL -->
      <div v-if="activeView === 'reservations'" class="dashboard-content">
        <!-- Quick Stats for Reservations -->
        <div class="stats-grid" style="margin-bottom: 2rem;">
          <div class="stat-card success">
            <div class="stat-icon-wrapper">
              <div class="stat-icon">✈️</div>
            </div>
            <div class="stat-content">
              <p class="stat-label">Llegadas Hoy</p>
              <h2 class="stat-value">{{ reservasHoy.length }}</h2>
              <p class="stat-change positive">{{ reservasHoy.filter(r => r.estado === 'confirmada').length }} pendientes de check-in</p>
            </div>
          </div>

          <div class="stat-card warning">
            <div class="stat-icon-wrapper">
              <div class="stat-icon">🚪</div>
            </div>
            <div class="stat-content">
              <p class="stat-label">Salidas Hoy</p>
              <h2 class="stat-value">{{ salidasHoy.length }}</h2>
              <p class="stat-change">{{ salidasHoy.filter(r => r.estado === 'activa').length }} pendientes de check-out</p>
            </div>
          </div>

          <div class="stat-card info">
            <div class="stat-icon-wrapper">
              <div class="stat-icon">🏨</div>
            </div>
            <div class="stat-content">
              <p class="stat-label">Huéspedes Actuales</p>
              <h2 class="stat-value">{{ huespedes.activos }}</h2>
              <p class="stat-change">En {{ reservasActivas.length }} habitaciones</p>
            </div>
          </div>

          <div class="stat-card" style="border-top-color: #6366f1;">
            <div class="stat-icon-wrapper" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(99, 102, 241, 0.05));">
              <div class="stat-icon">📅</div>
            </div>
            <div class="stat-content">
              <p class="stat-label">Próximos 7 Días</p>
              <h2 class="stat-value">{{ proximasReservas.length }}</h2>
              <p class="stat-change">Reservas confirmadas</p>
            </div>
          </div>
        </div>

        <div class="reservations-section">
          <div class="section-header">
            <h2 class="section-title">
              <span class="section-title-icon">📅</span>
              Gestión de Reservas
            </h2>
            <div style="display: flex; gap: 1rem;">
              <button class="btn-secondary" @click="exportarReservas">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                  <polyline points="7 10 12 15 17 10"></polyline>
                  <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Exportar Excel
              </button>
              <button class="btn-primary" @click="abrirModalNuevaReserva">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"></line>
                  <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Nueva Reserva
              </button>
            </div>
          </div>

          <!-- BANNER INFORMATIVO -->
          <div style="margin-bottom: 1.5rem; padding: 1rem 1.5rem; background: linear-gradient(135deg, #d1fae5, #a7f3d0); border-radius: var(--radius-lg); border-left: 5px solid var(--booking-success); display: flex; align-items: center; gap: 1rem; box-shadow: var(--shadow-sm);">
            <div style="font-size: 2rem;">💳</div>
            <div style="flex: 1;">
              <p style="font-weight: 700; color: var(--booking-success); margin: 0; font-size: 1rem;">
                Sistema de Pago Anticipado
              </p>
              <p style="font-size: 0.875rem; color: var(--color-gray-700); margin: 0; margin-top: 0.25rem;">
                Todas las reservas en el sistema están <strong>100% pagadas</strong>. El pago se procesa obligatoriamente al momento de crear cada reserva.
              </p>
            </div>
            <div style="background: var(--color-white); padding: 0.5rem 1rem; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
              <p style="margin: 0; font-size: 0.75rem; color: var(--color-gray-600); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Reservas</p>
              <p style="margin: 0; font-size: 1.5rem; font-weight: 800; color: var(--booking-success);">{{ allReservations.length }}</p>
            </div>
          </div>

          <!-- TABS PROFESIONALES -->
          <div class="booking-tabs">
            <button
              v-for="tab in reservationTabs"
              :key="tab.id"
              :class="['booking-tab', { active: activeReservationTab === tab.id }]"
              @click="activeReservationTab = tab.id"
            >
              <span class="tab-icon">{{ tab.icon }}</span>
              <span class="tab-label">{{ tab.label }}</span>
              <span class="tab-count">{{ tab.count }}</span>
            </button>
          </div>

          <!-- FILTROS AVANZADOS -->
          <div class="advanced-filters">
            <div class="search-input-wrapper">
              <svg class="search-input-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
              </svg>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Buscar por cliente, cédula, habitación..."
                class="search-input"
              />
            </div>

            <div class="date-filters">
              <button
                v-for="dateFilter in dateFilters"
                :key="dateFilter.id"
                :class="['date-filter-btn', { active: activeDateFilter === dateFilter.id }]"
                @click="activeDateFilter = dateFilter.id"
              >
                <span>{{ dateFilter.icon }}</span>
                <span>{{ dateFilter.label }}</span>
              </button>
            </div>

            <select v-model="filterStatus" class="filter-select">
              <option value="">💳 Todas (100% Pagadas)</option>
              <option value="confirmada">✅ Confirmadas - Pendiente Check-in</option>
              <option value="activa">🏨 Activas - Huésped en Hotel</option>
              <option value="completada">✔️ Completadas - Check-out Realizado</option>
              <option value="cancelada">❌ Canceladas</option>
            </select>
          </div>

          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Cliente</th>
                  <th>Habitación</th>
                  <th>Check-in</th>
                  <th>Check-out</th>
                  <th>Noches</th>
                  <th>Huéspedes</th>
                  <th>Total Pagado</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="reserva in filteredReservationsAdvanced" :key="reserva.id" class="animate-fade-in">
                  <td>
                    <strong style="color: var(--booking-primary);">#{{ reserva.id }}</strong>
                  </td>
                  <td>
                    <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                      <strong>{{ reserva.cliente_nombre }} {{ reserva.cliente_apellido }}</strong>
                      <small style="color: var(--color-gray-600);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; vertical-align: middle;">
                          <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                          <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        CC: {{ reserva.cliente_cedula }}
                      </small>
                    </div>
                  </td>
                  <td>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                      <span style="background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: var(--booking-primary); padding: 0.25rem 0.75rem; border-radius: 6px; font-weight: 600; font-size: 0.875rem;">
                        🏨 {{ reserva.habitacion }}
                      </span>
                    </div>
                  </td>
                  <td>
                    <div style="display: flex; flex-direction: column; gap: 0.125rem;">
                      <strong>{{ formatDate(reserva.fecha_entrada) }}</strong>
                      <small style="color: var(--color-gray-600);">{{ getDayName(reserva.fecha_entrada) }}</small>
                    </div>
                  </td>
                  <td>
                    <div style="display: flex; flex-direction: column; gap: 0.125rem;">
                      <strong>{{ formatDate(reserva.fecha_salida) }}</strong>
                      <small style="color: var(--color-gray-600);">{{ getDayName(reserva.fecha_salida) }}</small>
                    </div>
                  </td>
                  <td>
                    <span style="background: var(--color-gray-100); padding: 0.25rem 0.75rem; border-radius: 6px; font-weight: 600;">
                      {{ calcularNoches(reserva.fecha_entrada, reserva.fecha_salida) }} noches
                    </span>
                  </td>
                  <td>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                      <span style="display: inline-flex; align-items: center; gap: 0.25rem;">
                        👤 {{ reserva.num_adultos }}
                      </span>
                      <span v-if="reserva.num_ninos > 0" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                        👶 {{ reserva.num_ninos }}
                      </span>
                    </div>
                  </td>
                  <td>
                    <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                      <strong class="price">${{ formatPrice(reserva.precio_total) }}</strong>
                      <span style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.75rem; color: var(--booking-success); font-weight: 600;">
                        ✓ Pagado
                      </span>
                    </div>
                  </td>
                  <td>
                    <span :class="['badge', `badge-${reserva.estado}`]">
                      {{ getEstadoLabel(reserva.estado) }}
                    </span>
                  </td>
                  <td>
                    <div class="action-buttons">
                      <button v-if="reserva.estado === 'confirmada'" class="btn-icon btn-success" title="Realizar Check-in" @click="hacerCheckIn(reserva.id)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                          <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                      </button>
                      <button v-if="reserva.estado === 'activa'" class="btn-icon btn-warning" title="Realizar Check-out" @click="hacerCheckOut(reserva.id)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                          <polyline points="16 17 21 12 16 7"></polyline>
                          <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                      </button>
                      <button class="btn-icon btn-view" title="Ver detalles" @click="verDetallesReserva(reserva)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                          <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                      </button>
                      <button class="btn-icon btn-edit" title="Editar" @click="abrirModalEditar(reserva)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                          <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                      </button>
                      <button v-if="reserva.estado !== 'cancelada' && reserva.estado !== 'completada'" class="btn-icon btn-delete" title="Cancelar reserva" @click="eliminarReserva(reserva.id)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <circle cx="12" cy="12" r="10"></circle>
                          <line x1="15" y1="9" x2="9" y2="15"></line>
                          <line x1="9" y1="9" x2="15" y2="15"></line>
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

    <!-- Modal de Nueva Reserva -->
    <div v-if="modalNuevaReserva" class="modal-overlay" @click="cerrarModalNuevaReserva">
      <div class="modal-content modal-large" @click.stop>
        <div class="modal-header">
          <h2>✨ Nueva Reserva</h2>
          <button @click="cerrarModalNuevaReserva" class="btn-close">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>

        <div class="modal-body">
          <div class="form-grid">
            <div class="form-group">
              <label class="required">Nombre del Cliente</label>
              <input type="text" v-model="nuevaReserva.cliente_nombre" class="form-input" placeholder="Ingrese el nombre" />
            </div>

            <div class="form-group">
              <label class="required">Apellido del Cliente</label>
              <input type="text" v-model="nuevaReserva.cliente_apellido" class="form-input" placeholder="Ingrese el apellido" />
            </div>

            <div class="form-group">
              <label class="required">Cédula</label>
              <input type="text" v-model="nuevaReserva.cliente_cedula" class="form-input" placeholder="123456789" />
            </div>

            <div class="form-group">
              <label class="required">Email</label>
              <input type="email" v-model="nuevaReserva.cliente_email" class="form-input" placeholder="cliente@email.com" />
            </div>

            <div class="form-group">
              <label class="required">Teléfono</label>
              <input type="tel" v-model="nuevaReserva.cliente_telefono" class="form-input" placeholder="+57 300 123 4567" />
            </div>

            <div class="form-group">
              <label class="required">Tipo de Habitación</label>
              <select v-model="nuevaReserva.tipo_habitacion_id" class="form-select">
                <option value="">Seleccione una habitación</option>
                <option v-for="tipo in tiposHabitacion" :key="tipo.id" :value="tipo.id">
                  {{ tipo.nombre }} - ${{ formatPrice(tipo.precio_base) }}/noche
                </option>
              </select>
            </div>

            <div class="form-group">
              <label class="required">Fecha de Entrada</label>
              <input type="date" v-model="nuevaReserva.fecha_entrada" class="form-input" :min="fechaMinima" />
            </div>

            <div class="form-group">
              <label class="required">Fecha de Salida</label>
              <input type="date" v-model="nuevaReserva.fecha_salida" class="form-input" :min="nuevaReserva.fecha_entrada" />
            </div>

            <div class="form-group">
              <label class="required">Número de Adultos</label>
              <input type="number" v-model.number="nuevaReserva.num_adultos" min="1" max="10" class="form-input" />
            </div>

            <div class="form-group">
              <label>Número de Niños</label>
              <input type="number" v-model.number="nuevaReserva.num_ninos" min="0" max="10" class="form-input" />
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
              <label class="required">Precio Total (COP)</label>
              <div style="position: relative;">
                <input type="number" v-model.number="nuevaReserva.precio_total" min="0" class="form-input" style="padding-left: 2.5rem;" />
                <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); font-weight: 600; color: var(--booking-primary);">$</span>
              </div>
              <p class="form-hint" v-if="nuevaReserva.fecha_entrada && nuevaReserva.fecha_salida">
                {{ calcularNoches(nuevaReserva.fecha_entrada, nuevaReserva.fecha_salida) }} noches
              </p>
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
              <label>Observaciones</label>
              <textarea v-model="nuevaReserva.observaciones" class="form-textarea" rows="3" placeholder="Notas adicionales sobre la reserva..."></textarea>
            </div>
          </div>

          <div style="margin-top: 1.5rem; padding: 1rem; background: linear-gradient(135deg, #d1fae5, #a7f3d0); border-radius: var(--radius-md); border-left: 4px solid var(--booking-success);">
            <p style="font-weight: 600; color: var(--booking-success); margin: 0;">
              ✓ El pago se realizará al momento de crear la reserva
            </p>
          </div>
        </div>

        <div class="modal-footer">
          <button @click="cerrarModalNuevaReserva" class="btn-secondary">Cancelar</button>
          <button @click="crearNuevaReserva" class="btn-primary">Crear Reserva y Procesar Pago</button>
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
import '@/assets/booking-admin.css'
import '@/assets/booking-content.css'
import '@/assets/booking-modals.css'
import '@/assets/booking-reservations.css'

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

// Advanced Reservations Filters
const activeReservationTab = ref('all')
const activeDateFilter = ref('all')

// Nueva Reserva
const modalNuevaReserva = ref(false)
const tiposHabitacion = ref([])
const fechaMinima = computed(() => {
  const hoy = new Date()
  return hoy.toISOString().split('T')[0]
})

const nuevaReserva = ref({
  cliente_nombre: '',
  cliente_apellido: '',
  cliente_cedula: '',
  cliente_email: '',
  cliente_telefono: '',
  tipo_habitacion_id: '',
  fecha_entrada: '',
  fecha_salida: '',
  num_adultos: 1,
  num_ninos: 0,
  precio_total: 0,
  observaciones: ''
})

const dateFilters = ref([
  { id: 'all', label: 'Todas', icon: '📋' },
  { id: 'today', label: 'Hoy', icon: '📅' },
  { id: 'tomorrow', label: 'Mañana', icon: '🌅' },
  { id: 'week', label: 'Próximos 7 días', icon: '📆' },
  { id: 'month', label: 'Este mes', icon: '🗓️' }
])

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

// Helper function: Get day name in Spanish
const getDayName = (dateString) => {
  const fecha = new Date(dateString)
  const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
  return dias[fecha.getDay()]
}

// Helper function: Calculate nights
const calcularNoches = (checkIn, checkOut) => {
  const inicio = new Date(checkIn)
  const fin = new Date(checkOut)
  const diff = Math.abs(fin - inicio)
  return Math.ceil(diff / (1000 * 60 * 60 * 24))
}

// Helper function: Get formatted status label
const getEstadoLabel = (estado) => {
  const estados = {
    confirmada: 'Confirmada',
    'en-proceso': 'En Proceso',
    completada: 'Completada',
    cancelada: 'Cancelada',
    activa: 'Activa'
  }
  return estados[estado] || estado
}

// Advanced computed properties for reservations filtering
const reservasHoy = computed(() => {
  const hoy = new Date()
  hoy.setHours(0, 0, 0, 0)
  return allReservations.value.filter(r => {
    const checkIn = new Date(r.fecha_entrada)
    checkIn.setHours(0, 0, 0, 0)
    return checkIn.getTime() === hoy.getTime()
  })
})

const salidasHoy = computed(() => {
  const hoy = new Date()
  hoy.setHours(0, 0, 0, 0)
  return allReservations.value.filter(r => {
    const checkOut = new Date(r.fecha_salida)
    checkOut.setHours(0, 0, 0, 0)
    return checkOut.getTime() === hoy.getTime()
  })
})

const reservasActivas = computed(() => {
  const hoy = new Date()
  hoy.setHours(0, 0, 0, 0)
  return allReservations.value.filter(r => {
    const checkIn = new Date(r.fecha_entrada)
    const checkOut = new Date(r.fecha_salida)
    checkIn.setHours(0, 0, 0, 0)
    checkOut.setHours(0, 0, 0, 0)
    return checkIn <= hoy && checkOut >= hoy && (r.estado === 'confirmada' || r.estado === 'activa')
  })
})

const proximasReservas = computed(() => {
  const hoy = new Date()
  hoy.setHours(0, 0, 0, 0)
  const futuro = new Date(hoy)
  futuro.setDate(futuro.getDate() + 7)

  return allReservations.value.filter(r => {
    const checkIn = new Date(r.fecha_entrada)
    checkIn.setHours(0, 0, 0, 0)
    return checkIn > hoy && checkIn <= futuro
  })
})

const huespedes = computed(() => {
  return reservasActivas.value.reduce((total, reserva) => {
    return total + ((reserva.num_adultos || 0) + (reserva.num_ninos || 0))
  }, 0)
})

// Reservation tabs with dynamic counts
const reservationTabs = computed(() => [
  {
    id: 'all',
    label: 'Todas',
    icon: '📋',
    count: allReservations.value.length
  },
  {
    id: 'today',
    label: 'Hoy',
    icon: '📅',
    count: reservasHoy.value.length
  },
  {
    id: 'tomorrow',
    label: 'Mañana',
    icon: '🌅',
    count: (() => {
      const manana = new Date()
      manana.setDate(manana.getDate() + 1)
      manana.setHours(0, 0, 0, 0)
      return allReservations.value.filter(r => {
        const checkIn = new Date(r.fecha_entrada)
        checkIn.setHours(0, 0, 0, 0)
        return checkIn.getTime() === manana.getTime()
      }).length
    })()
  },
  {
    id: 'week',
    label: 'Esta Semana',
    icon: '📆',
    count: proximasReservas.value.length
  },
  {
    id: 'active',
    label: 'Activas',
    icon: '✅',
    count: reservasActivas.value.length
  }
])

// Advanced filtered reservations with all filters combined
const filteredReservationsAdvanced = computed(() => {
  let filtered = allReservations.value

  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(r =>
      r.cliente_nombre.toLowerCase().includes(query) ||
      r.cliente_apellido.toLowerCase().includes(query) ||
      r.habitacion.toLowerCase().includes(query) ||
      r.cliente_cedula.includes(query)
    )
  }

  // Apply status filter
  if (filterStatus.value) {
    filtered = filtered.filter(r => r.estado === filterStatus.value)
  }

  // Apply tab filter
  if (activeReservationTab.value !== 'all') {
    const hoy = new Date()
    hoy.setHours(0, 0, 0, 0)

    switch (activeReservationTab.value) {
      case 'today':
        filtered = filtered.filter(r => {
          const checkIn = new Date(r.fecha_entrada)
          checkIn.setHours(0, 0, 0, 0)
          return checkIn.getTime() === hoy.getTime()
        })
        break
      case 'tomorrow':
        const manana = new Date(hoy)
        manana.setDate(manana.getDate() + 1)
        filtered = filtered.filter(r => {
          const checkIn = new Date(r.fecha_entrada)
          checkIn.setHours(0, 0, 0, 0)
          return checkIn.getTime() === manana.getTime()
        })
        break
      case 'week':
        const futuro = new Date(hoy)
        futuro.setDate(futuro.getDate() + 7)
        filtered = filtered.filter(r => {
          const checkIn = new Date(r.fecha_entrada)
          checkIn.setHours(0, 0, 0, 0)
          return checkIn > hoy && checkIn <= futuro
        })
        break
      case 'active':
        filtered = filtered.filter(r => {
          const checkIn = new Date(r.fecha_entrada)
          const checkOut = new Date(r.fecha_salida)
          checkIn.setHours(0, 0, 0, 0)
          checkOut.setHours(0, 0, 0, 0)
          return checkIn <= hoy && checkOut >= hoy && (r.estado === 'confirmada' || r.estado === 'activa')
        })
        break
    }
  }

  // Apply date filter
  if (activeDateFilter.value !== 'all') {
    const hoy = new Date()
    hoy.setHours(0, 0, 0, 0)

    switch (activeDateFilter.value) {
      case 'today':
        filtered = filtered.filter(r => {
          const checkIn = new Date(r.fecha_entrada)
          checkIn.setHours(0, 0, 0, 0)
          return checkIn.getTime() === hoy.getTime()
        })
        break
      case 'tomorrow':
        const manana = new Date(hoy)
        manana.setDate(manana.getDate() + 1)
        filtered = filtered.filter(r => {
          const checkIn = new Date(r.fecha_entrada)
          checkIn.setHours(0, 0, 0, 0)
          return checkIn.getTime() === manana.getTime()
        })
        break
      case 'week':
        const futuro = new Date(hoy)
        futuro.setDate(futuro.getDate() + 7)
        filtered = filtered.filter(r => {
          const checkIn = new Date(r.fecha_entrada)
          checkIn.setHours(0, 0, 0, 0)
          return checkIn >= hoy && checkIn <= futuro
        })
        break
      case 'month':
        const mesActual = hoy.getMonth()
        const anioActual = hoy.getFullYear()
        filtered = filtered.filter(r => {
          const checkIn = new Date(r.fecha_entrada)
          return checkIn.getMonth() === mesActual && checkIn.getFullYear() === anioActual
        })
        break
    }
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

function verDetallesReserva(reserva) {
  // Create a detailed view of the reservation
  const detalles = `
=== DETALLES DE LA RESERVA #${reserva.id} ===

CLIENTE:
  Nombre: ${reserva.cliente_nombre} ${reserva.cliente_apellido}
  Cédula: ${reserva.cliente_cedula}
  Email: ${reserva.cliente_email}
  Teléfono: ${reserva.cliente_telefono}

RESERVA:
  Habitación: ${reserva.habitacion}
  Check-in: ${formatDate(reserva.fecha_entrada)} (${getDayName(reserva.fecha_entrada)})
  Check-out: ${formatDate(reserva.fecha_salida)} (${getDayName(reserva.fecha_salida)})
  Noches: ${calcularNoches(reserva.fecha_entrada, reserva.fecha_salida)}
  Adultos: ${reserva.num_adultos}
  Niños: ${reserva.num_ninos}

PAGO:
  Total: $${formatPrice(reserva.precio_total)}
  Estado: PAGADO ✓

ESTADO: ${getEstadoLabel(reserva.estado).toUpperCase()}
  `.trim()

  alert(detalles)
}

function exportarReservas() {
  try {
    const reservas = filteredReservationsAdvanced.value

    if (reservas.length === 0) {
      alert('No hay reservas para exportar')
      return
    }

    // Prepare CSV data
    const headers = [
      'ID',
      'Cliente',
      'Cédula',
      'Email',
      'Teléfono',
      'Habitación',
      'Check-in',
      'Check-out',
      'Noches',
      'Huéspedes',
      'Total',
      'Estado de Pago',
      'Estado'
    ]

    const rows = reservas.map(r => [
      r.id,
      `${r.cliente_nombre} ${r.cliente_apellido}`,
      r.cliente_cedula,
      r.cliente_email,
      r.cliente_telefono,
      r.habitacion,
      formatDate(r.fecha_entrada),
      formatDate(r.fecha_salida),
      calcularNoches(r.fecha_entrada, r.fecha_salida),
      `${r.num_adultos} adultos, ${r.num_ninos} niños`,
      `$${formatPrice(r.precio_total)}`,
      'PAGADO',
      getEstadoLabel(r.estado)
    ])

    // Create CSV content
    const csvContent = [
      headers.join(','),
      ...rows.map(row => row.map(cell => `"${cell}"`).join(','))
    ].join('\n')

    // Create download link
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)

    link.setAttribute('href', url)
    link.setAttribute('download', `reservas_${new Date().toISOString().split('T')[0]}.csv`)
    link.style.visibility = 'hidden'

    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)

    alert(`Se exportaron ${reservas.length} reservas exitosamente`)
  } catch (error) {
    console.error('Error al exportar reservas:', error)
    alert('Error al exportar las reservas. Por favor, intenta nuevamente.')
  }
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
    // Usar API de Laravel
    const response = await axios.get('http://localhost:8000/api/admin/reservas')

    if (response.data.success) {
      allReservations.value = response.data.reservas.map(reserva => ({
        id: reserva.id,
        cliente_nombre: reserva.cliente?.nombre || 'N/A',
        cliente_apellido: reserva.cliente?.apellido || '',
        cliente_email: reserva.cliente?.email || 'N/A',
        cliente_telefono: reserva.cliente?.telefono || 'N/A',
        cliente_cedula: reserva.cliente_cedula,
        habitacion: `${reserva.habitacion?.tipo_habitacion?.nombre || 'Habitación'} #${reserva.habitacion?.numero_habitacion || reserva.habitacion_id}`,
        tipo_nombre: reserva.habitacion?.tipo_habitacion?.nombre || 'N/A',
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

      console.log('✅ Reservas cargadas desde Laravel API:', allReservations.value.length)
    }
  } catch (error) {
    console.error('❌ Error al cargar reservas:', error)
    console.error('Detalles:', error.response?.data)
  } finally {
    loadingReservations.value = false
  }
}

// Función para eliminar reserva
async function eliminarReserva(reservaId) {
  if (!confirm('¿Estás seguro de que deseas cancelar esta reserva?')) {
    return
  }

  try {
    const response = await axios.post(`http://localhost:8000/api/admin/reservas/${reservaId}/cancelar`)

    if (response.data.success) {
      mostrarNotificacion('Reserva cancelada exitosamente', 'success')
      await cargarReservas() // Recargar lista
    } else {
      mostrarNotificacion('Error al cancelar reserva: ' + response.data.message, 'error')
    }
  } catch (error) {
    console.error('Error al cancelar reserva:', error)
    mostrarNotificacion('Error al cancelar reserva', 'error')
  }
}

// Función para hacer check-in
async function hacerCheckIn(reservaId) {
  if (!confirm('¿Confirmar check-in para esta reserva?')) {
    return
  }

  try {
    const response = await axios.post(`http://localhost:8000/api/admin/reservas/${reservaId}/check-in`)

    if (response.data.success) {
      mostrarNotificacion('Check-in realizado exitosamente', 'success')
      await cargarReservas() // Recargar lista
    } else {
      mostrarNotificacion('Error al realizar check-in: ' + response.data.message, 'error')
    }
  } catch (error) {
    console.error('Error al realizar check-in:', error)
    mostrarNotificacion('Error al realizar check-in', 'error')
  }
}

// Función para hacer check-out
async function hacerCheckOut(reservaId) {
  if (!confirm('¿Confirmar check-out para esta reserva?')) {
    return
  }

  try {
    const response = await axios.post(`http://localhost:8000/api/admin/reservas/${reservaId}/check-out`)

    if (response.data.success) {
      mostrarNotificacion('Check-out realizado exitosamente', 'success')
      await cargarReservas() // Recargar lista
    } else {
      mostrarNotificacion('Error al realizar check-out: ' + response.data.message, 'error')
    }
  } catch (error) {
    console.error('Error al realizar check-out:', error)
    mostrarNotificacion('Error al realizar check-out', 'error')
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
    const response = await axios.put(`http://localhost:8000/api/admin/reservas/${reservaEditando.value.id}`, {
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
      mostrarNotificacion('Error al actualizar reserva: ' + response.data.message, 'error')
    }
  } catch (error) {
    console.error('Error al actualizar reserva:', error)
    mostrarNotificacion('Error al actualizar reserva', 'error')
  }
}

// Funciones para Nueva Reserva
function abrirModalNuevaReserva() {
  // Reset form
  nuevaReserva.value = {
    cliente_nombre: '',
    cliente_apellido: '',
    cliente_cedula: '',
    cliente_email: '',
    cliente_telefono: '',
    tipo_habitacion_id: '',
    fecha_entrada: '',
    fecha_salida: '',
    num_adultos: 1,
    num_ninos: 0,
    precio_total: 0,
    observaciones: ''
  }
  modalNuevaReserva.value = true
}

function cerrarModalNuevaReserva() {
  modalNuevaReserva.value = false
}

async function crearNuevaReserva() {
  // Validación
  if (!nuevaReserva.value.cliente_nombre || !nuevaReserva.value.cliente_apellido) {
    alert('Por favor ingrese el nombre y apellido del cliente')
    return
  }

  if (!nuevaReserva.value.cliente_cedula) {
    alert('Por favor ingrese la cédula del cliente')
    return
  }

  if (!nuevaReserva.value.cliente_email) {
    alert('Por favor ingrese el email del cliente')
    return
  }

  if (!nuevaReserva.value.cliente_telefono) {
    alert('Por favor ingrese el teléfono del cliente')
    return
  }

  if (!nuevaReserva.value.tipo_habitacion_id) {
    alert('Por favor seleccione un tipo de habitación')
    return
  }

  if (!nuevaReserva.value.fecha_entrada || !nuevaReserva.value.fecha_salida) {
    alert('Por favor seleccione las fechas de entrada y salida')
    return
  }

  if (nuevaReserva.value.fecha_entrada >= nuevaReserva.value.fecha_salida) {
    alert('La fecha de salida debe ser posterior a la fecha de entrada')
    return
  }

  if (!nuevaReserva.value.precio_total || nuevaReserva.value.precio_total <= 0) {
    alert('Por favor ingrese el precio total de la reserva')
    return
  }

  try {
    loading.value = true

    const response = await axios.post(`${API_URL}/admin/reservas`, {
      cliente_nombre: nuevaReserva.value.cliente_nombre,
      cliente_apellido: nuevaReserva.value.cliente_apellido,
      cliente_cedula: nuevaReserva.value.cliente_cedula,
      cliente_email: nuevaReserva.value.cliente_email,
      cliente_telefono: nuevaReserva.value.cliente_telefono,
      tipo_habitacion_id: nuevaReserva.value.tipo_habitacion_id,
      fecha_entrada: nuevaReserva.value.fecha_entrada,
      fecha_salida: nuevaReserva.value.fecha_salida,
      num_adultos: nuevaReserva.value.num_adultos,
      num_ninos: nuevaReserva.value.num_ninos,
      precio_total: nuevaReserva.value.precio_total,
      observaciones: nuevaReserva.value.observaciones,
      estado: 'confirmada',
      pagado: true // Siempre pagado como indicaste
    })

    if (response.data.success) {
      alert('✅ Reserva creada exitosamente y pago procesado')
      cerrarModalNuevaReserva()
      await cargarReservas() // Recargar lista
    } else {
      alert('Error al crear reserva: ' + (response.data.message || 'Error desconocido'))
    }
  } catch (error) {
    console.error('Error al crear reserva:', error)
    alert('Error al crear la reserva. Por favor, intenta nuevamente.')
  } finally {
    loading.value = false
  }
}

async function cargarHabitaciones() {
  try {
    const response = await axios.get(`${API_URL}/room-types`)

    if (response.data.success) {
      const tipos = response.data.tipos

      // Guardar tipos para el formulario de nueva reserva
      tiposHabitacion.value = tipos

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
/* Todos los estilos están en booking-admin.css, booking-content.css y booking-modals.css */
</style>
