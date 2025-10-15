<template>
  <div>
    <EditorContainer
      editor-mode="answer"
      :id="assessmentId"
      :content-id="contentId"
      :question-data="questionData"
      :is-loading="isLoading"
      :load-error="loadError"
      :save-function="saveResponseData"
      :hide-submit-button="true"
      @back="handleBack"
      @saved="handleSaved"
      @error="handleError"
      @loaded="handleLoaded"
    />

    <!-- Action Buttons at Bottom -->
    <div v-if="!isLoading && !loadError" class="max-w-7xl mx-auto px-6 sm:px-8 pb-8">
      <div class="flex items-center justify-center gap-4 pt-6 pb-4">
        <button
          @click="handleEdit"
          class="px-6 py-3 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-lg transition-colors duration-200 flex items-center space-x-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
          <span>修改</span>
        </button>
        <button
          @click="handleConfirm"
          :disabled="confirming"
          class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center space-x-2"
        >
          <svg v-if="confirming" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <span>{{ confirming ? '處理中...' : '確認完成' }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useDataMapper } from '~/composables/useDataMapper'
import EditorContainer from '~/components/Editor/EditorContainer.vue'

definePageMeta({
  middleware: 'auth'
})

const route = useRoute()
const router = useRouter()

// 解析路由參數
const assessmentId = parseInt(route.params.id)
const userId = parseInt(route.params.userId)
const contentId = parseInt(route.query.contentId)

usePageTitle('填寫結果檢視')

// 初始化 Composables
const { backendToForm, formToBackend } = useDataMapper()

// 頁面狀態
const isLoading = ref(true)
const loadError = ref('')
const questionData = ref(null)
const currentResponseId = ref(null)
const confirming = ref(false)

// 生命週期 - 載入用戶的回答資料
onMounted(async () => {
  console.log('[Results] Mounted - AssessmentID:', assessmentId, 'UserID:', userId, 'ContentID:', contentId)
  await loadResponseData()
})

// 載入回答資料
const loadResponseData = async () => {
  try {
    isLoading.value = true
    console.log('Loading response data for user review...')

    let responseData

    if (contentId) {
      // 使用單筆記錄 API - 直接取得指定題目的回答
      console.log('📡 調用 API: /api/v1/question-management/assessment/' + assessmentId + '/user/' + userId + '/responses/' + contentId)
      const response = await $fetch(`/api/v1/question-management/assessment/${assessmentId}/user/${userId}/responses/${contentId}`, {
        method: 'GET'
      })

      if (!response.success || !response.data) {
        throw new Error(response.message || '取得填寫結果失敗')
      }

      responseData = response.data
      console.log('✅ API 回傳單一物件:', responseData)
    } else {
      // 舊的方式：取得該用戶所有回答，使用第一筆
      const response = await $fetch(`/api/v1/question-management/assessment/${assessmentId}/responses`, {
        method: 'GET',
        params: {
          answered_by: userId
        }
      })

      if (!response.success) {
        throw new Error(response.message || '取得填寫結果失敗')
      }

      const responses = response.data || []

      if (responses.length === 0) {
        throw new Error('沒有找到填寫結果')
      }

      responseData = responses[0]
      console.log('⚠️ API 回傳陣列，使用第一筆資料')
    }

    // 儲存 response ID 用於更新
    currentResponseId.value = responseData.id

    // 從 response_value 取得完整的回答資料
    const fields = responseData.response_value || {}

    // 轉換資料為表單格式
    const formData = backendToForm({
      factor_description: responseData.factor_description || '',
      b_content: responseData.b_content || '',
      ...fields
    })

    // 設置題目資料（包含結構資訊和用戶答案）
    questionData.value = {
      category_name: responseData.category_name || '',
      topic_name: responseData.topic_name || '',
      factor_name: responseData.factor_name || '',
      description: responseData.factor_name || responseData.topic_name || '填寫結果',
      // 表單資料
      ...formData
    }

    console.log('✅ 回答資料載入完成')
    console.log('📝 questionData:', questionData.value)
  } catch (error) {
    console.error('❌ 載入回答資料時發生錯誤:', error)
    loadError.value = error.message || '載入資料失敗'
  } finally {
    isLoading.value = false
  }
}

// 儲存回答資料函數
const saveResponseData = async (formData) => {
  console.log('=== 準備更新回答 ===')
  console.log('Response ID:', currentResponseId.value)

  if (!currentResponseId.value) {
    throw new Error('找不到回答記錄ID')
  }

  // 使用 DataMapper 轉換表單資料為後端格式
  const backendData = formToBackend(formData)

  // 準備更新資料（只更新 C-H 區段）
  const updateData = {
    c_risk_event_choice: backendData.c_risk_event_choice,
    c_risk_event_description: backendData.c_risk_event_description,
    d_counter_action_choice: backendData.d_counter_action_choice,
    d_counter_action_description: backendData.d_counter_action_description,
    d_counter_action_cost: backendData.d_counter_action_cost,
    e1_risk_description: backendData.e1_risk_description,
    e2_risk_probability: backendData.e2_risk_probability,
    e2_risk_impact: backendData.e2_risk_impact,
    e2_risk_calculation: backendData.e2_risk_calculation,
    f1_opportunity_description: backendData.f1_opportunity_description,
    f2_opportunity_probability: backendData.f2_opportunity_probability,
    f2_opportunity_impact: backendData.f2_opportunity_impact,
    f2_opportunity_calculation: backendData.f2_opportunity_calculation,
    g1_negative_impact_level: backendData.g1_negative_impact_level,
    g1_negative_impact_description: backendData.g1_negative_impact_description,
    h1_positive_impact_level: backendData.h1_positive_impact_level,
    h1_positive_impact_description: backendData.h1_positive_impact_description
  }

  console.log('=== 送出的資料結構 ===')
  console.log('資料:', updateData)

  // 呼叫更新 API
  const response = await $fetch(`/api/v1/question-management/responses/${currentResponseId.value}`, {
    method: 'PUT',
    body: updateData
  })

  console.log('=== API 回應 ===')
  console.log('回應資料:', response)

  if (response.success) {
    console.log('✅ 回答更新成功')
    // 不需要顯示通知，EditorContainer 會自動處理
  } else {
    throw new Error(response.message || '更新失敗')
  }
}

// 事件處理

/**
 * 返回列表頁
 */
const handleBack = () => {
  router.push(`/admin/risk-assessment/questions/${assessmentId}/assignments`)
}

/**
 * 儲存成功事件
 */
const handleSaved = (data) => {
  console.log('[Results] Saved successfully:', data)
}

/**
 * 錯誤事件
 */
const handleError = (error) => {
  console.error('[Results] Error:', error)
}

/**
 * 載入完成事件
 */
const handleLoaded = () => {
  console.log('[Results] Container loaded')
}

/**
 * 修改按鈕處理
 * 將該回答的狀態設為 pending（待更新 / 紅燈）
 */
const handleEdit = async () => {
  const { $notify } = useNuxtApp()

  try {
    if (!currentResponseId.value) {
      await $notify.warning('提示', '找不到回答記錄ID')
      return
    }

    // 更新審核狀態為 pending (待更新 / 紅燈)
    const response = await $fetch(`/api/v1/question-management/responses/${currentResponseId.value}/review-status`, {
      method: 'PUT',
      body: {
        review_status: 'pending'
      }
    })

    if (response.success) {
      await $notify.success('成功', '已標記為待更新狀態。請直接修改內容後點擊上方的「儲存」按鈕')
    } else {
      throw new Error(response.message || '更新狀態失敗')
    }
  } catch (error) {
    console.error('更新狀態失敗:', error)
    await $notify.error('錯誤', '更新狀態失敗，請稍後再試')
  }
}

/**
 * 確認完成按鈕處理
 * 將該回答的狀態設為 approved（已完成 / 綠燈）
 */
const handleConfirm = async () => {
  const { $notify } = useNuxtApp()

  try {
    confirming.value = true

    if (!currentResponseId.value) {
      await $notify.warning('提示', '找不到回答記錄ID')
      return
    }

    // 確認完成對話框
    const result = await $notify.confirm(
      '確認完成',
      '確認完成此評估？完成後將標記為已完成狀態並返回列表頁面。',
      '確認',
      '取消'
    )

    if (result.isConfirmed) {
      // 更新審核狀態為 approved (已完成 / 綠燈)
      const response = await $fetch(`/api/v1/question-management/responses/${currentResponseId.value}/review-status`, {
        method: 'PUT',
        body: {
          review_status: 'approved'
        }
      })

      if (response.success) {
        await $notify.success('完成', '評估已確認完成')

        // 返回上一頁（評估列表）
        router.push(`/admin/risk-assessment/questions/${assessmentId}/assignments`)
      } else {
        throw new Error(response.message || '更新狀態失敗')
      }
    }
  } catch (err) {
    console.error('確認完成時發生錯誤:', err)
    await $notify.error('錯誤', '確認完成失敗，請稍後再試')
  } finally {
    confirming.value = false
  }
}

// SEO - Dynamic title
useHead(() => ({
  title: questionData.value
    ? `${questionData.value.description} - 填寫結果 - 風險評估管理系統`
    : '填寫結果詳情 - 風險評估管理系統'
}))
</script>

