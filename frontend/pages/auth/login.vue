<template>
  <!-- Modern gradient background with subtle pattern -->
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
    <!-- Subtle background pattern -->
    <div class="absolute inset-0 opacity-5 dark:opacity-10">
      <div class="absolute inset-0" style="background-image: radial-gradient(circle at 1px 1px, rgba(99, 102, 241, 0.15) 1px, transparent 0); background-size: 20px 20px;"></div>
    </div>
    
    <!-- Main login container -->
    <div class="relative z-10 w-full max-w-md">
      <!-- Combined login card -->
      <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl rounded-2xl shadow-xl border border-white/20 dark:border-slate-700/50 p-8 transform hover:shadow-2xl transition-all duration-300">
        <!-- Brand logo and header -->
        <div class="text-center mb-8">
          <!-- Logo placeholder - you can replace with actual logo -->
          <div class="mx-auto w-16 h-16 bg-gradient-to-tr from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          
          <h1 class="text-2xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
            {{ t('auth.login_title') }}
          </h1>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 font-medium">
            {{ t('auth.login_subtitle') }}
          </p>
        </div>
        <form class="space-y-6" @submit.prevent="handleLogin">
          <!-- Form fields -->
          <div class="space-y-5">
            <!-- Username/Email with icon -->
            <div class="group">
              <label for="username" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                {{ t('auth.username_email') }}
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400 group-focus-within:text-primary-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
                <input
                  id="username"
                  v-model="form.username"
                  type="text"
                  required
                  class="w-full pl-12 pr-4 py-3.5 bg-gray-50 dark:bg-slate-700/50 border border-gray-200 dark:border-slate-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200 backdrop-blur-sm"
                  :placeholder="t('auth.username_email_placeholder')"
                />
              </div>
            </div>

            <!-- Password with icon -->
            <div class="group">
              <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                {{ t('auth.password') }}
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400 group-focus-within:text-primary-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </div>
                <input
                  id="password"
                  v-model="form.password"
                  type="password"
                  required
                  class="w-full pl-12 pr-4 py-3.5 bg-gray-50 dark:bg-slate-700/50 border border-gray-200 dark:border-slate-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200 backdrop-blur-sm"
                  :placeholder="t('auth.password_placeholder')"
                />
              </div>
            </div>
          </div>

          <!-- Error Message with enhanced styling -->
          <div v-if="error" class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl p-4 backdrop-blur-sm">
            <div class="flex items-center">
              <svg class="h-5 w-5 text-red-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <p class="text-sm text-red-700 dark:text-red-300 font-medium">{{ error }}</p>
            </div>
          </div>


          <!-- Enhanced Submit Button -->
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-3.5 px-4 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-[1.02] transition-all duration-200"
          >
            <span v-if="!loading" class="flex items-center">
              {{ t('auth.login') }}
              <svg class="ml-2 h-4 w-4 transform group-hover:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
              </svg>
            </span>
            <span v-else class="flex items-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ t('auth.logging_in') }}
            </span>
          </button>

          <!-- Register Link with enhanced styling -->
        </form>
        
        <!-- Footer info -->
        <div class="text-center mt-6 pt-6 border-t border-gray-200 dark:border-slate-600">
          <p class="text-xs text-gray-500 dark:text-gray-400">
            Professional Project Management for Freelancers
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: false,
  middleware: 'guest'
})

const { t } = useI18n()
const authStore = useAuthStore()

const form = ref({
  username: '',
  password: ''
})

const loading = ref(false)
const error = ref('')

const handleLogin = async () => {
  try {
    loading.value = true
    error.value = ''
    
    // Validate form data
    if (!form.value.username || !form.value.password) {
      throw new Error('請輸入用戶名和密碼')
    }
    
    // Attempt login
    await authStore.login(form.value)
    
    // 重定向到首頁
    await navigateTo('/')
  } catch (err) {
    error.value = err.message || '登入失敗，請檢查您的登入資訊'
    console.error('Login error:', err)
  } finally {
    loading.value = false
  }
}

// 初始化認證狀態並檢查是否已登入
onMounted(async () => {
  await authStore.initializeAuth()
  
  if (authStore.isLoggedIn) {
    await navigateTo('/')
  }
})
</script>