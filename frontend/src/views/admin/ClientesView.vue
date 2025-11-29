<template>
  <div class="p-6">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900">Gestión de Clientes</h1>
      <p class="mt-2 text-sm text-gray-600">Administra la base de datos de clientes</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <BaseCard>
        <div class="text-center">
          <p class="text-3xl font-bold text-gray-900">{{ stats.total }}</p>
          <p class="text-sm text-gray-600">Total Clientes</p>
        </div>
      </BaseCard>
      <BaseCard>
        <div class="text-center">
          <p class="text-3xl font-bold text-green-600">{{ stats.clientes_vip }}</p>
          <p class="text-sm text-gray-600">Clientes VIP</p>
        </div>
      </BaseCard>
      <BaseCard>
        <div class="text-center">
          <p class="text-3xl font-bold text-blue-600">{{ stats.con_reservas }}</p>
          <p class="text-sm text-gray-600">Con Reservas</p>
        </div>
      </BaseCard>
      <BaseCard>
        <div class="text-center">
          <p class="text-3xl font-bold text-purple-600">${{ stats.ingresos_totales?.toLocaleString() }}</p>
          <p class="text-sm text-gray-600">Ingresos Totales</p>
        </div>
      </BaseCard>
    </div>

    <!-- Tabla de clientes -->
    <BaseTable
      :columns="columns"
      :data="clientes"
      :loading="loading"
      searchable
    >
      <template #cell-nombre="{ row }">
        <div class="flex items-center gap-2">
          <div>
            <p class="font-medium text-gray-900">{{ row.nombre }} {{ row.apellido }}</p>
            <p class="text-sm text-gray-500">{{ row.cedula }}</p>
          </div>
          <BaseBadge v-if="row.vip_status?.es_vip" variant="warning" size="sm">
            {{ row.vip_status.nivel }}
          </BaseBadge>
        </div>
      </template>

      <template #cell-contacto="{ row }">
        <div>
          <p class="text-sm">{{ row.email }}</p>
          <p class="text-sm text-gray-500">{{ row.telefono }}</p>
        </div>
      </template>

      <template #cell-actividad="{ row }">
        <div>
          <p class="font-medium">{{ row.num_reservas }} reservas</p>
          <p class="text-sm text-green-600">${{ row.total_gastado?.toLocaleString() }}</p>
        </div>
      </template>

      <template #cell-acciones="{ row }">
        <div class="flex gap-2">
          <BaseButton size="sm" variant="ghost" @click="verPerfil(row)">
            Ver Perfil
          </BaseButton>
          <BaseButton size="sm" variant="primary" @click="verHistorial(row)">
            Historial
          </BaseButton>
        </div>
      </template>
    </BaseTable>

    <!-- Modal Perfil Cliente -->
    <BaseModal
      v-model="showPerfilModal"
      title="Perfil de Cliente"
      size="lg"
    >
      <div v-if="clienteSeleccionado" class="space-y-6">
        <!-- Información básica -->
        <div>
          <h3 class="text-lg font-medium mb-3">Información Personal</h3>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-600">Nombre Completo</p>
              <p class="font-medium">{{ clienteSeleccionado.nombre }} {{ clienteSeleccionado.apellido }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Cédula</p>
              <p class="font-medium">{{ clienteSeleccionado.cedula }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Email</p>
              <p class="font-medium">{{ clienteSeleccionado.email }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Teléfono</p>
              <p class="font-medium">{{ clienteSeleccionado.telefono }}</p>
            </div>
          </div>
        </div>

        <!-- Estado VIP -->
        <div v-if="clienteSeleccionado.vip_status?.es_vip" class="border-t pt-4">
          <h3 class="text-lg font-medium mb-3">Estado VIP</h3>
          <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
              <p class="font-bold text-yellow-900">Nivel {{ clienteSeleccionado.vip_status.nivel }}</p>
              <BaseBadge variant="warning">VIP</BaseBadge>
            </div>
            <div class="space-y-2">
              <p class="text-sm"><strong>Reservas:</strong> {{ clienteSeleccionado.vip_status.num_reservas }}</p>
              <p class="text-sm"><strong>Total Gastado:</strong> ${{ clienteSeleccionado.vip_status.total_gastado?.toLocaleString() }}</p>
              <div>
                <p class="text-sm font-medium mb-1">Beneficios:</p>
                <ul class="text-sm list-disc list-inside space-y-1">
                  <li v-for="beneficio in clienteSeleccionado.vip_status.beneficios" :key="beneficio">
                    {{ beneficio }}
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Estadísticas -->
        <div class="border-t pt-4">
          <h3 class="text-lg font-medium mb-3">Estadísticas</h3>
          <div class="grid grid-cols-3 gap-4">
            <div class="text-center p-3 bg-gray-50 rounded">
              <p class="text-2xl font-bold">{{ clienteSeleccionado.num_reservas }}</p>
              <p class="text-xs text-gray-600">Reservas</p>
            </div>
            <div class="text-center p-3 bg-gray-50 rounded">
              <p class="text-2xl font-bold text-green-600">${{ clienteSeleccionado.total_gastado?.toLocaleString() }}</p>
              <p class="text-xs text-gray-600">Total Gastado</p>
            </div>
            <div class="text-center p-3 bg-gray-50 rounded">
              <p class="text-sm font-medium">{{ clienteSeleccionado.ultima_reserva }}</p>
              <p class="text-xs text-gray-600">Última Reserva</p>
            </div>
          </div>
        </div>
      </div>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useClientesStore } from '@/stores/clientes'
import BaseTable from '@/components/ui/BaseTable.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseBadge from '@/components/ui/BaseBadge.vue'

const clientesStore = useClientesStore()

const clientes = ref([])
const stats = ref({ total: 0, clientes_vip: 0, con_reservas: 0, ingresos_totales: 0 })
const loading = ref(false)
const showPerfilModal = ref(false)
const clienteSeleccionado = ref(null)

const columns = [
  { key: 'nombre', label: 'Cliente' },
  { key: 'contacto', label: 'Contacto' },
  { key: 'actividad', label: 'Actividad' },
  { key: 'acciones', label: 'Acciones' }
]

async function cargarClientes() {
  loading.value = true
  try {
    clientes.value = await clientesStore.fetchClientes()
    stats.value = await clientesStore.fetchStats()
  } finally {
    loading.value = false
  }
}

async function verPerfil(cliente) {
  clienteSeleccionado.value = await clientesStore.fetchCliente(cliente.cedula)
  showPerfilModal.value = true
}

function verHistorial(cliente) {
  // Navegar a vista de historial o mostrar modal
  console.log('Ver historial de', cliente)
}

onMounted(() => {
  cargarClientes()
})
</script>
