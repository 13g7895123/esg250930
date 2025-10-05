/**
 * 題項架構管理 Composable
 *
 * 提供題項管理中的架構管理功能，包括：
 * - 風險分類、主題、因子的 CRUD 操作
 * - 從範本同步架構資料
 * - 架構資料的狀態管理
 *
 * 與範本管理的架構完全獨立
 *
 * @author Claude Code Assistant
 * @version 1.0
 * @since 2025-09-24
 */

export const useQuestionStructure = () => {
  // Initialize API inside the composable function (SSR safe)
  const api = useApi()

  // Use useState for SSR-safe reactive state
  const structureLoading = useState('questionStructure_loading', () => false)
  const categories = useState('questionStructure_categories', () => [])
  const topics = useState('questionStructure_topics', () => [])
  const factors = useState('questionStructure_factors', () => [])
  const contents = useState('questionStructure_contents', () => [])

  // Error handling
  const lastError = useState('questionStructure_lastError', () => null)

  /**
   * 取得評估記錄的完整架構資訊
   * @param {number} assessmentId - 評估記錄 ID
   * @returns {Promise<Object>} 架構資訊和統計資料
   */
  const getAssessmentStructure = async (assessmentId) => {
    if (!assessmentId) {
      throw new Error('評估記錄ID為必填項目')
    }

    structureLoading.value = true
    lastError.value = null

    try {
      const result = await api.questionManagement.getStructure(assessmentId)

      // Handle the API response structure
      if (!result.success) {
        throw new Error(result.error?.message || '取得架構資訊失敗')
      }

      // Update local state
      const structureData = result.data
      categories.value = structureData.structure?.categories || []
      topics.value = structureData.structure?.topics || []
      factors.value = structureData.structure?.factors || []

      return structureData
    } catch (error) {
      console.error('Error fetching assessment structure:', error)
      lastError.value = error.message || '取得架構資訊時發生錯誤'
      throw error
    } finally {
      structureLoading.value = false
    }
  }

  /**
   * 從範本同步架構到題項管理
   * @param {number} assessmentId - 評估記錄 ID
   * @returns {Promise<Object>} 同步結果
   */
  const syncFromTemplate = async (assessmentId) => {
    if (!assessmentId) {
      throw new Error('評估記錄ID為必填項目')
    }

    structureLoading.value = true
    lastError.value = null

    try {
      const result = await api.questionManagement.syncFromTemplate(assessmentId)

      // Handle the API response structure
      if (!result.success) {
        throw new Error(result.error?.message || '同步範本失敗')
      }

      // Reload structure data
      await getAssessmentStructure(assessmentId)
      return result.data
    } catch (error) {
      console.error('Error syncing from template:', error)
      lastError.value = error.message || '同步範本時發生錯誤'
      throw error
    } finally {
      structureLoading.value = false
    }
  }

  // =============================================
  // 風險分類管理
  // =============================================

  /**
   * 取得評估記錄的所有風險分類
   * @param {number} assessmentId - 評估記錄 ID
   * @param {string} search - 搜尋關鍵字
   * @returns {Promise<Array>} 風險分類列表
   */
  const getCategories = async (assessmentId, search = null) => {
    if (!assessmentId) {
      throw new Error('評估記錄ID為必填項目')
    }

    try {
      // Use the dedicated categories endpoint
      const result = await api.questionManagement.getCategories(assessmentId)

      // Handle the API response structure
      if (!result.success) {
        throw new Error(result.error?.message || '取得風險分類失敗')
      }

      // Extract categories from the response
      const categoriesData = result.data || []

      // Apply search filter if provided
      const filteredCategories = search
        ? categoriesData.filter(cat =>
            cat.category_name?.toLowerCase().includes(search.toLowerCase()) ||
            cat.description?.toLowerCase().includes(search.toLowerCase())
          )
        : categoriesData

      categories.value = filteredCategories
      return filteredCategories
    } catch (error) {
      console.error('Error fetching categories:', error)
      lastError.value = error.message || '取得風險分類時發生錯誤'
      throw error
    }
  }

  /**
   * 新增風險分類
   * @param {number} assessmentId - 評估記錄 ID
   * @param {Object} categoryData - 分類資料
   * @returns {Promise<Object>} 新建立的分類資料
   */
  const createCategory = async (assessmentId, categoryData) => {
    if (!assessmentId) {
      throw new Error('評估記錄ID為必填項目')
    }

    try {
      const result = await api.questionManagement.createCategory(assessmentId, categoryData)

      // Handle the API response structure
      // useApi returns: { success: true/false, data: <response>, error: null/object }
      if (!result.success) {
        throw new Error(result.error?.message || '新增風險分類失敗')
      }

      // The actual data is in result.data
      // Reload categories list
      await getCategories(assessmentId)

      // Return the created category data
      return result.data
    } catch (error) {
      console.error('Error creating category:', error)
      lastError.value = error.message || '新增風險分類時發生錯誤'
      throw error
    }
  }

  /**
   * 更新風險分類
   * @param {number} categoryId - 分類 ID
   * @param {Object} categoryData - 分類資料
   * @returns {Promise<Object>} 更新後的分類資料
   */
  const updateCategory = async (categoryId, categoryData) => {
    if (!categoryId) {
      throw new Error('風險分類ID為必填項目')
    }

    try {
      const result = await api.questionManagement.updateCategory(categoryId, categoryData)

      // Handle the API response structure
      if (!result.success) {
        throw new Error(result.error?.message || '更新風險分類失敗')
      }

      return result.data
    } catch (error) {
      console.error('Error updating category:', error)
      lastError.value = error.message || '更新風險分類時發生錯誤'
      throw error
    }
  }

  /**
   * 刪除風險分類
   * @param {number} categoryId - 分類 ID
   * @returns {Promise<boolean>} 是否成功
   */
  const deleteCategory = async (categoryId) => {
    if (!categoryId) {
      throw new Error('風險分類ID為必填項目')
    }

    try {
      const result = await api.questionManagement.deleteCategory(categoryId)

      // Handle the API response structure
      if (!result.success) {
        // API returned error, throw with specific error message
        const errorMessage = result.error?.message || '刪除風險分類失敗'
        throw new Error(errorMessage)
      }

      return true
    } catch (error) {
      console.error('Error deleting category:', error)
      lastError.value = error.message || '刪除風險分類時發生錯誤'
      throw error
    }
  }

  // =============================================
  // 風險主題管理
  // =============================================

  /**
   * 取得評估記錄的所有風險主題
   * @param {number} assessmentId - 評估記錄 ID
   * @param {number} categoryId - 分類 ID（可選）
   * @param {string} search - 搜尋關鍵字
   * @returns {Promise<Array>} 風險主題列表
   */
  const getTopics = async (assessmentId, categoryId = null, search = null) => {
    if (!assessmentId) {
      throw new Error('評估記錄ID為必填項目')
    }

    try {
      // Use the dedicated topics endpoint with query parameters
      const params = {}
      if (categoryId) params.category_id = categoryId
      if (search) params.search = search

      const result = await api.questionManagement.getTopics(assessmentId, params)

      // Handle the API response structure
      if (!result.success) {
        throw new Error(result.error?.message || '取得風險主題失敗')
      }

      // Extract topics from the response
      const topicsData = result.data || []

      topics.value = topicsData
      return topicsData
    } catch (error) {
      console.error('Error fetching topics:', error)
      lastError.value = error.message || '取得風險主題時發生錯誤'
      throw error
    }
  }

  /**
   * 新增風險主題
   * @param {number} assessmentId - 評估記錄 ID
   * @param {Object} topicData - 主題資料
   * @returns {Promise<Object>} 新建立的主題資料
   */
  const createTopic = async (assessmentId, topicData) => {
    if (!assessmentId) {
      throw new Error('評估記錄ID為必填項目')
    }

    try {
      const result = await api.questionManagement.createTopic(assessmentId, topicData)

      // Handle the API response structure
      // useApi returns: { success: true/false, data: <response>, error: null/object }
      if (!result.success) {
        throw new Error(result.error?.message || '新增風險主題失敗')
      }

      // The actual data is in result.data
      // Reload topics list
      await getTopics(assessmentId)

      // Return the created topic data
      return result.data
    } catch (error) {
      console.error('Error creating topic:', error)
      lastError.value = error.message || '新增風險主題時發生錯誤'
      throw error
    }
  }

  /**
   * 更新風險主題
   * @param {number} topicId - 主題 ID
   * @param {Object} topicData - 主題資料
   * @returns {Promise<Object>} 更新後的主題資料
   */
  const updateTopic = async (topicId, topicData) => {
    if (!topicId) {
      throw new Error('風險主題ID為必填項目')
    }

    try {
      const result = await api.questionManagement.updateTopic(topicId, topicData)

      // Handle the API response structure
      if (!result.success) {
        throw new Error(result.error?.message || '更新風險主題失敗')
      }

      return result.data
    } catch (error) {
      console.error('Error updating topic:', error)
      lastError.value = error.message || '更新風險主題時發生錯誤'
      throw error
    }
  }

  /**
   * 刪除風險主題
   * @param {number} topicId - 主題 ID
   * @returns {Promise<boolean>} 是否成功
   */
  const deleteTopic = async (topicId) => {
    if (!topicId) {
      throw new Error('風險主題ID為必填項目')
    }

    try {
      const result = await api.questionManagement.deleteTopic(topicId)

      // Handle the API response structure
      if (!result.success) {
        // API returned error, throw with specific error message
        const errorMessage = result.error?.message || '刪除風險主題失敗'
        throw new Error(errorMessage)
      }

      return true
    } catch (error) {
      console.error('Error deleting topic:', error)
      lastError.value = error.message || '刪除風險主題時發生錯誤'
      throw error
    }
  }

  // =============================================
  // 風險因子管理
  // =============================================

  /**
   * 取得評估記錄的所有風險因子
   * @param {number} assessmentId - 評估記錄 ID
   * @param {number} topicId - 主題 ID（可選）
   * @param {number} categoryId - 分類 ID（可選）
   * @param {string} search - 搜尋關鍵字
   * @returns {Promise<Array>} 風險因子列表
   */
  const getFactors = async (assessmentId, topicId = null, categoryId = null, search = null) => {
    if (!assessmentId) {
      throw new Error('評估記錄ID為必填項目')
    }

    try {
      // Use the dedicated factors endpoint with query parameters
      const params = {}
      if (topicId) params.topic_id = topicId
      if (categoryId) params.category_id = categoryId
      if (search) params.search = search

      const result = await api.questionManagement.getFactors(assessmentId, params)

      // Handle the API response structure
      if (!result.success) {
        throw new Error(result.error?.message || '取得風險因子失敗')
      }

      // Extract factors from the response
      const factorsData = result.data || []

      factors.value = factorsData
      return factorsData
    } catch (error) {
      console.error('Error fetching factors:', error)
      lastError.value = error.message || '取得風險因子時發生錯誤'
      throw error
    }
  }

  /**
   * 新增風險因子
   * @param {number} assessmentId - 評估記錄 ID
   * @param {Object} factorData - 因子資料
   * @returns {Promise<Object>} 新建立的因子資料
   */
  const createFactor = async (assessmentId, factorData) => {
    if (!assessmentId) {
      throw new Error('評估記錄ID為必填項目')
    }

    try {
      const result = await api.questionManagement.createFactor(assessmentId, factorData)

      // Handle the API response structure
      // useApi returns: { success: true/false, data: <response>, error: null/object }
      if (!result.success) {
        throw new Error(result.error?.message || '新增風險因子失敗')
      }

      // The actual data is in result.data
      // Reload factors list
      await getFactors(assessmentId)

      // Return the created factor data
      return result.data
    } catch (error) {
      console.error('Error creating factor:', error)
      lastError.value = error.message || '新增風險因子時發生錯誤'
      throw error
    }
  }

  /**
   * 更新風險因子
   * @param {number} factorId - 因子 ID
   * @param {Object} factorData - 因子資料
   * @returns {Promise<Object>} 更新後的因子資料
   */
  const updateFactor = async (factorId, factorData) => {
    if (!factorId) {
      throw new Error('風險因子ID為必填項目')
    }

    try {
      const result = await api.questionManagement.updateFactor(factorId, factorData)

      // Handle the API response structure
      if (!result.success) {
        throw new Error(result.error?.message || '更新風險因子失敗')
      }

      return result.data
    } catch (error) {
      console.error('Error updating factor:', error)
      lastError.value = error.message || '更新風險因子時發生錯誤'
      throw error
    }
  }

  /**
   * 刪除風險因子
   * @param {number} factorId - 因子 ID
   * @returns {Promise<boolean>} 是否成功
   */
  const deleteFactor = async (factorId) => {
    if (!factorId) {
      throw new Error('風險因子ID為必填項目')
    }

    try {
      const result = await api.questionManagement.deleteFactor(factorId)

      // Handle the API response structure
      if (!result.success) {
        // API returned error, throw with specific error message
        const errorMessage = result.error?.message || '刪除風險因子失敗'
        throw new Error(errorMessage)
      }

      return true
    } catch (error) {
      console.error('Error deleting factor:', error)
      lastError.value = error.message || '刪除風險因子時發生錯誤'
      throw error
    }
  }

  /**
   * 取得評估記錄的統計資料
   * @param {number} assessmentId - 評估記錄 ID
   * @returns {Promise<Object>} 統計資料
   */
  const getAssessmentStats = async (assessmentId) => {
    if (!assessmentId) {
      throw new Error('評估記錄ID為必填項目')
    }

    try {
      const result = await api.questionManagement.getStats(assessmentId)

      // Handle the API response structure
      if (!result.success) {
        throw new Error(result.error?.message || '取得統計資料失敗')
      }

      return result.data
    } catch (error) {
      console.error('Error fetching stats:', error)
      lastError.value = error.message || '取得統計資料時發生錯誤'
      throw error
    }
  }

  /**
   * 清除評估記錄的所有題項資料
   * @param {number} assessmentId - 評估記錄 ID
   * @returns {Promise<boolean>} 是否成功
   */
  const clearAssessmentData = async (assessmentId) => {
    if (!assessmentId) {
      throw new Error('評估記錄ID為必填項目')
    }

    try {
      const result = await api.questionManagement.clearStructure(assessmentId)

      // Handle the API response structure
      if (!result.success) {
        throw new Error(result.error?.message || '清除評估資料失敗')
      }

      // Clear local state
      categories.value = []
      topics.value = []
      factors.value = []
      contents.value = []
      return true
    } catch (error) {
      console.error('Error clearing assessment data:', error)
      lastError.value = error.message || '清除評估資料時發生錯誤'
      throw error
    }
  }

  return {
    // 狀態
    structureLoading: readonly(structureLoading),
    categories: readonly(categories),
    topics: readonly(topics),
    factors: readonly(factors),
    contents: readonly(contents),
    lastError: readonly(lastError),

    // 主要功能
    getAssessmentStructure,
    syncFromTemplate,
    getAssessmentStats,
    clearAssessmentData,

    // 分類管理
    getCategories,
    createCategory,
    updateCategory,
    deleteCategory,

    // 主題管理
    getTopics,
    createTopic,
    updateTopic,
    deleteTopic,

    // 因子管理
    getFactors,
    createFactor,
    updateFactor,
    deleteFactor
  }
}