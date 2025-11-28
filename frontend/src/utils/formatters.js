/**
 * Formatea un monto a formato de moneda colombiana (COP)
 * @param {number} amount - Monto a formatear
 * @returns {string} - Formato: COP 396.000
 */
export function formatCurrency(amount) {
  const formatted = new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount)

  return `COP ${formatted}`
}

/**
 * Formatea una fecha al formato de Booking
 * @param {Date|string} date - Fecha a formatear
 * @returns {string} - Formato: mié, 12 nov
 */
export function formatDate(date) {
  const d = new Date(date)
  const dias = ['dom', 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb']
  const meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic']

  return `${dias[d.getDay()]}, ${d.getDate()} ${meses[d.getMonth()]}`
}

/**
 * Calcula el número de noches entre dos fechas
 * @param {Date|string} start - Fecha inicio
 * @param {Date|string} end - Fecha fin
 * @returns {number} - Número de noches
 */
export function calculateNights(start, end) {
  const startDate = new Date(start)
  const endDate = new Date(end)
  const diffTime = Math.abs(endDate - startDate)
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays
}
