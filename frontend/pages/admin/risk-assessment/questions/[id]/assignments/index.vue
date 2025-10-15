<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <PageHeader
      title="評估表指派狀況"
      :description="`${assessmentInfo.templateVersion} (${assessmentInfo.year}年)`"
      :show-back-button="true"
      :back-path="assessmentInfo.company_id ? `/admin/risk-assessment/questions/${assessmentInfo.company_id}/management` : '/admin/risk-assessment/questions'"
    >
      <template #actions>
        <div v-if="statistics" class="flex items-center space-x-4">
          <div class="text-center">
            <div class="text-2xl font-bold text-blue-600">{{ statistics.total_questions }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">總題數</div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold text-purple-600">{{ statistics.total_assigned_users }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">指派人數</div>
          </div>
        </div>
      </template>
    </PageHeader>

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

    <!-- Questions DataTable -->
    <div v-else-if="expandedQuestions && expandedQuestions.length > 0" class="space-y-4">
      <DataTable
        :data="expandedQuestions"
        :columns="columns"
        search-placeholder="搜尋風險因子或人員名稱..."
        :search-fields="['factor_name', 'user_name', 'department']"
        empty-title="尚無指派題目"
        empty-message="此評估表尚未有任何題目被指派"
        no-search-results-title="沒有找到符合的題目"
        no-search-results-message="請嘗試其他搜尋關鍵字"
        :page-size="20"
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

        <!-- Actions Cell -->
        <template #cell-actions="{ item }">
          <div class="flex items-center gap-2">
            <!-- View Response Button - Direct Navigation -->
            <div class="relative group">
              <button
                @click="viewUserResponse(item.content_id, item.user_id, item.user_name)"
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

        <!-- Question Content Cell (Factor + Category/Topic) -->
        <template #cell-question_content="{ item }">
          <div class="py-2 flex flex-wrap gap-2 items-center">
            <!-- Category Badge -->
            <BadgeWithTooltip
              v-if="item.category_name"
              :text="item.category_name"
              variant="category"
              :truncate-length="6"
            />

            <!-- Topic Badge -->
            <BadgeWithTooltip
              v-if="item.topic_name"
              :text="item.topic_name"
              variant="topic"
              :truncate-length="6"
            />
            <span v-else-if="!item.topic_name && item.category_name" class="text-gray-400 dark:text-gray-500 italic text-xs">未設定主題</span>

            <!-- Factor Badge -->
            <BadgeWithTooltip
              v-if="item.factor_name"
              :text="item.factor_name"
              variant="factor"
              :truncate-length="6"
            />
            <span v-else class="text-gray-400 dark:text-gray-500 italic text-xs">未命名題目</span>
          </div>
        </template>

        <!-- Assigned Personnel Cell -->
        <template #cell-assigned_personnel="{ item }">
          <div class="text-sm text-gray-700 dark:text-gray-300">
            <div class="font-medium">{{ item.user_name }}</div>
            <div v-if="item.department || item.position" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
              <span v-if="item.department">{{ item.department }}</span>
              <span v-if="item.department && item.position"> · </span>
              <span v-if="item.position">{{ item.position }}</span>
            </div>
          </div>
        </template>

        <!-- Status Cell (Only indicator, no text) -->
        <template #cell-status="{ item }">
          <div class="flex justify-center">
            <!-- Status Indicator - Using review_status based logic -->
            <div
              class="w-3 h-3 rounded-full"
              :class="{
                'bg-green-500': item.status === 'completed',
                'bg-red-500': item.status === 'pending',
                'bg-gray-400': item.status === 'not_started'
              }"
              :title="getStatusText(item.status)"
            ></div>
          </div>
        </template>

        <!-- Updated At Cell - Using personal last_updated -->
        <template #cell-updated_at="{ item }">
          <div class="text-sm text-gray-700 dark:text-gray-300">
            {{ formatDateTime(item.last_updated || item.updated_at) }}
          </div>
        </template>

        <!-- Created At Cell -->
        <template #cell-created_at="{ item }">
          <div class="text-sm text-gray-700 dark:text-gray-300">
            {{ formatDateTime(item.created_at) }}
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
  ArrowPathIcon,
  EyeIcon,
  UsersIcon
} from '@heroicons/vue/24/outline'
import DataTable from '~/components/DataTable.vue'
import BadgeWithTooltip from '~/components/BadgeWithTooltip.vue'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const error = ref(null)
const assessmentInfo = ref({})
const questions = ref([])
const statistics = ref(null)

// 將後端的 questions 展開成每個用戶一筆資料
const expandedQuestions = computed(() => {
  const expanded = []

  questions.value.forEach(question => {
    // 如果沒有被指派的用戶，跳過
    if (!question.user_responses || question.user_responses.length === 0) {
      return
    }

    // 為每個被指派的用戶創建一筆資料
    question.user_responses.forEach(user => {
      expanded.push({
        // 題目資訊
        content_id: question.content_id,
        factor_name: question.factor_name,
        category_name: question.category_name,
        topic_name: question.topic_name,
        created_at: question.created_at,
        updated_at: question.updated_at,

        // 用戶資訊
        user_id: user.user_id,
        user_name: user.user_name,
        department: user.department,
        position: user.position,

        // 該用戶的個人狀態（不是整體狀態）
        status: user.status,
        last_updated: user.last_updated
      })
    })
  })

  return expanded
})

// DataTable columns definition
const columns = [
  {
    key: 'actions',
    label: '操作',
    sortable: false
  },
  {
    key: 'question_content',
    label: '題項內容',
    sortable: false
  },
  {
    key: 'assigned_personnel',
    label: '被指派人員',
    sortable: true
  },
  {
    key: 'status',
    label: '狀態',
    sortable: true
  },
  {
    key: 'updated_at',
    label: '更新時間',
    sortable: true
  },
  {
    key: 'created_at',
    label: '建立時間',
    sortable: true
  }
]

const getStatusText = (status) => {
  const statusMap = {
    'completed': '已完成',
    'pending': '待更新',
    'not_started': '未開始'
  }
  return statusMap[status] || '未知'
}

const formatDateTime = (dateTimeString) => {
  if (!dateTimeString) return '-'

  try {
    const date = new Date(dateTimeString)
    return date.toLocaleString('zh-TW', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
      hour12: false
    })
  } catch (error) {
    return dateTimeString
  }
}

const loadAssignmentData = async () => {
  try {
    loading.value = true
    error.value = null

    const assessmentId = route.params.id
    console.log('Loading assignment data (by question) for assessment:', assessmentId)

    // 調用新的後端 API，以題目為主返回資料
    const response = await $fetch(`/api/v1/question-management/assessment/${assessmentId}/assignments-by-question`)

    console.log('Assignment API response:', response)

    if (response.success) {
      assessmentInfo.value = response.data.assessment_info

      // 直接使用後端返回的題目資料（後端已經只返回有被指派的題目）
      questions.value = response.data.questions

      statistics.value = response.data.statistics

      console.log('Assignment data loaded successfully:', {
        info: assessmentInfo.value,
        assignedQuestions: questions.value.length,
        stats: statistics.value
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

const viewUserResponse = (contentId, userId, userName) => {
  const assessmentId = route.params.id
  const companyId = assessmentInfo.value.company_id

  // 導航到結果查看頁面，並傳入 contentId 以查看特定題目的作答
  router.push({
    path: `/admin/risk-assessment/questions/${assessmentId}/assignments/${userId}/results`,
    query: {
      companyId: companyId,
      userName: userName,
      contentId: contentId
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