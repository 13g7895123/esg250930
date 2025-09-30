<template>
  <div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg-custom shadow-sm p-6">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
        設定
      </h2>
      <p class="text-gray-600 dark:text-gray-300 mb-6">
        管理您的應用程式設定和偏好。
      </p>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <NuxtLink
          to="/settings/website"
          class="block p-6 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-primary-500 transition-colors duration-200"
        >
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
            網站設定
          </h3>
          <p class="text-gray-600 dark:text-gray-300 text-sm">
            網站名稱、標誌、多語言及功能設定
          </p>
        </NuxtLink>
        
        <NuxtLink
          to="/settings/ui"
          class="block p-6 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-primary-500 transition-colors duration-200"
        >
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
            介面設定
          </h3>
          <p class="text-gray-600 dark:text-gray-300 text-sm">
            頁尾顯示和側邊選單設定
          </p>
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup>
// Page metadata
definePageMeta({
  middleware: 'auth'
})
</script>