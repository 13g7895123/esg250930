<template>
  <div>
    <!-- Debug Info -->
    <div v-if="isDevelopment" class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
      <h3 class="text-sm font-bold mb-2">除錯資訊</h3>
      <p class="text-xs">風險類別數量: {{ questionCategories?.length || 0 }}</p>
      <p class="text-xs">風險主題數量: {{ questionTopics?.length || 0 }}</p>
      <p class="text-xs">風險因子數量: {{ questionFactors?.length || 0 }}</p>
      <p class="text-xs">主題層級啟用: {{ enableTopicLayer }}</p>
    </div>

    <ContentManagement
      :title="`題項內容管理 - ${questionInfo?.year || new Date().getFullYear()}年度`"
      description="管理風險評估題項的主題內容"
      :content-data="questionContent || []"
      :risk-categories="questionCategories || riskCategories || []"
      :risk-topics="questionTopics || []"
      :risk-factors="questionFactors || []"
      :risk-topics-enabled="enableTopicLayer"
      :show-risk-category-button="false"
      content-type="question"
      :parent-id="questionId"
      :is-refreshing="structureLoading"
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
  </div>
</template>

<script setup>
definePageMeta({
  middleware: 'auth'
})

const route = useRoute()
const companyId = route.params.companyId

// Enhanced questionId parsing with fallback
let questionId = parseInt(route.params.questionId)

// Debug route parameters
console.log('=== Admin Content Page Route Debug ===')
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

  // Find the questionId from path: /admin/risk-assessment/questions/{companyId}/management/{questionId}/content
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

// Question assessment data (fetched from API)
const questionInfo = ref({
  id: questionId,
  templateId: null,
  year: null,
  createdAt: null,
  templateVersion: null
})

// Use templates store to get template info
const templatesStore = useTemplatesStore()
const templateInfo = computed(() => {
  // If we have template version from the assessment data, use it directly
  if (questionInfo.value.templateVersion) {
    return { versionName: questionInfo.value.templateVersion }
  }

  // Otherwise, try to find it in the templates store
  const template = templatesStore.templates.find(t => t.id === questionInfo.value.templateId)
  return template || { versionName: '未知範本' }
})

// Use question structure composable for database-driven risk structure
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

// Topic layer toggle state
const enableTopicLayer = ref(true)

// Debug mode control - can be toggled to show/hide debug info
const showDebugInfo = ref(false)

// Development mode check (SSR safe) - currently disabled
const isDevelopment = computed(() => {
  return showDebugInfo.value
})

// Load topic layer setting from localStorage (set from management page)
const loadTopicLayerSetting = () => {
  if (process.client) {
    try {
      const settingKey = `question_management_${questionId}_topic_layer`
      const stored = localStorage.getItem(settingKey)
      if (stored !== null) {
        enableTopicLayer.value = JSON.parse(stored)
        console.log('Loaded topic layer setting:', enableTopicLayer.value)
      }
    } catch (error) {
      console.error('Error loading topic layer setting:', error)
    }
  }
}

// Function to update page title
const updatePageTitle = () => {
  const yearDisplay = questionInfo.value.year ? `(${questionInfo.value.year}年度)` : `#${questionId}`
  usePageTitle(`題項內容管理${yearDisplay}`)
  console.log('📝 Page title updated to:', `題項內容管理${yearDisplay}`)
}

// Page title will be set after questionInfo is resolved
watch(() => questionInfo.value.year, (newYear) => {
  console.log('🔄 Year changed in watch:', newYear)
  updatePageTitle()
}, { immediate: true })

// Also update title when company name loads (fallback)
watch(companyName, (newValue) => {
  if (newValue && !questionInfo.value.year) {
    console.log('🔄 Company name loaded, setting fallback title')
    updatePageTitle()
  }
}, { immediate: true })

// Database-only content management (no localStorage)

// Initialize reactive data
const questionContent = ref([])
const riskCategories = ref([])

// Load assessment info and question data from database API
const loadQuestionData = async () => {
  try {
    console.log('Loading question data from database API...')
    console.log('Using questionId for API call:', questionId)

    // Check if questionId is valid before making API call
    if (isNaN(questionId) || questionId <= 0) {
      console.error('Invalid questionId detected:', questionId)
      console.error('Cannot make API call with invalid questionId')
      questionContent.value = []
      return
    }

    // First, load the assessment information to get the year and template data
    try {
      console.log('Fetching assessment info for ID:', questionId)
      const assessmentResponse = await $fetch(`/api/v1/risk-assessment/company-assessments/${questionId}`)

      if (assessmentResponse.success && assessmentResponse.data) {
        const assessment = assessmentResponse.data
        console.log('Assessment data loaded:', assessment)

        // Update questionInfo with real data from API
        questionInfo.value = {
          id: questionId,
          templateId: assessment.template_id,
          year: assessment.assessment_year,
          createdAt: assessment.created_at,
          templateVersion: assessment.template_version_name || assessment.version_name
        }

        console.log('Updated questionInfo with real data:', questionInfo.value)

        // Explicitly update page title with the new year data
        updatePageTitle()
        console.log('✅ Assessment data loaded and page title updated')
      } else {
        console.warn('Failed to load assessment data:', assessmentResponse)
      }
    } catch (assessmentError) {
      console.error('Error loading assessment data:', assessmentError)
      // Use fallback values but don't fail completely
      questionInfo.value.year = questionInfo.value.year || new Date().getFullYear()
    }

    // Load content from database API
    const contentResponse = await $fetch(`/api/v1/question-management/assessment/${questionId}/contents`)

    if (contentResponse.success && Array.isArray(contentResponse.data)) {
      // Transform API response to compatible format
      questionContent.value = contentResponse.data.map(item => ({
        id: item.id,
        category_id: item.category_id,
        topic_id: item.topic_id,
        risk_factor_id: item.factor_id,
        topic: item.title || '未命名題目',
        description: item.description || '',
        order: item.sort_order || 0
      }))
      console.log('Loaded question content from API:', questionContent.value.length, 'items')
    } else {
      console.warn('No content found in database')
      questionContent.value = []
    }

    // Categories are managed through structure data, initialize as empty
    riskCategories.value = []

  } catch (error) {
    console.error('Error loading question data from API:', error)
    throw error // Re-throw to let caller handle the error
  }
}

// No longer needed - data is saved directly to database via API calls

// Content management methods using database API only
const addQuestionContent = async (contentData) => {
  // Check if questionId is valid
  if (isNaN(questionId) || questionId <= 0) {
    console.error('Cannot add content: Invalid questionId:', questionId)
    throw new Error('Invalid questionId')
  }

  const apiData = {
    category_id: contentData.categoryId,
    topic_id: contentData.topicId || null,
    factor_id: contentData.riskFactorId || null,
    title: contentData.topic || '',
    description: contentData.description || '',
    sort_order: questionContent.value.length + 1
  }

  console.log('Creating content via API:', apiData)

  const response = await $fetch(`/api/v1/question-management/assessment/${questionId}/contents`, {
    method: 'POST',
    body: apiData
  })

  if (response.success && response.data) {
    // Transform API response to compatible format
    const newContent = {
      id: response.data.id,
      category_id: response.data.category_id,
      topic_id: response.data.topic_id,
      risk_factor_id: response.data.factor_id,
      topic: response.data.title || '',
      description: response.data.description || '',
      order: response.data.sort_order || 0
    }

    // Add to local state
    questionContent.value.unshift(newContent)
    console.log('Content created successfully:', newContent)
  } else {
    throw new Error(response.message || '建立內容失敗')
  }
}

const updateQuestionContent = async (contentId, contentData) => {
  const index = questionContent.value.findIndex(c => c.id === contentId)
  if (index === -1) {
    throw new Error('找不到要更新的內容')
  }

  const apiData = {
    category_id: contentData.categoryId,
    topic_id: contentData.topicId || null,
    factor_id: contentData.riskFactorId || null,
    title: contentData.topic || '',
    description: contentData.description || ''
  }

  console.log('Updating content via API:', contentId, apiData)

  const response = await $fetch(`/api/v1/question-management/contents/${contentId}`, {
    method: 'PUT',
    body: apiData
  })

  if (response.success && response.data) {
    // Update local state with API response
    questionContent.value[index] = {
      id: response.data.id,
      category_id: response.data.category_id,
      topic_id: response.data.topic_id,
      risk_factor_id: response.data.factor_id,
      topic: response.data.title || '',
      description: response.data.description || '',
      order: response.data.sort_order || questionContent.value[index].order
    }
    console.log('Content updated successfully')
  } else {
    throw new Error(response.message || '更新內容失敗')
  }
}

const deleteQuestionContent = async (contentId) => {
  const index = questionContent.value.findIndex(c => c.id === contentId)
  if (index === -1) {
    throw new Error('找不到要刪除的內容')
  }

  console.log('Deleting content via API:', contentId)

  const response = await $fetch(`/api/v1/question-management/contents/${contentId}`, {
    method: 'DELETE'
  })

  if (response.success) {
    // Remove from local state
    questionContent.value.splice(index, 1)
    console.log('Content deleted successfully')
  } else {
    throw new Error(response.message || '刪除內容失敗')
  }
}

const reorderQuestionContent = (newOrder) => {
  questionContent.value = newOrder.map((item, index) => ({
    ...item,
    order: index + 1
  }))
  // TODO: Implement API call for reordering when needed
}

// Category management methods - Categories are now managed through structure API
const addRiskCategory = (categoryData) => {
  // TODO: Implement category API when structure management is extended
  const newCategory = {
    id: `temp_cat_${Date.now()}`,
    category: categoryData.category
  }
  riskCategories.value.unshift(newCategory)
}

const updateRiskCategory = (categoryId, categoryData) => {
  // TODO: Implement category API when structure management is extended
  const index = riskCategories.value.findIndex(c => c.id === categoryId)
  if (index > -1) {
    riskCategories.value[index].category = categoryData.category
  }
}

const deleteRiskCategory = (categoryId) => {
  // TODO: Implement category API when structure management is extended
  const index = riskCategories.value.findIndex(c => c.id === categoryId)
  if (index > -1) {
    riskCategories.value.splice(index, 1)
  }
}

// Load question structure data from database
const loadQuestionStructureData = async () => {
  try {
    console.log('Loading question structure data for assessment:', questionId)

    // Check if questionId is valid before loading structure
    if (isNaN(questionId) || questionId <= 0) {
      console.error('Cannot load structure: Invalid questionId:', questionId)
      questionCategories.value = []
      questionTopics.value = []
      questionFactors.value = []
      return
    }

    // Load the complete assessment structure
    await getAssessmentStructure(questionId)

    // Load categories
    await getCategories(questionId)
    console.log('Loaded categories:', questionCategories.value)

    // Load topics
    await getTopics(questionId)
    console.log('Loaded topics:', questionTopics.value)

    // Load all factors for this assessment (without filtering)
    await getFactors(questionId)
    console.log('Loaded factors:', questionFactors.value)

    console.log('Question structure loaded:', {
      categories: questionCategories.value?.length || 0,
      topics: questionTopics.value?.length || 0,
      factors: questionFactors.value?.length || 0
    })

    // Additional debugging for factors
    if (questionFactors.value?.length > 0) {
      console.log('Sample factor:', questionFactors.value[0])
    }
  } catch (error) {
    console.error('Error loading question structure data:', error)
    // Set empty arrays as fallback
    questionCategories.value = []
    questionTopics.value = []
    questionFactors.value = []
  }
}

// Event handlers for ContentManagement component
const handleFetchTopics = async (categoryId) => {
  console.log('handleFetchTopics called with categoryId:', categoryId)
  // 避免重新調用API，直接使用已載入的數據
  // 前端篩選邏輯已經在ContentManagement組件中處理
  console.log('Topics fetched successfully, count:', questionTopics.value?.length || 0)
}

const handleFetchFactors = async ({ categoryId, topicId }) => {
  console.log('handleFetchFactors called with:', { categoryId, topicId })
  // 避免重新調用API，直接使用已載入的數據
  // 前端篩選邏輯已經在ContentManagement組件中處理
  console.log('Factors fetched successfully, count:', questionFactors.value?.length || 0)
}

const handleRefreshContent = async () => {
  await loadQuestionStructureData()
}

onMounted(async () => {
  try {
    console.log('=== Admin Content Page Mounted ===')
    console.log('Question content management page mounted for:', { companyId, questionId })
    console.log('Route params analysis:')
    console.log('- All params:', route.params)
    console.log('- companyId (raw):', route.params.companyId, 'type:', typeof route.params.companyId)
    console.log('- questionId (raw):', route.params.questionId, 'type:', typeof route.params.questionId)
    console.log('- questionId (parsed):', questionId, 'type:', typeof questionId, 'isNaN:', isNaN(questionId))

    // Load topic layer setting from management page
    loadTopicLayerSetting()

    // Only proceed if questionId is valid
    if (isNaN(questionId) || questionId <= 0) {
      console.error('=== CRITICAL: Invalid questionId, skipping data loading ===')
      console.error('This will cause API calls to fail with NaN')
      return
    }

    // Load content data from database API (with localStorage fallback)
    await loadQuestionData()

    // Load structure data from database
    await loadQuestionStructureData()

    // Ensure page title is updated with final data
    updatePageTitle()
    console.log('🏁 Final page title update completed')

    console.log('=== Admin Content Page initialization completed ===')
  } catch (error) {
    console.error('Error in content page initialization:', error)
  }
})
</script>