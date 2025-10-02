<template>
  <div class="p-6">
    <!-- Page Header -->
    <div class="mb-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <button
            @click="$router.back()"
            class="p-2 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors duration-200"
          >
            <ArrowLeftIcon class="w-5 h-5" />
          </button>
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">評估表指派狀況</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ assessmentInfo.templateVersion }} ({{ assessmentInfo.year }}年)</p>
          </div>
        </div>
        <div v-if="assessmentStats" class="flex items-center space-x-4">
          <div class="text-center">
            <div class="text-2xl font-bold text-green-600">{{ assessmentStats.completed }}</div>
            <div class="text-sm text-gray-500">已完成</div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold text-yellow-600">{{ assessmentStats.in_progress }}</div>
            <div class="text-sm text-gray-500">進行中</div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold text-gray-600">{{ assessmentStats.not_started }}</div>
            <div class="text-sm text-gray-500">未開始</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
      <span class="ml-3 text-gray-600 dark:text-gray-400">載入中...</span>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800 dark:text-red-200">載入失敗</h3>
          <div class="mt-2 text-sm text-red-700 dark:text-red-300">
            <p>{{ error }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Assignment Status Table -->
    <div v-else-if="assignments && assignments.length > 0">
      <DataTable
        :data="assignments"
        :columns="columns"
        search-placeholder="搜尋人員姓名..."
        :search-fields="['user_name']"
        empty-title="尚無指派人員"
        empty-message="此評估表尚未指派給任何人員"
        no-search-results-title="沒有找到符合的人員"
        no-search-results-message="請嘗試其他搜尋關鍵字"
      >
        <!-- Actions Slot - Refresh Button -->
        <template #actions>
          <button
            @click="refreshData"
            :disabled="loading"
            class="flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
          >
            <ArrowPathIcon
              class="w-4 h-4 mr-2"
              :class="{ 'animate-spin': loading }"
            />
            {{ loading ? '重新整理中...' : '重新整理' }}
          </button>
        </template>
        <!-- Status Cell -->
        <template #cell-status="{ item }">
          <span
            :class="{
              'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': item.status === 'completed',
              'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400': item.status === 'in_progress',
              'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400': item.status === 'not_started'
            }"
            class="px-2 py-1 rounded-full text-xs font-medium"
          >
            {{ getStatusText(item.status) }}
          </span>
        </template>

        <!-- Actions Cell -->
        <template #cell-actions="{ item }">
          <div class="flex items-center space-x-2">
            <!-- View Results -->
            <div class="relative group">
              <button
                @click="viewResults(item)"
                class="p-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors duration-200"
              >
                <EyeIcon class="w-4 h-4" />
              </button>
              <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                查看填寫結果
                <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
              </div>
            </div>
          </div>
        </template>
      </DataTable>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <div class="mx-auto h-12 w-12 text-gray-400">
        <UsersIcon class="h-12 w-12" />
      </div>
      <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">尚無指派人員</h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">此評估表尚未指派給任何人員</p>
      <div class="mt-6">
        <button
          @click="$router.back()"
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
        >
          返回管理頁面
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  ArrowLeftIcon,
  ArrowPathIcon,
  EyeIcon,
  UsersIcon
} from '@heroicons/vue/24/outline'
import DataTable from '~/components/DataTable.vue'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const error = ref(null)
const assessmentInfo = ref({})
const assignments = ref([])
const assessmentStats = ref(null)

const columns = [
  {
    key: 'actions',
    label: '操作',
    sortable: false
  },
  {
    key: 'user_name',
    label: '人員姓名',
    sortable: true
  },
  {
    key: 'status',
    label: '填寫狀況',
    sortable: true
  },
  {
    key: 'assigned_at',
    label: '指派時間',
    sortable: true
  },
  {
    key: 'last_updated',
    label: '最後更新',
    sortable: true
  }
]

const getStatusText = (status) => {
  const statusMap = {
    'completed': '已完成',
    'in_progress': '進行中',
    'not_started': '未開始'
  }
  return statusMap[status] || '未知'
}

const loadAssignmentData = async () => {
  try {
    loading.value = true
    error.value = null

    const assessmentId = route.params.id
    console.log('Loading assignment data for assessment:', assessmentId)

    // 調用後端 API 取得詳細的指派資料
    const response = await $fetch(`/api/v1/question-management/assessment/${assessmentId}/assignments`)

    console.log('Assignment API response:', response)

    if (response.success) {
      assessmentInfo.value = response.data.assessment_info
      assignments.value = response.data.assignments
      assessmentStats.value = response.data.statistics

      console.log('Assignment data loaded successfully:', {
        info: assessmentInfo.value,
        assignments: assignments.value,
        stats: assessmentStats.value
      })
    } else {
      error.value = response.message || '載入指派資料失敗'
      console.error('載入指派資料失敗:', response.message)
    }
  } catch (err) {
    console.error('載入指派資料時發生錯誤:', err)
    error.value = err.data?.message || err.message || '載入指派資料時發生錯誤'
  } finally {
    loading.value = false
  }
}

const refreshData = async () => {
  try {
    await loadAssignmentData()
    if (window.$swal && !error.value) {
      window.$swal.success('成功', '資料已重新載入')
    }
  } catch (err) {
    console.error('重新載入資料失敗:', err)
    if (window.$swal) {
      window.$swal.error('錯誤', '重新載入資料失敗')
    }
  }
}

const viewResults = async (assignment) => {
  // 檢查使用者是否已填寫
  if (assignment.status === 'not_started') {
    // 使用者尚未填寫，顯示提示
    const { $swal } = useNuxtApp()
    $swal.fire({
      icon: 'warning',
      title: '系統提示',
      text: '使用者尚未填寫該題目',
      showConfirmButton: false,
      timer: 1500
    })
    return
  }

  // 導航到用戶結果頁面，顯示所有已填答的內容
  const assessmentId = route.params.id
  const companyId = assignment.company_id

  // 導航到專門的結果查看頁面
  router.push({
    path: `/admin/risk-assessment/questions/${assessmentId}/assignments/${assignment.user_id}/results`,
    query: {
      companyId: companyId,
      userName: assignment.user_name
    }
  })
}


onMounted(() => {
  loadAssignmentData()
})

// SEO
useHead({
  title: '評估表指派狀況 - 風險評估管理系統'
})
</script>