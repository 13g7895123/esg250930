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
  // Use unified API service
  const api = useApi()

  // 狀態管理
  const structureLoading = ref(false)
  const categories = ref([])
  const topics = ref([])
  const factors = ref([])
  const contents = ref([])

  // 錯誤處理
  const lastError = ref(null)

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
      console.log('=== 🚀 API CALL ===')
      console.log('📁 Frontend File: /frontend/composables/useQuestionStructure.js')
      console.log('⚙️  Function: getAssessmentStructure')
      console.log('🔧 Backend File: /backend/app/Controllers/Api/V1/QuestionManagement/QuestionManagementController.php')
      console.log('📝 Backend Method: getAssessmentStructure')
      console.log('===================')

      const result = await api.questionManagement.getStructure(assessmentId)
      const response = result.data

      if (response.success) {
        // 更新本地狀態
        categories.value = response.data.structure.categories || []
        topics.value = response.data.structure.topics || []
        factors.value = response.data.structure.factors || []

        return response.data
      } else {
        throw new Error(response.message || '取得架構資訊失敗')
      }
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
      console.log('=== 🚀 API CALL ===')
      console.log('📁 Frontend File: /frontend/composables/useQuestionStructure.js')
      console.log('⚙️  Function: syncFromTemplate')
      console.log('🔧 Backend File: /backend/app/Controllers/Api/V1/QuestionManagement/QuestionManagementController.php')
      console.log('📝 Backend Method: syncFromTemplate')
      console.log('===================')

      const result = await api.questionManagement.syncFromTemplate(assessmentId)
      const response = result.data

      if (response.success) {
        // 重新載入架構資料
        await getAssessmentStructure(assessmentId)
        return response.data
      } else {
        throw new Error(response.message || '同步範本失敗')
      }
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
      // Use the existing structure endpoint instead of non-existent categories endpoint
      const result = await api.questionManagement.getStructure(assessmentId)
      const response = result.data

      if (response.success) {
        // Extract categories from the structure response
        const categoriesData = response.data.structure.categories || []

        // Apply search filter if provided
        const filteredCategories = search
          ? categoriesData.filter(cat =>
              cat.category_name?.toLowerCase().includes(search.toLowerCase()) ||
              cat.description?.toLowerCase().includes(search.toLowerCase())
            )
          : categoriesData

        categories.value = filteredCategories
        return filteredCategories
      } else {
        throw new Error(response.message || '取得風險分類失敗')
      }
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
      const response = result.data

      if (response.success) {
        // 重新載入分類列表
        await getCategories(assessmentId)
        return response.data
      } else {
        throw new Error(response.message || '新增風險分類失敗')
      }
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
      const response = result.data

      if (response.success) {
        return response.data
      } else {
        throw new Error(response.message || '更新風險分類失敗')
      }
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
      const response = result.data

      if (response.success) {
        return true
      } else {
        // API返回success: false時，拋出包含具體錯誤訊息的錯誤
        const errorMessage = response.message || '刪除風險分類失敗'
        throw new Error(errorMessage)
      }
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
      // Use the existing structure endpoint instead of non-existent topics endpoint
      const result = await api.questionManagement.getStructure(assessmentId)
      const response = result.data

      if (response.success) {
        // Extract topics from the structure response
        let topicsData = response.data.structure.topics || []

        // Apply category filter if provided
        if (categoryId) {
          topicsData = topicsData.filter(topic => topic.category_id === categoryId)
        }

        // Apply search filter if provided
        if (search) {
          const searchLower = search.toLowerCase()
          topicsData = topicsData.filter(topic =>
            topic.topic_name?.toLowerCase().includes(searchLower) ||
            topic.description?.toLowerCase().includes(searchLower) ||
            topic.category_name?.toLowerCase().includes(searchLower)
          )
        }

        topics.value = topicsData
        return topicsData
      } else {
        throw new Error(response.message || '取得風險主題失敗')
      }
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
      const response = result.data

      if (response.success) {
        // 重新載入主題列表
        await getTopics(assessmentId)
        return response.data
      } else {
        throw new Error(response.message || '新增風險主題失敗')
      }
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
      const response = result.data

      if (response.success) {
        return response.data
      } else {
        throw new Error(response.message || '更新風險主題失敗')
      }
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
      const response = result.data

      if (response.success) {
        return true
      } else {
        // API返回success: false時，拋出包含具體錯誤訊息的錯誤
        const errorMessage = response.message || '刪除風險主題失敗'
        throw new Error(errorMessage)
      }
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
      // Use the existing structure endpoint instead of non-existent factors endpoint
      const result = await api.questionManagement.getStructure(assessmentId)
      const response = result.data

      if (response.success) {
        // Extract factors from the structure response
        let factorsData = response.data.structure.factors || []

        // Apply topic filter if provided
        if (topicId) {
          factorsData = factorsData.filter(factor => factor.topic_id === topicId)
        }

        // Apply category filter if provided
        if (categoryId) {
          factorsData = factorsData.filter(factor => factor.category_id === categoryId)
        }

        // Apply search filter if provided
        if (search) {
          const searchLower = search.toLowerCase()
          factorsData = factorsData.filter(factor =>
            factor.factor_name?.toLowerCase().includes(searchLower) ||
            factor.description?.toLowerCase().includes(searchLower) ||
            factor.topic_name?.toLowerCase().includes(searchLower) ||
            factor.category_name?.toLowerCase().includes(searchLower)
          )
        }

        factors.value = factorsData
        return factorsData
      } else {
        throw new Error(response.message || '取得風險因子失敗')
      }
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
      const response = result.data

      if (response.success) {
        // 重新載入因子列表
        await getFactors(assessmentId)
        return response.data
      } else {
        throw new Error(response.message || '新增風險因子失敗')
      }
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
      const response = result.data

      if (response.success) {
        return response.data
      } else {
        throw new Error(response.message || '更新風險因子失敗')
      }
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
      const response = result.data

      if (response.success) {
        return true
      } else {
        // API返回success: false時，拋出包含具體錯誤訊息的錯誤
        const errorMessage = response.message || '刪除風險因子失敗'
        throw new Error(errorMessage)
      }
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
      const response = result.data

      if (response.success) {
        return response.data
      } else {
        throw new Error(response.message || '取得統計資料失敗')
      }
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
      const response = result.data

      if (response.success) {
        // 清空本地狀態
        categories.value = []
        topics.value = []
        factors.value = []
        contents.value = []
        return true
      } else {
        throw new Error(response.message || '清除評估資料失敗')
      }
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