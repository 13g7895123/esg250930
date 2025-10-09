<template>
  <div class="p-6">
    <!-- Page Header -->
    <div class="mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
          風險評估題目
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
          {{ questionInfo?.year || 2024 }}年度 {{ templateInfo?.versionName || '未知範本' }} - 點擊作答按鈕開始填寫
        </p>
      </div>
    </div>

    <!-- Data Table -->
    <DataTable
      :data="questionContent"
      :columns="columns"
      search-placeholder="搜尋題目描述或分類..."
      :search-fields="['topic', 'description', 'category_name', 'factor_name']"
      empty-title="尚無指派給您的題目"
      empty-message="請聯繫管理員進行題目指派"
      no-search-results-title="沒有找到符合的題目"
      no-search-results-message="請嘗試其他搜尋關鍵字"
      @search="(query) => console.log('搜尋查詢:', query)"
      @sort="(field, order) => console.log('排序:', field, order)"
    >
      <!-- Actions Header Slot -->
      <template #actions>
        <div class="flex gap-3">
          <!-- Mapping Test Button -->
          <button
            @click="openMappingTest"
            class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200"
          >
            <CodeBracketIcon class="w-4 h-4 mr-2" />
            映射測試
          </button>

          <!-- Refresh Button -->
          <button
            @click="refreshData"
            :disabled="isRefreshing"
            class="flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
          >
            <ArrowPathIcon
              class="w-4 h-4 mr-2"
              :class="{ 'animate-spin': isRefreshing }"
            />
            {{ isRefreshing ? '重新整理中...' : '重新整理' }}
          </button>
        </div>
      </template>

      <!-- Custom Actions Cell -->
      <template #cell-actions="{ item }">
        <div class="flex items-center justify-center">
          <div class="relative group">
            <button
              @click="startAnswer(item)"
              class="p-2 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors duration-200"
            >
              <DocumentTextIcon class="w-4 h-4" />
            </button>
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
              開始作答
              <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
            </div>
          </div>
        </div>
      </template>

      <!-- Custom Description Cell -->
      <template #cell-description="{ item }">
        <div
          class="relative"
          @mouseenter="showDescriptionTooltip($event, item)"
          @mouseleave="hideDescriptionTooltip"
        >
          <div class="text-sm text-gray-900 dark:text-white cursor-pointer truncate">
            {{ truncateText(item.description || '無描述', 20) }}
          </div>
        </div>
      </template>

      <!-- Custom Categories Cell -->
      <template #cell-categories="{ item }">
        <div class="flex flex-wrap gap-1">
          <span v-if="item.category_name"
                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
            {{ item.category_name }}
          </span>
          <span v-if="item.topic_name"
                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
            {{ item.topic_name }}
          </span>
          <span v-if="item.factor_name"
                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
            {{ item.factor_name }}
          </span>
        </div>
      </template>

      <!-- Custom Status Cell with Indicator Light -->
      <template #cell-status="{ item }">
        <div class="flex items-center justify-center">
          <div class="relative group">
            <!-- Status Indicator Light -->
            <div
              class="w-4 h-4 rounded-full transition-all duration-200"
              :class="getResponseStatusClass(item)"
            ></div>
            <!-- Tooltip on hover -->
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1.5 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
              {{ getResponseStatus(item).label }}
              <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
            </div>
          </div>
        </div>
      </template>

      <!-- Empty Action Slot -->
      <template #emptyAction>
        <div class="text-center text-gray-500 dark:text-gray-400">
          <p class="text-sm">請聯繫管理員為您指派評估題項</p>
        </div>
      </template>
    </DataTable>

    <!-- Tooltip for Description (rendered at body level to avoid overflow issues) -->
    <Teleport to="body">
      <div
        v-if="descriptionTooltipData.visible && descriptionTooltipData.content"
        :style="{
          position: 'fixed',
          left: descriptionTooltipData.x + 'px',
          top: descriptionTooltipData.y + 'px',
          zIndex: 9999
        }"
        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-2xl p-4 max-w-2xl w-96 max-h-96 overflow-y-auto"
        @mouseenter="keepDescriptionTooltipOpen"
        @mouseleave="hideDescriptionTooltip"
      >
        <div
          class="text-sm text-gray-700 dark:text-gray-300 prose prose-sm dark:prose-invert max-w-none"
          v-html="descriptionTooltipData.content"
        ></div>
        <div class="absolute -top-2 left-4 w-4 h-4 bg-white dark:bg-gray-800 border-l border-t border-gray-200 dark:border-gray-700 transform rotate-45"></div>
      </div>
    </Teleport>

    <!-- Mapping Test Modal -->
    <Modal
      v-model="showMappingTest"
      title="映射測試"
      size="xl"
    >
      <div class="space-y-6">
        <!-- Mapping Summary -->
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
          <div class="flex justify-between items-center mb-3">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
              映射關係統計
            </h3>
            <button
              @click="refreshData"
              :disabled="isRefreshing"
              class="flex items-center px-3 py-1 text-xs bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
            >
              <ArrowPathIcon
                class="w-3 h-3 mr-1"
                :class="{ 'animate-spin': isRefreshing }"
              />
              {{ isRefreshing ? '載入中...' : '重新載入' }}
            </button>
          </div>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center">
              <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                {{ mappingStats.totalQuestions }}
              </div>
              <div class="text-sm text-gray-600 dark:text-gray-400">
                總題項數
              </div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                {{ mappingStats.categoriesCount }}
              </div>
              <div class="text-sm text-gray-600 dark:text-gray-400">
                風險分類數
              </div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                {{ mappingStats.topicsCount }}
              </div>
              <div class="text-sm text-gray-600 dark:text-gray-400">
                主題數
              </div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                {{ mappingStats.factorsCount }}
              </div>
              <div class="text-sm text-gray-600 dark:text-gray-400">
                風險因子數
              </div>
            </div>
          </div>
        </div>

        <!-- Mapping Details -->
        <div>
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">
            詳細映射關係
          </h3>

          <!-- No data message -->
          <div v-if="questionContent.length === 0" class="text-center py-8">
            <div class="text-gray-500 dark:text-gray-400">
              <CodeBracketIcon class="w-12 h-12 mx-auto mb-3 opacity-50" />
              <p class="text-lg font-medium mb-2">無映射資料</p>
              <p class="text-sm">目前沒有可用的題項資料進行映射測試</p>
              <p class="text-xs mt-2 text-blue-600 dark:text-blue-400">請先點擊「重新整理」按鈕載入資料</p>
            </div>
          </div>

          <!-- Mapping table -->
          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    題項ID
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    風險因子描述
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    分類ID
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    分類名稱
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    主題ID
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    主題名稱
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    因子ID
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    因子名稱
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    映射狀態
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="item in questionContent" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                  <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                    {{ item.id }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-900 dark:text-white max-w-xs truncate">
                    {{ item.description || '無描述' }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                    {{ item.category_id || '-' }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                    <span v-if="item.category_name" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                      {{ item.category_name }}
                    </span>
                    <span v-else class="text-gray-400">未分類</span>
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                    {{ item.topic_id || '-' }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                    <span v-if="item.topic_name" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                      {{ item.topic_name }}
                    </span>
                    <span v-else class="text-gray-400">未分類</span>
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                    {{ item.risk_factor_id || '-' }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                    <span v-if="item.factor_name" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                      {{ item.factor_name }}
                    </span>
                    <span v-else class="text-gray-400">未分類</span>
                  </td>
                  <td class="px-4 py-3 text-sm">
                    <span :class="getMappingStatusClass(item)">
                      {{ getMappingStatus(item) }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Debug Information -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
          <h3 class="text-lg font-medium text-blue-600 dark:text-blue-400 mb-3">
            除錯資訊
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
              <p class="text-gray-600 dark:text-gray-400 mb-1">
                <strong>公司ID:</strong> {{ companyId }}
              </p>
              <p class="text-gray-600 dark:text-gray-400 mb-1">
                <strong>評估ID:</strong> {{ questionId }}
              </p>
              <p class="text-gray-600 dark:text-gray-400 mb-1">
                <strong>用戶ID (內部):</strong> {{ externalUserStore.userId || '未設置' }}
              </p>
              <p class="text-gray-600 dark:text-gray-400">
                <strong>外部ID:</strong> {{ externalUserStore.externalId || '未設置' }}
              </p>
            </div>
            <div>
              <p class="text-gray-600 dark:text-gray-400 mb-1">
                <strong>題項資料筆數:</strong> {{ questionContent.length }}
              </p>
              <p class="text-gray-600 dark:text-gray-400 mb-1">
                <strong>API路徑:</strong> /api/v1/question-management/assessment/{{ questionId }}/contents
              </p>
              <p class="text-gray-600 dark:text-gray-400 mb-1">
                <strong>資料狀態:</strong> {{ questionContent.length > 0 ? '已載入' : '無資料' }}
              </p>
              <p class="text-gray-600 dark:text-gray-400">
                <strong>指派檢查:</strong> {{ assignmentDebugInfo.total || '未載入' }} 筆指派記錄
              </p>
            </div>
          </div>

          <!-- Assignment Debug Section -->
          <div v-if="assignmentDebugInfo.loaded" class="mt-4 pt-4 border-t border-blue-200 dark:border-blue-700">
            <h4 class="text-md font-medium text-blue-600 dark:text-blue-400 mb-2">
              指派記錄詳情
            </h4>
            <div class="bg-gray-50 dark:bg-gray-800 rounded p-3 text-xs font-mono">
              <p><strong>總指派記錄:</strong> {{ assignmentDebugInfo.total }}</p>
              <p><strong>匹配用戶ID {{ externalUserStore.userId }} 的記錄:</strong> {{ assignmentDebugInfo.forUser }}</p>
              <p><strong>評估ID {{ questionId }} 的記錄:</strong> {{ assignmentDebugInfo.forAssessment }}</p>
              <p><strong>同時匹配的記錄:</strong> {{ assignmentDebugInfo.matching }}</p>
              <p><strong>記錄狀態分佈:</strong> {{ JSON.stringify(assignmentDebugInfo.statusDistribution) }}</p>
            </div>
            <div v-if="assignmentDebugInfo.userAssignments.length > 0" class="mt-2">
              <p class="text-xs text-gray-600 dark:text-gray-400"><strong>您的指派記錄:</strong></p>
              <div class="bg-gray-50 dark:bg-gray-800 rounded p-2 text-xs font-mono max-h-32 overflow-y-auto">
                <div v-for="assignment in assignmentDebugInfo.userAssignments" :key="assignment.id" class="mb-1">
                  ID:{{ assignment.id }}, Content:{{ assignment.question_content_id }}, Status:{{ assignment.assignment_status }}
                </div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="mt-4 flex gap-2">
            <button
              @click="checkAssignments"
              :disabled="isCheckingAssignments"
              class="px-3 py-2 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ isCheckingAssignments ? '檢查中...' : '檢查指派記錄' }}
            </button>
            <button
              @click="debugPersonnelSync"
              :disabled="isSyncingPersonnel"
              class="px-3 py-2 text-xs bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ isSyncingPersonnel ? '同步中...' : '同步人員資料' }}
            </button>
          </div>
        </div>

        <!-- Mapping Issues -->
        <div v-if="mappingIssues.length > 0">
          <h3 class="text-lg font-medium text-red-600 dark:text-red-400 mb-3">
            映射問題檢測
          </h3>
          <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <ul class="space-y-2">
              <li v-for="issue in mappingIssues" :key="issue.id" class="flex items-center text-sm text-red-700 dark:text-red-300">
                <ExclamationTriangleIcon class="w-4 h-4 mr-2 flex-shrink-0" />
                題項 ID {{ issue.id }}: {{ issue.message }}
              </li>
            </ul>
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end">
          <button
            @click="showMappingTest = false"
            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-200"
          >
            關閉
          </button>
        </div>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ArrowPathIcon, DocumentTextIcon, CodeBracketIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

definePageMeta({
  middleware: 'auth'
})

const route = useRoute()
const companyId = route.params.companyId

// 獲取 URL 中的 token 參數
const token = computed(() => route.query.token)

// Enhanced questionId parsing with fallback
let questionId = parseInt(route.params.questionId)

// Debug route parameters
console.log('=== Web Content Page Route Debug ===')
console.log('Full route params:', route.params)
console.log('Raw companyId:', route.params.companyId)
console.log('Raw questionId:', route.params.questionId)
console.log('Parsed questionId:', questionId)
console.log('Is questionId NaN?', isNaN(questionId))
console.log('Route path:', route.path)
console.log('Route name:', route.name)

// Fallback: try to extract questionId from route path if parsing failed
if (isNaN(questionId)) {
  console.log('=== Attempting questionId fallback extraction ===')
  const pathParts = route.path.split('/')
  console.log('Path parts:', pathParts)

  // Find the questionId from path: /web/risk-assessment/questions/{companyId}/management/{questionId}/content
  const managementIndex = pathParts.findIndex(part => part === 'management')
  if (managementIndex !== -1 && pathParts[managementIndex + 1]) {
    const fallbackQuestionId = parseInt(pathParts[managementIndex + 1])
    if (!isNaN(fallbackQuestionId)) {
      console.log('Fallback extraction successful:', fallbackQuestionId)
      questionId = fallbackQuestionId
    } else {
      console.log('Fallback extraction failed:', pathParts[managementIndex + 1])
    }
  }
}

// Get company data from composable
const { getCompanyName } = useCompanies()
const companyName = computed(() => getCompanyName(companyId))

// Get external user data from pinia store
const externalUserStore = useExternalUserStore()

// Mock question management data (would be fetched from API/store)
const questionInfo = ref({
  id: questionId,
  templateId: questionId, // Use questionId as templateId to show the correct template
  year: 2024,
  createdAt: new Date('2024-01-15')
})

// Initialize reactive data
const questionContent = ref([])
const isRefreshing = ref(false)

// Tooltip data for description field
const descriptionTooltipData = ref({
  visible: false,
  content: '',
  x: 0,
  y: 0
})
let descriptionTooltipTimeout = null

// Mapping test functionality
const showMappingTest = ref(false)

// Assignment debugging
const assignmentDebugInfo = ref({
  loaded: false,
  total: 0,
  forUser: 0,
  forAssessment: 0,
  matching: 0,
  statusDistribution: {},
  userAssignments: []
})
const isCheckingAssignments = ref(false)
const isSyncingPersonnel = ref(false)

// Use templates store to get template info
const templatesStore = useTemplatesStore()
const templateInfo = computed(() =>
  templatesStore.templates.find(t => t.id === questionInfo.value.templateId) || { versionName: '未知範本' }
)

// Mapping test computed properties
const mappingStats = computed(() => {
  const uniqueCategories = new Set()
  const uniqueTopics = new Set()
  const uniqueFactors = new Set()

  questionContent.value.forEach(item => {
    if (item.category_id) uniqueCategories.add(item.category_id)
    if (item.topic_id) uniqueTopics.add(item.topic_id)
    if (item.risk_factor_id) uniqueFactors.add(item.risk_factor_id)
  })

  return {
    totalQuestions: questionContent.value.length,
    categoriesCount: uniqueCategories.size,
    topicsCount: uniqueTopics.size,
    factorsCount: uniqueFactors.size
  }
})

const mappingIssues = computed(() => {
  const issues = []

  questionContent.value.forEach(item => {
    if (!item.category_id && !item.topic_id && !item.risk_factor_id) {
      issues.push({
        id: item.id,
        message: '缺少所有映射關係（分類、主題、風險因子）'
      })
    } else if (!item.category_id) {
      issues.push({
        id: item.id,
        message: '缺少風險分類映射'
      })
    } else if (!item.topic_id) {
      issues.push({
        id: item.id,
        message: '缺少主題映射'
      })
    } else if (!item.risk_factor_id) {
      issues.push({
        id: item.id,
        message: '缺少風險因子映射'
      })
    }
  })

  return issues
})

// Mapping status functions
const getMappingStatus = (item) => {
  const hasCategory = !!item.category_id
  const hasTopic = !!item.topic_id
  const hasFactor = !!item.risk_factor_id

  if (hasCategory && hasTopic && hasFactor) {
    return '完整映射'
  } else if (hasCategory || hasTopic || hasFactor) {
    return '部分映射'
  } else {
    return '未映射'
  }
}

const getMappingStatusClass = (item) => {
  const status = getMappingStatus(item)

  switch (status) {
    case '完整映射':
      return 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
    case '部分映射':
      return 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
    case '未映射':
      return 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
    default:
      return 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
  }
}

// Response status indicator functions
const getResponseStatus = (item) => {
  if (item.response_count > 0) {
    return {
      color: 'yellow',
      label: '已提交',
      description: '使用者已提交答案'
    }
  } else {
    return {
      color: 'gray',
      label: '未作答',
      description: '尚未開始作答'
    }
  }
}

const getResponseStatusClass = (item) => {
  const status = getResponseStatus(item)

  switch (status.color) {
    case 'yellow':
      return 'bg-yellow-400 dark:bg-yellow-500'
    case 'gray':
      return 'bg-gray-300 dark:bg-gray-600'
    default:
      return 'bg-gray-300 dark:bg-gray-600'
  }
}

// Page title will be set after companyName is resolved
watch(companyName, (newValue) => {
  if (newValue) {
    usePageTitle(`題項內容管理 - ${newValue}`)
  }
}, { immediate: true })

// Load data from database API only with user filtering
const loadQuestionData = async () => {
  try {
    console.log('=== Starting loadQuestionData ===')
    console.log('Question ID:', questionId)
    console.log('User ID:', externalUserStore.userId)
    console.log('External ID:', externalUserStore.externalId)

    // Check if questionId is valid before making API call
    if (isNaN(questionId) || questionId <= 0) {
      console.error('Invalid questionId:', questionId)
      questionContent.value = []
      return
    }

    // Get user filtering parameters from pinia store
    const userId = externalUserStore.userId
    const externalId = externalUserStore.externalId

    // Prepare API URL with user filtering parameters
    let apiUrl = `/api/v1/question-management/assessment/${questionId}/contents`
    const urlParams = new URLSearchParams()

    // Add user_id parameter for filtering if available
    if (userId) {
      urlParams.append('user_id', userId.toString())
      console.log('Added user_id to API request:', userId)
    }

    // Add external_id parameter for filtering if available
    if (externalId) {
      urlParams.append('external_id', externalId.toString())
      console.log('Added external_id to API request:', externalId)
    }

    // Append parameters to URL if any filters are set
    if (urlParams.toString()) {
      apiUrl += `?${urlParams.toString()}`
    }

    console.log('Final API URL:', apiUrl)

    // Load content from database API with user filtering
    const contentResponse = await $fetch(apiUrl)

    console.log('API Response:', contentResponse)

    if (contentResponse.success && Array.isArray(contentResponse.data)) {
      // Transform API response to compatible format
      questionContent.value = contentResponse.data.map(item => ({
        id: item.id,
        category_id: item.category_id,
        topic_id: item.topic_id,
        risk_factor_id: item.factor_id,
        topic: item.title || '未命名題目',
        description: item.factor_description || '',  // 從 question_factors.description 欄位取得
        order: item.sort_order || 0,
        response_count: item.response_count || 0,
        category_name: item.category_name || '',
        topic_name: item.topic_name || '',
        factor_name: item.factor_name || ''
      }))

      console.log('Processed question content:', questionContent.value)
    } else {
      console.warn('API response invalid or empty:', contentResponse)
      questionContent.value = []
    }

  } catch (error) {
    console.error('=== Error loading question data from API ===')
    console.error('Error type:', error.constructor.name)
    console.error('Error message:', error.message)
    console.error('Error details:', error)

    // 檢查是否有後端返回的詳細錯誤資訊
    if (error.data && error.data.debug) {
      console.error('Backend debug info:', error.data.debug)
    }

    questionContent.value = []

    // 顯示用戶友好的錯誤訊息
    const toast = useToast()
    toast.add({
      title: '載入題目資料失敗',
      description: `API 錯誤: ${error.message || '未知錯誤'}`,
      color: 'red'
    })
  }
}

// Refresh data function
const refreshData = async () => {
  if (isRefreshing.value) return

  isRefreshing.value = true
  console.log('Refreshing question content data...')

  try {
    await loadQuestionData()

    const toast = useToast()
    toast.add({
      title: '重新整理完成',
      description: '題目資料已更新',
      color: 'green'
    })

    console.log('Question content refresh completed successfully')
  } catch (error) {
    console.error('Refresh question content error:', error)
    const toast = useToast()
    toast.add({
      title: '重新整理失敗',
      description: '無法更新題目資料，請稍後再試',
      color: 'red'
    })
  } finally {
    isRefreshing.value = false
  }
}

// No longer needed - data is saved directly to database via API calls

// DataTable columns configuration
const columns = ref([
  {
    key: 'actions',
    label: '操作',
    sortable: false,
    cellClass: 'text-center'
  },
  {
    key: 'description',
    label: '風險因子描述',
    sortable: true,
    cellClass: 'text-base text-gray-900 dark:text-white'
  },
  {
    key: 'categories',
    label: '分類標籤',
    sortable: false,
    cellClass: 'text-base text-gray-900 dark:text-white'
  },
  {
    key: 'status',
    label: '回答狀態',
    sortable: true,
    cellClass: 'text-center'
  }
])

// 映射測試功能
const openMappingTest = () => {
  console.log('=== 開啟映射測試 ===')
  console.log('目前題項資料:', questionContent.value)
  console.log('統計資訊:', mappingStats.value)
  console.log('映射問題:', mappingIssues.value)
  console.log('用戶資訊:', {
    userId: externalUserStore.userId,
    externalId: externalUserStore.externalId
  })

  showMappingTest.value = true
}

// 作答功能 - 導航到作答頁面
const startAnswer = (questionItem) => {
  const router = useRouter()

  console.log('開始作答題目:', questionItem)

  // 導航到作答頁面，不傳遞 query 參數，所有資訊從 URL ID 取得
  router.push({
    path: `/web/risk-assessment/questions/${companyId}/answer/${questionId}/${questionItem.id}`
  })
}

// 檢查指派記錄的除錯功能
const checkAssignments = async () => {
  if (isCheckingAssignments.value) return

  isCheckingAssignments.value = true

  try {
    console.log('=== 開始檢查指派記錄 ===')
    console.log('評估ID:', questionId)
    console.log('用戶ID:', externalUserStore.userId)

    // 調用新的除錯API端點
    const response = await $fetch(`/api/v1/personnel-assignments/debug/assessment/${questionId}`, {
      method: 'GET',
      query: {
        user_id: externalUserStore.userId
      }
    })

    if (response.success) {
      assignmentDebugInfo.value = {
        loaded: true,
        total: response.data.total_assignments,
        forUser: response.data.user_assignments,
        forAssessment: response.data.assessment_assignments,
        matching: response.data.matching_assignments,
        statusDistribution: response.data.status_distribution,
        userAssignments: response.data.user_assignment_details || []
      }

      console.log('✅ 指派記錄檢查完成:', assignmentDebugInfo.value)

      const toast = useToast()
      toast.add({
        title: '指派記錄檢查完成',
        description: `找到 ${response.data.matching_assignments} 筆匹配的指派記錄`,
        color: response.data.matching_assignments > 0 ? 'green' : 'orange'
      })
    } else {
      throw new Error(response.message || '檢查指派記錄失敗')
    }

  } catch (error) {
    console.error('❌ 檢查指派記錄失敗:', error)

    assignmentDebugInfo.value = {
      loaded: true,
      total: 0,
      forUser: 0,
      forAssessment: 0,
      matching: 0,
      statusDistribution: {},
      userAssignments: []
    }

    const toast = useToast()
    toast.add({
      title: '檢查指派記錄失敗',
      description: '無法取得指派記錄資訊，請檢查API連線',
      color: 'red'
    })
  } finally {
    isCheckingAssignments.value = false
  }
}

// 截斷文字輔助函數
const truncateText = (text, maxLength) => {
  if (!text) return ''

  // 移除 HTML 標籤來計算純文字長度
  const plainText = text.replace(/<[^>]*>/g, '')

  if (plainText.length <= maxLength) {
    return plainText
  }

  return plainText.substring(0, maxLength) + '...'
}

// Description tooltip 控制函數
const showDescriptionTooltip = (event, item) => {
  const content = item.description
  if (!content) return

  // Clear any existing timeout
  if (descriptionTooltipTimeout) {
    clearTimeout(descriptionTooltipTimeout)
  }

  // Get the element's position
  const rect = event.target.getBoundingClientRect()

  // Position tooltip below the element
  descriptionTooltipData.value = {
    visible: true,
    content: content,
    x: rect.left,
    y: rect.bottom + 8 // 8px gap below the element
  }
}

const hideDescriptionTooltip = () => {
  // Add a small delay to allow mouse to move to tooltip
  descriptionTooltipTimeout = setTimeout(() => {
    descriptionTooltipData.value.visible = false
  }, 100)
}

const keepDescriptionTooltipOpen = () => {
  // Cancel hide timeout when mouse enters tooltip
  if (descriptionTooltipTimeout) {
    clearTimeout(descriptionTooltipTimeout)
  }
}

// 除錯人員同步功能
const debugPersonnelSync = async () => {
  if (isSyncingPersonnel.value) return

  isSyncingPersonnel.value = true

  try {
    console.log('=== 開始人員資料同步除錯 ===')
    console.log('公司ID:', companyId)
    console.log('外部用戶ID:', externalUserStore.externalId)

    // 先嘗試同步人員資料
    const syncResponse = await $fetch(`/api/v1/personnel/companies/${companyId}/sync`, {
      method: 'POST'
    })

    if (syncResponse.success) {
      console.log('✅ 人員同步成功:', syncResponse)

      // 重新載入內部用戶ID
      if (externalUserStore.externalId) {
        const newInternalId = await externalUserStore.fetchInternalUserId(externalUserStore.externalId)
        console.log('🔄 重新載入的內部用戶ID:', newInternalId)
      }

      // 重新載入題目資料
      await loadQuestionData()

      const toast = useToast()
      toast.add({
        title: '人員同步完成',
        description: '人員資料已更新，題目資料已重新載入',
        color: 'green'
      })
    } else {
      throw new Error(syncResponse.message || '人員同步失敗')
    }

  } catch (error) {
    console.error('❌ 人員同步失敗:', error)

    const toast = useToast()
    toast.add({
      title: '人員同步失敗',
      description: '無法同步人員資料，請聯繫管理員',
      color: 'red'
    })
  } finally {
    isSyncingPersonnel.value = false
  }
}


onMounted(async () => {
  // 優先調用用戶資料解密 API 並儲存到 Pinia Store
  if (token.value) {
    try {
      await externalUserStore.fetchExternalUserData(token.value)
    } catch (error) {
      console.error('Failed to fetch external user data:', error)
      // 即使用戶資料載入失敗，仍繼續載入頁面
      // 可選：顯示友善的錯誤提示
    }
  }

  try {
    // Only proceed if questionId is valid
    if (isNaN(questionId) || questionId <= 0) {
      console.error('Invalid questionId, skipping data loading')
      return
    }

    // Load content data from database API (with user filtering)
    await loadQuestionData()
  } catch (error) {
    console.error('Error in content page initialization:', error)
  }
})
</script>