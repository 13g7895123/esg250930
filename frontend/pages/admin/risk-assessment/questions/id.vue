<template>
    <div class="p-6">
      <!-- Page Header -->
      <div class="mb-6">
        <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
          <NuxtLink to="/admin/risk-assessment" class="hover:text-primary-600">風險評估表</NuxtLink>
          <ChevronRightIcon class="w-4 h-4" />
          <NuxtLink to="/admin/risk-assessment/questions" class="hover:text-primary-600">題項管理</NuxtLink>
          <ChevronRightIcon class="w-4 h-4" />
          <span>{{ companyName }}</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ companyName }}</h1>
        <p class="text-gray-600 dark:text-gray-400">公司詳細資訊與管理功能</p>
      </div>
  
      <!-- Company Details Card -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">公司資訊</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">公司名稱</label>
            <p class="text-gray-900 dark:text-white">{{ companyName }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">建立日期</label>
            <p class="text-gray-900 dark:text-white">{{ formatDate(companyCreatedAt) }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">題項數量</label>
            <p class="text-gray-900 dark:text-white">{{ questionCount }} 個題項</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">最後更新</label>
            <p class="text-gray-900 dark:text-white">{{ formatDate(lastUpdated) }}</p>
          </div>
        </div>
      </div>
  
      <!-- Action Buttons -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Question Management -->
        <NuxtLink
          :to="`/admin/risk-assessment/questions/${companyId}/management`"
          class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-200"
        >
          <div class="flex items-center space-x-4">
            <div class="p-3 bg-primary-100 dark:bg-primary-900/20 rounded-lg">
              <ClipboardDocumentListIcon class="w-6 h-6 text-primary-600 dark:text-primary-400" />
            </div>
            <div>
              <h3 class="text-base font-semibold text-gray-900 dark:text-white">題項管理</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">管理風險評估題項</p>
            </div>
          </div>
        </NuxtLink>
  
        <!-- Personnel Assignment -->
        <button
          @click="showPersonnelModal = true"
          class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-200 text-left"
        >
          <div class="flex items-center space-x-4">
            <div class="p-3 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
              <UsersIcon class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
            <div>
              <h3 class="text-base font-semibold text-gray-900 dark:text-white">人員指派</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">指派評估人員</p>
            </div>
          </div>
        </button>
  
        <!-- Statistics -->
        <button
          @click="showStatistics = true"
          class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-200 text-left"
        >
          <div class="flex items-center space-x-4">
            <div class="p-3 bg-green-100 dark:bg-green-900/20 rounded-lg">
              <ChartBarIcon class="w-6 h-6 text-green-600 dark:text-green-400" />
            </div>
            <div>
              <h3 class="text-base font-semibold text-gray-900 dark:text-white">統計結果</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">檢視評估統計</p>
            </div>
          </div>
        </button>
  
        <!-- Settings -->
        <button
          @click="showSettings = true"
          class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-200 text-left"
        >
          <div class="flex items-center space-x-4">
            <div class="p-3 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
              <CogIcon class="w-6 h-6 text-purple-600 dark:text-purple-400" />
            </div>
            <div>
              <h3 class="text-base font-semibold text-gray-900 dark:text-white">公司設定</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">管理公司設定</p>
            </div>
          </div>
        </button>
  
        <!-- Export -->
        <button
          @click="exportData"
          class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-200 text-left"
        >
          <div class="flex items-center space-x-4">
            <div class="p-3 bg-orange-100 dark:bg-orange-900/20 rounded-lg">
              <ArrowDownTrayIcon class="w-6 h-6 text-orange-600 dark:text-orange-400" />
            </div>
            <div>
              <h3 class="text-base font-semibold text-gray-900 dark:text-white">匯出資料</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">下載評估資料</p>
            </div>
          </div>
        </button>
  
        <!-- Delete -->
        <button
          @click="showDeleteConfirm = true"
          class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-red-200 dark:border-red-900/20 p-6 hover:shadow-md transition-shadow duration-200 text-left hover:bg-red-50 dark:hover:bg-red-900/10"
        >
          <div class="flex items-center space-x-4">
            <div class="p-3 bg-red-100 dark:bg-red-900/20 rounded-lg">
              <TrashIcon class="w-6 h-6 text-red-600 dark:text-red-400" />
            </div>
            <div>
              <h3 class="text-base font-semibold text-gray-900 dark:text-white">刪除公司</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">永久刪除資料</p>
            </div>
          </div>
        </button>
      </div>
  
      <!-- Delete Confirmation Modal -->
      <div
        v-if="showDeleteConfirm"
        class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
        @click="showDeleteConfirm = false"
      >
        <div
          class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md"
          @click.stop
        >
          <div class="p-6">
            <div class="flex items-center mb-4">
              <ExclamationTriangleIcon class="w-6 h-6 text-red-600 mr-3" />
              <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                確認刪除
              </h3>
            </div>
            
            <p class="text-gray-500 dark:text-gray-400 mb-6">
              確定要刪除公司「{{ companyName }}」嗎？此操作將同時刪除該公司的所有題項資料，且無法復原。
            </p>
  
            <div class="flex justify-end space-x-3">
              <button
                @click="showDeleteConfirm = false"
                class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors duration-200"
              >
                取消
              </button>
              <button
                @click="confirmDelete"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200"
              >
                刪除
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  definePageMeta({
    middleware: 'auth'
  })
  
  import {
    ChevronRightIcon,
    ClipboardDocumentListIcon,
    UsersIcon,
    ChartBarIcon,
    CogIcon,
    ArrowDownTrayIcon,
    TrashIcon,
    ExclamationTriangleIcon
  } from '@heroicons/vue/24/outline'
  
  const route = useRoute()
  const router = useRouter()
  const companyId = route.params.id
  
  // Mock company data - would be fetched from API
  const companyName = ref('台積電股份有限公司')
  const companyCreatedAt = ref(new Date('2024-01-15'))
  const lastUpdated = ref(new Date())
  const questionCount = ref(12)
  
  const showDeleteConfirm = ref(false)
  const showPersonnelModal = ref(false)
  const showStatistics = ref(false)
  const showSettings = ref(false)
  
  usePageTitle(`${companyName.value} - 公司詳情`)
  
  // Methods
  const formatDate = (date) => {
    return new Intl.DateTimeFormat('zh-TW', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit'
    }).format(date)
  }
  
  const confirmDelete = async () => {
    // TODO: Implement delete logic
    console.log('Deleting company:', companyId)
    showDeleteConfirm.value = false
    // Navigate back to companies list after deletion
    await navigateTo('/admin/risk-assessment/questions')
  }
  
  const exportData = () => {
    // TODO: Implement export functionality
    console.log('Exporting data for company:', companyId)
  }
  
  // Fetch company data on mount
  onMounted(() => {
    // TODO: Fetch actual company data from API
    console.log('Loading company details for ID:', companyId)
  })
  </script>