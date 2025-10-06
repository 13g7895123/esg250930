<template>
  <Modal
    :model-value="modelValue"
    title="人員指派管理"
    size="4xl"
    @update:model-value="$emit('update:modelValue', $event)"
    @close="$emit('close')"
  >
    <div class="h-full max-h-[80vh] flex flex-col">
      <!-- Header -->
      <div class="flex-shrink-0 pb-4">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
              題項內容人員指派
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              為每個題項內容指派評估人員 (多對多關係)
            </p>
          </div>
          <div class="flex items-center space-x-2">
            <button
              @click="refreshPersonnelData"
              :disabled="isLoadingPersonnel"
              class="px-3 py-2 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
            >
              <svg v-if="isLoadingPersonnel" class="animate-spin w-4 h-4 mr-2 inline" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ isLoadingPersonnel ? '載入中...' : '重新載入人員' }}
            </button>
            <button
              v-if="false"
              @click="showDataDebug = true"
              class="px-3 py-2 bg-yellow-600 text-white text-sm rounded-lg hover:bg-yellow-700 transition-colors duration-200"
            >
              <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              資料除錯
            </button>
            <button
              @click="showBulkAssignment = true"
              :disabled="isLoadingPersonnel || availablePersonnel.length === 0"
              class="px-3 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
            >
              批量指派
            </button>
            <button
              @click="showIndividualAssignment = true"
              :disabled="isLoadingPersonnel || availablePersonnel.length === 0"
              class="px-3 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
            >
              <PlusIcon class="w-4 h-4 mr-2 inline" />
              指派人員
            </button>
          </div>
        </div>

        <!-- Compact Assignment Summary -->
        <div class="mt-4 bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
          <div class="flex items-center justify-between text-sm">
            <div class="flex items-center space-x-4">
              <span class="flex items-center">
                <span class="w-3 h-3 bg-primary-500 rounded-full mr-2"></span>
                {{ contentSummary.length }} 題項內容
              </span>
              <span class="flex items-center">
                <span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>
                {{ assignedPersonnelList.length }} 已指派人員
              </span>
              <span class="flex items-center">
                <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
                {{ totalAssignments }} 總指派數
              </span>
              <span class="flex items-center">
                <span class="w-3 h-3 bg-purple-500 rounded-full mr-2"></span>
                <span v-if="isLoadingPersonnel" class="text-purple-600">載入人員中...</span>
                <span v-else>{{ availablePersonnel.length }} 可用人員</span>
              </span>
            </div>
          </div>

          <!-- Loading or empty state message -->
          <div v-if="isLoadingPersonnel" class="mt-2 flex items-center text-xs text-purple-600">
            <svg class="animate-spin w-3 h-3 mr-1" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            正在從外部系統載入人員資料...
          </div>
          <div v-else-if="availablePersonnel.length === 0" class="mt-2 text-xs text-amber-600">
            ⚠️ 未找到可指派的人員，請檢查公司資料設定
          </div>
        </div>
      </div>

      <!-- Tab Navigation -->
      <div class="flex-shrink-0 border-b border-gray-200 dark:border-gray-600">
        <nav class="-mb-px flex space-x-8">
          <button
            @click="activeTab = 'assignments'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === 'assignments'
                ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
            ]"
          >
            指派狀況 ({{ filteredContentSummary.length }})
          </button>
          <button
            @click="activeTab = 'personnel'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === 'personnel'
                ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
            ]"
          >
            已指派人員 ({{ assignedPersonnelList.length }})
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <div class="flex-1 overflow-hidden">
        <!-- Assignments Tab -->
        <div v-if="activeTab === 'assignments'" class="h-full flex flex-col">
          <!-- Search and Filter -->
          <div class="flex-shrink-0 p-4 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center space-x-4">
              <div class="flex-1 relative">
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="搜尋題項內容或人員姓名..."
                  class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                />
                <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
              </div>
              <select
                v-model="filterBy"
                class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
              >
                <option value="all">所有內容</option>
                <option value="assigned">已指派</option>
                <option value="unassigned">未指派</option>
              </select>
            </div>
          </div>

          <!-- Assignment Cards -->
          <div class="flex-1 overflow-y-auto p-4 max-h-96">
            <div class="space-y-3">
              <div
                v-for="content in filteredContentSummary"
                :key="content.contentId"
                class="border border-gray-200 dark:border-gray-600 rounded-lg p-4"
              >
                <div class="flex items-start justify-between mb-3">
                  <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                      <h5
                        class="font-medium text-gray-900 dark:text-white cursor-help relative group"
                        :title="stripHtml(getFactorDescription(content.factorId))"
                      >
                        {{ truncateText(getFactorDescription(content.factorId), 10) || content.topic }}
                        <!-- Tooltip for full content -->
                        <span
                          v-if="stripHtml(getFactorDescription(content.factorId)).length > 10"
                          class="absolute left-0 top-full mt-2 p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible z-50 min-w-[300px] max-w-[500px] whitespace-normal text-sm font-normal"
                        >
                          {{ stripHtml(getFactorDescription(content.factorId)) }}
                        </span>
                      </h5>
                      <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        {{ getCategoryName(content.categoryId || content.category_id) }}
                      </span>
                      <span
                        v-if="getTopicName(content.topicId)"
                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200"
                      >
                        {{ getTopicName(content.topicId) }}
                      </span>
                      <span
                        v-if="getFactorName(content.factorId)"
                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200"
                      >
                        {{ getFactorName(content.factorId) }}
                      </span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                      {{ content.description }}
                    </p>
                  </div>
                  <div class="flex items-center space-x-2 ml-4">
                    <span class="px-2 py-1 text-xs rounded-full"
                          :class="content.assignmentCount > 0 
                            ? 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400' 
                            : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'">
                      {{ content.assignmentCount }} 人
                    </span>
                  </div>
                </div>

                <!-- Assigned Personnel for this content -->
                <div v-if="content.assignedUsers.length > 0" class="flex flex-wrap gap-2">
                  <div
                    v-for="user in content.assignedUsers"
                    :key="`${content.contentId}-${user.userId}`"
                    class="flex items-center space-x-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg px-3 py-1"
                  >
                    <div class="w-5 h-5 bg-blue-200 dark:bg-blue-700 rounded-full flex items-center justify-center">
                      <span class="text-blue-700 dark:text-blue-300 font-medium text-xs">
                        {{ user.personnelName.charAt(0) }}
                      </span>
                    </div>
                    <span class="text-sm text-blue-900 dark:text-blue-200">{{ user.personnelName }}</span>
                    <button
                      @click="removeUserFromContent(content.contentId, user.userId)"
                      class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200"
                    >
                      <XMarkIcon class="w-3 h-3" />
                    </button>
                  </div>
                </div>
                <div v-else class="text-sm text-gray-500 dark:text-gray-400">
                  尚未指派人員
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Personnel Tab -->
        <div v-else-if="activeTab === 'personnel'" class="h-full overflow-y-auto p-4">
          <div v-if="assignedPersonnelList.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
            <p>尚未指派任何人員</p>
          </div>
          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div
              v-for="person in assignedPersonnelList"
              :key="person.id"
              class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900/20 rounded-full flex items-center justify-center">
                    <span class="text-primary-600 dark:text-primary-400 font-medium text-sm">
                      {{ person.personnelName.charAt(0) }}
                    </span>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900 dark:text-white text-sm">{{ person.personnelName }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ person.assignmentCount }} 項內容</p>
                  </div>
                </div>
                <button
                  @click="removePersonFromAllContent(person.id)"
                  class="p-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors duration-200"
                >
                  <TrashIcon class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end">
        <button
          @click="$emit('close')"
          class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors duration-200"
        >
          關閉
        </button>
      </div>
    </template>
  </Modal>

  <!-- Individual Assignment Modal -->
  <Modal
    :model-value="showIndividualAssignment"
    title="指派人員到題項內容"
    size="lg"
    @update:model-value="showIndividualAssignment = $event"
    @close="showIndividualAssignment = false"
  >
    <IndividualAssignment
      :company-id="companyId"
      :question-id="questionId"
      :content-summary="contentSummary"
      :available-users="availablePersonnel"
      @assignment-completed="onAssignmentCompleted"
      @close="showIndividualAssignment = false"
    />
  </Modal>

  <!-- Bulk Assignment Modal -->
  <Modal
    :model-value="showBulkAssignment"
    title="批量人員指派"
    size="lg"
    @update:model-value="showBulkAssignment = $event"
    @close="showBulkAssignment = false"
  >
    <BulkAssignment
      :company-id="companyId"
      :question-id="questionId"
      :content-summary="contentSummary"
      :available-users="availablePersonnel"
      @assignment-completed="onAssignmentCompleted"
      @close="showBulkAssignment = false"
    />
  </Modal>

  <!-- Data Debug Modal -->
  <Modal
    :model-value="showDataDebug"
    title="人員指派資料除錯"
    size="4xl"
    @update:model-value="showDataDebug = $event"
    @close="showDataDebug = false"
  >
    <div class="space-y-6 max-h-[70vh] overflow-y-auto pr-2">
      <!-- Props Debug -->
      <div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Props 參數</h3>
        <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-4 max-h-32 overflow-y-auto">
          <pre class="text-sm text-gray-800 dark:text-gray-200">{{ JSON.stringify(debugData.props, null, 2) }}</pre>
        </div>
      </div>

      <!-- Categories Debug -->
      <div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">
          風險類別資料
          <span class="text-sm font-normal text-gray-600 dark:text-gray-400">
            (載入狀態: {{ debugData.questionCategories.loaded ? '已載入' : '未載入' }},
            數量: {{ debugData.questionCategories.count }})
          </span>
        </h3>
        <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-4 max-h-48 overflow-y-auto">
          <pre class="text-sm text-gray-800 dark:text-gray-200">{{ JSON.stringify(debugData.questionCategories.data, null, 2) }}</pre>
        </div>
      </div>

      <!-- Content Summary Debug -->
      <div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">
          題項內容摘要 (數量: {{ debugData.contentSummary.count }})
        </h3>
        <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-4 max-h-48 overflow-y-auto">
          <pre class="text-sm text-gray-800 dark:text-gray-200">{{ JSON.stringify(debugData.contentSummary.data, null, 2) }}</pre>
        </div>
      </div>

      <!-- Category Mapping Debug -->
      <div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">類別對應分析</h3>
        <div class="space-y-2 max-h-64 overflow-y-auto">
          <div
            v-for="item in debugData.categoryMapping"
            :key="item.contentId"
            class="border border-gray-200 dark:border-gray-600 rounded-lg p-3"
          >
            <div class="flex items-center justify-between mb-2">
              <h4 class="font-medium text-gray-900 dark:text-white">{{ item.topic }}</h4>
              <span
                :class="[
                  'px-2 py-1 text-xs rounded-full',
                  item.hasCategoryId
                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                    : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                ]"
              >
                {{ item.hasCategoryId ? '有 categoryId' : '無 categoryId' }}
              </span>
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm text-gray-600 dark:text-gray-400">
              <div>
                <strong>Content ID:</strong> {{ item.contentId }}<br>
                <strong>Category ID:</strong>
                <span :class="!item.categoryId ? 'text-red-600 dark:text-red-400 font-medium' : 'text-green-600 dark:text-green-400'">
                  {{ item.categoryId || '未設定' }}
                </span><br>
                <strong>欄位來源:</strong>
                <span :class="item.categoryIdField === 'none' ? 'text-red-600 dark:text-red-400 font-medium' : 'text-blue-600 dark:text-blue-400'">
                  {{ item.categoryIdField }}
                </span><br>
                <strong>顯示類別:</strong>
                <span :class="item.categoryName === '未分類' ? 'text-red-600 dark:text-red-400 font-medium' : 'text-green-600 dark:text-green-400'">
                  {{ item.categoryName }}
                </span>
                <div class="mt-2 text-xs">
                  <strong>欄位檢查:</strong><br>
                  <span class="text-gray-500">categoryId (camelCase): {{ item.fieldAnalysis.hasCategIdCamel ? '✓ 存在' : '✗ 不存在' }}</span><br>
                  <span class="text-gray-500">category_id (snake_case): {{ item.fieldAnalysis.hasCategIdSnake ? '✓ 存在' : '✗ 不存在' }}</span><br>
                  <span class="text-gray-500">所有欄位: {{ item.fieldAnalysis.allFields.join(', ') }}</span>
                </div>
              </div>
              <div>
                <strong>原始資料:</strong>
                <pre class="text-xs bg-gray-50 dark:bg-gray-700 p-2 rounded mt-1 max-h-32 overflow-y-auto">{{ JSON.stringify(item.rawContent, null, 2) }}</pre>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Field Name Analysis -->
      <div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">欄位名稱分析</h3>
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
          <div v-if="debugData.fieldNameAnalysis.sampleContent">
            <h4 class="font-medium text-blue-800 dark:text-blue-200 mb-2">發現資料樣本：</h4>
            <div class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
              <div><strong>可用欄位：</strong> {{ debugData.fieldNameAnalysis.contentFields.join(', ') }}</div>
              <div><strong>categoryId (駝峰式)：</strong>
                <span :class="debugData.fieldNameAnalysis.hasCategoryFields?.hasCategoryId ? 'text-green-600' : 'text-red-600'">
                  {{ debugData.fieldNameAnalysis.hasCategoryFields?.hasCategoryId ? '✓ 存在' : '✗ 不存在' }}
                </span>
              </div>
              <div><strong>category_id (底線式)：</strong>
                <span :class="debugData.fieldNameAnalysis.hasCategoryFields?.hasCategoryIdSnake ? 'text-green-600' : 'text-red-600'">
                  {{ debugData.fieldNameAnalysis.hasCategoryFields?.hasCategoryIdSnake ? '✓ 存在' : '✗ 不存在' }}
                </span>
              </div>
            </div>
            <div class="mt-2">
              <strong class="text-blue-800 dark:text-blue-200">資料樣本：</strong>
              <pre class="text-xs bg-blue-100 dark:bg-blue-800 p-2 rounded mt-1 max-h-24 overflow-y-auto">{{ JSON.stringify(debugData.fieldNameAnalysis.sampleContent, null, 2) }}</pre>
            </div>
          </div>
          <div v-else class="text-blue-700 dark:text-blue-300">
            <h4 class="font-medium text-blue-800 dark:text-blue-200 mb-2">⚠️ 無資料樣本</h4>
            <p class="text-sm">questionContent 是空陣列，這是問題的根源！</p>
            <p class="text-sm mt-1">可能原因：</p>
            <ul class="text-sm mt-1 list-disc list-inside">
              <li>父組件沒有正確傳遞 questionContent</li>
              <li>資料載入時機問題</li>
              <li>API 回應格式不正確</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Problem Analysis -->
      <div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">問題分析</h3>
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
          <h4 class="font-medium text-yellow-800 dark:text-yellow-200 mb-2">根據分析結果，問題診斷：</h4>
          <div v-if="debugData.props.questionContentLength === 0" class="space-y-2">
            <div class="text-sm text-red-700 dark:text-red-300 font-medium">🔴 主要問題：questionContent 是空陣列</div>
            <ul class="text-sm text-yellow-700 dark:text-yellow-300 space-y-1 ml-4">
              <li>• 父組件沒有正確傳遞題項內容資料</li>
              <li>• 可能是資料載入時機問題</li>
              <li>• 需要檢查父組件的資料傳遞邏輯</li>
            </ul>
          </div>
          <div v-else class="space-y-2">
            <div class="text-sm text-green-700 dark:text-green-300 font-medium">✅ questionContent 有 {{ debugData.props.questionContentLength }} 筆資料</div>
            <ul class="text-sm text-yellow-700 dark:text-yellow-300 space-y-1">
              <li v-if="!debugData.fieldNameAnalysis.hasCategoryFields?.hasCategoryId && !debugData.fieldNameAnalysis.hasCategoryFields?.hasCategoryIdSnake">
                🔴 category 欄位完全缺失（既無 categoryId 也無 category_id）
              </li>
              <li v-else-if="!debugData.fieldNameAnalysis.hasCategoryFields?.hasCategoryId && debugData.fieldNameAnalysis.hasCategoryFields?.hasCategoryIdSnake">
                🟡 欄位名稱不匹配：資料使用 category_id (snake_case)，但程式碼期望 categoryId (camelCase)
              </li>
              <li v-else>• 類別欄位正常，檢查 getCategoryName 函數邏輯</li>
            </ul>
          </div>

          <div class="mt-3 text-sm text-yellow-700 dark:text-yellow-300">
            <strong>建議解決步驟：</strong>
            <ol class="list-decimal list-inside space-y-1 mt-1">
              <li v-if="debugData.props.questionContentLength === 0">檢查父組件是否正確傳遞 questionContent prop</li>
              <li v-else-if="!debugData.fieldNameAnalysis.hasCategoryFields?.hasCategoryId && debugData.fieldNameAnalysis.hasCategoryFields?.hasCategoryIdSnake">
                修改程式碼支援 category_id 欄位名稱
              </li>
              <li v-else>檢查 getCategoryName 函數和類別資料匹配邏輯</li>
              <li>確認風險類別資料正確載入（目前：{{ debugData.questionCategories.loaded ? '已載入' : '未載入' }}）</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end">
        <button
          @click="showDataDebug = false"
          class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors duration-200"
        >
          關閉
        </button>
      </div>
    </template>
  </Modal>
</template>

<script setup>
import {
  PlusIcon,
  CogIcon,
  XMarkIcon,
  TrashIcon,
  MagnifyingGlassIcon
} from '@heroicons/vue/24/outline'
import { onMounted, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  companyId: {
    type: [String, Number],
    required: true
  },
  questionId: {
    type: [String, Number],
    default: null
  },
  questionContent: {
    type: Array,
    default: () => []
  },
})

const emit = defineEmits(['update:modelValue', 'close', 'assignment-updated'])

// Assignment management with API
const {
  loadAssignmentSummary,
  loadPersonnel,
  syncPersonnel,
  removeAssignment,
  removePersonnelFromAssessment,
  isLoading: isApiLoading,
  availablePersonnel,
  getCompatibleAssignmentSummary,
  getCompatibleAssignedPersonnel
} = usePersonnelAssignmentApi()

// Question structure management for categories, topics, and factors
const {
  getCategories,
  getTopics,
  getFactors,
  categories: questionCategories,
  topics: questionTopics,
  factors: questionFactors
} = useQuestionStructure()

// Reactive data
const showIndividualAssignment = ref(false)
const showBulkAssignment = ref(false)
const showDataDebug = ref(false)
const searchQuery = ref('')
const filterBy = ref('all')
const activeTab = ref('assignments')

// Loading state for current company
const isLoadingPersonnel = computed(() => {
  return isApiLoading.value
})

// Computed properties using API data
const contentSummary = computed(() => {
  if (!props.questionId) return []
  return getCompatibleAssignmentSummary(props.companyId, props.questionId, props.questionContent)
})

const assignedPersonnelList = computed(() => {
  if (!props.questionId) return []
  return getCompatibleAssignedPersonnel(props.companyId, props.questionId)
})

const totalAssignments = computed(() => 
  contentSummary.value.reduce((total, content) => total + content.assignmentCount, 0)
)

const filteredContentSummary = computed(() => {
  let filtered = contentSummary.value

  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(content =>
      content.topic.toLowerCase().includes(query) ||
      content.description.toLowerCase().includes(query) ||
      content.assignedUsers.some(user => 
        user.personnelName.toLowerCase().includes(query)
      )
    )
  }

  // Apply assignment filter
  if (filterBy.value === 'assigned') {
    filtered = filtered.filter(content => content.assignmentCount > 0)
  } else if (filterBy.value === 'unassigned') {
    filtered = filtered.filter(content => content.assignmentCount === 0)
  }

  // Sort by risk category order (same as risk category management)
  return filtered.sort((a, b) => {
    const orderA = getCategoryOrder(a.categoryId || a.category_id)
    const orderB = getCategoryOrder(b.categoryId || b.category_id)
    return orderA - orderB
  })
})

// Debug data for troubleshooting category display issues
const debugData = computed(() => {
  return {
    props: {
      companyId: props.companyId,
      questionId: props.questionId,
      questionContent: props.questionContent,
      questionContentType: Array.isArray(props.questionContent) ? 'Array' : typeof props.questionContent,
      questionContentLength: props.questionContent?.length || 0
    },
    questionCategories: {
      loaded: questionCategories.value ? true : false,
      count: questionCategories.value?.length || 0,
      data: questionCategories.value || []
    },
    contentSummary: {
      count: contentSummary.value?.length || 0,
      data: contentSummary.value || []
    },
    categoryMapping: props.questionContent?.map(content => ({
      contentId: content.id,
      topic: content.topic,
      // 檢查兩種可能的欄位名稱
      categoryId: content.categoryId || content.category_id,
      categoryIdField: content.categoryId ? 'categoryId' : (content.category_id ? 'category_id' : 'none'),
      categoryName: getCategoryName(content.categoryId || content.category_id),
      hasCategoryId: !!(content.categoryId || content.category_id),
      rawContent: content,
      // 詳細的欄位檢查
      fieldAnalysis: {
        hasCategIdCamel: 'categoryId' in content,
        hasCategIdSnake: 'category_id' in content,
        categoryIdValue: content.categoryId,
        categoryIdSnakeValue: content.category_id,
        allFields: Object.keys(content || {})
      }
    })) || [],
    // 新增：欄位名稱分析
    fieldNameAnalysis: {
      sampleContent: props.questionContent?.[0] || null,
      contentFields: props.questionContent?.[0] ? Object.keys(props.questionContent[0]) : [],
      hasCategoryFields: props.questionContent?.[0] ? {
        hasCategoryId: 'categoryId' in (props.questionContent[0] || {}),
        hasCategoryIdSnake: 'category_id' in (props.questionContent[0] || {})
      } : null
    }
  }
})

// Methods using API
const removeUserFromContent = async (contentId, userId) => {
  if (!props.questionId) return

  try {
    const success = await removeAssignment({
      company_id: props.companyId,
      assessment_id: props.questionId,
      question_content_id: contentId,
      personnel_id: userId
    })

    if (success) {
      emit('assignment-updated')
    }
  } catch (error) {
    console.error('Error removing user from content:', error)
  }
}

const removePersonFromAllContent = async (userId) => {
  if (!props.questionId) return

  try {
    const success = await removePersonnelFromAssessment(
      props.companyId,
      props.questionId,
      userId
    )

    if (success) {
      emit('assignment-updated')
    }
  } catch (error) {
    console.error('Error removing person from all content:', error)
  }
}

const manageContentAssignment = (content) => {
  // TODO: Implement specific content assignment management
  console.log('Manage assignment for content:', content)
}

const onAssignmentCompleted = () => {
  showIndividualAssignment.value = false
  showBulkAssignment.value = false
  emit('assignment-updated')
}

// Refresh personnel data from external API
const refreshPersonnelData = async () => {
  if (!props.companyId) return

  try {
    // Use new API to sync personnel data
    await syncPersonnel(props.companyId)

    // Also refresh assignment summary if we have a questionId
    if (props.questionId) {
      await loadAssignmentSummary(props.companyId, props.questionId)
    }
  } catch (error) {
    console.error('Error refreshing personnel data:', error)
  }
}

// Helper method to get category name
const getCategoryName = (categoryId) => {
  if (!categoryId) return '未分類'

  // Use categories from API instead of localStorage
  if (questionCategories.value && questionCategories.value.length > 0) {
    const category = questionCategories.value.find(cat => cat.id === categoryId)
    return category ? category.category_name : '未知類別'
  }

  return '未分類'
}

const getTopicName = (topicId) => {
  if (!topicId) return ''

  if (questionTopics.value && questionTopics.value.length > 0) {
    const topic = questionTopics.value.find(t => t.id === topicId)
    return topic ? topic.topic_name : ''
  }

  return ''
}

const getFactorName = (factorId) => {
  if (!factorId) return ''

  if (questionFactors.value && questionFactors.value.length > 0) {
    const factor = questionFactors.value.find(f => f.id === factorId)
    return factor ? factor.factor_name : ''
  }

  return ''
}

const getFactorDescription = (factorId) => {
  if (!factorId) return ''

  if (questionFactors.value && questionFactors.value.length > 0) {
    const factor = questionFactors.value.find(f => f.id === factorId)
    return factor ? (factor.description || '') : ''
  }

  return ''
}

const stripHtml = (html) => {
  if (!html) return ''
  const tmp = document.createElement('div')
  tmp.innerHTML = html
  return tmp.textContent || tmp.innerText || ''
}

const truncateText = (text, maxLength = 10) => {
  if (!text) return ''
  const plainText = stripHtml(text)
  if (plainText.length <= maxLength) return plainText
  return plainText.substring(0, maxLength) + '...'
}

// Helper method to get category order (index in the categories array)
const getCategoryOrder = (categoryId) => {
  if (!categoryId) return 999 // Put uncategorized items at the end

  // Use categories from API instead of localStorage
  if (questionCategories.value && questionCategories.value.length > 0) {
    const categoryIndex = questionCategories.value.findIndex(cat => cat.id === categoryId)
    return categoryIndex !== -1 ? categoryIndex : 999
  }

  return 999
}

// Lifecycle and watchers for API data loading
onMounted(async () => {
  if (props.companyId) {
    try {
      // Load personnel data
      await loadPersonnel(props.companyId)

      // Load assignment summary and structure data if we have a questionId
      if (props.questionId) {
        await Promise.all([
          loadAssignmentSummary(props.companyId, props.questionId),
          getCategories(props.questionId),
          getTopics(props.questionId),
          getFactors(props.questionId)
        ])
      }
    } catch (error) {
      console.error('Error loading initial data:', error)
    }
  }
})

// Watch for changes in companyId or questionId
watch([() => props.companyId, () => props.questionId], async ([newCompanyId, newQuestionId], [oldCompanyId, oldQuestionId]) => {
  if (newCompanyId && newCompanyId !== oldCompanyId) {
    try {
      await loadPersonnel(newCompanyId)
    } catch (error) {
      console.error('Error loading personnel for new company:', error)
    }
  }

  if (newCompanyId && newQuestionId && (newQuestionId !== oldQuestionId || newCompanyId !== oldCompanyId)) {
    try {
      await Promise.all([
        loadAssignmentSummary(newCompanyId, newQuestionId),
        getCategories(newQuestionId),
        getTopics(newQuestionId),
        getFactors(newQuestionId)
      ])
    } catch (error) {
      console.error('Error loading assignment summary or structure data:', error)
    }
  }
}, { immediate: false })
</script>