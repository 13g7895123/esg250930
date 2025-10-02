<template>
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
</template>

<script setup>
// Use useState for SSR-safe store initialization
const websiteSettingsStore = useState('app_websiteSettings', () => {
  return process.client ? useWebsiteSettingsStore() : null
})

useHead({
  title: 'Admin Template',
  meta: [
    { name: 'description', content: 'Modern admin template built with Nuxt 3' }
  ]
})

// Initialize website settings and theme on app mount
onMounted(async () => {
  if (process.client && websiteSettingsStore.value) {
    try {
      // Load website settings from API
      await websiteSettingsStore.value.loadSettings()

      // Apply theme settings to ensure proper SSR hydration
      websiteSettingsStore.value.applyThemeSettings()

      // Update page title with website settings
      if (websiteSettingsStore.value.websiteTitle) {
        document.title = websiteSettingsStore.value.websiteTitle
      }
    } catch (error) {
      console.warn('Failed to initialize website settings:', error)
    }
  }
})

// Watch for website settings changes and update head
if (process.client) {
  watch(() => websiteSettingsStore.value?.websiteTitle, (newTitle) => {
    if (newTitle && process.client) {
      document.title = newTitle
    }
  })
}
</script>