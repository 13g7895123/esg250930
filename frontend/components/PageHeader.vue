<template>
  <div class="mb-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-4">
        <button
          v-if="showBackButton"
          @click="handleBack"
          class="p-2 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors duration-200"
        >
          <ArrowLeftIcon class="w-5 h-5" />
        </button>
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ title }}</h1>
          <p v-if="description" class="text-gray-600 dark:text-gray-400">{{ description }}</p>
        </div>
      </div>
      <div v-if="$slots.actions" class="flex items-center space-x-4">
        <slot name="actions" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: ''
  },
  showBackButton: {
    type: Boolean,
    default: false
  }
})

const router = useRouter()

const handleBack = () => {
  router.back()
}
</script>
