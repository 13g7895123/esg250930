<template>
  <div class="p-6">
    <!-- Page Header -->
    <PageHeader
      :title="`題項管理 - ${companyName}`"
      :description="`管理 ${companyName} 的風險評估題項與相關資料`"
      :show-back-button="true"
      back-path="/admin/risk-assessment/questions"
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
        class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md flex flex-col"
        style="max-height: 90vh;"
        @click.stop
      >
        <div class="p-6 overflow-y-auto flex-1 min-h-0">
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
      v-model="showQuestionStructureModal"
      :title="`架構管理 - ${managingQuestion?.templateVersion || ''} (${managingQuestion?.year || ''}年)`"
      :item-name="`${managingQuestion?.templateVersion || ''} (${managingQuestion?.year || ''}年)`"
      :risk-topics-enabled="enableTopicLayer"
      :show-export-import="true"
      management-type="question"
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
        :data="sortedQuestionCategories"
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
          <HtmlTooltip
            v-if="item.description"
            :content="item.description"
            :truncate-length="20"
            text-class="text-base text-gray-500 dark:text-gray-400 cursor-pointer"
            max-width="min-w-[300px] max-w-[500px]"
          />
          <span v-else class="text-base text-gray-400 dark:text-gray-500 italic">無描述</span>
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
        :data="sortedQuestionTopics"
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
          <HtmlTooltip
            v-if="item.description"
            :content="item.description"
            :truncate-length="20"
            text-class="text-base text-gray-500 dark:text-gray-400 cursor-pointer"
            max-width="min-w-[300px] max-w-[500px]"
          />
          <span v-else class="text-base text-gray-400 dark:text-gray-500 italic">無描述</span>
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
        :data="sortedQuestionFactors"
        :columns="questionFactorColumns"
        search-placeholder="搜尋風險因子..."
        :search-fields="['factor_name', 'topic_name', 'category_name', 'description']"
        empty-title="還沒有風險因子"
        empty-message="開始建立您的第一個風險因子"
        no-search-results-title="沒有找到符合的風險因子"
        no-search-results-message="請嘗試其他搜尋關鍵字"
        :loading="structureLoading"
        :draggable="true"
        @drag-end="handleQuestionFactorDragEnd"
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

        <!-- Custom Factor Name Cell with HtmlTooltip -->
        <template #cell-factor_name="{ item }">
          <HtmlTooltip
            :content="item.factor_name || '-'"
            :truncate-length="6"
            text-class="text-base font-medium text-gray-900 dark:text-white cursor-pointer"
          />
        </template>

        <!-- Custom Category Name Cell with HtmlTooltip -->
        <template #cell-category_name="{ item }">
          <HtmlTooltip
            :content="item.category_name || '-'"
            :truncate-length="6"
            text-class="text-base text-gray-500 dark:text-gray-400 cursor-pointer"
          />
        </template>

        <!-- Custom Topic Name Cell with HtmlTooltip -->
        <template #cell-topic_name="{ item }">
          <HtmlTooltip
            v-if="item.topic_name"
            :content="item.topic_name"
            :truncate-length="6"
            text-class="text-base text-gray-500 dark:text-gray-400 cursor-pointer"
          />
          <span v-else class="text-base text-gray-400 dark:text-gray-500">-</span>
        </template>

        <!-- Custom Description Cell with HTML rendering and truncation -->
        <template #cell-description="{ item }">
          <HtmlTooltip
            v-if="item.description"
            :content="item.description"
            :truncate-length="20"
            text-class="text-base text-gray-500 dark:text-gray-400 cursor-pointer"
            max-width="min-w-[300px] max-w-[500px]"
          />
          <span v-else class="text-base text-gray-400 dark:text-gray-500 italic">無描述</span>
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

import HtmlTooltip from '~/components/HtmlTooltip.vue'
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
  deleteFactor,
  reorderFactors
} = useQuestionStructure()

// 直接使用後端回傳的資料順序，不再進行前端排序
const sortedQuestionCategories = computed(() => {
  if (!questionCategories.value || questionCategories.value.length === 0) {
    return []
  }

  return questionCategories.value
})

const sortedQuestionTopics = computed(() => {
  if (!questionTopics.value || questionTopics.value.length === 0) {
    return []
  }

  return questionTopics.value
})

const sortedQuestionFactors = computed(() => {
  if (!questionFactors.value || questionFactors.value.length === 0) {
    return []
  }

  return questionFactors.value
})

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
  // 如果使用者停用主題層級且已有主題存在，顯示確認對話框
  if (!enabled && questionTopics.value?.length > 0) {
    // 可在此處加入確認對話框
  }

  // 立即更新 UI
  enableTopicLayer.value = enabled

  // 儲存設定到 localStorage 以供內容頁面存取
  if (process.client && managingQuestion.value?.id) {
    try {
      const settingKey = `question_management_${managingQuestion.value.id}_topic_layer`
      localStorage.setItem(settingKey, JSON.stringify(enabled))
    } catch (error) {
      // 靜默處理錯誤
    }
  }

  // 可觸發 API 呼叫將此設定儲存到評估記錄
  // saveTopicLayerSetting(managingQuestion.value?.id, enabled)
}

// 從 localStorage 載入主題層級設定
const loadTopicLayerSetting = (questionId) => {
  if (process.client && questionId) {
    try {
      const settingKey = `question_management_${questionId}_topic_layer`
      const stored = localStorage.getItem(settingKey)
      if (stored !== null) {
        enableTopicLayer.value = JSON.parse(stored)
      }
    } catch (error) {
      // 靜默處理錯誤
    }
  }
}

// 移除舊的架構資料定義，現在使用 composable 管理

// DataTable columns for question structure management
const questionCategoryColumns = [
  {
    key: 'actions',
    label: '功能',
    sortable: false,
    cellClass: 'text-base text-gray-900 dark:text-white'
  },
  {
    key: 'category_name',
    label: '類別名稱',
    sortable: true,
    cellClass: 'text-base font-medium text-gray-900 dark:text-white cursor-help',
    formatter: (value) => {
      if (!value) return '-'
      return value.length > 6 ? value.substring(0, 6) + '...' : value
    },
    tooltip: (row) => row.category_name || ''
  },
  {
    key: 'description',
    label: '描述',
    sortable: false,
    cellClass: 'text-base text-gray-500 dark:text-gray-400 cursor-help',
    formatter: (value) => {
      if (!value) return '-'
      const text = value.replace(/<[^>]*>/g, '') // Strip HTML tags for plain text
      return text.length > 10 ? text.substring(0, 10) + '...' : text
    },
    tooltip: (row) => {
      // Strip HTML for tooltip to show plain text
      const text = (row.description || '').replace(/<[^>]*>/g, '')
      return text
    }
  },
  {
    key: 'created_at',
    label: '建立日期',
    sortable: true,
    cellClass: 'text-base text-gray-500 dark:text-gray-400',
    formatter: (value) => formatDate(value)
  }
]

const questionTopicColumns = [
  {
    key: 'actions',
    label: '功能',
    sortable: false,
    cellClass: 'text-base text-gray-900 dark:text-white'
  },
  {
    key: 'topic_name',
    label: '主題名稱',
    sortable: true,
    cellClass: 'text-base font-medium text-gray-900 dark:text-white cursor-help',
    formatter: (value) => {
      if (!value) return '-'
      return value.length > 6 ? value.substring(0, 6) + '...' : value
    },
    tooltip: (row) => row.topic_name || ''
  },
  {
    key: 'category_name',
    label: '所屬類別',
    sortable: true,
    cellClass: 'text-base text-gray-500 dark:text-gray-400'
  },
  {
    key: 'description',
    label: '描述',
    sortable: false,
    cellClass: 'text-base text-gray-500 dark:text-gray-400 cursor-help',
    formatter: (value) => {
      if (!value) return '-'
      const text = value.replace(/<[^>]*>/g, '') // Strip HTML tags for plain text
      return text.length > 10 ? text.substring(0, 10) + '...' : text
    },
    tooltip: (row) => {
      // Strip HTML for tooltip to show plain text
      const text = (row.description || '').replace(/<[^>]*>/g, '')
      return text
    }
  },
  {
    key: 'created_at',
    label: '建立日期',
    sortable: true,
    cellClass: 'text-base text-gray-500 dark:text-gray-400',
    formatter: (value) => formatDate(value)
  }
]

const questionFactorColumns = [
  {
    key: 'actions',
    label: '功能',
    sortable: false,
    cellClass: 'text-base text-gray-900 dark:text-white'
  },
  {
    key: 'sort_order',
    label: '排序',
    sortable: true,
    cellClass: 'text-base text-center text-gray-900 dark:text-white',
    formatter: (value) => value || '-'
  },
  {
    key: 'factor_name',
    label: '因子名稱',
    sortable: true,
    cellClass: 'text-base font-medium text-gray-900 dark:text-white cursor-help',
    formatter: (value) => {
      if (!value) return '-'
      return value.length > 6 ? value.substring(0, 6) + '...' : value
    },
    tooltip: (row) => row.factor_name || ''
  },
  {
    key: 'category_name',
    label: '所屬類別',
    sortable: true,
    cellClass: 'text-base text-gray-500 dark:text-gray-400 cursor-help',
    formatter: (value) => {
      if (!value) return '-'
      return value.length > 6 ? value.substring(0, 6) + '...' : value
    },
    tooltip: (row) => row.category_name || ''
  },
  {
    key: 'topic_name',
    label: '所屬主題',
    sortable: true,
    cellClass: 'text-base text-gray-500 dark:text-gray-400 cursor-help',
    formatter: (value) => {
      if (!value) return '-'
      return value.length > 6 ? value.substring(0, 6) + '...' : value
    },
    tooltip: (row) => row.topic_name || ''
  },
  {
    key: 'description',
    label: '描述',
    sortable: false,
    cellClass: 'text-base text-gray-500 dark:text-gray-400 cursor-help',
    isHtml: true,
    formatter: (value) => {
      if (!value) return '-'
      // For HTML content, strip tags for length check but preserve HTML for display
      const textOnly = value.replace(/<[^>]*>/g, '')
      if (textOnly.length <= 10) return value

      // Find a good truncation point that doesn't break HTML tags
      let truncated = ''
      let textLength = 0
      const htmlRegex = /<[^>]*>|[^<]+/g
      let match

      while ((match = htmlRegex.exec(value)) !== null && textLength < 10) {
        const piece = match[0]
        if (piece.startsWith('<')) {
          truncated += piece // Keep HTML tags intact
        } else {
          const remaining = 10 - textLength
          if (piece.length <= remaining) {
            truncated += piece
            textLength += piece.length
          } else {
            truncated += piece.substring(0, remaining) + '...'
            textLength = 10
          }
        }
      }
      return truncated
    },
    tooltip: (row) => {
      // Strip HTML for tooltip to show plain text
      const text = (row.description || '').replace(/<[^>]*>/g, '')
      return text
    }
  },
  {
    key: 'created_at',
    label: '建立日期',
    sortable: true,
    cellClass: 'text-base text-gray-500 dark:text-gray-400',
    formatter: (value) => formatDate(value)
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

// Handle factor drag and drop reorder
const handleQuestionFactorDragEnd = async ({ draggedItem, targetItem }) => {
  if (!managingQuestion.value?.id) return

  try {
    const factors = [...sortedQuestionFactors.value]
    const draggedIndex = factors.findIndex(f => f.id === draggedItem.id)
    const targetIndex = factors.findIndex(f => f.id === targetItem.id)

    if (draggedIndex === -1 || targetIndex === -1) return

    // Reorder array
    const [removed] = factors.splice(draggedIndex, 1)
    factors.splice(targetIndex, 0, removed)

    // Format orders for API
    const orders = factors.map((factor, index) => ({
      id: factor.id,
      sort_order: index + 1
    }))

    await reorderFactors(managingQuestion.value.id, orders)
    await loadQuestionStructureData(managingQuestion.value.id)
  } catch (error) {
    await showError('排序失敗', '更新風險因子排序時發生錯誤，請稍後再試')
  }
}

// 儲存架構項目
const saveQuestionCategory = async () => {
  if (!managingQuestion.value?.id) {
    return
  }

  const data = { ...editingStructureItem.value }
  const isEditing = !!data.id

  // Check for duplicate data
  const isDuplicate = questionCategories.value?.some(category => {
    // Skip the current editing item when checking for duplicates
    if (isEditing && category.id === data.id) {
      return false
    }

    // Check if category_name and description are exactly the same
    return category.category_name.trim() === data.category_name.trim() &&
           (category.description || '').trim() === (data.description || '').trim()
  })

  if (isDuplicate) {
    await showError(
      '資料重複',
      '已存在完全相同的風險類別資料（類別名稱和描述皆相同），請修改後再試'
    )
    return
  }

  try {
    if (data.id) {
      // Update existing category
      await updateCategory(data.id, data)
    } else {
      // Create new category
      await createCategory(managingQuestion.value.id, data)
    }

    // Reload data
    await loadQuestionStructureData(managingQuestion.value.id)

    // 關閉 Modal
    showEditCategoryModal.value = false

    // 顯示成功通知
    await showSuccess(
      isEditing ? '更新成功' : '新增成功',
      `風險分類「${data.category_name}」已成功${isEditing ? '更新' : '建立'}`
    )
  } catch (error) {
    // 顯示錯誤通知
    await showError(
      isEditing ? '更新失敗' : '新增失敗',
      error.message || '操作時發生錯誤，請稍後再試'
    )
  }
}

const saveQuestionTopic = async () => {
  if (!managingQuestion.value?.id) {
    return
  }

  const data = { ...editingStructureItem.value }
  const isEditing = !!data.id

  // Check for duplicate data
  const isDuplicate = questionTopics.value?.some(topic => {
    // Skip the current editing item when checking for duplicates
    if (isEditing && topic.id === data.id) {
      return false
    }

    // Check if topic_name, category_id, and description are exactly the same
    return topic.topic_name.trim() === data.topic_name.trim() &&
           String(topic.category_id) === String(data.category_id) &&
           (topic.description || '').trim() === (data.description || '').trim()
  })

  if (isDuplicate) {
    await showError(
      '資料重複',
      '已存在完全相同的風險主題資料（主題名稱、所屬類別和描述皆相同），請修改後再試'
    )
    return
  }

  try {
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

    // 顯示成功通知
    await showSuccess(
      isEditing ? '更新成功' : '新增成功',
      `風險主題「${data.topic_name}」已成功${isEditing ? '更新' : '建立'}`
    )
  } catch (error) {
    // 顯示錯誤通知
    await showError(
      isEditing ? '更新失敗' : '新增失敗',
      error.message || '操作時發生錯誤，請稍後再試'
    )
  }
}

const saveQuestionFactor = async () => {
  if (!managingQuestion.value?.id) {
    return
  }

  const data = { ...editingStructureItem.value }
  const isEditing = !!data.id

  // 檢查重複資料
  const isDuplicate = questionFactors.value?.some(factor => {
    // 檢查重複時跳過目前編輯項目
    if (isEditing && factor.id === data.id) {
      return false
    }

    // 檢查因子名稱、類別 ID、主題 ID 和描述是否完全相同
    return factor.factor_name.trim() === data.factor_name.trim() &&
           String(factor.category_id) === String(data.category_id) &&
           String(factor.topic_id || '') === String(data.topic_id || '') &&
           (factor.description || '').trim() === (data.description || '').trim()
  })

  if (isDuplicate) {
    await showError(
      '資料重複',
      '已存在完全相同的風險因子資料（因子名稱、所屬類別、所屬主題和描述皆相同），請修改後再試'
    )
    return
  }

  try {
    if (data.id) {
      // 更新現有因子
      await updateFactor(data.id, data)
    } else {
      // 建立新因子
      await createFactor(managingQuestion.value.id, data)
    }

    // 重新載入資料
    await loadQuestionStructureData(managingQuestion.value.id)

    // 關閉 Modal
    showEditFactorModal.value = false

    // 顯示成功通知
    await showSuccess(
      isEditing ? '更新成功' : '新增成功',
      `風險因子「${data.factor_name}」已成功${isEditing ? '更新' : '建立'}`
    )
  } catch (error) {
    // 顯示錯誤通知
    await showError(
      isEditing ? '更新失敗' : '新增失敗',
      error.message || '操作時發生錯誤，請稍後再試'
    )
  }
}

// 刪除確認訊息
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

// 確認刪除
const confirmDeleteStructureItem = async () => {
  if (!deletingStructureItem.value || !managingQuestion.value?.id) {
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

    // 顯示成功通知
    const typeNames = {
      'category': '風險分類',
      'topic': '風險主題',
      'factor': '風險因子'
    }

    await showSuccess(
      '刪除成功',
      `${typeNames[structureEditType.value]}「${itemName}」已成功刪除`
    )
  } catch (error) {
    // 顯示錯誤通知（包含特定 API 錯誤訊息）
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

// 表單 Modal 顯示狀態的 computed 屬性
const showFormModal = computed(() => showAddModal.value || showEditModal.value)

// 指派相關資料
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

// 欄位日期格式化函數
const formatDate = (date) => {
  if (!date) return '-'
  const dateObj = typeof date === 'string' ? new Date(date) : date
  return new Intl.DateTimeFormat('zh-TW', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  }).format(dateObj)
}

// 題項管理 composable
const {
  getQuestionManagementByCompany,
  addQuestionManagementItem,
  updateQuestionManagementItem,
  deleteQuestionManagementItem,
  copyQuestionManagementItem,
  initializeCompanyQuestionManagement,
  refreshCompanyAssessments
} = useQuestionManagement()

// 題項管理項目的響應式資料
const questionManagement = ref([])
const isLoadingQuestionManagement = ref(false)

// 載入題項管理資料
const loadQuestionManagementData = async () => {
  isLoadingQuestionManagement.value = true
  try {
    // 使用 refreshCompanyAssessments 強制從後端重新載入資料
    await refreshCompanyAssessments(companyId.value)
    const data = await getQuestionManagementByCompany(companyId.value)
    questionManagement.value = data || []
  } catch (error) {
    questionManagement.value = []
  } finally {
    isLoadingQuestionManagement.value = false
  }
}

// 初始化此公司的題項管理（確保新公司有空陣列）
onMounted(async () => {
  try {
    // 先載入公司名稱
    await loadCompanyName()

    // 載入公司的人員資料
    if (companyId.value) {
      try {
        await loadPersonnel(companyId.value)
      } catch (error) {
        // 靜默處理錯誤
      }
    }

    await initializeCompanyQuestionManagement(companyId.value)

    // 載入題項管理資料
    await loadQuestionManagementData()

    // 初始化 templates store 以從資料庫取得範本
    try {
      await templatesStore.initialize()
    } catch (error) {
      // 靜默處理錯誤
    }
  } catch (error) {
    // 設定安全的預設值以防止渲染錯誤
    questionManagement.value = []
    isLoadingQuestionManagement.value = false
  }
})

// 監聽題項管理資料變更（已停用以避免效能問題）
// watch(questionManagement, (newData, oldData) => {
//   // 資料變更處理邏輯
// }, { deep: true, immediate: true })

// 監聽公司 ID 變更（路由參數變更時）
watch(companyId, async (newId, oldId) => {
  if (newId !== oldId && newId) {
    await loadCompanyName()

    // 載入新公司的人員資料
    try {
      await loadPersonnel(newId)
    } catch (error) {
      // 靜默處理錯誤
    }
  }
})

// 監聽公司名稱變更（已停用以避免不必要的日誌記錄）
// watch(companyName, (newName, oldName) => {
//   // 名稱變更處理邏輯
// }, { immediate: true })

// 表單 Modal 開啟時自動聚焦至範本選擇欄位
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

// 注意：此函數目前未使用已註解
// 保留以供未來可能使用
/*
const copyTemplateContentToQuestionManagement = (templateId, questionId) => {
  // 從 templates store 取得範本內容
  const templateContent = templatesStore?.getTemplateContent?.(templateId)
  const riskCategories = templatesStore?.getRiskCategories?.(templateId)

  // 為此題項管理項目的內容建立唯一鍵值
  const contentKey = `question_${companyId.value}_${questionId}`

  // 複製範本內容至題項管理上下文
  if (templateContent.value && templateContent.value.length > 0) {
    // 首先複製分類以建立對應關係
    const copiedCategories = riskCategories.value ? riskCategories.value.map((cat, index) => ({
      ...cat,
      id: `${contentKey}_cat_${index + 1}`,
      originalTemplateId: templateId
    })) : []

    // 建立舊分類 ID 到新分類 ID 的對應表
    const categoryIdMap = {}
    if (riskCategories.value) {
      riskCategories.value.forEach((originalCat, index) => {
        const newCategoryId = `${contentKey}_cat_${index + 1}`
        categoryIdMap[originalCat.id] = newCategoryId
      })
    }

    // 複製內容並對應 categoryId 參照
    const copiedContent = templateContent.value.map((item, index) => ({
      ...item,
      id: `${contentKey}_${index + 1}`,
      categoryId: item.categoryId ? categoryIdMap[item.categoryId] : item.categoryId,
      order: index + 1,
      originalTemplateId: templateId
    }))

    // 儲存至 localStorage 以實現資料持久化（僅在客戶端執行）
    if (process.client) {
      const questionContentKey = `esg-question-content-${contentKey}`
      const questionCategoriesKey = `esg-question-categories-${contentKey}`

      try {
        localStorage.setItem(questionContentKey, JSON.stringify(copiedContent))
        localStorage.setItem(questionCategoriesKey, JSON.stringify(copiedCategories))
      } catch (error) {
        // 靜默處理儲存錯誤
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
      await loadQuestionManagementData() // 刪除後重新載入資料
      await showSuccess('題項管理已成功刪除')
    } catch (error) {
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
      await loadQuestionManagementData() // 複製後重新載入資料
    } catch (error) {
      // 靜默處理錯誤
    }
  }

  showCopyOptions.value = false
  itemToCopy.value = null
  copyOptions.value.includeQuestions = true
  copyOptions.value.includeResults = false
}

const submitForm = async () => {
  const { $notify } = useNuxtApp()
  const template = availableTemplates.value.find(t => t.id === parseInt(formData.value.templateId))

  // 顯示載入動畫
  $notify.loading('處理中...請稍候')

  try {
    if (showAddModal.value) {
      // 新增項目
      const itemData = {
        templateId: parseInt(formData.value.templateId),
        templateVersion: template ? template.version_name : '',
        year: parseInt(formData.value.year)
      }

      const newItem = await addQuestionManagementItem(companyId.value, itemData)

      // 建立新題項管理項目時複製範本內容
      if (template && newItem) {
        try {
          // 呼叫後端 API 從範本複製架構到題項管理
          await $fetch(`/api/v1/question-management/assessment/${newItem.id}/sync-from-template`, {
            method: 'POST'
          })
        } catch (error) {
          // 靜默處理同步錯誤
        }
      }

      // 新增後重新載入資料
      await loadQuestionManagementData()

      // 關閉載入動畫
      $notify.close()
      await showSuccess('題項管理已成功新增')
    } else if (showEditModal.value) {
      // 更新現有項目
      const itemData = {
        templateId: parseInt(formData.value.templateId),
        templateVersion: template ? template.version_name : '',
        year: parseInt(formData.value.year)
      }

      // 更新 assessment - 後端會自動檢測範本變更並同步
      await updateQuestionManagementItem(companyId.value, editingItem.value.id, itemData)

      // 更新後重新載入資料
      await loadQuestionManagementData()

      // 關閉載入動畫
      $notify.close()
      await showSuccess('題項管理已成功更新')
    }
  } catch (error) {
    // 關閉載入動畫
    $notify.close()
    await showError(error?.message || '操作失敗，請稍後再試')
  }

  closeModals()
}

const closeModals = () => {
  showAddModal.value = false
  showEditModal.value = false
  editingItem.value = null
  formData.value.templateId = ''
  formData.value.year = new Date().getFullYear()
}

// 動作按鈕方法
const showPersonnelAssignment = async (item) => {
  selectedQuestionForAssignment.value = item

  // 確保架構資料已載入
  if (item.id && (!questionCategories.value?.length || !questionTopics.value?.length || !questionFactors.value?.length)) {
    await loadQuestionStructureData(item.id)
  }

  // 載入選定項目的題項內容
  await loadQuestionContentForAssignment(item)

  showPersonnelModal.value = true
}

// 載入人員指派的題項內容
const loadQuestionContentForAssignment = async (questionItem) => {
  try {
    // 先重置內容
    questionContentForAssignment.value = []

    if (!questionItem || !questionItem.id) {
      return
    }

    // 使用 API 從資料庫取得題項內容，不使用 localStorage
    try {
      const response = await $fetch(`/api/v1/question-management/assessment/${questionItem.id}/contents`)

      if (response.success && Array.isArray(response.data)) {
        // 將 API 回應轉換為相容格式
        const transformedContent = response.data.map(item => ({
          contentId: item.id, // 使用資料庫 ID（數值型）
          id: item.id, // 向下相容
          topic: item.title || '未命名題目',
          description: item.description || '',
          categoryId: item.category_id,
          category_id: item.category_id,
          topicId: item.topic_id,
          topic_id: item.topic_id,
          factorId: item.factor_id,
          factor_id: item.factor_id,
          risk_factor_id: item.factor_id, // 替代欄位名稱
          assignmentCount: 0 // 將由 assignment API 更新
        }))

        // 直接使用後端回傳的資料順序，不再進行前端排序
        questionContentForAssignment.value = transformedContent
      } else {
        questionContentForAssignment.value = []
      }
    } catch (apiError) {
      // Fallback 至 localStorage 以保持向下相容
      const contentKey = `question_${companyId.value}_${questionItem.id}`
      const questionContentKey = `esg-question-content-${contentKey}`

      if (process.client) {
        const storedContent = localStorage.getItem(questionContentKey)
        if (storedContent) {
          const parsedContent = JSON.parse(storedContent)
          questionContentForAssignment.value = Array.isArray(parsedContent) ? parsedContent : []
        }
      }
    }
  } catch (error) {
    questionContentForAssignment.value = []
  }
}

// 處理人員指派更新
const handleAssignmentUpdated = () => {
  // 人員指派已更新，如有需要可在此處加入額外邏輯
}

const viewAssignments = (item) => {
  // 導航到新的指派狀況頁面
  navigateTo(`/admin/risk-assessment/questions/${item.id}/assignments`)
}

const showStatistics = (item) => {
  // 導航到統計結果頁面
  navigateTo(`/admin/risk-assessment/questions/${item.id}/statistics`)
}

const showCopyOverallForm = (item) => {
  // TODO: 實作複製整體表單功能
}

const manageQuestionContent = async (item) => {
  // 使用階層式路由導航至題項內容管理頁面
  try {
    await navigateTo(`/admin/risk-assessment/questions/${companyId.value}/management/${item.id}/content`)
  } catch (error) {
    // 靜默處理導航錯誤
  }
}

// 題項架構管理函數
const manageQuestionStructure = async (item) => {
  managingQuestion.value = item

  // 載入此題項的主題層級設定
  loadTopicLayerSetting(item.id)

  // 載入題項架構資料（分類、主題、因子）
  if (item.id) {
    try {
      await loadQuestionStructureData(item.id)
    } catch (error) {
      await showError('載入失敗', '無法載入架構資料，請稍後再試')
      return
    }
  }

  showQuestionStructureModal.value = true
}

// 注意：此函數目前未使用已註解
/*
const getTemplateVersionName = (templateId) => {
  if (!templateId) return '未知範本'
  const template = availableTemplates.value.find(t => t.id === templateId)
  return template ? template.version_name : `範本 #${templateId}`
}
*/

const openQuestionManagementModal = async (type) => {
  if (!managingQuestion.value) {
    await showError('錯誤', '請先選擇一個題項管理記錄')
    return
  }

  // 載入此題項的主題層級設定
  loadTopicLayerSetting(managingQuestion.value.id)

  // 使用評估記錄 ID 載入題項架構資料（獨立於範本）
  if (managingQuestion.value.id) {
    try {
      await loadQuestionStructureData(managingQuestion.value.id)
    } catch (error) {
      await showError('載入失敗', '無法載入架構資料，請稍後再試')
      return
    }
  }

  // 顯示對應的 Modal
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
  }
}

// 載入題項架構資料（獨立於範本）
const loadQuestionStructureData = async (assessmentId) => {
  if (!assessmentId) {
    return
  }

  try {
    // 載入分類、主題、因子資料
    await Promise.allSettled([
      getCategories(assessmentId),
      getTopics(assessmentId),
      getFactors(assessmentId)
    ])
  } catch (error) {
    // 如果載入失敗，可能是還沒有架構資料，這是正常的

    // 確保資料永遠是陣列型態
    if (!questionCategories.value) questionCategories.value = []
    if (!questionTopics.value) questionTopics.value = []
    if (!questionFactors.value) questionFactors.value = []
  }
}

// 重新整理架構資料，包含錯誤處理
const refreshStructureData = async () => {
  if (!managingQuestion.value?.id) {
    return
  }

  try {
    await loadQuestionStructureData(managingQuestion.value.id)
    await showSuccess('重新整理成功', '架構資料已更新')
  } catch (error) {
    await showError('重新整理失敗', '無法載入架構資料，請稍後再試')
  }
}

const syncFromTemplate = async () => {
  if (!managingQuestion.value?.id) {
    await showError('同步失敗', '找不到評估項目，請重新整理頁面後再試')
    return
  }

  try {
    // 使用新的 API 從範本同步架構
    const result = await syncStructureFromTemplate(managingQuestion.value.id)

    // 重新載入架構資料
    await loadQuestionStructureData(managingQuestion.value.id)

    // 顯示成功訊息
    const syncedCounts = result?.data || {}
    const message = `已同步 ${syncedCounts.categories || 0} 個類別、${syncedCounts.topics || 0} 個主題、${syncedCounts.factors || 0} 個因子`
    await showSuccess('從範本同步成功', message)
  } catch (error) {
    await showError('同步失敗', error?.message || '從範本同步架構時發生錯誤，請稍後再試')
  }
}

const goToQuestionContent = (questionId) => {
  if (questionId) {
    showQuestionStructureModal.value = false
    navigateTo(`/admin/risk-assessment/questions/${companyId.value}/management/${questionId}/content`)
  }
}

// HTML 輔助函數用於描述顯示

/**
 * 從字串中移除 HTML 標籤以取得純文字
 * @param {string} html - HTML 字串
 * @returns {string} 不含 HTML 標籤的純文字
 */
const stripHtml = (html) => {
  if (!html) return ''
  const tmp = document.createElement('div')
  tmp.innerHTML = html
  return tmp.textContent || tmp.innerText || ''
}

/**
 * 將 HTML 內容截斷至指定字元數
 * @param {string} html - HTML 字串
 * @param {number} maxLength - 最大字元長度
 * @returns {string} 截斷後的 HTML，如有需要會加上省略符號
 */
const truncateHtml = (html, maxLength = 20) => {
  if (!html) return ''

  const plainText = stripHtml(html)

  // 如果文字短於最大長度，返回原始 HTML
  if (plainText.length <= maxLength) {
    return html
  }

  // 截斷純文字並加上省略符號
  const truncated = plainText.substring(0, maxLength) + '...'

  // 如果原始內容有標籤，返回包裹在相同 HTML 結構中的截斷文字
  if (html.includes('<')) {
    return `<span>${truncated}</span>`
  }

  return truncated
}

// 匯入/匯出函數

const showLoading = (message) => {
  // 簡單的載入實作 - 可以使用更完整的載入元件來增強
}

const closeAll = () => {
  // 關閉所有 Modal - 用於匯出期間
}

const exportStructure = async () => {
  if (!managingQuestion.value) return

  try {
    showLoading('正在匯出架構資料...')

    const assessmentId = managingQuestion.value.id
    const hasTopicLayer = enableTopicLayer.value

    // 取得所有架構資料
    await loadQuestionStructureData(assessmentId)

    const categories = questionCategories.value || []
    const topics = hasTopicLayer ? (questionTopics.value || []) : []
    const factors = questionFactors.value || []

    // 準備 Excel 資料
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

    // 建立 workbook
    const ws = XLSX.utils.json_to_sheet(data)
    const wb = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(wb, ws, '架構資料')

    // 設定標題列樣式
    const range = XLSX.utils.decode_range(ws['!ref'])
    for (let C = range.s.c; C <= range.e.c; ++C) {
      const address = XLSX.utils.encode_col(C) + '1'
      if (!ws[address]) continue
      ws[address].s = {
        fill: { fgColor: { rgb: '4F46E5' } },
        font: { bold: true, color: { rgb: 'FFFFFF' } }
      }
    }

    // 設定欄位寬度
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

    // 產生檔案名稱
    const timestamp = new Date().toISOString().slice(0, 10)
    const filename = `${managingQuestion.value.templateVersion}_${managingQuestion.value.year}年_架構_${timestamp}.xlsx`

    // 下載檔案
    XLSX.writeFile(wb, filename)

    closeAll()
    await showSuccess('匯出成功', `架構資料已匯出為 ${filename}`)
  } catch (error) {
    closeAll()
    await showError('匯出失敗', '匯出架構資料時發生錯誤')
  }
}

const downloadTemplate = () => {
  if (!managingQuestion.value) return

  const hasTopicLayer = enableTopicLayer.value

  // 建立僅含標題的範本資料
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

  // 建立空白範本 workbook
  const ws = XLSX.utils.json_to_sheet([headers])
  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, '架構資料')

  // 設定標題列樣式
  const range = XLSX.utils.decode_range(ws['!ref'])
  for (let C = range.s.c; C <= range.e.c; ++C) {
    const address = XLSX.utils.encode_col(C) + '1'
    if (!ws[address]) continue
    ws[address].s = {
      fill: { fgColor: { rgb: '4F46E5' } },
      font: { bold: true, color: { rgb: 'FFFFFF' } }
    }
  }

  // 設定欄位寬度
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

  // 產生檔案名稱
  const timestamp = new Date().toISOString().slice(0, 10)
  const filename = `${managingQuestion.value.templateVersion}_${managingQuestion.value.year}年_匯入範本_${timestamp}.xlsx`

  // 下載檔案
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

    // 建立 FormData 用於檔案上傳
    const formData = new FormData()
    formData.append('file', selectedFile.value)

    // 上傳檔案至後端處理，支援 RichText 格式
    const assessmentId = managingQuestion.value.id
    const response = await $fetch(`/api/v1/question-management/assessment/${assessmentId}/import-structure`, {
      method: 'POST',
      body: formData
    })

    if (response.success) {
      // 重新整理資料
      await refreshStructureData()

      closeAll()
      closeStructureImportModal()

      // 顯示包含匯入詳情的成功訊息
      let message = response.message || '架構匯入成功'
      if (response.data?.errors && response.data.errors.length > 0) {
        message += `\n\n部分資料匯入失敗：\n${response.data.errors.join('\n')}`
      }

      await showSuccess('匯入完成', message)
    } else {
      throw new Error(response.message || '匯入失敗')
    }

  } catch (error) {
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