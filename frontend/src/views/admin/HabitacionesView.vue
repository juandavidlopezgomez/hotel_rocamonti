<template>
  <div class="p-6">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Gestión de Habitaciones</h1>
        <p class="mt-2 text-sm text-gray-600">Administra el inventario de habitaciones</p>
      </div>
      <BaseButton @click="showCrearModal = true" variant="primary">
        Nueva Habitación
      </BaseButton>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
      <BaseCard>
        <div class="text-center">
          <p class="text-3xl font-bold text-gray-900">{{ stats.total }}</p>
          <p class="text-sm text-gray-600">Total</p>
        </div>
      </BaseCard>
      <BaseCard>
        <div class="text-center">
          <p class="text-3xl font-bold text-green-600">{{ stats.disponibles }}</p>
          <p class="text-sm text-gray-600">Disponibles</p>
        </div>
      </BaseCard>
      <BaseCard>
        <div class="text-center">
          <p class="text-3xl font-bold text-red-600">{{ stats.ocupadas }}</p>
          <p class="text-sm text-gray-600">Ocupadas</p>
        </div>
      </BaseCard>
      <BaseCard>
        <div class="text-center">
          <p class="text-3xl font-bold text-yellow-600">{{ stats.limpieza }}</p>
          <p class="text-sm text-gray-600">Limpieza</p>
        </div>
      </BaseCard>
      <BaseCard>
        <div class="text-center">
          <p class="text-3xl font-bold text-orange-600">{{ stats.mantenimiento }}</p>
          <p class="text-sm text-gray-600">Mantenimiento</p>
        </div>
      </BaseCard>
    </div>

    <!-- Filtros -->
    <div class="mb-6 flex gap-4 bg-white p-4 rounded-lg shadow">
      <BaseSelect
        v-model="filtroEstado"
        label="Filtrar por Estado"
        :options="estadosOptions"
        @change="cargarHabitaciones"
      />
    </div>

    <!-- Grid de habitaciones -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <BaseCard
        v-for="habitacion in habitaciones"
        :key="habitacion.id"
        :class="getCardClass(habitacion.estado)"
        class="cursor-pointer hover:shadow-lg transition"
        @click="verDetalle(habitacion)"
      >
        <div class="text-center">
          <p class="text-2xl font-bold text-gray-900">{{ habitacion.numero }}</p>
          <p class="text-sm text-gray-600">Piso {{ habitacion.piso }}</p>
          <p class="text-sm font-medium mt-2">{{ habitacion.tipo.nombre }}</p>
          <BaseBadge :variant="getEstadoVariant(habitacion.estado)" class="mt-2">
            {{ habitacion.estado }}
          </BaseBadge>

          <div v-if="habitacion.reserva_actual" class="mt-3 pt-3 border-t text-xs">
            <p class="text-gray-600">Ocupada por:</p>
            <p class="font-medium">{{ habitacion.reserva_actual.cliente }}</p>
          </div>
        </div>
      </BaseCard>
    </div>

    <!-- Modal Crear/Editar -->
    <BaseModal
      v-model="showCrearModal"
      :title="habitacionEditar ? 'Editar Habitación' : 'Nueva Habitación'"
      @confirm="guardarHabitacion"
      @cancel="cerrarModal"
    >
      <div class="space-y-4">
        <BaseInput
          v-model="formHabitacion.numero_habitacion"
          label="Número de Habitación"
          required
        />
        <BaseSelect
          v-model="formHabitacion.tipo_habitacion_id"
          label="Tipo de Habitación"
          :options="tiposHabitacion"
          required
        />
        <BaseInput
          v-model.number="formHabitacion.piso"
          type="number"
          label="Piso"
          min="1"
          required
        />
        <BaseSelect
          v-model="formHabitacion.estado"
          label="Estado"
          :options="estadosOptions"
        />
      </div>
    </BaseModal>

    <!-- Modal Detalle -->
    <BaseModal
      v-model="showDetalleModal"
      title="Detalle de Habitación"
      size="lg"
    >
      <div v-if="habitacionSeleccionada" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <p class="text-sm text-gray-600">Número</p>
            <p class="font-medium text-2xl">{{ habitacionSeleccionada.numero }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Estado</p>
            <BaseBadge :variant="getEstadoVariant(habitacionSeleccionada.estado)">
              {{ habitacionSeleccionada.estado }}
            </BaseBadge>
          </div>
          <div>
            <p class="text-sm text-gray-600">Tipo</p>
            <p class="font-medium">{{ habitacionSeleccionada.tipo?.nombre }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Precio Base</p>
            <p class="font-medium">${{ habitacionSeleccionada.tipo?.precio_base?.toLocaleString() }}</p>
          </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="pt-4 border-t space-y-2">
          <h3 class="font-medium mb-2">Acciones</h3>
          <div class="grid grid-cols-2 gap-2">
            <BaseButton
              v-if="habitacionSeleccionada.estado === 'limpieza'"
              variant="success"
              size="sm"
              @click="marcarLimpia(habitacionSeleccionada.id)"
            >
              Marcar como Limpia
            </BaseButton>
            <BaseButton
              variant="warning"
              size="sm"
              @click="cambiarEstado(habitacionSeleccionada, 'mantenimiento')"
            >
              En Mantenimiento
            </BaseButton>
            <BaseButton
              variant="danger"
              size="sm"
              @click="bloquearHabitacion(habitacionSeleccionada.id)"
            >
              Bloquear
            </BaseButton>
            <BaseButton
              variant="ghost"
              size="sm"
              @click="editarHabitacion(habitacionSeleccionada)"
            >
              Editar
            </BaseButton>
          </div>
        </div>
      </div>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useHabitacionesStore } from '@/stores/habitaciones'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseSelect from '@/components/ui/BaseSelect.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import BaseBadge from '@/components/ui/BaseBadge.vue'

const habitacionesStore = useHabitacionesStore()

const habitaciones = ref([])
const stats = ref({ total: 0, disponibles: 0, ocupadas: 0, limpieza: 0, mantenimiento: 0 })
const showCrearModal = ref(false)
const showDetalleModal = ref(false)
const habitacionSeleccionada = ref(null)
const habitacionEditar = ref(null)
const filtroEstado = ref('')

const formHabitacion = ref({
  numero_habitacion: '',
  tipo_habitacion_id: '',
  piso: 1,
  estado: 'disponible'
})

const estadosOptions = [
  { label: 'Todos', value: '' },
  { label: 'Disponible', value: 'disponible' },
  { label: 'Ocupada', value: 'ocupada' },
  { label: 'Limpieza', value: 'limpieza' },
  { label: 'Mantenimiento', value: 'mantenimiento' },
  { label: 'Bloqueada', value: 'bloqueada' }
]

const tiposHabitacion = ref([
  { label: 'Habitación Sencilla', value: 1 },
  { label: 'Habitación Doble', value: 2 },
  { label: 'Apartamento', value: 3 }
])

async function cargarHabitaciones() {
  const filtros = filtroEstado.value ? { estado: filtroEstado.value } : {}
  habitaciones.value = await habitacionesStore.fetchHabitaciones(filtros)
  await cargarStats()
}

async function cargarStats() {
  stats.value = await habitacionesStore.fetchStats()
}

function verDetalle(habitacion) {
  habitacionSeleccionada.value = habitacion
  showDetalleModal.value = true
}

function editarHabitacion(habitacion) {
  habitacionEditar.value = habitacion
  formHabitacion.value = { ...habitacion }
  showDetalleModal.value = false
  showCrearModal.value = true
}

async function guardarHabitacion() {
  if (habitacionEditar.value) {
    await habitacionesStore.updateHabitacion(habitacionEditar.value.id, formHabitacion.value)
  } else {
    await habitacionesStore.crearHabitacion(formHabitacion.value)
  }
  cerrarModal()
  await cargarHabitaciones()
}

function cerrarModal() {
  showCrearModal.value = false
  habitacionEditar.value = null
  formHabitacion.value = {
    numero_habitacion: '',
    tipo_habitacion_id: '',
    piso: 1,
    estado: 'disponible'
  }
}

async function cambiarEstado(habitacion, nuevoEstado) {
  await habitacionesStore.cambiarEstado(habitacion.id, nuevoEstado)
  await cargarHabitaciones()
  showDetalleModal.value = false
}

async function marcarLimpia(id) {
  await habitacionesStore.marcarLimpia(id)
  await cargarHabitaciones()
  showDetalleModal.value = false
}

async function bloquearHabitacion(id) {
  const motivo = prompt('Motivo del bloqueo:')
  if (motivo) {
    await habitacionesStore.bloquear(id, motivo)
    await cargarHabitaciones()
    showDetalleModal.value = false
  }
}

function getCardClass(estado) {
  const classes = {
    disponible: 'border-green-200 bg-green-50',
    ocupada: 'border-red-200 bg-red-50',
    limpieza: 'border-yellow-200 bg-yellow-50',
    mantenimiento: 'border-orange-200 bg-orange-50',
    bloqueada: 'border-gray-200 bg-gray-50'
  }
  return classes[estado] || ''
}

function getEstadoVariant(estado) {
  const variants = {
    disponible: 'success',
    ocupada: 'danger',
    limpieza: 'warning',
    mantenimiento: 'info',
    bloqueada: 'default'
  }
  return variants[estado] || 'default'
}

onMounted(() => {
  cargarHabitaciones()
})
</script>
