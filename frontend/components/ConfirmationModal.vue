<template>
  <Modal
    :model-value="modelValue"
    :title="title"
    size="md"
    :show-default-footer="true"
    :cancel-text="cancelText"
    :confirm-text="confirmText"
    :confirm-button-class="confirmButtonClass"
    @update:model-value="(value) => $emit('update:modelValue', value)"
    @close="$emit('close')"
    @confirm="$emit('confirm')"
  >
    <div class="flex items-start space-x-3">
      <div :class="iconClasses">
        <component :is="iconComponent" class="w-6 h-6" />
      </div>
      <div class="flex-1">
        <p class="text-gray-600 dark:text-gray-400">
          {{ message }}
        </p>
        <div v-if="details" class="mt-2 text-sm text-gray-500 dark:text-gray-500">
          {{ details }}
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import {
  ExclamationTriangleIcon,
  InformationCircleIcon,
  CheckCircleIcon,
  XCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: '確認操作'
  },
  message: {
    type: String,
    required: true
  },
  details: {
    type: String,
    default: ''
  },
  type: {
    type: String,
    default: 'warning',
    validator: (value) => ['warning', 'danger', 'info', 'success'].includes(value)
  },
  cancelText: {
    type: String,
    default: '取消'
  },
  confirmText: {
    type: String,
    default: '確認'
  }
})

defineEmits(['update:modelValue', 'close', 'confirm'])

// Icon and styling based on type
const iconComponent = computed(() => {
  const iconMap = {
    warning: ExclamationTriangleIcon,
    danger: XCircleIcon,
    info: InformationCircleIcon,
    success: CheckCircleIcon
  }
  return iconMap[props.type]
})

const iconClasses = computed(() => {
  const classMap = {
    warning: 'text-yellow-600 bg-yellow-100 dark:bg-yellow-900/20 p-2 rounded-lg',
    danger: 'text-red-600 bg-red-100 dark:bg-red-900/20 p-2 rounded-lg',
    info: 'text-blue-600 bg-blue-100 dark:bg-blue-900/20 p-2 rounded-lg',
    success: 'text-green-600 bg-green-100 dark:bg-green-900/20 p-2 rounded-lg'
  }
  return classMap[props.type]
})

const confirmButtonClass = computed(() => {
  const classMap = {
    warning: 'bg-yellow-600 text-white hover:bg-yellow-700',
    danger: 'bg-red-600 text-white hover:bg-red-700',
    info: 'bg-blue-600 text-white hover:bg-blue-700',
    success: 'bg-green-600 text-white hover:bg-green-700'
  }
  return classMap[props.type]
})
</script>