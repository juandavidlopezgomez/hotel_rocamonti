<template>
  <div class="w-full">
    <!-- Búsqueda y acciones -->
    <div v-if="searchable || $slots.actions" class="mb-4 flex items-center justify-between gap-4">
      <!-- Búsqueda -->
      <div v-if="searchable" class="flex-1 max-w-md">
        <BaseInput
          v-model="searchQuery"
          type="text"
          placeholder="Buscar..."
          icon="search"
          @input="handleSearch"
        />
      </div>

      <!-- Acciones personalizadas -->
      <div v-if="$slots.actions" class="flex items-center gap-2">
        <slot name="actions"></slot>
      </div>
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto bg-white rounded-lg border border-gray-200 shadow-soft">
      <table class="min-w-full divide-y divide-gray-200">
        <!-- Header -->
        <thead class="bg-gray-50">
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              :class="[
                'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider',
                column.sortable && 'cursor-pointer hover:bg-gray-100 select-none',
                column.align === 'center' && 'text-center',
                column.align === 'right' && 'text-right'
              ]"
              @click="column.sortable && handleSort(column.key)"
            >
              <div class="flex items-center gap-2">
                <span>{{ column.label }}</span>

                <!-- Icono de ordenamiento -->
                <div v-if="column.sortable" class="flex flex-col">
                  <svg
                    v-if="sortColumn === column.key && sortDirection === 'asc'"
                    class="w-4 h-4 text-blue-600"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                  <svg
                    v-else-if="sortColumn === column.key && sortDirection === 'desc'"
                    class="w-4 h-4 text-blue-600"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                  </svg>
                  <svg
                    v-else
                    class="w-4 h-4 text-gray-400"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </div>
              </div>
            </th>
          </tr>
        </thead>

        <!-- Body -->
        <tbody class="bg-white divide-y divide-gray-200">
          <!-- Loading state -->
          <tr v-if="loading">
            <td :colspan="columns.length" class="px-6 py-12 text-center">
              <LoadingSpinner size="lg" />
              <p class="text-gray-500 mt-4">Cargando datos...</p>
            </td>
          </tr>

          <!-- Empty state -->
          <tr v-else-if="!paginatedData.length">
            <td :colspan="columns.length" class="px-6 py-12 text-center">
              <div class="flex flex-col items-center justify-center">
                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <p class="text-gray-500 text-lg font-medium">No hay datos disponibles</p>
                <p class="text-gray-400 text-sm mt-1">{{ emptyMessage }}</p>
              </div>
            </td>
          </tr>

          <!-- Data rows -->
          <tr
            v-else
            v-for="(row, index) in paginatedData"
            :key="getRowKey(row, index)"
            :class="[
              'transition-colors duration-150',
              striped && index % 2 === 0 && 'bg-gray-50',
              hoverable && 'hover:bg-blue-50 cursor-pointer'
            ]"
            @click="handleRowClick(row)"
          >
            <td
              v-for="column in columns"
              :key="column.key"
              :class="[
                'px-6 py-4 whitespace-nowrap',
                column.align === 'center' && 'text-center',
                column.align === 'right' && 'text-right'
              ]"
            >
              <!-- Slot personalizado por columna -->
              <slot
                :name="`cell-${column.key}`"
                :row="row"
                :value="getNestedValue(row, column.key)"
              >
                <!-- Valor por defecto -->
                <span :class="column.class || 'text-sm text-gray-900'">
                  {{ formatValue(getNestedValue(row, column.key), column.format) }}
                </span>
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <div
      v-if="paginated && !loading && totalPages > 1"
      class="mt-4 flex items-center justify-between"
    >
      <div class="text-sm text-gray-700">
        Mostrando
        <span class="font-medium">{{ startItem }}</span>
        a
        <span class="font-medium">{{ endItem }}</span>
        de
        <span class="font-medium">{{ totalItems }}</span>
        resultados
      </div>

      <div class="flex items-center space-x-2">
        <!-- Botón anterior -->
        <button
          :disabled="currentPage === 1"
          :class="[
            'px-3 py-2 rounded-md text-sm font-medium transition-colors',
            currentPage === 1
              ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
              : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
          ]"
          @click="goToPage(currentPage - 1)"
        >
          Anterior
        </button>

        <!-- Números de página -->
        <button
          v-for="page in visiblePages"
          :key="page"
          :class="[
            'px-3 py-2 rounded-md text-sm font-medium transition-colors',
            page === currentPage
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
          ]"
          @click="goToPage(page)"
        >
          {{ page }}
        </button>

        <!-- Botón siguiente -->
        <button
          :disabled="currentPage === totalPages"
          :class="[
            'px-3 py-2 rounded-md text-sm font-medium transition-colors',
            currentPage === totalPages
              ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
              : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
          ]"
          @click="goToPage(currentPage + 1)"
        >
          Siguiente
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import BaseInput from './BaseInput.vue'
import LoadingSpinner from './LoadingSpinner.vue'

const props = defineProps({
  columns: {
    type: Array,
    required: true,
    validator: (columns) => columns.every(col => col.key && col.label)
  },
  data: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  searchable: {
    type: Boolean,
    default: false
  },
  paginated: {
    type: Boolean,
    default: true
  },
  perPage: {
    type: Number,
    default: 10
  },
  striped: {
    type: Boolean,
    default: false
  },
  hoverable: {
    type: Boolean,
    default: true
  },
  emptyMessage: {
    type: String,
    default: 'No se encontraron registros'
  },
  rowKey: {
    type: String,
    default: 'id'
  }
})

const emit = defineEmits(['row-click', 'search', 'sort'])

const currentPage = ref(1)
const searchQuery = ref('')
const sortColumn = ref(null)
const sortDirection = ref('asc')

// Datos filtrados por búsqueda
const filteredData = computed(() => {
  if (!props.searchable || !searchQuery.value) {
    return props.data
  }

  const query = searchQuery.value.toLowerCase()
  return props.data.filter(row => {
    return props.columns.some(column => {
      const value = getNestedValue(row, column.key)
      return String(value).toLowerCase().includes(query)
    })
  })
})

// Datos ordenados
const sortedData = computed(() => {
  if (!sortColumn.value) {
    return filteredData.value
  }

  return [...filteredData.value].sort((a, b) => {
    const aVal = getNestedValue(a, sortColumn.value)
    const bVal = getNestedValue(b, sortColumn.value)

    let comparison = 0
    if (aVal > bVal) comparison = 1
    if (aVal < bVal) comparison = -1

    return sortDirection.value === 'asc' ? comparison : -comparison
  })
})

// Datos paginados
const paginatedData = computed(() => {
  if (!props.paginated) {
    return sortedData.value
  }

  const start = (currentPage.value - 1) * props.perPage
  const end = start + props.perPage
  return sortedData.value.slice(start, end)
})

const totalItems = computed(() => sortedData.value.length)
const totalPages = computed(() => Math.ceil(totalItems.value / props.perPage))
const startItem = computed(() => (currentPage.value - 1) * props.perPage + 1)
const endItem = computed(() => Math.min(currentPage.value * props.perPage, totalItems.value))

// Páginas visibles en la paginación
const visiblePages = computed(() => {
  const pages = []
  const maxVisible = 5
  let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2))
  let end = Math.min(totalPages.value, start + maxVisible - 1)

  if (end - start + 1 < maxVisible) {
    start = Math.max(1, end - maxVisible + 1)
  }

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  return pages
})

// Métodos
const getNestedValue = (obj, path) => {
  return path.split('.').reduce((value, key) => value?.[key], obj)
}

const formatValue = (value, format) => {
  if (!format) return value

  if (typeof format === 'function') {
    return format(value)
  }

  return value
}

const getRowKey = (row, index) => {
  return row[props.rowKey] || index
}

const handleSearch = () => {
  currentPage.value = 1
  emit('search', searchQuery.value)
}

const handleSort = (columnKey) => {
  if (sortColumn.value === columnKey) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortColumn.value = columnKey
    sortDirection.value = 'asc'
  }

  currentPage.value = 1
  emit('sort', { column: columnKey, direction: sortDirection.value })
}

const handleRowClick = (row) => {
  emit('row-click', row)
}

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
  }
}

// Reset página cuando cambian los datos
watch(() => props.data, () => {
  currentPage.value = 1
})
</script>
