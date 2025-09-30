<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-8">

      <!-- Page Title Section -->
      <div class="mb-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">使用者表單預覽</h1>
            <p class="mt-1 text-base text-gray-500 dark:text-gray-400">
              {{ questionData?.description || '題目預覽' }} - 檢視使用者將看到的表單內容
            </p>
          </div>
          <div class="flex space-x-3">
            <button
              @click="goBackToEdit"
              class="px-4 py-2 bg-green-600 text-white rounded-2xl hover:bg-green-700 transition-colors duration-200"
            >
              結束預覽
            </button>
          </div>
        </div>
      </div>

      <!-- User-Facing Preview Content -->
      <div class="space-y-6">

        <!-- Section A: 風險因子議題描述 (可收折) -->
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
          <button
            @click="toggleSection('sectionA')"
            class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50"
          >
            <div class="flex items-center space-x-3">
              <span class="font_title">風險因子議題描述</span>
              <span class="bg-gray-200 text-gray-800 px-2 py-1 rounded text-base font-medium">A</span>
            </div>
            <ChevronUpIcon v-if="expandedSections.sectionA" class="w-5 h-5 text-gray-400" />
            <ChevronDownIcon v-else class="w-5 h-5 text-gray-400" />
          </button>
          <div v-show="expandedSections.sectionA" class="px-6 pb-6">
            <div class="mb-3">
              <div class="border-t-2 border-gray-300 mb-4 rounded-full"></div>
            </div>
            <!-- 顯示富文本內容，HTML格式 -->
            <div class="prose max-w-none text-gray-700" v-html="previewData.riskFactorDescription || '請填寫風險因子議題描述...'">
            </div>
          </div>
        </div>

        <!-- Section B: 參考文字&模組工具評估結果 (可收折) -->
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
          <button
            @click="toggleSection('sectionB')"
            class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50"
          >
            <div class="flex items-center space-x-3">
              <span class="font_title">參考文字&模組工具評估結果</span>
              <span class="bg-gray-200 text-gray-800 px-2 py-1 rounded text-base font-medium">B</span>
            </div>
            <ChevronUpIcon v-if="expandedSections.sectionB" class="w-5 h-5 text-gray-400" />
            <ChevronDownIcon v-else class="w-5 h-5 text-gray-400" />
          </button>
          <div v-show="expandedSections.sectionB" class="px-6 pb-6">
            <div class="mb-3">
              <div class="border-t-2 border-gray-300 mb-4 rounded-full"></div>
            </div>
            <!-- 顯示富文本內容，HTML格式 -->
            <div class="prose max-w-none text-gray-700" v-html="previewData.referenceText || '請填寫參考文字與評估結果...'">
            </div>
          </div>
        </div>

        <!-- Section C: 公司報導年度是否有發生實際風險/負面衝擊事件 -->
        <div class="bg-white border border-gray-200 p-6 rounded-2xl">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
              <span class="font-bold text-gray-900 text-xl">公司報導年度是否有發生實際風險/負面衝擊事件</span>
              <span class="bg-gray-200 text-gray-800 px-2 py-1 rounded text-base font-medium">C</span>
            </div>
          </div>
          <div class="space-y-4">
            <!-- 互動式 Radio 選項 -->
            <div class="grid grid-cols-2 gap-6">
              <label class="radio-card-option radio-card-no" :class="{ 'selected': riskEventChoice === 'yes' }">
                <input
                  v-model="riskEventChoice"
                  type="radio"
                  value="yes"
                  name="riskEvent"
                  class="sr-only"
                />
                <div class="radio-card-content">
                  <div class="radio-card-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                  </div>
                  <span class="radio-card-text">是</span>
                </div>
              </label>
              <label class="radio-card-option radio-card-yes" :class="{ 'selected': riskEventChoice === 'no' }">
                <input
                  v-model="riskEventChoice"
                  type="radio"
                  value="no"
                  name="riskEvent"
                  class="sr-only"
                />
                <div class="radio-card-content">
                  <div class="radio-card-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                  </div>
                  <span class="radio-card-text">否</span>
                </div>
              </label>
            </div>
            <div>
              <label class="text-gray-600 mt-6 mb-1">*請描述</label>
              <textarea
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-2xl bg-gray-50 text-gray-600 cursor-not-allowed"
                :placeholder="previewData.riskEventDescription || '請輸入該題目描述文字'"
                readonly
                disabled
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Section D: 公司報導年度是否有相關對應作為 -->
        <div class="bg-white border border-gray-200 p-6 rounded-2xl">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
              <span class="font-bold text-gray-900 text-xl">公司報導年度是否有相關對應作為</span>
              <span class="bg-gray-200 text-gray-800 px-2 py-1 rounded text-base font-medium">D</span>
            </div>
          </div>
          <div class="space-y-4">
            <!-- 互動式 Radio 選項 -->
            <div class="grid grid-cols-2 gap-6">
              <label class="radio-card-option radio-card-no" :class="{ 'selected': counterActionChoice === 'yes' }">
                <input
                  v-model="counterActionChoice"
                  type="radio"
                  value="yes"
                  name="counterAction"
                  class="sr-only"
                />
                <div class="radio-card-content">
                  <div class="radio-card-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                  </div>
                  <span class="radio-card-text">是</span>
                </div>
              </label>
              <label class="radio-card-option radio-card-yes" :class="{ 'selected': counterActionChoice === 'no' }">
                <input
                  v-model="counterActionChoice"
                  type="radio"
                  value="no"
                  name="counterAction"
                  class="sr-only"
                />
                <div class="radio-card-content">
                  <div class="radio-card-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                  </div>
                  <span class="radio-card-text">否</span>
                </div>
              </label>
            </div>
            <div>
              <label class="text-gray-600 mt-6 mb-1">*請描述</label>
              <textarea
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-2xl bg-gray-50 text-gray-600 cursor-not-allowed"
                :placeholder="previewData.counterActionDescription || '請輸入對應措施描述'"
                readonly
                disabled
              ></textarea>
            </div>
            <div>
              <label class="text-gray-600 mt-6 mb-1">*上述對策費用</label>
              <textarea
                rows="2"
                class="w-full px-3 py-2 border border-gray-300 rounded-2xl bg-gray-50 text-gray-600 cursor-not-allowed"
                :placeholder="previewData.counterActionCost || '請輸入對策費用估算'"
                readonly
                disabled
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Green Context Bar -->
        <div class="bg-green-600 text-white px-6 py-3 rounded-2xl">
          <span class="font-bold text-white text-xl">請依上述資訊，整點公司致富相關之風險情況，並評估未來永續在各風險/機會情境</span>
        </div>

        <!-- Sections E, F, G, H in Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Section E-1: 相關風險 -->
          <div class="bg-white border border-gray-200 p-6 rounded-2xl">
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center space-x-3">
                <span class="font-bold text-gray-900 text-xl">相關風險</span>
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-base font-medium flex items-center">E-1</span>
                <div
                  class="relative group w-5 h-5 ml-2 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-serif font-bold cursor-pointer hover:scale-110 transition-transform duration-200"
                >
                  <span class="italic">i</span>
                  <!-- Hover Tooltip -->
                  <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-800 text-white text-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-20">
                    {{ hoverTexts.E1 }}
                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-800"></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="space-y-4">
              <p class="text-base text-gray-600">公司未來潛在相關風險營清說明，未來潛在風險（收入減少）、費用增加於損益</p>
              <div>
                <label class="text-gray-600 mt-6 mb-1">風險描述</label>
                <textarea
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-2xl bg-gray-50 text-gray-600 cursor-not-allowed"
                  :placeholder="previewData.riskDescription || '請輸入風險描述'"
                  readonly
                  disabled
                ></textarea>
              </div>
              <div class="border border-gray-300 rounded-2xl p-4 space-y-3">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-base text-gray-600 mb-1">*風險發生可能性</label>
                    <select
                      v-model="riskProbability"
                      class="w-full border border-gray-300 rounded-2xl px-3 py-2 text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
                    >
                      <option value="" disabled>請選擇可能性等級</option>
                      <option v-for="option in probabilityOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                      </option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-base text-gray-600 mb-1">*風險發生衝擊程度</label>
                    <select
                      v-model="riskImpact"
                      class="w-full border border-gray-300 rounded-2xl px-3 py-2 text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
                    >
                      <option value="" disabled>請選擇衝擊程度</option>
                      <option v-for="option in impactOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                      </option>
                    </select>
                  </div>
                </div>
                <div>
                  <label class="flex items-center text-base text-gray-600 mb-1">*計算說明</label>
                  <textarea
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-2xl bg-gray-50 text-gray-600 cursor-not-allowed text-base"
                    :placeholder="previewData.riskCalculation || '請輸入風險計算說明'"
                    readonly
                    disabled
                  ></textarea>
                </div>
              </div>
            </div>
          </div>

          <!-- Section F-1: 相關機會 -->
          <div class="bg-white border border-gray-200 p-6 rounded-2xl">
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center space-x-3">
                <span class="font-bold text-gray-900 text-xl">相關機會</span>
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-base font-medium flex items-center">F-1</span>
                <div
                  class="relative group w-5 h-5 ml-2 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-serif font-bold cursor-pointer hover:scale-110 transition-transform duration-200"
                >
                  <span class="italic">i</span>
                  <!-- Hover Tooltip -->
                  <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-800 text-white text-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-20">
                    {{ hoverTexts.F1 }}
                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-800"></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="space-y-4">
              <p class="text-base text-gray-600">公司未來潛在相關機會營清說明，未來潛在機會（收入增加）、費用減少於收益等不會定</p>
              <div>
                <label class="text-gray-600 mt-6 mb-1">機會描述</label>
                <textarea
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-2xl bg-gray-50 text-gray-600 cursor-not-allowed"
                  :placeholder="previewData.opportunityDescription || '請輸入機會描述'"
                  readonly
                  disabled
                ></textarea>
              </div>
              <div class="border border-gray-300 rounded-2xl p-4 space-y-3">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-base text-gray-600 mb-1">*機會發生可能性</label>
                    <select
                      v-model="opportunityProbability"
                      class="w-full border border-gray-300 rounded-2xl px-3 py-2 text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
                    >
                      <option value="" disabled>請選擇可能性等級</option>
                      <option v-for="option in probabilityOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                      </option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-base text-gray-600 mb-1">*機會發生衝擊程度</label>
                    <select
                      v-model="opportunityImpact"
                      class="w-full border border-gray-300 rounded-2xl px-3 py-2 text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
                    >
                      <option value="" disabled>請選擇衝擊程度</option>
                      <option v-for="option in impactOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                      </option>
                    </select>
                  </div>
                </div>
                <div>
                  <label class="flex items-center text-base text-gray-600 mb-1">*計算說明</label>
                  <textarea
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-2xl bg-gray-50 text-gray-600 cursor-not-allowed text-base"
                    :placeholder="previewData.opportunityCalculation || '請輸入機會計算說明'"
                    readonly
                    disabled
                  ></textarea>
                </div>
              </div>
            </div>
          </div>

          <!-- Section G-1: 對外負面衝擊 -->
          <div class="bg-white border border-gray-200 p-6 rounded-2xl">
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center space-x-3">
                <span class="font-bold text-gray-900 text-xl">對外負面衝擊</span>
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-base font-medium flex items-center">G-1</span>
                <div
                  class="relative group w-5 h-5 ml-2 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-serif font-bold cursor-pointer hover:scale-110 transition-transform duration-200"
                >
                  <span class="italic">i</span>
                  <!-- Hover Tooltip -->
                  <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-800 text-white text-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-20">
                    {{ hoverTexts.G1 }}
                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-800"></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="space-y-4">
              <div>
                <label class="block text-base text-gray-600 mb-1">負面衝擊程度</label>
                <select
                  v-model="negativeImpactLevel"
                  class="w-full border border-gray-300 rounded-2xl px-3 py-2 text-center font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
                >
                  <option value="" disabled>請選擇衝擊程度</option>
                  <option v-for="option in impactLevelOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                  </option>
                </select>
              </div>
              <div>
                <label class="block text-base text-gray-600 mb-1">評分說明</label>
                <textarea
                  rows="4"
                  class="w-full px-3 py-2 border border-gray-300 rounded-2xl bg-gray-50 text-gray-600 cursor-not-allowed text-base"
                  :placeholder="previewData.negativeImpactDescription || '請輸入負面衝擊評分說明'"
                  readonly
                  disabled
                ></textarea>
              </div>
            </div>
          </div>

          <!-- Section H-1: 對外正面影響 -->
          <div class="bg-white border border-gray-200 p-6 rounded-2xl">
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center space-x-3">
                <span class="font-bold text-gray-900 text-xl">對外正面影響</span>
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-base font-medium flex items-center">H-1</span>
                <div
                  class="relative group w-5 h-5 ml-2 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-serif font-bold cursor-pointer hover:scale-110 transition-transform duration-200"
                >
                  <span class="italic">i</span>
                  <!-- Hover Tooltip -->
                  <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-800 text-white text-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-20">
                    {{ hoverTexts.H1 }}
                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-800"></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="space-y-4">
              <div>
                <label class="block text-base text-gray-600 mb-1">正面影響程度</label>
                <select
                  v-model="positiveImpactLevel"
                  class="w-full border border-gray-300 rounded-2xl px-3 py-2 text-center font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
                >
                  <option value="" disabled>請選擇影響程度</option>
                  <option v-for="option in impactLevelOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                  </option>
                </select>
              </div>
              <div>
                <label class="block text-base text-gray-600 mb-1">評分說明</label>
                <textarea
                  rows="4"
                  class="w-full px-3 py-2 border border-gray-300 rounded-2xl bg-gray-50 text-gray-600 cursor-not-allowed text-base"
                  :placeholder="previewData.positiveImpactDescription || '請輸入正面影響評分說明'"
                  readonly
                  disabled
                ></textarea>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import {
  ChevronUpIcon,
  ChevronDownIcon
} from '@heroicons/vue/24/outline'

definePageMeta({
  middleware: 'auth'
})

const route = useRoute()
const router = useRouter()

// Extract IDs from route params
const routeParams = route.params
const templateId = parseInt(routeParams.templateId)
const contentId = parseInt(routeParams.contentId)

// Use stores
const templatesStore = useTemplatesStore()

usePageTitle('表單預覽')

// Expandable sections state
const expandedSections = ref({
  sectionA: true,
  sectionB: false
})

// Toggle section function
const toggleSection = (sectionKey) => {
  expandedSections.value[sectionKey] = !expandedSections.value[sectionKey]
}

// Get preview data from route query or default values
const previewData = computed(() => {
  // Try to get data from route query first (passed from edit page)
  const queryData = route.query

  // If no query data, use question data or defaults
  const defaultData = questionData.value || {}

  return {
    riskFactorDescription: queryData.riskFactorDescription || defaultData.description || defaultData.a_content || defaultData.risk_factor_description || '企業的營運往往高度依賴自然資源，如水資源、石油、天然氣、動植物資源、海洋魚類供給、土壤、植木等、隨著氣候變遷、生態退化與資源稀缺問題日益嚴峻，若企業未能妥善管理資源使用環境衝擊，可能面臨供應中斷、成本上升與合規壓力等風險。同時，過度依賴自然資源或可生產活動衝擊，亦可能引發社會關注與質疑指稱。',
    referenceText: queryData.referenceText || defaultData.b_content || defaultData.reference_text || '🔵去年報告書文字或第三方背景資料整理：<br>1. 台灣與生產據點的用水，皆不是韌水稀缺，對韌地區水資源缺乏威脅低，政府10個高溫期造成為高風險中高風險，並無缺點墊常落高風險之中<br>2. 政府推薦有七個生產據點位於水稀缺聯盟審查風險之地區，對當地自然環境具一定程度之衝擊',
    riskEventDescription: queryData.riskEventDescription || defaultData.c_placeholder || defaultData.risk_event_description || '台灣與生產據點的用水，皆不是韌水稀缺，沒有韌點韌點水用微低的風險',
    counterActionDescription: queryData.counterActionDescription || defaultData.d_placeholder_1 || defaultData.counter_action_description || '導入TNFD請專業，進一步了解致利納於自然資源保護性',
    counterActionCost: queryData.counterActionCost || defaultData.d_placeholder_2 || defaultData.counter_action_cost || '顧問費用約80萬(可初估)',
    riskDescription: queryData.riskDescription || defaultData.e1_placeholder_1 || defaultData.risk_description || '致使集團對外資源、石油、天然氣、動植物資源、海洋魚類供給、土壤、植木等依賴不高，威脅相關風險',
    riskCalculation: queryData.riskCalculation || defaultData.e1_placeholder_2 || defaultData.risk_calculation || '致使相關風險、衝擊程度低',
    opportunityDescription: queryData.opportunityDescription || defaultData.opportunity_description || '集團持續導入資源效率管理、循環經濟及生物保護措施，以降低風險並最終可能導致業務擴張，可帶來更多客戶期待',
    opportunityCalculation: queryData.opportunityCalculation || defaultData.opportunity_calculation || '因持續符合客戶ESG要求，可望獲加公司營收機會以帶年增加5%計算，約可帶來30億的正面效益',
    negativeImpactDescription: queryData.negativeImpactDescription || defaultData.negative_impact_description || '致使集團為士範生產據點位於水稀缺響景象風險政策一定程度之衝擊，但透過確實四有著含高地調評湖法，且覺讓落實未來高污染活道，所以負面衝擊不至於太高',
    positiveImpactDescription: queryData.positiveImpactDescription || defaultData.positive_impact_description || '50年-60年，並且目前已導入TNFD專業，對外部自然環境應常來正面影響',
    // Radio button default values
    riskEventChoice: queryData.riskEventChoice || defaultData.c_choice || defaultData.risk_event_choice || 'no',
    counterActionChoice: queryData.counterActionChoice || defaultData.d_choice || defaultData.counter_action_choice || 'yes'
  }
})

// Interactive form choices - initialize with preview data
const riskEventChoice = ref('')
const counterActionChoice = ref('')

// Dropdown selections
const riskProbability = ref('')
const riskImpact = ref('')
const opportunityProbability = ref('')
const opportunityImpact = ref('')
const negativeImpactLevel = ref('')
const positiveImpactLevel = ref('')

// Dropdown options
const probabilityOptions = [
  { value: 'very-low', label: '極低 (1-5%)' },
  { value: 'low', label: '低 (6-25%)' },
  { value: 'medium', label: '中等 (26-50%)' },
  { value: 'high', label: '高 (51-75%)' },
  { value: 'very-high', label: '極高 (76-100%)' }
]

const impactOptions = [
  { value: 'very-low', label: '極低影響' },
  { value: 'low', label: '低影響' },
  { value: 'medium', label: '中等影響' },
  { value: 'high', label: '高影響' },
  { value: 'very-high', label: '極高影響' }
]

const impactLevelOptions = [
  { value: 'level-1', label: '等級1 - 極輕微' },
  { value: 'level-2', label: '等級2 - 輕微' },
  { value: 'level-3', label: '等級3 - 中等' },
  { value: 'level-4', label: '等級4 - 嚴重' },
  { value: 'level-5', label: '等級5 - 極嚴重' }
]

// Get question data
const questionData = computed(() => {
  const contentList = templatesStore.getTemplateContent(templateId)
  return contentList.value?.find(item => item.id === contentId)
})

// Hover text data from database - Initialize with default values for immediate display
const hoverTexts = ref({
  E1: '相關風險說明：企業面臨的風險評估相關資訊',
  F1: '相關機會說明：企業可能的機會評估相關資訊',
  G1: '對外負面衝擊說明：企業對外部環境可能造成的負面影響',
  H1: '對外正面影響說明：企業對外部環境可能產生的正面影響'
})

// Fetch hover text data - Update existing values from API
const fetchHoverTexts = async () => {
  try {
    const response = await $fetch(`/api/v1/risk-assessment/templates/${templateId}/contents/${contentId}`)
    if (response && response.data) {
      // Update existing values with API data, keeping defaults if API data is empty
      hoverTexts.value.E1 = response.data.e1_info || hoverTexts.value.E1
      hoverTexts.value.F1 = response.data.f1_info || hoverTexts.value.F1
      hoverTexts.value.G1 = response.data.g1_info || hoverTexts.value.G1
      hoverTexts.value.H1 = response.data.h1_info || hoverTexts.value.H1
    }
  } catch (error) {
    console.error('Failed to fetch hover texts:', error)
    // Default values are already set, so no action needed on error
  }
}

// Methods
const goBackToEdit = () => {
  router.push(`/admin/risk-assessment/templates/edit/${templateId}-${contentId}`)
}

// Lifecycle
onMounted(async () => {
  console.log('Template preview page mounted for template:', templateId, 'content:', contentId)

  // Initialize store if needed
  if (!templatesStore.templates || templatesStore.templates.length === 0) {
    await templatesStore.initialize()
  }

  // Ensure template content is loaded
  const existingContent = templatesStore.getTemplateContent(templateId)
  if (!existingContent.value || existingContent.value.length === 0) {
    try {
      await templatesStore.fetchTemplateContent(templateId)
    } catch (error) {
      console.error('Failed to fetch template content:', error)
    }
  }

  // Fetch hover text data
  await fetchHoverTexts()

  // Initialize radio button values with default values from previewData
  await nextTick(() => {
    riskEventChoice.value = previewData.value.riskEventChoice
    counterActionChoice.value = previewData.value.counterActionChoice
  })
})
</script>

<style scoped>
.font_title {
  @apply font-bold text-gray-900 text-xl;
}

.font_title_white {
  @apply font-bold text-white text-xl;
}

/* Custom styling for the ESG form */
.bg-green-600 {
  background-color: #059669;
}

/* 精緻的卡片式 Radio 樣式 */
.radio-card-option {
  @apply relative cursor-pointer block border rounded-2xl px-4 py-3 transition-all duration-200 ease-in-out;
  @apply bg-white border-gray-300 hover:shadow-md;
}

.radio-card-option.radio-card-yes {
  @apply hover:border-red-400;
}

.radio-card-option.radio-card-no {
  @apply hover:border-green-400;
}

.radio-card-content {
  @apply flex items-center justify-center space-x-3;
}

.radio-card-icon {
  @apply w-7 h-7 flex items-center justify-center rounded-full;
  @apply transition-all duration-200 border;
  @apply bg-gray-100 border-gray-300 text-gray-500;
}

.radio-card-text {
  @apply text-lg font-semibold text-gray-700;
}

/* 選中狀態樣式 */
.radio-card-option.selected.radio-card-yes {
  @apply border-red-500 bg-red-50;
}

.radio-card-option.selected.radio-card-no {
  @apply border-green-500 bg-green-50;
}

.radio-card-option.selected .radio-card-icon {
  @apply bg-white border-current;
}

.radio-card-option.selected.radio-card-yes .radio-card-icon {
  @apply text-red-600;
}

.radio-card-option.selected.radio-card-no .radio-card-icon {
  @apply text-green-600;
}

.radio-card-option.selected .radio-card-text {
  @apply text-current;
}

.radio-card-option.selected.radio-card-yes .radio-card-text {
  @apply text-red-700;
}

.radio-card-option.selected.radio-card-no .radio-card-text {
  @apply text-green-700;
}

/* 響應式設計 */
@media (max-width: 640px) {
  .radio-card-content {
    @apply flex-col space-x-0 space-y-2;
  }

  .radio-card-text {
    @apply text-base;
  }
}
</style>