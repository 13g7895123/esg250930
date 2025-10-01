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
              @click="goBack"
              class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-2xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200"
            >
              返回
            </button>
            <button
              @click="showPreview = true"
              class="px-4 py-2 text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/20 border border-blue-300 dark:border-blue-600 rounded-2xl hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors duration-200 hidden"
            >
              預覽
            </button>
            <button
              @click="saveQuestion"
              :disabled="!isFormValid"
              class="px-4 py-2 bg-green-600 text-white rounded-2xl hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors duration-200"
            >
              儲存題目
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

      <!-- Basic Question Info Section -->
      <div v-if="showBasicInfo" class="bg-white assessment-rounded border border-gray-200 p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">基本資訊</h2>

        <!-- Risk Hierarchy Selection -->
        <div :class="riskTopicsEnabled ? 'grid grid-cols-1 md:grid-cols-3 gap-6 mb-6' : 'grid grid-cols-1 md:grid-cols-2 gap-6 mb-6'">
          <div>
            <label class="block text-base font-medium text-gray-700 mb-2">
              風險類別 <span class="text-red-500">*</span>
            </label>
            <select
              v-model="basicInfo.categoryId"
              @change="onCategoryChange"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent"
            >
              <option value="">請選擇風險類別</option>
              <option
                v-for="category in riskCategories.value"
                :key="category.id"
                :value="category.id"
              >
                {{ category.category_name }}
              </option>
            </select>
          </div>

          <div v-if="riskTopicsEnabled">
            <label class="block text-base font-medium text-gray-700 mb-2">
              風險主題 <span class="text-red-500">*</span>
            </label>
            <select
              v-model="basicInfo.topicId"
              @change="onTopicChange"
              :disabled="!basicInfo.categoryId"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent disabled:bg-gray-100"
            >
              <option value="">請選擇風險主題</option>
              <option
                v-for="topic in riskTopics.value"
                :key="topic.id"
                :value="topic.id"
              >
                {{ topic.topic_name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-base font-medium text-gray-700 mb-2">
              風險因子 <span class="text-red-500">*</span>
            </label>
            <select
              v-model="basicInfo.riskFactorId"
              :disabled="riskTopicsEnabled ? !basicInfo.topicId : !basicInfo.categoryId"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent disabled:bg-gray-100"
            >
              <option value="">請選擇風險因子</option>
              <option
                v-for="factor in riskFactors.value"
                :key="factor.id"
                :value="factor.id"
              >
                {{ factor.factor_name }}
              </option>
            </select>
          </div>
        </div>

        <!-- Risk Factor Description (if selected) -->
        <div v-if="selectedRiskFactorDescription" class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-2xl">
          <h3 class="text-base font-medium text-blue-900 mb-2">風險因子描述</h3>
          <p class="text-blue-800 text-base" v-html="selectedRiskFactorDescription"></p>
        </div>

        <!-- Description and Required Status -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-base font-medium text-gray-700 mb-2">
              是否必填
            </label>
            <select
              v-model="basicInfo.isRequired"
              class="w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent"
            >
              <option :value="1">必填</option>
              <option :value="0">選填</option>
            </select>
          </div>
        </div>

        <div class="mt-4">
          <label class="block text-base font-medium text-gray-700 mb-2">
            題目描述 <span class="text-red-500">*</span>
          </label>
          <textarea
            v-model="basicInfo.description"
            rows="3"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent"
            placeholder="請輸入題目描述"
          />
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

            <!-- 使用富文本編輯器組件 -->
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
            v-if="probabilityScaleRows.length > 0 || impactScaleRows.length > 0"
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
                <InformationCircleIcon class="w-4 h-4 ml-1" />
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

              <div class="border border-gray-300 rounded-2xl p-4 space-y-3">
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
          </div>

          <!-- Section F-1: 相關機會 -->
          <div class="bg-white assessment-rounded border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center space-x-3">
                <span class="font_title">相關機會</span>
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-base font-medium flex items-center">
                  F-1
                </span>
                <InformationCircleIcon class="w-4 h-4 ml-1" />
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

              <div class="border border-gray-300 rounded-2xl p-4 space-y-3">
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
                <InformationCircleIcon class="w-4 h-4 ml-1" />
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
                <InformationCircleIcon class="w-4 h-4 ml-1" />
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
        :disabled="!isFormValid"
        class="flex items-center px-6 py-3 bg-green-600 text-white rounded-2xl shadow-lg hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-all duration-200 transform hover:scale-105"
      >
        <CheckIcon class="w-5 h-5 mr-2" />
        儲存題目
      </button>
    </div>
    
    <!-- Preview Modal -->
    <div v-if="showPreview" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" @click.self="showPreview = false">
      <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
        <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-xl font-semibold text-gray-900 dark:text-white">表單預覽</h2>
          <button @click="showPreview = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
          <div class="space-y-6">
            <!-- Basic Info -->
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-2xl">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">基本資訊</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-base">
                <div>
                  <span class="font-medium text-gray-600 dark:text-gray-300">描述：</span>
                  <span class="text-gray-900 dark:text-white">{{ questionData?.description || '未設定' }}</span>
                </div>
                <div>
                  <span class="font-medium text-gray-600 dark:text-gray-300">類別：</span>
                  <span class="text-gray-900 dark:text-white">{{ getCategoryName(questionData?.category_id) }}</span>
                </div>
              </div>
            </div>
            
            <!-- Section A -->
            <div class="border border-gray-200 dark:border-gray-600 rounded-2xl p-4">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">A. 風險因子議題描述</h3>
              <div class="text-base text-gray-700 dark:text-gray-300" v-html="formData.riskFactorDescription || '尚未填寫'">
              </div>
            </div>
            
            <!-- Section B -->
            <div class="border border-gray-200 dark:border-gray-600 rounded-2xl p-4">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">B. 參考文字&模組工具評估結果</h3>
              <div class="text-base text-gray-700 dark:text-gray-300">
                {{ formData.referenceText || '尚未填寫' }}
              </div>
            </div>
            
            <!-- Section C -->
            <div class="border border-gray-200 dark:border-gray-600 rounded-2xl p-4">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">C. 風險事件</h3>
              <div class="text-base text-gray-700 dark:text-gray-300 space-y-2">
                <div>
                  <span class="font-medium">是否有風險事件：</span>
                  <span class="ml-2 px-2 py-1 rounded text-xs" :class="formData.hasRiskEvent === 'yes' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'">
                    {{ formData.hasRiskEvent === 'yes' ? '是' : '否' }}
                  </span>
                </div>
                <div v-if="formData.hasRiskEvent === 'yes'">
                  <span class="font-medium">風險事件描述：</span>
                  <div class="mt-1">{{ formData.riskEventDescription || '尚未填寫' }}</div>
                </div>
              </div>
            </div>
            
            <!-- Section D -->
            <div class="border border-gray-200 dark:border-gray-600 rounded-2xl p-4">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">D. 因應措施</h3>
              <div class="text-base text-gray-700 dark:text-gray-300 space-y-2">
                <div>
                  <span class="font-medium">是否有因應措施：</span>
                  <span class="ml-2 px-2 py-1 rounded text-xs" :class="formData.hasCounterAction === 'yes' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                    {{ formData.hasCounterAction === 'yes' ? '是' : '否' }}
                  </span>
                </div>
                <div v-if="formData.hasCounterAction === 'yes'">
                  <span class="font-medium">因應措施描述：</span>
                  <div class="mt-1">{{ formData.counterActionDescription || '尚未填寫' }}</div>
                </div>
              </div>
            </div>
            
            <!-- Section E & F -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="border border-gray-200 dark:border-gray-600 rounded-2xl p-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">E. 風險評估</h3>
                <div class="text-base text-gray-700 dark:text-gray-300 space-y-2">
                  <div>機率等級：{{ formData.riskProbability }}</div>
                  <div>衝擊等級：{{ formData.riskImpactLevel }}</div>
                  <div class="mt-2">
                    <span class="font-medium">風險計算：</span>
                    <div class="mt-1">{{ formData.riskCalculation || '尚未填寫' }}</div>
                  </div>
                </div>
              </div>
              <div class="border border-gray-200 dark:border-gray-600 rounded-2xl p-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">F. 機會評估</h3>
                <div class="text-base text-gray-700 dark:text-gray-300 space-y-2">
                  <div>機率等級：{{ formData.opportunityProbability }}</div>
                  <div>衝擊等級：{{ formData.opportunityImpactLevel }}</div>
                  <div class="mt-2">
                    <span class="font-medium">機會計算：</span>
                    <div class="mt-1">{{ formData.opportunityCalculation || '尚未填寫' }}</div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Section G & H -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="border border-gray-200 dark:border-gray-600 rounded-2xl p-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">G. 負面衝擊</h3>
                <div class="text-base text-gray-700 dark:text-gray-300 space-y-2">
                  <div>衝擊等級：{{ formData.negativeImpactLevel }}</div>
                  <div class="mt-2">
                    <span class="font-medium">負面衝擊描述：</span>
                    <div class="mt-1">{{ formData.negativeImpactDescription || '尚未填寫' }}</div>
                  </div>
                </div>
              </div>
              <div class="border border-gray-200 dark:border-gray-600 rounded-2xl p-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">H. 正面衝擊</h3>
                <div class="text-base text-gray-700 dark:text-gray-300 space-y-2">
                  <div>衝擊等級：{{ formData.positiveImpactLevel }}</div>
                  <div class="mt-2">
                    <span class="font-medium">正面衝擊描述：</span>
                    <div class="mt-1">{{ formData.positiveImpactDescription || '尚未填寫' }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="flex justify-end p-6 border-t border-gray-200 dark:border-gray-700">
          <button @click="showPreview = false" class="px-4 py-2 bg-gray-600 text-white rounded-2xl hover:bg-gray-700 transition-colors duration-200">
            關閉
          </button>
        </div>
      </div>
    </div>


    <!-- Scale Editor Modal -->
    <ScaleEditorModal
      v-model="showProbabilityScaleModal"
      title="量表編輯"
      :is-loading="false"
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
      @save="saveQuestionScales"
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
import ScaleEditorModal from '~/components/Scale/ScaleEditorModal.vue'
import { useScaleManagement } from '~/composables/useScaleManagement'

definePageMeta({
  middleware: 'auth'
})

const route = useRoute()
const router = useRouter()

// Extract IDs from route params
// NOTE: Despite the param names (templateId/contentId), these actually represent:
// - templateId = assessmentId (評估記錄ID)
// - contentId = questionContentId (題項內容ID)
const routeParams = route.params
const assessmentId = parseInt(routeParams.templateId)  // Actually assessment ID
const questionContentId = parseInt(routeParams.contentId)  // Actually question content ID

usePageTitle('題目編輯')

// Reactive state
const showPreview = ref(false)
const showProbabilityScaleModal = ref(false)
const activeScaleTab = ref('probability')
const isLoading = ref(true)
const loadError = ref(null)

// Control visibility of basic info section - set to false to hide
const showBasicInfo = ref(false)

// Expandable sections state
const expandedSections = ref({
  sectionA: true,
  sectionB: false
})

// Load question data from API
const questionData = ref(null)
const riskCategories = ref([])
const riskTopics = ref([])
const riskFactors = ref([])

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

// Fetch question content data
const loadQuestionData = async () => {
  try {
    isLoading.value = true
    loadError.value = null

    // Fetch question content
    const contentResponse = await $fetch(`/api/v1/question-management/contents/${questionContentId}`)

    if (contentResponse.success && contentResponse.data?.content) {
      questionData.value = contentResponse.data.content

      // Load related structure data (categories, topics, factors) for this assessment
      const structureResponse = await $fetch(`/api/v1/question-management/assessment/${assessmentId}/structure`)

      if (structureResponse.success && structureResponse.data?.structure) {
        riskCategories.value = structureResponse.data.structure.categories || []
        riskTopics.value = structureResponse.data.structure.topics || []
        riskFactors.value = structureResponse.data.structure.factors || []
      }

      // Load scale data for this assessment
      try {
        const scalesResponse = await $fetch(`/api/v1/question-management/assessment/${assessmentId}/scales`)

        if (scalesResponse.success && scalesResponse.data) {
          // Use composable's loadScalesData to process the response
          loadScalesData(scalesResponse.data)

          console.log('=== Question Scale Data Loaded ===')
          console.log('Raw API Response:', scalesResponse.data)
          console.log('Probability Columns:', probabilityScaleColumns.value)
          console.log('Probability Rows:', probabilityScaleRows.value)
          console.log('Probability Row[0] dynamicFields:', probabilityScaleRows.value[0]?.dynamicFields)
          console.log('Selected Probability Display Column:', selectedProbabilityDisplayColumn.value)
          console.log('Impact Columns:', impactScaleColumns.value)
          console.log('Impact Rows:', impactScaleRows.value)
          console.log('Impact Row[0] dynamicFields:', impactScaleRows.value[0]?.dynamicFields)
          console.log('Selected Impact Display Column:', selectedImpactDisplayColumn.value)
        }
      } catch (scaleError) {
        console.warn('Failed to load scale data:', scaleError)
        // Scale data is optional, don't fail the whole page
      }
    } else {
      throw new Error('無法載入題項資料')
    }
  } catch (error) {
    console.error('Error loading question data:', error)
    loadError.value = error.message || '載入題項資料時發生錯誤'
  } finally {
    isLoading.value = false
  }
}

// Load data on mount
onMounted(() => {
  loadQuestionData()
})

// Get risk topics enabled state based on whether topics exist in the structure
const riskTopicsEnabled = computed(() => {
  return riskTopics.value && riskTopics.value.length > 0
})

// Get selected risk factor description
const selectedRiskFactorDescription = computed(() => {
  if (!basicInfo.value.riskFactorId) return null
  const factor = riskFactors.value?.find(f => f.id == basicInfo.value.riskFactorId)
  return factor?.description || factor?.factor_description || null
})

// Basic question info (description, category, etc.)
const basicInfo = ref({
  description: '',
  categoryId: '',
  topicId: '',
  riskFactorId: '',
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
  riskProbability: 1,
  riskImpactLevel: 1,
  riskCalculation: '',
  // Section F-1
  opportunityDescription: '',
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

// Initialize form data when question data is available
watch(questionData, (newData) => {
  if (newData) {
    // Initialize basic info
    basicInfo.value = {
      description: newData.description || '風險評估內容',
      categoryId: newData.category_id || '',
      topicId: newData.topic_id || '',
      riskFactorId: newData.risk_factor_id || '',
      isRequired: newData.is_required || 1
    }

    // Initialize with existing data or defaults
    // 先從新欄位讀取，如果沒有則使用舊欄位，最後才使用預設值
    formData.value = {
      riskFactorDescription: newData.a_content || newData.risk_factor_description || '企業的營運往往高度依賴自然資源，如水資源、石油、天然氣、動植物資源、海洋魚類供給、土壤、植木等、隨著氣候變遷、生態退化與資源稀缺問題日益嚴峻，若企業未能妥善管理資源使用環境衝擊，可能面臨供應中斷、成本上升與合規壓力等風險。同時，過度依賴自然資源或可生產活動衝擊，亦可能引發社會關注與質疑指稱。',
      referenceText: newData.b_content || newData.reference_text || '🔵去年報告書文字或第三方背景資料整理：<br>1. 台灣與生產據點的用水，皆不是韌水稀缺，對韌地區水資源缺乏威脅低，政府10個高溫期造成為高風險中高風險，並無缺點墊常落高風險之中<br>2. 政府推薦有七個生產據點位於水稀缺聯盟審查風險之地區，對當地自然環境具一定程度之衝擊<br><br>🔵可能思考之風險情境面：<br>1. 自然資源依賴性(對內)：缺乏水資源、石油、天然氣、動植物資源、海洋魚類供給、土壤、植木科集團之影響<br>2. 自然資源衝擊性(對外)：企業生產營運對自然資源之衝擊影響，例如果案圍7個生產據點對森林總藍海之衝擊影響<br><br>🔵參考文獻說明：<br>1. WWFBRF中的分析報告面，對於供應關的「水資源短缺」，致使10個高溫期造成為高風險中高風險，並無缺點墊常落高風險之中。<br>2. 供應鏈查訊於自然資源流之地區（例如水資源力區等）－豐等結果<br>3. WWFBRF資融關的之「生物多樣性壓力」中的「植木資業」具有高風險：致使圍、致使東業、致使東國、致使東業、油染危農州、油染危業等、溫染危業關',
      hasRiskEvent: newData.has_risk_event || 'no',
      riskEventDescription: newData.c_placeholder || newData.risk_event_description || '台灣與生產據點的用水，皆不是韌水稀缺，沒有韌點韌點水用微低的風險',
      hasCounterAction: newData.has_counter_action || 'yes',
      counterActionDescription: newData.d_placeholder_1 || newData.counter_action_description || '',
      counterActionCost: newData.d_placeholder_2 || newData.counter_action_cost || '',
      riskDescription: newData.e1_placeholder_1 || newData.risk_description || '',
      riskProbability: newData.e1_select_1 ? parseInt(newData.e1_select_1) : (newData.risk_probability || 1),
      riskImpactLevel: newData.e1_select_2 ? parseInt(newData.e1_select_2) : (newData.risk_impact_level || 1),
      riskCalculation: newData.e1_placeholder_2 || newData.risk_calculation || '致使相關風險、衝擊程度低',
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

// 可能性量表下拉選單選項
// Note: probabilityScaleOptions and impactScaleOptions are now provided by useScaleManagement composable

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

// ===== Scale editing functions =====
// Note: All scale editing functions are now provided by useScaleManagement composable
// Including: addProbabilityColumn, removeProbabilityColumn, addProbabilityRow, removeProbabilityRow
//           addImpactColumn, removeImpactColumn, addImpactRow, removeImpactRow
//           addProbabilityDescriptionText, removeProbabilityDescriptionText
//           addImpactDescriptionText, removeImpactDescriptionText

// Save scale data
const saveQuestionScales = async () => {
  try {
    const { $swal } = useNuxtApp()

    // Use composable's method to prepare data
    const scaleData = prepareScaleDataForSubmission()

    // Call API to save
    const response = await $fetch(`/api/v1/question-management/assessment/${assessmentId}/scales`, {
      method: 'PUT',
      body: scaleData
    })

    if (response.success) {
      await $swal.fire({
        icon: 'success',
        title: '系統提示',
        text: '量表已儲存',
        showConfirmButton: false,
        timer: 1500
      })
      showProbabilityScaleModal.value = false
    }
  } catch (error) {
    const { $swal } = useNuxtApp()
    await $swal.fire({
      icon: 'error',
      title: '系統提示',
      text: error.data?.message || '儲存量表時發生錯誤',
      showConfirmButton: false,
      timer: 1500
    })
  }
}

// Form validation
const isFormValid = computed(() => {
  const basicValidation = basicInfo.value.description.trim() &&
         basicInfo.value.categoryId &&
         basicInfo.value.riskFactorId &&
         formData.value.riskFactorDescription.trim() &&
         formData.value.hasRiskEvent &&
         formData.value.hasCounterAction

  // If risk topics are enabled, topic_id is required
  if (riskTopicsEnabled.value) {
    return basicValidation && basicInfo.value.topicId
  }

  // If risk topics are disabled, topic_id is not required
  return basicValidation
})

// Methods
const goBack = () => {
  // Go back to the question management page for this assessment
  router.push(`/admin/risk-assessment/questions/${assessmentId}/management`)
}

const saveQuestion = async () => {
  if (!isFormValid.value) return

  try {
    // 確認富文本編輯器內容為HTML格式
    const htmlContent = formData.value.riskFactorDescription
    console.log('=== 送出資料確認 ===')
    console.log('1. 富文本編輯器內容格式: HTML')
    console.log('2. HTML內容長度:', htmlContent.length, '字元')
    console.log('3. 是否包含HTML標籤:', /<[^>]+>/.test(htmlContent))
    console.log('4. 實際HTML內容預覽:')
    console.log(htmlContent)

    // 顯示HTML內容提示（開發用）
    const toast = useToast()
    toast.add({
      title: '資料格式確認',
      description: `將以HTML格式送出，內容長度: ${htmlContent.length} 字元`,
      color: 'green'
    })

    // Update the content with ESG question details
    const updatedContent = {
      // Include required fields from basicInfo form
      description: basicInfo.value.description,
      category_id: basicInfo.value.categoryId ? parseInt(basicInfo.value.categoryId) : null,  // 轉換為整數或 null
      topic_id: riskTopicsEnabled.value ? (basicInfo.value.topicId ? parseInt(basicInfo.value.topicId) : null) : null,  // 轉換為整數或 null
      risk_factor_id: basicInfo.value.riskFactorId ? parseInt(basicInfo.value.riskFactorId) : null,  // 轉換為整數或 null
      is_required: basicInfo.value.isRequired,

      // Include all existing data
      ...questionData.value,

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
      e1_select_1: formData.value.riskProbability.toString(), // Section E1 選擇項 1
      e1_select_2: formData.value.riskImpactLevel.toString(), // Section E1 選擇項 2
      e1_placeholder_2: formData.value.riskCalculation // Section E1 占位符 2
    }

    console.log('5. 完整送出資料:', updatedContent)

    // Update through store
    templatesStore.updateTemplateContent(templateId, contentId, updatedContent)

    // Show success message
    const { $toast } = useNuxtApp()
    $toast.success('ESG評估題目已成功儲存！（HTML格式）')

    // Navigate back
    goBack()
  } catch (error) {
    console.error('儲存題目時發生錯誤:', error)
    const { $toast } = useNuxtApp()
    $toast.error('儲存失敗，請稍後再試')
  }
}

// Note: Lifecycle and data loading is handled by the loadQuestionData() function
// which is called in the onMounted hook defined above (line 849)
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