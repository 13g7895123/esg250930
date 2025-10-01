<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-8">

      <!-- Page Title Section -->
      <div class="mb-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ questionData?.description || '題目編輯' }}</h1>
            <p class="mt-1 text-base text-gray-500 dark:text-gray-400">
              類別：{{ getCategoryName(questionData?.category_id) }} | 編輯完整的ESG評估題目內容
            </p>
          </div>
          <div class="flex space-x-3">
            <button
              @click="fillTestData"
              class="px-4 py-2 text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-900/20 border border-purple-300 dark:border-purple-600 rounded-2xl hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors duration-200"
            >
              填入測試資料
            </button>
            <button
              @click="goBack"
              class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-2xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200"
            >
              返回
            </button>
            <button
              @click="goToPreview"
              class="px-4 py-2 text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/20 border border-blue-300 dark:border-blue-600 rounded-2xl hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors duration-200"
            >
              預覽
            </button>
            <button
              @click="saveQuestion"
              :disabled="isSaving"
              class="px-4 py-2 bg-green-600 text-white rounded-2xl hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors duration-200"
            >
              <span v-if="isSaving" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                儲存中...
              </span>
              <span v-else>儲存題目</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Top Category Information -->
      <div class="mb-6">
        <div class="grid grid-cols-[1fr_1fr_2fr] gap-5 mb-4">
          <div class="flex items-center gap-3 bg-gray-100 p-4 rounded-2xl">
            <h3 class="font_title">風險類別</h3>
            <p class="text-gray-900 text-xl">環境風險類別說明</p>
          </div>
          <div class="flex items-center gap-3 bg-gray-100 p-4 rounded-2xl">
            <h3 class="font_title">風險主題</h3>
            <p class="text-gray-900 text-xl">自然資源相關主題</p>
          </div>
          <div class="flex items-center gap-3 bg-green-600 p-4 rounded-2xl">
            <div>
              <h3 class="w-20 font-bold text-white text-xl inline-block">風險因子</h3>
            </div>
            <div>
              <p class="text-white text-xl">自然資源依賴及衝擊</p>
            </div>
          </div>
        </div>
      </div>


      <!-- Form Sections -->
      <div class="space-y-6">
        <!-- Section A: 風險因子議題描述 -->
        <div class="bg-white assessment-rounded border border-gray-200 overflow-hidden">
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
            <!-- 分隔線與標題 -->
            <div class="mb-3">
              <div class="border-t-2 border-gray-300 mb-4 rounded-full"></div>
            </div>

            <!-- 使用富文本編輯器組件，載入content頁面的風險因子描述 -->
            <RichTextEditor
              v-model="formData.riskFactorDescription"
              :placeholder="''"
              :show-html-info="false"
              @change="onRiskFactorChange"
              @blur="onRiskFactorBlur"
            />
          </div>
        </div>

        <!-- Section B: 參考文字&模組工具評估結果 -->
        <div class="bg-white assessment-rounded border border-gray-200 overflow-hidden">
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
            <!-- 分隔線與標題 -->
            <div class="mb-3">
              <div class="border-t-2 border-gray-300 mb-4 rounded-full"></div>
            </div>
            <div class="space-y-4">


              <!-- 使用富文本編輯器組件 -->
              <RichTextEditor
                v-model="formData.referenceText"
                placeholder=""
                :show-html-info="false"
                @change="onReferenceTextChange"
                @blur="onReferenceTextBlur"
              />
            </div>
          </div>
        </div>

        <!-- Section C: 公司報導年度是否有發生實際風險/負面衝擊事件 -->
        <div class="bg-white assessment-rounded border border-gray-200 p-6">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
              <span class="font_title">公司報導年度是否有發生實際風險/負面衝擊事件</span>
              <span class="bg-gray-200 text-gray-800 px-2 py-1 rounded text-base font-medium">C</span>
            </div>
            <button class="px-3 py-1 bg-green-600 text-white text-base rounded-full">紀錄</button>
          </div>

          <div class="space-y-4">
            <!-- 美觀的卡片式 Radio 選項 -->
            <div class="grid grid-cols-2 gap-6">
              <label class="radio-card-option radio-card-no" :class="{ 'selected': formData.hasRiskEvent === 'yes' }">
                <input
                  v-model="formData.hasRiskEvent"
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

              <label class="radio-card-option radio-card-yes" :class="{ 'selected': formData.hasRiskEvent === 'no' }">
                <input
                  v-model="formData.hasRiskEvent"
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
                class="w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent"
                placeholder="請輸入該題目懸浮文字"
              />
            </div>
          </div>
        </div>

        <!-- Section D: 公司報導年度是否有相關對應作為 -->
        <div class="bg-white assessment-rounded border border-gray-200 p-6">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
              <span class="font_title">公司報導年度是否有相關對應作為</span>
              <span class="bg-gray-200 text-gray-800 px-2 py-1 rounded text-base font-medium">D</span>
            </div>
            <button class="px-3 py-1 bg-green-600 text-white text-base rounded-full">紀錄</button>
          </div>

          <div class="space-y-4">
            <!-- 美觀的卡片式 Radio 選項 -->
            <div class="grid grid-cols-2 gap-6">
              <label class="radio-card-option radio-card-no" :class="{ 'selected': formData.hasCounterAction === 'yes' }">
                <input
                  v-model="formData.hasCounterAction"
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

              <label class="radio-card-option radio-card-yes" :class="{ 'selected': formData.hasCounterAction === 'no' }">
                <input
                  v-model="formData.hasCounterAction"
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
                class="w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent"
                placeholder="導入TNFD請專業，進一步了解致利納於自然資源保護性"
              />
            </div>

            <div>
              <label class="text-gray-600 mt-6 mb-1">*上述對策費用</label>
              <textarea
                v-model="formData.counterActionCost"
                rows="2"
                class="w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent"
                placeholder="顧問費用約80萬(可初估)"
              />
            </div>
          </div>
        </div>

        <!-- Green Context Bar -->
        <div class="bg-green-600 text-white px-6 py-3 rounded-2xl flex items-center justify-between">
          <span class="font_title_white">請依上述資訊，整點公司致富相關之風險情況，並評估未來永續在各風險/機會情境</span>
          <button
            @click="showProbabilityScaleModal = true"
            class="px-4 py-2 bg-white text-black font-bold rounded-full hover:bg-gray-100 transition-colors duration-200 flex items-center space-x-2 whitespace-nowrap"
          >
            <span>可能性量表</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </button>
        </div>

        <!-- Sections E, F, G, H in Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Section E-1: 相關風險 -->
          <div class="bg-white assessment-rounded border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center space-x-3">
                <span class="font_title">相關風險</span>
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-base font-medium flex items-center">
                  E-1
                </span>
                <div
                  class="relative group w-5 h-5 ml-2 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-serif font-bold cursor-pointer hover:scale-110 transition-transform duration-200"
                  @click="editHoverText('E1')"
                  :title="hoverTexts.E1"
                >
                  <span class="italic">i</span>
                </div>
              </div>
              <button class="px-3 py-1 bg-green-600 text-white text-base rounded-full">紀錄</button>
            </div>

            <div class="space-y-4">
              <p class="text-base text-gray-600">公司未來潛在相關風險營清說明，未來潛在風險（收入減少）、費用增加於損益</p>

              <div>
                <label class="text-gray-600 mt-6 mb-1">風險描述</label>
                <textarea
                  v-model="formData.riskDescription"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent"
                  placeholder="致使集團對外資源、石油、天然氣、動植物資源、海洋魚類供給、土壤、植木等依賴不高，威脅相關風險"
                />
              </div>
            </div>
          </div>

          <!-- Section E-2: 風險財務影響評估 -->
          <div class="bg-white assessment-rounded border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center space-x-3">
                <span class="font_title">請依上述公司盤點之風險情境評估一旦發生風險對公司之財務影響</span>
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-base font-medium flex items-center">
                  E-2
                </span>
              </div>
              <button class="px-3 py-1 bg-green-600 text-white text-base rounded-full">紀錄</button>
            </div>

            <div class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-base text-gray-600 mb-1">*風險發生可能性</label>
                  <select
                    v-model="formData.riskProbability"
                    class="w-full border border-gray-300 rounded-2xl px-3 py-2 text-center focus:ring-2 focus:ring-green-500 focus:border-transparent"
                  >
                    <option v-for="option in probabilityScaleOptions" :key="option.value" :value="option.value">
                      {{ option.text }}
                    </option>
                  </select>
                </div>
                <div>
                  <label class="block text-base text-gray-600 mb-1">*風險發生衝擊程度</label>
                  <select
                    v-model="formData.riskImpactLevel"
                    class="w-full border border-gray-300 rounded-2xl px-3 py-2 text-center focus:ring-2 focus:ring-green-500 focus:border-transparent"
                  >
                    <option v-for="option in impactScaleOptions" :key="option.value" :value="option.value">
                      {{ option.text }}
                    </option>
                  </select>
                </div>
              </div>

              <div>
                <label class="flex items-center text-base text-gray-600 mb-1">
                  *計算說明
                  <InformationCircleIcon class="w-4 h-4 ml-1" />
                </label>
                <textarea
                  v-model="formData.riskCalculation"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent text-base"
                  placeholder="致使相關風險、衝擊程度低"
                />
              </div>
            </div>
          </div>

          <!-- Section F-1: 相關機會 -->
          <div class="bg-white assessment-rounded border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center space-x-3">
                <span class="font_title">相關機會</span>
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-base font-medium flex items-center">
                  F-1
                </span>
                <div
                  class="relative group w-5 h-5 ml-2 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-serif font-bold cursor-pointer hover:scale-110 transition-transform duration-200"
                  @click="editHoverText('F1')"
                  :title="hoverTexts.F1"
                >
                  <span class="italic">i</span>
                </div>
              </div>
              <button class="px-3 py-1 bg-green-600 text-white text-base rounded-full">紀錄</button>
            </div>

            <div class="space-y-4">
              <p class="text-base text-gray-600">公司未來潛在相關機會營清說明，未來潛在機會（收入增加）、費用減少於收益等不會定</p>

              <div>
                <label class="text-gray-600 mt-6 mb-1">機會描述</label>
                <textarea
                  v-model="formData.opportunityDescription"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent"
                  placeholder="集團持續導入資源效率管理、循環經濟及生物保護措施，以降低風險並最終可能導致業務擴張，可帶來更多客戶期待"
                />
              </div>
            </div>
          </div>

          <!-- Section F-2: 機會財務影響評估 -->
          <div class="bg-white assessment-rounded border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center space-x-3">
                <span class="font_title">請依上述公司盤點之機會情境評估一旦發生機會對公司之財務影響</span>
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-base font-medium flex items-center">
                  F-2
                </span>
              </div>
              <button class="px-3 py-1 bg-green-600 text-white text-base rounded-full">紀錄</button>
            </div>

            <div class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-base text-gray-600 mb-1">*機會發生可能性</label>
                  <select
                    v-model="formData.opportunityProbability"
                    class="w-full border border-gray-300 rounded-2xl px-3 py-2 text-center focus:ring-2 focus:ring-green-500 focus:border-transparent"
                  >
                    <option v-for="option in probabilityScaleOptions" :key="option.value" :value="option.value">
                      {{ option.text }}
                    </option>
                  </select>
                </div>
                <div>
                  <label class="block text-base text-gray-600 mb-1">*機會發生衝擊程度</label>
                  <select
                    v-model="formData.opportunityImpactLevel"
                    class="w-full border border-gray-300 rounded-2xl px-3 py-2 text-center focus:ring-2 focus:ring-green-500 focus:border-transparent"
                  >
                    <option v-for="option in impactScaleOptions" :key="option.value" :value="option.value">
                      {{ option.text }}
                    </option>
                  </select>
                </div>
              </div>

              <div>
                <label class="flex items-center text-base text-gray-600 mb-1">
                  *計算說明
                  <InformationCircleIcon class="w-4 h-4 ml-1" />
                </label>
                <textarea
                  v-model="formData.opportunityCalculation"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent text-base"
                  placeholder="因持續符合客戶ESG要求，可望獲加公司營收機會以帶年增加5%計算，約可帶來30億的正面效益"
                />
              </div>
            </div>
          </div>

          <!-- Final Assessment Context -->
          <div class="col-span-1 lg:col-span-2">
            <div class="bg-green-600 text-white px-6 py-3 assessment-rounded mb-6">
              <span class="font_title_white">請依上述公司營點之進行或風險會環境，結合評估公司之營運程此議題可能造成的「對外」衝擊（對外部環境、環境、人群（含含責人補）之正/負面影響）</span>
            </div>
          </div>

          <!-- Section G-1: 對外負面衝擊 -->
          <div class="bg-white assessment-rounded border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center space-x-3">
                <span class="font_title">對外負面衝擊</span>
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-base font-medium flex items-center">
                  G-1
                </span>
                <div
                  class="relative group w-5 h-5 ml-2 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-serif font-bold cursor-pointer hover:scale-110 transition-transform duration-200"
                  @click="editHoverText('G1')"
                  :title="hoverTexts.G1"
                >
                  <span class="italic">i</span>
                </div>
              </div>
              <button class="px-3 py-1 bg-green-600 text-white text-base rounded-full">紀錄</button>
            </div>

            <div class="space-y-4">
              <div>
                <label class="block text-base text-gray-600 mb-1">負面衝擊程度</label>
                <select
                  v-model="formData.negativeImpactLevel"
                  class="w-full border border-gray-300 rounded-2xl px-3 py-2 text-center font-medium focus:ring-2 focus:ring-green-500 focus:border-transparent"
                >
                  <option value="1">1 (極低衝擊)</option>
                  <option value="2">2 (低衝擊)</option>
                  <option value="3">3 (中等衝擊)</option>
                  <option value="4">4 (高衝擊)</option>
                  <option value="5">5 (極高衝擊)</option>
                </select>
              </div>

              <div>
                <label class="block text-base text-gray-600 mb-1">評分說明</label>
                <textarea
                  v-model="formData.negativeImpactDescription"
                  rows="4"
                  class="w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent text-base"
                  placeholder="致使集團為士範生產據點位於水稀缺響景象風險政策一定程度之衝擊，但透過確實四有著含高地調評湖法，且覺讓落實未來高污染活道，所以負面衝擊不至於太高"
                />
              </div>
            </div>
          </div>

          <!-- Section H-1: 對外正面影響 -->
          <div class="bg-white assessment-rounded border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center space-x-3">
                <span class="font_title">對外正面影響</span>
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-base font-medium flex items-center">
                  H-1
                </span>
                <div
                  class="relative group w-5 h-5 ml-2 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-serif font-bold cursor-pointer hover:scale-110 transition-transform duration-200"
                  @click="editHoverText('H1')"
                  :title="hoverTexts.H1"
                >
                  <span class="italic">i</span>
                </div>
              </div>
              <button class="px-3 py-1 bg-green-600 text-white text-base rounded-full">紀錄</button>
            </div>

            <div class="space-y-4">
              <div>
                <label class="block text-base text-gray-600 mb-1">正面影響程度</label>
                <select
                  v-model="formData.positiveImpactLevel"
                  class="w-full border border-gray-300 rounded-2xl px-3 py-2 text-center font-medium focus:ring-2 focus:ring-green-500 focus:border-transparent"
                >
                  <option value="1">1 (極低影響)</option>
                  <option value="2">2 (低影響)</option>
                  <option value="3">3 (中等影響)</option>
                  <option value="4">4 (高影響)</option>
                  <option value="5">5 (極高影響)</option>
                </select>
              </div>

              <div>
                <label class="block text-base text-gray-600 mb-1">評分說明</label>
                <textarea
                  v-model="formData.positiveImpactDescription"
                  rows="4"
                  class="w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent text-base"
                  placeholder="50年-60年，並且目前已導入TNFD專業，對外部自然環境應常來正面影響"
                />
              </div>
            </div>
          </div>
        </div>


        <!-- Submit Button -->
        <div class="flex justify-center hidden">
          <button
            @click="saveQuestion"
            class="px-8 py-3 bg-green-600 text-white rounded-2xl hover:bg-green-700 font-medium text-lg"
          >
            提交
          </button>
        </div>
      </div>
    </div>

    <!-- Floating Save Button -->
    <div class="fixed bottom-6 right-6 z-50 hidden">
      <button
        @click="saveQuestion"
        class="flex items-center px-6 py-3 bg-green-600 text-white rounded-2xl shadow-lg hover:bg-green-700 transition-all duration-200 transform hover:scale-105"
      >
        <CheckIcon class="w-5 h-5 mr-2" />
        儲存題目
      </button>
    </div>

    <!-- Hover Text Edit Modal -->
    <Teleport to="body" v-if="showHoverEditModal">
      <div
        class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center"
        @click="cancelHoverEdit"
        style="position: fixed !important; top: 0 !important; left: 0 !important; width: 100vw !important; height: 100vh !important;"
      >
        <div
          class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md mx-4"
          @click.stop
        >
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


    <!-- Probability Scale Modal -->
    <ScaleEditorModal
      v-model="showProbabilityScaleModal"
      title="量表編輯"
      :is-loading="isLoadingScales"
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
      @save="saveProbabilityScale"
    />

  </div>
</template>

<script setup>
import {
  ChevronUpIcon,
  ChevronDownIcon,
  InformationCircleIcon,
  CheckIcon
} from '@heroicons/vue/24/outline'
import RichTextEditor from '~/components/RichTextEditor.vue'
import apiClient from '~/utils/api.js'
import { useScaleManagement } from '~/composables/useScaleManagement'
import ScaleEditorModal from '~/components/Scale/ScaleEditorModal.vue'

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

usePageTitle('題目編輯')

// Reactive state

// Expandable sections state
const expandedSections = ref({
  sectionA: true,
  sectionB: false
})

// Hover text state for info icons (default values)
const hoverTexts = ref({
  E1: '相關風險說明：企業面臨的風險評估相關資訊',
  F1: '相關機會說明：企業可能的機會評估相關資訊',
  G1: '對外負面衝擊說明：企業對外部環境可能造成的負面影響',
  H1: '對外正面影響說明：企業對外部環境可能產生的正面影響'
})

// Modal state for editing hover text
const showHoverEditModal = ref(false)
const editingSection = ref('')
const editingHoverText = ref('')

// Modal state for probability scale
const showProbabilityScaleModal = ref(false)
const activeTab = ref('probability')
const isLoadingScales = ref(false)

// Use scale management composable
const {
  // Probability Scale State
  probabilityScaleColumns,
  probabilityScaleRows,
  probabilityScaleId,
  selectedProbabilityDisplayColumn,
  showDescriptionText,
  descriptionText,

  // Impact Scale State
  impactScaleColumns,
  impactScaleRows,
  impactScaleId,
  selectedImpactDisplayColumn,
  showImpactDescriptionText,
  impactDescriptionText,

  // Probability Scale Methods
  addProbabilityColumn,
  removeProbabilityColumn,
  addProbabilityRow,
  removeProbabilityRow,
  addProbabilityDescriptionText,
  removeProbabilityDescriptionText,

  // Impact Scale Methods
  addImpactColumn,
  removeImpactColumn,
  addImpactRow,
  removeImpactRow,
  addImpactDescriptionText,
  removeImpactDescriptionText,

  // Computed
  probabilityScaleOptions,
  impactScaleOptions,

  // Utility Methods
  loadScalesData,
  prepareScaleDataForSubmission
} = useScaleManagement()

// Preview state
const showPreview = ref(false)

const togglePreview = () => {
  showPreview.value = !showPreview.value
}

// Get question data
const questionData = computed(() => {
  const contentList = templatesStore.getTemplateContent(templateId)
  const items = contentList.value || []
  return items.find(item => parseInt(item.id) === parseInt(contentId))
})

// Template information for breadcrumb
const templateInfo = templatesStore.getTemplateById(templateId)

const riskCategories = templatesStore.getRiskCategories(templateId)
const riskTopics = templatesStore.getRiskTopics(templateId)
const riskFactors = templatesStore.getRiskFactors(templateId)

// Get risk topics enabled state from template settings
const riskTopicsEnabled = computed(() => {
  return templateInfo.value?.risk_topics_enabled ?? true
})

// Get risk factor description from current template content record
const selectedRiskFactorDescription = computed(() => {
  // 從 template content 中根據當前的 contentId 載入對應記錄的描述
  const templateContent = templatesStore.getTemplateContent(templateId).value
  const currentContent = templateContent?.find(content =>
    content.id == contentId
  )

  console.log('載入當前內容的風險因子描述:', {
    contentId: contentId,
    templateContent: templateContent,
    currentContent: currentContent,
    description: currentContent?.description
  })

  return currentContent?.description || null
})

// Basic question info (description, category, etc.)
const basicInfo = ref({
  description: '風險評估內容',
  categoryId: 1,
  topicId: 1,
  riskFactorId: 1,
  isRequired: 1
})

// Form data matching the ESG structure
const formData = ref({
  // Section A
  riskFactorDescription: '',
  // Section B
  referenceText: '',
  // Section C
  hasRiskEvent: '',
  riskEventDescription: '',
  // Section D
  hasCounterAction: '',
  counterActionDescription: '',
  counterActionCost: '',
  // Section E-1
  riskDescription: '',
  // Section E-2
  riskProbability: 1,
  riskImpactLevel: 1,
  riskCalculation: '',
  // Section F-1
  opportunityDescription: '',
  // Section F-2
  opportunityProbability: 1,
  opportunityImpactLevel: 3,
  opportunityCalculation: '',
  // Section G-1
  negativeImpactLevel: 2,
  negativeImpactDescription: '',
  // Section H-1
  positiveImpactLevel: 2,
  positiveImpactDescription: ''
})

// Saving state
const isSaving = ref(false)

// Initialize form data when question data is available
watch(questionData, (newData) => {
  if (newData) {
    // Initialize basic info with actual data from API
    basicInfo.value = {
      description: newData.description || '風險評估內容',
      categoryId: newData.category_id ? parseInt(newData.category_id) : null,
      topicId: newData.topic_id ? parseInt(newData.topic_id) : null,
      riskFactorId: newData.risk_factor_id ? parseInt(newData.risk_factor_id) : null,
      isRequired: newData.is_required ? parseInt(newData.is_required) : 1
    }

    console.log('✅ basicInfo 初始化完成:', basicInfo.value)

    // Initialize hover texts with saved data from API
    if (newData.e1_info) hoverTexts.value.E1 = newData.e1_info
    if (newData.f1_info) hoverTexts.value.F1 = newData.f1_info
    if (newData.g1_info) hoverTexts.value.G1 = newData.g1_info
    if (newData.h1_info) hoverTexts.value.H1 = newData.h1_info

    // Initialize with existing data or defaults
    // 優先使用從 content 頁面輸入的風險因子描述，然後是已保存的內容，最後才使用預設值
    console.log('初始化數據:', {
      description: newData.description,
      a_content: newData.a_content,
      risk_factor_description: newData.risk_factor_description,
      selectedRiskFactorDescription: selectedRiskFactorDescription.value
    })

    // 優先使用從 template_contents 載入的風險因子描述
    const riskFactorDescription = selectedRiskFactorDescription.value || newData.a_content || newData.risk_factor_description || '企業的營運往往高度依賴自然資源，如水資源、石油、天然氣、動植物資源、海洋魚類供給、土壤、植木等、隨著氣候變遷、生態退化與資源稀缺問題日益嚴峻，若企業未能妥善管理資源使用環境衝擊，可能面臨供應中斷、成本上升與合規壓力等風險。同時，過度依賴自然資源或可生產活動衝擊，亦可能引發社會關注與質疑指稱。'

    console.log('最終使用的風險因子描述:', riskFactorDescription)

    formData.value = {
      riskFactorDescription: riskFactorDescription,
      referenceText: newData.b_content || newData.reference_text || '🔵去年報告書文字或第三方背景資料整理：<br>1. 台灣與生產據點的用水，皆不是韌水稀缺，對韌地區水資源缺乏威脅低，政府10個高溫期造成為高風險中高風險，並無缺點墊常落高風險之中<br>2. 政府推薦有七個生產據點位於水稀缺聯盟審查風險之地區，對當地自然環境具一定程度之衝擊<br><br>🔵可能思考之風險情境面：<br>1. 自然資源依賴性(對內)：缺乏水資源、石油、天然氣、動植物資源、海洋魚類供給、土壤、植木科集團之影響<br>2. 自然資源衝擊性(對外)：企業生產營運對自然資源之衝擊影響，例如果案圍7個生產據點對森林總藍海之衝擊影響<br><br>🔵參考文獻說明：<br>1. WWFBRF中的分析報告面，對於供應關的「水資源短缺」，致使10個高溫期造成為高風險中高風險，並無缺點墊常落高風險之中。<br>2. 供應鏈查訊於自然資源流之地區（例如水資源力區等）－豐等結果<br>3. WWFBRF資融關的之「生物多樣性壓力」中的「植木資業」具有高風險：致使圍、致使東業、致使東國、致使東業、油染危農州、油染危業等、溫染危業關',
      hasRiskEvent: newData.has_risk_event || 'no',
      riskEventDescription: newData.c_placeholder || newData.risk_event_description || '台灣與生產據點的用水，皆不是韌水稀缺，沒有韌點韌點水用微低的風險',
      hasCounterAction: newData.has_counter_action || 'yes',
      counterActionDescription: newData.d_placeholder_1 || newData.counter_action_description || '',
      counterActionCost: newData.d_placeholder_2 || newData.counter_action_cost || '',
      riskDescription: newData.e1_placeholder_1 || newData.risk_description || '',
      // E-2 uses new fields, fallback to old e1 fields for backward compatibility
      riskProbability: newData.e2_select_1 ? parseInt(newData.e2_select_1) : (newData.e1_select_1 ? parseInt(newData.e1_select_1) : (newData.risk_probability || 1)),
      riskImpactLevel: newData.e2_select_2 ? parseInt(newData.e2_select_2) : (newData.e1_select_2 ? parseInt(newData.e1_select_2) : (newData.risk_impact_level || 1)),
      riskCalculation: newData.e2_placeholder || newData.e1_placeholder_2 || newData.risk_calculation || '致使相關風險、衝擊程度低',
      opportunityProbability: newData.opportunity_probability || 1,
      opportunityImpactLevel: newData.opportunity_impact_level || 3,
      opportunityCalculation: newData.opportunity_calculation || '因持續符合客戶ESG要求，可望獲加公司營收機會以帶年增加5%計算，約可帶來30億的正面效益',
      negativeImpactLevel: newData.negative_impact_level || 2,
      negativeImpactDescription: newData.negative_impact_description || '致使集團為士範生產據點位於水稀缺響景象風險政策一定程度之衝擊，但透過確實四有著含高地調評湖法，且覺讓落實未來高污染活道，所以負面衝擊不至於太高',
      positiveImpactLevel: newData.positive_impact_level || 2,
      positiveImpactDescription: newData.positive_impact_description || '50年-60年，並且目前已導入TNFD專業，對外部自然環境應常來正面影響'
    }
  }
}, { immediate: true })

// Helper methods
const getCategoryName = (categoryId) => {
  const category = riskCategories.value?.find(cat => cat.id === categoryId)
  return category ? category.category_name : '未知類別'
}

const toggleSection = (sectionKey) => {
  expandedSections.value[sectionKey] = !expandedSections.value[sectionKey]
}

// 富文本編輯器事件處理
const onRiskFactorChange = (htmlContent) => {
  console.log('風險因子描述已更新:', htmlContent.length, '字元')
}

const onRiskFactorBlur = () => {
  console.log('風險因子編輯器失去焦點')
}

// 參考文字富文本編輯器事件處理
const onReferenceTextChange = (htmlContent) => {
  console.log('參考文字已更新:', htmlContent.length, '字元')
}

const onReferenceTextBlur = () => {
  console.log('參考文字編輯器失去焦點')
}

// Cascade dropdown handlers
const onCategoryChange = async () => {
  // Reset dependent dropdowns
  basicInfo.value.topicId = ''
  basicInfo.value.riskFactorId = ''

  if (basicInfo.value.categoryId) {
    if (riskTopicsEnabled.value) {
      // Load topics for selected category if topics are enabled
      try {
        await templatesStore.fetchRiskTopics(templateId, { category_id: basicInfo.value.categoryId })
      } catch (error) {
        console.error('Failed to fetch topics for category:', error)
      }
    } else {
      // Load risk factors directly for category if topics are disabled
      try {
        await templatesStore.fetchRiskFactors(templateId, { category_id: basicInfo.value.categoryId })
      } catch (error) {
        console.error('Failed to fetch factors for category:', error)
      }
    }
  }
}

const onTopicChange = async () => {
  // Reset dependent dropdown
  basicInfo.value.riskFactorId = ''

  // Load risk factors for selected topic (only when topics are enabled)
  if (basicInfo.value.topicId && riskTopicsEnabled.value) {
    try {
      await templatesStore.fetchRiskFactors(templateId, { topic_id: basicInfo.value.topicId })
    } catch (error) {
      console.error('Failed to fetch factors for topic:', error)
    }
  }
}

// Form validation - Simplified to focus on actual editable form inputs
const isFormValid = computed(() => {
  // Only validate the actual form inputs that users can edit
  return formData.value.riskFactorDescription.trim() &&
         formData.value.hasRiskEvent &&
         formData.value.hasCounterAction
})

// Test data generation
const generateRandomTestData = () => {
  const riskEventOptions = ['yes', 'no']
  const counterActionOptions = ['yes', 'no']

  const riskDescriptions = [
    '氣候變遷可能導致原物料供應中斷，影響生產營運',
    '水資源短缺風險可能影響製造流程，增加營運成本',
    '法規變動可能要求額外的合規成本和流程改善',
    '生物多樣性衝擊可能引發社會關注和商譽風險',
    '能源價格波動可能增加營運成本和財務壓力'
  ]

  const opportunityDescriptions = [
    '發展綠色產品可開拓新市場，提升品牌價值',
    '節能技術導入可降低營運成本，提高競爭力',
    '循環經濟模式可創造新的收入來源',
    'ESG績效提升可吸引投資者，降低融資成本',
    '永續供應鏈管理可提升客戶滿意度和忠誠度'
  ]

  const negativeImpactDescriptions = [
    '生產活動可能對當地生態環境造成一定程度影響',
    '廢棄物處理不當可能影響周邊社區環境品質',
    '水資源使用可能與當地社區產生資源競爭',
    '碳排放可能加劇氣候變遷對全球環境的衝擊',
    '化學物質使用可能對土壤和水體造成污染風險'
  ]

  const positiveImpactDescriptions = [
    '持續投資環保技術，對環境保護產生正面效益',
    '創造就業機會，促進當地經濟發展',
    '推動供應商ESG改善，帶動產業鏈永續發展',
    '環境教育推廣提升社會環保意識',
    '綠色產品推廣有助減少環境足跡'
  ]

  const calculationDescriptions = [
    '根據歷史數據分析，預估影響程度約為營收的2-5%',
    '參考同業標準，估計相關成本約3000萬至1億元',
    '基於專家評估，認為發生機率屬於中等風險等級',
    '考量市場趨勢，預期可帶來5-10%的營收成長',
    '參照國際標準，評估衝擊程度為中低等級'
  ]

  const eventDescriptions = [
    '去年發生供應鏈中斷事件，造成短期生產調整',
    '遭遇極端氣候事件，部分廠區營運受到影響',
    '面臨新環保法規要求，需投入額外合規成本',
    '因應客戶ESG要求，調整部分營運流程',
    '配合政府環保政策，導入新的廢棄物處理機制'
  ]

  const counterActionDescriptions = [
    '導入TNFD框架，加強自然資源風險評估',
    '建立供應鏈ESG管理制度，提升整體永續績效',
    '投資節能減碳技術，降低環境衝擊風險',
    '建立危機應變機制，提升風險管理能力',
    '與利害關係人建立溝通機制，降低衝突風險'
  ]

  const costDescriptions = [
    '顧問費用約80萬元，系統建置費用約200萬元',
    '設備投資約500萬元，年度維護費用約50萬元',
    '人員培訓費用約30萬元，認證費用約20萬元',
    '技術導入費用約150萬元，效益評估約300萬元',
    '管理系統建置約100萬元，持續改善費用約60萬元'
  ]

  return {
    riskFactorDescription: `<p><strong>企業營運高度依賴自然資源風險評估</strong></p><p>企業的營運往往高度依賴自然資源，如水資源、石油、天然氣、動植物資源、海洋魚類供給、土壤、森林等。隨著氣候變遷、生態退化與資源稀缺問題日益嚴峻，若企業未能妥善管理資源使用及環境衝擊，可能面臨供應中斷、成本上升與合規壓力等風險。</p><p>同時，過度依賴自然資源或生產活動造成的環境衝擊，亦可能引發社會關注與監管質疑。因此，企業需要建立完善的自然資源管理策略，以確保營運的可持續性。</p>`,
    referenceText: `<p><strong>🔵 去年報告書文字或第三方背景資料整理：</strong></p><ul><li>台灣與生產據點的用水，皆不屬於水資源稀缺地區，對當地區水資源威脅相對較低</li><li>政府調查顯示有${Math.floor(Math.random() * 5) + 3}個生產據點位於水稀缺風險地區，對當地自然環境具一定程度之衝擊</li></ul><p><strong>🔵 可能思考之風險情境面：</strong></p><ol><li><strong>自然資源依賴性(對內)：</strong>水資源、石油、天然氣、動植物資源等對集團營運之影響</li><li><strong>自然資源衝擊性(對外)：</strong>企業生產營運對自然資源之衝擊影響</li></ol><p><strong>🔵 參考文獻說明：</strong></p><p>根據TNFD框架分析，供應鏈面臨的「水資源短缺」風險評估為中高風險等級。生物多樣性壓力中的「森林砍伐」具有高風險影響。</p>`,
    hasRiskEvent: riskEventOptions[Math.floor(Math.random() * riskEventOptions.length)],
    riskEventDescription: eventDescriptions[Math.floor(Math.random() * eventDescriptions.length)],
    hasCounterAction: counterActionOptions[Math.floor(Math.random() * counterActionOptions.length)],
    counterActionDescription: counterActionDescriptions[Math.floor(Math.random() * counterActionDescriptions.length)],
    counterActionCost: costDescriptions[Math.floor(Math.random() * costDescriptions.length)],
    riskDescription: riskDescriptions[Math.floor(Math.random() * riskDescriptions.length)],
    riskProbability: Math.floor(Math.random() * 5) + 1,
    riskImpactLevel: Math.floor(Math.random() * 5) + 1,
    riskCalculation: calculationDescriptions[Math.floor(Math.random() * calculationDescriptions.length)],
    opportunityDescription: opportunityDescriptions[Math.floor(Math.random() * opportunityDescriptions.length)],
    opportunityProbability: Math.floor(Math.random() * 5) + 1,
    opportunityImpactLevel: Math.floor(Math.random() * 5) + 1,
    opportunityCalculation: calculationDescriptions[Math.floor(Math.random() * calculationDescriptions.length)],
    negativeImpactLevel: Math.floor(Math.random() * 5) + 1,
    negativeImpactDescription: negativeImpactDescriptions[Math.floor(Math.random() * negativeImpactDescriptions.length)],
    positiveImpactLevel: Math.floor(Math.random() * 5) + 1,
    positiveImpactDescription: positiveImpactDescriptions[Math.floor(Math.random() * positiveImpactDescriptions.length)]
  }
}

const fillTestData = () => {
  const testData = generateRandomTestData()

  // Fill all form data with random test data
  Object.keys(testData).forEach(key => {
    if (formData.value.hasOwnProperty(key)) {
      formData.value[key] = testData[key]
    }
  })

  // Show success message
  const toast = useToast()
  toast.add({
    title: '測試資料已填入',
    description: '所有表單欄位已自動填入隨機測試資料',
    color: 'green'
  })

  console.log('已填入測試資料:', testData)
}

// Hover text editing methods
const editHoverText = (sectionKey) => {
  editingSection.value = sectionKey
  editingHoverText.value = hoverTexts.value[sectionKey]
  showHoverEditModal.value = true
}

const saveHoverText = async () => {
  try {
    // Update local hover texts
    hoverTexts.value[editingSection.value] = editingHoverText.value

    // Prepare data for API call
    const hoverTextData = {}

    // Map section keys to database field names
    const sectionToFieldMap = {
      'E1': 'e1_info',
      'F1': 'f1_info',
      'G1': 'g1_info',
      'H1': 'h1_info'
    }

    const fieldName = sectionToFieldMap[editingSection.value]
    if (fieldName) {
      hoverTextData[fieldName] = editingHoverText.value

      // Save to database via API
      const response = await apiClient.contents.update(templateId, contentId, hoverTextData)

      if (response.success) {
        console.log(`✅ ${editingSection.value} 資訊圖示文字已儲存`)
      } else {
        console.error('❌ 儲存資訊圖示文字失敗:', response.error)
        // Revert local change if API call failed
        hoverTexts.value[editingSection.value] = hoverTexts.value[editingSection.value] || ''
      }
    }

    // Close modal
    showHoverEditModal.value = false
    editingSection.value = ''
    editingHoverText.value = ''

  } catch (error) {
    console.error('❌ 儲存資訊圖示文字時發生錯誤:', error)
    // Revert local change if error occurred
    hoverTexts.value[editingSection.value] = hoverTexts.value[editingSection.value] || ''
  }
}

const cancelHoverEdit = () => {
  showHoverEditModal.value = false
  editingSection.value = ''
  editingHoverText.value = ''
}

// Save probability scale data
const saveProbabilityScale = async () => {
  try {
    console.log('=== Saving Probability Scale ===')
    console.log('Columns to save:', probabilityScaleColumns.value)
    console.log('Rows to save:', probabilityScaleRows.value)

    // Use composable's method to prepare data
    const scaleData = prepareScaleDataForSubmission()

    // 儲存可能性量表
    const probabilityResponse = await apiClient.request(
      `/templates/${templateId}/scales/probability`,
      {
        method: 'POST',
        body: {
          columns: scaleData.probability_scale.columns,
          rows: scaleData.probability_scale.rows,
          descriptionText: scaleData.probability_scale.description_text,
          showDescriptionText: scaleData.probability_scale.show_description,
          selectedDisplayColumn: scaleData.probability_scale.selected_display_column
        }
      }
    )

    console.log('Probability scale save response:', probabilityResponse)

    // 儲存財務衝擊量表
    const impactResponse = await apiClient.request(
      `/templates/${templateId}/scales/impact`,
      {
        method: 'POST',
        body: {
          columns: scaleData.impact_scale.columns,
          rows: scaleData.impact_scale.rows,
          descriptionText: scaleData.impact_scale.description_text,
          showDescriptionText: scaleData.impact_scale.show_description,
          selectedDisplayColumn: scaleData.impact_scale.selected_display_column
        }
      }
    )

    console.log('量表儲存成功:', {
      probability: probabilityResponse.data,
      impact: impactResponse.data
    })

    showProbabilityScaleModal.value = false

    if (window.$swal) {
      window.$swal.success('成功', '可能性量表和財務衝擊量表已儲存')
    }
  } catch (error) {
    console.error('儲存量表失敗:', error)
    if (window.$swal) {
      window.$swal.error('錯誤', '儲存失敗，請稍後再試')
    }
  }
}

// Load scale data from backend
const loadScaleData = async () => {
  isLoadingScales.value = true
  try {
    // 載入可能性量表
    const probabilityResponse = await apiClient.request(
      `/templates/${templateId}/scales/probability`,
      {
        method: 'GET'
      }
    )

    // 載入財務衝擊量表
    const impactResponse = await apiClient.request(
      `/templates/${templateId}/scales/impact`,
      {
        method: 'GET'
      }
    )

    // Use composable's loadScalesData to process the response
    const scalesData = {
      probability_scale: probabilityResponse.success ? probabilityResponse.data : null,
      impact_scale: impactResponse.success ? impactResponse.data : null
    }

    loadScalesData(scalesData)

    console.log('=== Scale Data Loaded Successfully ===')
    console.log('Probability Columns:', probabilityScaleColumns.value)
    console.log('Probability Rows:', probabilityScaleRows.value)
    console.log('Selected Display Column:', selectedProbabilityDisplayColumn.value)
    console.log('Impact Columns:', impactScaleColumns.value)
    console.log('Impact Rows:', impactScaleRows.value)
  } catch (error) {
    console.error('載入量表資料失敗:', error)
  } finally {
    isLoadingScales.value = false
  }
}

// Methods
const goBack = () => {
  router.push(`/admin/risk-assessment/templates/${templateId}/content`)
}

const goToPreview = () => {
  // Navigate to preview page with current form data as query parameters
  const previewQuery = {
    riskFactorDescription: formData.value.riskFactorDescription,
    referenceText: formData.value.referenceText,
    riskEventDescription: formData.value.riskEventDescription,
    counterActionDescription: formData.value.counterActionDescription,
    counterActionCost: formData.value.counterActionCost,
    riskDescription: formData.value.riskDescription,
    riskCalculation: formData.value.riskCalculation,
    opportunityDescription: formData.value.opportunityDescription,
    opportunityCalculation: formData.value.opportunityCalculation,
    negativeImpactDescription: formData.value.negativeImpactDescription,
    positiveImpactDescription: formData.value.positiveImpactDescription,
    // Add radio button values for C and D sections
    riskEventChoice: formData.value.hasRiskEvent,
    counterActionChoice: formData.value.hasCounterAction
  }

  router.push({
    path: `/admin/risk-assessment/templates/edit/${templateId}-${contentId}-preview`,
    query: previewQuery
  })
}

const saveQuestion = async () => {
  // 防止重複點擊
  if (isSaving.value) return

  // 設定儲存狀態
  isSaving.value = true

  // 立即顯示儲存中的 SweetAlert（暫時註解以修復載入問題）
  // const Swal = (await import('sweetalert2')).default
  // Swal.fire({
  //   title: '儲存中...',
  //   text: '正在處理您的題目資料',
  //   icon: 'info',
  //   allowOutsideClick: false,
  //   allowEscapeKey: false,
  //   showConfirmButton: false,
  //   didOpen: () => {
  //     Swal.showLoading()
  //   }
  // })
  console.log('儲存中...正在處理您的題目資料')

  try {
    // 確認富文本編輯器內容為HTML格式
    const htmlContent = formData.value.riskFactorDescription
    console.log('=== 送出資料確認 ===')
    console.log('1. 富文本編輯器內容格式: HTML')
    console.log('2. HTML內容長度:', htmlContent.length, '字元')
    console.log('3. 是否包含HTML標籤:', /<[^>]+>/.test(htmlContent))
    console.log('4. 實際HTML內容預覽:')
    console.log(htmlContent)

    // 先取得原本的資料，不包含會覆蓋的欄位
    const { description: originalDescription, ...existingData } = questionData.value || {}

    // Update the content with ESG question details
    const updatedContent = {
      // 先載入所有原本的資料
      ...existingData,

      // 保留原本的 description（絕對不覆蓋用戶在content頁面輸入的描述）
      description: originalDescription || '風險評估內容',

      // 後端期望的欄位名稱（camelCase）
      categoryId: basicInfo.value.categoryId ? parseInt(basicInfo.value.categoryId) : null,
      topicId: riskTopicsEnabled.value ? (basicInfo.value.topicId ? parseInt(basicInfo.value.topicId) : null) : null,
      riskFactorId: basicInfo.value.riskFactorId ? parseInt(basicInfo.value.riskFactorId) : null,
      is_required: basicInfo.value.isRequired,

      // Update with form data
      risk_factor_description: htmlContent, // 確認為HTML格式
      reference_text: formData.value.referenceText,
      has_risk_event: formData.value.hasRiskEvent,
      risk_event_description: formData.value.riskEventDescription,
      has_counter_action: formData.value.hasCounterAction,
      counter_action_description: formData.value.counterActionDescription,
      counter_action_cost: formData.value.counterActionCost,
      risk_description: formData.value.riskDescription,
      risk_probability: formData.value.riskProbability,
      risk_impact_level: formData.value.riskImpactLevel,
      risk_calculation: formData.value.riskCalculation,
      opportunity_description: formData.value.opportunityDescription,
      opportunity_probability: formData.value.opportunityProbability,
      opportunity_impact_level: formData.value.opportunityImpactLevel,
      opportunity_calculation: formData.value.opportunityCalculation,
      negative_impact_level: formData.value.negativeImpactLevel,
      negative_impact_description: formData.value.negativeImpactDescription,
      positive_impact_level: formData.value.positiveImpactLevel,
      positive_impact_description: formData.value.positiveImpactDescription,
      // 新增的題目欄位對應
      a_content: htmlContent, // Section A 內容
      b_content: formData.value.referenceText, // Section B 內容
      c_placeholder: formData.value.riskEventDescription, // Section C 占位符
      d_placeholder_1: formData.value.counterActionDescription, // Section D 占位符 1
      d_placeholder_2: formData.value.counterActionCost, // Section D 占位符 2
      e1_placeholder_1: formData.value.riskDescription, // Section E1 占位符 1
      // E-2 now uses dedicated fields
      e2_select_1: formData.value.riskProbability.toString(), // Section E2 選擇項 1
      e2_select_2: formData.value.riskImpactLevel.toString(), // Section E2 選擇項 2
      e2_placeholder: formData.value.riskCalculation, // Section E2 占位符
      // 資訊圖示提示文字
      e1_info: hoverTexts.value.E1, // E-1 資訊圖示提示文字
      f1_info: hoverTexts.value.F1, // F-1 資訊圖示提示文字
      g1_info: hoverTexts.value.G1, // G-1 資訊圖示提示文字
      h1_info: hoverTexts.value.H1  // H-1 資訊圖示提示文字
    }

    console.log('5. 完整送出資料:', updatedContent)
    console.log('6. description 欄位值:', updatedContent.description)
    console.log('7. originalDescription 值:', originalDescription)
    console.log('8. questionData 完整內容:', questionData.value)
    console.log('9. basicInfo description:', basicInfo.value.description)

    // Update through store
    await templatesStore.updateTemplateContent(templateId, contentId, updatedContent)

    // 儲存成功，顯示完成 SweetAlert（1.5秒後自動跳轉）
    // Swal.fire({
    //   title: '儲存完成！',
    //   text: 'ESG評估題目已成功儲存',
    //   icon: 'success',
    //   showConfirmButton: false,
    //   timer: 1500,
    //   timerProgressBar: true
    // })
    console.log('儲存完成！ESG評估題目已成功儲存')

    // 1.5秒後自動跳轉
    setTimeout(() => {
      goBack()
    }, 1500)

  } catch (error) {
    console.error('儲存題目時發生錯誤:', error)

    // 儲存失敗，顯示錯誤 SweetAlert（不需要確認按鈕）
    // Swal.fire({
    //   title: '儲存失敗！',
    //   text: '發生錯誤，請稍後再試',
    //   icon: 'error',
    //   showConfirmButton: false,
    //   timer: 3000,
    //   timerProgressBar: true
    // })
    console.error('儲存失敗！發生錯誤，請稍後再試')
  } finally {
    // 無論成功或失敗，都重置儲存狀態
    isSaving.value = false
  }
}

// Lifecycle
onMounted(async () => {
  console.log('Template ESG Question editing page mounted for template:', templateId, 'content:', contentId)

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

  // Ensure risk categories are loaded
  if (!riskCategories.value || riskCategories.value.length === 0) {
    try {
      await templatesStore.fetchRiskCategories(templateId)
    } catch (error) {
      console.error('Failed to fetch risk categories:', error)
    }
  }

  // Load topics and factors if they are already selected in the content
  const templateContentList = templatesStore.getTemplateContent(templateId)
  const currentContent = templateContentList.value?.find(item => item.id === contentId)

  if (currentContent) {
    const isTopicsEnabled = templateInfo.value?.risk_topics_enabled ?? true

    if (isTopicsEnabled) {
      // Load topics for the selected category if topics are enabled
      if (currentContent.category_id) {
        try {
          await templatesStore.fetchRiskTopics(templateId, { category_id: currentContent.category_id })
        } catch (error) {
          console.error('Failed to fetch topics for existing category:', error)
        }
      }

      // Load factors for the selected topic
      if (currentContent.topic_id) {
        try {
          await templatesStore.fetchRiskFactors(templateId, { topic_id: currentContent.topic_id })
        } catch (error) {
          console.error('Failed to fetch factors for existing topic:', error)
        }
      }
    } else {
      // Load factors directly for the category if topics are disabled
      if (currentContent.category_id) {
        try {
          await templatesStore.fetchRiskFactors(templateId, { category_id: currentContent.category_id })
        } catch (error) {
          console.error('Failed to fetch factors for existing category:', error)
        }
      }
    }
  }

  // 再次確保使用正確的風險因子描述（從 template_contents 載入）
  if (selectedRiskFactorDescription.value) {
    console.log('更新 formData 使用 template_contents 的描述:', selectedRiskFactorDescription.value)
    formData.value.riskFactorDescription = selectedRiskFactorDescription.value
  } else if (!formData.value.riskFactorDescription) {
    // 只有在沒有任何內容時才使用預設值
    formData.value.riskFactorDescription = '企業的營運往往高度依賴自然資源，如水資源、石油、天然氣、動植物資源、海洋魚類供給、土壤、植木等、隨著氣候變遷、生態退化與資源稀缺問題日益嚴峻，若企業未能妥善管理資源使用環境衝擊，可能面臨供應中斷、成本上升與合規壓力等風險。同時，過度依賴自然資源或可生產活動衝擊，亦可能引發社會關注與質疑指稱。'
  }

  // Load scale data for E-1 and F-1 dropdown options
  await loadScaleData()
})

// Watch for modal open to reload scale data
watch(() => showProbabilityScaleModal.value, (newValue) => {
  if (newValue) {
    // Reload scale data when modal opens to ensure fresh data
    loadScaleData()
  }
})

// Watch for changes in selected risk factor description to update the rich text editor
watch(selectedRiskFactorDescription, (newDescription) => {
  console.log('selectedRiskFactorDescription 變化:', newDescription)
  console.log('當前 basicInfo.riskFactorId:', basicInfo.value.riskFactorId)
  console.log('當前 riskFactors:', riskFactors.value)

  if (newDescription && newDescription.trim()) {
    console.log('更新富文本編輯器內容:', newDescription)
    formData.value.riskFactorDescription = newDescription
  }
}, { immediate: true })

// 移除原本的 riskFactors watch，因為我們只需要使用 template_contents.description
// risk_factors.description 和 template_contents.description 是完全不相干的東西
</script>

<style scoped>
.assessment-rounded {
  @apply rounded-2xl;
}

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

.border-red-300 {
  border-color: #fca5a5;
}

.text-blue-600 {
  color: #2563eb;
}

/* Smooth transitions for expandable sections */
.expand-enter-active,
.expand-leave-active {
  transition: all 0.3s ease;
  overflow: hidden;
}

.expand-enter-from,
.expand-leave-to {
  max-height: 0;
  opacity: 0;
}

.expand-enter-to,
.expand-leave-from {
  max-height: 500px;
  opacity: 1;
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

.radio-card-option.selected.radio-card-yes {
  @apply border-red-500 shadow-lg;
}

.radio-card-option.selected.radio-card-no {
  @apply border-green-500 shadow-lg;
}

.radio-card-content {
  @apply flex items-center justify-center space-x-3;
}

.radio-card-icon {
  @apply w-7 h-7 flex items-center justify-center rounded-full;
  @apply transition-all duration-200 border;
}

.radio-card-option:not(.selected) .radio-card-icon {
  @apply bg-gray-100 border-gray-300 text-gray-500;
}

.radio-card-option.selected.radio-card-yes .radio-card-icon {
  @apply bg-red-50 border-red-500 text-red-600;
}

.radio-card-option.selected.radio-card-no .radio-card-icon {
  @apply bg-green-50 border-green-500 text-green-600;
}

.radio-card-text {
  @apply text-lg font-semibold;
}

.radio-card-option:not(.selected) .radio-card-text {
  @apply text-gray-700;
}

.radio-card-option.selected.radio-card-yes .radio-card-text {
  @apply text-red-600;
}

.radio-card-option.selected.radio-card-no .radio-card-text {
  @apply text-green-600;
}

/* Hover 圖示顏色變化 */
.radio-card-option.radio-card-yes:hover:not(.selected) .radio-card-icon {
  @apply bg-red-50 border-red-400 text-red-500;
}

.radio-card-option.radio-card-no:hover:not(.selected) .radio-card-icon {
  @apply bg-green-50 border-green-400 text-green-500;
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