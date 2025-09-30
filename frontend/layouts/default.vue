<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <AppSidebar />
    <div 
      class="main-content-area transition-margin"
      :class="{
        'sidebar-collapsed': sidebarCollapsed,
        'sidebar-transitioning': sidebarTransitioning
      }"
    >
      <AppNavbar />
      <main class="flex-1 p-6 overflow-auto">
        <slot />
      </main>
      <AppFootbar v-if="showFootbar" />
    </div>
  </div>
</template>

<script setup>
const sidebarStore = useSidebarStore()
const { sidebarCollapsed, sidebarTransitioning } = storeToRefs(sidebarStore)

// Environment-based footer control
const config = useRuntimeConfig()
const showFootbar = computed(() => config.public.showFooter)
</script>