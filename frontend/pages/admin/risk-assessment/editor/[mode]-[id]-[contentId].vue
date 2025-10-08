<template>
  <EditorContainer
    :editor-mode="editorMode"
    :id="id"
    :content-id="contentId"
    :question-data="questionData"
    :is-loading="isLoading"
    :load-error="loadError"
    :save-function="saveQuestion"
    @back="handleBack"
    @saved="handleSaved"
    @error="handleError"
    @loaded="handleLoaded"
  />
</template>

<script setup>
import { useQuestionEditor } from '~/composables/useQuestionEditor'
import { useEditorFeatures } from '~/composables/useEditorFeatures'
import EditorContainer from '~/components/Editor/EditorContainer.vue'

definePageMeta({
  middleware: 'auth'
})

const route = useRoute()
const router = useRouter()

// 解析路由參數
const editorMode = route.params.mode
const id = parseInt(route.params.id)
const contentId = parseInt(route.params.contentId)

usePageTitle('題目編輯')

// 初始化 Composables
const {
  questionData,
  isLoading,
  loadError,
  currentId,
  currentContentId,
  initializeDataSource,
  saveQuestion,
  getStructureData
} = useQuestionEditor()

const { getBackPath, getPreviewPath } = useEditorFeatures(computed(() => editorMode))

// 生命週期 - 初始化資料來源
onMounted(async () => {
  console.log('[Admin Editor] Mounted - Mode:', editorMode, 'ID:', id, 'ContentID:', contentId)

  // 初始化資料來源
  await initializeDataSource(editorMode, id, contentId)
})

// 事件處理

/**
 * 返回列表頁
 */
const handleBack = () => {
  const from = route.query.from // 取得 preview 來源

  // 對於 question 模式，傳遞 company_id 和 assessment_id
  const companyId = questionData.value?.company_id || null
  const assessmentId = questionData.value?.assessment_id || id

  const backPath = getBackPath(id, contentId, from, companyId, assessmentId)
  router.push(backPath)
}

/**
 * 儲存成功事件
 */
const handleSaved = (data) => {
  console.log('[Admin Editor] Saved successfully:', data)
}

/**
 * 錯誤事件
 */
const handleError = (error) => {
  console.error('[Admin Editor] Error:', error)
}

/**
 * 載入完成事件
 */
const handleLoaded = () => {
  console.log('[Admin Editor] Container loaded')
}
</script>
