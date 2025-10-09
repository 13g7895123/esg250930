<template>
  <ContentManagement
    :title="`題項內容管理 - ${questionInfo?.year || new Date().getFullYear()}年度`"
    description="管理風險評估題項的主題內容"
    :show-back-button="true"
    :back-path="`/admin/risk-assessment/questions/${companyId}/management`"
    :content-data="questionContent"
    :risk-categories="questionCategories"
    :risk-topics="questionTopics"
    :risk-factors="questionFactors"
    :risk-topics-enabled="enableTopicLayer"
    :show-risk-category-button="false"
    content-type="question"
    :parent-id="questionId"
    :is-refreshing="isRefreshing"
    @add-content="addQuestionContent"
    @update-content="updateQuestionContent"
    @delete-content="deleteQuestionContent"
    @reorder-content="reorderQuestionContent"
    @add-category="addRiskCategory"
    @update-category="updateRiskCategory"
    @delete-category="deleteRiskCategory"
    @fetch-topics="handleFetchTopics"
    @fetch-factors="handleFetchFactors"
    @refresh-content="handleRefreshContent"
  />
</template>

<script setup>
definePageMeta({
  middleware: 'auth'
})

const route = useRoute()
const companyId = route.params.companyId
const questionId = parseInt(route.params.questionId)

// Use stores
const questionManagementStore = useQuestionManagementStore()

// Get company data from composable
const { getCompanyName } = useCompanies()
const companyName = computed(() => getCompanyName(companyId))

// Get assessment info from store
const questionInfo = questionManagementStore.getAssessmentById(questionId)

// Get content and structure data from store
const questionContent = questionManagementStore.getQuestionContent(questionId)
const questionCategories = questionManagementStore.getQuestionCategories(questionId)
const questionTopics = questionManagementStore.getQuestionTopics(questionId)
const questionFactors = questionManagementStore.getQuestionFactors(questionId)

// Topic layer toggle state
const enableTopicLayer = ref(true)

// Refresh state
const isRefreshing = ref(false)

// Load topic layer setting from localStorage (set from management page)
const loadTopicLayerSetting = () => {
  if (process.client) {
    try {
      const settingKey = `question_management_${questionId}_topic_layer`
      const stored = localStorage.getItem(settingKey)
      if (stored !== null) {
        enableTopicLayer.value = JSON.parse(stored)
      }
    } catch (error) {
      console.error('Error loading topic layer setting:', error)
    }
  }
}

// Update page title based on assessment year
const updatePageTitle = () => {
  const yearDisplay = questionInfo.value?.year ? `(${questionInfo.value.year}年度)` : `#${questionId}`
  usePageTitle(`題項內容管理${yearDisplay}`)
}

// Watch for year changes to update page title
watch(() => questionInfo.value?.year, () => {
  updatePageTitle()
}, { immediate: true })

// Content management methods using store
const addQuestionContent = async (contentData) => {
  const { showSuccess, showError } = useNotification()

  // 保存當前滾動位置
  const scrollPosition = window.scrollY || document.documentElement.scrollTop

  try {
    await questionManagementStore.addQuestionContent(questionId, contentData)

    // 重新載入資料
    await questionManagementStore.refreshAssessment(questionId)

    await showSuccess('新增成功', '題目已成功新增')

    // 等待 DOM 更新後恢復滾動位置
    await nextTick()
    window.scrollTo({ top: scrollPosition, behavior: 'instant' })
  } catch (error) {
    console.error('Failed to add question content:', error)
    await showError('新增失敗', error?.message || '無法新增題目，請稍後再試')
  }
}

const updateQuestionContent = async (contentId, contentData) => {
  console.log('[Content Page] updateQuestionContent called with contentId:', contentId)
  console.log('[Content Page] contentData received:', contentData)
  console.log('[Content Page] contentData.factorDescription:', contentData.factorDescription)

  const { showSuccess, showError } = useNotification()

  // 保存當前滾動位置
  const scrollPosition = window.scrollY || document.documentElement.scrollTop

  try {
    await questionManagementStore.updateQuestionContent(questionId, contentId, contentData)

    // 重新載入資料
    await questionManagementStore.refreshAssessment(questionId)

    await showSuccess('更新成功', '題目已成功更新')

    // 等待 DOM 更新後恢復滾動位置
    await nextTick()
    window.scrollTo({ top: scrollPosition, behavior: 'instant' })
  } catch (error) {
    console.error('Failed to update question content:', error)
    await showError('更新失敗', error?.message || '無法更新題目，請稍後再試')
  }
}

const deleteQuestionContent = async (contentId) => {
  const { showSuccess, showError } = useNotification()

  try {
    await questionManagementStore.deleteQuestionContent(questionId, contentId)

    // 重新載入資料
    await questionManagementStore.refreshAssessment(questionId)

    await showSuccess('刪除成功', '題目已成功刪除')
  } catch (error) {
    console.error('Failed to delete question content:', error)
    await showError('刪除失敗', error?.message || '無法刪除題目，請稍後再試')
  }
}

const reorderQuestionContent = async (newOrder) => {
  const { showSuccess, showError } = useNotification()

  // 保存當前滾動位置
  const scrollPosition = window.scrollY || document.documentElement.scrollTop

  try {
    await questionManagementStore.reorderQuestionContent(questionId, newOrder)

    await showSuccess('排序成功', '題目順序已成功更新')

    // 等待 DOM 更新後恢復滾動位置
    await nextTick()
    window.scrollTo({ top: scrollPosition, behavior: 'instant' })
  } catch (error) {
    console.error('Failed to reorder question content:', error)
    await showError('排序失敗', error?.message || '無法更新題目順序，請稍後再試')

    // 重新載入資料以恢復正確的順序
    await questionManagementStore.refreshAssessment(questionId)
  }
}

// Category management methods - Categories are now managed through structure API
const addRiskCategory = (categoryData) => {
  // TODO: Implement category API when structure management is extended
}

const updateRiskCategory = (categoryId, categoryData) => {
  // TODO: Implement category API when structure management is extended
}

const deleteRiskCategory = (categoryId) => {
  // TODO: Implement category API when structure management is extended
}

// Event handlers for ContentManagement component
const handleFetchTopics = async (categoryId) => {
  // 前端篩選邏輯已經在ContentManagement組件中處理
}

const handleFetchFactors = async ({ categoryId, topicId }) => {
  // 前端篩選邏輯已經在ContentManagement組件中處理
}

const handleRefreshContent = async () => {
  if (isRefreshing.value) return

  isRefreshing.value = true
  const { showSuccess, showError } = useNotification()

  try {
    await questionManagementStore.refreshAssessment(questionId)

    // Show success notification
    await showSuccess('內容資料已更新')
  } catch (error) {
    console.error('Refresh content error:', error)
    await showError('無法更新內容資料，請稍後再試')
  } finally {
    isRefreshing.value = false
  }
}

onMounted(async () => {
  // Load topic layer setting from management page
  loadTopicLayerSetting()

  // Initialize store data (load all data for this assessment)
  try {
    await questionManagementStore.initialize(questionId)

    // Update page title with final data
    updatePageTitle()
  } catch (error) {
    console.error('Error initializing content page:', error)
  }
})
</script>
