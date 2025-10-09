<template>
  <EditorContainer
    editor-mode="answer"
    :id="questionId"
    :content-id="contentId"
    :company-id="companyId"
    :question-data="questionData"
    :question-template="questionTemplate"
    :is-loading="isLoading"
    :load-error="loadError"
    :save-function="saveAnswerData"
    :is-web-mode="true"
    :external-user-id="externalUserStore.userId"
    @back="handleBack"
    @saved="handleSaved"
    @error="handleError"
    @loaded="handleLoaded"
  />
</template>

<script setup>
import { useDataMapper } from '~/composables/useDataMapper'
import { useEditorFeatures } from '~/composables/useEditorFeatures'
import EditorContainer from '~/components/Editor/EditorContainer.vue'

definePageMeta({
  middleware: 'auth'
})

const route = useRoute()
const router = useRouter()

// 解析路由參數
const companyId = route.params.companyId
const questionId = parseInt(route.params.questionId)
const contentId = parseInt(route.params.contentId)

// 取得外部用戶資料
const externalUserStore = useExternalUserStore()

usePageTitle('風險評估作答')

// 初始化 Composables
const { backendToForm, formToBackend } = useDataMapper()
const editorMode = ref('answer')
const { getBackPath } = useEditorFeatures(editorMode)

// 頁面狀態
const isLoading = ref(true)
const loadError = ref('')
const questionData = ref(null)
const questionTemplate = ref(null) // 題目模板，用於 placeholder

// 生命週期 - 初始化用戶資料和題目資料
onMounted(async () => {
  console.log('[Web Answer] Mounted - CompanyID:', companyId, 'QuestionID:', questionId, 'ContentID:', contentId)

  // 檢查並初始化用戶資料
  const token = route.query.token || ''

  console.log('=== 用戶資料初始化檢查 ===')
  console.log('Token from URL:', token)
  console.log('Current userId in store:', externalUserStore.userId)

  // 檢查是否需要載入用戶資料
  if (externalUserStore.userId) {
    console.log('✅ Store 中已有 userId，可以直接使用:', externalUserStore.userId)
  } else if (token) {
    console.log('🔄 從 token 載入用戶資料...')
    try {
      await externalUserStore.fetchExternalUserData(token)
      console.log('✅ 用戶資料載入完成')
    } catch (error) {
      console.error('❌ 載入用戶資料失敗:', error)
      loadError.value = '用戶資料載入失敗'
      isLoading.value = false
      return
    }
  } else {
    console.warn('⚠️ 未提供 token 且 Store 中沒有 userId')
  }

  // 如果 userId 仍然為空，但有 externalId，嘗試重新獲取
  if (!externalUserStore.userId && externalUserStore.externalId) {
    console.log('🔄 嘗試從 externalId 重新獲取內部用戶ID...')
    try {
      await externalUserStore.fetchInternalUserId(externalUserStore.externalId)
    } catch (error) {
      console.error('❌ 重新獲取內部用戶ID時發生錯誤:', error)
    }
  }

  // 載入題目資料
  await loadQuestionData()
})

// 載入題目資料
const loadQuestionData = async () => {
  try {
    isLoading.value = true
    console.log('Loading question data for answer form...')

    // 載入題目內容以取得 A 和 B 區段
    console.log('📡 調用 API: /api/v1/question-management/contents/' + contentId)
    const contentResponse = await $fetch(`/api/v1/question-management/contents/${contentId}`)
    console.log('📡 API 回應:', contentResponse)

    if (contentResponse.success && contentResponse.data?.content) {
      const content = contentResponse.data.content
      console.log('📦 content 資料:', content)
      console.log('📦 factor_description:', content.factor_description)
      console.log('📦 b_content:', content.b_content)

      const formData = backendToForm(content)
      console.log('🔄 backendToForm 轉換後:', formData)

      // 儲存題目模板（用於 placeholder）
      questionTemplate.value = formData

      // 儲存題目的結構資訊和 A/B 區段內容
      // C-H 區段初始為空，題目內容作為 placeholder
      questionData.value = {
        category_name: content.category_name || '',
        topic_name: content.topic_name || '',
        factor_name: content.factor_name || '',
        // A 和 B 區段顯示題目內容
        riskFactorDescription: formData.riskFactorDescription,
        referenceText: formData.referenceText,
        // C-H 區段初始為空
        hasRiskEvent: '',
        riskEventDescription: '',
        hasCounterAction: '',
        counterActionDescription: '',
        counterActionCost: '',
        risk: {
          description: '',
          probability: 1,
          impactLevel: 1,
          calculation: ''
        },
        opportunity: {
          description: '',
          probability: 1,
          impactLevel: 3,
          calculation: ''
        },
        negativeImpact: {
          level: 'level-2',
          description: ''
        },
        positiveImpact: {
          level: 'level-2',
          description: ''
        },
        hoverTexts: formData.hoverTexts
      }

      console.log('✅ 題目內容載入完成')
      console.log('📋 questionTemplate:', questionTemplate.value)
      console.log('📝 questionData:', questionData.value)
    } else {
      console.error('❌ API 回應格式錯誤或無資料')
    }

    // 載入現有答案（如果存在）
    console.log('🔍 嘗試載入現有答案...')

    if (!externalUserStore.userId) {
      console.warn('⚠️ userId 為空，無法載入現有答案')
      return
    }

    const responseResponse = await $fetch(`/api/v1/question-management/assessment/${questionId}/responses`, {
      query: {
        content_id: contentId,
        answered_by: externalUserStore.userId
      }
    })

    if (responseResponse.success && responseResponse.data && responseResponse.data.length > 0) {
      const existingAnswer = responseResponse.data[0]
      console.log('找到現有答案:', existingAnswer)

      if (existingAnswer.response_fields) {
        // 合併現有答案到題目資料（只更新 C-H 區段）
        const answerData = backendToForm(existingAnswer.response_fields, true)

        // 只更新 C-H 區段的答案，保留 A 和 B 的題目內容
        questionData.value = {
          ...questionData.value,
          // C-H 區段使用用戶答案
          hasRiskEvent: answerData.hasRiskEvent,
          riskEventDescription: answerData.riskEventDescription,
          hasCounterAction: answerData.hasCounterAction,
          counterActionDescription: answerData.counterActionDescription,
          counterActionCost: answerData.counterActionCost,
          risk: answerData.risk,
          opportunity: answerData.opportunity,
          negativeImpact: answerData.negativeImpact,
          positiveImpact: answerData.positiveImpact
        }
        console.log('✅ 現有答案已載入到表單')
      }
    } else {
      console.log('ℹ️ 沒有找到現有答案，這是新的填答')
    }
  } catch (error) {
    console.error('❌ 載入問題資料時發生錯誤:', error)
    loadError.value = error.message || '載入資料失敗'
  } finally {
    isLoading.value = false
  }
}

// 儲存答案函數
const saveAnswerData = async (formData) => {
  const { $notify } = useNuxtApp()

  console.log('=== 準備送出答案 ===')
  console.log('用戶ID:', externalUserStore.userId)

  // 驗證必要參數
  if (!externalUserStore.userId) {
    if (!externalUserStore.externalId) {
      throw new Error('用戶驗證失敗：未找到外部用戶ID，請檢查是否有有效的 token 參數')
    } else {
      throw new Error(`用戶驗證失敗：外部用戶ID (${externalUserStore.externalId}) 在系統中找不到對應的內部用戶記錄，請聯繫管理員進行用戶同步`)
    }
  }

  if (!questionId || !contentId) {
    throw new Error('問題ID或內容ID不存在')
  }

  // 使用 DataMapper 轉換表單資料為後端格式
  const backendData = formToBackend(formData)

  const answerData = {
    responses: [
      {
        question_content_id: parseInt(contentId),
        response_value: backendData
      }
    ],
    answered_by: parseInt(externalUserStore.userId)
  }

  console.log('=== 送出的資料結構 ===')
  console.log('資料:', answerData)

  const response = await $fetch(`/api/v1/question-management/assessment/${questionId}/responses`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: answerData
  })

  console.log('=== API 回應 ===')
  console.log('回應資料:', response)

  if (response.success) {
    console.log('✅ 答案保存成功')
    await $notify.success('保存成功！', '您的答案已成功保存')

    // 返回到題目列表
    const backPath = getBackPath(questionId, contentId, null, companyId, questionId)
    router.push(backPath)
  } else {
    throw new Error(response.message || '儲存失敗')
  }
}

// 事件處理

/**
 * 返回列表頁
 */
const handleBack = async () => {
  const { $notify } = useNuxtApp()

  const result = await $notify.confirm(
    '確認離開',
    '您確定要離開此頁面嗎？未保存的資料將會遺失。',
    '離開',
    '取消'
  )

  if (result.isConfirmed) {
    const backPath = getBackPath(questionId, contentId, null, companyId, questionId)
    router.push(backPath)
  }
}

/**
 * 儲存成功事件
 */
const handleSaved = (data) => {
  console.log('[Web Answer] Saved successfully:', data)
}

/**
 * 錯誤事件
 */
const handleError = (error) => {
  console.error('[Web Answer] Error:', error)
}

/**
 * 載入完成事件
 */
const handleLoaded = () => {
  console.log('[Web Answer] Container loaded')
}
</script>
