// Funciones adicionales para el Dashboard Admin Mejorado

// Cargar próximas reservas (próximos 7 días)
export async function cargarProximasReservas(api, proximasReservas, loadingProximas) {
  loadingProximas.value = true
  try {
    const hoy = new Date()
    const en7Dias = new Date()
    en7Dias.setDate(hoy.getDate() + 7)

    const response = await api.get('/admin/proximas-reservas', {
      params: {
        fecha_inicio: hoy.toISOString().split('T')[0],
        fecha_fin: en7Dias.toISOString().split('T')[0]
      }
    })

    if (response.data.success) {
      proximasReservas.value = response.data.reservas
    }
  } catch (error) {
    console.error('Error cargando próximas reservas:', error)
  } finally {
    loadingProximas.value = false
  }
}

// Cargar ocupación semanal
export async function cargarOcupacionSemanal(api, ocupacionSemanal) {
  try {
    const response = await api.get('/admin/ocupacion-semanal')

    if (response.data.success) {
      ocupacionSemanal.value = response.data.datos
    }
  } catch (error) {
    console.error('Error cargando ocupación semanal:', error)
  }
}

// Cargar top habitaciones
export async function cargarTopHabitaciones(api, topHabitaciones) {
  try {
    const response = await api.get('/admin/top-habitaciones', {
      params: {
        limit: 5
      }
    })

    if (response.data.success) {
      topHabitaciones.value = response.data.tipos
    }
  } catch (error) {
    console.error('Error cargando top habitaciones:', error)
  }
}

// Generar reporte
export async function generarReporte(api, reportes, reporteGenerado, loadingReporte) {
  if (!reportes.value.fechaInicio || !reportes.value.fechaFin) {
    alert('Por favor selecciona las fechas de inicio y fin')
    return
  }

  loadingReporte.value = true
  try {
    const response = await api.post('/admin/generar-reporte', {
      tipo: reportes.value.tipoReporte,
      fecha_inicio: reportes.value.fechaInicio,
      fecha_fin: reportes.value.fechaFin
    })

    if (response.data.success) {
      reporteGenerado.value = response.data.reporte
    } else {
      alert('Error al generar el reporte: ' + (response.data.error || 'Error desconocido'))
    }
  } catch (error) {
    console.error('Error generando reporte:', error)
    alert('Error al generar el reporte')
  } finally {
    loadingReporte.value = false
  }
}

// Exportar reporte a PDF
export function exportarReporte(reporteGenerado) {
  if (!reporteGenerado.value) return

  // Crear contenido HTML para imprimir
  const contenido = `
    <html>
    <head>
      <title>${reporteGenerado.value.titulo}</title>
      <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h1 { color: #667eea; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #667eea; color: white; }
        .resumen { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
        .resumen-card { padding: 15px; background: #f3f4f6; border-radius: 8px; }
      </style>
    </head>
    <body>
      <h1>${reporteGenerado.value.titulo}</h1>
      <p><strong>Período:</strong> ${reporteGenerado.value.periodo}</p>

      <div class="resumen">
        ${Object.values(reporteGenerado.value.resumen).map(item => `
          <div class="resumen-card">
            <div><strong>${item.label}</strong></div>
            <div style="font-size: 24px; color: #667eea;">${item.value}</div>
          </div>
        `).join('')}
      </div>

      <table>
        <thead>
          <tr>
            ${reporteGenerado.value.columnas.map(col => `<th>${col.label}</th>`).join('')}
          </tr>
        </thead>
        <tbody>
          ${reporteGenerado.value.datos.map(fila => `
            <tr>
              ${reporteGenerado.value.columnas.map(col => `<td>${fila[col.key] || ''}</td>`).join('')}
            </tr>
          `).join('')}
        </tbody>
      </table>
    </body>
    </html>
  `

  // Abrir ventana de impresión
  const ventana = window.open('', '_blank')
  ventana.document.write(contenido)
  ventana.document.close()
  ventana.print()
}

// Utilidades de formato
export function getDia(fecha) {
  return new Date(fecha).getDate()
}

export function getMes(fecha) {
  const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic']
  return meses[new Date(fecha).getMonth()]
}

export function getDiasRestantes(fecha) {
  const hoy = new Date()
  hoy.setHours(0, 0, 0, 0)
  const fechaReserva = new Date(fecha)
  fechaReserva.setHours(0, 0, 0, 0)
  const diff = fechaReserva - hoy
  return Math.ceil(diff / (1000 * 60 * 60 * 24))
}

export function calcularNoches(reserva) {
  const entrada = new Date(reserva.fecha_entrada)
  const salida = new Date(reserva.fecha_salida)
  const diff = salida - entrada
  return Math.ceil(diff / (1000 * 60 * 60 * 24))
}

export function formatearCelda(valor, tipo) {
  if (!valor) return '-'

  switch (tipo) {
    case 'fecha':
      return new Date(valor).toLocaleDateString('es-CO')
    case 'precio':
      return '$' + new Intl.NumberFormat('es-CO').format(valor)
    case 'porcentaje':
      return valor + '%'
    default:
      return valor
  }
}
