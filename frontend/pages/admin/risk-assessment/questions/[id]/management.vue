<template>
  <div class="p-6">
    <!-- Page Header -->
    <PageHeader
      :title="`題項管理 - ${companyName}`"
      :description="`管理 ${companyName} 的風險評估題項與相關資料`"
      :show-back-button="true"
    />

    <!-- Company Warning Message -->
    <div v-if="showCompanyWarning" class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
            公司不存在
          </h3>
          <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
            <p>找不到 ID 為 "{{ companyId }}" 的公司。這可能是因為：</p>
            <ul class="mt-1 ml-4 list-disc">
              <li>公司資料已被刪除</li>
              <li>URL 中的公司 ID 不正確</li>
              <li>系統資料需要更新</li>
            </ul>
            <p class="mt-2">
              請檢查 URL 或聯繫系統管理員。
              <nuxt-link to="/admin/risk-assessment/questions" class="font-medium underline hover:no-underline">
                返回公司列表
              </nuxt-link>
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Data Table -->
    <DataTable
      :data="questionManagement"
      :columns="columns"
      :loading="isLoadingQuestionManagement"
      search-placeholder="搜尋年份或範本版本..."
      :search-fields="['year', 'templateVersion']"
      empty-title="還沒有題項管理資料"
      empty-message="開始建立您的第一個題項管理項目"
      no-search-results-title="沒有找到符合的項目"
      no-search-results-message="請嘗試其他搜尋關鍵字"
      @search="(query) => console.log('搜尋查詢:', query)"
      @sort="(field, order) => console.log('排序:', field, order)"
    >
      <!-- Actions Slot -->
      <template #actions>
        <div class="flex gap-2">
          <button
            @click="showAddModal = true"
            class="flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200"
          >
            <PlusIcon class="w-4 h-4 mr-2" />
            新增題項管理
          </button>
          <button
            @click="loadQuestionManagementData"
            :disabled="isLoadingQuestionManagement"
            class="flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
          >
            <ArrowPathIcon
              class="w-4 h-4 mr-2"
              :class="{ 'animate-spin': isLoadingQuestionManagement }"
            />
            重新整理
          </button>
        </div>
      </template>

      <!-- Custom Actions Cell -->
      <template #cell-actions="{ item }">
        <div class="flex flex-wrap items-center gap-2">
          <!-- Personnel Assignment -->
          <div class="relative group">
            <button
              @click="showPersonnelAssignment(item)"
              class="p-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors duration-200"
            >
              <UsersIcon class="w-4 h-4" />
            </button>
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
              人員指派
              <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
            </div>
          </div>

          <!-- View Assignments -->
          <div class="relative group">
            <button
              @click="viewAssignments(item)"
              class="p-2 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors duration-200"
            >
              <DocumentTextIcon class="w-4 h-4" />
            </button>
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
              檢視已填寫的評估表
              <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
            </div>
          </div>

          <!-- View Statistics -->
          <div class="relative group">
            <button
              @click="showStatistics(item)"
              class="p-2 text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-lg transition-colors duration-200"
            >
              <ChartBarIcon class="w-4 h-4" />
            </button>
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
              檢視評估表統計結果
              <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
            </div>
          </div>

          <!-- Copy Overall Form -->
          <div class="relative group">
            <button
              @click="showCopyOverallForm(item)"
              class="p-2 text-orange-600 hover:text-orange-800 dark:text-orange-400 dark:hover:text-orange-300 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-lg transition-colors duration-200"
            >
              <DocumentDuplicateIcon class="w-4 h-4" />
            </button>
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
              複製總表
              <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
            </div>
          </div>

          <!-- Structure Management Button -->
          <div class="relative group">
            <button
              @click="manageQuestionStructure(item)"
              class="p-2 text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-lg transition-colors duration-200"
            >
              <Cog6ToothIcon class="w-4 h-4" />
            </button>
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
              架構管理
              <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
            </div>
          </div>

          <!-- Manage Question Content -->
          <div class="relative group">
            <button
              @click="manageQuestionContent(item)"
              class="p-2 text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors duration-200"
            >
              <CogIcon class="w-4 h-4" />
            </button>
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
              管理題項內容
              <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
            </div>
          </div>

          <!-- Edit -->
          <div class="relative group">
            <button
              @click="editItem(item)"
              class="p-2 text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 rounded-lg transition-colors duration-200"
            >
              <PencilIcon class="w-4 h-4" />
            </button>
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
              編輯
              <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
            </div>
          </div>

          <!-- Copy -->
          <div class="relative group">
            <button
              @click="showCopyModal(item)"
              class="p-2 text-cyan-600 hover:text-cyan-800 dark:text-cyan-400 dark:hover:text-cyan-300 hover:bg-cyan-50 dark:hover:bg-cyan-900/20 rounded-lg transition-colors duration-200"
            >
              <DocumentDuplicateIcon class="w-4 h-4" />
            </button>
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
              複製
              <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
            </div>
          </div>

          <!-- Delete -->
          <div class="relative group">
            <button
              @click="deleteItem(item)"
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

      <!-- Empty Action Slot -->
      <template #emptyAction>
        <button
          @click="showAddModal = true"
          class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200"
        >
          <PlusIcon class="w-4 h-4 mr-2" />
          新增題項管理
        </button>
      </template>
    </DataTable>

    <!-- Add/Edit Modal -->
    <Modal
      :model-value="showFormModal"
      :title="showAddModal ? '新增題項管理' : '編輯題項管理'"
      size="md"
      @update:model-value="(value) => { if (!value) closeModals() }"
      @close="closeModals"
    >
      <form @submit.prevent="submitForm">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            範本版本
          </label>
          <select
            ref="templateSelectRef"
            v-model="formData.templateId"
            required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
          >
            <option value="">請選擇範本版本</option>
            <option v-for="template in availableTemplates" :key="template.id" :value="template.id">
              {{ template.version_name }}
            </option>
          </select>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            年份
          </label>
          <input
            v-model="formData.year"
            type="number"
            min="2020"
            max="2030"
            required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
            placeholder="請輸入年份"
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
            class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200"
          >
            {{ showAddModal ? '新增' : '更新' }}
          </button>
        </div>
      </template>
    </Modal>

    <!-- Copy Modal -->
    <div
      v-if="showCopyOptions"
      class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
      @click="showCopyOptions = false"
    >
      <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md"
        @click.stop
      >
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
            複製選項
          </h3>
          
          <form @submit.prevent="confirmCopy">
            <div class="mb-4">
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                選擇要複製的內容：
              </p>
              <div class="space-y-2">
                <label class="flex items-center">
                  <input
                    v-model="copyOptions.includeQuestions"
                    type="checkbox"
                    class="form-checkbox h-4 w-4 text-primary-600"
                  />
                  <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">複製題目</span>
                </label>
                <label class="flex items-center">
                  <input
                    v-model="copyOptions.includeResults"
                    type="checkbox"
                    class="form-checkbox h-4 w-4 text-primary-600"
                  />
                  <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">複製結果</span>
                </label>
              </div>
            </div>

            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="showCopyOptions = false"
                class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors duration-200"
              >
                取消
              </button>
              <button
                type="submit"
                class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200"
              >
                確認複製
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Enhanced Personnel Assignment Modal -->
    <PersonnelAssignmentModal
      :model-value="showPersonnelModal"
      :company-id="companyId"
      :question-id="selectedQuestionForAssignment?.id"
      :question-content="questionContentForAssignment"
      :available-users="availableUsers"
      @update:model-value="(value) => showPersonnelModal = value"
      @close="showPersonnelModal = false"
      @assignment-updated="handleAssignmentUpdated"
    />

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal
      :model-value="showDeleteModal"
      title="確認刪除"
      message="確定要刪除這個題項管理項目嗎？"
      details="此操作將同時刪除相關的所有題項資料，且無法復原。"
      type="danger"
      cancel-text="取消"
      confirm-text="刪除"
      @update:model-value="(value) => showDeleteModal = value"
      @close="showDeleteModal = false"
      @confirm="confirmDelete"
    />


    <!-- Question Structure Management Modal -->
    <StructureManagementModal
      v-if="managingQuestion"
      :model-value="showQuestionStructureModal"
      :title="`架構管理 - ${managingQuestion?.templateVersion || ''} (${managingQuestion?.year || ''}年)`"
      :item-name="`${managingQuestion?.templateVersion || ''} (${managingQuestion?.year || ''}年)`"
      :risk-topics-enabled="enableTopicLayer"
      :show-export-import="true"
      management-type="question"
      @update:model-value="(value) => showQuestionStructureModal = value"
      @close="showQuestionStructureModal = false"
      @toggle-risk-topics="onTopicLayerToggleChange"
      @open-category-management="openQuestionManagementModal('categories')"
      @open-topic-management="openQuestionManagementModal('topics')"
      @open-factor-management="openQuestionManagementModal('factors')"
      @sync-from-template="syncFromTemplate"
      @go-to-content="goToQuestionContent(managingQuestion?.id)"
      @export-structure="exportStructure"
      @import-structure="showStructureImportModal = true"
    />

    <!-- Structure Import Modal -->
    <Modal
      :model-value="showStructureImportModal"
      title="匯入架構資料"
      size="lg"
      @update:model-value="(value) => showStructureImportModal = value"
      @close="closeStructureImportModal"
    >
      <div class="space-y-4">
        <!-- Download Template Button -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
          <div class="flex items-center justify-between">
            <div>
              <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-1 break-words">下載匯入範本</h4>
              <p class="text-xs text-blue-700 dark:text-blue-300 break-words">下載空白範本以填寫資料</p>
            </div>
            <button
              type="button"
              @click="downloadTemplate"
              class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex-shrink-0"
            >
              <ArrowDownTrayIcon class="w-4 h-4 mr-2" />
              下載範本
            </button>
          </div>
        </div>

        <!-- File Upload Area -->
        <div
          @drop.prevent="handleDrop"
          @dragover.prevent="isDragging = true"
          @dragleave.prevent="isDragging = false"
          :class="[
            'border-2 border-dashed rounded-lg p-8 text-center transition-colors',
            isDragging
              ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
              : 'border-gray-300 dark:border-gray-600'
          ]"
        >
          <ArrowUpTrayIcon class="w-12 h-12 mx-auto text-gray-400 mb-4" />
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-2 break-words">
            拖曳 Excel 檔案到此處，或
          </p>
          <input
            ref="fileInput"
            type="file"
            accept=".xlsx,.xls"
            @change="handleFileSelect"
            class="hidden"
          />
          <button
            type="button"
            @click="$refs.fileInput.click()"
            class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors"
          >
            選擇檔案
          </button>
        </div>

        <!-- Selected File Info -->
        <div v-if="selectedFile" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <DocumentTextIcon class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2 flex-shrink-0" />
              <span class="text-sm font-medium text-blue-900 dark:text-blue-100 break-words">{{ selectedFile.name }}</span>
            </div>
            <button
              @click="selectedFile = null"
              class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
            >
              <XMarkIcon class="w-5 h-5" />
            </button>
          </div>
        </div>

        <!-- Validation Errors/Warnings -->
        <div v-if="importErrors.length > 0" :class="[
          'rounded-lg p-4 border',
          importErrors[0]?.startsWith('發現') && importErrors[0]?.includes('重複')
            ? 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800'
            : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800'
        ]">
          <div class="flex items-start">
            <ExclamationTriangleIcon :class="[
              'w-5 h-5 mr-2 flex-shrink-0 mt-0.5',
              importErrors[0]?.startsWith('發現') && importErrors[0]?.includes('重複')
                ? 'text-yellow-600 dark:text-yellow-400'
                : 'text-red-600 dark:text-red-400'
            ]" />
            <div class="flex-1">
              <h4 :class="[
                'text-sm font-medium mb-2',
                importErrors[0]?.startsWith('發現') && importErrors[0]?.includes('重複')
                  ? 'text-yellow-900 dark:text-yellow-100'
                  : 'text-red-900 dark:text-red-100'
              ]">
                {{ importErrors[0]?.startsWith('發現') && importErrors[0]?.includes('重複') ? '警告' : `發現 ${importErrors.length} 個錯誤` }}
              </h4>
              <ul :class="[
                'text-sm space-y-1 list-disc list-inside',
                importErrors[0]?.startsWith('發現') && importErrors[0]?.includes('重複')
                  ? 'text-yellow-800 dark:text-yellow-200'
                  : 'text-red-800 dark:text-red-200'
              ]">
                <li v-for="(error, index) in importErrors" :key="index" class="break-words">
                  {{ error }}
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Import Button -->
        <div class="flex justify-end space-x-3 pt-4">
          <button
            type="button"
            @click="closeStructureImportModal"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors"
          >
            取消
          </button>
          <button
            type="button"
            @click="processImport"
            :disabled="!selectedFile || isImporting"
            class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <span v-if="isImporting">處理中...</span>
            <span v-else>匯入</span>
          </button>
        </div>
      </div>
    </Modal>

    <!-- Categories Management Modal -->
    <Modal
      :model-value="showCategoriesModal"
      :title="`管理風險類別 - ${managingQuestion?.templateVersion} (${managingQuestion?.year}年)`"
      size="4xl"
      @update:model-value="(value) => showCategoriesModal = value"
      @close="showCategoriesModal = false"
    >
      <DataTable
        :data="questionCategories || []"
        :columns="questionCategoryColumns"
        search-placeholder="搜尋風險類別..."
        :search-fields="['category_name', 'description']"
        empty-title="還沒有風險類別"
        empty-message="開始建立您的第一個風險類別"
        no-search-results-title="沒有找到符合的風險類別"
        no-search-results-message="請嘗試其他搜尋關鍵字"
        :loading="structureLoading"
      >
        <!-- Actions Slot -->
        <template #actions>
          <div class="flex items-center space-x-3">
            <!-- Sync from Template Button -->
            <button
              @click="syncFromTemplate"
              :disabled="structureLoading"
              class="flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
            >
              <ArrowDownIcon
                class="w-4 h-4 mr-2"
                :class="{ 'animate-spin': structureLoading }"
              />
              從範本同步
            </button>

            <!-- Add Button -->
            <button
              @click="addQuestionCategory"
              class="flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200"
            >
              <PlusIcon class="w-4 h-4 mr-2" />
              新增類別
            </button>

            <!-- Refresh Button -->
            <button
              @click="refreshStructureData"
              :disabled="structureLoading"
              class="flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
              title="重新整理"
            >
              <ArrowPathIcon
                class="w-4 h-4 mr-2"
                :class="{ 'animate-spin': structureLoading }"
              />
              重新整理
            </button>
          </div>
        </template>

        <!-- Custom Actions Cell -->
        <template #cell-actions="{ item }">
          <div class="flex items-center space-x-2">
            <button
              @click="editQuestionCategory(item)"
              class="p-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors duration-200"
              title="編輯"
            >
              <PencilIcon class="w-4 h-4" />
            </button>

            <button
              @click="deleteQuestionCategory(item)"
              class="p-2 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors duration-200"
              title="刪除"
            >
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>
        </template>

        <!-- Custom Description Cell with HTML rendering and truncation -->
        <template #cell-description="{ item }">
          <div class="relative group">
            <div
              v-if="item.description"
              class="text-gray-600 dark:text-gray-400"
              v-html="truncateHtml(item.description, 20)"
            ></div>
            <div v-else class="text-gray-400 dark:text-gray-500 italic">無描述</div>

            <!-- Tooltip for full content on hover -->
            <div
              v-if="item.description && stripHtml(item.description).length > 20"
              class="absolute left-0 top-full mt-2 p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 min-w-[300px] max-w-[500px]"
            >
              <div
                class="text-sm text-gray-700 dark:text-gray-300 break-words"
                v-html="item.description"
              ></div>
            </div>
          </div>
        </template>
      </DataTable>
    </Modal>

    <!-- Topics Management Modal -->
    <Modal
      :model-value="showTopicsModal"
      :title="`管理風險主題 - ${managingQuestion?.templateVersion} (${managingQuestion?.year}年)`"
      size="4xl"
      @update:model-value="(value) => showTopicsModal = value"
      @close="showTopicsModal = false"
    >
      <DataTable
        :data="questionTopics || []"
        :columns="questionTopicColumns"
        search-placeholder="搜尋風險主題..."
        :search-fields="['topic_name', 'category_name', 'description']"
        empty-title="還沒有風險主題"
        empty-message="請啟用風險主題層級並開始建立您的第一個風險主題"
        no-search-results-title="沒有找到符合的風險主題"
        no-search-results-message="請嘗試其他搜尋關鍵字"
        :loading="structureLoading"
      >
        <!-- Actions Slot -->
        <template #actions>
          <div class="flex items-center space-x-3">
            <!-- Sync from Template Button -->
            <button
              @click="syncFromTemplate"
              :disabled="structureLoading"
              class="flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
            >
              <ArrowDownIcon
                class="w-4 h-4 mr-2"
                :class="{ 'animate-spin': structureLoading }"
              />
              從範本同步
            </button>

            <!-- Add Button -->
            <button
              @click="addQuestionTopic"
              :disabled="!enableTopicLayer"
              class="flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
            >
              <PlusIcon class="w-4 h-4 mr-2" />
              新增主題
            </button>

            <!-- Refresh Button -->
            <button
              @click="refreshStructureData"
              :disabled="structureLoading"
              class="flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
              title="重新整理"
            >
              <ArrowPathIcon
                class="w-4 h-4 mr-2"
                :class="{ 'animate-spin': structureLoading }"
              />
              重新整理
            </button>
          </div>
        </template>

        <!-- Custom Actions Cell -->
        <template #cell-actions="{ item }">
          <div class="flex items-center space-x-2">
            <button
              @click="editQuestionTopic(item)"
              class="p-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors duration-200"
              title="編輯"
            >
              <PencilIcon class="w-4 h-4" />
            </button>

            <button
              @click="deleteQuestionTopic(item)"
              class="p-2 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors duration-200"
              title="刪除"
            >
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>
        </template>

        <!-- Custom Description Cell with HTML rendering and truncation -->
        <template #cell-description="{ item }">
          <div class="relative group">
            <div
              v-if="item.description"
              class="text-gray-600 dark:text-gray-400"
              v-html="truncateHtml(item.description, 20)"
            ></div>
            <div v-else class="text-gray-400 dark:text-gray-500 italic">無描述</div>

            <!-- Tooltip for full content on hover -->
            <div
              v-if="item.description && stripHtml(item.description).length > 20"
              class="absolute left-0 top-full mt-2 p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 min-w-[300px] max-w-[500px]"
            >
              <div
                class="text-sm text-gray-700 dark:text-gray-300 break-words"
                v-html="item.description"
              ></div>
            </div>
          </div>
        </template>
      </DataTable>
    </Modal>

    <!-- Factors Management Modal -->
    <Modal
      :model-value="showFactorsModal"
      :title="`管理風險因子 - ${managingQuestion?.templateVersion} (${managingQuestion?.year}年)`"
      size="4xl"
      @update:model-value="(value) => showFactorsModal = value"
      @close="showFactorsModal = false"
    >
      <DataTable
        :data="questionFactors || []"
        :columns="questionFactorColumns"
        search-placeholder="搜尋風險因子..."
        :search-fields="['factor_name', 'topic_name', 'category_name', 'description']"
        empty-title="還沒有風險因子"
        empty-message="開始建立您的第一個風險因子"
        no-search-results-title="沒有找到符合的風險因子"
        no-search-results-message="請嘗試其他搜尋關鍵字"
        :loading="structureLoading"
      >
        <!-- Actions Slot -->
        <template #actions>
          <div class="flex items-center space-x-3">
            <!-- Sync from Template Button -->
            <button
              @click="syncFromTemplate"
              :disabled="structureLoading"
              class="flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
            >
              <ArrowDownIcon
                class="w-4 h-4 mr-2"
                :class="{ 'animate-spin': structureLoading }"
              />
              從範本同步
            </button>

            <!-- Add Button -->
            <button
              @click="addQuestionFactor"
              class="flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200"
            >
              <PlusIcon class="w-4 h-4 mr-2" />
              新增因子
            </button>

            <!-- Refresh Button -->
            <button
              @click="refreshStructureData"
              :disabled="structureLoading"
              class="flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
              title="重新整理"
            >
              <ArrowPathIcon
                class="w-4 h-4 mr-2"
                :class="{ 'animate-spin': structureLoading }"
              />
              重新整理
            </button>
          </div>
        </template>

        <!-- Custom Actions Cell -->
        <template #cell-actions="{ item }">
          <div class="flex items-center space-x-2">
            <button
              @click="editQuestionFactor(item)"
              class="p-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors duration-200"
              title="編輯"
            >
              <PencilIcon class="w-4 h-4" />
            </button>

            <button
              @click="deleteQuestionFactor(item)"
              class="p-2 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors duration-200"
              title="刪除"
            >
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>
        </template>

        <!-- Custom Description Cell with HTML rendering and truncation -->
        <template #cell-description="{ item }">
          <div class="relative group">
            <div
              v-if="item.description"
              class="text-gray-600 dark:text-gray-400"
              v-html="truncateHtml(item.description, 20)"
            ></div>
            <div v-else class="text-gray-400 dark:text-gray-500 italic">無描述</div>

            <!-- Tooltip for full content on hover -->
            <div
              v-if="item.description && stripHtml(item.description).length > 20"
              class="absolute left-0 top-full mt-2 p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 min-w-[300px] max-w-[500px]"
            >
              <div
                class="text-sm text-gray-700 dark:text-gray-300 break-words"
                v-html="item.description"
              ></div>
            </div>
          </div>
        </template>
      </DataTable>
    </Modal>

    <!-- Edit Category Modal -->
    <Modal
      :model-value="showEditCategoryModal"
      :title="editingStructureItem?.id ? '編輯風險類別' : '新增風險類別'"
      size="md"
      @update:model-value="(value) => showEditCategoryModal = value"
      @close="showEditCategoryModal = false"
    >
      <form @submit.prevent="saveQuestionCategory" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            類別名稱 <span class="text-red-500">*</span>
          </label>
          <input
            v-model="editingStructureItem.category_name"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
            placeholder="輸入類別名稱"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            描述
          </label>
          <textarea
            v-model="editingStructureItem.description"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
            placeholder="輸入類別描述（可選）"
          ></textarea>
        </div>


        <div class="flex justify-end space-x-3 pt-4">
          <button
            type="button"
            @click="showEditCategoryModal = false"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200"
          >
            取消
          </button>
          <button
            type="submit"
            :disabled="structureLoading"
            class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
          >
            {{ structureLoading ? '儲存中...' : '儲存' }}
          </button>
        </div>
      </form>
    </Modal>

    <!-- Edit Topic Modal -->
    <Modal
      :model-value="showEditTopicModal"
      :title="editingStructureItem?.id ? '編輯風險主題' : '新增風險主題'"
      size="md"
      @update:model-value="(value) => showEditTopicModal = value"
      @close="showEditTopicModal = false"
    >
      <form @submit.prevent="saveQuestionTopic" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            主題名稱 <span class="text-red-500">*</span>
          </label>
          <input
            v-model="editingStructureItem.topic_name"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
            placeholder="輸入主題名稱"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            所屬類別 <span class="text-red-500">*</span>
          </label>
          <select
            v-model="editingStructureItem.category_id"
            required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
          >
            <option value="">請選擇類別</option>
            <option
              v-for="category in questionCategories"
              :key="category.id"
              :value="category.id"
            >
              {{ category.category_name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            描述
          </label>
          <textarea
            v-model="editingStructureItem.description"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
            placeholder="輸入主題描述（可選）"
          ></textarea>
        </div>

        <div class="flex justify-end space-x-3 pt-4">
          <button
            type="button"
            @click="showEditTopicModal = false"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200"
          >
            取消
          </button>
          <button
            type="submit"
            :disabled="structureLoading"
            class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
          >
            {{ structureLoading ? '儲存中...' : '儲存' }}
          </button>
        </div>
      </form>
    </Modal>

    <!-- Edit Factor Modal -->
    <Modal
      :model-value="showEditFactorModal"
      :title="editingStructureItem?.id ? '編輯風險因子' : '新增風險因子'"
      size="md"
      @update:model-value="(value) => showEditFactorModal = value"
      @close="showEditFactorModal = false"
    >
      <form @submit.prevent="saveQuestionFactor" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            因子名稱 <span class="text-red-500">*</span>
          </label>
          <input
            v-model="editingStructureItem.factor_name"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
            placeholder="輸入因子名稱"
          />
        </div>

        <div v-if="enableTopicLayer">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            所屬主題 <span class="text-red-500">*</span>
          </label>
          <select
            v-model="editingStructureItem.topic_id"
            :required="enableTopicLayer"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
          >
            <option value="">請選擇主題</option>
            <option
              v-for="topic in questionTopics"
              :key="topic.id"
              :value="topic.id"
            >
              {{ topic.topic_name }}
            </option>
          </select>
        </div>

        <div v-if="!enableTopicLayer">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            所屬類別 <span class="text-red-500">*</span>
          </label>
          <select
            v-model="editingStructureItem.category_id"
            :required="!enableTopicLayer"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
          >
            <option value="">請選擇類別</option>
            <option
              v-for="category in questionCategories"
              :key="category.id"
              :value="category.id"
            >
              {{ category.category_name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            描述
          </label>
          <textarea
            v-model="editingStructureItem.description"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
            placeholder="輸入因子描述（可選）"
          ></textarea>
        </div>

        <div class="flex justify-end space-x-3 pt-4">
          <button
            type="button"
            @click="showEditFactorModal = false"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200"
          >
            取消
          </button>
          <button
            type="submit"
            :disabled="structureLoading"
            class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
          >
            {{ structureLoading ? '儲存中...' : '儲存' }}
          </button>
        </div>
      </form>
    </Modal>

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal
      :model-value="showDeleteConfirmModal"
      title="確認刪除"
      :message="getDeleteConfirmMessage()"
      details="此操作無法復原。"
      type="danger"
      cancel-text="取消"
      confirm-text="刪除"
      @update:model-value="(value) => showDeleteConfirmModal = value"
      @close="showDeleteConfirmModal = false"
      @confirm="confirmDeleteStructureItem"
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
  DocumentTextIcon,
  ChartBarIcon,
  DocumentDuplicateIcon,
  CogIcon,
  UsersIcon,
  Cog6ToothIcon,
  ArrowDownTrayIcon,
  ArrowUpTrayIcon,
  ArrowDownIcon,
  ArrowPathIcon,
  ExclamationTriangleIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

import * as XLSX from 'xlsx'

const route = useRoute()
const companyId = computed(() => route.params.id)

// Notification system using SweetAlert
const { showSuccess, showError } = useNotification()

// Get templates from store
const templatesStore = useTemplatesStore()
const availableTemplates = computed(() => templatesStore?.templates || [])

// Get company data from database-backed composable
const { getCompanyNameById, loading: companiesLoading, error: companiesError } = useLocalCompanies()

// Store company name in reactive ref for async loading
const companyName = ref('載入中...')
const companyExists = ref(true)

// Load company name asynchronously
const loadCompanyName = async () => {
  if (!companyId.value) {
    companyName.value = '未知公司'
    companyExists.value = false
    return
  }

  try {
    const name = await getCompanyNameById(companyId.value)
    if (name) {
      companyName.value = name
      companyExists.value = true
    } else {
      companyName.value = `公司 #${companyId.value}`
      companyExists.value = false
    }
  } catch (error) {
    console.error('Error loading company name:', error)
    companyName.value = `公司 #${companyId.value}`
    companyExists.value = false
  }
}

// Show warning message for non-existent companies
const showCompanyWarning = computed(() => {
  return !companiesLoading.value && !companiesError.value && !companyExists.value
})

// Use personnel assignment API for loading personnel
const {
  loadPersonnel,
  personnel: availablePersonnel
} = usePersonnelAssignmentApi()

// Load personnel when company changes
const availableUsers = computed(() => availablePersonnel.value || [])

// 使用題項架構管理功能
const {
  structureLoading,
  categories: questionCategories,
  topics: questionTopics,
  factors: questionFactors,
  syncFromTemplate: syncStructureFromTemplate,
  getCategories,
  getTopics,
  getFactors,
  createCategory,
  updateCategory,
  deleteCategory,
  createTopic,
  updateTopic,
  deleteTopic,
  createFactor,
  updateFactor,
  deleteFactor
} = useQuestionStructure()

// Set page title safely for SSR
const pageTitle = computed(() => `題項管理 - ${companyName.value || '公司'}`)
usePageTitle(pageTitle)

// Reactive data
const showAddModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const showCopyOptions = ref(false)
const showPersonnelModal = ref(false)

// Question structure management
const showQuestionStructureModal = ref(false)
const managingQuestion = ref(null)

// Assessment Status Modal

// Structure management modals
const showCategoriesModal = ref(false)
const showTopicsModal = ref(false)
const showFactorsModal = ref(false)

// Topic layer toggle
const enableTopicLayer = ref(true) // Default to enabled

// Topic layer toggle change handler
const onTopicLayerToggleChange = (enabled) => {
  console.log('Topic layer toggle changed:', enabled)

  // Show confirmation if user is disabling topic layer and there are existing topics
  if (!enabled && questionTopics.value?.length > 0) {
    // Could add a confirmation dialog here
    console.warn('警告：停用風險主題層級將影響現有的主題資料')
  }

  // Update UI immediately
  enableTopicLayer.value = enabled

  // Save setting to localStorage so content page can access it
  if (process.client && managingQuestion.value?.id) {
    try {
      const settingKey = `question_management_${managingQuestion.value.id}_topic_layer`
      localStorage.setItem(settingKey, JSON.stringify(enabled))
      console.log('Saved topic layer setting:', enabled)
    } catch (error) {
      console.error('Error saving topic layer setting:', error)
    }
  }

  // Could trigger API call to save this setting to the assessment
  // saveTopicLayerSetting(managingQuestion.value?.id, enabled)
}

// Load topic layer setting from localStorage
const loadTopicLayerSetting = (questionId) => {
  if (process.client && questionId) {
    try {
      const settingKey = `question_management_${questionId}_topic_layer`
      const stored = localStorage.getItem(settingKey)
      if (stored !== null) {
        enableTopicLayer.value = JSON.parse(stored)
        console.log('Loaded topic layer setting:', enableTopicLayer.value)
      }
    } catch (error) {
      console.error('Error loading topic layer setting:', error)
    }
  }
}

// 移除舊的架構資料定義，現在使用 composable 管理

// DataTable columns for question structure management
const questionCategoryColumns = [
  {
    key: 'actions',
    label: '功能',
    class: 'text-center w-32'
  },
  {
    key: 'category_name',
    label: '類別名稱',
    sortable: true,
    class: 'font-medium text-gray-900 dark:text-white'
  },
  {
    key: 'description',
    label: '描述',
    class: 'text-gray-600 dark:text-gray-400'
  }
]

const questionTopicColumns = [
  {
    key: 'actions',
    label: '功能',
    class: 'text-center w-32'
  },
  {
    key: 'topic_name',
    label: '主題名稱',
    sortable: true,
    class: 'font-medium text-gray-900 dark:text-white'
  },
  {
    key: 'category_name',
    label: '所屬類別',
    class: 'text-gray-600 dark:text-gray-400'
  },
  {
    key: 'description',
    label: '描述',
    class: 'text-gray-600 dark:text-gray-400'
  }
]

const questionFactorColumns = [
  {
    key: 'actions',
    label: '功能',
    class: 'text-center w-32'
  },
  {
    key: 'factor_name',
    label: '因子名稱',
    sortable: true,
    class: 'font-medium text-gray-900 dark:text-white'
  },
  {
    key: 'topic_name',
    label: '所屬主題',
    class: 'text-gray-600 dark:text-gray-400'
  },
  {
    key: 'category_name',
    label: '所屬類別',
    class: 'text-gray-600 dark:text-gray-400'
  },
  {
    key: 'description',
    label: '描述',
    class: 'text-gray-600 dark:text-gray-400'
  }
]

// Form modals for editing
const showEditCategoryModal = ref(false)
const showEditTopicModal = ref(false)
const showEditFactorModal = ref(false)
const showDeleteConfirmModal = ref(false)
const editingStructureItem = ref(null)
const deletingStructureItem = ref(null)
const structureEditType = ref('') // 'category', 'topic', 'factor'

// Import/Export state
const showStructureImportModal = ref(false)
const selectedFile = ref(null)
const importErrors = ref([])
const isDragging = ref(false)
const isImporting = ref(false)
const fileInput = ref(null)

// Structure item management methods
const addQuestionCategory = () => {
  editingStructureItem.value = {
    category_name: '',
    description: '',
    sort_order: 0
  }
  structureEditType.value = 'category'
  showEditCategoryModal.value = true
}

const editQuestionCategory = (item) => {
  editingStructureItem.value = { ...item }
  structureEditType.value = 'category'
  showEditCategoryModal.value = true
}

const deleteQuestionCategory = (item) => {
  deletingStructureItem.value = item
  structureEditType.value = 'category'
  showDeleteConfirmModal.value = true
}

// Topic management methods
const addQuestionTopic = () => {
  editingStructureItem.value = {
    topic_name: '',
    description: '',
    sort_order: 0,
    category_id: null
  }
  structureEditType.value = 'topic'
  showEditTopicModal.value = true
}

const editQuestionTopic = (item) => {
  editingStructureItem.value = { ...item }
  structureEditType.value = 'topic'
  showEditTopicModal.value = true
}

const deleteQuestionTopic = (item) => {
  deletingStructureItem.value = item
  structureEditType.value = 'topic'
  showDeleteConfirmModal.value = true
}

// Factor management methods
const addQuestionFactor = () => {
  editingStructureItem.value = {
    factor_name: '',
    description: '',
    sort_order: 0,
    topic_id: null,
    category_id: null
  }
  structureEditType.value = 'factor'
  showEditFactorModal.value = true
}

const editQuestionFactor = (item) => {
  editingStructureItem.value = { ...item }
  structureEditType.value = 'factor'
  showEditFactorModal.value = true
}

const deleteQuestionFactor = (item) => {
  deletingStructureItem.value = item
  structureEditType.value = 'factor'
  showDeleteConfirmModal.value = true
}

// Save structure item
const saveQuestionCategory = async () => {
  if (!managingQuestion.value?.id) {
    console.error('No assessment ID found')
    return
  }

  try {
    const data = { ...editingStructureItem.value }
    const isEditing = !!data.id

    if (data.id) {
      // Update existing category
      await updateCategory(data.id, data)
    } else {
      // Create new category
      await createCategory(managingQuestion.value.id, data)
    }

    // Reload data
    await loadQuestionStructureData(managingQuestion.value.id)

    // Close modal
    showEditCategoryModal.value = false

    // Show success notification
    await showSuccess(
      isEditing ? '更新成功' : '新增成功',
      `風險分類「${data.category_name}」已成功${isEditing ? '更新' : '建立'}`
    )

    console.log('Category saved successfully')
  } catch (error) {
    console.error('Error saving category:', error)

    // Show error notification
    const isEditing = !!editingStructureItem.value.id
    await showError(
      isEditing ? '更新失敗' : '新增失敗',
      error.message || '操作時發生錯誤，請稍後再試'
    )
  }
}

const saveQuestionTopic = async () => {
  if (!managingQuestion.value?.id) {
    console.error('No assessment ID found')
    return
  }

  try {
    const data = { ...editingStructureItem.value }
    const isEditing = !!data.id

    if (data.id) {
      // Update existing topic
      await updateTopic(data.id, data)
    } else {
      // Create new topic
      await createTopic(managingQuestion.value.id, data)
    }

    // Reload data
    await loadQuestionStructureData(managingQuestion.value.id)

    // Close modal
    showEditTopicModal.value = false

    // Show success notification
    await showSuccess(
      isEditing ? '更新成功' : '新增成功',
      `風險主題「${data.topic_name}」已成功${isEditing ? '更新' : '建立'}`
    )

    console.log('Topic saved successfully')
  } catch (error) {
    console.error('Error saving topic:', error)

    // Show error notification
    const isEditing = !!editingStructureItem.value.id
    await showError(
      isEditing ? '更新失敗' : '新增失敗',
      error.message || '操作時發生錯誤，請稍後再試'
    )
  }
}

const saveQuestionFactor = async () => {
  if (!managingQuestion.value?.id) {
    console.error('No assessment ID found')
    return
  }

  try {
    const data = { ...editingStructureItem.value }
    const isEditing = !!data.id

    if (data.id) {
      // Update existing factor
      await updateFactor(data.id, data)
    } else {
      // Create new factor
      await createFactor(managingQuestion.value.id, data)
    }

    // Reload data
    await loadQuestionStructureData(managingQuestion.value.id)

    // Close modal
    showEditFactorModal.value = false

    // Show success notification
    await showSuccess(
      isEditing ? '更新成功' : '新增成功',
      `風險因子「${data.factor_name}」已成功${isEditing ? '更新' : '建立'}`
    )

    console.log('Factor saved successfully')
  } catch (error) {
    console.error('Error saving factor:', error)

    // Show error notification
    const isEditing = !!editingStructureItem.value.id
    await showError(
      isEditing ? '更新失敗' : '新增失敗',
      error.message || '操作時發生錯誤，請稍後再試'
    )
  }
}

// Delete confirmation message
const getDeleteConfirmMessage = () => {
  if (!deletingStructureItem.value) return ''

  switch (structureEditType.value) {
    case 'category':
      return `確定要刪除風險類別「${deletingStructureItem.value.category_name}」嗎？`
    case 'topic':
      return `確定要刪除風險主題「${deletingStructureItem.value.topic_name}」嗎？`
    case 'factor':
      return `確定要刪除風險因子「${deletingStructureItem.value.factor_name}」嗎？`
    default:
      return '確定要刪除此項目嗎？'
  }
}

// Confirm delete
const confirmDeleteStructureItem = async () => {
  if (!deletingStructureItem.value || !managingQuestion.value?.id) {
    console.error('No item to delete or assessment ID')
    return
  }

  try {
    const itemId = deletingStructureItem.value.id
    const itemName = deletingStructureItem.value.category_name ||
                    deletingStructureItem.value.topic_name ||
                    deletingStructureItem.value.factor_name || '項目'

    switch (structureEditType.value) {
      case 'category':
        await deleteCategory(itemId)
        break
      case 'topic':
        await deleteTopic(itemId)
        break
      case 'factor':
        await deleteFactor(itemId)
        break
    }

    // Reload data
    await loadQuestionStructureData(managingQuestion.value.id)

    // Close modal
    showDeleteConfirmModal.value = false

    // Show success notification
    const typeNames = {
      'category': '風險分類',
      'topic': '風險主題',
      'factor': '風險因子'
    }

    await showSuccess(
      '刪除成功',
      `${typeNames[structureEditType.value]}「${itemName}」已成功刪除`
    )

    console.log(`${structureEditType.value} deleted successfully`)
  } catch (error) {
    console.error(`Error deleting ${structureEditType.value}:`, error)

    // Show error notification with specific API error message
    const typeNames = {
      'category': '風險分類',
      'topic': '風險主題',
      'factor': '風險因子'
    }

    await showError(
      '刪除失敗',
      error.message || `刪除${typeNames[structureEditType.value]}時發生錯誤`
    )
  }
}

// Computed property for form modal visibility
const showFormModal = computed(() => showAddModal.value || showEditModal.value)

// Assignment-related data
const selectedQuestionForAssignment = ref(null)
const questionContentForAssignment = ref([])
const itemToDelete = ref(null)
const itemToCopy = ref(null)
const editingItem = ref(null)
const templateSelectRef = ref(null)

const formData = ref({
  templateId: '',
  year: new Date().getFullYear()
})

const copyOptions = ref({
  includeQuestions: true,
  includeResults: false
})

// Date is now stored in display format, no need for formatDate function

// Question management composable
const {
  getQuestionManagementByCompany,
  addQuestionManagementItem,
  updateQuestionManagementItem,
  deleteQuestionManagementItem,
  copyQuestionManagementItem,
  initializeCompanyQuestionManagement,
  refreshCompanyAssessments
} = useQuestionManagement()

// Reactive data for question management items
const questionManagement = ref([])
const isLoadingQuestionManagement = ref(false)

// Load question management data
const loadQuestionManagementData = async () => {
  isLoadingQuestionManagement.value = true
  try {
    // 使用 refreshCompanyAssessments 強制從後端重新載入資料
    await refreshCompanyAssessments(companyId.value)
    const data = await getQuestionManagementByCompany(companyId.value)
    questionManagement.value = data || []

    console.log('=== /admin/risk-assessment/questions/1/management 頁面資料 ===')
    console.log('Company ID:', companyId.value)
    console.log('Company Name:', companyName.value)
    console.log('Question Management Data:', data)

    // 檢查建立日期格式 - 現在直接存儲顯示格式
    if (data && data.length > 0) {
      console.log('=== 建立日期格式檢查 ===')
      data.forEach((item, index) => {
        console.log(`第${index + 1}筆資料:`)
        console.log('  建立日期:', item.createdAt)
      })
      console.log('=== 建立日期格式檢查結束 ===')
    }

    console.log('Available Templates:', availableTemplates.value)
    console.log('Available Users:', availableUsers.value)
    console.log('=== 資料結束 ===')
  } catch (error) {
    console.error('Error loading question management data:', error)
    questionManagement.value = []
  } finally {
    isLoadingQuestionManagement.value = false
  }
}

// Initialize question management for this company (ensures empty array for new companies)
onMounted(async () => {
  try {
    // Load company name first
    await loadCompanyName()

    // Load personnel data for the company
    if (companyId.value) {
      try {
        await loadPersonnel(companyId.value)
        console.log('Personnel loaded for company:', companyId.value)
      } catch (error) {
        console.error('Error loading personnel:', error)
      }
    }

    await initializeCompanyQuestionManagement(companyId.value)

    // Load question management data
    await loadQuestionManagementData()

    // Initialize templates store to fetch templates from database
    try {
      await templatesStore.initialize()
      console.log('Templates loaded from database:', templatesStore.templates.length)
    } catch (error) {
      console.error('Failed to load templates from database:', error)
    }

    console.log('=== 頁面載入時的初始資料 ===')
    console.log('Route Params:', route.params)
    console.log('Company ID from Route:', companyId.value)
    console.log('Available Templates on Mount:', availableTemplates.value)
    console.log('Available Users on Mount:', availableUsers.value)
    console.log('Available Personnel on Mount:', availablePersonnel.value)
    console.log('=== 初始資料結束 ===')
  } catch (error) {
    console.error('Error during component mount:', error)
    // Set safe defaults to prevent render errors
    questionManagement.value = []
    isLoadingQuestionManagement.value = false
  }
})

// Watch for changes in question management data (disabled to prevent performance issues)
// watch(questionManagement, (newData, oldData) => {
//   console.log('=== 題項管理資料變更 ===')
//   console.log('舊資料:', oldData)
//   console.log('新資料:', newData)
//   console.log('資料筆數:', newData?.length || 0)
//   if (newData && newData.length > 0) {
//     console.log('第一筆資料結構:', newData[0])
//     console.log('所有資料詳細內容:', JSON.stringify(newData, null, 2))
//   }
//   console.log('=== 資料變更結束 ===')
// }, { deep: true, immediate: true })

// Watch for changes in company ID (route parameter changes)
watch(companyId, async (newId, oldId) => {
  if (newId !== oldId && newId) {
    console.log('=== 公司 ID 變更，重新載入公司資料 ===')
    console.log('舊 ID:', oldId)
    console.log('新 ID:', newId)
    await loadCompanyName()

    // Load personnel for the new company
    try {
      await loadPersonnel(newId)
      console.log('Personnel reloaded for new company:', newId)
    } catch (error) {
      console.error('Error reloading personnel:', error)
    }

    console.log('=== 公司資料重新載入完成 ===')
  }
})

// Watch for changes in company name (disabled to prevent unnecessary logging)
// watch(companyName, (newName, oldName) => {
//   console.log('=== 公司名稱變更 ===')
//   console.log('舊名稱:', oldName)
//   console.log('新名稱:', newName)
//   console.log('=== 名稱變更結束 ===')
// }, { immediate: true })

// Auto-focus template select when form modal opens
watch(showFormModal, (newValue) => {
  if (newValue) {
    nextTick(() => {
      templateSelectRef.value?.focus()
    })
  }
})

// DataTable columns configuration
const columns = ref([
  {
    key: 'actions',
    label: '功能',
    sortable: false,
    cellClass: 'text-base text-gray-900 dark:text-white'
  },
  {
    key: 'templateVersion',
    label: '範本版本',
    sortable: true,
    cellClass: 'text-base text-gray-900 dark:text-white'
  },
  {
    key: 'year',
    label: '年份',
    sortable: true,
    cellClass: 'text-base font-medium text-gray-900 dark:text-white'
  },
  {
    key: 'createdAt',
    label: '建立日期',
    sortable: true,
    cellClass: 'text-base text-gray-900 dark:text-white'
  }
])

// Note: This function is commented out as it's not currently in use
// Keep for potential future use
/*
const copyTemplateContentToQuestionManagement = (templateId, questionId) => {
  // Get template content from templates store
  const templateContent = templatesStore?.getTemplateContent?.(templateId)
  const riskCategories = templatesStore?.getRiskCategories?.(templateId)

  // Create a unique key for this question management item's content
  const contentKey = `question_${companyId.value}_${questionId}`

  // Copy template content to question management context
  if (templateContent.value && templateContent.value.length > 0) {
    // First copy the categories to create mapping
    const copiedCategories = riskCategories.value ? riskCategories.value.map((cat, index) => ({
      ...cat,
      id: `${contentKey}_cat_${index + 1}`,
      originalTemplateId: templateId
    })) : []

    // Create a mapping from old category IDs to new category IDs
    const categoryIdMap = {}
    if (riskCategories.value) {
      riskCategories.value.forEach((originalCat, index) => {
        const newCategoryId = `${contentKey}_cat_${index + 1}`
        categoryIdMap[originalCat.id] = newCategoryId
      })
    }

    // Copy content and map categoryId references
    const copiedContent = templateContent.value.map((item, index) => ({
      ...item,
      id: `${contentKey}_${index + 1}`,
      categoryId: item.categoryId ? categoryIdMap[item.categoryId] : item.categoryId,
      order: index + 1,
      originalTemplateId: templateId
    }))

    // Save to localStorage for persistence (only on client side)
    if (process.client) {
      const questionContentKey = `esg-question-content-${contentKey}`
      const questionCategoriesKey = `esg-question-categories-${contentKey}`

      try {
        localStorage.setItem(questionContentKey, JSON.stringify(copiedContent))
        localStorage.setItem(questionCategoriesKey, JSON.stringify(copiedCategories))
        console.log(`Successfully copied template ${templateId} content to question ${questionId}`)
      } catch (error) {
        console.error('Error saving question content to storage:', error)
      }
    }
  }
}
*/

const editItem = (item) => {
  editingItem.value = item
  formData.value.templateId = item.templateId
  formData.value.year = item.year
  showEditModal.value = true
}

const deleteItem = (item) => {
  itemToDelete.value = item
  showDeleteModal.value = true
}

const showCopyModal = (item) => {
  itemToCopy.value = item
  showCopyOptions.value = true
}

const confirmDelete = async () => {
  if (itemToDelete.value) {
    try {
      await deleteQuestionManagementItem(companyId.value, itemToDelete.value.id)
      await loadQuestionManagementData() // Reload data after deletion
      await showSuccess('題項管理已成功刪除')
    } catch (error) {
      console.error('Error deleting item:', error)
      await showError(error?.message || '刪除題項管理時發生錯誤，請稍後再試')
    }
  }
  showDeleteModal.value = false
  itemToDelete.value = null
}

const confirmCopy = async () => {
  if (itemToCopy.value) {
    try {
      await copyQuestionManagementItem(companyId.value, itemToCopy.value.id, copyOptions.value)
      await loadQuestionManagementData() // Reload data after copying
    } catch (error) {
      console.error('Error copying item:', error)
    }
  }
  
  showCopyOptions.value = false
  itemToCopy.value = null
  copyOptions.value.includeQuestions = true
  copyOptions.value.includeResults = false
}

const submitForm = async () => {
  const template = availableTemplates.value.find(t => t.id === parseInt(formData.value.templateId))
  
  console.log('=== 表單提交 ===')
  console.log('表單資料:', formData.value)
  console.log('選中的範本:', template)
  console.log('公司ID:', companyId.value)
  
  try {
    if (showAddModal.value) {
      // Add new item
      const itemData = {
        templateId: parseInt(formData.value.templateId),
        templateVersion: template ? template.version_name : '',
        year: parseInt(formData.value.year)
      }
      
      console.log('新增項目資料:', itemData)
      const newItem = await addQuestionManagementItem(companyId.value, itemData)
      console.log('新增後返回的項目:', newItem)

      // Copy template content when creating new question management item
      if (template && newItem) {
        console.log('開始從範本同步內容到資料庫...')
        try {
          // 呼叫後端 API 從範本複製架構到題項管理
          const syncResponse = await $fetch(`/api/v1/question-management/assessment/${newItem.id}/sync-from-template`, {
            method: 'POST'
          })

          if (syncResponse.success) {
            console.log('範本架構複製成功:', syncResponse.data)
          } else {
            console.error('範本架構複製失敗:', syncResponse.message)
          }
        } catch (error) {
          console.error('同步範本架構時發生錯誤:', error)
        }
      }

      // Reload data after adding
      await loadQuestionManagementData()
      await showSuccess('題項管理已成功新增')
    } else if (showEditModal.value) {
      // Update existing item
      const oldTemplateId = editingItem.value.templateId
      const newTemplateId = parseInt(formData.value.templateId)

      const itemData = {
        templateId: newTemplateId,
        templateVersion: template ? template.version_name : '',
        year: parseInt(formData.value.year)
      }

      console.log('編輯項目資料:', itemData)
      console.log('編輯的項目:', editingItem.value)
      await updateQuestionManagementItem(companyId.value, editingItem.value.id, itemData)

      // If template changed, copy new template content
      if (oldTemplateId !== newTemplateId && template) {
        console.log('範本已變更，重新從範本同步內容到資料庫...')
        try {
          // 呼叫後端 API 從範本複製架構到題項管理
          const syncResponse = await $fetch(`/api/v1/question-management/assessment/${editingItem.value.id}/sync-from-template`, {
            method: 'POST'
          })

          if (syncResponse.success) {
            console.log('範本架構複製成功:', syncResponse.data)
          } else {
            console.error('範本架構複製失敗:', syncResponse.message)
          }
        } catch (error) {
          console.error('同步範本架構時發生錯誤:', error)
        }
      }

      // Reload data after updating
      await loadQuestionManagementData()
      await showSuccess('題項管理已成功更新')
    }
  } catch (error) {
    console.error('Error in submitForm:', error)
    await showError(error?.message || '操作失敗，請稍後再試')
  }
  
  console.log('=== 表單提交結束 ===')
  closeModals()
}

const closeModals = () => {
  showAddModal.value = false
  showEditModal.value = false
  editingItem.value = null
  formData.value.templateId = ''
  formData.value.year = new Date().getFullYear()
}

// Action button methods
const showPersonnelAssignment = async (item) => {
  selectedQuestionForAssignment.value = item

  // Load question content for the selected item
  await loadQuestionContentForAssignment(item)

  showPersonnelModal.value = true
}

// Load question content for assignment
const loadQuestionContentForAssignment = async (questionItem) => {
  try {
    // Reset content first
    questionContentForAssignment.value = []

    if (!questionItem || !questionItem.id) {
      console.warn('Invalid question item provided for assignment')
      return
    }

    console.log('=== 載入人員指派題項內容（從資料庫API）===')
    console.log('Question Item:', questionItem)

    // Use API to fetch question content from database instead of localStorage
    try {
      const response = await $fetch(`/api/v1/question-management/assessment/${questionItem.id}/contents`)

      if (response.success && Array.isArray(response.data)) {
        // Transform API response to compatible format
        const transformedContent = response.data.map(item => ({
          contentId: item.id, // Use database ID (numeric)
          id: item.id, // Backward compatibility
          topic: item.title || '未命名題目',
          description: item.description || '',
          categoryId: item.category_id,
          category_id: item.category_id,
          assignmentCount: 0 // Will be updated by assignment API
        }))

        questionContentForAssignment.value = transformedContent
        console.log('Loaded content from database API:', transformedContent)
        console.log('Content IDs:', transformedContent.map(item => item.contentId))
      } else {
        console.warn('Invalid API response or no content found')
        questionContentForAssignment.value = []
      }
    } catch (apiError) {
      console.warn('API fetch failed, falling back to localStorage:', apiError)

      // Fallback to localStorage for backward compatibility
      const contentKey = `question_${companyId.value}_${questionItem.id}`
      const questionContentKey = `esg-question-content-${contentKey}`

      if (process.client) {
        const storedContent = localStorage.getItem(questionContentKey)
        if (storedContent) {
          const parsedContent = JSON.parse(storedContent)
          console.log('Fallback: loaded content from localStorage:', parsedContent)
          questionContentForAssignment.value = Array.isArray(parsedContent) ? parsedContent : []
        } else {
          console.log('No content found in localStorage for key:', questionContentKey)
        }
      }
    }

    console.log('=== 載入完成 ===')
    console.log('Final questionContentForAssignment:', questionContentForAssignment.value)
  } catch (error) {
    console.error('Error loading question content for assignment:', error)
    questionContentForAssignment.value = []
  }
}

// Handle assignment updates
const handleAssignmentUpdated = () => {
  // Assignment updated, you can add any additional logic here if needed
  console.log('Assignment updated for question:', selectedQuestionForAssignment.value?.id)
}

const viewAssignments = (item) => {
  console.log('Navigating to assignments for:', item)
  // 導航到新的指派狀況頁面
  navigateTo(`/admin/risk-assessment/questions/${item.id}/assignments`)
}

const showStatistics = (item) => {
  console.log('Navigating to statistics for:', item)
  // 導航到統計結果頁面
  navigateTo(`/admin/risk-assessment/questions/${item.id}/statistics`)
}

const showCopyOverallForm = (item) => {
  // TODO: Implement copy overall form functionality
  console.log('Copy overall form for:', item)
}

const manageQuestionContent = async (item) => {
  // Navigate to question content management page using hierarchical routing
  console.log('Navigating to content page for item:', item)
  console.log('Target URL:', `/admin/risk-assessment/questions/${companyId.value}/management/${item.id}/content`)

  try {
    await navigateTo(`/admin/risk-assessment/questions/${companyId.value}/management/${item.id}/content`)
  } catch (error) {
    console.error('Navigation error:', error)
  }
}

// Question structure management functions
const manageQuestionStructure = async (item) => {
  managingQuestion.value = item

  // Load topic layer setting for this question
  loadTopicLayerSetting(item.id)

  // Load question structure data (categories, topics, factors)
  if (item.id) {
    try {
      await loadQuestionStructureData(item.id)
    } catch (error) {
      console.error('Error loading structure data:', error)
      await showError('載入失敗', '無法載入架構資料，請稍後再試')
      return
    }
  }

  showQuestionStructureModal.value = true
}

// Note: This function is commented out as it's not currently in use
/*
const getTemplateVersionName = (templateId) => {
  if (!templateId) return '未知範本'
  const template = availableTemplates.value.find(t => t.id === templateId)
  return template ? template.version_name : `範本 #${templateId}`
}
*/

const openQuestionManagementModal = async (type) => {
  if (!managingQuestion.value) {
    console.error('No question selected for management')
    await showError('錯誤', '請先選擇一個題項管理記錄')
    return
  }

  // Load topic layer setting for this question
  loadTopicLayerSetting(managingQuestion.value.id)

  // 使用評估記錄 ID 載入題項架構資料（獨立於範本）
  if (managingQuestion.value.id) {
    try {
      await loadQuestionStructureData(managingQuestion.value.id)

      // Ensure data is loaded properly
      console.log('Structure data loaded:', {
        categories: questionCategories.value?.length || 0,
        topics: questionTopics.value?.length || 0,
        factors: questionFactors.value?.length || 0
      })
    } catch (error) {
      console.error('Error loading question structure data:', error)
      await showError('載入失敗', '無法載入架構資料，請稍後再試')
      return
    }
  }

  // Show appropriate modal
  switch (type) {
    case 'categories':
      showCategoriesModal.value = true
      break
    case 'topics':
      showTopicsModal.value = true
      break
    case 'factors':
      showFactorsModal.value = true
      break
    default:
      console.warn(`Unknown structure type: ${type}`)
  }
}

// 載入題項架構資料（獨立於範本）
const loadQuestionStructureData = async (assessmentId) => {
  if (!assessmentId) {
    console.warn('No assessment ID provided for loading structure')
    return
  }

  try {
    console.log('Loading question structure data for assessment:', assessmentId)

    // 載入分類、主題、因子資料
    const results = await Promise.allSettled([
      getCategories(assessmentId),
      getTopics(assessmentId),
      getFactors(assessmentId)
    ])

    // Check each result individually
    results.forEach((result, index) => {
      const types = ['categories', 'topics', 'factors']
      if (result.status === 'rejected') {
        console.error(`Error loading ${types[index]}:`, result.reason)
      }
    })

    console.log('Loaded question structure data:', {
      assessmentId,
      categories: questionCategories.value?.length || 0,
      topics: questionTopics.value?.length || 0,
      factors: questionFactors.value?.length || 0
    })
  } catch (error) {
    console.error('Error loading question structure data:', error)
    // 如果載入失敗，可能是還沒有架構資料，這是正常的
    console.log('No structure data found - this might be expected for new assessments')

    // Ensure data is always an array
    if (!questionCategories.value) questionCategories.value = []
    if (!questionTopics.value) questionTopics.value = []
    if (!questionFactors.value) questionFactors.value = []
  }
}

// Refresh structure data with error handling
const refreshStructureData = async () => {
  if (!managingQuestion.value?.id) {
    console.error('No assessment ID for refresh')
    return
  }

  try {
    await loadQuestionStructureData(managingQuestion.value.id)
    await showSuccess('重新整理成功', '架構資料已更新')
  } catch (error) {
    console.error('Error refreshing structure data:', error)
    await showError('重新整理失敗', '無法載入架構資料，請稍後再試')
  }
}

const syncFromTemplate = async () => {
  if (!managingQuestion.value?.id) {
    console.error('No assessment ID found for syncing')
    return
  }

  try {
    console.log('Syncing from template for assessment:', managingQuestion.value.id)

    // 使用新的 API 從範本同步架構
    const result = await syncStructureFromTemplate(managingQuestion.value.id)

    console.log('Successfully synced from template:', result)

    // 重新載入架構資料
    await loadQuestionStructureData(managingQuestion.value.id)

    // 顯示成功訊息（可以加入通知系統）
    console.log('Template sync completed successfully')
  } catch (error) {
    console.error('Error syncing from template:', error)
    // 這裡可以加入錯誤通知
  }
}

const goToQuestionContent = (questionId) => {
  if (questionId) {
    showQuestionStructureModal.value = false
    navigateTo(`/admin/risk-assessment/questions/${companyId.value}/management/${questionId}/content`)
  }
}

// HTML Helper Functions for Description Display

/**
 * Strip HTML tags from string to get plain text
 * @param {string} html - HTML string
 * @returns {string} Plain text without HTML tags
 */
const stripHtml = (html) => {
  if (!html) return ''
  const tmp = document.createElement('div')
  tmp.innerHTML = html
  return tmp.textContent || tmp.innerText || ''
}

/**
 * Truncate HTML content to specified character limit
 * @param {string} html - HTML string
 * @param {number} maxLength - Maximum character length
 * @returns {string} Truncated HTML with ellipsis if needed
 */
const truncateHtml = (html, maxLength = 20) => {
  if (!html) return ''

  const plainText = stripHtml(html)

  // If text is shorter than max length, return original HTML
  if (plainText.length <= maxLength) {
    return html
  }

  // Truncate plain text and add ellipsis
  const truncated = plainText.substring(0, maxLength) + '...'

  // Return truncated text wrapped in same HTML structure if original had tags
  if (html.includes('<')) {
    return `<span>${truncated}</span>`
  }

  return truncated
}

// Import/Export Functions

const showLoading = (message) => {
  // Simple loading implementation - you can enhance this with a proper loading component
  console.log('Loading:', message)
}

const closeAll = () => {
  // Close all modals - used during export
  console.log('Closing all modals')
}

const exportStructure = async () => {
  if (!managingQuestion.value) return

  try {
    showLoading('正在匯出架構資料...')

    const assessmentId = managingQuestion.value.id
    const hasTopicLayer = enableTopicLayer.value

    // Fetch all structure data
    await loadQuestionStructureData(assessmentId)

    const categories = questionCategories.value || []
    const topics = hasTopicLayer ? (questionTopics.value || []) : []
    const factors = questionFactors.value || []

    // Prepare data for Excel
    const data = []

    factors.forEach(factor => {
      const category = categories.find(c => c.id === factor.category_id)
      const row = {
        '風險類別名稱': category?.category_name || '',
        '風險類別描述': category?.description || ''
      }

      if (hasTopicLayer) {
        const topic = topics.find(t => t.id === factor.topic_id)
        row['風險主題名稱'] = topic?.topic_name || ''
        row['風險主題描述'] = topic?.description || ''
      }

      row['風險因子名稱'] = factor.factor_name || ''
      row['風險因子描述'] = factor.description || ''

      data.push(row)
    })

    // Create workbook
    const ws = XLSX.utils.json_to_sheet(data)
    const wb = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(wb, ws, '架構資料')

    // Style the header row
    const range = XLSX.utils.decode_range(ws['!ref'])
    for (let C = range.s.c; C <= range.e.c; ++C) {
      const address = XLSX.utils.encode_col(C) + '1'
      if (!ws[address]) continue
      ws[address].s = {
        fill: { fgColor: { rgb: '4F46E5' } },
        font: { bold: true, color: { rgb: 'FFFFFF' } }
      }
    }

    // Set column widths
    ws['!cols'] = [
      { wch: 20 },  // 風險類別名稱
      { wch: 40 },  // 風險類別描述
      ...(hasTopicLayer ? [
        { wch: 20 },  // 風險主題名稱
        { wch: 40 }   // 風險主題描述
      ] : []),
      { wch: 20 },  // 風險因子名稱
      { wch: 40 }   // 風險因子描述
    ]

    // Generate filename
    const timestamp = new Date().toISOString().slice(0, 10)
    const filename = `${managingQuestion.value.templateVersion}_${managingQuestion.value.year}年_架構_${timestamp}.xlsx`

    // Download
    XLSX.writeFile(wb, filename)

    closeAll()
    await showSuccess('匯出成功', `架構資料已匯出為 ${filename}`)
  } catch (error) {
    console.error('Export structure error:', error)
    closeAll()
    await showError('匯出失敗', '匯出架構資料時發生錯誤')
  }
}

const downloadTemplate = () => {
  if (!managingQuestion.value) return

  const hasTopicLayer = enableTopicLayer.value

  // Create template data with headers only
  const headers = {
    '風險類別名稱': '',
    '風險類別描述': ''
  }

  if (hasTopicLayer) {
    headers['風險主題名稱'] = ''
    headers['風險主題描述'] = ''
  }

  headers['風險因子名稱'] = ''
  headers['風險因子描述'] = ''

  // Create workbook with empty template
  const ws = XLSX.utils.json_to_sheet([headers])
  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, '架構資料')

  // Style the header row
  const range = XLSX.utils.decode_range(ws['!ref'])
  for (let C = range.s.c; C <= range.e.c; ++C) {
    const address = XLSX.utils.encode_col(C) + '1'
    if (!ws[address]) continue
    ws[address].s = {
      fill: { fgColor: { rgb: '4F46E5' } },
      font: { bold: true, color: { rgb: 'FFFFFF' } }
    }
  }

  // Set column widths
  ws['!cols'] = [
    { wch: 20 },  // 風險類別名稱
    { wch: 40 },  // 風險類別描述
    ...(hasTopicLayer ? [
      { wch: 20 },  // 風險主題名稱
      { wch: 40 }   // 風險主題描述
    ] : []),
    { wch: 20 },  // 風險因子名稱
    { wch: 40 }   // 風險因子描述
  ]

  // Generate filename
  const timestamp = new Date().toISOString().slice(0, 10)
  const filename = `${managingQuestion.value.templateVersion}_${managingQuestion.value.year}年_匯入範本_${timestamp}.xlsx`

  // Download
  XLSX.writeFile(wb, filename)
}

const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file) {
    selectedFile.value = file
    importErrors.value = []
  }
}

const handleDrop = (event) => {
  isDragging.value = false
  const file = event.dataTransfer.files[0]
  if (file && (file.name.endsWith('.xlsx') || file.name.endsWith('.xls'))) {
    selectedFile.value = file
    importErrors.value = []
  } else {
    importErrors.value = ['請上傳 Excel 檔案 (.xlsx 或 .xls)']
  }
}

const closeStructureImportModal = () => {
  showStructureImportModal.value = false
  selectedFile.value = null
  importErrors.value = []
  isDragging.value = false
}

const processImport = async () => {
  if (!selectedFile.value || !managingQuestion.value) return

  isImporting.value = true
  importErrors.value = []

  try {
    showLoading('正在匯入架構資料...')

    // Create FormData for file upload
    const formData = new FormData()
    formData.append('file', selectedFile.value)

    // Upload file to backend for processing with RichText support
    const assessmentId = managingQuestion.value.id
    const response = await $fetch(`/api/v1/question-management/assessment/${assessmentId}/import-structure`, {
      method: 'POST',
      body: formData
    })

    if (response.success) {
      // Refresh data
      await refreshStructureData()

      closeAll()
      closeStructureImportModal()

      // Show success message with import details
      let message = response.message || '架構匯入成功'
      if (response.data?.errors && response.data.errors.length > 0) {
        message += `\n\n部分資料匯入失敗：\n${response.data.errors.join('\n')}`
      }

      await showSuccess('匯入完成', message)
    } else {
      throw new Error(response.message || '匯入失敗')
    }

  } catch (error) {
    console.error('Import error:', error)
    closeAll()

    let errorMessage = '匯入架構資料時發生錯誤'
    if (error.message) {
      errorMessage += '：' + error.message
    }

    await showError('匯入失敗', errorMessage)
    importErrors.value = [errorMessage]
  } finally {
    isImporting.value = false
  }
}


</script>