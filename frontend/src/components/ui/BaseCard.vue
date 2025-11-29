<template>
  <div
    :class="[
      'bg-white rounded-lg border border-gray-200 overflow-hidden transition-all duration-200',
      shadow,
      hover && 'hover:shadow-lg hover:border-blue-300',
      clickable && 'cursor-pointer',
      padding && getPaddingClass,
      customClass
    ]"
    @click="handleClick"
  >
    <!-- Header con título y acciones -->
    <div
      v-if="title || $slots.header || $slots.actions"
      :class="[
        'px-6 py-4 border-b border-gray-200',
        headerClass
      ]"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <!-- Icono opcional -->
          <div v-if="icon" class="flex-shrink-0">
            <component :is="icon" :class="['w-5 h-5', iconColor]" />
          </div>

          <!-- Título o slot personalizado -->
          <div v-if="title || $slots.header">
            <slot name="header">
              <h3 :class="['font-semibold', titleSize]">{{ title }}</h3>
              <p v-if="subtitle" class="text-sm text-gray-500 mt-0.5">{{ subtitle }}</p>
            </slot>
          </div>
        </div>

        <!-- Acciones del header -->
        <div v-if="$slots.actions" class="flex items-center space-x-2">
          <slot name="actions"></slot>
        </div>
      </div>
    </div>

    <!-- Contenido principal -->
    <div :class="contentClass">
      <slot></slot>
    </div>

    <!-- Footer opcional -->
    <div
      v-if="$slots.footer"
      :class="[
        'px-6 py-4 bg-gray-50 border-t border-gray-200',
        footerClass
      ]"
    >
      <slot name="footer"></slot>
    </div>

    <!-- Badge de estado (opcional) -->
    <div v-if="badge" class="absolute top-4 right-4">
      <BaseBadge :variant="badgeVariant" :size="badgeSize">
        {{ badge }}
      </BaseBadge>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import BaseBadge from './BaseBadge.vue'

const props = defineProps({
  title: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  },
  icon: {
    type: Object,
    default: null
  },
  iconColor: {
    type: String,
    default: 'text-blue-600'
  },
  shadow: {
    type: String,
    default: 'shadow-soft',
    validator: (value) => ['shadow-none', 'shadow-soft', 'shadow-medium', 'shadow-strong'].includes(value)
  },
  hover: {
    type: Boolean,
    default: false
  },
  clickable: {
    type: Boolean,
    default: false
  },
  padding: {
    type: String,
    default: 'normal',
    validator: (value) => ['none', 'small', 'normal', 'large'].includes(value)
  },
  titleSize: {
    type: String,
    default: 'text-lg'
  },
  badge: {
    type: String,
    default: ''
  },
  badgeVariant: {
    type: String,
    default: 'primary'
  },
  badgeSize: {
    type: String,
    default: 'sm'
  },
  customClass: {
    type: String,
    default: ''
  },
  headerClass: {
    type: String,
    default: ''
  },
  contentClass: {
    type: String,
    default: ''
  },
  footerClass: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['click'])

const getPaddingClass = computed(() => {
  const paddingMap = {
    none: '',
    small: 'p-4',
    normal: 'p-6',
    large: 'p-8'
  }
  return paddingMap[props.padding] || paddingMap.normal
})

const handleClick = () => {
  if (props.clickable) {
    emit('click')
  }
}
</script>

<style scoped>
.shadow-soft {
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

.shadow-medium {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.shadow-strong {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
</style>
