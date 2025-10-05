/**
 * 編輯器功能開關管理 Composable
 *
 * 用途: 根據不同編輯模式 (template/question/preview/answer) 控制功能顯示與行為
 *
 * @param {Ref<string>} mode - 編輯器模式
 * @returns {Object} 功能開關配置
 */
export const useEditorFeatures = (mode) => {
  /**
   * 功能開關配置
   * 根據不同模式返回對應的功能啟用狀態
   */
  const features = computed(() => {
    const currentMode = unref(mode)

    return {
      // ========== 按鈕與操作功能 ==========

      /**
       * 顯示「填入測試資料」按鈕
       * template 和 question 模式顯示，方便開發測試
       */
      showTestDataButton: currentMode === 'template' || currentMode === 'question',

      /**
       * 顯示「儲存」按鈕
       * preview 模式不顯示儲存功能
       */
      showSaveButton: currentMode !== 'preview',

      /**
       * 顯示「預覽」按鈕
       * preview 和 answer 模式不顯示預覽按鈕
       */
      showPreviewButton: currentMode !== 'preview' && currentMode !== 'answer',

      /**
       * 顯示「返回」按鈕
       * 所有模式都需要
       */
      showBackButton: true,

      // ========== 資訊與編輯功能 ==========

      /**
       * 啟用資訊圖示 (i) 編輯功能
       * 僅在 template 模式可以編輯 hover 文字
       */
      enableHoverTextEdit: currentMode === 'template',

      /**
       * 顯示資訊圖示 (i)
       * template, preview 和 answer 模式都顯示，但只有 template 可編輯
       */
      showHoverIcons: currentMode === 'template' || currentMode === 'preview' || currentMode === 'answer',

      /**
       * 顯示基本資訊區塊 (類別、主題、因子選擇)
       * question 模式和 preview 模式隱藏此區塊
       */
      showBasicInfo: currentMode === 'template',

      // ========== 量表功能 ==========

      /**
       * 量表功能模式
       * - 'editor': 完整編輯模式 (可新增/刪除/編輯列與欄)
       * - 'viewer': 唯讀檢視模式 (只能看，不能編輯)
       * - 'viewer-compact': 精簡檢視模式 (可拖曳、精簡化的檢視視窗)
       */
      scaleMode: currentMode === 'preview' || currentMode === 'answer'
        ? 'viewer-compact'
        : 'editor',

      /**
       * 顯示量表按鈕
       * 所有模式都顯示
       */
      showScaleButton: true,

      /**
       * 量表按鈕文字
       */
      scaleButtonText: '量表檢視',

      // ========== 表單欄位狀態 ==========

      /**
       * 表單欄位唯讀狀態
       * preview 模式所有欄位都是唯讀
       */
      readonly: currentMode === 'preview',

      /**
       * Section 區塊可收折
       * preview 和 answer 模式下 Section A 和 B 可以收折
       */
      collapsibleSections: currentMode === 'preview' || currentMode === 'answer',

      /**
       * 預設展開的 Section
       * preview/answer 模式: Section A 展開, Section B 收折
       * 其他模式: 全部展開
       */
      defaultExpandedSections: currentMode === 'preview' || currentMode === 'answer'
        ? { sectionA: true, sectionB: false }
        : { sectionA: true, sectionB: true },

      // ========== 預覽功能 ==========

      /**
       * 預覽模式類型
       * - 'inline': 內建預覽 modal (question 模式)
       * - 'navigation': 跳轉到獨立預覽頁面 (template 模式)
       * - 'none': 不提供預覽功能 (preview 模式)
       */
      previewMode: currentMode === 'preview'
        ? 'none'
        : currentMode === 'question'
          ? 'inline'
          : 'navigation',

      // ========== UI 顯示差異 ==========

      /**
       * 頁面標題文字
       */
      pageTitle: currentMode === 'preview'
        ? '使用者表單預覽'
        : currentMode === 'answer'
          ? '風險評估作答'
          : currentMode === 'question'
            ? '題目編輯'
            : '題目編輯',

      /**
       * 頁面副標題
       */
      pageSubtitle: currentMode === 'preview'
        ? '檢視使用者將看到的表單內容'
        : currentMode === 'answer'
          ? '請完整填寫以下表單內容'
          : currentMode === 'question'
            ? '編輯完整的ESG評估題目內容'
            : '類別：{category} | 編輯完整的ESG評估題目內容',

      /**
       * 「紀錄」按鈕顯示
       * preview 模式不顯示紀錄按鈕
       */
      showRecordButtons: currentMode !== 'preview',

      /**
       * 結束按鈕文字
       */
      backButtonText: currentMode === 'preview' ? '結束預覽' : '返回',

      // ========== 當前模式資訊 ==========

      /**
       * 當前模式
       */
      currentMode,

      /**
       * 是否為模板模式
       */
      isTemplateMode: currentMode === 'template',

      /**
       * 是否為問卷模式
       */
      isQuestionMode: currentMode === 'question',

      /**
       * 是否為預覽模式
       */
      isPreviewMode: currentMode === 'preview',

      /**
       * 是否為答題模式
       */
      isAnswerMode: currentMode === 'answer'
    }
  })

  /**
   * 檢查特定功能是否啟用
   * @param {string} featureName - 功能名稱
   * @returns {boolean}
   */
  const isFeatureEnabled = (featureName) => {
    return features.value[featureName] || false
  }

  /**
   * 取得功能相關的 CSS class
   * @param {string} baseClass - 基礎 class
   * @returns {string} 根據模式調整的 class
   */
  const getFeatureClass = (baseClass) => {
    const currentMode = unref(mode)
    return `${baseClass} ${baseClass}--${currentMode}`
  }

  /**
   * 取得導航路徑
   * 根據不同模式返回正確的返回路徑
   * @param {number} id - templateId 或 assessmentId
   * @param {number} contentId - 題目內容 ID (preview 模式必需)
   * @param {string} from - preview 模式的來源 ('template' 或 'question')
   * @param {number} companyId - 公司 ID (question/answer 模式必需)
   * @param {number} assessmentId - 評估記錄 ID (question/answer 模式必需)
   * @returns {string} 路由路徑
   */
  const getBackPath = (id, contentId, from = null, companyId = null, assessmentId = null) => {
    const currentMode = unref(mode)

    if (currentMode === 'preview') {
      // 根據來源決定返回路徑
      if (from === 'question') {
        return `/admin/risk-assessment/editor/question-${id}-${contentId}`
      } else {
        return `/admin/risk-assessment/editor/template-${id}-${contentId}`
      }
    } else if (currentMode === 'template') {
      return `/admin/risk-assessment/templates/${id}/content`
    } else if (currentMode === 'question') {
      // question 模式需要返回到 /admin/risk-assessment/questions/{companyId}/management/{assessmentId}/content
      if (companyId && assessmentId) {
        return `/admin/risk-assessment/questions/${companyId}/management/${assessmentId}/content`
      }
      // 備用：如果沒有提供參數，使用舊的路徑
      return `/admin/risk-assessment/questions/${id}/management`
    } else if (currentMode === 'answer') {
      // answer 模式返回到題目內容列表
      if (companyId && assessmentId) {
        return `/web/risk-assessment/questions/${companyId}/management/${assessmentId}/content`
      }
    }

    return '/admin/risk-assessment'
  }

  /**
   * 取得預覽路徑
   * @param {number} id - templateId 或 assessmentId
   * @param {number} contentId - 題目內容 ID
   * @returns {object|null} 預覽路由物件 (含 path 和 query)，如果不支援預覽則返回 null
   */
  const getPreviewPath = (id, contentId) => {
    const currentMode = unref(mode)

    if (currentMode === 'template') {
      return {
        path: `/admin/risk-assessment/editor/preview-${id}-${contentId}`,
        query: { from: 'template' }
      }
    } else if (currentMode === 'question') {
      return {
        path: `/admin/risk-assessment/editor/preview-${id}-${contentId}`,
        query: { from: 'question' }
      }
    }

    return null
  }

  return {
    features,
    isFeatureEnabled,
    getFeatureClass,
    getBackPath,
    getPreviewPath
  }
}
