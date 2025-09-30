<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-8">

      <!-- Page Title Section -->
      <div class="mb-6">
        <div class="flex items-start justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">風險評估作答</h1>
            <p class="mt-1 text-base text-gray-500 dark:text-gray-400">
              {{ route.query.title || '題目作答' }} - {{ isViewMode ? route.query.description || '查看填寫內容' : '請完整填寫以下表單內容' }}
            </p>
          </div>
          <!-- Test Data Button (conditionally shown) -->
          <div v-if="showTestDataButton && !isViewMode" class="flex-shrink-0">
            <button
              @click="fillTestData"
              :disabled="isFillingTestData"
              class="inline-flex items-center px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 shadow-sm"
              title="填入隨機測試資料"
            >
              <svg v-if="!isFillingTestData" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
              </svg>
              <svg v-else class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
              {{ isFillingTestData ? '填入中...' : '填入測試資料' }}
            </button>
          </div>
        </div>
      </div>

      <!-- User-Facing Content -->
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
            <div class="prose max-w-none text-gray-700" v-html="formData.riskFactorDescription || '請填寫風險因子議題描述...'">
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
            <div class="prose max-w-none text-gray-700" v-html="formData.referenceText || '請填寫參考文字與評估結果...'">
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
              <label class="radio-card-option radio-card-no" :class="{ 'selected': formData.riskEventChoice === 'yes' }">
                <input
                  v-model="formData.riskEventChoice"
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
              <label class="radio-card-option radio-card-yes" :class="{ 'selected': formData.riskEventChoice === 'no' }">
                <input
                  v-model="formData.riskEventChoice"
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
                v-model="formData.riskEventDescription"
                rows="3"
                :disabled="isViewMode"
                class="w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                placeholder="請輸入該題目描述文字"
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
              <label class="radio-card-option radio-card-no" :class="{ 'selected': formData.counterActionChoice === 'yes' }">
                <input
                  v-model="formData.counterActionChoice"
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
              <label class="radio-card-option radio-card-yes" :class="{ 'selected': formData.counterActionChoice === 'no' }">
                <input
                  v-model="formData.counterActionChoice"
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
                v-model="formData.counterActionDescription"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="請輸入對應措施描述"
              ></textarea>
            </div>
            <div>
              <label class="text-gray-600 mt-6 mb-1">*上述對策費用</label>
              <textarea
                v-model="formData.counterActionCost"
                rows="2"
                class="w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="請輸入對策費用估算"
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
                  v-model="formData.riskDescription"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="請輸入風險描述"
                ></textarea>
              </div>
              <div class="border border-gray-300 rounded-2xl p-4 space-y-3">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-base text-gray-600 mb-1">*風險發生可能性</label>
                    <select
                      v-model="formData.riskProbability"
                      :disabled="isViewMode"
                      class="w-full border border-gray-300 rounded-2xl px-3 py-2 text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white disabled:bg-gray-100 disabled:cursor-not-allowed"
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
                      v-model="formData.riskImpact"
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
                    v-model="formData.riskCalculation"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base"
                    placeholder="請輸入風險計算說明"
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
                  v-model="formData.opportunityDescription"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="請輸入機會描述"
                ></textarea>
              </div>
              <div class="border border-gray-300 rounded-2xl p-4 space-y-3">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-base text-gray-600 mb-1">*機會發生可能性</label>
                    <select
                      v-model="formData.opportunityProbability"
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
                      v-model="formData.opportunityImpact"
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
                    v-model="formData.opportunityCalculation"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base"
                    placeholder="請輸入機會計算說明"
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
                  v-model="formData.negativeImpactLevel"
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
                  v-model="formData.negativeImpactDescription"
                  rows="4"
                  class="w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base"
                  placeholder="請輸入負面衝擊評分說明"
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
                  v-model="formData.positiveImpactLevel"
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
                  v-model="formData.positiveImpactDescription"
                  rows="4"
                  class="w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base"
                  placeholder="請輸入正面影響評分說明"
                ></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- Bottom Action Buttons -->
        <div class="mt-8 flex justify-center space-x-4 pb-8">
          <button
            @click="goBack"
            class="px-6 py-3 bg-gray-600 text-white rounded-2xl hover:bg-gray-700 transition-colors duration-200 font-medium"
          >
            返回
          </button>
          <button
            v-if="!isViewMode"
            @click="saveAnswers"
            :disabled="isSaving"
            class="px-6 py-3 bg-green-600 text-white rounded-2xl hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 font-medium"
          >
            {{ isSaving ? '儲存中...' : '送出' }}
          </button>
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
const companyId = route.params.companyId
const questionId = parseInt(route.params.questionId)
const contentId = parseInt(route.params.contentId)

// Check if in view mode (read-only)
const isViewMode = ref(route.query.mode === 'view')
const viewUserId = ref(route.query.userId || null)

// Get external user data from pinia store
const externalUserStore = useExternalUserStore()

usePageTitle('風險評估作答')

// Expandable sections state
const expandedSections = ref({
  sectionA: true,
  sectionB: true
})

// Toggle section function
const toggleSection = (sectionKey) => {
  expandedSections.value[sectionKey] = !expandedSections.value[sectionKey]
}

// Form data - 可編輯的表單資料
const formData = ref({
  // A和B區域是顯示用的，不可編輯
  riskFactorDescription: '',
  referenceText: '',

  // C區域
  riskEventChoice: '',
  riskEventDescription: '',

  // D區域
  counterActionChoice: '',
  counterActionDescription: '',
  counterActionCost: '',

  // E區域
  riskDescription: '',
  riskProbability: '',
  riskImpact: '',
  riskCalculation: '',

  // F區域
  opportunityDescription: '',
  opportunityProbability: '',
  opportunityImpact: '',
  opportunityCalculation: '',

  // G區域
  negativeImpactLevel: '',
  negativeImpactDescription: '',

  // H區域
  positiveImpactLevel: '',
  positiveImpactDescription: ''
})

// Saving state
const isSaving = ref(false)

// Test data functionality
const showTestDataButton = ref(true) // 設為 true 顯示按鈕，false 隱藏按鈕
const isFillingTestData = ref(false)

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

// Hover text data
const hoverTexts = ref({
  E1: '相關風險說明：企業面臨的風險評估相關資訊',
  F1: '相關機會說明：企業可能的機會評估相關資訊',
  G1: '對外負面衝擊說明：企業對外部環境可能造成的負面影響',
  H1: '對外正面影響說明：企業對外部環境可能產生的正面影響'
})

// Load existing data
const loadQuestionData = async () => {
  try {
    console.log('Loading question data for answer form...')

    // Load question content to get A and B sections
    const contentResponse = await $fetch(`/api/v1/question-management/contents/${contentId}`)
    if (contentResponse.success && contentResponse.data) {
      const data = contentResponse.data
      formData.value.riskFactorDescription = data.a_content || ''
      formData.value.referenceText = data.b_content || ''
    }

    // Load existing answer if any
    const responseResponse = await $fetch(`/api/v1/question-management/assessment/${questionId}/responses`, {
      query: {
        content_id: contentId,
        answered_by: externalUserStore.userId
      }
    })

    if (responseResponse.success && responseResponse.data && responseResponse.data.length > 0) {
      const existingAnswer = responseResponse.data[0]

      // 使用分離的欄位資料
      if (existingAnswer.response_fields) {
        Object.assign(formData.value, existingAnswer.response_fields)
      }
    }
  } catch (error) {
    console.error('Error loading question data:', error)
  }
}

// 驗證必填欄位
const validateRequiredFields = () => {
  const errors = []

  // 檢查 E-1 相關風險區域的必填欄位
  if (!formData.value.riskProbability) {
    errors.push('E-1 相關風險：風險發生可能性')
  }
  if (!formData.value.riskImpact) {
    errors.push('E-1 相關風險：風險發生衝擊程度')
  }
  if (!formData.value.riskCalculation?.trim()) {
    errors.push('E-1 相關風險：計算說明')
  }

  // 檢查 F-1 相關機會區域的必填欄位
  if (!formData.value.opportunityProbability) {
    errors.push('F-1 相關機會：機會發生可能性')
  }
  if (!formData.value.opportunityImpact) {
    errors.push('F-1 相關機會：機會發生衝擊程度')
  }
  if (!formData.value.opportunityCalculation?.trim()) {
    errors.push('F-1 相關機會：計算說明')
  }

  return errors
}

// Load existing answers for view mode
const loadExistingAnswers = async () => {
  if (!isViewMode.value || !viewUserId.value) return

  try {
    console.log('Loading existing answers for user:', viewUserId.value)

    const response = await $fetch(`/api/v1/question-management/assessment/${questionId}/user/${viewUserId.value}/responses/${contentId}`)

    if (response.success && response.data && response.data.response_value) {
      // Load the saved answers into formData
      formData.value = {
        ...formData.value,
        ...response.data.response_value
      }
      console.log('Loaded existing answers:', formData.value)
    }
  } catch (error) {
    console.error('Error loading existing answers:', error)
  }
}

// Save answers
const saveAnswers = async () => {
  const { $notify } = useNuxtApp()

  // 先驗證必填欄位
  const validationErrors = validateRequiredFields()
  if (validationErrors.length > 0) {
    const errorList = validationErrors.map(error => `• ${error}`).join('<br>')
    await $notify.error(
      '請完成必填欄位',
      `以下欄位為必填：<br><br>${errorList}`
    )
    return
  }

  // 顯示系統提示對話框
  const confirmResult = await $notify.confirm(
    '確認送出答案',
    '您確定要送出這份評估答案嗎？',
    '送出',
    '取消'
  )

  if (!confirmResult.isConfirmed) {
    return
  }

  isSaving.value = true

  // 使用 SweetAlert 顯示載入中
  const loadingSwal = $notify.loading('儲存中...', '正在保存您的答案，請稍候')

  try {
    console.log('=== 準備送出答案 ===')
    console.log('表單資料:', formData.value)
    console.log('用戶ID:', externalUserStore.userId)
    console.log('外部ID:', externalUserStore.externalId)
    console.log('問題ID:', questionId)
    console.log('內容ID:', contentId)

    // 驗證必要參數
    if (!externalUserStore.userId) {
      console.error('=== 用戶ID驗證失敗詳細資訊 ===')
      console.error('userId:', externalUserStore.userId)
      console.error('externalId:', externalUserStore.externalId)
      console.error('isLoaded:', externalUserStore.isLoaded)
      console.error('hasUserInfo:', externalUserStore.hasUserInfo)
      console.error('userName:', externalUserStore.userName)
      console.error('comId:', externalUserStore.comId)

      if (!externalUserStore.externalId) {
        throw new Error('用戶驗證失敗：未找到外部用戶ID，請檢查是否有有效的 token 參數')
      } else {
        throw new Error(`用戶驗證失敗：外部用戶ID (${externalUserStore.externalId}) 在系統中找不到對應的內部用戶記錄，請聯繫管理員進行用戶同步`)
      }
    }

    if (!questionId || !contentId) {
      throw new Error('問題ID或內容ID不存在')
    }

    // 修正資料結構 - 使用 API 期望的格式
    const answerData = {
      responses: [
        {
          question_content_id: parseInt(contentId),
          response_value: formData.value
        }
      ],
      answered_by: parseInt(externalUserStore.userId)
    }

    console.log('=== 送出的資料結構 ===')
    console.log('API URL:', `/api/v1/question-management/assessment/${questionId}/responses`)
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

    // 關閉載入中對話框
    $notify.close()

    if (response.success) {
      console.log('✅ 答案保存成功')

      // 使用 SweetAlert 顯示成功訊息並自動跳轉
      await $notify.success('保存成功！', '您的答案已成功保存')

      // 直接返回到題目列表，不需要再次確認
      router.push(`/web/risk-assessment/questions/${companyId}/management/${questionId}/content`)
    } else {
      throw new Error(response.message || '儲存失敗')
    }
  } catch (error) {
    console.error('❌ 保存答案時發生錯誤:', error)

    // 關閉載入中對話框
    $notify.close()

    // 詳細錯誤分析
    if (error.response) {
      console.error('HTTP 錯誤狀態:', error.response.status)
      console.error('錯誤詳情:', error.response._data)
    }

    // 顯示用戶友好的錯誤訊息
    let errorTitle = '保存失敗'
    let errorMessage = ''

    if (error.message.includes('用戶ID不存在') || error.message.includes('用戶驗證失敗')) {
      errorTitle = '用戶驗證失敗'
      errorMessage = '請重新登入或檢查權限設定'
    } else if (error.response?.status === 400) {
      errorTitle = '資料格式錯誤'
      errorMessage = '請檢查填寫內容是否完整正確<br><br>可能原因：<br>• 必填欄位未填寫<br>• 資料格式不正確<br>• 網路連線異常'
    } else if (error.response?.status === 500) {
      errorTitle = '系統錯誤'
      errorMessage = '伺服器發生錯誤，請稍後再試'
    } else if (error.response?._data?.message) {
      errorMessage = error.response._data.message
    } else {
      errorMessage = error.message || '未知錯誤，請聯繫系統管理員'
    }

    // 使用 SweetAlert 顯示錯誤訊息
    await $notify.error(errorTitle, errorMessage)
  } finally {
    isSaving.value = false
  }
}

// Go back to question list
const goBack = async () => {
  const { $notify } = useNuxtApp()

  // 確認是否要離開頁面
  const result = await $notify.confirm(
    '確認離開',
    '您確定要離開此頁面嗎？未保存的資料將會遺失。',
    '離開',
    '取消'
  )

  if (result.isConfirmed) {
    router.push(`/web/risk-assessment/questions/${companyId}/management/${questionId}/content`)
  }
}

// Fill test data functionality
const fillTestData = async () => {
  isFillingTestData.value = true

  try {
    // 模擬填入過程的延遲
    await new Promise(resolve => setTimeout(resolve, 500))

    // 隨機選擇器函數
    const getRandomOption = (options) => {
      const randomIndex = Math.floor(Math.random() * options.length)
      return options[randomIndex].value
    }

    // 隨機文字生成器
    const getRandomText = (templates) => {
      const randomIndex = Math.floor(Math.random() * templates.length)
      return templates[randomIndex]
    }

    // 隨機選擇是/否
    const getRandomChoice = () => Math.random() > 0.5 ? 'yes' : 'no'

    // C區域測試資料模板
    const riskEventDescriptions = [
      '本年度未發生重大風險事件，但有輕微的供應鏈中斷情況',
      '曾發生一次輕微的環境污染事件，已立即處理並改善',
      '發生設備故障導致短暫停產，影響約2天生產時間',
      '遭遇颱風天災影響，造成部分廠房設施受損',
      '發生員工工安意外事件，已加強安全管控措施'
    ]

    // D區域測試資料模板
    const counterActionDescriptions = [
      '導入ISO 14001環境管理系統，強化環境風險控制',
      '建立完善的供應商評估機制，提升供應鏈穩定性',
      '投資設備升級及預防性維護，降低故障風險',
      '制定完整的緊急應變計畫，提升災害應對能力',
      '加強員工安全訓練，建立安全文化'
    ]

    const counterActionCosts = [
      '預估投入約200萬元進行系統建置',
      '年度預算約150萬元用於供應商管理',
      '設備升級費用約500萬元',
      '緊急應變設施建置約300萬元',
      '安全訓練及設施改善約100萬元'
    ]

    // E區域測試資料模板
    const riskDescriptions = [
      '氣候變遷可能導致極端天氣增加，影響生產及供應鏈穩定性',
      '原物料價格波動風險，可能影響產品成本及獲利能力',
      '法規變更風險，環保法規趨嚴可能增加合規成本',
      '市場需求變化風險，消費者偏好改變影響產品銷售',
      '技術變革風險，新技術發展可能使現有產品面臨淘汰'
    ]

    const riskCalculations = [
      '預估極端天氣發生頻率增加20%，可能影響年營收5-10%',
      '原物料成本波動±15%，直接影響毛利率變化',
      '環保合規成本預估每年增加3-5%',
      '市場需求變化可能影響銷售量±20%',
      '技術升級投資需求每年約佔營收2-3%'
    ]

    // F區域測試資料模板
    const opportunityDescriptions = [
      '發展綠色產品線，搶攻環保意識高漲的市場商機',
      '數位轉型提升營運效率，降低成本創造競爭優勢',
      '拓展新興市場，分散單一市場風險並增加收入來源',
      '投資研發創新技術，建立技術領先的市場地位',
      '建立循環經濟商業模式，創造新的獲利機會'
    ]

    const opportunityCalculations = [
      '綠色產品預估可帶來年營收成長10-15%',
      '數位轉型預估可節省營運成本8-12%',
      '新市場拓展預估3年內貢獻營收20%',
      '創新技術預估可提升毛利率5-8%',
      '循環經濟模式預估可創造額外營收5-10%'
    ]

    // G&H區域測試資料模板
    const negativeImpactDescriptions = [
      '生產過程產生的廢水及廢氣排放，對當地環境造成輕微影響',
      '運輸過程的碳排放，對全球暖化產生微小貢獻',
      '原料採購可能間接影響生態環境，但已採取永續採購政策',
      '包裝材料使用產生廢棄物，已推動減量及回收措施',
      '能源使用產生碳足跡，正積極導入再生能源'
    ]

    const positiveImpactDescriptions = [
      '投資綠色技術研發，為產業永續發展做出貢獻',
      '創造在地就業機會，帶動社區經濟發展',
      '推動供應商ESG改善，提升整體產業鏈永續性',
      '開發環保產品，協助客戶降低環境負荷',
      '參與環境保護活動，提升社會環保意識'
    ]

    // 填入所有表單資料
    formData.value = {
      // A和B區域保持原有內容
      riskFactorDescription: formData.value.riskFactorDescription,
      referenceText: formData.value.referenceText,

      // C區域
      riskEventChoice: getRandomChoice(),
      riskEventDescription: getRandomText(riskEventDescriptions),

      // D區域
      counterActionChoice: getRandomChoice(),
      counterActionDescription: getRandomText(counterActionDescriptions),
      counterActionCost: getRandomText(counterActionCosts),

      // E區域
      riskDescription: getRandomText(riskDescriptions),
      riskProbability: getRandomOption(probabilityOptions),
      riskImpact: getRandomOption(impactOptions),
      riskCalculation: getRandomText(riskCalculations),

      // F區域
      opportunityDescription: getRandomText(opportunityDescriptions),
      opportunityProbability: getRandomOption(probabilityOptions),
      opportunityImpact: getRandomOption(impactOptions),
      opportunityCalculation: getRandomText(opportunityCalculations),

      // G區域
      negativeImpactLevel: getRandomOption(impactLevelOptions),
      negativeImpactDescription: getRandomText(negativeImpactDescriptions),

      // H區域
      positiveImpactLevel: getRandomOption(impactLevelOptions),
      positiveImpactDescription: getRandomText(positiveImpactDescriptions)
    }

    console.log('測試資料填入完成')

  } catch (error) {
    console.error('填入測試資料時發生錯誤:', error)
  } finally {
    isFillingTestData.value = false
  }
}

// Lifecycle
onMounted(async () => {
  console.log('Answer page mounted for:', { companyId, questionId, contentId })

  // 檢查並初始化用戶資料
  const { $route } = useNuxtApp()
  const token = $route.query.token || ''

  console.log('=== 用戶資料初始化檢查 ===')
  console.log('Token from URL:', token)
  console.log('Current userId in store:', externalUserStore.userId)
  console.log('Current externalId in store:', externalUserStore.externalId)
  console.log('Is user data loaded:', externalUserStore.isLoaded)

  // 如果有 token 且用戶資料尚未載入，則調用用戶資料解密 API
  if (token && !externalUserStore.isLoaded) {
    console.log('🔄 開始載入用戶資料...')
    try {
      await externalUserStore.fetchExternalUserData(token)
      console.log('✅ 用戶資料載入完成')
      console.log('New userId:', externalUserStore.userId)
      console.log('New externalId:', externalUserStore.externalId)
    } catch (error) {
      console.error('❌ 載入用戶資料失敗:', error)
    }
  } else if (!token) {
    console.log('⚠️ 未提供 token，無法載入用戶資料')
  } else {
    console.log('ℹ️ 用戶資料已存在，跳過載入')
  }

  // 如果 userId 仍然為空，嘗試重新獲取
  if (!externalUserStore.userId && externalUserStore.externalId) {
    console.log('🔄 嘗試重新獲取內部用戶ID...')
    try {
      const internalId = await externalUserStore.fetchInternalUserId(externalUserStore.externalId)
      if (internalId) {
        console.log('✅ 重新獲取內部用戶ID成功:', internalId)
      } else {
        console.log('❌ 重新獲取內部用戶ID失敗')
      }
    } catch (error) {
      console.error('❌ 重新獲取內部用戶ID時發生錯誤:', error)
    }
  }

  // 載入問題資料
  await loadQuestionData()

  // 如果是查看模式，載入已存在的答案
  if (isViewMode.value) {
    await loadExistingAnswers()
  }
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