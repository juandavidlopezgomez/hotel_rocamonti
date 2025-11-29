<template>
  <TransitionRoot :show="isOpen" as="template">
    <Dialog as="div" class="relative z-50" @close="handleClose">
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel :class="modalClasses">
              <!-- Header -->
              <div v-if="showHeader" class="border-b border-gray-200 pb-4 mb-4">
                <div class="flex items-start justify-between">
                  <div class="flex items-center gap-3">
                    <div
                      v-if="icon"
                      :class="iconContainerClasses"
                    >
                      <component :is="icon" class="h-6 w-6" />
                    </div>
                    <div class="text-left">
                      <DialogTitle as="h3" class="text-xl font-semibold text-gray-900">
                        {{ title }}
                      </DialogTitle>
                      <p v-if="description" class="mt-1 text-sm text-gray-500">
                        {{ description }}
                      </p>
                    </div>
                  </div>
                  <button
                    v-if="showCloseButton"
                    type="button"
                    class="rounded-lg p-1.5 text-gray-400 hover:text-gray-500 hover:bg-gray-100 transition-colors"
                    @click="handleClose"
                  >
                    <XMarkIcon class="h-5 w-5" />
                  </button>
                </div>
              </div>

              <!-- Content -->
              <div class="mt-2">
                <slot></slot>
              </div>

              <!-- Footer -->
              <div v-if="showFooter" class="mt-6 pt-4 border-t border-gray-200 flex justify-end gap-3">
                <slot name="footer">
                  <BaseButton
                    v-if="showCancelButton"
                    variant="ghost"
                    @click="handleCancel"
                  >
                    {{ cancelText }}
                  </BaseButton>
                  <BaseButton
                    v-if="showConfirmButton"
                    :variant="confirmVariant"
                    :loading="loading"
                    @click="handleConfirm"
                  >
                    {{ confirmText }}
                  </BaseButton>
                </slot>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { computed } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import BaseButton from './BaseButton.vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  title: {
    type: String,
    default: ''
  },
  description: {
    type: String,
    default: ''
  },
  icon: {
    type: Object,
    default: null
  },
  iconVariant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'success', 'warning', 'danger'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'xl', '2xl', 'full'].includes(value)
  },
  showHeader: {
    type: Boolean,
    default: true
  },
  showFooter: {
    type: Boolean,
    default: true
  },
  showCloseButton: {
    type: Boolean,
    default: true
  },
  showCancelButton: {
    type: Boolean,
    default: true
  },
  showConfirmButton: {
    type: Boolean,
    default: true
  },
  cancelText: {
    type: String,
    default: 'Cancelar'
  },
  confirmText: {
    type: String,
    default: 'Confirmar'
  },
  confirmVariant: {
    type: String,
    default: 'primary'
  },
  loading: {
    type: Boolean,
    default: false
  },
  closeOnBackdrop: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['close', 'confirm', 'cancel'])

const modalClasses = computed(() => {
  const sizeClasses = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    '2xl': 'max-w-2xl',
    full: 'max-w-full mx-4'
  }

  return `w-full ${sizeClasses[props.size]} transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all`
})

const iconContainerClasses = computed(() => {
  const variantClasses = {
    primary: 'bg-primary-100 text-primary-600',
    success: 'bg-success-100 text-success-600',
    warning: 'bg-warning-100 text-warning-600',
    danger: 'bg-danger-100 text-danger-600'
  }

  return `flex h-12 w-12 items-center justify-center rounded-xl ${variantClasses[props.iconVariant]}`
})

const handleClose = () => {
  if (props.closeOnBackdrop && !props.loading) {
    emit('close')
  }
}

const handleConfirm = () => {
  emit('confirm')
}

const handleCancel = () => {
  emit('cancel')
  if (!props.loading) {
    emit('close')
  }
}
</script>
