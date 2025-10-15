<template>
  <div class="p-6">
    <!-- Page Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">題項管理 - {{ companyName }}</h1>
      <div class="flex items-center gap-3">
        <p class="text-gray-600 dark:text-gray-400">僅顯示您被指派的風險評估題項</p>
        <div v-if="externalUserStore.userId" class="flex items-center gap-1 px-2 py-1 bg-blue-100 dark:bg-blue-900/20 rounded-full">
          <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z" />
          </svg>
          <span class="text-xs font-medium text-blue-600 dark:text-blue-400">已篩選</span>
        </div>
      </div>
    </div>

    <!-- 點15 測試用顯示區塊 - 已隱藏 -->
    <!--
    <div v-if="token" class="mb-6 p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg border border-orange-200 dark:border-orange-800">
      <h3 class="text-sm font-bold mb-3 text-orange-800 dark:text-orange-200">🧪 點15 用戶ID映射測試</h3>

      Token資訊
      <div class="mb-3">
        <h4 class="text-xs font-semibold mb-2 text-gray-700 dark:text-gray-300">Token資訊：</h4>
        <div class="bg-white dark:bg-gray-800 p-2 rounded border text-xs">
          <div class="mb-1">
            <span class="font-medium text-gray-600 dark:text-gray-400">Token值：</span>
            <code class="text-blue-600 dark:text-blue-400">{{ token ? token.slice(0, 20) + '...' : '無' }}</code>
          </div>
        </div>
      </div>

      原始用戶資訊
      <div class="mb-3">
        <h4 class="text-xs font-semibold mb-2 text-gray-700 dark:text-gray-300">原始用戶資訊 (userInfo)：</h4>
        <div class="bg-white dark:bg-gray-800 p-2 rounded border text-xs">
          <div class="mb-1">
            <span class="font-medium text-gray-600 dark:text-gray-400">userInfo.data.user_id：</span>
            <span class="text-gray-800 dark:text-gray-200">{{ externalUserStore.userInfo?.data?.user_id || 'null' }}</span>
          </div>
          <div class="mb-1">
            <span class="font-medium text-gray-600 dark:text-gray-400">userInfo.data.user_name：</span>
            <span class="text-gray-800 dark:text-gray-200">{{ externalUserStore.userInfo?.data?.user_name || 'null' }}</span>
          </div>
          <div class="mb-1">
            <span class="font-medium text-gray-600 dark:text-gray-400">userInfo.data.email：</span>
            <span class="text-gray-800 dark:text-gray-200">{{ externalUserStore.userInfo?.data?.email || 'null' }}</span>
          </div>
          <div class="mb-1">
            <span class="font-medium text-gray-600 dark:text-gray-400">userInfo.data.com_id：</span>
            <span class="text-gray-800 dark:text-gray-200">{{ externalUserStore.userInfo?.data?.com_id || 'null' }}</span>
          </div>
        </div>
      </div>

      ID映射驗證
      <div class="mb-3">
        <h4 class="text-xs font-semibold mb-2 text-gray-700 dark:text-gray-300">ID映射驗證：</h4>
        <div class="grid grid-cols-2 gap-2 text-xs">
          <div class="bg-white dark:bg-gray-800 p-2 rounded border">
            <span class="font-medium text-gray-600 dark:text-gray-400">externalId (should be userInfo.data.user_id)：</span>
            <span class="text-gray-800 dark:text-gray-200">{{ externalUserStore.externalId || 'null' }}</span>
          </div>
          <div class="bg-white dark:bg-gray-800 p-2 rounded border">
            <span class="font-medium text-gray-600 dark:text-gray-400">userId (internal from API)：</span>
            <span class="text-gray-800 dark:text-gray-200">{{ externalUserStore.userId || 'null' }}</span>
          </div>
          <div class="bg-white dark:bg-gray-800 p-2 rounded border">
            <span class="font-medium text-gray-600 dark:text-gray-400">companyId (should be userInfo.user.com_id)：</span>
            <span class="text-blue-600 dark:text-blue-400 font-medium">{{ externalUserStore.companyId || 'null' }}</span>
          </div>
        </div>
      </div>

      映射狀態檢查
      <div>
        <h4 class="text-xs font-semibold mb-2 text-gray-700 dark:text-gray-300">映射狀態檢查：</h4>
        <div class="bg-white dark:bg-gray-800 p-2 rounded border text-xs">
          <div class="mb-1">
            <span class="font-medium text-gray-600 dark:text-gray-400">映射是否正確：</span>
            <span :class="mappingStatusColor">{{ mappingStatus }}</span>
          </div>
          <div v-if="mappingIssues.length > 0" class="mt-2">
            <span class="font-medium text-red-600 dark:text-red-400">發現問題：</span>
            <ul class="list-disc list-inside text-red-600 dark:text-red-400 mt-1">
              <li v-for="issue in mappingIssues" :key="issue">{{ issue }}</li>
            </ul>
            <div v-if="mappingIssues.some(issue => issue.includes('內部用戶ID'))" class="mt-2">
              <span class="font-medium text-blue-600 dark:text-blue-400">🔄 自動修復：</span>
              <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                系統將自動調用人員同步API ({{ `/api/v1/personnel/companies/${externalUserStore.companyId}/sync` }}) 來載入人員資料
              </p>
            </div>
          </div>
        </div>
      </div>

      篩選參數檢查
      <div class="mt-3">
        <h4 class="text-xs font-semibold mb-2 text-gray-700 dark:text-gray-300">篩選參數檢查：</h4>
        <div class="bg-white dark:bg-gray-800 p-2 rounded border text-xs">
          <div class="grid grid-cols-2 gap-2">
            <div class="mb-1">
              <span class="font-medium text-gray-600 dark:text-gray-400">篩選狀態：</span>
              <span v-if="externalUserStore.userId" class="text-green-600 dark:text-green-400 font-medium">✅ 已啟用使用者篩選</span>
              <span v-else class="text-yellow-600 dark:text-yellow-400 font-medium">⚠️ 顯示所有資料</span>
            </div>
            <div class="mb-1">
              <span class="font-medium text-gray-600 dark:text-gray-400">篩選依據：</span>
              <span class="text-blue-600 dark:text-blue-400">personnel_id = {{ externalUserStore.userId || 'null' }}</span>
            </div>
            <div class="mb-1">
              <span class="font-medium text-gray-600 dark:text-gray-400">載入狀態：</span>
              <span v-if="isFilteringAssignments" class="text-blue-600 dark:text-blue-400">🔄 正在篩選中...</span>
              <span v-else class="text-green-600 dark:text-green-400">✅ 載入完成</span>
            </div>
            <div class="mb-1">
              <span class="font-medium text-gray-600 dark:text-gray-400">結果數量：</span>
              <span class="text-purple-600 dark:text-purple-400 font-medium">{{ questionManagement.length }} 個評估項目</span>
            </div>
          </div>
          <div v-if="externalUserStore.userId" class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-600">
            <span class="font-medium text-gray-600 dark:text-gray-400">篩選邏輯：</span>
            <code class="text-xs text-blue-600 dark:text-blue-400 bg-gray-100 dark:bg-gray-700 px-1 rounded ml-1">
              只顯示 personnel_id={{ externalUserStore.userId }} 被指派的評估項目
            </code>
          </div>
        </div>
      </div>
    </div>
    -->

    <!-- External User Info Display - Hidden per request -->
    <!--
    <div v-if="externalUserStore.hasUserInfo" class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
            外部用戶已登入
          </h3>
          <div class="mt-2 text-sm text-green-700 dark:text-green-300">
            <p><strong>用戶名稱:</strong> {{ externalUserStore.userName }}</p>
            <p v-if="externalUserStore.externalId"><strong>外部ID:</strong> {{ externalUserStore.externalId }}</p>
            <p v-if="externalUserStore.userId"><strong>內部ID:</strong> {{ externalUserStore.userId }}</p>
            <p v-if="externalUserStore.userEmail"><strong>電子郵件:</strong> {{ externalUserStore.userEmail }}</p>
            <p class="text-xs mt-1 text-green-600 dark:text-green-400">
              資料已儲存至 SessionStorage，最後更新: {{ new Date(externalUserStore.lastUpdated).toLocaleString() }}
            </p>
          </div>
        </div>
      </div>
    </div>


    <div v-else-if="token && !externalUserStore.isLoaded" class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
      <div class="flex items-center">
        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600 mr-3"></div>
        <span class="text-sm text-blue-800 dark:text-blue-200">正在載入外部用戶資訊...</span>
      </div>
    </div>


    <div v-else-if="!token" class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
            未提供 Token
          </h3>
          <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
            <p>URL 中未包含 token 參數，無法載入外部用戶資訊</p>
          </div>
        </div>
      </div>
    </div>
    -->


    <!-- Company Warning Message -->
    <div v-if="showCompanyWarning" class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
            公司不存在
          </h3>
          <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
            <p>找不到 ID 為 "{{ companyId }}" 的公司。這可能是因為：</p>
            <ul class="mt-1 ml-4 list-disc">
              <li>公司資料已被刪除</li>
              <li>URL 中的公司 ID 不正確</li>
              <li>系統資料需要更新</li>
            </ul>
            <p class="mt-2">
              請檢查 URL 或聯繫系統管理員。
              <nuxt-link to="/web/risk-assessment/questions" class="font-medium underline hover:no-underline">
                返回公司列表
              </nuxt-link>
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State for Assignment Filtering -->
    <div v-if="isFilteringAssignments" class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
      <div class="flex items-center">
        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600 mr-3"></div>
        <span class="text-sm text-blue-800 dark:text-blue-200">正在篩選您被指派的題項...</span>
      </div>
    </div>

    <!-- Data Table -->
    <DataTable
      :data="questionManagement"
      :columns="columns"
      search-placeholder="搜尋題項名稱、年份或範本版本..."
      :search-fields="['name', 'year', 'templateVersion']"
      empty-title="沒有被指派的題項"
      empty-message="您目前沒有被指派到任何風險評估題項"
      no-search-results-title="沒有找到符合的項目"
      no-search-results-message="請嘗試其他搜尋關鍵字"
      @search="(query) => console.log('搜尋查詢:', query)"
      @sort="(field, order) => console.log('排序:', field, order)"
    >
      <!-- Actions Slot for Web Version -->
      <template #actions>
        <div class="flex gap-2">
          <button
            @click="loadQuestionManagementData"
            :disabled="isFilteringAssignments"
            class="flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
          >
            <ArrowPathIcon
              class="w-4 h-4 mr-2"
              :class="{ 'animate-spin': isFilteringAssignments }"
            />
            重新整理
          </button>
        </div>
      </template>

      <!-- Custom Actions Cell - Web Version (Simplified) -->
      <template #cell-actions="{ item }">
        <div class="flex items-center space-x-2 justify-center">
          <!-- Fill Assessment Form -->
          <div class="relative group">
            <button
              @click="showAssessmentForm(item)"
              class="p-2 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors duration-200"
            >
              <DocumentTextIcon class="w-4 h-4" />
            </button>
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
              填寫評估表
              <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
            </div>
          </div>

          <!-- View Statistics -->
          <div class="relative group">
            <button
              @click="showStatistics(item)"
              class="p-2 text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-lg transition-colors duration-200"
            >
              <ChartBarIcon class="w-4 h-4" />
            </button>
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
              檢視評估表統計結果
              <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
            </div>
          </div>
        </div>
      </template>

      <!-- Empty Action Slot - Web Version -->
      <template #emptyAction>
        <div class="text-center text-gray-500 dark:text-gray-400">
          <p class="text-sm">請聯繫管理員為您指派評估題項</p>
        </div>
      </template>
    </DataTable>
  </div>
</template>

<script setup>
definePageMeta({
  middleware: 'auth'
})

import {
  DocumentTextIcon,
  ChartBarIcon,
  ArrowPathIcon
} from '@heroicons/vue/24/outline'

const route = useRoute()
const companyId = computed(() => route.params.id)

// 獲取 URL 中的 token 參數
const token = computed(() => route.query.token)

// 使用外部用戶 store
const externalUserStore = useExternalUserStore()

// Web version: Simplified - only need for display
const templatesStore = useTemplatesStore()
const availableTemplates = computed(() => templatesStore?.templates || [])

// Get company data from local database API (same as admin version)
const { getCompanyNameById, loading: companiesLoading, error: companiesError } = useLocalCompanies()

// Store company name in reactive ref for async loading
const companyName = ref('載入中...')
const companyExists = ref(true)

// Load company name asynchronously
const loadCompanyName = async () => {
  if (!companyId.value) {
    companyName.value = '未知公司'
    companyExists.value = false
    return
  }

  try {
    const name = await getCompanyNameById(companyId.value)
    if (name) {
      companyName.value = name
      companyExists.value = true
    } else {
      companyName.value = `公司 #${companyId.value}`
      companyExists.value = false
    }
  } catch (error) {
    console.error('Error loading company name:', error)
    companyName.value = `公司 #${companyId.value}`
    companyExists.value = false
  }
}

// Show warning message for non-existent companies
const showCompanyWarning = computed(() => {
  return !companiesLoading.value && !companiesError.value && !companyExists.value
})

// 映射狀態檢查計算屬性
const mappingStatus = computed(() => {
  const userDataUserId = externalUserStore.userInfo?.data?.user_id
  const storeExternalId = externalUserStore.externalId

  if (!userDataUserId) {
    return '無用戶資料'
  } else if (userDataUserId.toString() === storeExternalId?.toString()) {
    return '✓ 正確映射'
  } else {
    return '✗ 映射錯誤'
  }
})

const mappingStatusColor = computed(() => {
  const status = mappingStatus.value
  if (status === '✓ 正確映射') {
    return 'text-green-600 dark:text-green-400 font-medium'
  } else if (status === '✗ 映射錯誤') {
    return 'text-red-600 dark:text-red-400 font-medium'
  } else {
    return 'text-yellow-600 dark:text-yellow-400 font-medium'
  }
})

const mappingIssues = computed(() => {
  const issues = []
  const userDataUserId = externalUserStore.userInfo?.data?.user_id
  const storeExternalId = externalUserStore.externalId
  const storeUserId = externalUserStore.userId

  if (!userDataUserId) {
    issues.push('缺少 userInfo.data.user_id 資料')
  }

  if (userDataUserId && !storeExternalId) {
    issues.push('externalId 未正確設定')
  }

  if (userDataUserId && storeExternalId && userDataUserId.toString() !== storeExternalId.toString()) {
    issues.push(`ID不匹配：userInfo.data.user_id(${userDataUserId}) ≠ externalId(${storeExternalId})`)
  }

  if (storeExternalId && storeUserId === null) {
    issues.push('內部用戶ID (userId) 尚未通過API查詢獲得')
  }

  return issues
})

// Set page title
const pageTitle = computed(() => `題項管理 - ${companyName.value || '公司'}`)
usePageTitle(pageTitle)

// Question management composable
const {
  getQuestionManagementByCompany,
  initializeCompanyQuestionManagement
} = useQuestionManagement()

// Reactive data for question management items
const questionManagement = ref([])
const isFilteringAssignments = ref(false)

// Load question management data filtered by current user assignments
const loadQuestionManagementData = async () => {
  try {
    isFilteringAssignments.value = true

    console.log('=== Web版本頁面資料載入 ===')
    console.log('Company ID:', companyId.value)
    console.log('Company Name:', companyName.value)
    console.log('Current User ID:', externalUserStore.userId)

    // Use the updated getter with userId parameter for automatic filtering
    const filteredData = await getQuestionManagementByCompany(companyId.value, externalUserStore.userId)

    questionManagement.value = filteredData

    console.log('=== Getter結果 ===')
    console.log(`過濾後評估數量: ${filteredData.length}`)
    console.log('Filtered Assessments:', filteredData.map(a => ({ id: a.id, year: a.year })))
    console.log('=== 資料結束 ===')

  } catch (error) {
    console.error('Error loading question management data:', error)
    questionManagement.value = []
  } finally {
    isFilteringAssignments.value = false
  }
}

// Initialize question management for this company
onMounted(async () => {
  // 優先調用用戶資料解密 API 並儲存到 Pinia Store
  if (token.value) {
    await externalUserStore.fetchExternalUserData(token.value)
  }

  // Load company name first
  await loadCompanyName()

  await initializeCompanyQuestionManagement(companyId.value, externalUserStore.userId)
  await loadQuestionManagementData()

  // Initialize templates store to fetch templates from database
  try {
    await templatesStore.initialize()
    console.log('Templates loaded from database:', templatesStore.templates.length)
  } catch (error) {
    console.error('Failed to load templates from database:', error)
  }

  console.log('=== Web版本頁面載入完成 ===')
  console.log('Route Params:', route.params)
  console.log('Route Query (Token):', route.query)
  console.log('Company ID from Route:', companyId.value)
  console.log('Company Name:', companyName.value)
  console.log('Available Templates on Mount:', availableTemplates.value)
  console.log('外部用戶資訊:', externalUserStore.userInfo)
  console.log('外部用戶名稱:', externalUserStore.userName)
  console.log('外部用戶ID:', externalUserStore.userId)
  console.log('外部用戶Email:', externalUserStore.userEmail)
  console.log('外部用戶公司ID (companyId):', externalUserStore.companyId)

  console.log('=== 篩選參數檢查 ===')
  console.log('篩選狀態:', externalUserStore.userId ? '✅ 已啟用使用者篩選' : '⚠️ 顯示所有資料')
  console.log('篩選依據: personnel_id =', externalUserStore.userId || 'null')
  console.log('最終顯示項目數量:', questionManagement.value.length)
  console.log('=== 初始資料結束 ===')
})

// Watch for changes in company ID (route parameter changes)
watch(companyId, async (newId, oldId) => {
  if (newId !== oldId && newId) {
    console.log('=== Web版本：公司 ID 變更 ===')
    console.log('舊 ID:', oldId)
    console.log('新 ID:', newId)
    await loadQuestionManagementData()
    console.log('=== 公司資料重新載入完成 ===')
  }
})

// DataTable columns configuration
const columns = ref([
  {
    key: 'actions',
    label: '功能',
    sortable: false,
    cellClass: 'text-base text-gray-900 dark:text-white'
  },
  {
    key: 'name',
    label: '題項名稱',
    sortable: true,
    cellClass: 'text-base text-gray-900 dark:text-white'
  },
  {
    key: 'year',
    label: '年份',
    sortable: true,
    cellClass: 'text-base font-medium text-gray-900 dark:text-white'
  },
  {
    key: 'createdAt',
    label: '建立日期',
    sortable: true,
    cellClass: 'text-base text-gray-900 dark:text-white'
  }
])

// Web version: Navigate to content page (same as admin) to show assigned questions
const showAssessmentForm = (item) => {
  console.log('=== 導航到內容頁面 ===')
  console.log('Navigating to content page for:', item)
  console.log('Company ID:', companyId.value)
  console.log('Question ID (item.id):', item.id)

  // 根據點13要求：導航到內容頁面，與 admin 版本相同，列出被指派的題目
  // 導航到 [companyId]/management/[questionId]/content.vue 路由
  const targetUrl = `/web/risk-assessment/questions/${companyId.value}/management/${item.id}/content`
  console.log('Target URL:', targetUrl)
  console.log('路由說明: 導航到內容頁面，列出被指派的題目 ([companyId]/management/[questionId]/content.vue)')

  // Navigate to the content page to show assigned questions (same as admin)
  // Use item.id as questionId
  navigateTo(targetUrl)
}

const showStatistics = (item) => {
  // TODO: Implement statistics modal
  console.log('Show statistics for:', item)
}
</script>