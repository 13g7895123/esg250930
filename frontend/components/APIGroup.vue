<template>
  <UCard class="mb-6">
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ description }}</p>
        </div>
        <UBadge color="blue" variant="soft">{{ filteredEndpoints.length }} APIs</UBadge>
      </div>
    </template>

    <div class="space-y-4">
      <APIEndpoint
        v-for="(endpoint, index) in filteredEndpoints"
        :key="`${endpoint.method}-${endpoint.path}`"
        :endpoint="endpoint"
        :search-query="searchQuery"
      />

      <div v-if="filteredEndpoints.length === 0 && searchQuery" class="text-center py-8 text-gray-500 dark:text-gray-400">
        <Icon name="i-heroicons-magnifying-glass" class="w-8 h-8 mx-auto mb-2 opacity-50" />
        <p>沒有找到符合 "{{ searchQuery }}" 的 API 端點</p>
      </div>
    </div>
  </UCard>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    required: true
  },
  endpoints: {
    type: Array,
    required: true
  },
  searchQuery: {
    type: String,
    default: ''
  }
})

// Filter endpoints based on search query
const filteredEndpoints = computed(() => {
  if (!props.searchQuery) {
    return props.endpoints
  }

  const query = props.searchQuery.toLowerCase()
  return props.endpoints.filter(endpoint => {
    const searchableText = [
      endpoint.method,
      endpoint.path,
      endpoint.title,
      endpoint.description
    ].join(' ').toLowerCase()

    return searchableText.includes(query)
  })
})
</script>