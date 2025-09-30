<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex">
    <!-- Sidebar integration for consistency -->
    <AppSidebar />
    
    <!-- Main content area with sidebar positioning -->
    <div 
      class="min-h-screen flex flex-col flex-1 main-content-area"
      :class="{
        'sidebar-collapsed': sidebarCollapsed
      }"
    >
      <AppNavbar />
      
      <!-- 404 Content -->
      <main class="flex-1 flex items-center justify-center p-6">
        <div class="text-center max-w-md mx-auto">
          <!-- Primary content card with existing styling patterns -->
          <div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-12">
            
            <!-- Large error code number in primary color -->
            <div class="mb-8">
              <h1 class="text-9xl font-bold text-primary-500 leading-none select-none">
                {{ errorCode }}
              </h1>
            </div>

            <!-- Folder/document icon with question mark overlay -->
            <div class="mb-8">
              <div class="relative inline-flex items-center justify-center">
                <FolderIcon class="w-24 h-24 text-gray-300 dark:text-gray-600" />
                <div class="absolute -top-2 -right-2 bg-primary-500 rounded-full w-10 h-10 flex items-center justify-center">
                  <QuestionMarkCircleIcon class="w-6 h-6 text-white" />
                </div>
              </div>
            </div>

            <!-- Error messaging -->
            <div class="mb-8 space-y-4">
              <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                頁面未找到
              </h2>
              <p class="text-gray-600 dark:text-gray-300">
                抱歉，您所尋找的頁面不存在或已被移除。
              </p>
            </div>

            <!-- Back to Home button -->
            <div>
              <NuxtLink
                to="/"
                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transform hover:scale-[1.02] transition-all duration-200"
              >
                <HomeIcon class="w-5 h-5 mr-2" />
                返回首頁
              </NuxtLink>
            </div>

          </div>
        </div>
      </main>
      
      <!-- Footer integration -->
      <AppFootbar v-if="showFootbar" />
    </div>
  </div>
</template>

<script setup>
import {
  FolderIcon,
  QuestionMarkCircleIcon,
  HomeIcon
} from '@heroicons/vue/24/outline'

// Use existing composables and stores - only on client side for SSR compatibility
const sidebarStore = process.client ? useSidebarStore() : null
const sidebarCollapsed = process.client && sidebarStore ? storeToRefs(sidebarStore).sidebarCollapsed : ref(false)

// Environment-based footer control
const config = useRuntimeConfig()
const showFootbar = computed(() => config.public.showFooter)

// Handle different error types
const error = useError()

const errorCode = computed(() => {
  return error.value?.statusCode || 404
})

const errorTitle = computed(() => {
  const code = errorCode.value
  switch (code) {
    case 500: return `${code} - 伺服器錯誤`
    case 403: return `${code} - 拒絕訪問`
    case 401: return `${code} - 未授權`
    default: return `${code} - 頁面未找到`
  }
})

// Set page metadata
useHead({
  title: `${errorTitle.value} | ${config.public.appTitle}`,
  meta: [
    { name: 'description', content: '頁面未找到' }
  ]
})

// Add error tracking if needed
onMounted(() => {
  // Optional: Track errors for analytics
  console.log('Error:', {
    statusCode: errorCode.value,
    path: useRoute().fullPath,
    timestamp: new Date().toISOString()
  })
})
</script>

<style scoped>
/* Additional responsive design enhancements */
@media (max-width: 640px) {
  .text-9xl {
    font-size: 6rem;
  }
}

/* Hover effects for interactive elements */
.transform {
  transition: transform 0.2s ease-in-out;
}

/* Focus improvements for accessibility */
button:focus-visible,
a:focus-visible {
  outline: 2px solid var(--primary-500);
  outline-offset: 2px;
}

/* Dark mode specific adjustments */
.dark .bg-gradient-to-r {
  background-image: linear-gradient(
    to right,
    var(--primary-500),
    var(--primary-600)
  );
}

.dark .hover\:from-primary-600:hover {
  background-image: linear-gradient(
    to right,
    var(--primary-600),
    var(--primary-700)
  );
}
</style>