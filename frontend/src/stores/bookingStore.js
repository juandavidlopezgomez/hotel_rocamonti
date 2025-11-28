import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../services/api'
import dayjs from 'dayjs'

export const useBookingStore = defineStore('booking', () => {
  // State
  const currentBooking = ref(null)
  const selectedDates = ref({
    fechaInicio: null,
    fechaFin: null
  })
  const selectedRoom = ref(null)
  const guestInfo = ref({
    cedula: '',
    nombre: '',
    apellido: '',
    email: '',
    celular: '',
    telefono: ''
  })
  const availableRooms = ref([])
  const loading = ref(false)
  const error = ref(null)

  // Getters
  const numberOfNights = computed(() => {
    if (!selectedDates.value.fechaInicio || !selectedDates.value.fechaFin) {
      return 0
    }
    const start = dayjs(selectedDates.value.fechaInicio)
    const end = dayjs(selectedDates.value.fechaFin)
    return end.diff(start, 'day')
  })

  const totalPrice = computed(() => {
    if (!selectedRoom.value || numberOfNights.value === 0) {
      return 0
    }
    return selectedRoom.value.precio * numberOfNights.value
  })

  // Actions
  async function searchAvailableRooms(dates, guests) {
    loading.value = true
    error.value = null

    try {
      const response = await api.post('/rooms/available', {
        fecha_inicio: dates.fechaInicio,
        fecha_fin: dates.fechaFin,
        personas: guests
      })

      if (response.data.success) {
        availableRooms.value = response.data.data
        selectedDates.value = dates
        return response.data.data
      } else {
        throw new Error(response.data.message || 'Error al buscar habitaciones')
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message || 'Error al buscar habitaciones'
      throw err
    } finally {
      loading.value = false
    }
  }

  function selectRoom(room) {
    selectedRoom.value = room
  }

  async function createBooking(guestData, totalPersonas) {
    loading.value = true
    error.value = null

    try {
      const bookingData = {
        cedula: guestData.cedula,
        nombre: guestData.nombre,
        apellido: guestData.apellido,
        email: guestData.email,
        celular: guestData.celular,
        telefono: guestData.telefono,
        habitacion_id: selectedRoom.value.idHabitacion,
        fecha_inicio: selectedDates.value.fechaInicio,
        fecha_fin: selectedDates.value.fechaFin,
        total_personas: totalPersonas
      }

      const response = await api.post('/bookings', bookingData)

      if (response.data.success) {
        currentBooking.value = response.data.data
        guestInfo.value = guestData
        return response.data.data
      } else {
        throw new Error(response.data.message || 'Error al crear la reserva')
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message || 'Error al crear la reserva'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function confirmPayment(transactionId, estado) {
    loading.value = true
    error.value = null

    try {
      const response = await api.post('/bookings/confirm-payment', {
        reserva_id: currentBooking.value.idReserva,
        transaction_id: transactionId,
        estado: estado
      })

      if (response.data.success) {
        currentBooking.value = response.data.data
        return response.data.data
      } else {
        throw new Error(response.data.message || 'Error al confirmar el pago')
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message || 'Error al confirmar el pago'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function getBookingDetails(bookingId) {
    loading.value = true
    error.value = null

    try {
      const response = await api.get(`/bookings/${bookingId}`)

      if (response.data.success) {
        currentBooking.value = response.data.data
        return response.data.data
      } else {
        throw new Error(response.data.message || 'Error al obtener la reserva')
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message || 'Error al obtener la reserva'
      throw err
    } finally {
      loading.value = false
    }
  }

  function resetBooking() {
    currentBooking.value = null
    selectedDates.value = {
      fechaInicio: null,
      fechaFin: null
    }
    selectedRoom.value = null
    guestInfo.value = {
      cedula: '',
      nombre: '',
      apellido: '',
      email: '',
      celular: '',
      telefono: ''
    }
    availableRooms.value = []
    error.value = null
  }

  return {
    // State
    currentBooking,
    selectedDates,
    selectedRoom,
    guestInfo,
    availableRooms,
    loading,
    error,
    // Getters
    numberOfNights,
    totalPrice,
    // Actions
    searchAvailableRooms,
    selectRoom,
    createBooking,
    confirmPayment,
    getBookingDetails,
    resetBooking
  }
})
