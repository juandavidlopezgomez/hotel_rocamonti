<template>
  <div class="p-6">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900">Configuración del Sistema</h1>
      <p class="mt-2 text-sm text-gray-600">Gestiona la configuración general del hotel</p>
    </div>

    <!-- Tabs -->
    <BaseTabs v-model="tabActivo" :tabs="tabs" class="mb-6">
      <!-- General -->
      <template #general>
        <div class="space-y-6">
          <BaseCard>
            <h3 class="text-lg font-medium mb-4">Información del Hotel</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <BaseInput
                v-model="config.general.nombre_hotel"
                label="Nombre del Hotel"
              />
              <BaseInput
                v-model="config.general.email_contacto"
                type="email"
                label="Email de Contacto"
              />
              <BaseInput
                v-model="config.general.telefono"
                label="Teléfono"
              />
              <BaseInput
                v-model="config.general.direccion"
                label="Dirección"
              />
              <BaseSelect
                v-model="config.general.moneda"
                label="Moneda"
                :options="monedasOptions"
              />
              <BaseSelect
                v-model="config.general.zona_horaria"
                label="Zona Horaria"
                :options="zonasHorariasOptions"
              />
            </div>
            <div class="mt-4 flex justify-end">
              <BaseButton @click="guardarConfiguracion('general')" variant="primary">
                Guardar Cambios
              </BaseButton>
            </div>
          </BaseCard>

          <BaseCard>
            <h3 class="text-lg font-medium mb-4">Configuración de Reservas</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <BaseInput
                v-model="config.reservas.check_in_hora"
                type="time"
                label="Hora Check-in"
              />
              <BaseInput
                v-model="config.reservas.check_out_hora"
                type="time"
                label="Hora Check-out"
              />
              <BaseInput
                v-model.number="config.reservas.dias_anticipacion_max"
                type="number"
                label="Días Máx. Anticipación"
              />
              <BaseInput
                v-model.number="config.reservas.dias_anticipacion_min"
                type="number"
                label="Días Mín. Anticipación"
              />
              <BaseInput
                v-model.number="config.reservas.noches_minimas"
                type="number"
                label="Noches Mínimas"
              />
              <BaseInput
                v-model.number="config.reservas.noches_maximas"
                type="number"
                label="Noches Máximas"
              />
            </div>
            <div class="mt-4 flex justify-end">
              <BaseButton @click="guardarConfiguracion('reservas')" variant="primary">
                Guardar Cambios
              </BaseButton>
            </div>
          </BaseCard>
        </div>
      </template>

      <!-- Pagos -->
      <template #pagos>
        <div class="space-y-6">
          <BaseCard>
            <h3 class="text-lg font-medium mb-4">Configuración de Wompi</h3>

            <!-- Estado de conexión -->
            <div class="mb-4">
              <BaseAlert :variant="wompiStatus.configurado ? 'success' : 'warning'">
                {{ wompiStatus.message || 'Sin probar' }}
              </BaseAlert>
            </div>

            <div class="space-y-4">
              <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                <div>
                  <p class="font-medium">Estado de Wompi</p>
                  <p class="text-sm text-gray-600">{{ wompiStatus.configurado ? 'Configurado' : 'No configurado' }}</p>
                </div>
                <BaseButton @click="testWompi" variant="primary" :loading="testingWompi">
                  Test Conexión
                </BaseButton>
              </div>

              <div v-if="wompiStatus.detalles" class="space-y-2 p-3 bg-gray-50 rounded text-sm">
                <p><strong>URL:</strong> {{ wompiStatus.detalles.url_base }}</p>
                <p><strong>Public Key:</strong> {{ wompiStatus.detalles.public_key_preview }}</p>
                <p v-if="wompiStatus.detalles.merchant_status">
                  <strong>Merchant:</strong> {{ wompiStatus.detalles.merchant_status }}
                </p>
              </div>
            </div>
          </BaseCard>

          <BaseCard>
            <h3 class="text-lg font-medium mb-4">Métodos de Pago</h3>
            <div class="space-y-3">
              <div class="flex items-center justify-between p-3 border rounded">
                <label class="flex items-center gap-2">
                  <input type="checkbox" v-model="config.pagos.wompi_habilitado" class="form-checkbox">
                  <span>Wompi (Tarjetas y PSE)</span>
                </label>
              </div>
              <div class="flex items-center justify-between p-3 border rounded">
                <span>Efectivo</span>
                <span class="text-green-600 font-medium">Activo</span>
              </div>
              <div class="flex items-center justify-between p-3 border rounded">
                <span>Transferencia</span>
                <span class="text-green-600 font-medium">Activo</span>
              </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-4">
              <div class="flex items-center gap-2">
                <input type="checkbox" v-model="config.pagos.requiere_prepago" class="form-checkbox">
                <label>Requiere Prepago</label>
              </div>
              <BaseInput
                v-if="config.pagos.requiere_prepago"
                v-model.number="config.pagos.porcentaje_prepago"
                type="number"
                label="% Prepago"
                min="0"
                max="100"
              />
            </div>

            <div class="mt-4 flex justify-end">
              <BaseButton @click="guardarConfiguracion('pagos')" variant="primary">
                Guardar Cambios
              </BaseButton>
            </div>
          </BaseCard>
        </div>
      </template>

      <!-- Políticas -->
      <template #politicas>
        <div class="space-y-6">
          <BaseCard v-for="(politica, key) in politicas" :key="key">
            <h3 class="text-lg font-medium mb-4">{{ politica.titulo }}</h3>
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                <textarea
                  v-model="politica.descripcion"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                ></textarea>
              </div>

              <!-- Campos específicos según tipo de política -->
              <div v-if="key === 'cancelacion'" class="grid grid-cols-2 gap-4">
                <BaseInput
                  v-model.number="politica.dias_cancelacion_gratuita"
                  type="number"
                  label="Días Cancelación Gratuita"
                />
                <BaseInput
                  v-model.number="politica.porcentaje_penalizacion"
                  type="number"
                  label="% Penalización"
                />
              </div>

              <div v-if="key === 'mascotas'" class="grid grid-cols-2 gap-4">
                <div class="flex items-center gap-2">
                  <input type="checkbox" v-model="politica.permitidas" class="form-checkbox">
                  <label>Mascotas Permitidas</label>
                </div>
                <BaseInput
                  v-if="politica.permitidas"
                  v-model.number="politica.cargo_adicional"
                  type="number"
                  label="Cargo Adicional"
                />
              </div>
            </div>
            <div class="mt-4 flex justify-end">
              <BaseButton @click="guardarPolitica(key)" variant="primary">
                Guardar Política
              </BaseButton>
            </div>
          </BaseCard>
        </div>
      </template>

      <!-- Respaldos -->
      <template #respaldos>
        <div class="space-y-6">
          <BaseCard>
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium">Respaldos de Base de Datos</h3>
              <BaseButton @click="crearBackup" variant="primary" :loading="creandoBackup">
                Crear Respaldo
              </BaseButton>
            </div>

            <div v-if="backups.length > 0" class="space-y-2">
              <div
                v-for="backup in backups"
                :key="backup.nombre"
                class="flex items-center justify-between p-3 border rounded hover:bg-gray-50"
              >
                <div>
                  <p class="font-medium">{{ backup.nombre }}</p>
                  <p class="text-sm text-gray-600">{{ backup.fecha }} - {{ backup.tamano_legible }}</p>
                </div>
                <BaseButton
                  size="sm"
                  variant="ghost"
                  @click="restaurarBackup(backup.nombre)"
                >
                  Restaurar
                </BaseButton>
              </div>
            </div>
            <div v-else class="text-center py-8 text-gray-500">
              No hay respaldos disponibles
            </div>
          </BaseCard>

          <BaseCard>
            <h3 class="text-lg font-medium mb-4">Mantenimiento del Sistema</h3>
            <div class="space-y-3">
              <div class="flex items-center justify-between p-3 border rounded">
                <div>
                  <p class="font-medium">Limpiar Caché</p>
                  <p class="text-sm text-gray-600">Limpia la caché del sistema</p>
                </div>
                <BaseButton @click="limpiarCache" variant="ghost" :loading="limpiandoCache">
                  Limpiar
                </BaseButton>
              </div>
            </div>
          </BaseCard>

          <BaseCard v-if="systemInfo">
            <h3 class="text-lg font-medium mb-4">Información del Sistema</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <p class="text-gray-600">Versión PHP</p>
                <p class="font-medium">{{ systemInfo.info?.php_version }}</p>
              </div>
              <div>
                <p class="text-gray-600">Versión Laravel</p>
                <p class="font-medium">{{ systemInfo.info?.laravel_version }}</p>
              </div>
              <div>
                <p class="text-gray-600">Entorno</p>
                <p class="font-medium">{{ systemInfo.info?.environment }}</p>
              </div>
              <div>
                <p class="text-gray-600">Base de Datos</p>
                <p class="font-medium">{{ systemInfo.info?.database?.driver }}</p>
              </div>
            </div>
          </BaseCard>
        </div>
      </template>
    </BaseTabs>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import BaseTabs from '@/components/ui/BaseTabs.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseSelect from '@/components/ui/BaseSelect.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseAlert from '@/components/ui/BaseAlert.vue'

const tabActivo = ref('general')
const tabs = [
  { key: 'general', label: 'General' },
  { key: 'pagos', label: 'Pagos' },
  { key: 'politicas', label: 'Políticas' },
  { key: 'respaldos', label: 'Respaldos' }
]

const config = ref({
  general: {},
  reservas: {},
  pagos: {},
  notificaciones: {}
})

const politicas = ref({})
const backups = ref([])
const systemInfo = ref(null)
const wompiStatus = ref({})

const testingWompi = ref(false)
const creandoBackup = ref(false)
const limpiandoCache = ref(false)

const monedasOptions = [
  { label: 'COP - Peso Colombiano', value: 'COP' },
  { label: 'USD - Dólar', value: 'USD' }
]

const zonasHorariasOptions = [
  { label: 'America/Bogota', value: 'America/Bogota' },
  { label: 'America/New_York', value: 'America/New_York' }
]

async function cargarConfiguracion() {
  const response = await api.get('/admin/configuracion')
  config.value = response.data.configuracion
}

async function cargarPoliticas() {
  const response = await api.get('/admin/configuracion/politicas')
  politicas.value = response.data.politicas
}

async function cargarBackups() {
  const response = await api.get('/admin/configuracion/backups')
  backups.value = response.data.backups
}

async function cargarSystemInfo() {
  const response = await api.get('/admin/configuracion/sistema/info')
  systemInfo.value = response.data
}

async function guardarConfiguracion(seccion) {
  await api.put('/admin/configuracion', {
    seccion,
    datos: config.value[seccion]
  })
  alert('Configuración guardada exitosamente')
}

async function guardarPolitica(tipo) {
  await api.put('/admin/configuracion/politicas', {
    tipo,
    datos: politicas.value[tipo]
  })
  alert('Política guardada exitosamente')
}

async function testWompi() {
  testingWompi.value = true
  try {
    const response = await api.get('/admin/configuracion/wompi/test')
    wompiStatus.value = response.data
  } finally {
    testingWompi.value = false
  }
}

async function crearBackup() {
  if (!confirm('¿Crear un nuevo respaldo de la base de datos?')) return

  creandoBackup.value = true
  try {
    await api.post('/admin/configuracion/backups/crear')
    await cargarBackups()
    alert('Respaldo creado exitosamente')
  } finally {
    creandoBackup.value = false
  }
}

async function restaurarBackup(nombre) {
  if (!confirm('¿ADVERTENCIA: Restaurar este respaldo sobrescribirá todos los datos actuales. ¿Continuar?')) return

  await api.post('/admin/configuracion/backups/restaurar', { nombre })
  alert('Respaldo restaurado exitosamente')
}

async function limpiarCache() {
  limpiandoCache.value = true
  try {
    await api.post('/admin/configuracion/cache/limpiar')
    alert('Caché limpiado exitosamente')
  } finally {
    limpiandoCache.value = false
  }
}

onMounted(() => {
  cargarConfiguracion()
  cargarPoliticas()
  cargarBackups()
  cargarSystemInfo()
})
</script>
