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

    console.log('[QuestionManagement Store] addQuestionContent - contentData:', contentData)

    const apiData = {
      category_id: contentData.categoryId,
      topic_id: contentData.topicId || null,
      factor_id: contentData.riskFactorId || null,
      factorDescription: contentData.factorDescription || contentData.a_content || contentData.aContent || '',
      sort_order: (questionContent.value[assessmentId]?.length || 0) + 1
    }

    console.log('[QuestionManagement Store] addQuestionContent - apiData:', apiData)
    console.log('[QuestionManagement Store] factorDescription:', apiData.factorDescription)

    try {
      const response = await $fetch(`/api/v1/question-management/assessment/${assessmentId}/contents`, {
        method: 'POST',
        body: apiData
      })

      console.log('[QuestionManagement Store] addQuestionContent - response:', response)

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

    console.log('[QuestionManagement Store] updateQuestionContent - contentData:', contentData)

    const apiData = {
      category_id: contentData.categoryId,
      topic_id: contentData.topicId || null,
      factor_id: contentData.riskFactorId || null,
      factorDescription: contentData.factorDescription || contentData.a_content || contentData.aContent || ''
    }

    console.log('[QuestionManagement Store] updateQuestionContent - apiData:', apiData)
    console.log('[QuestionManagement Store] factorDescription:', apiData.factorDescription)
    console.log('[QuestionManagement Store] apiData keys:', Object.keys(apiData))
    console.log('[QuestionManagement Store] apiData JSON:', JSON.stringify(apiData, null, 2))

    try {
      console.log('[QuestionManagement Store] About to send PUT request to:', `/api/v1/question-management/contents/${contentId}`)
      console.log('[QuestionManagement Store] Request body:', JSON.stringify(apiData, null, 2))

      const response = await $fetch(`/api/v1/question-management/contents/${contentId}`, {
        method: 'PUT',
        body: apiData
      })

      console.log('[QuestionManagement Store] updateQuestionContent - response:', response)
      console.log('[QuestionManagement Store] response.debug:', response.debug)

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
   * 重新排序問題內容
   */
  const reorderQuestionContent = async (assessmentId, newOrder) => {
    loading.value = true
    clearError()

    try {
      // 將新的順序轉換為 API 需要的格式
      const orders = newOrder.map((item, index) => ({
        id: item.id,
        sort_order: index + 1
      }))

      console.log('=== Sending reorder request ===')
      console.log('Assessment ID:', assessmentId)
      console.log('Orders to send:', orders)

      const response = await $fetch(`/api/v1/question-management/assessment/${assessmentId}/contents/reorder`, {
        method: 'PUT',
        body: { orders }
      })

      console.log('=== Reorder API response ===')
      console.log('Success:', response.success)
      console.log('Updated count:', response.data?.updated_count)
      console.log('Server confirmed orders:', response.data?.orders)

      if (response.success) {
        // 暫時更新本地狀態（提供即時反應）
        // 注意：呼叫者應該在成功後呼叫 refreshAssessment() 以確保資料完全同步
        questionContent.value[assessmentId] = newOrder

        console.log('[Store] Local state updated temporarily, caller should refresh for full sync')

        // 回傳包含伺服器確認的順序資訊
        return {
          ...response,
          clientOrders: orders,
          serverOrders: response.data?.orders || []
        }
      } else {
        throw new Error(response.message || '更新排序失敗')
      }
    } catch (err) {
      handleError(err, 'Failed to reorder question content')
      throw err
    } finally {
      loading.value = false
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

  /**
   * 清除特定 assessment 的所有快取資料
   */
  const clearAssessmentCache = (assessmentId) => {
    console.log('[QuestionManagement Store] Clearing cache for assessment:', assessmentId)

    // 清除該 assessment 的所有快取資料
    delete assessments.value[assessmentId]
    delete questionContent.value[assessmentId]
    delete questionCategories.value[assessmentId]
    delete questionTopics.value[assessmentId]
    delete questionFactors.value[assessmentId]

    console.log('[QuestionManagement Store] Cache cleared successfully')
  }

  /**
   * 重新載入特定 assessment 的所有資料（清除快取後使用）
   */
  const fetchAllQuestionData = async (assessmentId) => {
    console.log('[QuestionManagement Store] Fetching all question data for assessment:', assessmentId)

    // 使用 initialize 方法重新載入所有資料
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
    reorderQuestionContent,
    fetchQuestionStructure,
    initialize,
    refreshAssessment,
    clearAssessmentCache,
    fetchAllQuestionData,
    clearError
  }
})
