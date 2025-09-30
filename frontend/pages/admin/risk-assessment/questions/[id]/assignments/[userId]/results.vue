<template>
  <div class="p-6">
    <!-- Page Header -->
    <div class="mb-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <button
            @click="$router.back()"
            class="p-2 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors duration-200"
          >
            <ArrowLeftIcon class="w-5 h-5" />
          </button>
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">填寫結果詳情</h1>
            <p v-if="userInfo && assessmentInfo" class="text-gray-600 dark:text-gray-400">
              {{ userInfo.user_name }} - {{ assessmentInfo.templateVersion }} ({{ assessmentInfo.year }}年)
            </p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center space-x-2">
          <!-- Save Button -->
          <button
            @click="saveData"
            :disabled="saving || loading"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center space-x-2"
          >
            <svg v-if="saving" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ saving ? '儲存中...' : '儲存' }}</span>
          </button>

          <!-- Refresh Button -->
          <div class="relative group">
            <button
              @click="refreshData"
              :disabled="loading"
              :class="{
                'p-2 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors duration-200': !loading,
                'p-2 text-gray-400 dark:text-gray-600 cursor-not-allowed rounded-lg': loading
              }"
            >
              <ArrowPathIcon :class="['w-5 h-5', { 'animate-spin': loading }]" />
            </button>
            <div class="absolute bottom-full right-0 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
              重新整理
              <div class="absolute top-full right-2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
      <span class="ml-3 text-gray-600 dark:text-gray-400">載入中...</span>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800 dark:text-red-200">載入失敗</h3>
          <div class="mt-2 text-sm text-red-700 dark:text-red-300">
            <p>{{ error }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Results Content -->
    <div v-else-if="resultData" class="min-h-screen bg-gray-50 dark:bg-gray-900 -m-6 p-6">
      <!-- Main Content -->
      <div class="w-full px-4 sm:px-6 lg:px-8 py-8">

        <!-- Page Title Section -->
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">風險評估作答檢視</h1>
          <p class="mt-1 text-base text-gray-500 dark:text-gray-400">
            {{ resultData.title || '未命名題目' }} - 已填寫內容檢視模式
          </p>
        </div>

        <!-- User-Facing Content -->
        <div class="space-y-6">

          <!-- Section A: 風險因子議題描述 (可收折) -->
          <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <button
              @click="toggleSection('sectionA')"
              class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 dark:hover:bg-gray-700"
            >
              <div class="flex items-center space-x-3">
                <span class="font-bold text-gray-900 dark:text-white text-xl">風險因子議題描述</span>
                <span class="bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-200 px-2 py-1 rounded text-base font-medium">A</span>
              </div>
              <ChevronUpIcon v-if="expandedSections.sectionA" class="w-5 h-5 text-gray-400" />
              <ChevronDownIcon v-else class="w-5 h-5 text-gray-400" />
            </button>
            <div v-show="expandedSections.sectionA" class="px-6 pb-6">
              <div class="mb-3">
                <div class="border-t-2 border-gray-300 dark:border-gray-600 mb-4 rounded-full"></div>
              </div>
              <!-- 顯示富文本內容，HTML格式 -->
              <div class="prose max-w-none text-gray-700 dark:text-gray-300" v-html="responseData.riskFactorDescription || '測試風險因子描述002 - 這是一個關於環境風險的評估題目，主要探討公司在氣候變遷影響下的適應能力。'">
              </div>
            </div>
          </div>

          <!-- Section B: 參考文字&模組工具評估結果 (可收折) -->
          <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <button
              @click="toggleSection('sectionB')"
              class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 dark:hover:bg-gray-700"
            >
              <div class="flex items-center space-x-3">
                <span class="font-bold text-gray-900 dark:text-white text-xl">參考文字&模組工具評估結果</span>
                <span class="bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-200 px-2 py-1 rounded text-base font-medium">B</span>
              </div>
              <ChevronUpIcon v-if="expandedSections.sectionB" class="w-5 h-5 text-gray-400" />
              <ChevronDownIcon v-else class="w-5 h-5 text-gray-400" />
            </button>
            <div v-show="expandedSections.sectionB" class="px-6 pb-6">
              <div class="mb-3">
                <div class="border-t-2 border-gray-300 dark:border-gray-600 mb-4 rounded-full"></div>
              </div>
              <!-- 顯示富文本內容，HTML格式 -->
              <div class="prose max-w-none text-gray-700 dark:text-gray-300" v-html="responseData.referenceText || '根據TCFD框架，企業應評估氣候相關的風險與機會。參考最新的科學報告和政策發展。'">
              </div>
            </div>
          </div>

          <!-- Section C: 公司報導年度是否有發生實際風險/負面衝擊事件 -->
          <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-6 rounded-2xl">
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center space-x-3">
                <span class="font-bold text-gray-900 dark:text-white text-xl">公司報導年度是否有發生實際風險/負面衝擊事件</span>
                <span class="bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-200 px-2 py-1 rounded text-base font-medium">C</span>
              </div>
            </div>
            <div class="space-y-4">
              <!-- 互動式 Radio 選項 (可點擊) -->
              <div class="grid grid-cols-2 gap-6">
                <div
                  @click="responseData.riskEventChoice = 'yes'"
                  class="radio-card-option radio-card-no cursor-pointer"
                  :class="{ 'selected': responseData.riskEventChoice === 'yes' }"
                >
                  <div class="radio-card-content">
                    <div class="radio-card-icon">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                    </div>
                    <span class="radio-card-text">是</span>
                  </div>
                </div>
                <div
                  @click="responseData.riskEventChoice = 'no'"
                  class="radio-card-option radio-card-yes cursor-pointer"
                  :class="{ 'selected': responseData.riskEventChoice === 'no' }"
                >
                  <div class="radio-card-content">
                    <div class="radio-card-icon">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                      </svg>
                    </div>
                    <span class="radio-card-text">否</span>
                  </div>
                </div>
              </div>
              <div>
                <label class="text-gray-600 dark:text-gray-400 mt-6 mb-1">*請描述</label>
                <textarea
                  v-model="responseData.riskEventDescription"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-2xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="請描述風險/負面衝擊事件"
                ></textarea>
              </div>
            </div>
          </div>

          <!-- Section D: 公司報導年度是否有相關對應作為 -->
          <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-6 rounded-2xl">
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center space-x-3">
                <span class="font-bold text-gray-900 dark:text-white text-xl">公司報導年度是否有相關對應作為</span>
                <span class="bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-200 px-2 py-1 rounded text-base font-medium">D</span>
              </div>
            </div>
            <div class="space-y-4">
              <!-- 互動式 Radio 選項 (可點擊) -->
              <div class="grid grid-cols-2 gap-6">
                <div
                  @click="responseData.counterActionChoice = 'yes'"
                  class="radio-card-option radio-card-no cursor-pointer"
                  :class="{ 'selected': responseData.counterActionChoice === 'yes' }"
                >
                  <div class="radio-card-content">
                    <div class="radio-card-icon">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                    </div>
                    <span class="radio-card-text">是</span>
                  </div>
                </div>
                <div
                  @click="responseData.counterActionChoice = 'no'"
                  class="radio-card-option radio-card-yes cursor-pointer"
                  :class="{ 'selected': responseData.counterActionChoice === 'no' }"
                >
                  <div class="radio-card-content">
                    <div class="radio-card-icon">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                      </svg>
                    </div>
                    <span class="radio-card-text">否</span>
                  </div>
                </div>
              </div>
              <div>
                <label class="text-gray-600 dark:text-gray-400 mt-6 mb-1">*請描述</label>
                <textarea
                  v-model="responseData.counterActionDescription"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-2xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="請描述相關對應作為"
                ></textarea>
              </div>
              <div>
                <label class="text-gray-600 dark:text-gray-400 mt-6 mb-1">*上述對策費用</label>
                <input
                  v-model="responseData.counterActionCost"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-2xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="請輸入對策費用"
                />
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
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-6 rounded-2xl">
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                  <span class="font-bold text-gray-900 dark:text-white text-xl">相關風險</span>
                  <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 px-2 py-1 rounded text-base font-medium flex items-center">E-1</span>
                  <div
                    class="relative group w-5 h-5 ml-2 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-serif font-bold cursor-pointer hover:scale-110 transition-transform duration-200"
                  >
                    <span class="italic">i</span>
                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-800 dark:bg-gray-700 text-white text-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-20">
                      相關風險說明：企業面臨的風險評估相關資訊
                      <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-800 dark:border-t-gray-700"></div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="space-y-4">
                <p class="text-base text-gray-600 dark:text-gray-400">公司未來潛在相關風險營清說明，未來潛在風險（收入減少）、費用增加於損益</p>
                <div>
                  <label class="text-gray-600 dark:text-gray-400 mt-6 mb-1">風險描述</label>
                  <textarea
                    v-model="responseData.riskDescription"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-2xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="請描述風險"
                  ></textarea>
                </div>
                <div class="border border-gray-300 dark:border-gray-600 rounded-2xl p-4 space-y-3">
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="block text-base text-gray-600 dark:text-gray-400 mb-1">*風險發生可能性</label>
                      <select
                        v-model="responseData.riskProbability"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-2xl px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      >
                        <option value="">請選擇</option>
                        <option value="very-low">極低 (1-5%)</option>
                        <option value="low">低 (6-25%)</option>
                        <option value="medium">中等 (26-50%)</option>
                        <option value="high">高 (51-75%)</option>
                        <option value="very-high">極高 (76-100%)</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-base text-gray-600 dark:text-gray-400 mb-1">*風險發生衝擊程度</label>
                      <select
                        v-model="responseData.riskImpact"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-2xl px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      >
                        <option value="">請選擇</option>
                        <option value="very-low">極低影響</option>
                        <option value="low">低影響</option>
                        <option value="medium">中等影響</option>
                        <option value="high">高影響</option>
                        <option value="very-high">極高影響</option>
                      </select>
                    </div>
                  </div>
                  <div>
                    <label class="flex items-center text-base text-gray-600 dark:text-gray-400 mb-1">*計算說明</label>
                    <textarea
                      v-model="responseData.riskCalculation"
                      rows="3"
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-2xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="請說明計算方式"
                    ></textarea>
                  </div>
                </div>
              </div>
            </div>

            <!-- Section F-1: 相關機會 -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-6 rounded-2xl">
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                  <span class="font-bold text-gray-900 dark:text-white text-xl">相關機會</span>
                  <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 px-2 py-1 rounded text-base font-medium flex items-center">F-1</span>
                  <div
                    class="relative group w-5 h-5 ml-2 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-serif font-bold cursor-pointer hover:scale-110 transition-transform duration-200"
                  >
                    <span class="italic">i</span>
                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-800 dark:bg-gray-700 text-white text-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-20">
                      相關機會說明：企業可能的機會評估相關資訊
                      <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-800 dark:border-t-gray-700"></div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="space-y-4">
                <p class="text-base text-gray-600 dark:text-gray-400">公司未來潛在相關機會營清說明，未來潛在機會（收入增加）、費用減少於收益等不會定</p>
                <div>
                  <label class="text-gray-600 dark:text-gray-400 mt-6 mb-1">機會描述</label>
                  <textarea
                    v-model="responseData.opportunityDescription"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-2xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="請描述機會"
                  ></textarea>
                </div>
                <div class="border border-gray-300 dark:border-gray-600 rounded-2xl p-4 space-y-3">
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="block text-base text-gray-600 dark:text-gray-400 mb-1">*機會發生可能性</label>
                      <select
                        v-model="responseData.opportunityProbability"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-2xl px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      >
                        <option value="">請選擇</option>
                        <option value="very-low">極低 (1-5%)</option>
                        <option value="low">低 (6-25%)</option>
                        <option value="medium">中等 (26-50%)</option>
                        <option value="high">高 (51-75%)</option>
                        <option value="very-high">極高 (76-100%)</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-base text-gray-600 dark:text-gray-400 mb-1">*機會發生衝擊程度</label>
                      <select
                        v-model="responseData.opportunityImpact"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-2xl px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      >
                        <option value="">請選擇</option>
                        <option value="very-low">極低影響</option>
                        <option value="low">低影響</option>
                        <option value="medium">中等影響</option>
                        <option value="high">高影響</option>
                        <option value="very-high">極高影響</option>
                      </select>
                    </div>
                  </div>
                  <div>
                    <label class="flex items-center text-base text-gray-600 dark:text-gray-400 mb-1">*計算說明</label>
                    <textarea
                      v-model="responseData.opportunityCalculation"
                      rows="3"
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-2xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="請說明計算方式"
                    ></textarea>
                  </div>
                </div>
              </div>
            </div>

            <!-- Section G-1: 對外負面衝擊 -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-6 rounded-2xl">
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                  <span class="font-bold text-gray-900 dark:text-white text-xl">對外負面衝擊</span>
                  <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 px-2 py-1 rounded text-base font-medium flex items-center">G-1</span>
                  <div
                    class="relative group w-5 h-5 ml-2 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-serif font-bold cursor-pointer hover:scale-110 transition-transform duration-200"
                  >
                    <span class="italic">i</span>
                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-800 dark:bg-gray-700 text-white text-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-20">
                      對外負面衝擊說明：企業對外部環境可能造成的負面影響
                      <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-800 dark:border-t-gray-700"></div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="space-y-4">
                <div>
                  <label class="block text-base text-gray-600 dark:text-gray-400 mb-1">負面衝擊程度</label>
                  <select
                    v-model="responseData.negativeImpactLevel"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-2xl px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  >
                    <option value="">請選擇</option>
                    <option value="level-1">等級1 - 極輕微</option>
                    <option value="level-2">等級2 - 輕微</option>
                    <option value="level-3">等級3 - 中等</option>
                    <option value="level-4">等級4 - 嚴重</option>
                    <option value="level-5">等級5 - 極嚴重</option>
                  </select>
                </div>
                <div>
                  <label class="block text-base text-gray-600 dark:text-gray-400 mb-1">評分說明</label>
                  <textarea
                    v-model="responseData.negativeImpactDescription"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-2xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="請說明負面衝擊"
                  ></textarea>
                </div>
              </div>
            </div>

            <!-- Section H-1: 對外正面影響 -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-6 rounded-2xl">
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                  <span class="font-bold text-gray-900 dark:text-white text-xl">對外正面影響</span>
                  <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 px-2 py-1 rounded text-base font-medium flex items-center">H-1</span>
                  <div
                    class="relative group w-5 h-5 ml-2 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-serif font-bold cursor-pointer hover:scale-110 transition-transform duration-200"
                  >
                    <span class="italic">i</span>
                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-800 dark:bg-gray-700 text-white text-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-20">
                      對外正面影響說明：企業對外部環境可能產生的正面影響
                      <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-800 dark:border-t-gray-700"></div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="space-y-4">
                <div>
                  <label class="block text-base text-gray-600 dark:text-gray-400 mb-1">正面影響程度</label>
                  <select
                    v-model="responseData.positiveImpactLevel"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-2xl px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  >
                    <option value="">請選擇</option>
                    <option value="level-1">等級1 - 極輕微</option>
                    <option value="level-2">等級2 - 輕微</option>
                    <option value="level-3">等級3 - 中等</option>
                    <option value="level-4">等級4 - 嚴重</option>
                    <option value="level-5">等級5 - 極嚴重</option>
                  </select>
                </div>
                <div>
                  <label class="block text-base text-gray-600 dark:text-gray-400 mb-1">評分說明</label>
                  <textarea
                    v-model="responseData.positiveImpactDescription"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-2xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="請說明正面影響"
                  ></textarea>
                </div>
              </div>
            </div>
          </div>

          <!-- Response Info -->
          <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4 rounded-lg">
            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">填寫資訊</h4>
            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
              <span>填寫時間：{{ responseData.answered_at ? new Date(responseData.answered_at).toLocaleString('zh-TW') : '2024-09-29 22:30:00' }}</span>
              <span v-if="responseData.updated_at && responseData.updated_at !== responseData.answered_at">
                最後更新：{{ new Date(responseData.updated_at).toLocaleString('zh-TW') }}
              </span>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <div class="mx-auto h-12 w-12 text-gray-400">
        <DocumentIcon class="h-12 w-12" />
      </div>
      <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">沒有找到填寫結果</h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">該用戶尚未開始填寫此評估表</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  ArrowLeftIcon,
  ArrowPathIcon,
  DocumentIcon,
  ChevronUpIcon,
  ChevronDownIcon
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const error = ref(null)
const resultData = ref(null)
const userInfo = ref(null)
const assessmentInfo = ref(null)

// 選項對應表
const probabilityOptions = {
  'very-low': '極低 (1-5%)',
  'low': '低 (6-25%)',
  'medium': '中等 (26-50%)',
  'high': '高 (51-75%)',
  'very-high': '極高 (76-100%)'
}

const impactOptions = {
  'very-low': '極低影響',
  'low': '低影響',
  'medium': '中等影響',
  'high': '高影響',
  'very-high': '極高影響'
}

const impactLevelOptions = {
  'level-1': '等級1 - 極輕微',
  'level-2': '等級2 - 輕微',
  'level-3': '等級3 - 中等',
  'level-4': '等級4 - 嚴重',
  'level-5': '等級5 - 極嚴重'
}

// Expandable sections state
const expandedSections = ref({
  sectionA: true,
  sectionB: true
})

// Form data (editable)
const responseData = ref({
  riskFactorDescription: null,
  referenceText: null,
  riskEventChoice: 'yes',
  riskEventDescription: null,
  counterActionChoice: 'yes',
  counterActionDescription: null,
  counterActionCost: null,
  riskDescription: null,
  riskProbability: null,
  riskImpact: null,
  riskCalculation: null,
  opportunityDescription: null,
  opportunityProbability: null,
  opportunityImpact: null,
  opportunityCalculation: null,
  negativeImpactLevel: null,
  negativeImpactDescription: null,
  positiveImpactLevel: null,
  positiveImpactDescription: null,
  answered_at: null,
  updated_at: null
})

// 儲存狀態
const saving = ref(false)
const currentResponseId = ref(null)

// Toggle section function
const toggleSection = (section) => {
  expandedSections.value[section] = !expandedSections.value[section]
}

const loadResultData = async () => {
  try {
    loading.value = true
    error.value = null

    const assessmentId = route.params.id
    const userId = route.params.userId

    console.log('Loading result data for assessment:', assessmentId, 'user:', userId)

    // 從後端 API 取得真實資料
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
      resultData.value = null
      return
    }

    // 使用第一筆回答資料（單題模式）
    const firstResponse = responses[0]

    resultData.value = {
      title: firstResponse.factor_name || firstResponse.topic_name || '未命名題目',
      statistics: {
        completed_questions: responses.length,
        pending_questions: 0,
        completion_percentage: 100,
        last_updated: firstResponse.updated_at?.split(' ')[0]
      }
    }

    userInfo.value = {
      user_name: firstResponse.personnel_name || '填寫人員'
    }

    assessmentInfo.value = {
      templateVersion: firstResponse.category_name || '評估範本',
      year: new Date(firstResponse.created_at).getFullYear().toString()
    }

    // 從 response_fields 取得完整的回答資料
    const fields = firstResponse.response_fields || {}

    // 儲存 response ID 用於更新
    currentResponseId.value = firstResponse.id

    responseData.value = {
      riskFactorDescription: firstResponse.question_description || null,
      referenceText: null, // question_contents 中的 a_content 或 b_content
      riskEventChoice: fields.riskEventChoice || null,
      riskEventDescription: fields.riskEventDescription || null,
      counterActionChoice: fields.counterActionChoice || null,
      counterActionDescription: fields.counterActionDescription || null,
      counterActionCost: fields.counterActionCost || null,
      riskDescription: fields.riskDescription || null,
      riskProbability: fields.riskProbability || null,
      riskImpact: fields.riskImpact || null,
      riskCalculation: fields.riskCalculation || null,
      opportunityDescription: fields.opportunityDescription || null,
      opportunityProbability: fields.opportunityProbability || null,
      opportunityImpact: fields.opportunityImpact || null,
      opportunityCalculation: fields.opportunityCalculation || null,
      negativeImpactLevel: fields.negativeImpactLevel || null,
      negativeImpactDescription: fields.negativeImpactDescription || null,
      positiveImpactLevel: fields.positiveImpactLevel || null,
      positiveImpactDescription: fields.positiveImpactDescription || null,
      answered_at: firstResponse.answered_at || null,
      updated_at: firstResponse.updated_at || null
    }

    console.log('Result data loaded successfully from API')
  } catch (err) {
    console.error('載入填寫結果時發生錯誤:', err)
    error.value = err.message || '載入填寫結果時發生錯誤'
  } finally {
    loading.value = false
  }
}

const saveData = async () => {
  try {
    saving.value = true

    const assessmentId = route.params.id
    const userId = route.params.userId

    // 準備更新資料
    const updateData = {
      c_risk_event_choice: responseData.value.riskEventChoice,
      c_risk_event_description: responseData.value.riskEventDescription,
      d_counter_action_choice: responseData.value.counterActionChoice,
      d_counter_action_description: responseData.value.counterActionDescription,
      d_counter_action_cost: responseData.value.counterActionCost,
      e1_risk_description: responseData.value.riskDescription,
      e1_risk_probability: responseData.value.riskProbability,
      e1_risk_impact: responseData.value.riskImpact,
      e1_risk_calculation: responseData.value.riskCalculation,
      f1_opportunity_description: responseData.value.opportunityDescription,
      f1_opportunity_probability: responseData.value.opportunityProbability,
      f1_opportunity_impact: responseData.value.opportunityImpact,
      f1_opportunity_calculation: responseData.value.opportunityCalculation,
      g1_negative_impact_level: responseData.value.negativeImpactLevel,
      g1_negative_impact_description: responseData.value.negativeImpactDescription,
      h1_positive_impact_level: responseData.value.positiveImpactLevel,
      h1_positive_impact_description: responseData.value.positiveImpactDescription
    }

    // 呼叫更新 API
    const response = await $fetch(`/api/v1/question-management/responses/${currentResponseId.value}`, {
      method: 'PUT',
      body: updateData
    })

    if (!response.success) {
      throw new Error(response.message || '儲存失敗')
    }

    // 重新載入資料
    await loadResultData()

    // 顯示成功通知
    if (window.$swal) {
      window.$swal.success('成功', '資料已儲存')
    }
  } catch (err) {
    console.error('儲存資料時發生錯誤:', err)

    if (window.$swal) {
      window.$swal.error('錯誤', err.message || '儲存資料失敗')
    }
  } finally {
    saving.value = false
  }
}

const refreshData = async () => {
  try {
    await loadResultData()

    // 顯示成功通知
    if (window.$swal && !error.value) {
      window.$swal.success('成功', '資料已重新載入')
    }
  } catch (err) {
    console.error('重新載入資料時發生錯誤:', err)

    if (window.$swal) {
      window.$swal.error('錯誤', '重新載入資料失敗')
    }
  }
}

// Watch for route parameter changes and reload data
watch([() => route.params.id, () => route.params.userId], ([newId, newUserId], [oldId, oldUserId]) => {
  // Only reload if the parameters actually changed
  if ((newId !== oldId || newUserId !== oldUserId) && newId && newUserId) {
    console.log('Route parameters changed, reloading data:', { newId, newUserId, oldId, oldUserId })
    loadResultData()
  }
}, { immediate: false })

onMounted(() => {
  loadResultData()
})

// SEO - Dynamic title based on data
useHead(() => ({
  title: userInfo.value && assessmentInfo.value
    ? `${userInfo.value.user_name} - ${assessmentInfo.value.templateVersion} 填寫結果 - 風險評估管理系統`
    : '填寫結果詳情 - 風險評估管理系統'
}))
</script>

<style scoped>
/* Custom styling for the ESG form results view */
.bg-green-600 {
  background-color: #059669;
}

/* 精緻的卡片式 Radio 樣式 - 顯示選中狀態 */
.radio-card-option {
  @apply relative block border rounded-2xl px-4 py-3 transition-all duration-200 ease-in-out;
  @apply bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600;
}

.radio-card-option.radio-card-yes {
  @apply border-red-400 dark:border-red-500;
}

.radio-card-option.radio-card-no {
  @apply border-green-400 dark:border-green-500;
}

.radio-card-content {
  @apply flex items-center justify-center space-x-3;
}

.radio-card-icon {
  @apply w-7 h-7 flex items-center justify-center rounded-full;
  @apply transition-all duration-200 border;
  @apply bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400;
}

.radio-card-text {
  @apply text-lg font-semibold text-gray-700 dark:text-gray-300;
}

/* 選中狀態樣式 */
.radio-card-option.selected.radio-card-yes {
  @apply border-red-500 bg-red-50 dark:bg-red-900/20;
}

.radio-card-option.selected.radio-card-no {
  @apply border-green-500 bg-green-50 dark:bg-green-900/20;
}

.radio-card-option.selected .radio-card-icon {
  @apply bg-white dark:bg-gray-800 border-current;
}

.radio-card-option.selected.radio-card-yes .radio-card-icon {
  @apply text-red-600 dark:text-red-400;
}

.radio-card-option.selected.radio-card-no .radio-card-icon {
  @apply text-green-600 dark:text-green-400;
}

.radio-card-option.selected .radio-card-text {
  @apply text-current;
}

.radio-card-option.selected.radio-card-yes .radio-card-text {
  @apply text-red-700 dark:text-red-300;
}

.radio-card-option.selected.radio-card-no .radio-card-text {
  @apply text-green-700 dark:text-green-300;
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