<template>
  <div class="p-6">
    <!-- Page Header -->
    <div class="mb-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
            填寫評估表 - {{ companyName }}
          </h1>
          <p class="text-gray-600 dark:text-gray-400">
            {{ questionInfo?.year || 2024 }}年度 {{ templateInfo?.versionName || '風險評估' }}
          </p>
        </div>
        <div class="flex items-center space-x-4">
          <button
            @click="saveProgress"
            :disabled="saving"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ saving ? '儲存中...' : '儲存進度' }}
          </button>
          <nuxt-link
            :to="`/web/risk-assessment/questions/${companyId}/management`"
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
          >
            返回管理頁面
          </nuxt-link>
        </div>
      </div>
    </div>

    <!-- Progress Indicator -->
    <div v-if="questionContent.length > 0" class="mb-6">
      <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
        <span>完成進度</span>
        <span>{{ completedCount }}/{{ questionContent.length }}</span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
        <div
          class="bg-green-600 h-2 rounded-full transition-all duration-300"
          :style="{ width: `${progressPercentage}%` }"
        ></div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      <span class="ml-3 text-gray-600 dark:text-gray-400">載入題目中...</span>
    </div>

    <!-- Empty State -->
    <div v-else-if="questionContent.length === 0" class="text-center py-12">
      <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
        <DocumentTextIcon class="w-8 h-8 text-gray-400" />
      </div>
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">尚未建立評估題目</h3>
      <p class="text-gray-500 dark:text-gray-400">請聯繫管理員建立評估題目</p>
    </div>

    <!-- Assessment Content -->
    <div v-else class="space-y-8">
      <!-- Group by Category -->
      <div
        v-for="category in categorizedContent"
        :key="category.id"
        class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden"
      >
        <!-- Category Header -->
        <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-b border-gray-200 dark:border-gray-600">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ category.name || `分類 ${category.id}` }}
          </h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ category.items.length }} 個評估項目
          </p>
        </div>

        <!-- Category Content -->
        <div class="divide-y divide-gray-200 dark:divide-gray-600">
          <div
            v-for="(item, index) in category.items"
            :key="item.id"
            class="p-6"
          >
            <!-- Question -->
            <div class="mb-4">
              <div class="flex items-start justify-between mb-2">
                <h4 class="text-base font-medium text-gray-900 dark:text-white">
                  {{ index + 1 }}. {{ item.topic || '未命名題目' }}
                </h4>
                <span
                  v-if="responses[item.id]"
                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200"
                >
                  已回答
                </span>
              </div>
              <p v-if="item.description" class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                {{ item.description }}
              </p>
            </div>

            <!-- Answer Input -->
            <div class="space-y-4">
              <!-- Text Response -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  回答內容
                </label>
                <textarea
                  v-model="responses[item.id].answer"
                  @input="updateResponse(item.id, 'answer', $event.target.value)"
                  rows="4"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                  placeholder="請輸入您的回答..."
                ></textarea>
              </div>

              <!-- Score Input -->
              <div class="flex items-center space-x-4">
                <div class="flex-1">
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    評分 (1-5分)
                  </label>
                  <select
                    v-model="responses[item.id].score"
                    @change="updateResponse(item.id, 'score', $event.target.value)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                  >
                    <option value="">請選擇分數</option>
                    <option value="1">1分 - 非常不符合</option>
                    <option value="2">2分 - 不符合</option>
                    <option value="3">3分 - 普通</option>
                    <option value="4">4分 - 符合</option>
                    <option value="5">5分 - 非常符合</option>
                  </select>
                </div>
              </div>

              <!-- Evidence Upload -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  佐證文件 (選填)
                </label>
                <div class="flex items-center justify-center w-full">
                  <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                      <CloudArrowUpIcon class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" />
                      <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                        <span class="font-semibold">點擊上傳</span> 或拖拽檔案到此處
                      </p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG, PDF (最大 10MB)</p>
                    </div>
                    <input type="file" class="hidden" multiple accept=".png,.jpg,.jpeg,.pdf" />
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Fixed Bottom Actions -->
    <div class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-4">
      <div class="max-w-7xl mx-auto flex items-center justify-between">
        <div class="text-sm text-gray-600 dark:text-gray-400">
          已完成 {{ completedCount }}/{{ questionContent.length }} 題
        </div>
        <div class="flex items-center space-x-4">
          <button
            @click="saveProgress"
            :disabled="saving || completedCount === 0"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ saving ? '儲存中...' : '儲存進度' }}
          </button>
          <button
            @click="submitAssessment"
            :disabled="saving || completedCount < questionContent.length"
            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ saving ? '提交中...' : '提交評估' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Bottom padding for fixed actions -->
    <div class="h-20"></div>
  </div>
</template>

<script setup>
definePageMeta({
  middleware: 'auth'
})

import {
  DocumentTextIcon,
  CloudArrowUpIcon
} from '@heroicons/vue/24/outline'

const route = useRoute()
const companyId = route.params.id
const questionId = parseInt(route.params.questionId)

// Loading states
const loading = ref(true)
const saving = ref(false)

// Get company data from composable
const { getCompanyNameById } = useLocalCompanies()

// Company name reactive
const companyName = ref('載入中...')

// Load company name asynchronously
const loadCompanyName = async () => {
  try {
    const name = await getCompanyNameById(companyId)
    companyName.value = name || `公司 #${companyId}`
  } catch (error) {
    console.error('Error loading company name:', error)
    companyName.value = `公司 #${companyId}`
  }
}

// Mock question management data
const questionInfo = ref({
  id: questionId,
  templateId: questionId,
  year: 2024,
  createdAt: new Date('2024-01-15')
})

// Use templates store to get template info
const templatesStore = useTemplatesStore()
const templateInfo = computed(() =>
  templatesStore.templates.find(t => t.id === questionInfo.value.templateId) || { versionName: '風險評估' }
)

// Question structure composable
const {
  structureLoading,
  categories: questionCategories,
  topics: questionTopics,
  factors: questionFactors,
  getAssessmentStructure,
  getCategories,
  getTopics,
  getFactors
} = useQuestionStructure()

// Data
const questionContent = ref([])
const responses = ref({})

// Load question data from database API
const loadQuestionData = async () => {
  try {
    console.log('Loading question data from database API...')

    const contentResponse = await $fetch(`/api/v1/question-management/assessment/${questionId}/contents`)

    if (contentResponse.success && Array.isArray(contentResponse.data)) {
      questionContent.value = contentResponse.data.map(item => ({
        id: item.id,
        category_id: item.category_id,
        topic_id: item.topic_id,
        risk_factor_id: item.factor_id,
        topic: item.title || '未命名題目',
        description: item.description || '',
        order: item.sort_order || 0
      }))

      // Initialize responses
      questionContent.value.forEach(item => {
        responses.value[item.id] = {
          answer: '',
          score: '',
          evidence: []
        }
      })

      console.log('Loaded question content:', questionContent.value.length, 'items')
    } else {
      questionContent.value = []
      console.warn('No content found in database')
    }
  } catch (error) {
    console.error('Error loading question data:', error)
    questionContent.value = []
  }
}

// Load question structure data from database
const loadQuestionStructureData = async () => {
  try {
    console.log('Loading question structure data for assessment:', questionId)

    await getAssessmentStructure(questionId)
    await getCategories(questionId)
    await getTopics(questionId)
    await getFactors(questionId)

    console.log('Question structure loaded successfully')
  } catch (error) {
    console.error('Error loading question structure data:', error)
  }
}

// Computed properties
const categorizedContent = computed(() => {
  if (!questionContent.value || questionContent.value.length === 0) return []

  const categoryMap = new Map()

  questionContent.value.forEach(item => {
    const categoryId = item.category_id || 'uncategorized'

    if (!categoryMap.has(categoryId)) {
      const category = questionCategories.value?.find(c => c.id === categoryId)
      categoryMap.set(categoryId, {
        id: categoryId,
        name: category?.category || '未分類',
        items: []
      })
    }

    categoryMap.get(categoryId).items.push(item)
  })

  return Array.from(categoryMap.values()).sort((a, b) => a.name.localeCompare(b.name))
})

const completedCount = computed(() => {
  return Object.values(responses.value).filter(response =>
    response.answer.trim() !== '' && response.score !== ''
  ).length
})

const progressPercentage = computed(() => {
  if (questionContent.value.length === 0) return 0
  return Math.round((completedCount.value / questionContent.value.length) * 100)
})

// Methods
const updateResponse = (itemId, field, value) => {
  if (!responses.value[itemId]) {
    responses.value[itemId] = { answer: '', score: '', evidence: [] }
  }
  responses.value[itemId][field] = value
}

const saveProgress = async () => {
  if (saving.value) return

  saving.value = true
  try {
    // TODO: Implement API call to save progress
    console.log('Saving progress:', responses.value)

    await new Promise(resolve => setTimeout(resolve, 1000)) // Mock delay

    // Show success message
    const { showNotification } = useNotifications()
    showNotification('進度已儲存', 'success')

  } catch (error) {
    console.error('Error saving progress:', error)
    const { showNotification } = useNotifications()
    showNotification('儲存失敗，請稍後再試', 'error')
  } finally {
    saving.value = false
  }
}

const submitAssessment = async () => {
  if (saving.value || completedCount.value < questionContent.value.length) return

  saving.value = true
  try {
    // TODO: Implement API call to submit assessment
    console.log('Submitting assessment:', responses.value)

    await new Promise(resolve => setTimeout(resolve, 2000)) // Mock delay

    // Show success message and redirect
    const { showNotification } = useNotifications()
    showNotification('評估已成功提交', 'success')

    // Redirect back to management page
    await navigateTo(`/web/risk-assessment/questions/${companyId}/management`)

  } catch (error) {
    console.error('Error submitting assessment:', error)
    const { showNotification } = useNotifications()
    showNotification('提交失敗，請稍後再試', 'error')
  } finally {
    saving.value = false
  }
}

// Page title
const pageTitle = computed(() => `填寫評估表 - ${companyName.value}`)
usePageTitle(pageTitle)

// Initialize
onMounted(async () => {
  try {
    console.log('=== 新的評估表填寫頁面載入 ===')
    console.log('Web assessment content page mounted for:', { companyId, questionId })
    console.log('Route params:', route.params)
    console.log('Route path:', route.path)
    console.log('Route name:', route.name)

    loading.value = true

    // Load company name
    await loadCompanyName()

    // Load structure data first
    await loadQuestionStructureData()

    // Load question content data
    await loadQuestionData()

    // Initialize templates store
    try {
      await templatesStore.initialize()
    } catch (error) {
      console.error('Failed to load templates from database:', error)
    }

    console.log('=== 新的評估表填寫頁面載入完成 ===')
  } catch (error) {
    console.error('Error in content page initialization:', error)
  } finally {
    loading.value = false
  }
})
</script>