<template>
  <Menu as="div" :class="['relative inline-block text-left', customClass]">
    <!-- Botón trigger -->
    <MenuButton
      :class="[
        'inline-flex items-center justify-center w-full rounded-md px-4 py-2 text-sm font-medium transition-colors',
        variant === 'primary' && 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500',
        variant === 'secondary' && 'bg-gray-100 text-gray-700 hover:bg-gray-200 focus:ring-2 focus:ring-gray-400',
        variant === 'ghost' && 'bg-transparent text-gray-700 hover:bg-gray-100 focus:ring-2 focus:ring-gray-400',
        variant === 'danger' && 'bg-red-600 text-white hover:bg-red-700 focus:ring-2 focus:ring-red-500',
        buttonClass
      ]"
    >
      <slot name="button">
        <span>{{ label }}</span>
        <ChevronDownIcon class="ml-2 -mr-1 h-5 w-5" aria-hidden="true" />
      </slot>
    </MenuButton>

    <!-- Panel del dropdown -->
    <Transition
      enter-active-class="transition duration-100 ease-out"
      enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100"
      leave-active-class="transition duration-75 ease-in"
      leave-from-class="transform scale-100 opacity-100"
      leave-to-class="transform scale-95 opacity-0"
    >
      <MenuItems
        :class="[
          'absolute mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none divide-y divide-gray-100 z-50',
          align === 'left' && 'left-0 origin-top-left',
          align === 'right' && 'right-0 origin-top-right',
          menuClass
        ]"
      >
        <!-- Header del dropdown -->
        <div v-if="$slots.header" class="px-4 py-3">
          <slot name="header"></slot>
        </div>

        <!-- Contenido del dropdown -->
        <div class="py-1">
          <MenuItem
            v-for="item in items"
            :key="item.id || item.label"
            v-slot="{ active }"
            :disabled="item.disabled"
          >
            <button
              v-if="!item.href"
              :class="[
                active ? 'bg-gray-100 text-gray-900' : 'text-gray-700',
                item.disabled && 'opacity-50 cursor-not-allowed',
                item.danger && 'text-red-600',
                'group flex items-center w-full px-4 py-2 text-sm transition-colors'
              ]"
              :disabled="item.disabled"
              @click="handleItemClick(item)"
            >
              <component
                v-if="item.icon"
                :is="item.icon"
                :class="[
                  active ? 'text-gray-500' : 'text-gray-400',
                  item.danger && 'text-red-500',
                  'mr-3 h-5 w-5'
                ]"
                aria-hidden="true"
              />
              <span class="flex-1">{{ item.label }}</span>

              <BaseBadge
                v-if="item.badge"
                :variant="item.badgeVariant || 'secondary'"
                size="sm"
                class="ml-2"
              >
                {{ item.badge }}
              </BaseBadge>
            </button>

            <a
              v-else
              :href="item.href"
              :target="item.target || '_self'"
              :class="[
                active ? 'bg-gray-100 text-gray-900' : 'text-gray-700',
                item.disabled && 'opacity-50 cursor-not-allowed',
                item.danger && 'text-red-600',
                'group flex items-center px-4 py-2 text-sm transition-colors'
              ]"
            >
              <component
                v-if="item.icon"
                :is="item.icon"
                :class="[
                  active ? 'text-gray-500' : 'text-gray-400',
                  item.danger && 'text-red-500',
                  'mr-3 h-5 w-5'
                ]"
                aria-hidden="true"
              />
              <span class="flex-1">{{ item.label }}</span>

              <BaseBadge
                v-if="item.badge"
                :variant="item.badgeVariant || 'secondary'"
                size="sm"
                class="ml-2"
              >
                {{ item.badge }}
              </BaseBadge>
            </a>
          </MenuItem>
        </div>

        <!-- Footer del dropdown -->
        <div v-if="$slots.footer" class="px-4 py-3">
          <slot name="footer"></slot>
        </div>
      </MenuItems>
    </Transition>
  </Menu>
</template>

<script setup>
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import { ChevronDownIcon } from '@heroicons/vue/24/outline'
import BaseBadge from './BaseBadge.vue'

const props = defineProps({
  label: {
    type: String,
    default: 'Opciones'
  },
  items: {
    type: Array,
    default: () => []
  },
  variant: {
    type: String,
    default: 'secondary',
    validator: (value) => ['primary', 'secondary', 'ghost', 'danger'].includes(value)
  },
  align: {
    type: String,
    default: 'right',
    validator: (value) => ['left', 'right'].includes(value)
  },
  customClass: {
    type: String,
    default: ''
  },
  buttonClass: {
    type: String,
    default: ''
  },
  menuClass: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['item-click'])

const handleItemClick = (item) => {
  if (!item.disabled && item.onClick) {
    item.onClick()
  }
  emit('item-click', item)
}
</script>
