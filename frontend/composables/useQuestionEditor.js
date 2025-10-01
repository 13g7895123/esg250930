/**
 * 題目編輯器統一資料管理 Composable
 *
 * 用途: 統一管理 Template 編輯、Question 編輯、Preview 三種模式的資料載入與儲存
 *
 * @returns {Object} 編輯器資料管理方法與狀態
 */
export const useQuestionEditor = () => {
  const mode = ref('template') // 'template' | 'question' | 'preview'
  const dataSource = ref(null) // 'store' | 'api'

  // 統一資料介面
  const questionData = ref(null)
  const isLoading = ref(false)
  const loadError = ref(null)

  // 當前 ID (根據模式可能是 templateId 或 assessmentId)
  const currentId = ref(null)
  const currentContentId = ref(null)

  /**
   * 初始化資料來源
   * @param {string} editorMode - 編輯器模式 'template' | 'question' | 'preview'
   * @param {number} id - templateId 或 assessmentId
   * @param {number} contentId - 題目內容 ID
   */
  const initializeDataSource = async (editorMode, id, contentId) => {
    mode.value = editorMode
    currentId.value = id
    currentContentId.value = contentId

    console.log(`[useQuestionEditor] 初始化編輯器 - 模式: ${editorMode}, ID: ${id}, ContentID: ${contentId}`)

    if (editorMode === 'template' || editorMode === 'preview') {
      dataSource.value = 'store'
      return await loadFromStore(id, contentId)
    } else if (editorMode === 'question') {
      dataSource.value = 'api'
      return await loadFromApi(id, contentId)
    }
  }

  /**
   * 從 Pinia Store 載入資料 (Template 模式)
   */
  const loadFromStore = async (templateId, contentId) => {
    try {
      isLoading.value = true
      loadError.value = null

      const templatesStore = useTemplatesStore()

      // 確保 store 已初始化
      if (!templatesStore.templates || templatesStore.templates.length === 0) {
        await templatesStore.initialize()
      }

      // 載入模板內容
      const existingContent = templatesStore.getTemplateContent(templateId)
      if (!existingContent.value || existingContent.value.length === 0) {
        await templatesStore.fetchTemplateContent(templateId)
      }

      // 取得指定題目資料
      const contentList = templatesStore.getTemplateContent(templateId)
      questionData.value = contentList.value?.find(item => parseInt(item.id) === parseInt(contentId))

      if (!questionData.value) {
        throw new Error(`找不到題目資料 (Template ID: ${templateId}, Content ID: ${contentId})`)
      }

      console.log('[useQuestionEditor] 從 Store 載入資料成功:', questionData.value)

      return questionData.value
    } catch (error) {
      console.error('[useQuestionEditor] 從 Store 載入資料失敗:', error)
      loadError.value = error.message
      throw error
    } finally {
      isLoading.value = false
    }
  }

  /**
   * 從 API 載入資料 (Question 模式)
   */
  const loadFromApi = async (assessmentId, contentId) => {
    try {
      isLoading.value = true
      loadError.value = null

      // 載入題目內容
      const contentResponse = await $fetch(`/api/v1/question-management/contents/${contentId}`)

      if (!contentResponse.success || !contentResponse.data?.content) {
        throw new Error('無法載入題項資料')
      }

      questionData.value = contentResponse.data.content

      console.log('[useQuestionEditor] 從 API 載入資料成功:', questionData.value)

      return questionData.value
    } catch (error) {
      console.error('[useQuestionEditor] 從 API 載入資料失敗:', error)
      loadError.value = error.message || '載入題項資料時發生錯誤'
      throw error
    } finally {
      isLoading.value = false
    }
  }

  /**
   * 儲存題目資料
   * @param {Object} formData - 表單資料
   * @param {Object} originalData - 原始資料 (用於保留未修改欄位)
   */
  const saveQuestion = async (formData, originalData = null) => {
    if (mode.value === 'preview') {
      console.warn('[useQuestionEditor] 預覽模式不允許儲存')
      return
    }

    try {
      isLoading.value = true

      // 準備要儲存的資料 (保留原始資料的未修改欄位)
      const dataToSave = originalData ? { ...originalData, ...formData } : formData

      if (dataSource.value === 'store') {
        return await saveToStore(dataToSave)
      } else if (dataSource.value === 'api') {
        return await saveToApi(dataToSave)
      }
    } catch (error) {
      console.error('[useQuestionEditor] 儲存資料失敗:', error)
      throw error
    } finally {
      isLoading.value = false
    }
  }

  /**
   * 儲存至 Store (Template 模式)
   */
  const saveToStore = async (dataToSave) => {
    try {
      const templatesStore = useTemplatesStore()

      const response = await templatesStore.updateTemplateContent(
        currentId.value,
        currentContentId.value,
        dataToSave
      )

      console.log('[useQuestionEditor] 儲存至 Store 成功')

      return response
    } catch (error) {
      console.error('[useQuestionEditor] 儲存至 Store 失敗:', error)
      throw error
    }
  }

  /**
   * 儲存至 API (Question 模式)
   */
  const saveToApi = async (dataToSave) => {
    try {
      const response = await $fetch(
        `/api/v1/question-management/contents/${currentContentId.value}`,
        {
          method: 'PUT',
          body: dataToSave
        }
      )

      if (!response.success) {
        throw new Error(response.message || '儲存失敗')
      }

      console.log('[useQuestionEditor] 儲存至 API 成功')

      return response
    } catch (error) {
      console.error('[useQuestionEditor] 儲存至 API 失敗:', error)
      throw error
    }
  }

  /**
   * 取得結構資料 (分類、主題、因子)
   * @returns {Object} 包含 categories, topics, factors 的物件
   */
  const getStructureData = async () => {
    try {
      if (dataSource.value === 'store') {
        const templatesStore = useTemplatesStore()

        // 確保已載入分類資料
        const categories = templatesStore.getRiskCategories(currentId.value)
        if (!categories.value || categories.value.length === 0) {
          await templatesStore.fetchRiskCategories(currentId.value)
        }

        return {
          categories: templatesStore.getRiskCategories(currentId.value),
          topics: templatesStore.getRiskTopics(currentId.value),
          factors: templatesStore.getRiskFactors(currentId.value)
        }
      } else if (dataSource.value === 'api') {
        const structureResponse = await $fetch(
          `/api/v1/question-management/assessment/${currentId.value}/structure`
        )

        if (structureResponse.success && structureResponse.data?.structure) {
          return {
            categories: ref(structureResponse.data.structure.categories || []),
            topics: ref(structureResponse.data.structure.topics || []),
            factors: ref(structureResponse.data.structure.factors || [])
          }
        }
      }

      return {
        categories: ref([]),
        topics: ref([]),
        factors: ref([])
      }
    } catch (error) {
      console.error('[useQuestionEditor] 載入結構資料失敗:', error)
      return {
        categories: ref([]),
        topics: ref([]),
        factors: ref([])
      }
    }
  }

  return {
    // 狀態
    mode: readonly(mode),
    dataSource: readonly(dataSource),
    questionData,
    isLoading: readonly(isLoading),
    loadError: readonly(loadError),
    currentId: readonly(currentId),
    currentContentId: readonly(currentContentId),

    // 方法
    initializeDataSource,
    saveQuestion,
    getStructureData
  }
}
