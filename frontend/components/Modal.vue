<template>
  <div
    v-if="modelValue"
    class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
    @click="handleBackdropClick"
  >
    <div
      :class="[
        'bg-white dark:bg-gray-800 rounded-lg shadow-xl',
        'transition-all duration-200 transform',
        modelValue ? 'scale-100 opacity-100' : 'scale-95 opacity-0',
        sizeClasses
      ]"
      @click.stop
      role="dialog"
      aria-modal="true"
      :aria-labelledby="titleId"
    >
      <!-- Modal Header -->
      <div v-if="$slots.header || title" class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <slot name="header">
            <h3 :id="titleId" class="text-lg font-medium text-gray-900 dark:text-white">
              {{ title }}
            </h3>
          </slot>
          <button
            v-if="showCloseButton"
            @click="close"
            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
            aria-label="關閉"
          >
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- Modal Body -->
      <div :class="['p-6 overflow-y-auto flex-1', bodyClass]">
        <slot />
      </div>

      <!-- Modal Footer -->
      <div v-if="$slots.footer" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 rounded-b-lg">
        <slot name="footer" />
      </div>

      <!-- Default Footer with Actions -->
      <div v-else-if="showDefaultFooter" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 rounded-b-lg">
        <div class="flex justify-end space-x-3">
          <button
            v-if="showCancelButton"
            type="button"
            @click="close"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors duration-200"
          >
            {{ cancelText }}
          </button>
          <button
            v-if="showConfirmButton"
            type="button"
            @click="confirm"
            :class="[
              'px-4 py-2 rounded-lg transition-colors duration-200',
              confirmButtonClass
            ]"
          >
            {{ confirmText }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', 'full'].includes(value)
  },
  showCloseButton: {
    type: Boolean,
    default: true
  },
  showDefaultFooter: {
    type: Boolean,
    default: false
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
    default: '取消'
  },
  confirmText: {
    type: String,
    default: '確認'
  },
  confirmButtonClass: {
    type: String,
    default: 'bg-primary-600 text-white hover:bg-primary-700'
  },
  bodyClass: {
    type: String,
    default: ''
  },
  closableOnBackdrop: {
    type: Boolean,
    default: true
  },
  closableOnEscape: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:modelValue', 'close', 'confirm'])

// Generate unique ID for accessibility
const titleId = computed(() => `modal-title-${Math.random().toString(36).substring(2, 11)}`)

// Size classes mapping
const sizeClasses = computed(() => {
  const sizeMap = {
    xs: 'w-full max-w-xs',
    sm: 'w-full max-w-sm',
    md: 'w-full max-w-md',
    lg: 'w-full max-w-lg',
    xl: 'w-full max-w-xl',
    '2xl': 'w-full max-w-2xl',
    '3xl': 'w-full max-w-3xl',
    '4xl': 'w-full max-w-4xl max-h-[90vh] flex flex-col',
    full: 'w-full h-full max-w-none max-h-none m-0 rounded-none'
  }
  return sizeMap[props.size]
})

// Handle backdrop click
const handleBackdropClick = () => {
  if (props.closableOnBackdrop) {
    close()
  }
}

// Close modal
const close = () => {
  emit('update:modelValue', false)
  emit('close')
}

// Confirm action
const confirm = () => {
  emit('confirm')
}

// Handle keyboard events
const handleKeydown = (event) => {
  if (event.key === 'Escape' && props.closableOnEscape && props.modelValue) {
    close()
  }
}

// Add/remove event listeners for ESC key
onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})

// Watch for modelValue changes to manage body scroll
watch(() => props.modelValue, (newValue) => {
  if (process.client) {
    if (newValue) {
      // Prevent body scroll when modal is open
      document.body.style.overflow = 'hidden'
    } else {
      // Restore body scroll when modal is closed
      document.body.style.overflow = ''
    }
  }
})

// Cleanup on unmount
onUnmounted(() => {
  if (process.client) {
    document.body.style.overflow = ''
  }
})
</script>