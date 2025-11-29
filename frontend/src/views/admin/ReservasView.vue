<template>
  <div class="p-6">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Gestión de Reservas</h1>
        <p class="mt-2 text-sm text-gray-600">Administra todas las reservas del hotel</p>
      </div>
      <BaseButton @click="showNuevaReservaModal = true" variant="primary">
        Nueva Reserva
      </BaseButton>
    </div>

    <!-- Filtros -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4 bg-white p-4 rounded-lg shadow">
      <BaseSelect
        v-model="filtros.estado"
        label="Estado"
        :options="estadosOptions"
        @change="cargarReservas"
      />
      <BaseInput
        v-model="filtros.fecha_desde"
        type="date"
        label="Desde"
        @change="cargarReservas"
      />
      <BaseInput
        v-model="filtros.fecha_hasta"
        type="date"
        label="Hasta"
        @change="cargarReservas"
      />
      <BaseInput
        v-model="filtros.busqueda"
        type="text"
        label="Buscar"
        placeholder="Cliente, ID..."
        @input="buscarDebounced"
      />
    </div>

    <!-- Tabla de reservas -->
    <BaseTable
      :columns="columns"
      :data="reservas"
      :loading="loading"
      searchable
    >
      <template #cell-cliente="{ row }">
        <div class="flex items-center">
          <div>
            <p class="font-medium text-gray-900">{{ row.cliente.nombre }} {{ row.cliente.apellido }}</p>
            <p class="text-sm text-gray-500">{{ row.cliente.cedula }}</p>
          </div>
        </div>
      </template>

      <template #cell-estado="{ row }">
        <BaseBadge :variant="getBadgeVariant(row.estado)">
          {{ row.estado }}
        </BaseBadge>
      </template>

      <template #cell-acciones="{ row }">
        <div class="flex gap-2">
          <BaseButton size="sm" variant="ghost" @click="verDetalle(row)">
            Ver
          </BaseButton>
          <BaseButton
            v-if="row.estado === 'pendiente'"
            size="sm"
            variant="success"
            @click="checkIn(row.id)"
          >
            Check-in
          </BaseButton>
          <BaseButton
            v-if="row.estado === 'en_curso'"
            size="sm"
            variant="warning"
            @click="checkOut(row.id)"
          >
            Check-out
          </BaseButton>
          <BaseButton
            v-if="row.estado !== 'cancelada' && row.estado !== 'completada'"
            size="sm"
            variant="danger"
            @click="cancelarReserva(row.id)"
          >
            Cancelar
          </BaseButton>
        </div>
      </template>
    </BaseTable>

    <!-- Modal Nueva Reserva -->
    <BaseModal
      v-model="showNuevaReservaModal"
      title="Nueva Reserva"
      size="lg"
      :show-footer="true"
      :loading="creandoReserva"
      confirm-text="Crear Reserva"
      @confirm="crearReserva"
      @cancel="cerrarModalNuevaReserva"
    >
      <div class="space-y-4">
        <!-- Paso 1: Datos del cliente -->
        <div v-if="paso === 1">
          <h3 class="text-lg font-medium mb-4">Datos del Cliente</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <BaseInput
              v-model="nuevaReserva.cliente_cedula"
              label="Cédula"
              required
            />
            <BaseInput
              v-model="nuevaReserva.cliente_nombre"
              label="Nombre"
              required
            />
            <BaseInput
              v-model="nuevaReserva.cliente_apellido"
              label="Apellido"
            />
            <BaseInput
              v-model="nuevaReserva.cliente_email"
              type="email"
              label="Email"
            />
            <BaseInput
              v-model="nuevaReserva.cliente_telefono"
              label="Teléfono"
            />
          </div>
        </div>

        <!-- Paso 2: Detalles de la reserva -->
        <div v-if="paso === 2">
          <h3 class="text-lg font-medium mb-4">Detalles de la Reserva</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <BaseInput
              v-model="nuevaReserva.fecha_entrada"
              type="date"
              label="Fecha Entrada"
              required
            />
            <BaseInput
              v-model="nuevaReserva.fecha_salida"
              type="date"
              label="Fecha Salida"
              required
            />
            <BaseSelect
              v-model="nuevaReserva.habitacion_id"
              label="Habitación"
              :options="habitacionesDisponibles"
              required
            />
            <BaseInput
              v-model.number="nuevaReserva.num_adultos"
              type="number"
              label="Adultos"
              min="1"
              required
            />
            <BaseInput
              v-model.number="nuevaReserva.num_ninos"
              type="number"
              label="Niños"
              min="0"
            />
          </div>
        </div>

        <!-- Navegación de pasos -->
        <div class="flex justify-between mt-6 pt-4 border-t">
          <BaseButton
            v-if="paso > 1"
            variant="ghost"
            @click="paso--"
          >
            Anterior
          </BaseButton>
          <BaseButton
            v-if="paso < 2"
            @click="paso++"
            class="ml-auto"
          >
            Siguiente
          </BaseButton>
        </div>
      </div>
    </BaseModal>

    <!-- Modal Detalle Reserva -->
    <BaseModal
      v-model="showDetalleModal"
      title="Detalle de Reserva"
      size="lg"
    >
      <div v-if="reservaSeleccionada" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <p class="text-sm text-gray-600">ID Reserva</p>
            <p class="font-medium">{{ reservaSeleccionada.id }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Estado</p>
            <BaseBadge :variant="getBadgeVariant(reservaSeleccionada.estado)">
              {{ reservaSeleccionada.estado }}
            </BaseBadge>
          </div>
          <div>
            <p class="text-sm text-gray-600">Cliente</p>
            <p class="font-medium">{{ reservaSeleccionada.cliente?.nombre }} {{ reservaSeleccionada.cliente?.apellido }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Fechas</p>
            <p class="font-medium">{{ reservaSeleccionada.fecha_entrada }} - {{ reservaSeleccionada.fecha_salida }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Precio Total</p>
            <p class="font-medium text-lg text-green-600">${{ reservaSeleccionada.precio_total?.toLocaleString() }}</p>
          </div>
        </div>
      </div>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useReservasStore } from '@/stores/reservas'
import BaseTable from '@/components/ui/BaseTable.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseSelect from '@/components/ui/BaseSelect.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import BaseBadge from '@/components/ui/BaseBadge.vue'

const reservasStore = useReservasStore()

const loading = ref(false)
const showNuevaReservaModal = ref(false)
const showDetalleModal = ref(false)
const reservaSeleccionada = ref(null)
const creandoReserva = ref(false)
const paso = ref(1)

const filtros = ref({
  estado: '',
  fecha_desde: '',
  fecha_hasta: '',
  busqueda: ''
})

const nuevaReserva = ref({
  cliente_cedula: '',
  cliente_nombre: '',
  cliente_apellido: '',
  cliente_email: '',
  cliente_telefono: '',
  habitacion_id: '',
  fecha_entrada: '',
  fecha_salida: '',
  num_adultos: 1,
  num_ninos: 0
})

const estadosOptions = [
  { label: 'Todos', value: '' },
  { label: 'Pendiente', value: 'pendiente' },
  { label: 'Pagada', value: 'pagada' },
  { label: 'En Curso', value: 'en_curso' },
  { label: 'Completada', value: 'completada' },
  { label: 'Cancelada', value: 'cancelada' }
]

const habitacionesDisponibles = ref([])

const columns = [
  { key: 'id', label: 'ID', sortable: true },
  { key: 'cliente', label: 'Cliente' },
  { key: 'fecha_entrada', label: 'Entrada', sortable: true },
  { key: 'fecha_salida', label: 'Salida', sortable: true },
  { key: 'estado', label: 'Estado' },
  { key: 'precio_total', label: 'Total' },
  { key: 'acciones', label: 'Acciones' }
]

const reservas = computed(() => reservasStore.reservas)

async function cargarReservas() {
  loading.value = true
  try {
    await reservasStore.fetchReservas(filtros.value)
  } finally {
    loading.value = false
  }
}

async function crearReserva() {
  if (paso.value < 2) {
    paso.value++
    return
  }

  creandoReserva.value = true
  try {
    await reservasStore.crearReserva(nuevaReserva.value)
    cerrarModalNuevaReserva()
    await cargarReservas()
  } finally {
    creandoReserva.value = false
  }
}

function cerrarModalNuevaReserva() {
  showNuevaReservaModal.value = false
  paso.value = 1
  nuevaReserva.value = {
    cliente_cedula: '',
    cliente_nombre: '',
    cliente_apellido: '',
    cliente_email: '',
    cliente_telefono: '',
    habitacion_id: '',
    fecha_entrada: '',
    fecha_salida: '',
    num_adultos: 1,
    num_ninos: 0
  }
}

async function checkIn(id) {
  if (confirm('¿Realizar check-in para esta reserva?')) {
    await reservasStore.checkIn(id)
    await cargarReservas()
  }
}

async function checkOut(id) {
  if (confirm('¿Realizar check-out para esta reserva?')) {
    await reservasStore.checkOut(id)
    await cargarReservas()
  }
}

async function cancelarReserva(id) {
  if (confirm('¿Está seguro de cancelar esta reserva?')) {
    await reservasStore.cancelarReserva(id)
    await cargarReservas()
  }
}

function verDetalle(reserva) {
  reservaSeleccionada.value = reserva
  showDetalleModal.value = true
}

function getBadgeVariant(estado) {
  const variants = {
    pendiente: 'warning',
    pagada: 'info',
    en_curso: 'primary',
    completada: 'success',
    cancelada: 'danger'
  }
  return variants[estado] || 'default'
}

let searchTimeout
function buscarDebounced() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    cargarReservas()
  }, 500)
}

onMounted(() => {
  cargarReservas()
})
</script>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
