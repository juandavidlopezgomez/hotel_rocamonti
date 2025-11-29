<template>
  <div>
    <!-- Tab Headers -->
    <div :class="['border-b border-gray-200', headerClass]">
      <nav :class="['-mb-px flex', variant === 'pills' ? 'space-x-2' : 'space-x-8']" aria-label="Tabs">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          :class="[
            'whitespace-nowrap py-4 px-1 font-medium text-sm transition-all duration-200',
            variant === 'pills' ? getPillClasses(tab) : getUnderlineClasses(tab),
            tab.disabled && 'opacity-50 cursor-not-allowed'
          ]"
          :disabled="tab.disabled"
          @click="!tab.disabled && selectTab(tab.id)"
        >
          <div class="flex items-center space-x-2">
            <!-- Icono -->
            <component
              v-if="tab.icon"
              :is="tab.icon"
              :class="['w-5 h-5', activeTab === tab.id ? 'text-blue-600' : 'text-gray-500']"
            />

            <!-- Label -->
            <span>{{ tab.label }}</span>

            <!-- Badge -->
            <BaseBadge
              v-if="tab.badge"
              :variant="activeTab === tab.id ? 'primary' : 'secondary'"
              size="sm"
            >
              {{ tab.badge }}
            </BaseBadge>
          </div>
        </button>
      </nav>
    </div>

    <!-- Tab Content -->
    <div :class="['mt-4', contentClass]">
      <slot :name="`tab-${activeTab}`" :tab="currentTab">
        <!-- Contenido por defecto -->
        <div class="text-gray-500">
          No hay contenido para esta pestaña
        </div>
      </slot>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import BaseBadge from './BaseBadge.vue'

const props = defineProps({
  tabs: {
    type: Array,
    required: true,
    validator: (tabs) => tabs.every(tab => tab.id && tab.label)
  },
  modelValue: {
    type: String,
    default: null
  },
  variant: {
    type: String,
    default: 'underline',
    validator: (value) => ['underline', 'pills'].includes(value)
  },
  headerClass: {
    type: String,
    default: ''
  },
  contentClass: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue', 'tab-change'])

// Tab activo inicial
const activeTab = ref(props.modelValue || (props.tabs[0]?.id || null))

// Tab actual
const currentTab = computed(() => {
  return props.tabs.find(tab => tab.id === activeTab.value)
})

// Clases para variante underline
const getUnderlineClasses = (tab) => {
  if (activeTab.value === tab.id) {
    return 'border-b-2 border-blue-600 text-blue-600'
  }
  return 'border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
}

// Clases para variante pills
const getPillClasses = (tab) => {
  if (activeTab.value === tab.id) {
    return 'bg-blue-100 text-blue-700 rounded-lg px-4'
  }
  return 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg px-4'
}

// Seleccionar tab
const selectTab = (tabId) => {
  activeTab.value = tabId
  emit('update:modelValue', tabId)
  emit('tab-change', tabId)
}

// Sincronizar con v-model
watch(() => props.modelValue, (newValue) => {
  if (newValue !== activeTab.value) {
    activeTab.value = newValue
  }
})
</script>
