export function formatCurrency(value) {
  // Si el valor es un string con formato 'COP 100.000', extraer el número
  if (typeof value === 'string') {
    // Eliminar 'COP', puntos, comas y espacios
    const cleanValue = value.replace('COP', '').replace(/\./g, '').replace(/,/g, '').trim()
    value = parseFloat(cleanValue)
  }

  // Si no es un número válido, retornar 0 formateado
  if (isNaN(value) || value === null || value === undefined) {
    value = 0
  }

  // Formatear el número
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(value)
}
