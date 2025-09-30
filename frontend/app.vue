<template>
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
</template>

<script setup>
// Only initialize websiteSettingsStore on client-side to prevent SSR issues
const websiteSettingsStore = process.client ? useWebsiteSettingsStore() : null

useHead({
  title: 'Admin Template',
  meta: [
    { name: 'description', content: 'Modern admin template built with Nuxt 3' }
  ]
})

// Initialize website settings and theme on app mount
onMounted(async () => {
  if (process.client && websiteSettingsStore) {
    try {
      // Load website settings from API
      await websiteSettingsStore.loadSettings()
      
      // Apply theme settings to ensure proper SSR hydration
      websiteSettingsStore.applyThemeSettings()
      
      // Update page title with website settings
      if (websiteSettingsStore.websiteTitle) {
        document.title = websiteSettingsStore.websiteTitle
      }
    } catch (error) {
      console.warn('Failed to initialize website settings:', error)
    }
  }
})

// Watch for website settings changes and update head
if (process.client && websiteSettingsStore) {
  watch(() => websiteSettingsStore.websiteTitle, (newTitle) => {
    if (newTitle && process.client) {
      document.title = newTitle
    }
  })
}
</script>