<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-8">

      <!-- Loading State -->
      <div v-if="isLoading" class="flex items-center justify-center p-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="loadError" class="p-6 bg-red-50 border border-red-200 rounded-2xl">
        <h3 class="text-lg font-medium text-red-900 mb-2">載入失敗</h3>
        <p class="text-red-700">{{ loadError }}</p>
        <button
          @click="handleBack"
          class="mt-4 px-4 py-2 bg-red-600 text-white rounded-2xl hover:bg-red-700"
        >
          返回列表
        </button>
      </div>

      <!-- Editor Content -->
      <template v-else>
        <!-- Page Header -->
        <EditorHeader
          :title="pageTitle"
          :subtitle="pageSubtitle"
          :features="features"
          :is-saving="isSaving"
          @back="handleBack"
          @preview="handlePreview"
          @save="handleSave"
          @fill-test-data="fillTestData"
        />

        <!-- Form Sections -->
        <div class="space-y-6">
          <!-- Section A: 風險因子議題描述 -->
          <EditorSection
            section-id="A"
            title="風險因子議題描述"
            :collapsible="features.collapsibleSections"
            v-model:expanded="expandedSections.sectionA"
          >
            <RichTextEditor
              v-model="formData.riskFactorDescription"
              :readonly="features.readonly"
              :show-html-info="false"
              placeholder="請輸入風險因子議題描述..."
            />
          </EditorSection>

          <!-- Section B: 參考文字 -->
          <EditorSection
            section-id="B"
            title="參考文字&模組工具評估結果"
            :collapsible="features.collapsibleSections"
            v-model:expanded="expandedSections.sectionB"
          >
            <RichTextEditor
              v-model="formData.referenceText"
              :readonly="features.readonly"
              :show-html-info="false"
              placeholder="請輸入參考文字..."
            />
          </EditorSection>

          <!-- Section C: 風險事件 -->
          <EditorRiskEventSection
            v-model:choice="formData.hasRiskEvent"
            v-model:description="formData.riskEventDescription"
            :readonly="features.readonly"
          />

          <!-- Section D: 對應作為 -->
          <EditorCounterActionSection
            v-model:choice="formData.hasCounterAction"
            v-model:description="formData.counterActionDescription"
            v-model:cost="formData.counterActionCost"
            :readonly="features.readonly"
          />

          <!-- 量表按鈕區 -->
          <EditorScaleBar
            :mode="features.scaleMode"
            :show-button="features.showScaleButton"
            @open-scale="showScaleModal = true"
          />

          <!-- Section E & F: 風險與機會 (Grid Layout) -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <EditorRiskSection
              v-model="formData.risk"
              :probability-options="probabilityScaleOptions"
              :impact-options="impactScaleOptions"
              :readonly="features.readonly"
              :hover-text="formData.hoverTexts.E1"
              :show-hover-icon="features.showHoverIcons"
              :editable-hover="features.enableHoverTextEdit"
              @edit-hover="editHoverText('E1')"
            />

            <EditorOpportunitySection
              v-model="formData.opportunity"
              :probability-options="probabilityScaleOptions"
              :impact-options="impactScaleOptions"
              :readonly="features.readonly"
              :hover-text="formData.hoverTexts.F1"
              :show-hover-icon="features.showHoverIcons"
              :editable-hover="features.enableHoverTextEdit"
              @edit-hover="editHoverText('F1')"
            />
          </div>

          <!-- Context Text for External Impact -->
          <div class="bg-green-600 text-white px-6 py-3 rounded-2xl">
            <span class="font-bold text-white text-xl">
              請依上述公司營點之進行或風險會環境,結合評估公司之營運程此議題可能造成的「對外」衝擊(對外部環境、環境、人群(含含責人補)之正/負面影響)
            </span>
          </div>

          <!-- Section G & H: 對外衝擊 (Grid Layout) -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <EditorImpactSection
              type="negative"
              v-model="formData.negativeImpact"
              :readonly="features.readonly"
              :hover-text="formData.hoverTexts.G1"
              :show-hover-icon="features.showHoverIcons"
              :editable-hover="features.enableHoverTextEdit"
              @edit-hover="editHoverText('G1')"
            />

            <EditorImpactSection
              type="positive"
              v-model="formData.positiveImpact"
              :readonly="features.readonly"
              :hover-text="formData.hoverTexts.H1"
              :show-hover-icon="features.showHoverIcons"
              :editable-hover="features.enableHoverTextEdit"
              @edit-hover="editHoverText('H1')"
            />
          </div>
        </div>
      </template>
    </div>

    <!-- Hover Text Edit Modal (僅 template 模式) -->
    <Teleport to="body" v-if="features.enableHoverTextEdit && showHoverEditModal">
      <div
        class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center"
        @click.self="cancelHoverEdit"
      >
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md mx-4" @click.stop>
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
              編輯提示文字 - {{ editingSection }}
            </h3>

            <form @submit.prevent="saveHoverText">
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  提示文字內容
                </label>
                <textarea
                  v-model="editingHoverText"
                  rows="4"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                  placeholder="請輸入使用者 hover 時顯示的文字"
                  required
                ></textarea>
              </div>

              <div class="flex justify-end space-x-3">
                <button
                  type="button"
                  @click="cancelHoverEdit"
                  class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors duration-200"
                >
                  取消
                </button>
                <button
                  type="submit"
                  class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200"
                >
                  儲存
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Scale Modal - Editor Mode -->
    <ScaleEditorModal
      v-if="showScaleModal && features.scaleMode === 'editor'"
      v-model="showScaleModal"
      title="量表編輯"
      :is-loading="isLoadingScales"
      :mode="features.scaleMode"
      :probability-columns="probabilityScaleColumns"
      :probability-rows="probabilityScaleRows"
      :selected-probability-display-column="selectedProbabilityDisplayColumn"
      :show-probability-description="showDescriptionText"
      :probability-description-text="descriptionText"
      :impact-columns="impactScaleColumns"
      :impact-rows="impactScaleRows"
      :selected-impact-display-column="selectedImpactDisplayColumn"
      :show-impact-description="showImpactDescriptionText"
      :impact-description-text="impactDescriptionText"
      @update:selected-probability-display-column="selectedProbabilityDisplayColumn = $event"
      @update:selected-impact-display-column="selectedImpactDisplayColumn = $event"
      @update:probability-description-text="descriptionText = $event"
      @update:impact-description-text="impactDescriptionText = $event"
      @add-probability-column="addProbabilityColumn"
      @remove-probability-column="removeProbabilityColumn"
      @add-probability-row="addProbabilityRow"
      @remove-probability-row="removeProbabilityRow"
      @add-probability-description="addProbabilityDescriptionText"
      @remove-probability-description="removeProbabilityDescriptionText"
      @add-impact-column="addImpactColumn"
      @remove-impact-column="removeImpactColumn"
      @add-impact-row="addImpactRow"
      @remove-impact-row="removeImpactRow"
      @add-impact-description="addImpactDescriptionText"
      @remove-impact-description="removeImpactDescriptionText"
      @save="handleScaleSave"
    />

    <!-- Scale Modal - Viewer Mode -->
    <ScaleViewerModal
      v-if="showScaleModal && features.scaleMode !== 'editor'"
      v-model="showScaleModal"
      :loading="isLoadingScales"
      :probability-scale-columns="probabilityScaleColumns"
      :probability-scale-rows="probabilityScaleRows"
      :description-text="descriptionText"
      :show-description-text="showDescriptionText"
      :impact-scale-columns="impactScaleColumns"
      :impact-scale-rows="impactScaleRows"
    />
  </div>
</template>

<script setup>
import { useQuestionEditor } from '~/composables/useQuestionEditor'
import { useEditorFeatures } from '~/composables/useEditorFeatures'
import { useDataMapper } from '~/composables/useDataMapper'
import { useScaleManagement } from '~/composables/useScaleManagement'
import RichTextEditor from '~/components/RichTextEditor.vue'
import ScaleEditorModal from '~/components/Scale/ScaleEditorModal.vue'
import ScaleViewerModal from '~/components/Scale/ScaleViewerModal.vue'
import EditorHeader from '~/components/Editor/EditorHeader.vue'
import EditorSection from '~/components/Editor/EditorSection.vue'
import EditorRiskEventSection from '~/components/Editor/EditorRiskEventSection.vue'
import EditorCounterActionSection from '~/components/Editor/EditorCounterActionSection.vue'
import EditorScaleBar from '~/components/Editor/EditorScaleBar.vue'
import EditorRiskSection from '~/components/Editor/EditorRiskSection.vue'
import EditorOpportunitySection from '~/components/Editor/EditorOpportunitySection.vue'
import EditorImpactSection from '~/components/Editor/EditorImpactSection.vue'
import apiClient from '~/utils/api.js'

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

const { features, getBackPath, getPreviewPath } = useEditorFeatures(computed(() => editorMode))
const { backendToForm, formToBackend, getDefaultFormData, preparePreviewQuery, generateTestData } = useDataMapper()

// 量表管理
const {
  probabilityScaleColumns,
  probabilityScaleRows,
  selectedProbabilityDisplayColumn,
  showDescriptionText,
  descriptionText,
  impactScaleColumns,
  impactScaleRows,
  selectedImpactDisplayColumn,
  showImpactDescriptionText,
  impactDescriptionText,
  addProbabilityColumn,
  removeProbabilityColumn,
  addProbabilityRow,
  removeProbabilityRow,
  addProbabilityDescriptionText,
  removeProbabilityDescriptionText,
  addImpactColumn,
  removeImpactColumn,
  addImpactRow,
  removeImpactRow,
  addImpactDescriptionText,
  removeImpactDescriptionText,
  probabilityScaleOptions,
  impactScaleOptions,
  loadScalesData,
  prepareScaleDataForSubmission
} = useScaleManagement()

// 頁面狀態
const isSaving = ref(false)
const showScaleModal = ref(false)
const isLoadingScales = ref(false)
const showHoverEditModal = ref(false)
const editingSection = ref('')
const editingHoverText = ref('')

// Section 展開狀態
const expandedSections = ref(features.value.defaultExpandedSections)

// 表單資料
const formData = ref(getDefaultFormData())

// 計算頁面標題
const pageTitle = computed(() => {
  return questionData.value?.description || features.value.pageTitle
})

const pageSubtitle = computed(() => {
  if (editorMode === 'template') {
    const categoryName = '未知類別' // TODO: 從資料取得
    return `類別：${categoryName} | 編輯完整的ESG評估題目內容`
  }
  return features.value.pageSubtitle
})

// ===== 生命週期 =====
onMounted(async () => {
  console.log('[Editor] Mounted - Mode:', editorMode, 'ID:', id, 'ContentID:', contentId)

  // 初始化資料來源
  await initializeDataSource(editorMode, id, contentId)

  // 將後端資料轉換為表單格式
  if (questionData.value) {
    formData.value = backendToForm(questionData.value)
    console.log('[Editor] Form data initialized:', formData.value)
  }

  // 載入量表資料
  await loadScaleData()
})

// ===== 方法 =====

/**
 * 載入量表資料
 */
const loadScaleData = async () => {
  isLoadingScales.value = true
  try {
    let scalesData = null

    if (editorMode === 'template' || editorMode === 'preview') {
      // Template 模式: 從 API 載入
      const [probResponse, impactResponse] = await Promise.all([
        apiClient.request(`/templates/${id}/scales/probability`, { method: 'GET' }),
        apiClient.request(`/templates/${id}/scales/impact`, { method: 'GET' })
      ])

      scalesData = {
        probability_scale: probResponse.success ? probResponse.data : null,
        impact_scale: impactResponse.success ? impactResponse.data : null
      }
    } else if (editorMode === 'question') {
      // Question 模式: 從 API 載入
      const response = await $fetch(`/api/v1/question-management/assessment/${id}/scales`)
      if (response.success) {
        scalesData = response.data
      }
    }

    if (scalesData) {
      loadScalesData(scalesData)
      console.log('[Editor] Scale data loaded successfully')
    }
  } catch (error) {
    console.error('[Editor] Failed to load scale data:', error)
  } finally {
    isLoadingScales.value = false
  }
}

/**
 * 返回列表頁
 */
const handleBack = () => {
  const backPath = getBackPath(id)
  router.push(backPath)
}

/**
 * 開啟預覽
 */
const handlePreview = () => {
  if (editorMode === 'preview') return

  const previewPath = getPreviewPath(id, contentId)
  if (previewPath) {
    router.push({
      path: previewPath,
      query: preparePreviewQuery(formData.value)
    })
  } else {
    // question 模式使用 inline modal (TODO: 實作)
    console.log('Preview modal not implemented yet')
  }
}

/**
 * 儲存題目
 */
const handleSave = async () => {
  if (isSaving.value || editorMode === 'preview') return

  isSaving.value = true

  try {
    // 轉換表單資料為後端格式
    const backendData = formToBackend(formData.value, questionData.value)

    console.log('[Editor] Saving data:', backendData)

    // 呼叫儲存
    await saveQuestion(backendData, questionData.value)

    // 顯示成功訊息
    const { $toast } = useNuxtApp()
    $toast.success('題目已成功儲存')

    // 返回列表頁
    setTimeout(() => {
      handleBack()
    }, 1000)
  } catch (error) {
    console.error('[Editor] Save failed:', error)
    const { $toast } = useNuxtApp()
    $toast.error('儲存失敗，請稍後再試')
  } finally {
    isSaving.value = false
  }
}

/**
 * 填入測試資料
 */
const fillTestData = () => {
  if (!features.value.showTestDataButton) return

  formData.value = generateTestData()

  const { $toast } = useNuxtApp()
  $toast.success('測試資料已填入')
}

/**
 * 編輯 Hover 文字
 */
const editHoverText = (section) => {
  if (!features.value.enableHoverTextEdit) return

  editingSection.value = section
  editingHoverText.value = formData.value.hoverTexts[section]
  showHoverEditModal.value = true
}

/**
 * 儲存 Hover 文字
 */
const saveHoverText = () => {
  formData.value.hoverTexts[editingSection.value] = editingHoverText.value
  cancelHoverEdit()

  const { $toast } = useNuxtApp()
  $toast.success('提示文字已更新')
}

/**
 * 取消編輯 Hover 文字
 */
const cancelHoverEdit = () => {
  showHoverEditModal.value = false
  editingSection.value = ''
  editingHoverText.value = ''
}

/**
 * 儲存量表資料
 */
const handleScaleSave = async () => {
  if (editorMode === 'question' || editorMode === 'preview') {
    // question 模式不允許編輯量表
    const { $toast } = useNuxtApp()
    $toast.info('此模式下量表為唯讀')
    return
  }

  try {
    const scaleData = prepareScaleDataForSubmission()

    // 儲存可能性量表
    await apiClient.request(`/templates/${id}/scales/probability`, {
      method: 'POST',
      body: {
        columns: scaleData.probability_scale.columns,
        rows: scaleData.probability_scale.rows,
        descriptionText: scaleData.probability_scale.description_text,
        showDescriptionText: scaleData.probability_scale.show_description,
        selectedDisplayColumn: scaleData.probability_scale.selected_display_column
      }
    })

    // 儲存財務衝擊量表
    await apiClient.request(`/templates/${id}/scales/impact`, {
      method: 'POST',
      body: {
        columns: scaleData.impact_scale.columns,
        rows: scaleData.impact_scale.rows,
        descriptionText: scaleData.impact_scale.description_text,
        showDescriptionText: scaleData.impact_scale.show_description,
        selectedDisplayColumn: scaleData.impact_scale.selected_display_column
      }
    })

    showScaleModal.value = false

    const { $toast } = useNuxtApp()
    $toast.success('量表已成功儲存')
  } catch (error) {
    console.error('[Editor] Scale save failed:', error)
    const { $toast } = useNuxtApp()
    $toast.error('量表儲存失敗')
  }
}
</script>

<style scoped>
/* 樣式已使用 Tailwind CSS 和各元件的 scoped styles */
</style>
