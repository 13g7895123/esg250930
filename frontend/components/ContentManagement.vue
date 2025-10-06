<template>
  <div class="p-6">
    <!-- Page Header -->
    <PageHeader
      :title="title"
      :description="description"
      :show-back-button="showBackButton"
    />

    <!-- Data Table with Pagination -->
    <DataTable
      ref="dataTableRef"
      :data="contentData"
      :columns="columns"
      search-placeholder="搜尋題目..."
      :search-fields="['risk_category', 'risk_topic', 'risk_factor']"
      empty-title="還沒有題目內容"
      empty-message="開始建立您的第一個題目內容"
      no-search-results-title="沒有找到符合的題目"
      no-search-results-message="請嘗試其他搜尋關鍵字"
    >
      <!-- Actions Slot -->
      <template #actions>
        <div class="flex items-center space-x-3">
          <!-- Add Button -->
          <button
            @click="showAddModal = true"
            class="flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200"
          >
            <PlusIcon class="w-4 h-4 mr-2" />
            新增題目
          </button>

          <!-- Export/Import Dropdown Button -->
          <div class="relative" ref="dropdownRef">
            <button
              @click="toggleExportImportDropdown"
              class="flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200"
            >
              <ArrowDownTrayIcon class="w-4 h-4 mr-2" />
              匯出/匯入
              <ChevronDownIcon class="w-4 h-4 ml-2" />
            </button>

            <!-- Dropdown Menu -->
            <div
              v-if="showExportImportDropdown"
              class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-10"
            >
              <button
                @click="exportToExcel"
                class="w-full flex items-center px-4 py-3 text-left text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 rounded-t-lg"
              >
                <DocumentArrowDownIcon class="w-5 h-5 mr-3 text-green-600 dark:text-green-400" />
                <span>匯出 Excel</span>
              </button>
              <button
                @click="openImportModal"
                class="w-full flex items-center px-4 py-3 text-left text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"
              >
                <DocumentArrowUpIcon class="w-5 h-5 mr-3 text-blue-600 dark:text-blue-400" />
                <span>匯入 Excel</span>
              </button>
              <button
                v-if="latestBatchSummary || importDebugData"
                @click="openImportHistoryModal"
                class="w-full flex items-center px-4 py-3 text-left text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 rounded-b-lg border-t border-gray-200 dark:border-gray-600"
              >
                <InformationCircleIcon class="w-5 h-5 mr-3 text-purple-600 dark:text-purple-400" />
                <span>匯入紀錄</span>
              </button>
            </div>
          </div>

          <!-- Refresh Button -->
          <button
            @click="emit('refresh-content')"
            :disabled="isRefreshing"
            class="flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
          >
            <ArrowPathIcon
              class="w-4 h-4 mr-2"
              :class="{ 'animate-spin': isRefreshing }"
            />
            重新整理
          </button>
        </div>
      </template>

      <!-- Custom Actions Cell -->
      <template #cell-actions="{ item }">
        <div class="flex items-center space-x-2">
          <!-- Drag Handle with functional drag-and-drop -->
          <div
            class="relative group cursor-move drag-handle"
            draggable="true"
            @dragstart="handleDragStart($event, item)"
            @dragend="handleDragEnd"
            @dragover.prevent="handleDragOver($event, item)"
            @dragleave="handleDragLeave"
            @drop="handleDrop($event, item)"
          >
            <div class="p-2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 rounded-lg transition-colors duration-200">
              <Bars3Icon class="w-4 h-4" />
            </div>
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
              拖曳以排序
              <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
            </div>
          </div>

          <!-- Edit Button -->
          <div class="relative group">
            <button
              @click="editContent(item)"
              class="p-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors duration-200"
            >
              <PencilIcon class="w-4 h-4" />
            </button>
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
              編輯
              <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
            </div>
          </div>

          <!-- Question Editing Button -->
          <div class="relative group">
            <button
              @click="editQuestions(item)"
              class="p-2 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors duration-200"
            >
              <DocumentTextIcon class="w-4 h-4" />
            </button>
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
              題目編輯
              <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
            </div>
          </div>

          <!-- Delete Button -->
          <div class="relative group">
            <button
              @click="deleteContent(item)"
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

      <!-- Custom Risk Category Cell -->
      <template #cell-risk_category="{ item }">
        <div class="text-base text-gray-900 dark:text-white">
          <span v-if="item.category_id" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
            {{ getCategoryName(item.category_id) }}
          </span>
          <span v-else class="text-gray-400 dark:text-gray-500 italic">未分類</span>
        </div>
      </template>

      <!-- Custom Risk Topic Cell -->
      <template #cell-risk_topic="{ item }">
        <div class="text-base font-medium text-gray-900 dark:text-white">
          <span v-if="item.topic_id" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
            {{ getTopicName(item.topic_id) }}
          </span>
          <span v-else-if="item.topic" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
            {{ item.topic }}
          </span>
          <span v-else class="text-gray-400 dark:text-gray-500 italic">未設定</span>
        </div>
      </template>

      <!-- Custom Risk Factor Cell -->
      <template #cell-risk_factor="{ item }">
        <div class="text-base text-gray-900 dark:text-white">
          <span v-if="item.risk_factor_id" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
            {{ getRiskFactorName(item.risk_factor_id) }}
          </span>
          <span v-else-if="item.risk_factor" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
            {{ item.risk_factor }}
          </span>
          <span v-else class="text-gray-400 dark:text-gray-500 italic">未設定</span>
        </div>
      </template>

      <!-- Custom A Content Cell (Risk Factor Description with HTML rendering) -->
      <template #cell-a_content="{ item }">
        <div
          class="relative group"
          @mouseenter="showTooltip($event, item)"
          @mouseleave="hideTooltip"
        >
          <div
            v-html="item.factor_description || item.a_content || item.aContent || ''"
            class="text-base text-gray-500 dark:text-gray-400 line-clamp-2 overflow-hidden cursor-pointer"
          ></div>
        </div>
      </template>

      <!-- Custom Created At Cell -->
      <template #cell-created_at="{ item }">
        <div class="text-base text-gray-500 dark:text-gray-400">
          {{ formatDateTime(item.created_at) }}
        </div>
      </template>

      <!-- Empty Action Slot -->
      <template #emptyAction>
        <button
          @click="showAddModal = true"
          class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200"
        >
          <PlusIcon class="w-4 h-4 mr-2" />
          新增題目
        </button>
      </template>
    </DataTable>

    <!-- Hidden File Input for Import -->
    <input
      ref="fileInput"
      type="file"
      accept=".xlsx,.xls"
      @change="handleFileImport"
      class="hidden"
    />

    <!-- Tooltip for A Content (rendered at body level to avoid overflow issues) -->
    <Teleport to="body">
      <div
        v-if="tooltipData.visible && tooltipData.content"
        :style="{
          position: 'fixed',
          left: tooltipData.x + 'px',
          top: tooltipData.y + 'px',
          zIndex: 9999
        }"
        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-2xl p-4 max-w-2xl w-96 max-h-96 overflow-y-auto"
        @mouseenter="keepTooltipOpen"
        @mouseleave="hideTooltip"
      >
        <div
          class="text-sm text-gray-700 dark:text-gray-300 prose prose-sm dark:prose-invert max-w-none"
          v-html="tooltipData.content"
        ></div>
        <div class="absolute -top-2 left-4 w-4 h-4 bg-white dark:bg-gray-800 border-l border-t border-gray-200 dark:border-gray-700 transform rotate-45"></div>
      </div>
    </Teleport>

    <!-- Add/Edit Modal -->
    <div
      v-if="showAddModal || showEditModal"
      class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 overflow-y-auto"
      @click="closeModals"
    >
      <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-4xl my-8"
        @click.stop
      >
        <div class="p-6 max-h-[calc(100vh-4rem)] overflow-y-auto">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
            {{ showAddModal ? '新增題目' : '編輯題目' }}
          </h3>
          
          <form @submit.prevent="submitForm">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                風險類別 <span class="text-red-500">*</span>
              </label>
              <select
                v-model="formData.categoryId"
                required
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
              >
                <option value="">請選擇風險類別</option>
                <option
                  v-for="category in riskCategories"
                  :key="category.id"
                  :value="category.id"
                >
                  {{ category.category_name }}
                </option>
              </select>
            </div>

            <!-- Risk Topic Section (only shown when enabled) -->
            <div v-if="riskTopicsEnabled" class="mb-4">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                風險主題 <span class="text-red-500">*</span>
              </label>
              <select
                v-model="formData.topicId"
                :required="riskTopicsEnabled"
                :disabled="!formData.categoryId"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <option value="">{{ formData.categoryId ? '請選擇風險主題' : '請先選擇風險類別' }}</option>
                <option
                  v-for="topic in filteredRiskTopics"
                  :key="topic.id"
                  :value="topic.id"
                >
                  {{ topic.topic_name }}
                </option>
              </select>
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                風險因子 <span class="text-red-500">*</span>
              </label>

              <!-- Debug info for factors -->
              <div v-if="isDevelopment" class="mb-2 p-2 bg-gray-100 dark:bg-gray-800 rounded text-xs">
                <p>總風險因子: {{ riskFactors?.length || 0 }}</p>
                <p>篩選後因子: {{ filteredRiskFactors?.length || 0 }}</p>
                <p>選定類別: {{ formData.categoryId || '無' }}</p>
                <p>選定主題: {{ formData.topicId || '無' }}</p>
              </div>

              <select
                v-model="formData.riskFactorId"
                required
                :disabled="!formData.categoryId"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <option value="">{{ getFactorOptionText() }}</option>
                <option
                  v-for="factor in filteredRiskFactors"
                  :key="factor.id"
                  :value="factor.id"
                >
                  {{ factor.factor_name }}
                </option>
              </select>
            </div>

            <!-- Risk Factor Description Editor -->
            <div v-if="formData.riskFactorId" class="mb-4">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                風險因子描述 <span class="text-red-500">*</span>
              </label>
              <RichTextEditor
                :key="`factor-desc-${formData.riskFactorId}`"
                v-model="formData.factorDescription"
                placeholder="請輸入風險因子描述"
                :show-html-info="false"
              />
            </div>

            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="closeModals"
                class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors duration-200"
              >
                取消
              </button>
              <button
                type="submit"
                class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200"
              >
                {{ showAddModal ? '新增' : '更新' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Import Modal with Drag and Drop -->
    <div
      v-if="showImportModal"
      class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
      @click="closeImportModal"
    >
      <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl"
        @click.stop
      >
        <div class="p-6">
          <!-- Header -->
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
              匯入 Excel
            </h3>
            <button
              @click="closeImportModal"
              class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
            >
              <XMarkIcon class="w-6 h-6" />
            </button>
          </div>

          <!-- Download Template Button (moved above upload area) -->
          <div class="mb-6 flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
            <div class="flex items-center">
              <InformationCircleIcon class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-3" />
              <div>
                <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                  第一次使用匯入功能？
                </p>
                <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">
                  下載範本了解正確的格式
                </p>
              </div>
            </div>
            <button
              @click="downloadTemplate"
              class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm font-medium"
            >
              <DocumentTextIcon class="w-4 h-4 mr-2" />
              下載範本
            </button>
          </div>

          <!-- Drag and Drop Area -->
          <div
            @drop.prevent="handleFileDrop"
            @dragover.prevent="isDraggingFile = true"
            @dragleave.prevent="isDraggingFile = false"
            @click="triggerImport"
            :class="[
              'border-2 border-dashed rounded-lg p-12 text-center cursor-pointer transition-all duration-200',
              isDraggingFile
                ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
                : 'border-gray-300 dark:border-gray-600 hover:border-primary-400 dark:hover:border-primary-500 hover:bg-gray-50 dark:hover:bg-gray-700/50'
            ]"
          >
            <DocumentArrowUpIcon class="w-16 h-16 mx-auto mb-4 text-gray-400 dark:text-gray-500" />
            <p class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">
              點擊上傳或拖曳檔案至此
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
              支援 .xlsx、.xls 格式
            </p>
            <p v-if="selectedFileName" class="text-sm font-medium text-primary-600 dark:text-primary-400 mt-4">
              已選擇：{{ selectedFileName }}
            </p>
          </div>

          <!-- Instructions -->
          <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              注意事項：
            </h4>
            <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
              <li>• 第一行為範例資料，匯入時會自動跳過</li>
              <li>• 風險類別和風險因子為必填欄位</li>
              <li>• 系統將自動使用所選風險因子的描述作為內容</li>
              <li>• 如果類別、主題、因子不存在，系統會自動建立</li>
              <li>• 文字格式支援：使用【文字】表示粗體，_文字_表示斜體</li>
              <li>• 匯入過程中若有錯誤，會顯示詳細的錯誤訊息</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Import History Modal (Point 46 - DataTable版本) -->
    <div
      v-if="showDebugModal && !showBatchDetails"
      class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
      @click="showDebugModal = false"
    >
      <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-6xl max-h-[90vh] overflow-hidden flex flex-col"
        @click.stop
      >
        <!-- Header -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
              匯入紀錄
            </h3>
            <button
              @click="showDebugModal = false"
              class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
            >
              <XMarkIcon class="w-6 h-6" />
            </button>
          </div>

          <!-- Latest Batch Summary -->
          <div v-if="latestBatchSummary">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-4">
              <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border border-green-200 dark:border-green-800">
                <p class="text-xs text-green-600 dark:text-green-400 font-medium mb-1 flex items-center">
                  <span class="mr-1">✓</span> 成功匯入
                </p>
                <p class="text-2xl font-bold text-green-900 dark:text-green-100">
                  {{ latestBatchSummary.rows_imported || 0 }}
                </p>
              </div>
              <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-4 border border-orange-200 dark:border-orange-800">
                <p class="text-xs text-orange-600 dark:text-orange-400 font-medium mb-1 flex items-center">
                  <span class="mr-1">⊘</span> 重複跳過
                </p>
                <p class="text-2xl font-bold text-orange-900 dark:text-orange-100">
                  {{ latestBatchSummary.rows_skipped || 0 }}
                </p>
              </div>
              <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 border border-red-200 dark:border-red-800">
                <p class="text-xs text-red-600 dark:text-red-400 font-medium mb-1 flex items-center">
                  <span class="mr-1">✗</span> 失敗筆數
                </p>
                <p class="text-2xl font-bold text-red-900 dark:text-red-100">
                  {{ latestBatchSummary.rows_failed || 0 }}
                </p>
              </div>
              <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                <p class="text-xs text-blue-600 dark:text-blue-400 font-medium mb-1">Excel 總行數</p>
                <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                  {{ latestBatchSummary.total_rows_in_excel || 0 }}
                </p>
              </div>
              <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 border border-purple-200 dark:border-purple-800">
                <p class="text-xs text-purple-600 dark:text-purple-400 font-medium mb-1">實際資料行數</p>
                <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">
                  {{ latestBatchSummary.data_rows_processed || 0 }}
                </p>
              </div>
            </div>

            <!-- Symbol Legend -->
            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
              <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">符號說明</p>
              <div class="flex flex-wrap gap-x-6 gap-y-1 text-xs">
                <div class="flex items-center text-green-600 dark:text-green-400">
                  <span class="mr-1 font-bold">✓</span>
                  <span>成功匯入的資料筆數</span>
                </div>
                <div class="flex items-center text-orange-600 dark:text-orange-400">
                  <span class="mr-1 font-bold">⊘</span>
                  <span>因重複而跳過的資料筆數</span>
                </div>
                <div class="flex items-center text-red-600 dark:text-red-400">
                  <span class="mr-1 font-bold">✗</span>
                  <span>匯入失敗的資料筆數</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Content - Scrollable -->
        <div class="p-6 overflow-y-auto flex-1">
          <!-- DataTable Component -->
          <DataTable
            :data="formattedBatchData"
            :columns="importHistoryColumns"
            :loading="isLoadingHistory"
            :searchable="true"
            search-placeholder="搜尋日期 (格式: YYYY-MM-DD，例如: 2025-10-06)"
            empty-message="暫無匯入記錄"
            :initial-page-size="10"
          >
            <!-- Actions Slot for Buttons -->
            <template #actions>
              <!-- Refresh Button -->
              <button
                @click="refreshImportHistory"
                class="flex items-center px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200 text-sm"
              >
                <ArrowPathIcon class="w-4 h-4 mr-1.5" />
                重新整理
              </button>
            </template>
            <!-- Actions Column -->
            <template #cell-actions="{ item }">
              <div class="relative group">
                <button
                  @click="fetchBatchDetails(item.batch_id)"
                  class="p-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors duration-200"
                >
                  <DocumentTextIcon class="w-5 h-5" />
                </button>
                <div class="absolute left-full ml-2 top-1/2 transform -translate-y-1/2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                  檢視詳細資料
                </div>
              </div>
            </template>

            <!-- Time Column -->
            <template #cell-created_at="{ item }">
              <span class="whitespace-nowrap">{{ item.created_at_formatted }}</span>
            </template>

            <!-- Summary Column -->
            <template #cell-summary="{ item }">
              <div class="flex items-center space-x-4">
                <span class="text-green-600 dark:text-green-400">✓ {{ item.success_count }}</span>
                <span class="text-orange-600 dark:text-orange-400">⊘ {{ item.skipped_count }}</span>
                <span class="text-red-600 dark:text-red-400">✗ {{ item.error_count }}</span>
              </div>
            </template>
          </DataTable>
        </div>

        <!-- Footer -->
        <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex justify-end">
          <button
            @click="showDebugModal = false"
            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200"
          >
            關閉
          </button>
        </div>
      </div>
    </div>

    <!-- Batch Details Modal (Point 46) -->
    <div
      v-if="showBatchDetails && selectedBatchDetails"
      class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
      @click="closeBatchDetails"
    >
      <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-6xl max-h-[90vh] overflow-hidden flex flex-col"
        @click.stop
      >
        <!-- Header -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
            批次詳細資料
          </h3>
          <button
            @click="closeBatchDetails"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
          >
            <XMarkIcon class="w-6 h-6" />
          </button>
        </div>

        <!-- Content -->
        <div class="p-6 overflow-y-auto flex-1">
          <!-- Summary -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border border-green-200 dark:border-green-800">
              <p class="text-xs text-green-600 dark:text-green-400 font-medium mb-1">成功</p>
              <p class="text-2xl font-bold text-green-900 dark:text-green-100">
                {{ selectedBatchDetails.summary.success }}
              </p>
            </div>
            <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-4 border border-orange-200 dark:border-orange-800">
              <p class="text-xs text-orange-600 dark:text-orange-400 font-medium mb-1">跳過</p>
              <p class="text-2xl font-bold text-orange-900 dark:text-orange-100">
                {{ selectedBatchDetails.summary.skipped }}
              </p>
            </div>
            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 border border-red-200 dark:border-red-800">
              <p class="text-xs text-red-600 dark:text-red-400 font-medium mb-1">失敗</p>
              <p class="text-2xl font-bold text-red-900 dark:text-red-100">
                {{ selectedBatchDetails.summary.error }}
              </p>
            </div>
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
              <p class="text-xs text-blue-600 dark:text-blue-400 font-medium mb-1">總計</p>
              <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                {{ selectedBatchDetails.summary.total }}
              </p>
            </div>
          </div>

          <!-- Records DataTable -->
          <DataTable
            :data="formattedBatchRecords"
            :columns="batchDetailsColumns"
            :searchable="true"
            :search-fields="['row_number', 'category_name', 'topic_name', 'factor_name', 'reason']"
            search-placeholder="搜尋行號、類別、主題、因子或備註..."
            empty-message="暫無記錄"
            :initial-page-size="20"
          >
            <!-- Actions Slot for Buttons -->
            <template #actions>
              <!-- Refresh Button -->
              <button
                @click="fetchBatchDetails(selectedBatchDetails.batch_id)"
                class="flex items-center px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200 text-sm"
              >
                <ArrowPathIcon class="w-4 h-4 mr-1.5" />
                重新整理
              </button>
            </template>
            <!-- Status Column -->
            <template #cell-status="{ item }">
              <span :class="[
                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                item.status === 'success'
                  ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300'
                  : item.status === 'skipped'
                  ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300'
                  : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300'
              ]">
                {{ item.status === 'success' ? '✓ 成功' : item.status === 'skipped' ? '⊘ 跳過' : '✗ 失敗' }}
              </span>
            </template>

            <!-- Row Number Column -->
            <template #cell-row_number="{ item }">
              <span class="whitespace-nowrap">{{ item.row_number }}</span>
            </template>

            <!-- Category Name Column -->
            <template #cell-category_name="{ item }">
              {{ item.category_name || 'N/A' }}
            </template>

            <!-- Topic Name Column -->
            <template #cell-topic_name="{ item }">
              {{ item.topic_name || 'N/A' }}
            </template>

            <!-- Factor Name Column -->
            <template #cell-factor_name="{ item }">
              {{ item.factor_name || 'N/A' }}
            </template>

            <!-- Reason Column -->
            <template #cell-reason="{ item }">
              <span v-if="item.status === 'error'" class="text-red-600 dark:text-red-400">
                {{ item.error_message || item.reason }}
              </span>
              <span v-else-if="item.status === 'skipped'" class="text-orange-600 dark:text-orange-400">
                {{ item.reason === 'duplicate' ? `重複（ID: ${item.duplicate_id}）` : item.reason }}
              </span>
              <span v-else class="text-green-600 dark:text-green-400">
                ID: {{ item.inserted_id }}
              </span>
            </template>
          </DataTable>
        </div>

        <!-- Footer -->
        <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex justify-between">
          <button
            @click="closeBatchDetails"
            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-200"
          >
            返回列表
          </button>
          <button
            @click="closeBatchDetails"
            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200"
          >
            關閉
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal
      :model-value="showDeleteModal"
      title="確認刪除"
      :message="`確定要刪除題目「${contentToDelete?.topic}」嗎？`"
      details="此操作無法復原。"
      type="danger"
      cancel-text="取消"
      confirm-text="刪除"
      @update:model-value="(value) => showDeleteModal = value"
      @close="showDeleteModal = false"
      @confirm="confirmDelete"
    />

    <!-- Risk Category Modal -->
    <Modal
      :model-value="showCategoryModal && showRiskCategoryButton"
      title="風險類別管理"
      size="4xl"
      body-class="overflow-y-auto"
      @update:model-value="(value) => showCategoryModal = value"
      @close="showCategoryModal = false"
    >
      <DataTable
        :data="riskCategories"
        :columns="categoryColumns"
        search-placeholder="搜尋類別..."
        :search-fields="['category_name']"
        empty-title="還沒有風險類別"
        empty-message="開始建立您的第一個風險類別"
        no-search-results-title="沒有找到符合的類別"
        no-search-results-message="請嘗試其他搜尋關鍵字"
      >
        <!-- Actions Slot for Category Modal -->
        <template #actions>
          <button
            @click="showAddCategoryModal = true"
            class="flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200"
          >
            <PlusIcon class="w-4 h-4 mr-2" />
            新增類別
          </button>
        </template>

        <!-- Custom Actions Cell for Categories -->
        <template #cell-actions="{ item }">
          <div class="flex items-center space-x-2">
            <!-- Edit Button -->
            <div class="relative group">
              <button
                @click="editCategory(item)"
                class="p-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors duration-200"
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
                @click="deleteCategory(item)"
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
      </DataTable>
    </Modal>

    <!-- Add Category Modal -->
    <Modal
      :model-value="showAddCategoryModal || showEditCategoryModal"
      :title="showAddCategoryModal ? '新增風險類別' : '編輯風險類別'"
      size="md"
      @update:model-value="handleCategoryModalClose"
      @close="closeCategoryModals"
    >
      <form @submit.prevent="submitCategoryForm">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            類別名稱
          </label>
          <input
            ref="categoryNameInput"
            v-model="categoryFormData.category_name"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
            placeholder="請輸入類別名稱"
          />
        </div>

        <div class="flex justify-end space-x-3 mt-6">
          <button
            type="button"
            @click="closeCategoryModals"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors duration-200"
          >
            取消
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200"
          >
            {{ showAddCategoryModal ? '新增' : '更新' }}
          </button>
        </div>
      </form>
    </Modal>

    <!-- Risk Factor Modal -->
    <Modal
      :model-value="showRiskFactorModal"
      title="風險因子管理"
      size="3xl"
      @update:model-value="(value) => showRiskFactorModal = value"
      @close="showRiskFactorModal = false"
    >
      <div class="p-4">
        <p class="text-gray-600 dark:text-gray-400 mb-4">這裡可以管理風險因子的相關設定</p>
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
          <div class="flex items-center">
            <ExclamationTriangleIcon class="w-5 h-5 text-yellow-500 mr-2" />
            <span class="text-yellow-800 dark:text-yellow-200">風險因子功能開發中...</span>
          </div>
        </div>
      </div>
    </Modal>

    <!-- Risk Topic Modal -->
    <Modal
      :model-value="showRiskTopicModal"
      title="風險主題管理"
      size="3xl"
      @update:model-value="(value) => showRiskTopicModal = value"
      @close="showRiskTopicModal = false"
    >
      <div class="p-4">
        <p class="text-gray-600 dark:text-gray-400 mb-4">這裡可以管理風險主題的相關設定</p>
        <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-4">
          <div class="flex items-center">
            <ChatBubbleLeftRightIcon class="w-5 h-5 text-purple-500 mr-2" />
            <span class="text-purple-800 dark:text-purple-200">風險主題功能開發中...</span>
          </div>
        </div>
      </div>
    </Modal>

  </div>
</template>

<script setup>
import {
  PlusIcon,
  PencilIcon,
  TrashIcon,
  TagIcon,
  Bars3Icon,
  DocumentTextIcon,
  XMarkIcon,
  ExclamationTriangleIcon,
  ChatBubbleLeftRightIcon,
  ArrowPathIcon,
  DocumentArrowDownIcon,
  DocumentArrowUpIcon,
  ArrowDownTrayIcon,
  ChevronDownIcon,
  InformationCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  title: {
    type: String,
    default: '內容管理'
  },
  description: {
    type: String,
    default: '管理主題內容'
  },
  showBackButton: {
    type: Boolean,
    default: false
  },
  contentData: {
    type: Array,
    required: true
  },
  riskCategories: {
    type: Array,
    default: () => []
  },
  riskTopicsEnabled: {
    type: Boolean,
    default: true
  },
  riskTopics: {
    type: Array,
    default: () => []
  },
  riskFactors: {
    type: Array,
    default: () => []
  },
  showRiskCategoryButton: {
    type: Boolean,
    default: false
  },
  showManagementButtons: {
    type: Boolean,
    default: true
  },
  contentType: {
    type: String,
    default: 'template' // 'template' or 'question'
  },
  parentId: {
    type: [String, Number],
    required: true
  },
  isRefreshing: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits([
  'add-content',
  'update-content',
  'delete-content',
  'reorder-content',
  'add-category',
  'update-category',
  'delete-category',
  'fetch-topics',
  'fetch-factors',
  'refresh-content'
])

// Reactive data
const showAddModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const showCategoryModal = ref(false)
const showAddCategoryModal = ref(false)
const showEditCategoryModal = ref(false)
const showRiskFactorModal = ref(false)
const showRiskTopicModal = ref(false)
const contentToDelete = ref(null)
const editingContent = ref(null)
const editingCategory = ref(null)
const categoryNameInput = ref(null)
const dataTableRef = ref(null)
const fileInput = ref(null)
const showExportImportDropdown = ref(false)
const showImportModal = ref(false)
const isDraggingFile = ref(false)
const selectedFileName = ref('')
const dropdownRef = ref(null)
const showDebugModal = ref(false)
const importDebugData = ref(null)
const showErrorRecords = ref(false)

// Import history state (Point 46)
const importHistoryBatches = ref([])
const latestBatchSummary = ref(null)
const selectedBatchDetails = ref(null)
const isLoadingHistory = ref(false)
const showBatchDetails = ref(false)
const showAllErrorRecords = ref(false)

// Drag and drop state
const draggedItem = ref(null)
const dragOverItem = ref(null)

// Tooltip state for a_content
const tooltipData = ref({
  visible: false,
  content: '',
  x: 0,
  y: 0
})
let tooltipTimeout = null

const formData = ref({
  categoryId: '',
  topicId: '',
  riskFactorId: '',
  topic: '',
  factorDescription: ''
})

const categoryFormData = ref({
  category_name: ''
})

// Debug mode control - can be toggled to show/hide debug info
const showDebugInfo = ref(false)

// Development mode check (SSR safe) - currently disabled
const isDevelopment = computed(() => {
  return showDebugInfo.value
})

// Get selected factor's description for read-only display
const selectedFactorDescription = computed(() => {
  if (!formData.value.riskFactorId) return null
  const factor = props.riskFactors.find(f => f.id === parseInt(formData.value.riskFactorId))
  return factor?.description || null
})

// DataTable columns configuration
const columns = computed(() => {
  const baseColumns = [
    {
      key: 'actions',
      label: '功能',
      sortable: false,
      cellClass: 'text-base text-gray-900 dark:text-white'
    },
    {
      key: 'risk_category',
      label: '風險類別',
      sortable: false,
      cellClass: 'text-base text-gray-900 dark:text-white'
    }
  ]

  // 只有當風險主題啟用時才顯示風險主題欄位
  if (props.riskTopicsEnabled) {
    baseColumns.push({
      key: 'risk_topic',
      label: '風險主題',
      sortable: false,
      cellClass: 'text-base font-medium text-gray-900 dark:text-white'
    })
  }

  baseColumns.push(
    {
      key: 'risk_factor',
      label: '風險因子',
      sortable: false,
      cellClass: 'text-base text-gray-900 dark:text-white'
    },
    {
      key: 'a_content',
      label: '風險因子描述',
      sortable: true,
      cellClass: 'text-base text-gray-500 dark:text-gray-400'
    },
    {
      key: 'created_at',
      label: '建立時間',
      sortable: true,
      cellClass: 'text-base text-gray-500 dark:text-gray-400'
    }
  )

  return baseColumns
})


// DataTable columns configuration for risk categories
const categoryColumns = ref([
  {
    key: 'actions',
    label: '功能',
    sortable: false,
    cellClass: 'text-base text-gray-900 dark:text-white'
  },
  {
    key: 'category_name',
    label: '類別',
    sortable: true,
    cellClass: 'text-base font-medium text-gray-900 dark:text-white'
  }
])

// Helper methods to get names by ID
const getCategoryName = (categoryId) => {
  const category = props.riskCategories.find(cat => cat.id === categoryId)
  return category ? category.category_name : '未知類別'
}

const getTopicName = (topicId) => {
  const topic = props.riskTopics.find(t => t.id === topicId)
  return topic ? topic.topic_name : '未設定'
}

const getRiskFactorName = (riskFactorId) => {
  const factor = props.riskFactors.find(f => f.id === riskFactorId)
  return factor ? factor.factor_name : '未設定'
}

// Helper method to strip HTML tags and truncate text
const stripHtmlTags = (html, maxLength = 100) => {
  if (!html) return ''

  // Create a temporary div to parse HTML
  const tmp = document.createElement('div')
  tmp.innerHTML = html

  // Get text content (strips all HTML tags)
  const text = tmp.textContent || tmp.innerText || ''

  // Truncate if needed
  if (text.length > maxLength) {
    return text.substring(0, maxLength) + '...'
  }

  return text
}

// Helper method to format date time
const formatDateTime = (dateTime) => {
  if (!dateTime) return ''

  try {
    const date = new Date(dateTime)
    return date.toLocaleString('zh-TW', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
      hour12: false
    })
  } catch (error) {
    return dateTime
  }
}

// Helper method to convert HTML to Excel-compatible rich text
const htmlToExcelRichText = (html) => {
  if (!html) return ''

  // For now, we'll export as plain text with formatting hints
  // Excel's rich text support in SheetJS is limited
  const tmp = document.createElement('div')
  tmp.innerHTML = html

  // Extract text while preserving some structure
  let text = tmp.innerHTML
    .replace(/<br\s*\/?>/gi, '\n')
    .replace(/<\/p>/gi, '\n')
    .replace(/<p[^>]*>/gi, '')
    .replace(/<strong[^>]*>(.*?)<\/strong>/gi, '【$1】') // Bold markers
    .replace(/<b[^>]*>(.*?)<\/b>/gi, '【$1】')
    .replace(/<em[^>]*>(.*?)<\/em>/gi, '_$1_') // Italic markers
    .replace(/<i[^>]*>(.*?)<\/i>/gi, '_$1_')
    .replace(/<u[^>]*>(.*?)<\/u>/gi, '$1') // Underline (removed)
    .replace(/<li[^>]*>/gi, '• ')
    .replace(/<\/li>/gi, '\n')
    .replace(/<[^>]+>/g, '') // Remove remaining tags
    .replace(/&nbsp;/g, ' ')
    .replace(/&amp;/g, '&')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/&quot;/g, '"')
    .trim()

  return text
}

// Helper method to convert text with formatting markers back to HTML
const textToHtml = (text) => {
  if (!text) return ''

  // Convert formatting markers back to HTML
  let html = text
    .replace(/【(.*?)】/g, '<strong>$1</strong>') // Bold
    .replace(/_(.*?)_/g, '<em>$1</em>') // Italic
    .replace(/\n/g, '<br>') // Line breaks

  return `<p>${html}</p>`
}

// Tooltip methods for a_content display
const showTooltip = (event, item) => {
  const content = item.factor_description || item.a_content || item.aContent
  if (!content) return

  // Clear any existing timeout
  if (tooltipTimeout) {
    clearTimeout(tooltipTimeout)
  }

  // Get the element's position
  const rect = event.target.getBoundingClientRect()

  // Position tooltip below the element
  tooltipData.value = {
    visible: true,
    content: content,
    x: rect.left,
    y: rect.bottom + 8 // 8px gap below the element
  }
}

const hideTooltip = () => {
  // Add a small delay to allow mouse to move to tooltip
  tooltipTimeout = setTimeout(() => {
    tooltipData.value.visible = false
  }, 100)
}

const keepTooltipOpen = () => {
  // Cancel hide timeout when mouse enters tooltip
  if (tooltipTimeout) {
    clearTimeout(tooltipTimeout)
  }
}

// Helper method for factor option text
const getFactorOptionText = () => {
  if (!formData.value.categoryId) {
    return '請先選擇風險類別'
  }
  return '請選擇風險因子'
}

// Computed properties for filtering dropdown options

// Filter risk topics based on selected category
const filteredRiskTopics = computed(() => {
  let topics = props.riskTopics || []

  // Filter by selected category if available
  if (formData.value.categoryId) {
    topics = topics.filter(topic => topic.category_id == formData.value.categoryId)
  }

  return topics
})

// Filter risk factors based on selected category and topic  
const filteredRiskFactors = computed(() => {
  let factors = props.riskFactors || []

  console.log('=== Filtering risk factors ===')
  console.log('Total factors:', factors.length)
  console.log('Selected category:', formData.value.categoryId)
  console.log('Selected topic:', formData.value.topicId)
  console.log('Topics enabled:', props.riskTopicsEnabled)

  if (factors.length > 0) {
    console.log('Sample factor (full data):', factors[0])
    console.log('Sample factor has description?', !!factors[0].description)
    console.log('Sample factor description length:', factors[0].description?.length || 0)
  }

  // 如果沒有選擇類別，顯示所有因子
  if (!formData.value.categoryId) {
    console.log('No category selected, returning all factors')
    return factors
  }

  // 如果啟用主題且選擇了主題，按主題篩選
  if (props.riskTopicsEnabled && formData.value.topicId) {
    factors = factors.filter(factor => {
      // 強制類型轉換為字符串進行比較
      const factorTopicId = String(factor.topic_id || '')
      const selectedTopicId = String(formData.value.topicId || '')
      const match = factorTopicId === selectedTopicId
      console.log(`Factor ${factor.id} (${factor.factor_name}) topic_id: "${factorTopicId}" (type: ${typeof factor.topic_id}), matches topic "${selectedTopicId}" (type: ${typeof formData.value.topicId}): ${match}`)
      return match
    })
    console.log('After topic filter:', factors.length)
  } else {
    // 如果主題未啟用或未選擇主題，則按分類篩選
    factors = factors.filter(factor => {
      // 檢查直接分類匹配
      const directCategoryMatch = factor.category_id == formData.value.categoryId

      // 檢查透過主題的分類匹配（後端已經在SQL中處理了這個邏輯）
      // 從API返回的category_name應該已經正確反映了分類資訊
      const hasCategoryName = !!factor.category_name

      const match = directCategoryMatch || hasCategoryName
      console.log(`Factor ${factor.id} (${factor.factor_name}) - direct: ${directCategoryMatch}, has category_name: ${hasCategoryName}, final: ${match}`)
      return match
    })
    console.log('After category filter:', factors.length)
  }

  console.log('=== Final filtered factors ===')
  console.log('Count:', factors.length)
  if (factors.length > 0) {
    console.log('Factors:', factors.map(f => ({
      id: f.id,
      name: f.factor_name,
      topic_id: f.topic_id,
      category_id: f.category_id,
      hasDescription: !!f.description,
      descriptionLength: f.description?.length || 0
    })))
    console.log('First factor full data:', factors[0])
  }

  return factors
})

// Note: Drag and drop functionality is not implemented in DataTable
// Consider implementing manual reordering through buttons or order field if needed

// Methods
const editContent = async (content) => {
  editingContent.value = content

  // 保存原始值
  const originalTopicId = content.topic_id || ''
  const originalRiskFactorId = content.risk_factor_id || ''

  // 先設置基本欄位
  formData.value.topic = content.topic || ''

  // 編輯時需要手動載入對應的 topics 和 factors 到下拉選單
  if (content.category_id) {
    try {
      // 先設置 categoryId，這會觸發 watcher 並清除 topicId 和 riskFactorId
      formData.value.categoryId = content.category_id

      // 載入該 category 的 topics（如果啟用）
      if (props.riskTopicsEnabled) {
        await fetchTopicsForCategory(content.category_id)
      }

      // 等待一個 tick 確保下拉選單資料已載入
      await nextTick()

      // 現在設置 topicId
      if (originalTopicId && props.riskTopicsEnabled) {
        formData.value.topicId = originalTopicId
        // 載入該 topic 的 factors
        await fetchFactorsForTopic(originalTopicId, content.category_id)
        // 等待 API 完成並更新 props
        await new Promise(resolve => setTimeout(resolve, 500))
      } else {
        // 載入該 category 的 factors
        await fetchFactorsForCategory(content.category_id)
        // 等待 API 完成並更新 props
        await new Promise(resolve => setTimeout(resolve, 500))
      }

      // 等待多個 tick 確保 factors 資料已載入到 props
      await nextTick()
      await nextTick()

      console.log('[EditContent] Available factors after fetch:', props.riskFactors?.length || 0)

      // 最後設置 riskFactorId
      if (originalRiskFactorId) {
        formData.value.riskFactorId = originalRiskFactorId

        // Wait for the watcher to populate the description
        await nextTick()
        await nextTick()

        // Find the factor and populate description (convert both IDs to numbers)
        const searchId = parseInt(originalRiskFactorId)
        const factor = props.riskFactors.find(f => parseInt(f.id) === searchId)
        console.log('[EditContent] Looking for factor ID:', searchId)
        console.log('[EditContent] Factor ID type in search:', typeof searchId)
        console.log('[EditContent] Found factor:', factor ? `Yes (${factor.factor_name})` : 'No')

        if (factor) {
          console.log('[EditContent] Factor description:', factor.description ? 'Has description' : 'No description')
          if (factor.description) {
            formData.value.factorDescription = factor.description
            await nextTick()
          }
        } else {
          console.warn('[EditContent] Factor ID', searchId, 'not found in', props.riskFactors?.length || 0, 'factors')
          console.log('[EditContent] Available factor IDs:', props.riskFactors?.map(f => f.id) || [])
          console.log('[EditContent] Available factor IDs (as numbers):', props.riskFactors?.map(f => parseInt(f.id)) || [])
        }
      }
    } catch (error) {
      console.error('Failed to load dropdown data for edit:', error)
    }
  } else {
    // 如果沒有 category_id，直接設置空值
    formData.value.categoryId = ''
    formData.value.topicId = ''
    formData.value.riskFactorId = ''
    formData.value.factorDescription = ''
  }

  showEditModal.value = true
}

const deleteContent = (content) => {
  contentToDelete.value = content
  showDeleteModal.value = true
}

const confirmDelete = () => {
  if (contentToDelete.value) {
    const { $notify } = useNuxtApp()
    $notify.loading('刪除中...')
    emit('delete-content', contentToDelete.value.id)
  }
  showDeleteModal.value = false
  contentToDelete.value = null
}

const submitForm = () => {
  // Validate required fields - risk factor is now required
  if (!formData.value.riskFactorId) {
    alert('請選擇風險因子')
    return
  }

  // Validate factor description is required
  if (!formData.value.factorDescription || formData.value.factorDescription.trim() === '') {
    alert('請輸入風險因子描述')
    return
  }

  // 只傳送表單實際填寫的欄位，其他由後端處理預設值
  const submitData = {
    categoryId: formData.value.categoryId,
    riskFactorId: formData.value.riskFactorId || null,
    factorDescription: formData.value.factorDescription
  }

  // Handle topic data based on whether risk topics are enabled
  if (props.riskTopicsEnabled) {
    submitData.topicId = formData.value.topicId
  } else {
    submitData.topic = formData.value.topic
  }

  if (showAddModal.value) {
    // Add new content
    emit('add-content', submitData)
  } else if (showEditModal.value) {
    // Update existing content
    emit('update-content', editingContent.value.id, submitData)
  }

  closeModals()
}

const closeModals = () => {
  showAddModal.value = false
  showEditModal.value = false
  editingContent.value = null
  formData.value.categoryId = ''
  formData.value.topicId = ''
  formData.value.riskFactorId = ''
  formData.value.topic = ''
  formData.value.factorDescription = ''
}

// Question management methods
const editQuestions = (content) => {
  // Navigate to the appropriate question editing page based on content type
  const router = useRouter()

  if (props.contentType === 'template') {
    // Navigate to unified editor with template mode
    router.push(`/admin/risk-assessment/editor/template-${props.parentId}-${content.id}`)
  } else {
    // Navigate to unified editor with question mode
    router.push(`/admin/risk-assessment/editor/question-${props.parentId}-${content.id}`)
  }
}


// Category management methods
const editCategory = (category) => {
  editingCategory.value = category
  categoryFormData.value.category_name = category.category_name
  showEditCategoryModal.value = true
}

const deleteCategory = (category) => {
  emit('delete-category', category.id)
}

const submitCategoryForm = () => {
  if (showAddCategoryModal.value) {
    // Add new category
    emit('add-category', {
      category_name: categoryFormData.value.category_name
    })
  } else if (showEditCategoryModal.value) {
    // Update existing category
    emit('update-category', editingCategory.value.id, {
      category_name: categoryFormData.value.category_name
    })
  }
  closeCategoryModals()
}

const closeCategoryModals = () => {
  showAddCategoryModal.value = false
  showEditCategoryModal.value = false
  editingCategory.value = null
  categoryFormData.value.category_name = ''
}

const handleCategoryModalClose = (value) => {
  if (!value) {
    closeCategoryModals()
  }
}

// Helper functions for dynamic API calls
const fetchTopicsForCategory = async (categoryId) => {
  emit('fetch-topics', categoryId)
}

const fetchFactorsForCategory = async (categoryId) => {
  emit('fetch-factors', { categoryId })
}

const fetchFactorsForTopic = async (topicId, categoryId) => {
  emit('fetch-factors', { categoryId, topicId })
}

// Watch for category modal opening to set input focus
watch(() => showAddCategoryModal.value || showEditCategoryModal.value, (newValue) => {
  if (newValue) {
    nextTick(() => {
      categoryNameInput.value?.focus()
    })
  }
})

// Watch for category changes to implement cascading selection
watch(() => formData.value.categoryId, async (newCategoryId, oldCategoryId) => {
  console.log('Category changed from', oldCategoryId, 'to', newCategoryId)

  // Clear topic and risk factor when category changes
  if (newCategoryId !== oldCategoryId) {
    formData.value.topicId = ''
    formData.value.riskFactorId = ''
    formData.value.factorDescription = ''

    // Fetch topics for the selected category if risk topics are enabled
    if (newCategoryId && props.riskTopicsEnabled) {
      try {
        console.log('Fetching topics for category:', newCategoryId)
        await fetchTopicsForCategory(newCategoryId)
      } catch (error) {
        console.error('Failed to fetch topics for category:', error)
      }
    }

    // Fetch factors for the selected category
    if (newCategoryId) {
      try {
        console.log('Fetching factors for category:', newCategoryId)
        await fetchFactorsForCategory(newCategoryId)
      } catch (error) {
        console.error('Failed to fetch factors for category:', error)
      }
    }
  }
})

// Watch for topic changes to clear risk factor selection
watch(() => formData.value.topicId, async (newTopicId, oldTopicId) => {
  console.log('Topic changed from', oldTopicId, 'to', newTopicId)

  // Clear risk factor when topic changes
  if (newTopicId !== oldTopicId) {
    formData.value.riskFactorId = ''
    formData.value.factorDescription = ''

    // Fetch factors for the selected topic if risk topics are enabled and topic is selected
    if (newTopicId && props.riskTopicsEnabled && formData.value.categoryId) {
      try {
        console.log('Fetching factors for topic:', newTopicId, 'and category:', formData.value.categoryId)
        await fetchFactorsForTopic(newTopicId, formData.value.categoryId)
      } catch (error) {
        console.error('Failed to fetch factors for topic:', error)
      }
    }
  }
})

// Watch for risk factor changes to auto-populate description
watch(() => formData.value.riskFactorId, async (newFactorId, oldFactorId) => {
  console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━')
  console.log('[Watcher] Risk factor ID changed')
  console.log('[Watcher] From:', oldFactorId, '→ To:', newFactorId)
  console.log('[Watcher] Total available factors:', props.riskFactors?.length || 0)
  console.log('[Watcher] Factor IDs in props:', props.riskFactors?.map(f => f.id) || [])

  if (newFactorId) {
    // Wait for the next tick to ensure DOM and props are updated
    await nextTick()

    // Convert both IDs to numbers for comparison to handle type mismatch
    const searchId = parseInt(newFactorId)
    const factor = props.riskFactors.find(f => parseInt(f.id) === searchId)
    console.log('[Watcher] Searching for factor ID:', searchId)
    console.log('[Watcher] Type of search ID:', typeof searchId)
    console.log('[Watcher] Sample factor ID type:', props.riskFactors[0] ? typeof props.riskFactors[0].id : 'N/A')
    console.log('[Watcher] Found:', factor ? '✅ Yes' : '❌ No')

    if (factor) {
      console.log('[Watcher] Factor details:', {
        id: factor.id,
        name: factor.factor_name,
        hasDescription: !!factor.description,
        descriptionLength: factor.description?.length || 0
      })

      // Always set description from factor, even if empty
      const description = factor.description || factor.factor_description || ''

      // Use nextTick again to ensure the value is properly set
      await nextTick()
      formData.value.factorDescription = description

      // Force another update cycle to ensure RichTextEditor receives the value
      await nextTick()
      console.log('[Watcher] ✅ Description set successfully')
    } else {
      console.warn('[Watcher] ❌ Factor not found - clearing description')
      formData.value.factorDescription = ''
    }
  } else {
    // Clear description when no factor is selected
    formData.value.factorDescription = ''
    console.log('[Watcher] Cleared (no factor selected)')
  }
  console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━')
})

// Watch props.riskFactors to debug data structure
watch(() => props.riskFactors, (newFactors) => {
  if (newFactors && newFactors.length > 0) {
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━')
    console.log('[Props] riskFactors updated')
    console.log('[Props] Total factors:', newFactors.length)
    console.log('[Props] First factor sample:', newFactors[0])
    console.log('[Props] First factor has description?', !!newFactors[0]?.description)
    console.log('[Props] Description content:', newFactors[0]?.description?.substring(0, 100) || 'No description')
    console.log('[Props] All factor IDs:', newFactors.map(f => f.id))
    console.log('[Props] Factors with description:', newFactors.filter(f => f.description).length)
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━')
  }
}, { deep: true })

// Drag and drop handlers
const handleDragStart = (event, item) => {
  draggedItem.value = item
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('text/html', event.target)

  // Add visual feedback
  event.target.closest('tr')?.classList.add('opacity-50')
}

const handleDragEnd = (event) => {
  // Remove visual feedback
  event.target.closest('tr')?.classList.remove('opacity-50')
  draggedItem.value = null
  dragOverItem.value = null
}

const handleDragOver = (event, item) => {
  event.preventDefault()
  dragOverItem.value = item

  // Add visual feedback for drop target
  const row = event.target.closest('tr')
  if (row && draggedItem.value && draggedItem.value.id !== item.id) {
    row.classList.add('border-t-2', 'border-primary-500')
  }
}

const handleDragLeave = (event) => {
  // Remove visual feedback when leaving the drop target
  const row = event.target.closest('tr')
  if (row) {
    row.classList.remove('border-t-2', 'border-primary-500')
  }
}

const handleDrop = async (event, targetItem) => {
  event.preventDefault()

  // Remove visual feedback
  const allRows = document.querySelectorAll('tr')
  allRows.forEach(row => {
    row.classList.remove('border-t-2', 'border-primary-500', 'opacity-50')
  })

  if (!draggedItem.value || draggedItem.value.id === targetItem.id) {
    draggedItem.value = null
    dragOverItem.value = null
    return
  }

  // Create a new order array based on the drop
  const items = [...props.contentData]
  const draggedIndex = items.findIndex(item => item.id === draggedItem.value.id)
  const targetIndex = items.findIndex(item => item.id === targetItem.id)

  if (draggedIndex === -1 || targetIndex === -1) {
    return
  }

  // Remove dragged item from its original position
  const [removed] = items.splice(draggedIndex, 1)

  // Insert it at the target position
  items.splice(targetIndex, 0, removed)

  // Emit the reorder event with the new order
  emit('reorder-content', items)

  draggedItem.value = null
  dragOverItem.value = null
}

// Dropdown and Modal Methods
const toggleExportImportDropdown = () => {
  showExportImportDropdown.value = !showExportImportDropdown.value
}

const openImportModal = () => {
  showExportImportDropdown.value = false
  showImportModal.value = true
  selectedFileName.value = ''
}

const closeImportModal = () => {
  showImportModal.value = false
  isDraggingFile.value = false
  selectedFileName.value = ''
}

const handleFileDrop = (event) => {
  isDraggingFile.value = false
  const files = event.dataTransfer.files
  if (files.length > 0) {
    const file = files[0]
    if (file.name.endsWith('.xlsx') || file.name.endsWith('.xls')) {
      selectedFileName.value = file.name
      // Create a fake event to trigger file import
      const fakeEvent = {
        target: {
          files: [file],
          value: ''
        }
      }
      handleFileImport(fakeEvent)
    } else {
      alert('請上傳 .xlsx 或 .xls 格式的檔案')
    }
  }
}

// Click outside to close dropdown
const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    showExportImportDropdown.value = false
  }
}

// Excel Import/Export Methods
const exportToExcel = async () => {
  showExportImportDropdown.value = false
  const { $notify } = useNuxtApp()

  try {
    // Call backend API to export Excel with RichText support
    // Use native fetch for blob responses as $fetch has issues with blob type
    const response = await fetch(`/api/v1/risk-assessment/templates/${props.parentId}/export-excel`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      }
    })

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    // Get blob from response
    const blob = await response.blob()

    // Create download link
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `範本內容_${props.parentId}_${Date.now()}.xlsx`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    window.URL.revokeObjectURL(url)

    // Show success message with shared SweetAlert composable
    $notify.success('Excel 檔案已成功下載')
  } catch (error) {
    console.error('Export failed:', error)
    $notify.error(`匯出失敗：${error.message}`)
  }
}

// Legacy frontend export method (kept for reference, can be removed later)
const exportToExcelLegacy = async () => {
  showExportImportDropdown.value = false
  try {
    const XLSX = await import('xlsx')

    // Prepare data for export
    const exportData = props.contentData.map(item => ({
      '風險類別': getCategoryName(item.category_id) || '',
      '風險主題': getTopicName(item.topic_id) || '',
      '風險因子': getRiskFactorName(item.risk_factor_id) || '',
      '排序': item.sort_order || '',
      '是否必填': item.is_required ? '是' : '否',
      // Note: a_content removed, now using factor description directly
      '參考文字': htmlToExcelRichText(item.b_content || '') || '',
      '風險事件描述範例': item.c_placeholder || '',
      '因應措施描述範例': item.d_placeholder_1 || '',
      '因應措施費用範例': item.d_placeholder_2 || '',
      'E1風險描述範例': item.e1_placeholder_1 || '',
      'E2風險可能性預設值': item.e2_select_1 || '',
      'E2風險衝擊程度預設值': item.e2_select_2 || '',
      'E2風險計算說明': item.e2_placeholder || '',
      'F2機會可能性預設值': item.f2_select_1 || '',
      'F2機會衝擊程度預設值': item.f2_select_2 || '',
      'F2機會計算說明': item.f2_placeholder || '',
      'E1資訊提示': item.e1_info || '',
      'F1資訊提示': item.f1_info || '',
      'G1資訊提示': item.g1_info || '',
      'H1資訊提示': item.h1_info || ''
    }))

    // Create workbook
    const wb = XLSX.utils.book_new()

    // Create data worksheet
    const ws = XLSX.utils.json_to_sheet(exportData)

    // Set column widths
    ws['!cols'] = [
      { wch: 15 }, // 風險類別
      { wch: 15 }, // 風險主題
      { wch: 15 }, // 風險因子
      { wch: 10 }, // 排序
      { wch: 10 }, // 是否必填
      { wch: 50 }, // 參考文字
      { wch: 30 }, // 風險事件描述範例
      { wch: 30 }, // 因應措施描述範例
      { wch: 20 }, // 因應措施費用範例
      { wch: 30 }, // E1風險描述範例
      { wch: 20 }, // E2風險可能性預設值
      { wch: 20 }, // E2風險衝擊程度預設值
      { wch: 30 }, // E2風險計算說明
      { wch: 20 }, // F2機會可能性預設值
      { wch: 20 }, // F2機會衝擊程度預設值
      { wch: 30 }, // F2機會計算說明
      { wch: 20 }, // E1資訊提示
      { wch: 20 }, // F1資訊提示
      { wch: 20 }, // G1資訊提示
      { wch: 20 }  // H1資訊提示
    ]

    // Apply header styling
    const range = XLSX.utils.decode_range(ws['!ref'])
    for (let C = range.s.c; C <= range.e.c; ++C) {
      const address = XLSX.utils.encode_col(C) + "1"
      if (!ws[address]) continue
      ws[address].s = {
        fill: { fgColor: { rgb: "4472C4" } },
        font: { bold: true, color: { rgb: "FFFFFF" }, sz: 12 },
        alignment: { horizontal: "center", vertical: "center", wrapText: true }
      }
    }

    // Apply alternating row colors for data rows
    for (let R = range.s.r + 1; R <= range.e.r; ++R) {
      for (let C = range.s.c; C <= range.e.c; ++C) {
        const address = XLSX.utils.encode_cell({ r: R, c: C })
        if (!ws[address]) continue
        ws[address].s = {
          fill: { fgColor: { rgb: R % 2 === 0 ? "FFFFFF" : "F2F2F2" } },
          alignment: { vertical: "top", wrapText: true },
          border: {
            top: { style: "thin", color: { rgb: "D0D0D0" } },
            bottom: { style: "thin", color: { rgb: "D0D0D0" } },
            left: { style: "thin", color: { rgb: "D0D0D0" } },
            right: { style: "thin", color: { rgb: "D0D0D0" } }
          }
        }
      }
    }

    XLSX.utils.book_append_sheet(wb, ws, '範本內容')

    // Create help worksheet
    const helpData = [
      { 欄位: '風險類別', 說明: '必填。風險所屬的分類', 範例: '財務風險' },
      { 欄位: '風險主題', 說明: '選填。更細緻的風險分類', 範例: '市場風險' },
      { 欄位: '風險因子', 說明: '必填。具體的風險因子（系統將使用該因子的描述）', 範例: '匯率波動' },
      { 欄位: '排序', 說明: '選填。顯示順序', 範例: '1' },
      { 欄位: '是否必填', 說明: '選填。填寫「是」或「否」', 範例: '是' },
      { 欄位: '參考文字', 說明: '選填。參考資訊。支援格式：【文字】=粗體、_文字_=斜體', 範例: '請參考【最近一年】的財報...' },
      { 欄位: '風險事件描述範例', 說明: '選填。風險事件範例說明', 範例: '請描述可能發生的風險事件' },
      { 欄位: '因應措施描述範例', 說明: '選填。因應措施範例', 範例: '請說明對應的處理措施' },
      { 欄位: '因應措施費用範例', 說明: '選填。費用範例', 範例: '預估所需費用' },
      { 欄位: 'E1風險描述範例', 說明: '選填。E1風險描述', 範例: '描述風險情境' },
      { 欄位: 'E2風險可能性預設值', 說明: '選填。可能性等級', 範例: 'high' },
      { 欄位: 'E2風險衝擊程度預設值', 說明: '選填。衝擊等級', 範例: 'medium' },
      { 欄位: 'E2風險計算說明', 說明: '選填。計算方式說明', 範例: '風險值 = 可能性 × 衝擊' },
      { 欄位: 'F2機會可能性預設值', 說明: '選填。機會可能性', 範例: 'medium' },
      { 欄位: 'F2機會衝擊程度預設值', 說明: '選填。機會效益', 範例: 'high' },
      { 欄位: 'F2機會計算說明', 說明: '選填。計算方式', 範例: '機會值 = 可能性 × 效益' },
      { 欄位: 'E1資訊提示', 說明: '選填。E1資訊提示文字', 範例: '風險評估說明' },
      { 欄位: 'F1資訊提示', 說明: '選填。F1資訊提示文字', 範例: '機會評估說明' },
      { 欄位: 'G1資訊提示', 說明: '選填。G1資訊提示文字', 範例: '負面影響說明' },
      { 欄位: 'H1資訊提示', 說明: '選填。H1資訊提示文字', 範例: '正面影響說明' }
    ]
    const wsHelp = XLSX.utils.json_to_sheet(helpData)
    XLSX.utils.book_append_sheet(wb, wsHelp, '填寫說明')

    // Download file
    XLSX.writeFile(wb, `範本內容_${props.parentId}_${new Date().getTime()}.xlsx`)

    alert('匯出成功')
  } catch (error) {
    console.error('Export failed:', error)
    alert('匯出失敗：' + error.message)
  }
}

const downloadTemplate = async () => {
  try {
    const XLSX = await import('xlsx')

    // Template data with example row (matches export format exactly)
    const templateData = [{
      '風險類別': '財務風險',
      '風險主題': '市場風險',
      '風險因子': '匯率波動',
      'A風險因子描述': '企業營運高度依賴【自然資源】的_風險評估_...',
      'B參考文字': '請參考【最近一年】的_財報資料_...',
      'C風險事件描述': '請描述可能發生的風險事件',
      'D對應作為描述': '請說明對應的處理措施',
      'D對應作為費用': '預估所需費用',
      'E風險描述': '描述風險情境',
      'E風險計算說明': '風險值 = 可能性 × 衝擊',
      'F機會描述': '描述機會情境',
      'F機會計算說明': '機會值 = 可能性 × 效益',
      'G對外負面衝擊評分說明': '負面影響描述',
      'H對外正面影響評分說明': '正面影響描述',
      'E1資訊提示': '風險評估說明',
      'F1資訊提示': '機會評估說明',
      'G1資訊提示': '負面影響說明',
      'H1資訊提示': '正面影響說明',
      '備註': '範例資料'
    }]

    const wb = XLSX.utils.book_new()
    const ws = XLSX.utils.json_to_sheet(templateData)

    // Set column widths (matches export format)
    ws['!cols'] = [
      { wch: 15 }, // 風險類別
      { wch: 15 }, // 風險主題
      { wch: 15 }, // 風險因子
      { wch: 50 }, // A風險因子描述
      { wch: 50 }, // B參考文字
      { wch: 40 }, // C風險事件描述
      { wch: 40 }, // D對應作為描述
      { wch: 20 }, // D對應作為費用
      { wch: 40 }, // E風險描述
      { wch: 30 }, // E風險計算說明
      { wch: 40 }, // F機會描述
      { wch: 30 }, // F機會計算說明
      { wch: 40 }, // G對外負面衝擊評分說明
      { wch: 40 }, // H對外正面影響評分說明
      { wch: 30 }, // E1資訊提示
      { wch: 30 }, // F1資訊提示
      { wch: 30 }, // G1資訊提示
      { wch: 30 }, // H1資訊提示
      { wch: 15 }  // 備註
    ]

    XLSX.utils.book_append_sheet(wb, ws, '資料填寫區')

    // Add help sheet (matches export format)
    const helpSheet = XLSX.utils.aoa_to_sheet([
      ['欄位名稱', '說明', '範例'],
      ['風險類別', '必填。風險所屬的分類', '財務風險'],
      ['風險主題', '選填。更細緻的風險分類（依據範本設定）', '市場風險'],
      ['風險因子', '必填。具體的風險因子', '匯率波動'],
      ['A風險因子描述', '選填。風險因子議題描述（支援富文本格式）', '企業營運高度依賴【自然資源】的_風險評估_...'],
      ['B參考文字', '選填。參考資訊（支援富文本格式）', '請參考【最近一年】的_財報資料_...'],
      ['C風險事件描述', '選填。風險事件的詳細描述', '請描述可能發生的風險事件'],
      ['D對應作為描述', '選填。對應措施的詳細說明', '請說明對應的處理措施'],
      ['D對應作為費用', '選填。對應措施所需費用', '預估所需費用'],
      ['E風險描述', '選填。風險情境描述', '描述風險情境'],
      ['E風險計算說明', '選填。風險計算方式說明', '風險值 = 可能性 × 衝擊'],
      ['F機會描述', '選填。機會情境描述', '描述機會情境'],
      ['F機會計算說明', '選填。機會計算方式說明', '機會值 = 可能性 × 效益'],
      ['G對外負面衝擊評分說明', '選填。對外負面衝擊的評分說明', '負面影響描述'],
      ['H對外正面影響評分說明', '選填。對外正面影響的評分說明', '正面影響描述'],
      ['E1資訊提示', '選填。E1區塊資訊提示文字', '風險評估說明'],
      ['F1資訊提示', '選填。F1區塊資訊提示文字', '機會評估說明'],
      ['G1資訊提示', '選填。G1區塊資訊提示文字', '負面影響說明'],
      ['H1資訊提示', '選填。H1區塊資訊提示文字', '正面影響說明'],
      ['備註', '系統欄位。第一行範例資料標記為「範例資料」，匯入時自動跳過', '範例資料'],
      [],
      ['注意事項'],
      ['1. 備註欄位若為「範例資料」，該行會自動跳過不匯入'],
      ['2. 風險類別和風險因子為必填欄位'],
      ['3. A、B欄位支援富文本格式：【文字】=粗體、_文字_=斜體'],
      ['4. 如果類別、主題、因子不存在，系統會自動建立'],
      ['5. 匯出時HTML格式會轉換為Excel富文本格式，匯入時自動還原為HTML'],
      ['6. 是否選項、可能性、衝擊程度等欄位不開放匯入，請於系統中設定']
    ])
    XLSX.utils.book_append_sheet(wb, helpSheet, '填寫說明')

    XLSX.writeFile(wb, '範本內容匯入範本.xlsx')

    // Show success message with shared SweetAlert composable
    const { $notify } = useNuxtApp()
    $notify.success('範本下載成功，請使用下載的範本填寫資料後匯入')
  } catch (error) {
    console.error('Download template failed:', error)

    const { $notify } = useNuxtApp()
    $notify.error(`下載範本失敗：${error.message}`)
  }
}

const triggerImport = () => {
  fileInput.value?.click()
}

const handleFileImport = async (event) => {
  const file = event.target.files[0]
  if (!file) return

  const { $notify } = useNuxtApp()

  // Show importing notification
  $notify.info('正在匯入 Excel 檔案，請稍候...')

  try {
    // Call backend API to import Excel with RichText support
    const formData = new FormData()
    formData.append('file', file)

    const result = await $fetch(`/api/v1/risk-assessment/templates/${props.parentId}/import-excel`, {
      method: 'POST',
      body: formData
    })

    // Show result message
    if (result.success) {
      // Store debug information
      if (result.debug) {
        importDebugData.value = result.debug
      }

      const messageLines = [
        `成功匯入 ${result.imported} 筆`,
        result.skipped > 0 ? `跳過重複 ${result.skipped} 筆` : null,
        result.errors.length > 0 ? `錯誤 ${result.errors.length} 筆` : null
      ].filter(Boolean).join('、')

      if (result.errors.length > 0) {
        console.error('Import errors:', result.errors)
        // Build detailed error message
        const errorDetails = result.errors.slice(0, 5).join('<br>') // Show first 5 errors
        const moreErrors = result.errors.length > 5 ? `<br>...及其他 ${result.errors.length - 5} 個錯誤` : ''
        // Show warning with detailed errors and link to debug modal
        $notify.warning(`${messageLines}<br><br>錯誤詳情：<br>${errorDetails}${moreErrors}<br><br>點擊查看完整除錯資訊`)
      } else {
        // Show success message
        $notify.success(messageLines)
      }

      // Debug modal re-enabled per Point 44
      if (result.debug) {
        showDebugModal.value = true
      }

      // Refresh data
      emit('refresh-content')

      // Refresh import history after successful import (Point 48)
      await fetchLatestBatchSummary()
      await fetchImportHistory()

      showImportModal.value = false
      selectedFileName.value = ''
    } else {
      $notify.error(`匯入失敗：${result.message}`)
    }
  } catch (error) {
    console.error('Import failed:', error)
    $notify.error(`匯入失敗：${error.message}`)
  }

  // Reset file input
  event.target.value = ''
}

// DataTable columns for import history (Point 46)
const importHistoryColumns = computed(() => [
  {
    key: 'actions',
    label: '操作',
    sortable: false
  },
  {
    key: 'created_at',
    label: '時間',
    sortable: true
  },
  {
    key: 'summary',
    label: '匯入摘要',
    sortable: false
  }
])

// DataTable columns for batch details (Point 48)
const batchDetailsColumns = computed(() => [
  {
    key: 'status',
    label: '狀態',
    sortable: true
  },
  {
    key: 'row_number',
    label: '行號',
    sortable: true
  },
  {
    key: 'category_name',
    label: '風險類別',
    sortable: true
  },
  {
    key: 'topic_name',
    label: '風險主題',
    sortable: true
  },
  {
    key: 'factor_name',
    label: '風險因子',
    sortable: true
  },
  {
    key: 'reason',
    label: '備註',
    sortable: false
  }
])

// Format batch data for display (Point 46)
const formattedBatchData = computed(() => {
  return importHistoryBatches.value.map(batch => ({
    ...batch,
    id: batch.batch_id,
    created_at_formatted: new Date(batch.created_at).toLocaleString('zh-TW', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit'
    }),
    summary_text: `成功 ${batch.success_count} / 跳過 ${batch.skipped_count} / 失敗 ${batch.error_count}`
  }))
})

// Format batch details records for display (Point 48)
const formattedBatchRecords = computed(() => {
  if (!selectedBatchDetails.value?.records) return []
  return selectedBatchDetails.value.records
})

// Refresh import history (reload data from API)
const refreshImportHistory = async () => {
  await fetchImportHistory()
  await fetchLatestBatchSummary()
}

// Fetch import history from API (Point 46)
const fetchImportHistory = async () => {
  if (!props.parentId) return

  isLoadingHistory.value = true
  try {
    const response = await $fetch(`/api/v1/risk-assessment/templates/${props.parentId}/import-history`, {
      params: {
        page: 1,
        limit: 1000 // Fetch all records for client-side pagination in DataTable
      }
    })

    if (response.success) {
      importHistoryBatches.value = response.data
    }
  } catch (error) {
    console.error('Failed to fetch import history:', error)
  } finally {
    isLoadingHistory.value = false
  }
}

// Fetch latest batch summary (Point 46)
const fetchLatestBatchSummary = async () => {
  if (!props.parentId) return

  try {
    const response = await $fetch(`/api/v1/risk-assessment/templates/${props.parentId}/import-history/latest`)

    if (response.success && response.has_data) {
      latestBatchSummary.value = response.summary
    } else {
      latestBatchSummary.value = null
    }
  } catch (error) {
    console.error('Failed to fetch latest batch summary:', error)
    latestBatchSummary.value = null
  }
}

// Fetch batch details (Point 46)
const fetchBatchDetails = async (batchId) => {
  isLoadingHistory.value = true
  try {
    const response = await $fetch(`/api/v1/risk-assessment/import-history/batch/${batchId}`)

    if (response.success) {
      selectedBatchDetails.value = response
      showBatchDetails.value = true
    }
  } catch (error) {
    console.error('Failed to fetch batch details:', error)
  } finally {
    isLoadingHistory.value = false
  }
}

// Open import history modal and fetch data (Point 46)
const openImportHistoryModal = async () => {
  showDebugModal.value = true
  await fetchLatestBatchSummary()
  await fetchImportHistory()
}

// Close batch details view
const closeBatchDetails = () => {
  showBatchDetails.value = false
  selectedBatchDetails.value = null
}

// Legacy frontend import method (kept for reference, can be removed later)
const handleFileImportLegacy = async (event) => {
  const file = event.target.files[0]
  if (!file) return

  try {
    const XLSX = await import('xlsx')
    const reader = new FileReader()

    reader.onload = async (e) => {
      try {
        // Read Excel
        const data = new Uint8Array(e.target.result)
        const workbook = XLSX.read(data, { type: 'array' })

        // Get first sheet
        const firstSheet = workbook.Sheets[workbook.SheetNames[0]]
        const jsonData = XLSX.utils.sheet_to_json(firstSheet)

        // Skip first row (example data)
        const actualData = jsonData.slice(1)

        if (actualData.length === 0) {
          alert('Excel 檔案中沒有資料（第一行為範例會自動跳過）')
          return
        }

        // Validate and transform data
        const importItems = actualData.map(row => ({
          categoryName: row['風險類別'],
          topicName: row['風險主題'] || null,
          factorName: row['風險因子'] || null,
          sort_order: row['排序'] || null,
          is_required: row['是否必填'] === '是' ? 1 : 0,
          // Note: a_content removed, now using factor description
          // Convert formatted text back to HTML
          b_content: textToHtml(row['參考文字'] || '') || null,
          c_placeholder: row['風險事件描述範例'] || null,
          d_placeholder_1: row['因應措施描述範例'] || null,
          d_placeholder_2: row['因應措施費用範例'] || null,
          e1_placeholder_1: row['E1風險描述範例'] || null,
          e2_select_1: row['E2風險可能性預設值'] || null,
          e2_select_2: row['E2風險衝擊程度預設值'] || null,
          e2_placeholder: row['E2風險計算說明'] || null,
          f2_select_1: row['F2機會可能性預設值'] || null,
          f2_select_2: row['F2機會衝擊程度預設值'] || null,
          f2_placeholder: row['F2機會計算說明'] || null,
          e1_info: row['E1資訊提示'] || null,
          f1_info: row['F1資訊提示'] || null,
          g1_info: row['G1資訊提示'] || null,
          h1_info: row['H1資訊提示'] || null
        }))

        // Validate required fields
        const invalidRows = []
        importItems.forEach((item, index) => {
          if (!item.categoryName || !item.factorName) {
            invalidRows.push(index + 2) // +2 because: +1 for 0-index, +1 for skipped example row
          }
        })

        if (invalidRows.length > 0) {
          alert(`以下行缺少必填欄位（風險類別、風險因子）：第 ${invalidRows.join(', ')} 行`)
          return
        }

        // Call backend API
        const templatesStore = useTemplatesStore()
        await templatesStore.batchImportTemplateContent(props.parentId, importItems)

        // Close modal
        closeImportModal()

        // Refresh content
        emit('refresh-content')

        alert(`成功匯入 ${importItems.length} 筆資料`)
      } catch (error) {
        console.error('Import processing failed:', error)
        alert('匯入處理失敗：' + error.message)
      }
    }

    reader.readAsArrayBuffer(file)
  } catch (error) {
    console.error('Import failed:', error)
    alert('匯入失敗：' + error.message)
  }

  // Clear input to allow re-selecting the same file
  event.target.value = ''
}

// Setup click outside listener for dropdown
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  // Fetch latest batch summary on mount (Point 46)
  fetchLatestBatchSummary()
})

// Debug: Watch contentData prop changes
watch(() => props.contentData, (newValue) => {
  console.log(`[ContentManagement] Received contentData: ${newValue?.length || 0} items`)
}, { immediate: true })

// Cleanup on unmount
onUnmounted(() => {
  if (tooltipTimeout) {
    clearTimeout(tooltipTimeout)
  }
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.sortable-row {
  transition: all 0.3s ease;
}

.sortable-row:hover {
  cursor: pointer;
}

.sortable-row.dragging {
  opacity: 0.5;
  transform: rotate(5deg);
}

.drag-handle {
  cursor: grab;
}

.drag-handle:active {
  cursor: grabbing;
}

.sortable-row[draggable="true"]:hover .drag-handle {
  background-color: rgb(249 250 251);
}

.dark .sortable-row[draggable="true"]:hover .drag-handle {
  background-color: rgb(55 65 81);
}
</style>