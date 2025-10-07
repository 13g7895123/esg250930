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
          :features="computedFeatures"
          :is-saving="isSaving"
          @back="handleBack"
          @preview="handlePreview"
          @save="handleSave"
          @fill-test-data="fillTestData"
        />

        <!-- Risk Structure Information (舊版風格) -->
        <div v-if="showStructureInfo" class="mb-6">
          <div :class="getStructureGridClass">
            <!-- Risk Category -->
            <div
              v-if="questionData?.category_name"
              class="flex items-center gap-3 bg-gray-100 dark:bg-gray-700 p-4 rounded-2xl"
            >
              <h3 class="font-bold text-gray-900 dark:text-white text-xl whitespace-nowrap">風險類別</h3>
              <div class="flex-1 min-w-0">
                <p class="text-gray-900 dark:text-white text-xl truncate">
                  {{ questionData.category_name }}
                </p>
              </div>
            </div>

            <!-- Risk Topic -->
            <div
              v-if="questionData?.topic_name"
              class="flex items-center gap-3 bg-gray-100 dark:bg-gray-700 p-4 rounded-2xl"
            >
              <h3 class="font-bold text-gray-900 dark:text-white text-xl whitespace-nowrap">風險主題</h3>
              <div class="flex-1 min-w-0">
                <p class="text-gray-900 dark:text-white text-xl truncate">
                  {{ questionData.topic_name }}
                </p>
              </div>
            </div>

            <!-- Risk Factor -->
            <div
              v-if="questionData?.factor_name"
              class="flex items-center gap-3 risk-factor-bg p-4 rounded-2xl"
            >
              <h3 class="font-bold text-white text-xl whitespace-nowrap">風險因子</h3>
              <div class="flex-1 min-w-0">
                <p class="text-white text-xl font-medium truncate">
                  {{ questionData.factor_name }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Sections -->
        <div class="space-y-6">
          <!-- Section A: 風險因子議題描述 (Editable rich text like Section B) -->
          <EditorSection
            section-id="A"
            title="風險因子議題描述"
            :collapsible="computedFeatures.collapsibleSections"
            v-model:expanded="expandedSections.sectionA"
          >
            <RichTextEditor
              v-model="formData.riskFactorDescription"
              :show-html-info="false"
              placeholder="請輸入風險因子議題描述..."
            />
          </EditorSection>

          <!-- Section B: 參考文字 -->
          <EditorSection
            section-id="B"
            title="參考文字&模組工具評估結果"
            :collapsible="computedFeatures.collapsibleSections"
            v-model:expanded="expandedSections.sectionB"
          >
            <RichTextEditor
              v-model="formData.referenceText"
              :readonly="computedFeatures.readonly"
              :show-html-info="false"
              placeholder="請輸入參考文字..."
            />
          </EditorSection>

          <!-- Section C: 風險事件 -->
          <EditorRiskEventSection
            v-model:choice="formData.hasRiskEvent"
            v-model:description="formData.riskEventDescription"
            :readonly="computedFeatures.readonly"
          />

          <!-- Section D: 對應作為 -->
          <EditorCounterActionSection
            v-model:choice="formData.hasCounterAction"
            v-model:description="formData.counterActionDescription"
            v-model:cost="formData.counterActionCost"
            :readonly="computedFeatures.readonly"
          />

          <!-- 量表按鈕區 -->
          <EditorScaleBar
            :mode="features.scaleMode"
            :show-button="features.showScaleButton"
            @open-scale="openScaleModal"
          />

          <!-- Section E & F: 風險與機會 (Grid Layout) -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <RiskSection
              v-model:e1-risk-description="formData.risk.description"
              v-model:e2-risk-probability="formData.risk.probability"
              v-model:e2-risk-impact="formData.risk.impactLevel"
              v-model:e2-risk-calculation="formData.risk.calculation"
              :probability-options="probabilityScaleOptions"
              :impact-options="impactScaleOptions"
              :disabled="computedFeatures.readonly"
              :info-text="formData.hoverTexts?.E1"
              @edit-info="editHoverText"
            />

            <OpportunitySection
              v-model:f1-opportunity-description="formData.opportunity.description"
              v-model:f2-opportunity-probability="formData.opportunity.probability"
              v-model:f2-opportunity-impact="formData.opportunity.impactLevel"
              v-model:f2-opportunity-calculation="formData.opportunity.calculation"
              :probability-options="probabilityScaleOptions"
              :impact-options="impactScaleOptions"
              :disabled="computedFeatures.readonly"
              :info-text="formData.hoverTexts?.F1"
              @edit-info="editHoverText"
            />
          </div>

          <!-- Context Text for External Impact -->
          <div class="text-white px-6 py-3 rounded-2xl" style="background-color: #059669;">
            <span class="font-bold text-white text-xl">
              請依上述公司營點之進行或風險會環境,結合評估公司之營運程此議題可能造成的「對外」衝擊(對外部環境、環境、人群(含含責人補)之正/負面影響)
            </span>
          </div>

          <!-- Section G & H: 對外衝擊 (Grid Layout) -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <NegativeImpactSection
              v-model:g1-negative-impact-level="formData.negativeImpact.level"
              v-model:g1-negative-impact-description="formData.negativeImpact.description"
              :disabled="computedFeatures.readonly"
              :info-text="formData.hoverTexts?.G1"
              @edit-info="editHoverText"
            />

            <PositiveImpactSection
              v-model:h1-positive-impact-level="formData.positiveImpact.level"
              v-model:h1-positive-impact-description="formData.positiveImpact.description"
              :disabled="computedFeatures.readonly"
              :info-text="formData.hoverTexts?.H1"
              @edit-info="editHoverText"
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
      v-if="showScaleModal && (features.scaleMode === 'editor' && scaleViewMode === 'editor')"
      v-model="showScaleModal"
      title="量表編輯"
      :is-loading="isLoadingScales"
      :mode="features.scaleMode"
      :show-view-toggle="editorMode === 'question' || editorMode === 'template'"
      @toggle-view="scaleViewMode = 'viewer'"
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
      v-if="showScaleModal && (features.scaleMode !== 'editor' || scaleViewMode === 'viewer')"
      v-model="showScaleModal"
      :loading="isLoadingScales"
      :default-compact-mode="features.scaleMode === 'viewer-compact'"
      :probability-scale-columns="probabilityScaleColumns"
      :probability-scale-rows="probabilityScaleRows"
      :description-text="descriptionText"
      :show-description-text="showDescriptionText"
      :impact-scale-columns="impactScaleColumns"
      :impact-scale-rows="impactScaleRows"
      :show-edit-toggle="editorMode === 'question' || editorMode === 'template'"
      @toggle-edit="scaleViewMode = 'editor'"
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
import RiskSection from '~/components/RiskAssessment/RiskSection.vue'
import OpportunitySection from '~/components/RiskAssessment/OpportunitySection.vue'
import NegativeImpactSection from '~/components/RiskAssessment/NegativeImpactSection.vue'
import PositiveImpactSection from '~/components/RiskAssessment/PositiveImpactSection.vue'
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
const isPreviewMode = ref(false)
const scaleViewMode = ref('editor') // 'editor' | 'viewer'

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
    const categoryName = questionData.value?.category_name || '未分類'
    const topicName = questionData.value?.topic_name || ''
    const factorName = questionData.value?.factor_name || ''

    let subtitle = `類別：${categoryName}`
    if (topicName) subtitle += ` > ${topicName}`
    if (factorName) subtitle += ` > ${factorName}`
    subtitle += ' | 編輯完整的ESG評估題目內容'

    return subtitle
  }
  return features.value.pageSubtitle
})

// 顯示風險架構資訊卡片 (所有模式都顯示，只要有資料)
const showStructureInfo = computed(() => {
  return !!questionData.value && (
    questionData.value.category_name ||
    questionData.value.topic_name ||
    questionData.value.factor_name
  )
})

// 計算風險架構資訊卡片的 grid class (根據有多少層級動態調整)
const getStructureGridClass = computed(() => {
  if (!questionData.value) return 'grid grid-cols-1 gap-5 mb-4'

  const hasCategory = !!questionData.value.category_name
  const hasTopic = !!questionData.value.topic_name
  const hasFactor = !!questionData.value.factor_name

  const count = [hasCategory, hasTopic, hasFactor].filter(Boolean).length

  if (count === 3) {
    // 三個都有：類別、主題、因子 (舊版樣式: 1fr 1fr 2fr)
    return 'grid grid-cols-[1fr_1fr_2fr] gap-5 mb-4'
  } else if (count === 2) {
    // 兩個：通常是類別和因子
    return 'grid grid-cols-2 gap-5 mb-4'
  } else {
    // 一個
    return 'grid grid-cols-1 gap-5 mb-4'
  }
})

// 計算動態 features (支援預覽模式切換)
const computedFeatures = computed(() => {
  if (isPreviewMode.value) {
    return {
      ...features.value,
      readonly: true,
      showPreviewButton: true,
      previewButtonText: '結束預覽',
      showSaveButton: false,
      showTestDataButton: false,
      showBackButton: false,
      collapsibleSections: true
    }
  }
  return features.value
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
  const from = route.query.from // 取得 preview 來源

  // 對於 question 模式，傳遞 company_id 和 assessment_id
  const companyId = questionData.value?.company_id || null
  const assessmentId = questionData.value?.assessment_id || id

  const backPath = getBackPath(id, contentId, from, companyId, assessmentId)
  router.push(backPath)
}

/**
 * 切換預覽模式 (原地預覽)
 */
const handlePreview = () => {
  if (editorMode === 'preview') return

  // 切換預覽狀態
  isPreviewMode.value = !isPreviewMode.value

  if (isPreviewMode.value) {
    // 進入預覽模式: Section A 展開, Section B 收折
    expandedSections.value.sectionA = true
    expandedSections.value.sectionB = false
    console.log('[Editor] 進入預覽模式')
  } else {
    // 結束預覽模式: 全部展開
    expandedSections.value.sectionA = true
    expandedSections.value.sectionB = true
    console.log('[Editor] 結束預覽模式')
  }
}

/**
 * 儲存題目
 */
const handleSave = async () => {
  if (isSaving.value || editorMode === 'preview') return

  isSaving.value = true

  const { executeWithNotification } = useNotification()

  try {
    // 轉換表單資料為後端格式
    const backendData = formToBackend(formData.value, questionData.value)

    console.log('[Editor] Saving data:', backendData)

    // 使用 executeWithNotification 處理儲存，自動顯示載入中和成功/失敗提示
    await executeWithNotification(
      async () => await saveQuestion(backendData, questionData.value),
      {
        loadingMessage: '儲存中...請稍候',
        successMessage: '題目已成功儲存',
        errorMessage: '儲存失敗，請稍後再試'
      }
    )

    // 返回列表頁
    setTimeout(() => {
      handleBack()
    }, 1500)
  } catch (error) {
    console.error('[Editor] Save failed:', error)
    // 錯誤已經由 executeWithNotification 處理
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

  const { $notify } = useNuxtApp()
  $notify.toast.success('測試資料已填入')
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

  const { $notify } = useNuxtApp()
  $notify.toast.success('提示文字已更新')
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
 * 開啟量表 Modal
 */
const openScaleModal = () => {
  scaleViewMode.value = 'editor' // 重置為編輯模式
  showScaleModal.value = true
}

/**
 * 儲存量表資料
 */
const handleScaleSave = async () => {
  const { executeWithNotification } = useNotification()

  if (editorMode === 'preview') {
    // preview 模式不允許編輯量表
    const { $notify } = useNuxtApp()
    $notify.info('提示', '預覽模式下量表為唯讀')
    return
  }

  try {
    await executeWithNotification(
      async () => {
        const scaleData = prepareScaleDataForSubmission()

        let probabilityUrl, impactUrl

        if (editorMode === 'question') {
          // Question 模式：使用 assessment API
          probabilityUrl = `/api/v1/question-management/assessment/${id}/scales/probability`
          impactUrl = `/api/v1/question-management/assessment/${id}/scales/impact`
        } else {
          // Template 模式：使用 template API
          probabilityUrl = `/templates/${id}/scales/probability`
          impactUrl = `/templates/${id}/scales/impact`
        }

        // 儲存可能性量表
        if (editorMode === 'question') {
          await $fetch(probabilityUrl, {
            method: 'POST',
            body: {
              columns: scaleData.probability_scale.columns,
              rows: scaleData.probability_scale.rows,
              descriptionText: scaleData.probability_scale.description_text,
              showDescriptionText: scaleData.probability_scale.show_description,
              selectedDisplayColumn: scaleData.probability_scale.selected_display_column
            }
          })
        } else {
          await apiClient.request(probabilityUrl, {
            method: 'POST',
            body: {
              columns: scaleData.probability_scale.columns,
              rows: scaleData.probability_scale.rows,
              descriptionText: scaleData.probability_scale.description_text,
              showDescriptionText: scaleData.probability_scale.show_description,
              selectedDisplayColumn: scaleData.probability_scale.selected_display_column
            }
          })
        }

        // 儲存財務衝擊量表
        if (editorMode === 'question') {
          await $fetch(impactUrl, {
            method: 'POST',
            body: {
              columns: scaleData.impact_scale.columns,
              rows: scaleData.impact_scale.rows,
              descriptionText: scaleData.impact_scale.description_text,
              showDescriptionText: scaleData.impact_scale.show_description,
              selectedDisplayColumn: scaleData.impact_scale.selected_display_column
            }
          })
        } else {
          await apiClient.request(impactUrl, {
            method: 'POST',
            body: {
              columns: scaleData.impact_scale.columns,
              rows: scaleData.impact_scale.rows,
              descriptionText: scaleData.impact_scale.description_text,
              showDescriptionText: scaleData.impact_scale.show_description,
              selectedDisplayColumn: scaleData.impact_scale.selected_display_column
            }
          })
        }

        showScaleModal.value = false
      },
      {
        loadingMessage: '儲存量表中...請稍候',
        successMessage: '量表已成功儲存',
        errorMessage: '量表儲存失敗，請稍後再試'
      }
    )
  } catch (error) {
    console.error('[Editor] Scale save failed:', error)
    // 錯誤已經由 executeWithNotification 處理
  }
}
</script>

<style scoped>
/* 風險架構資訊卡片樣式 */
.risk-factor-bg {
  background-color: #059669; /* Tailwind green-600 */
}

.dark .risk-factor-bg {
  background-color: #047857; /* Tailwind green-700 for dark mode */
}
</style>
