<template>
  <div class="p-6">
    <!-- Page Header -->
    <div class="mb-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
            {{ showQuestionnaireView ? '風險評估問卷檢視' : '題項管理 - 公司列表' }}
          </h1>
          <p class="text-gray-600 dark:text-gray-400">
            {{ showQuestionnaireView ? '檢視問卷內容與用戶填答結果' : '選擇公司進入題項管理系統，管理各公司的風險評估題項' }}
          </p>
        </div>

        <!-- View Toggle -->
        <div class="flex items-center space-x-2">
          <button
            @click="toggleView('list')"
            :class="!showQuestionnaireView
              ? 'bg-primary-600 text-white'
              : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
            class="px-4 py-2 rounded-lg transition-colors duration-200"
          >
            公司列表
          </button>
          <button
            @click="toggleView('questionnaire')"
            :class="showQuestionnaireView
              ? 'bg-primary-600 text-white'
              : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
            class="px-4 py-2 rounded-lg transition-colors duration-200"
          >
            問卷檢視
          </button>
        </div>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
        <p class="text-red-700 dark:text-red-400">載入公司資料時發生錯誤: {{ error }}</p>
        <button
          @click="refreshCompanies"
          class="mt-2 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 underline"
        >
          重試
        </button>
      </div>
    </div>

    <!-- Company List View -->
    <div v-if="!showQuestionnaireView">
      <DataTable
        :data="companiesWithStatus"
        :columns="columns"
        search-placeholder="搜尋公司名稱..."
        :search-fields="['companyName']"
        empty-title="還沒有公司"
        empty-message="開始建立您的第一個公司"
        no-search-results-title="沒有找到符合的公司"
        no-search-results-message="請嘗試其他搜尋關鍵字"
      >
      <!-- Actions Slot -->
      <template #actions>
        <div class="flex gap-2">
          <button
            @click="refreshCompanies"
            :disabled="loading"
            class="flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
          >
            <ArrowPathIcon class="w-4 h-4 mr-2" :class="{ 'animate-spin': loading }" />
            重新載入
          </button>
          <button
            @click="showAddModal = true"
            class="flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200"
          >
            <PlusIcon class="w-4 h-4 mr-2" />
            新增公司
          </button>
        </div>
      </template>

      <!-- Custom Actions Cell -->
      <template #cell-actions="{ item }">
        <div class="flex items-center space-x-2">
          <!-- Enter Question Management Button -->
          <div class="relative group">
            <NuxtLink
              :to="`/admin/risk-assessment/questions/${item.id}/management`"
              class="block p-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors duration-200"
            >
              <ClipboardDocumentListIcon class="w-4 h-4" />
            </NuxtLink>
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
              進入題項管理
              <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
            </div>
          </div>

          <!-- Edit Button -->
          <div class="relative group">
            <button
              @click="editCompany(item)"
              class="p-2 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors duration-200"
            >
              <PencilIcon class="w-4 h-4" />
            </button>
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
              編輯
              <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
            </div>
          </div>

          <!-- Delete Button -->
          <div class="relative group">
            <button
              @click="handleDeleteCompany(item)"
              class="p-2 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors duration-200"
            >
              <TrashIcon class="w-4 h-4" />
            </button>
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
              刪除
              <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
            </div>
          </div>
        </div>
      </template>

      <!-- Custom Status Cell -->
      <template #cell-questionManagementStatus="{ item }">
        <span 
          v-if="item.questionManagementStatus"
          class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400 rounded-full"
        >
          <CheckCircleIcon class="w-3 h-3 mr-1" />
          已有題項管理
        </span>
        <span 
          v-else
          class="inline-flex items-center px-2 py-1 text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-full"
        >
          <PlusCircleIcon class="w-3 h-3 mr-1" />
          新建公司
        </span>
      </template>

      <!-- Empty Action Slot -->
      <template #emptyAction>
        <button
          @click="showAddModal = true"
          class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200"
        >
          <PlusIcon class="w-4 h-4 mr-2" />
          新增公司
        </button>
      </template>
      </DataTable>
    </div>

    <!-- Questionnaire View -->
    <div v-else class="min-h-screen bg-gray-50 dark:bg-gray-900 -m-6 p-6">
      <!-- Sample Data Controls -->
      <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">選擇檢視資料</h3>
        <div class="flex items-center space-x-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">公司</label>
            <select
              v-model="selectedCompanyId"
              @change="loadQuestionnaireData"
              class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
            >
              <option value="">選擇公司</option>
              <option v-for="company in companies" :key="company.id" :value="company.id">
                {{ company.companyName }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">問卷</label>
            <select
              v-model="selectedQuestionId"
              @change="loadQuestionnaireData"
              class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
            >
              <option value="">選擇問卷</option>
              <option value="1">範例問卷 1</option>
              <option value="2">範例問卷 2</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">題目</label>
            <select
              v-model="selectedContentId"
              @change="loadQuestionnaireData"
              class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
            >
              <option value="">選擇題目</option>
              <option value="2">範例題目 2</option>
              <option value="10">範例題目 10</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Questionnaire Content -->
      <div v-if="selectedCompanyId && selectedQuestionId && selectedContentId" class="space-y-6">
        <!-- Page Title Section -->
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">風險評估作答檢視</h1>
          <p class="mt-1 text-base text-gray-500 dark:text-gray-400">
            {{ questionnaireData.title || '風險評估題目' }} - 已填寫內容檢視
          </p>
        </div>

        <!-- Loading State -->
        <div v-if="questionnaireLoading" class="flex items-center justify-center py-12">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
          <span class="ml-3 text-gray-600 dark:text-gray-400">載入問卷資料中...</span>
        </div>

        <!-- Error State -->
        <div v-else-if="questionnaireError" class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
          <p class="text-red-700 dark:text-red-400">{{ questionnaireError }}</p>
        </div>

        <!-- Questionnaire Form Content -->
        <div v-else class="space-y-6">
          <!-- Section A: 風險因子議題描述 -->
          <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <button
              @click="toggleSection('sectionA')"
              class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 dark:hover:bg-gray-700"
            >
              <div class="flex items-center space-x-3">
                <span class="font-semibold text-gray-900 dark:text-white">風險因子議題描述</span>
                <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 px-2 py-1 rounded text-base font-medium">A</span>
              </div>
              <ChevronUpIcon v-if="expandedSections.sectionA" class="w-5 h-5 text-gray-400" />
              <ChevronDownIcon v-else class="w-5 h-5 text-gray-400" />
            </button>
            <div v-show="expandedSections.sectionA" class="px-6 pb-6">
              <div class="border-t border-gray-200 dark:border-gray-600 mb-4"></div>
              <div class="prose max-w-none text-gray-700 dark:text-gray-300" v-html="questionnaireData.riskFactorDescription || '暫無內容'"></div>
            </div>
          </div>

          <!-- Section B: 參考文字&模組工具評估結果 -->
          <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <button
              @click="toggleSection('sectionB')"
              class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 dark:hover:bg-gray-700"
            >
              <div class="flex items-center space-x-3">
                <span class="font-semibold text-gray-900 dark:text-white">參考文字&模組工具評估結果</span>
                <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 px-2 py-1 rounded text-base font-medium">B</span>
              </div>
              <ChevronUpIcon v-if="expandedSections.sectionB" class="w-5 h-5 text-gray-400" />
              <ChevronDownIcon v-else class="w-5 h-5 text-gray-400" />
            </button>
            <div v-show="expandedSections.sectionB" class="px-6 pb-6">
              <div class="border-t border-gray-200 dark:border-gray-600 mb-4"></div>
              <div class="prose max-w-none text-gray-700 dark:text-gray-300" v-html="questionnaireData.referenceText || '暫無內容'"></div>
            </div>
          </div>

          <!-- Section C: 風險事件 -->
          <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-6 rounded-2xl">
            <div class="flex items-center space-x-3 mb-4">
              <span class="font-bold text-gray-900 dark:text-white text-xl">公司報導年度是否有發生實際風險/負面衝擊事件</span>
              <span class="bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400 px-2 py-1 rounded text-base font-medium">C</span>
            </div>
            <div class="space-y-4">
              <!-- Radio Options Display -->
              <div class="grid grid-cols-2 gap-6">
                <div
                  :class="questionnaireData.responses?.c_risk_event_choice === 'yes'
                    ? 'bg-red-100 dark:bg-red-900/30 border-red-300 dark:border-red-600 text-red-800 dark:text-red-300'
                    : 'bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400'"
                  class="border-2 rounded-xl p-4 text-center font-medium"
                >
                  <div class="flex items-center justify-center space-x-2">
                    <span>✓</span>
                    <span>是</span>
                  </div>
                </div>
                <div
                  :class="questionnaireData.responses?.c_risk_event_choice === 'no'
                    ? 'bg-green-100 dark:bg-green-900/30 border-green-300 dark:border-green-600 text-green-800 dark:text-green-300'
                    : 'bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400'"
                  class="border-2 rounded-xl p-4 text-center font-medium"
                >
                  <div class="flex items-center justify-center space-x-2">
                    <span>✗</span>
                    <span>否</span>
                  </div>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">描述內容</label>
                <div class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white min-h-[80px]">
                  {{ questionnaireData.responses?.c_risk_event_description || '尚未填寫' }}
                </div>
              </div>
            </div>
          </div>

          <!-- Section D: 對策行動 -->
          <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-6 rounded-2xl">
            <div class="flex items-center space-x-3 mb-4">
              <span class="font-bold text-gray-900 dark:text-white text-xl">公司是否有對潛在風險與機會採取相應的對策行動</span>
              <span class="bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-400 px-2 py-1 rounded text-base font-medium">D</span>
            </div>
            <div class="space-y-4">
              <!-- Radio Options Display -->
              <div class="grid grid-cols-2 gap-6">
                <div
                  :class="questionnaireData.responses?.d_counter_action_choice === 'yes'
                    ? 'bg-green-100 dark:bg-green-900/30 border-green-300 dark:border-green-600 text-green-800 dark:text-green-300'
                    : 'bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400'"
                  class="border-2 rounded-xl p-4 text-center font-medium"
                >
                  <div class="flex items-center justify-center space-x-2">
                    <span>✓</span>
                    <span>是</span>
                  </div>
                </div>
                <div
                  :class="questionnaireData.responses?.d_counter_action_choice === 'no'
                    ? 'bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-300'
                    : 'bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400'"
                  class="border-2 rounded-xl p-4 text-center font-medium"
                >
                  <div class="flex items-center justify-center space-x-2">
                    <span>✗</span>
                    <span>否</span>
                  </div>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">描述內容</label>
                <div class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white min-h-[80px]">
                  {{ questionnaireData.responses?.d_counter_action_description || '尚未填寫' }}
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">對策費用</label>
                <div class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                  {{ questionnaireData.responses?.d_counter_action_cost ? `NT$ ${Number(questionnaireData.responses.d_counter_action_cost).toLocaleString()}` : '尚未填寫' }}
                </div>
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
              <div class="flex items-center space-x-3 mb-4">
                <span class="font-bold text-gray-900 dark:text-white text-xl">相關風險</span>
                <span class="bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400 px-2 py-1 rounded text-base font-medium">E-1</span>
              </div>
              <div class="space-y-4">
                <p class="text-base text-gray-600 dark:text-gray-400">公司未來潛在相關風險營清說明，未來潛在風險（收入減少）、費用增加於損益</p>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">風險描述</label>
                  <div class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white min-h-[80px]">
                    {{ questionnaireData.responses?.e1_risk_description || '尚未填寫' }}
                  </div>
                </div>
                <div class="border border-gray-300 dark:border-gray-600 rounded-xl p-4 space-y-3">
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">風險發生可能性</label>
                      <div class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-center text-gray-900 dark:text-white">
                        {{ questionnaireData.responses?.e1_risk_probability || '尚未選擇' }}
                      </div>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">風險發生衝擊程度</label>
                      <div class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-center text-gray-900 dark:text-white">
                        {{ questionnaireData.responses?.e1_risk_impact || '尚未選擇' }}
                      </div>
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">計算說明</label>
                    <div class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white min-h-[80px]">
                      {{ questionnaireData.responses?.e1_risk_calculation || '尚未填寫' }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Section F-1: 相關機會 -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-6 rounded-2xl">
              <div class="flex items-center space-x-3 mb-4">
                <span class="font-bold text-gray-900 dark:text-white text-xl">相關機會</span>
                <span class="bg-teal-100 dark:bg-teal-900/30 text-teal-800 dark:text-teal-400 px-2 py-1 rounded text-base font-medium">F-1</span>
              </div>
              <div class="space-y-4">
                <p class="text-base text-gray-600 dark:text-gray-400">公司未來潛在相關機會營清說明，未來潛在機會（收入增加）、費用減少於收益等不會定</p>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">機會描述</label>
                  <div class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white min-h-[80px]">
                    {{ questionnaireData.responses?.f1_opportunity_description || '尚未填寫' }}
                  </div>
                </div>
                <div class="border border-gray-300 dark:border-gray-600 rounded-xl p-4 space-y-3">
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">機會發生可能性</label>
                      <div class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-center text-gray-900 dark:text-white">
                        {{ questionnaireData.responses?.f1_opportunity_probability || '尚未選擇' }}
                      </div>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">機會發生衝擊程度</label>
                      <div class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-center text-gray-900 dark:text-white">
                        {{ questionnaireData.responses?.f1_opportunity_impact || '尚未選擇' }}
                      </div>
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">計算說明</label>
                    <div class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white min-h-[80px]">
                      {{ questionnaireData.responses?.f1_opportunity_calculation || '尚未填寫' }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Section G-1: 對外負面衝擊 -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-6 rounded-2xl">
              <div class="flex items-center space-x-3 mb-4">
                <span class="font-bold text-gray-900 dark:text-white text-xl">對外負面衝擊</span>
                <span class="bg-rose-100 dark:bg-rose-900/30 text-rose-800 dark:text-rose-400 px-2 py-1 rounded text-base font-medium">G-1</span>
              </div>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">負面衝擊程度</label>
                  <div class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-center text-gray-900 dark:text-white">
                    {{ questionnaireData.responses?.g1_negative_impact_level || '尚未選擇' }}
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">評分說明</label>
                  <div class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white min-h-[100px]">
                    {{ questionnaireData.responses?.g1_negative_impact_description || '尚未填寫' }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Section H-1: 對外正面影響 -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-6 rounded-2xl">
              <div class="flex items-center space-x-3 mb-4">
                <span class="font-bold text-gray-900 dark:text-white text-xl">對外正面影響</span>
                <span class="bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-400 px-2 py-1 rounded text-base font-medium">H-1</span>
              </div>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">正面影響程度</label>
                  <div class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-center text-gray-900 dark:text-white">
                    {{ questionnaireData.responses?.h1_positive_impact_level || '尚未選擇' }}
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">評分說明</label>
                  <div class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white min-h-[100px]">
                    {{ questionnaireData.responses?.h1_positive_impact_description || '尚未填寫' }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Response Metadata -->
          <div v-if="questionnaireData.responses" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4 rounded-lg">
            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">填寫資訊</h4>
            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
              <span>填寫時間：{{ questionnaireData.responses.answered_at ? new Date(questionnaireData.responses.answered_at).toLocaleString('zh-TW') : '未知' }}</span>
              <span v-if="questionnaireData.responses.updated_at && questionnaireData.responses.updated_at !== questionnaireData.responses.answered_at">
                最後更新：{{ new Date(questionnaireData.responses.updated_at).toLocaleString('zh-TW') }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12">
        <div class="mx-auto h-12 w-12 text-gray-400">
          <DocumentIcon class="h-12 w-12" />
        </div>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">請選擇要檢視的問卷</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">請先選擇公司、問卷和題目來檢視填答內容</p>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <Modal
      :model-value="showFormModal"
      :title="showAddModal ? '新增公司' : '編輯公司'"
      size="md"
      @update:model-value="(value) => { if (!value) closeModals() }"
      @close="closeModals"
    >
      <form @submit.prevent="submitForm">
        <div class="mb-4" v-if="showAddModal">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            選擇公司 <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <input
              ref="companySearchInput"
              v-model="companySearchQuery"
              @input="filterCompanies"
              @focus="handleCompanyInputFocus"
              @blur="handleCompanyInputBlur"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
              placeholder="請搜尋公司名稱..."
              autocomplete="off"
            />
            
            <!-- Dropdown List -->
            <div 
              v-if="showCompanyDropdown && filteredCompanies.length > 0"
              class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-80 overflow-y-auto"
            >
              <div 
                v-for="company in filteredCompanies" 
                :key="company.com_id"
                @click="selectCompany(company)"
                class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer transition-colors duration-200"
              >
                <div class="font-medium text-gray-900 dark:text-white">{{ company.com_title }}</div>
                <div v-if="company.abbreviation" class="text-sm text-gray-500 dark:text-gray-400">{{ company.abbreviation }}</div>
              </div>
            </div>
            
            <!-- No Results -->
            <div 
              v-if="showCompanyDropdown && companySearchQuery && filteredCompanies.length === 0"
              class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg p-3 text-center text-gray-500 dark:text-gray-400"
            >
              未找到符合的公司
            </div>
            
            <!-- Loading -->
            <div 
              v-if="loadingExternalCompanies"
              class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg p-3 text-center text-gray-500 dark:text-gray-400"
            >
              載入中...
            </div>
          </div>
        </div>
        
        <div class="mb-4" v-if="showEditModal">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            公司名稱
          </label>
          <input
            v-model="formData.companyName"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
            placeholder="請輸入公司名稱"
          />
        </div>
      </form>

      <template #footer>
        <div class="flex justify-end space-x-3">
          <button
            type="button"
            @click="closeModals"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors duration-200"
          >
            取消
          </button>
          <button
            type="button"
            @click="submitForm"
            :disabled="showAddModal && !selectedCompany"
            class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
          >
            {{ showAddModal ? '新增' : '更新' }}
          </button>
        </div>
      </template>
    </Modal>

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal
      :model-value="showDeleteModal"
      title="確認刪除"
      :message="`確定要刪除公司「${companyToDelete?.companyName}」嗎？`"
      details="此操作將同時刪除該公司的所有題項資料，且無法復原。"
      type="danger"
      cancel-text="取消"
      confirm-text="刪除"
      @update:model-value="(value) => showDeleteModal = value"
      @close="showDeleteModal = false"
      @confirm="confirmDelete"
    />
  </div>
</template>

<script setup>
definePageMeta({
  middleware: 'auth'
})

import {
  PlusIcon,
  PencilIcon,
  TrashIcon,
  ClipboardDocumentListIcon,
  CheckCircleIcon,
  PlusCircleIcon,
  ArrowPathIcon,
  ChevronUpIcon,
  ChevronDownIcon,
  DocumentIcon
} from '@heroicons/vue/24/outline'

// Dynamic page title
const pageTitle = computed(() =>
  showQuestionnaireView.value ? '風險評估問卷檢視' : '題項管理 - 公司列表'
)
usePageTitle(pageTitle)

// Reactive data
const showAddModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)

// Questionnaire view state
const showQuestionnaireView = ref(false)
const selectedCompanyId = ref('')
const selectedQuestionId = ref('')
const selectedContentId = ref('')
const questionnaireData = ref({})
const questionnaireError = ref(null)
const questionnaireLoading = ref(false)

// Expandable sections state for questionnaire
const expandedSections = ref({
  sectionA: true,
  sectionB: true
})

// Computed property for form modal visibility
const showFormModal = computed(() => showAddModal.value || showEditModal.value)
const companyToDelete = ref(null)
const editingCompany = ref(null)

const formData = ref({
  companyName: ''
})

// External company search data
const externalCompanies = ref([])
const filteredCompanies = ref([])
const companySearchQuery = ref('')
const selectedCompany = ref(null)
const showCompanyDropdown = ref(false)
const loadingExternalCompanies = ref(false)
const companySearchInput = ref(null)

// Use local companies API instead of localStorage
const {
  localCompanies,
  loading,
  error,
  loadLocalCompanies,
  addLocalCompany,
  updateLocalCompany,
  deleteLocalCompany,
  refreshLocalCompanies,
  clearError
} = useLocalCompanies()

// Computed property to get companies data
const companies = computed(() => localCompanies.value)

// Company management functions (API-based)
const addCompany = async (companyData) => {
  return await addLocalCompany({
    company_name: companyData.companyName,
    external_id: companyData.externalId,
    abbreviation: companyData.abbreviation
  })
}

const updateCompany = async (id, companyData) => {
  return await updateLocalCompany(id, {
    company_name: companyData.companyName,
    abbreviation: companyData.abbreviation
  })
}

const deleteCompany = async (id) => {
  return await deleteLocalCompany(id)
}

const refreshCompanies = async () => {
  await refreshLocalCompanies()
}

// Initialize data on component mount
onMounted(async () => {
  await loadLocalCompanies() // Load companies from database API
  await fetchExternalCompanies() // Load external API companies for dropdown
})

// Get question management status
const { hasQuestionManagementItems } = useQuestionManagement()

// Computed companies with status
const companiesWithStatus = computed(() => 
  companies.value.map(company => ({
    ...company,
    questionManagementStatus: hasQuestionManagementItems(company.id)
  }))
)

// DataTable columns configuration
const columns = ref([
  {
    key: 'actions',
    label: '功能',
    sortable: false,
    cellClass: 'text-base text-gray-900 dark:text-white'
  },
  {
    key: 'companyName',
    label: '公司名稱',
    sortable: true,
    cellClass: 'text-base font-medium text-gray-900 dark:text-white'
  },
  {
    key: 'questionManagementStatus',
    label: '題項管理狀態',
    sortable: false,
    cellClass: 'text-sm text-gray-600 dark:text-gray-400'
  }
])

// Questionnaire view methods
const toggleView = (view) => {
  showQuestionnaireView.value = view === 'questionnaire'

  if (view === 'questionnaire') {
    // Set default selections to match the example from the prompt
    selectedCompanyId.value = '1'
    selectedQuestionId.value = '10'
    selectedContentId.value = '2'
    loadQuestionnaireData()
  }
}

const toggleSection = (section) => {
  expandedSections.value[section] = !expandedSections.value[section]
}

const loadQuestionnaireData = async () => {
  if (!selectedCompanyId.value || !selectedQuestionId.value || !selectedContentId.value) {
    questionnaireData.value = {}
    return
  }

  questionnaireLoading.value = true
  questionnaireError.value = null

  try {
    // Simulate loading questionnaire data based on the example
    // This would normally be an API call to get the content and user responses
    const mockData = {
      title: '未命名題目',
      riskFactorDescription: '<p>測試風險因子描述002 - 這是一個關於環境風險的評估題目，主要探討公司在氣候變遷影響下的適應能力。</p>',
      referenceText: '<p>根據TCFD框架，企業應評估氣候相關的風險與機會。參考最新的科學報告和政策發展。</p>',
      responses: {
        c_risk_event_choice: 'yes',
        c_risk_event_description: '公司在2023年面臨了嚴重的洪水災害，導致生產線停工3天，損失約500萬台幣。',
        d_counter_action_choice: 'yes',
        d_counter_action_description: '已建立緊急應變計畫，購買相關保險，並投資防洪設施升級。',
        d_counter_action_cost: '2500000',
        e1_risk_description: '氣候變遷可能導致極端天氣事件增加，影響供應鏈穩定性和營運成本。',
        e1_risk_probability: '高 (3)',
        e1_risk_impact: '中等 (2)',
        e1_risk_calculation: '風險值 = 3 × 2 = 6，屬於中高風險等級，需要積極管理。',
        f1_opportunity_description: '發展綠色技術產品，開拓新的市場機會，提升品牌形象。',
        f1_opportunity_probability: '中等 (2)',
        f1_opportunity_impact: '高 (3)',
        f1_opportunity_calculation: '機會值 = 2 × 3 = 6，具有良好的發展潛力。',
        g1_negative_impact_level: '中等',
        g1_negative_impact_description: '可能對當地水資源造成輕微污染，影響社區環境品質。',
        h1_positive_impact_level: '高',
        h1_positive_impact_description: '透過綠色轉型創造就業機會，提升當地經濟發展。',
        answered_at: '2024-09-29T14:30:00Z',
        updated_at: '2024-09-29T16:45:00Z'
      }
    }

    // Simulate API delay
    await new Promise(resolve => setTimeout(resolve, 500))

    questionnaireData.value = mockData
  } catch (error) {
    console.error('Failed to load questionnaire data:', error)
    questionnaireError.value = '載入問卷資料失敗'
  } finally {
    questionnaireLoading.value = false
  }
}

// Methods
const editCompany = (company) => {
  editingCompany.value = company
  formData.value.companyName = company.companyName
  showEditModal.value = true
}

const handleDeleteCompany = (company) => {
  companyToDelete.value = company
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  if (companyToDelete.value) {
    const success = await deleteCompany(companyToDelete.value.id)
    
    if (!success) {
      // Handle error (could show a toast notification)
      console.error('Failed to delete company')
      return
    }
  }
  showDeleteModal.value = false
  companyToDelete.value = null
}

const submitForm = async () => {
  if (showAddModal.value) {
    // Add new company from external selection
    if (selectedCompany.value) {
      const result = await addCompany({
        companyName: selectedCompany.value.com_title,
        externalId: selectedCompany.value.com_id,
        abbreviation: selectedCompany.value.abbreviation
      })
      
      if (!result) {
        // Handle error (could show a toast notification)
        console.error('Failed to add company')
        return
      }
    }
  } else if (showEditModal.value) {
    // Update existing company
    const result = await updateCompany(editingCompany.value.id, {
      companyName: formData.value.companyName
    })
    
    if (!result) {
      // Handle error (could show a toast notification)
      console.error('Failed to update company')
      return
    }
  }
  
  closeModals()
}

const closeModals = () => {
  showAddModal.value = false
  showEditModal.value = false
  editingCompany.value = null
  formData.value.companyName = ''
  
  // Reset search data
  companySearchQuery.value = ''
  selectedCompany.value = null
  showCompanyDropdown.value = false
  filteredCompanies.value = []
}

// External company API methods
const fetchExternalCompanies = async () => {
  if (externalCompanies.value.length > 0) return // Already loaded
  
  loadingExternalCompanies.value = true
  try {
    const response = await $fetch('https://csr.cc-sustain.com/admin/api/risk/get_all_companies')
    if (response.success && response.data) {
      externalCompanies.value = response.data
    }
  } catch (error) {
    console.error('Failed to fetch external companies:', error)
  } finally {
    loadingExternalCompanies.value = false
  }
}

const filterCompanies = () => {
  const query = companySearchQuery.value.toLowerCase()
  if (!query) {
    // Show all companies when no search query (user requested "all")
    filteredCompanies.value = externalCompanies.value
    return
  }
  
  // Show all matching companies when searching (no limits)
  filteredCompanies.value = externalCompanies.value.filter(company => 
    company.com_title.toLowerCase().includes(query) ||
    (company.abbreviation && company.abbreviation.toLowerCase().includes(query))
  )
}

const selectCompany = (company) => {
  selectedCompany.value = company
  companySearchQuery.value = company.com_title
  showCompanyDropdown.value = false
}

const handleCompanyInputFocus = async () => {
  showCompanyDropdown.value = true
  
  // Ensure external companies are loaded
  if (externalCompanies.value.length === 0) {
    await fetchExternalCompanies()
  }
  
  // Trigger filtering to show default companies
  filterCompanies()
}

const handleCompanyInputBlur = () => {
  // Delay hiding dropdown to allow for click events
  setTimeout(() => {
    showCompanyDropdown.value = false
  }, 200)
}

// Watch for modal opening to load companies and focus input
watch(() => showAddModal.value, (newValue) => {
  if (newValue) {
    fetchExternalCompanies()
    nextTick(() => {
      companySearchInput.value?.focus()
    })
  }
})

// Watch for search query to filter companies
watch(() => companySearchQuery.value, () => {
  if (showAddModal.value) {
    filterCompanies()
  }
})
</script>