import { defineStore } from 'pinia'

export const useQuestionManagementStore = defineStore('questionManagement', () => {
  // State - 使用與 templates store 相似的結構
  const assessments = ref({}) // key: assessmentId, value: assessment info
  const questionContent = ref({}) // key: assessmentId, value: content array
  const questionCategories = ref({}) // key: assessmentId, value: categories array
  const questionTopics = ref({}) // key: assessmentId, value: topics array
  const questionFactors = ref({}) // key: assessmentId, value: factors array
  const loading = ref(false)
  const error = ref(null)

  // Loading states
  const isFetchingAssessment = ref(false)
  const isFetchingContent = ref(false)
  const isAddingContent = ref(false)
  const isUpdatingContent = ref(false)
  const isDeletingContent = ref(false)

  // Getters
  const getAssessmentById = (assessmentId) => {
    return computed(() => assessments.value[assessmentId] || null)
  }

  const getQuestionContent = (assessmentId) => {
    return computed(() => questionContent.value[assessmentId] || [])
  }

  const getQuestionCategories = (assessmentId) => {
    return computed(() => questionCategories.value[assessmentId] || [])
  }

  const getQuestionTopics = (assessmentId) => {
    return computed(() => questionTopics.value[assessmentId] || [])
  }

  const getQuestionFactors = (assessmentId) => {
    return computed(() => questionFactors.value[assessmentId] || [])
  }

  // Error handling
  const handleError = (err, defaultMessage = 'An error occurred') => {
    console.error('QuestionManagement Store error:', err)

    if (err.error && typeof err.error === 'object') {
      error.value = err.error.message || defaultMessage
    } else if (err.response?.data?.message) {
      error.value = err.response.data.message
    } else {
      error.value = err.message || defaultMessage
    }
  }

  const clearError = () => {
    error.value = null
  }

  // API Actions

  /**
   * 載入評估基本資訊
   */
  const fetchAssessment = async (assessmentId) => {
    isFetchingAssessment.value = true
    clearError()

    try {
      const response = await $fetch(`/api/v1/risk-assessment/company-assessments/${assessmentId}`)

      if (response.success && response.data) {
        // 儲存評估資訊
        assessments.value[assessmentId] = {
          id: assessmentId,
          templateId: response.data.template_id,
          year: response.data.assessment_year,
          createdAt: response.data.created_at,
          templateVersion: response.data.template_version_name || response.data.version_name,
          companyId: response.data.company_id
        }
        return response.data
      } else {
        throw new Error('Invalid response format')
      }
    } catch (err) {
      handleError(err, 'Failed to fetch assessment')
      throw err
    } finally {
      isFetchingAssessment.value = false
    }
  }

  /**
   * 載入評估內容
   */
  const fetchQuestionContent = async (assessmentId) => {
    isFetchingContent.value = true
    loading.value = true
    clearError()

    try {
      const response = await $fetch(`/api/v1/question-management/assessment/${assessmentId}/contents`)

      if (response.success && Array.isArray(response.data)) {
        // 轉換 API 回應為統一格式
        questionContent.value[assessmentId] = response.data.map(item => ({
          id: item.id,
          category_id: item.category_id,
          topic_id: item.topic_id,
          risk_factor_id: item.factor_id,
          topic: item.title || '未命名題目',
          a_content: item.a_content || '',  // 修正：使用 a_content 欄位
          order: item.sort_order || 0,
          sort_order: item.sort_order || 0,
          // 複製其他可能需要的欄位
          b_content: item.b_content || '',
          c_placeholder: item.c_placeholder || '',
          d_placeholder_1: item.d_placeholder_1 || '',
          d_placeholder_2: item.d_placeholder_2 || '',
          e1_placeholder_1: item.e1_placeholder_1 || '',
          e1_info: item.e1_info || '',
          f1_info: item.f1_info || '',
          g1_info: item.g1_info || '',
          h1_info: item.h1_info || '',
          // 時間戳記欄位
          created_at: item.created_at || null,
          updated_at: item.updated_at || null
        }))
        return response.data
      } else {
        questionContent.value[assessmentId] = []
        return []
      }
    } catch (err) {
      handleError(err, 'Failed to fetch question content')
      questionContent.value[assessmentId] = []
      throw err
    } finally {
      isFetchingContent.value = false
      loading.value = false
    }
  }

  /**
   * 新增問題內容
   */
  const addQuestionContent = async (assessmentId, contentData) => {
    isAddingContent.value = true
    clearError()

    const apiData = {
      category_id: contentData.categoryId,
      topic_id: contentData.topicId || null,
      factor_id: contentData.riskFactorId || null,
      title: contentData.topic || '',
      description: contentData.a_content || contentData.aContent || '',
      sort_order: (questionContent.value[assessmentId]?.length || 0) + 1
    }

    try {
      const response = await $fetch(`/api/v1/question-management/assessment/${assessmentId}/contents`, {
        method: 'POST',
        body: apiData
      })

      if (response.success && response.data) {
        // 轉換並加入本地狀態
        const newContent = {
          id: response.data.id,
          category_id: response.data.category_id,
          topic_id: response.data.topic_id,
          risk_factor_id: response.data.factor_id,
          topic: response.data.title || '',
          a_content: response.data.description || '',
          description: response.data.description || '',
          order: response.data.sort_order || 0,
          sort_order: response.data.sort_order || 0
        }

        if (!questionContent.value[assessmentId]) {
          questionContent.value[assessmentId] = []
        }
        questionContent.value[assessmentId].unshift(newContent)

        return response.data
      } else {
        throw new Error(response.message || '建立內容失敗')
      }
    } catch (err) {
      handleError(err, 'Failed to add question content')
      throw err
    } finally {
      isAddingContent.value = false
    }
  }

  /**
   * 更新問題內容
   */
  const updateQuestionContent = async (assessmentId, contentId, contentData) => {
    isUpdatingContent.value = true
    clearError()

    const apiData = {
      category_id: contentData.categoryId,
      topic_id: contentData.topicId || null,
      factor_id: contentData.riskFactorId || null,
      title: contentData.topic || '',
      description: contentData.a_content || contentData.aContent || ''
    }

    try {
      const response = await $fetch(`/api/v1/question-management/contents/${contentId}`, {
        method: 'PUT',
        body: apiData
      })

      if (response.success && response.data) {
        // 更新本地狀態
        const contents = questionContent.value[assessmentId] || []
        const index = contents.findIndex(c => c.id === contentId)

        if (index !== -1) {
          contents[index] = {
            id: response.data.id,
            category_id: response.data.category_id,
            topic_id: response.data.topic_id,
            risk_factor_id: response.data.factor_id,
            topic: response.data.title || '',
            a_content: response.data.description || '',
            description: response.data.description || '',
            order: response.data.sort_order || contents[index].order,
            sort_order: response.data.sort_order || contents[index].sort_order
          }
        }

        return response.data
      } else {
        throw new Error(response.message || '更新內容失敗')
      }
    } catch (err) {
      handleError(err, 'Failed to update question content')
      throw err
    } finally {
      isUpdatingContent.value = false
    }
  }

  /**
   * 刪除問題內容
   */
  const deleteQuestionContent = async (assessmentId, contentId) => {
    isDeletingContent.value = true
    clearError()

    try {
      const response = await $fetch(`/api/v1/question-management/contents/${contentId}`, {
        method: 'DELETE'
      })

      if (response.success) {
        // 從本地狀態移除
        const contents = questionContent.value[assessmentId] || []
        const index = contents.findIndex(c => c.id === contentId)

        if (index !== -1) {
          contents.splice(index, 1)
        }

        return response
      } else {
        throw new Error(response.message || '刪除內容失敗')
      }
    } catch (err) {
      handleError(err, 'Failed to delete question content')
      throw err
    } finally {
      isDeletingContent.value = false
    }
  }

  /**
   * 載入問題架構（分類、主題、因子）
   */
  const fetchQuestionStructure = async (assessmentId) => {
    loading.value = true
    clearError()

    try {
      // 使用現有的 useQuestionStructure composable 的 API
      const [categoriesRes, topicsRes, factorsRes] = await Promise.all([
        $fetch(`/api/v1/question-management/assessment/${assessmentId}/categories`),
        $fetch(`/api/v1/question-management/assessment/${assessmentId}/topics`),
        $fetch(`/api/v1/question-management/assessment/${assessmentId}/factors`)
      ])

      if (categoriesRes.success && Array.isArray(categoriesRes.data)) {
        questionCategories.value[assessmentId] = categoriesRes.data
      } else {
        questionCategories.value[assessmentId] = []
      }

      if (topicsRes.success && Array.isArray(topicsRes.data)) {
        questionTopics.value[assessmentId] = topicsRes.data
      } else {
        questionTopics.value[assessmentId] = []
      }

      if (factorsRes.success && Array.isArray(factorsRes.data)) {
        questionFactors.value[assessmentId] = factorsRes.data
      } else {
        questionFactors.value[assessmentId] = []
      }

      return {
        categories: questionCategories.value[assessmentId],
        topics: questionTopics.value[assessmentId],
        factors: questionFactors.value[assessmentId]
      }
    } catch (err) {
      handleError(err, 'Failed to fetch question structure')
      questionCategories.value[assessmentId] = []
      questionTopics.value[assessmentId] = []
      questionFactors.value[assessmentId] = []
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * 初始化評估資料（一次載入所有需要的資料）
   */
  const initialize = async (assessmentId) => {
    loading.value = true
    clearError()

    try {
      // 並行載入所有資料
      await Promise.all([
        fetchAssessment(assessmentId),
        fetchQuestionContent(assessmentId),
        fetchQuestionStructure(assessmentId)
      ])

      return {
        assessment: assessments.value[assessmentId],
        content: questionContent.value[assessmentId],
        categories: questionCategories.value[assessmentId],
        topics: questionTopics.value[assessmentId],
        factors: questionFactors.value[assessmentId]
      }
    } catch (err) {
      handleError(err, 'Failed to initialize assessment data')
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * 重新整理評估資料
   */
  const refreshAssessment = async (assessmentId) => {
    return initialize(assessmentId)
  }

  return {
    // State
    assessments,
    questionContent,
    questionCategories,
    questionTopics,
    questionFactors,
    loading,
    error,
    isFetchingAssessment,
    isFetchingContent,
    isAddingContent,
    isUpdatingContent,
    isDeletingContent,

    // Getters
    getAssessmentById,
    getQuestionContent,
    getQuestionCategories,
    getQuestionTopics,
    getQuestionFactors,

    // Actions
    fetchAssessment,
    fetchQuestionContent,
    addQuestionContent,
    updateQuestionContent,
    deleteQuestionContent,
    fetchQuestionStructure,
    initialize,
    refreshAssessment,
    clearError
  }
})
