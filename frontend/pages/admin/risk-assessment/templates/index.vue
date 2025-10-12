<template>
  <div class="p-6">
    <!-- Page Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">範本管理</h1>
      <p class="text-gray-600 dark:text-gray-400">管理風險評估範本版本</p>
    </div>

    <!-- Data Table -->
    <DataTable
      :data="templates"
      :columns="columns"
      search-placeholder="搜尋範本名稱..."
      :search-fields="['version_name']"
      empty-title="還沒有範本"
      empty-message="開始建立您的第一個風險評估範本"
      no-search-results-title="沒有找到符合的範本"
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
            新增範本
          </button>

          <!-- Refresh Button -->
          <button
            @click="refreshTemplates"
            :disabled="templatesStore.isFetchingTemplates"
            class="flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
          >
            <ArrowPathIcon
              class="w-4 h-4 mr-2"
              :class="{ 'animate-spin': templatesStore.isFetchingTemplates }"
            />
            重新整理
          </button>
        </div>
      </template>

      <!-- Custom Actions Cell -->
      <template #cell-actions="{ item }">
        <div class="flex flex-wrap items-center gap-2">
          <!-- Edit Button -->
          <div class="relative group">
            <button
              @click="editTemplate(item)"
              class="p-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors duration-200"
            >
              <PencilIcon class="w-4 h-4" />
            </button>
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
              編輯
              <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
            </div>
          </div>

          <!-- Copy Button -->
          <div class="relative group">
            <button
              @click="copyTemplate(item)"
              class="p-2 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors duration-200"
            >
              <DocumentDuplicateIcon class="w-4 h-4" />
            </button>
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
              複製
              <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
            </div>
          </div>

          <!-- Delete Button -->
          <div class="relative group">
            <button
              @click="deleteTemplate(item)"
              class="p-2 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors duration-200"
            >
              <TrashIcon class="w-4 h-4" />
            </button>
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
              刪除
              <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
            </div>
          </div>

          <!-- Template Management Button -->
          <div class="relative group">
            <button
              @click="manageTemplateStructure(item)"
              class="p-2 text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors duration-200"
            >
              <Cog6ToothIcon class="w-4 h-4" />
            </button>
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
              架構管理
              <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
            </div>
          </div>

          <!-- Template Content Button -->
          <div class="relative group">
            <button
              @click="viewTemplateContent(item)"
              class="p-2 text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-lg transition-colors duration-200"
            >
              <DocumentTextIcon class="w-4 h-4" />
            </button>
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
              範本內容
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
          新增範本
        </button>
      </template>
    </DataTable>

    <!-- Add/Edit Modal -->
    <Modal
      :model-value="showAddModal || showEditModal"
      :title="showAddModal ? '新增範本' : '編輯範本'"
      size="md"
      @update:model-value="handleModalClose"
      @close="closeModals"
    >
      <form @submit.prevent="submitForm">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            版本名稱
          </label>
          <input
            ref="versionNameInput"
            v-model="formData.versionName"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
            placeholder="請輸入版本名稱"
          />
        </div>

        <div class="flex justify-end space-x-3 mt-6">
          <button
            type="button"
            @click="closeModals"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors duration-200"
          >
            取消
          </button>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
          >
            {{ isSubmitting ? '處理中...' : (showAddModal ? '新增' : '更新') }}
          </button>
        </div>
      </form>
    </Modal>

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal
      :model-value="showDeleteModal"
      title="確認刪除"
      :message="`確定要刪除範本「${templateToDelete?.version_name}」嗎？`"
      details="此操作無法復原。"
      type="danger"
      cancel-text="取消"
      confirm-text="刪除"
      @update:model-value="(value) => showDeleteModal = value"
      @close="showDeleteModal = false"
      @confirm="confirmDelete"
    />

    <!-- Copy Template Modal -->
    <Modal
      :model-value="showCopyModal"
      title="複製範本"
      size="md"
      :show-default-footer="true"
      cancel-text="取消"
      confirm-text="確認複製"
      @update:model-value="(value) => showCopyModal = value"
      @close="closeCopyModal"
      @confirm="confirmCopy"
    >
      <div class="space-y-4">
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
          <p class="text-sm text-blue-800 dark:text-blue-200">
            預設名稱：<span class="font-medium">{{ copyDefaultName }}</span>
          </p>
          <p class="text-sm text-blue-600 dark:text-blue-300 mt-1">
            如要使用預設名稱，請直接點擊「確認複製」
          </p>
        </div>

        <div>
          <label for="copy-version-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            自訂版本名稱（選填）
          </label>
          <input
            id="copy-version-name"
            v-model="copyVersionName"
            type="text"
            :placeholder="copyDefaultName"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
          />
        </div>
      </div>
    </Modal>

    <!-- Template Structure Management Modal -->
    <StructureManagementModal
      v-model="showTemplateManagementModal"
      :title="`架構管理 - ${managingTemplate?.version_name}`"
      :item-name="managingTemplate?.version_name"
      :risk-topics-enabled="!!managingTemplate?.risk_topics_enabled"
      :show-export-import="true"
      management-type="template"
      @close="showTemplateManagementModal = false"
      @toggle-risk-topics="() => toggleTemplateRiskTopics(managingTemplate?.id)"
      @open-category-management="openManagementModal('categories')"
      @open-topic-management="openManagementModal('topics')"
      @open-factor-management="openManagementModal('factors')"
      @go-to-content="goToTemplateContent(managingTemplate?.id)"
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
              <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-1">下載匯入範本</h4>
              <p class="text-xs text-blue-700 dark:text-blue-300">下載空白範本以填寫資料</p>
            </div>
            <button
              type="button"
              @click="downloadTemplate"
              class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
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
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
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
              <DocumentTextIcon class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" />
              <span class="text-sm font-medium text-blue-900 dark:text-blue-100">{{ selectedFile.name }}</span>
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
                <li v-for="(error, index) in importErrors" :key="index">
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

    <!-- Risk Category Management Modal -->
    <Modal
      :model-value="showRiskCategoryManagementModal"
      :title="`管理風險類別 - ${managingTemplate?.version_name}`"
      size="4xl"
      @update:model-value="(value) => showRiskCategoryManagementModal = value"
      @close="showRiskCategoryManagementModal = false"
    >
      <DataTable
        :data="currentTemplateRiskCategories"
        :columns="riskCategoryColumns"
        search-placeholder="搜尋風險類別..."
        :search-fields="['category_name']"
        empty-title="還沒有風險類別"
        empty-message="開始建立您的第一個風險類別"
        no-search-results-title="沒有找到符合的風險類別"
        no-search-results-message="請嘗試其他搜尋關鍵字"
        :draggable="true"
        @drag-end="handleCategoryDragEnd"
      >
        <!-- Actions Slot -->
        <template #actions>
          <button
            @click="addRiskCategory"
            class="flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200"
          >
            <PlusIcon class="w-4 h-4 mr-2" />
            新增風險類別
          </button>
        </template>

        <!-- Custom Category Name Cell with HtmlTooltip -->
        <template #cell-category_name="{ item }">
          <HtmlTooltip
            :content="item.category_name || '-'"
            :truncate-length="20"
            text-class="text-base font-medium text-gray-900 dark:text-white cursor-pointer"
          />
        </template>

        <!-- Custom Description Cell with HtmlTooltip -->
        <template #cell-description="{ item }">
          <HtmlTooltip
            :content="item.description || '-'"
            :truncate-length="10"
            text-class="text-base text-gray-500 dark:text-gray-400 cursor-pointer"
          />
        </template>

        <!-- Custom Actions Cell -->
        <template #cell-actions="{ item }">
          <div class="flex items-center space-x-2">
            <!-- Edit Button -->
            <div class="relative group">
              <button
                @click="editRiskCategory(item)"
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
                @click="deleteRiskCategory(item)"
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
            @click="addRiskCategory"
            class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200"
          >
            <PlusIcon class="w-4 h-4 mr-2" />
            新增風險類別
          </button>
        </template>
      </DataTable>
    </Modal>

    <!-- Add/Edit Risk Category Modal -->
    <Modal
      :model-value="showAddRiskCategoryModal || showEditRiskCategoryModal"
      :title="showAddRiskCategoryModal ? '新增風險類別' : '編輯風險類別'"
      size="md"
      @update:model-value="(value) => !value && closeRiskCategoryModals()"
      @close="closeRiskCategoryModals"
    >
      <form @submit.prevent="submitRiskCategoryForm">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            類別名稱 <span class="text-red-500">*</span>
          </label>
          <input
            v-model="riskCategoryFormData.category_name"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
            placeholder="請輸入類別名稱"
          />
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            描述
          </label>
          <textarea
            v-model="riskCategoryFormData.description"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
            placeholder="請輸入類別描述（可選）"
          ></textarea>
        </div>

        <div class="flex justify-end space-x-3 mt-6">
          <button
            type="button"
            @click="closeRiskCategoryModals"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors duration-200"
          >
            取消
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200"
          >
            {{ showAddRiskCategoryModal ? '新增' : '更新' }}
          </button>
        </div>
      </form>
    </Modal>

    <!-- Delete Risk Category Confirmation Modal -->
    <ConfirmationModal
      :model-value="showDeleteRiskCategoryModal"
      title="確認刪除"
      :message="`確定要刪除風險類別「${deletingRiskCategory?.category_name}」嗎？`"
      details="此操作無法復原，相關的風險因子也會一併刪除。"
      type="danger"
      cancel-text="取消"
      confirm-text="刪除"
      @update:model-value="(value) => showDeleteRiskCategoryModal = value"
      @close="showDeleteRiskCategoryModal = false"
      @confirm="confirmDeleteRiskCategory"
    />

    <!-- Risk Factor Management Modal -->
    <Modal
      :model-value="showRiskFactorManagementModal"
      :title="`管理風險因子 - ${managingTemplate?.version_name}`"
      size="4xl"
      @update:model-value="(value) => showRiskFactorManagementModal = value"
      @close="showRiskFactorManagementModal = false"
    >
      <DataTable
        :data="currentTemplateRiskFactors"
        :columns="riskFactorColumns"
        search-placeholder="搜尋風險因子..."
        :search-fields="['factor_name', 'category_name', 'topic_name']"
        empty-title="還沒有風險因子"
        empty-message="開始建立您的第一個風險因子"
        no-search-results-title="沒有找到符合的風險因子"
        no-search-results-message="請嘗試其他搜尋關鍵字"
        :draggable="true"
        @drag-end="handleFactorDragEnd"
      >
        <!-- Actions Slot -->
        <template #actions>
          <button
            @click="addRiskFactor"
            class="flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200"
          >
            <PlusIcon class="w-4 h-4 mr-2" />
            新增風險因子
          </button>
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

        <!-- Custom Description Cell with HtmlTooltip -->
        <template #cell-description="{ item }">
          <HtmlTooltip
            :content="item.description || '-'"
            :truncate-length="30"
            text-class="text-base text-gray-500 dark:text-gray-400 cursor-pointer"
            max-width="min-w-[300px] max-w-[500px]"
          />
        </template>

        <!-- Custom Actions Cell -->
        <template #cell-actions="{ item }">
          <div class="flex items-center space-x-2">
            <!-- Edit Button -->
            <div class="relative group">
              <button
                @click="editRiskFactor(item)"
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
                @click="deleteRiskFactor(item)"
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
            @click="addRiskFactor"
            class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200"
          >
            <PlusIcon class="w-4 h-4 mr-2" />
            新增風險因子
          </button>
        </template>
      </DataTable>
    </Modal>

    <!-- Add/Edit Risk Factor Modal -->
    <Modal
      :model-value="showAddRiskFactorModal || showEditRiskFactorModal"
      :title="showAddRiskFactorModal ? '新增風險因子' : '編輯風險因子'"
      size="2xl"
      @update:model-value="(value) => !value && closeRiskFactorModals()"
      @close="closeRiskFactorModals"
    >
      <form @submit.prevent="submitRiskFactorForm">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            因子名稱 <span class="text-red-500">*</span>
          </label>
          <input
            v-model="riskFactorFormData.factor_name"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
            placeholder="請輸入因子名稱"
          />
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            所屬風險類別 <span class="text-red-500">*</span>
          </label>
          <select
            v-model="riskFactorFormData.category_id"
            required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
          >
            <option value="">請選擇風險類別</option>
            <option
              v-for="category in currentTemplateRiskCategories"
              :key="category.id"
              :value="category.id"
            >
              {{ category.category_name }}
            </option>
          </select>
        </div>

        <div v-if="managingTemplate?.risk_topics_enabled" class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            所屬風險主題 <span class="text-red-500">*</span>
          </label>
          <select
            v-model="riskFactorFormData.topic_id"
            :required="managingTemplate?.risk_topics_enabled"
            :disabled="!riskFactorFormData.category_id || isLoadingTopics"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white disabled:bg-gray-100 disabled:text-gray-400 dark:disabled:bg-gray-600 dark:disabled:text-gray-500"
          >
            <option value="">
              {{ !riskFactorFormData.category_id
                ? '請先選擇風險類別'
                : (isLoadingTopics ? '載入中...' : '請選擇風險主題')
              }}
            </option>
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
            描述 <span class="text-red-500">*</span>
          </label>
          <RichTextEditor
            v-model="riskFactorFormData.description"
            placeholder="請輸入因子描述"
            :show-html-info="false"
          />
        </div>

        <div class="flex justify-end space-x-3 mt-6">
          <button
            type="button"
            @click="closeRiskFactorModals"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors duration-200"
          >
            取消
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200"
          >
            {{ showAddRiskFactorModal ? '新增' : '更新' }}
          </button>
        </div>
      </form>
    </Modal>

    <!-- Delete Risk Factor Confirmation Modal -->
    <ConfirmationModal
      :model-value="showDeleteRiskFactorModal"
      title="確認刪除"
      :message="`確定要刪除風險因子「${deletingRiskFactor?.factor_name}」嗎？`"
      details="此操作無法復原。"
      type="danger"
      cancel-text="取消"
      confirm-text="刪除"
      @update:model-value="(value) => showDeleteRiskFactorModal = value"
      @close="showDeleteRiskFactorModal = false"
      @confirm="confirmDeleteRiskFactor"
    />

    <!-- Risk Topic Management Modal -->
    <Modal
      :model-value="showRiskTopicManagementModal"
      :title="`管理風險主題 - ${managingTemplate?.version_name}`"
      size="4xl"
      @update:model-value="(value) => showRiskTopicManagementModal = value"
      @close="showRiskTopicManagementModal = false"
    >
      <DataTable
        :data="currentTemplateRiskTopics"
        :columns="riskTopicColumns"
        search-placeholder="搜尋風險主題..."
        :search-fields="['topic_name', 'category_name']"
        empty-title="還沒有風險主題"
        empty-message="開始建立您的第一個風險主題"
        no-search-results-title="沒有找到符合的風險主題"
        no-search-results-message="請嘗試其他搜尋關鍵字"
        :draggable="true"
        @drag-end="handleTopicDragEnd"
      >
        <!-- Actions Slot -->
        <template #actions>
          <button
            @click="addRiskTopic"
            class="flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200"
          >
            <PlusIcon class="w-4 h-4 mr-2" />
            新增風險主題
          </button>
        </template>

        <!-- Custom Topic Name Cell with HtmlTooltip -->
        <template #cell-topic_name="{ item }">
          <HtmlTooltip
            :content="item.topic_name || '-'"
            :truncate-length="20"
            text-class="text-base font-medium text-gray-900 dark:text-white cursor-pointer"
          />
        </template>

        <!-- Custom Category Name Cell with HtmlTooltip -->
        <template #cell-category_name="{ item }">
          <HtmlTooltip
            :content="item.category_name || '-'"
            :truncate-length="20"
            text-class="text-base text-gray-500 dark:text-gray-400 cursor-pointer"
          />
        </template>

        <!-- Custom Description Cell with HtmlTooltip -->
        <template #cell-description="{ item }">
          <HtmlTooltip
            :content="item.description || '-'"
            :truncate-length="10"
            text-class="text-base text-gray-500 dark:text-gray-400 cursor-pointer"
          />
        </template>

        <!-- Custom Actions Cell -->
        <template #cell-actions="{ item }">
          <div class="flex items-center space-x-2">
            <!-- Edit Button -->
            <div class="relative group">
              <button
                @click="editRiskTopic(item)"
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
                @click="deleteRiskTopic(item)"
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
            @click="addRiskTopic"
            class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200"
          >
            <PlusIcon class="w-4 h-4 mr-2" />
            新增風險主題
          </button>
        </template>
      </DataTable>
    </Modal>

    <!-- Add/Edit Risk Topic Modal -->
    <Modal
      :model-value="showAddRiskTopicModal || showEditRiskTopicModal"
      :title="showAddRiskTopicModal ? '新增風險主題' : '編輯風險主題'"
      size="md"
      @update:model-value="(value) => !value && closeRiskTopicModals()"
      @close="closeRiskTopicModals"
    >
      <form @submit.prevent="submitRiskTopicForm">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            主題名稱 <span class="text-red-500">*</span>
          </label>
          <input
            v-model="riskTopicFormData.topic_name"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
            placeholder="請輸入主題名稱"
          />
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            所屬風險類別 <span class="text-red-500">*</span>
          </label>
          <select
            v-model="riskTopicFormData.category_id"
            required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
          >
            <option value="">請選擇風險類別</option>
            <option
              v-for="category in currentTemplateRiskCategories"
              :key="category.id"
              :value="category.id"
            >
              {{ category.category_name }}
            </option>
          </select>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            描述
          </label>
          <textarea
            v-model="riskTopicFormData.description"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
            placeholder="請輸入主題描述（可選）"
          ></textarea>
        </div>

        <div class="flex justify-end space-x-3 mt-6">
          <button
            type="button"
            @click="closeRiskTopicModals"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors duration-200"
          >
            取消
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200"
          >
            {{ showAddRiskTopicModal ? '新增' : '更新' }}
          </button>
        </div>
      </form>
    </Modal>

    <!-- Delete Risk Topic Confirmation Modal -->
    <ConfirmationModal
      :model-value="showDeleteRiskTopicModal"
      title="確認刪除"
      :message="`確定要刪除風險主題「${deletingRiskTopic?.topic_name}」嗎？`"
      details="此操作無法復原，相關的風險因子也會受到影響。"
      type="danger"
      cancel-text="取消"
      confirm-text="刪除"
      @update:model-value="(value) => showDeleteRiskTopicModal = value"
      @close="showDeleteRiskTopicModal = false"
      @confirm="confirmDeleteRiskTopic"
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
  DocumentDuplicateIcon,
  TrashIcon,
  DocumentTextIcon,
  ArrowPathIcon,
  CheckCircleIcon,
  XCircleIcon,
  Cog6ToothIcon,
  ArrowDownIcon,
  TagIcon,
  ChatBubbleLeftRightIcon,
  ExclamationTriangleIcon,
  ArrowDownTrayIcon,
  ArrowUpTrayIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'
import * as XLSX from 'xlsx'

usePageTitle('範本管理')

// Use templates store
const templatesStore = useTemplatesStore()

// Reactive data
const showAddModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const showCopyModal = ref(false)
const templateToDelete = ref(null)
const templateToCopy = ref(null)
const copyVersionName = ref('')
const copyDefaultName = computed(() => templateToCopy.value ? `${templateToCopy.value.version_name} (副本)` : '')
const editingTemplate = ref(null)
const isSubmitting = ref(false)

// Template structure management
const showTemplateManagementModal = ref(false)
const managingTemplate = ref(null)

// Risk category management
const showRiskCategoryManagementModal = ref(false)
const showAddRiskCategoryModal = ref(false)
const showEditRiskCategoryModal = ref(false)
const showDeleteRiskCategoryModal = ref(false)
const editingRiskCategory = ref(null)
const deletingRiskCategory = ref(null)

const riskCategoryFormData = ref({
  category_name: '',
  description: ''
})

// Risk factor management
const showRiskFactorManagementModal = ref(false)
const showAddRiskFactorModal = ref(false)
const showEditRiskFactorModal = ref(false)
const showDeleteRiskFactorModal = ref(false)
const editingRiskFactor = ref(null)
const deletingRiskFactor = ref(null)

const riskFactorFormData = ref({
  factor_name: '',
  category_id: '',
  topic_id: '',
  description: ''
})

// Loading states
const isLoadingTopics = ref(false)
const isInitializingEditForm = ref(false)

// Risk topic management
const showRiskTopicManagementModal = ref(false)
const showAddRiskTopicModal = ref(false)
const showEditRiskTopicModal = ref(false)
const showDeleteRiskTopicModal = ref(false)
const editingRiskTopic = ref(null)
const deletingRiskTopic = ref(null)

const riskTopicFormData = ref({
  topic_name: '',
  category_id: '',
  description: ''
})

// Structure import/export
const showStructureImportModal = ref(false)
const selectedFile = ref(null)
const isDragging = ref(false)
const isImporting = ref(false)
const importErrors = ref([])
const fileInput = ref(null)

const formData = ref({
  versionName: ''
})

// Template refs for focus management
const versionNameInput = ref(null)

// Notification system using SweetAlert
const { showSuccess, showError, showLoading, closeAll } = useNotification()

// Get templates from store - use computed to ensure reactivity
const templates = computed(() => templatesStore.templates)

// DataTable columns configuration
const columns = ref([
  {
    key: 'actions',
    label: '功能',
    sortable: false,
    cellClass: 'text-base text-gray-900 dark:text-white'
  },
  {
    key: 'version_name',
    label: '版本名稱',
    sortable: true,
    cellClass: 'text-base font-medium text-gray-900 dark:text-white'
  },
  {
    key: 'created_at',
    label: '建立日期',
    sortable: true,
    cellClass: 'text-base text-gray-500 dark:text-gray-400',
    formatter: (value) => formatDate(value)
  }
])

// Risk Category DataTable columns configuration
const riskCategoryColumns = ref([
  {
    key: 'category_name',
    label: '類別名稱',
    sortable: true
  },
  {
    key: 'description',
    label: '描述',
    sortable: false
  },
  {
    key: 'created_at',
    label: '建立日期',
    sortable: true,
    cellClass: 'text-base text-gray-500 dark:text-gray-400',
    formatter: (value) => formatDate(value)
  }
])

// Risk Factor DataTable columns configuration
const riskFactorColumns = ref([
  {
    key: 'factor_name',
    label: '因子名稱',
    sortable: true
  },
  {
    key: 'category_name',
    label: '所屬類別',
    sortable: true
  },
  {
    key: 'topic_name',
    label: '所屬主題',
    sortable: true
  },
  {
    key: 'description',
    label: '描述',
    sortable: false
  },
  {
    key: 'created_at',
    label: '建立日期',
    sortable: true,
    cellClass: 'text-base text-gray-500 dark:text-gray-400',
    formatter: (value) => formatDate(value)
  }
])

// Risk Topic DataTable columns configuration
const riskTopicColumns = ref([
  {
    key: 'topic_name',
    label: '主題名稱',
    sortable: true
  },
  {
    key: 'category_name',
    label: '所屬類別',
    sortable: true
  },
  {
    key: 'description',
    label: '描述',
    sortable: false
  },
  {
    key: 'created_at',
    label: '建立日期',
    sortable: true,
    cellClass: 'text-base text-gray-500 dark:text-gray-400',
    formatter: (value) => formatDate(value)
  }
])

// Methods
const getTextLength = (html) => {
  if (!html) return 0
  return html.replace(/<[^>]*>/g, '').length
}

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

// Strip HTML tags from string
const stripHtml = (html) => {
  if (!html) return ''
  return html.replace(/<[^>]*>/g, '')
}

// Truncate text to specified length
const truncateText = (text, maxLength) => {
  if (!text) return '-'
  if (text.length <= maxLength) return text
  return text.substring(0, maxLength) + '...'
}

const editTemplate = (template) => {
  editingTemplate.value = template
  formData.value.versionName = template.version_name
  showEditModal.value = true
}

const copyTemplate = (template) => {
  templateToCopy.value = template
  copyVersionName.value = ''
  showCopyModal.value = true
}

const closeCopyModal = () => {
  showCopyModal.value = false
  templateToCopy.value = null
  copyVersionName.value = ''
}

const confirmCopy = async () => {
  if (!templateToCopy.value) return

  // Save template info before closing modal
  const templateId = templateToCopy.value.id
  const templateName = templateToCopy.value.version_name
  const versionName = copyVersionName.value?.trim() || copyDefaultName.value

  // Close modal
  closeCopyModal()

  // Show loading
  showLoading('正在複製範本...')

  try {
    await templatesStore.copyTemplate(templateId, versionName)
    closeAll()
    await showSuccess(`範本「${templateName}」已成功複製`)
  } catch (error) {
    closeAll()
    console.error('Copy template error:', error)
    // 使用 API 回傳的錯誤訊息
    const errorMessage = error.error?.message || error.message || '複製範本時發生錯誤，請稍後再試'
    await showError(errorMessage)
  }
}

const deleteTemplate = (template) => {
  templateToDelete.value = template
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  if (!templateToDelete.value) return

  // Save template info and close modal
  const templateId = templateToDelete.value.id
  const templateName = templateToDelete.value.version_name
  showDeleteModal.value = false
  templateToDelete.value = null

  // Show loading
  showLoading('正在刪除範本...')

  try {
    await templatesStore.deleteTemplate(templateId)
    closeAll()
    await showSuccess(`範本「${templateName}」已成功刪除`)
  } catch (error) {
    closeAll()
    console.error('Delete template error:', error)
    // 使用 API 回傳的錯誤訊息
    const errorMessage = error.error?.message || error.message || '刪除範本時發生錯誤，請稍後再試'
    await showError(errorMessage)
  }
}

const viewTemplateContent = (template) => {
  navigateTo(`/admin/risk-assessment/templates/${template.id}/content`)
}

const refreshTemplates = async () => {
  try {
    await templatesStore.fetchTemplates()
  } catch (error) {
    console.error('Refresh templates error:', error)
  }
}

const manageTemplateStructure = (template) => {
  // Convert risk_topics_enabled to boolean
  const rawValue = template.risk_topics_enabled
  let riskTopicsEnabled = false

  if (rawValue === 1 || rawValue === true || rawValue === '1' || rawValue === 'true') {
    riskTopicsEnabled = true
  } else if (rawValue === 0 || rawValue === false || rawValue === '0' || rawValue === 'false') {
    riskTopicsEnabled = false
  } else if (rawValue === null || rawValue === undefined) {
    riskTopicsEnabled = true // Default to true if not set
  }

  const templateWithDefaults = {
    ...template,
    risk_topics_enabled: riskTopicsEnabled
  }
  managingTemplate.value = templateWithDefaults
  showTemplateManagementModal.value = true
}

const toggleTemplateRiskTopics = async (templateId) => {
  try {
    // Show loading indicator
    showLoading('正在更新設定...')

    // Call store method (now async)
    await templatesStore.toggleRiskTopics(templateId)

    // Force reactivity update for the managing template
    if (managingTemplate.value && managingTemplate.value.id === templateId) {
      const template = templatesStore.getTemplateById(templateId).value
      if (template) {
        // Ensure risk_topics_enabled is boolean
        const rawValue = template.risk_topics_enabled
        let riskTopicsEnabled = false

        if (rawValue === 1 || rawValue === true || rawValue === '1' || rawValue === 'true') {
          riskTopicsEnabled = true
        } else if (rawValue === 0 || rawValue === false || rawValue === '0' || rawValue === 'false') {
          riskTopicsEnabled = false
        }

        managingTemplate.value = {
          ...template,
          risk_topics_enabled: riskTopicsEnabled
        }
      }
    }

    // Close loading and show success
    closeAll()
    await showSuccess('設定已更新', '範本風險主題設定已更新')
  } catch (error) {
    console.error('Toggle risk topics error:', error)
    closeAll()
    await showError('設定失敗', '更新風險主題設定時發生錯誤')
  }
}

const openManagementModal = async (type) => {
  switch (type) {
    case 'categories':
      // Load risk categories from backend before showing modal
      if (managingTemplate.value?.id) {
        try {
          await templatesStore.fetchRiskCategories(managingTemplate.value.id)
        } catch (error) {
          console.error('Failed to load risk categories:', error)
          await showError('載入失敗', '無法載入風險分類資料，請稍後再試')
          return
        }
      }
      showRiskCategoryManagementModal.value = true
      break
    case 'topics':
      // Load risk topics from backend before showing modal
      if (managingTemplate.value?.id) {
        try {
          await templatesStore.fetchRiskTopics(managingTemplate.value.id)
        } catch (error) {
          console.error('Failed to load risk topics:', error)
          await showError('載入失敗', '無法載入風險主題資料，請稍後再試')
          return
        }
      }
      showRiskTopicManagementModal.value = true
      break
    case 'factors':
      // Load risk factors from backend before showing modal
      if (managingTemplate.value?.id) {
        try {
          await templatesStore.fetchRiskFactors(managingTemplate.value.id)
        } catch (error) {
          console.error('Failed to load risk factors:', error)
          await showError('載入失敗', '無法載入風險因子資料，請稍後再試')
          return
        }
      }
      showRiskFactorManagementModal.value = true
      break
  }
}

const goToTemplateContent = (templateId) => {
  if (templateId) {
    showTemplateManagementModal.value = false
    navigateTo(`/admin/risk-assessment/templates/${templateId}/content`)
  }
}

// Risk Category Management Functions
const currentTemplateRiskCategories = computed(() => {
  if (!managingTemplate.value?.id) return []
  return templatesStore.getRiskCategories(managingTemplate.value.id).value || []
})

const addRiskCategory = () => {
  riskCategoryFormData.value = {
    category_name: '',
    description: ''
  }
  showAddRiskCategoryModal.value = true
}

const editRiskCategory = (category) => {
  editingRiskCategory.value = category
  riskCategoryFormData.value = {
    category_name: category.category_name,
    description: category.description || ''
  }
  showEditRiskCategoryModal.value = true
}

const deleteRiskCategory = (category) => {
  deletingRiskCategory.value = category
  showDeleteRiskCategoryModal.value = true
}

const submitRiskCategoryForm = async () => {
  if (!managingTemplate.value?.id) return

  // Check for duplicate data
  const isDuplicate = currentTemplateRiskCategories.value.some(category => {
    // Skip the current editing item when checking for duplicates
    if (editingRiskCategory.value && category.id === editingRiskCategory.value.id) {
      return false
    }

    // Check if category_name and description are exactly the same
    return category.category_name.trim() === riskCategoryFormData.value.category_name.trim() &&
           (category.description || '').trim() === (riskCategoryFormData.value.description || '').trim()
  })

  if (isDuplicate) {
    await showError(
      showAddRiskCategoryModal.value ? '新增失敗' : '更新失敗',
      '已存在完全相同的風險類別資料，請修改後再試'
    )
    return
  }

  try {
    if (showAddRiskCategoryModal.value) {
      await templatesStore.addRiskCategory(managingTemplate.value.id, riskCategoryFormData.value)
      await showSuccess('新增成功', `風險類別「${riskCategoryFormData.value.category_name}」已成功建立`)
    } else if (showEditRiskCategoryModal.value && editingRiskCategory.value) {
      await templatesStore.updateRiskCategory(
        managingTemplate.value.id,
        editingRiskCategory.value.id,
        riskCategoryFormData.value
      )
      await showSuccess('更新成功', `風險類別「${riskCategoryFormData.value.category_name}」已成功更新`)
    }

    // Refresh risk categories data after successful operation
    await templatesStore.fetchRiskCategories(managingTemplate.value.id)
    closeRiskCategoryModals()
  } catch (error) {
    console.error('風險類別操作錯誤:', error)
    await showError(showAddRiskCategoryModal.value ? '新增失敗' : '更新失敗', '操作時發生錯誤，請稍後再試')
  }
}

const confirmDeleteRiskCategory = async () => {
  if (!deletingRiskCategory.value || !managingTemplate.value?.id) return

  try {
    await templatesStore.deleteRiskCategory(managingTemplate.value.id, deletingRiskCategory.value.id)
    await showSuccess('刪除成功', `風險類別「${deletingRiskCategory.value.category_name}」已成功刪除`)

    // Refresh risk categories data after successful deletion
    await templatesStore.fetchRiskCategories(managingTemplate.value.id)
    closeRiskCategoryModals()
  } catch (error) {
    console.error('刪除風險類別錯誤:', error)
    await showError('刪除失敗', '刪除風險類別時發生錯誤，請稍後再試')
  }
}

const closeRiskCategoryModals = () => {
  showAddRiskCategoryModal.value = false
  showEditRiskCategoryModal.value = false
  showDeleteRiskCategoryModal.value = false
  editingRiskCategory.value = null
  deletingRiskCategory.value = null
  riskCategoryFormData.value = {
    category_name: '',
    description: ''
  }
}

const handleCategoryDragEnd = async ({ draggedItem, targetItem }) => {
  if (!managingTemplate.value?.id) return

  try {
    const categories = [...currentTemplateRiskCategories.value]
    const draggedIndex = categories.findIndex(c => c.id === draggedItem.id)
    const targetIndex = categories.findIndex(c => c.id === targetItem.id)

    if (draggedIndex === -1 || targetIndex === -1) return

    // Reorder array
    const [removed] = categories.splice(draggedIndex, 1)
    categories.splice(targetIndex, 0, removed)

    // Format orders for API
    const orders = categories.map((category, index) => ({
      id: category.id,
      sort_order: index + 1
    }))

    await templatesStore.reorderRiskCategories(managingTemplate.value.id, orders)
    await templatesStore.fetchRiskCategories(managingTemplate.value.id)
  } catch (error) {
    console.error('風險類別排序錯誤:', error)
    await showError('排序失敗', '更新風險類別排序時發生錯誤，請稍後再試')
  }
}

// Risk Factor Management Functions
const currentTemplateRiskFactors = computed(() => {
  if (!managingTemplate.value?.id) return []
  return templatesStore.getRiskFactors(managingTemplate.value.id).value || []
})

const addRiskFactor = () => {
  riskFactorFormData.value = {
    factor_name: '',
    category_id: '',
    topic_id: '',
    description: ''
  }
  showAddRiskFactorModal.value = true
}

const editRiskFactor = async (factor) => {
  editingRiskFactor.value = factor
  isInitializingEditForm.value = true

  // First set category_id and other fields
  riskFactorFormData.value = {
    factor_name: factor.factor_name,
    category_id: factor.category_id,
    topic_id: '',  // Will be set after topics load
    description: factor.description || ''
  }

  // If risk topics are enabled and category has topics, wait for topics to load
  if (managingTemplate.value?.risk_topics_enabled && factor.category_id && managingTemplate.value?.id) {
    isLoadingTopics.value = true
    try {
      await templatesStore.fetchRiskTopics(managingTemplate.value.id)
      // After topics are loaded, set the topic_id
      await nextTick()
      riskFactorFormData.value.topic_id = factor.topic_id || ''
    } catch (error) {
      console.error('Failed to load risk topics:', error)
    } finally {
      isLoadingTopics.value = false
    }
  } else {
    // No topics, just set the value
    riskFactorFormData.value.topic_id = factor.topic_id || ''
  }

  isInitializingEditForm.value = false
  showEditRiskFactorModal.value = true
}

const deleteRiskFactor = (factor) => {
  deletingRiskFactor.value = factor
  showDeleteRiskFactorModal.value = true
}

const submitRiskFactorForm = async () => {
  if (!managingTemplate.value?.id) return

  // Validation: Description is required
  if (!riskFactorFormData.value.description || riskFactorFormData.value.description.trim() === '') {
    await showError('驗證失敗', '描述為必填欄位')
    return
  }

  // Validation: If risk topics are enabled, topic_id is required
  if (managingTemplate.value?.risk_topics_enabled && !riskFactorFormData.value.topic_id) {
    await showError('驗證失敗', '風險主題為必填欄位')
    return
  }

  // Check for duplicate data
  const isDuplicate = currentTemplateRiskFactors.value.some(factor => {
    // Skip the current editing item when checking for duplicates
    if (editingRiskFactor.value && factor.id === editingRiskFactor.value.id) {
      return false
    }

    // Check if factor_name, category_id, topic_id, and description are exactly the same
    return factor.factor_name.trim() === riskFactorFormData.value.factor_name.trim() &&
           String(factor.category_id) === String(riskFactorFormData.value.category_id) &&
           String(factor.topic_id || '') === String(riskFactorFormData.value.topic_id || '') &&
           (factor.description || '').trim() === (riskFactorFormData.value.description || '').trim()
  })

  if (isDuplicate) {
    await showError(
      showAddRiskFactorModal.value ? '新增失敗' : '更新失敗',
      '已存在完全相同的風險因子資料，請修改後再試'
    )
    return
  }

  try {
    if (showAddRiskFactorModal.value) {
      await templatesStore.addRiskFactor(managingTemplate.value.id, riskFactorFormData.value)
      await showSuccess('新增成功', `風險因子「${riskFactorFormData.value.factor_name}」已成功建立`)
    } else if (showEditRiskFactorModal.value && editingRiskFactor.value) {
      await templatesStore.updateRiskFactor(
        managingTemplate.value.id,
        editingRiskFactor.value.id,
        riskFactorFormData.value
      )
      await showSuccess('更新成功', `風險因子「${riskFactorFormData.value.factor_name}」已成功更新`)
    }

    // Refresh risk factors data after successful operation
    await templatesStore.fetchRiskFactors(managingTemplate.value.id)
    closeRiskFactorModals()
  } catch (error) {
    console.error('風險因子操作錯誤:', error)
    await showError(showAddRiskFactorModal.value ? '新增失敗' : '更新失敗', '操作時發生錯誤，請稍後再試')
  }
}

const confirmDeleteRiskFactor = async () => {
  if (!deletingRiskFactor.value || !managingTemplate.value?.id) return

  try {
    await templatesStore.deleteRiskFactor(managingTemplate.value.id, deletingRiskFactor.value.id)
    await showSuccess('刪除成功', `風險因子「${deletingRiskFactor.value.factor_name}」已成功刪除`)

    // Refresh risk factors data after successful deletion
    await templatesStore.fetchRiskFactors(managingTemplate.value.id)
    closeRiskFactorModals()
  } catch (error) {
    console.error('刪除風險因子錯誤:', error)
    await showError('刪除失敗', '刪除風險因子時發生錯誤，請稍後再試')
  }
}

const closeRiskFactorModals = () => {
  showAddRiskFactorModal.value = false
  showEditRiskFactorModal.value = false
  showDeleteRiskFactorModal.value = false
  editingRiskFactor.value = null
  deletingRiskFactor.value = null
  isLoadingTopics.value = false
  riskFactorFormData.value = {
    factor_name: '',
    category_id: '',
    topic_id: '',
    description: ''
  }
}

const handleFactorDragEnd = async ({ draggedItem, targetItem }) => {
  if (!managingTemplate.value?.id) return

  try {
    const factors = [...currentTemplateRiskFactors.value]
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

    await templatesStore.reorderRiskFactors(managingTemplate.value.id, orders)
    await templatesStore.fetchRiskFactors(managingTemplate.value.id)
  } catch (error) {
    console.error('風險因子排序錯誤:', error)
    await showError('排序失敗', '更新風險因子排序時發生錯誤，請稍後再試')
  }
}

// Risk Topic Management Functions
const currentTemplateRiskTopics = computed(() => {
  if (!managingTemplate.value?.id) return []
  return templatesStore.getRiskTopics(managingTemplate.value.id).value || []
})

// Filtered risk topics based on selected category
const filteredRiskTopics = computed(() => {
  if (!riskFactorFormData.value.category_id || !currentTemplateRiskTopics.value) {
    return []
  }
  const selectedCategoryId = String(riskFactorFormData.value.category_id)
  const filtered = currentTemplateRiskTopics.value.filter(topic =>
    String(topic.category_id) === selectedCategoryId
  )

  console.log('Filtering topics:', {
    selectedCategoryId,
    allTopics: currentTemplateRiskTopics.value,
    filteredTopics: filtered
  })

  return filtered
})

const addRiskTopic = () => {
  riskTopicFormData.value = {
    topic_name: '',
    category_id: '',
    description: ''
  }
  showAddRiskTopicModal.value = true
}

const editRiskTopic = (topic) => {
  editingRiskTopic.value = topic
  riskTopicFormData.value = {
    topic_name: topic.topic_name,
    category_id: topic.category_id,
    description: topic.description || ''
  }
  showEditRiskTopicModal.value = true
}

const deleteRiskTopic = (topic) => {
  deletingRiskTopic.value = topic
  showDeleteRiskTopicModal.value = true
}

const submitRiskTopicForm = async () => {
  if (!managingTemplate.value?.id) return

  // Check for duplicate data
  const isDuplicate = currentTemplateRiskTopics.value.some(topic => {
    // Skip the current editing item when checking for duplicates
    if (editingRiskTopic.value && topic.id === editingRiskTopic.value.id) {
      return false
    }

    // Check if topic_name, category_id, and description are exactly the same
    return topic.topic_name.trim() === riskTopicFormData.value.topic_name.trim() &&
           String(topic.category_id) === String(riskTopicFormData.value.category_id) &&
           (topic.description || '').trim() === (riskTopicFormData.value.description || '').trim()
  })

  if (isDuplicate) {
    await showError(
      showAddRiskTopicModal.value ? '新增失敗' : '更新失敗',
      '已存在完全相同的風險主題資料，請修改後再試'
    )
    return
  }

  try {
    if (showAddRiskTopicModal.value) {
      await templatesStore.addRiskTopic(managingTemplate.value.id, riskTopicFormData.value)
      await showSuccess('新增成功', `風險主題「${riskTopicFormData.value.topic_name}」已成功建立`)
    } else if (showEditRiskTopicModal.value && editingRiskTopic.value) {
      await templatesStore.updateRiskTopic(
        managingTemplate.value.id,
        editingRiskTopic.value.id,
        riskTopicFormData.value
      )
      await showSuccess('更新成功', `風險主題「${riskTopicFormData.value.topic_name}」已成功更新`)
    }

    // Refresh risk topics data after successful operation
    await templatesStore.fetchRiskTopics(managingTemplate.value.id)
    closeRiskTopicModals()
  } catch (error) {
    console.error('風險主題操作錯誤:', error)
    await showError(showAddRiskTopicModal.value ? '新增失敗' : '更新失敗', '操作時發生錯誤，請稍後再試')
  }
}

const confirmDeleteRiskTopic = async () => {
  if (!deletingRiskTopic.value || !managingTemplate.value?.id) return

  try {
    await templatesStore.deleteRiskTopic(managingTemplate.value.id, deletingRiskTopic.value.id)
    await showSuccess('刪除成功', `風險主題「${deletingRiskTopic.value.topic_name}」已成功刪除`)

    // Refresh risk topics data after successful deletion
    await templatesStore.fetchRiskTopics(managingTemplate.value.id)
    closeRiskTopicModals()
  } catch (error) {
    console.error('刪除風險主題錯誤:', error)
    await showError('刪除失敗', '刪除風險主題時發生錯誤，請稍後再試')
  }
}

const closeRiskTopicModals = () => {
  showAddRiskTopicModal.value = false
  showEditRiskTopicModal.value = false
  showDeleteRiskTopicModal.value = false
  editingRiskTopic.value = null
  deletingRiskTopic.value = null
  riskTopicFormData.value = {
    topic_name: '',
    category_id: '',
    description: ''
  }
}

const handleTopicDragEnd = async ({ draggedItem, targetItem }) => {
  if (!managingTemplate.value?.id) return

  try {
    const topics = [...currentTemplateRiskTopics.value]
    const draggedIndex = topics.findIndex(t => t.id === draggedItem.id)
    const targetIndex = topics.findIndex(t => t.id === targetItem.id)

    if (draggedIndex === -1 || targetIndex === -1) return

    // Reorder array
    const [removed] = topics.splice(draggedIndex, 1)
    topics.splice(targetIndex, 0, removed)

    // Format orders for API
    const orders = topics.map((topic, index) => ({
      id: topic.id,
      sort_order: index + 1
    }))

    await templatesStore.reorderRiskTopics(managingTemplate.value.id, orders)
    await templatesStore.fetchRiskTopics(managingTemplate.value.id)
  } catch (error) {
    console.error('風險主題排序錯誤:', error)
    await showError('排序失敗', '更新風險主題排序時發生錯誤，請稍後再試')
  }
}

const submitForm = async () => {
  if (isSubmitting.value) return

  isSubmitting.value = true

  // 顯示 loading
  showLoading(showAddModal.value ? '正在新增範本...' : '正在更新範本...')

  try {
    if (showAddModal.value) {
      // Add new template
      await templatesStore.addTemplate({
        versionName: formData.value.versionName
      })

      closeAll() // 關閉 loading
      await showSuccess('新增成功', `範本「${formData.value.versionName}」已成功建立`)
    } else if (showEditModal.value) {
      // Update existing template
      await templatesStore.updateTemplate(editingTemplate.value.id, {
        versionName: formData.value.versionName
      })

      closeAll() // 關閉 loading
      await showSuccess('更新成功', `範本「${formData.value.versionName}」已成功更新`)
    }

    closeModals()
  } catch (error) {
    console.error('Form submission error:', error)
    console.log('Error structure:', {
      'error': error,
      'error.error': error.error,
      'error.error?.message': error.error?.message,
      'error.message': error.message,
      'error.data': error.data,
      'error.data?.message': error.data?.message
    })

    closeAll() // 關閉 loading

    // 使用 API 回傳的錯誤訊息
    const errorMessage = error.error?.message || error.message || '操作時發生錯誤，請稍後再試'
    console.log('Extracted error message:', errorMessage)
    await showError(showAddModal.value ? '新增失敗' : '更新失敗', errorMessage)
  } finally {
    isSubmitting.value = false
  }
}

const closeModals = () => {
  showAddModal.value = false
  showEditModal.value = false
  editingTemplate.value = null
  formData.value.versionName = ''
  isSubmitting.value = false
}

const handleModalClose = (value) => {
  if (!value) {
    closeModals()
  }
}

// Auto-focus management for modal inputs
watch([showAddModal, showEditModal], ([newAddModal, newEditModal]) => {
  const isModalOpen = newAddModal || newEditModal

  if (isModalOpen) {
    // Use nextTick to ensure the DOM is updated before focusing
    nextTick(() => {
      if (versionNameInput.value) {
        versionNameInput.value.focus()
      }
    })
  }
})

// Watch for category changes in risk factor form
watch(() => riskFactorFormData.value.category_id, async (newCategoryId, oldCategoryId) => {
  console.log('Category changed:', { newCategoryId, oldCategoryId, isInitializing: isInitializingEditForm.value })

  // Don't clear topic_id if we're initializing the edit form
  if (isInitializingEditForm.value) {
    return
  }

  // Clear topic selection when category changes (user manually changed it)
  if (newCategoryId !== oldCategoryId) {
    riskFactorFormData.value.topic_id = ''

    // If a category is selected and risk topics are enabled, ensure topics are loaded
    if (newCategoryId && managingTemplate.value?.risk_topics_enabled && managingTemplate.value?.id) {
      isLoadingTopics.value = true
      console.log('Loading topics for template:', managingTemplate.value.id)

      try {
        await templatesStore.fetchRiskTopics(managingTemplate.value.id)
        console.log('Topics loaded successfully')
      } catch (error) {
        console.error('Failed to load risk topics:', error)
        await showError('載入失敗', '無法載入風險主題資料，請稍後再試')
      } finally {
        isLoadingTopics.value = false
      }
    }
  }
})

// Structure Import/Export Functions
const exportStructure = async () => {
  if (!managingTemplate.value) return

  try {
    showLoading('正在匯出架構資料...')

    const templateId = managingTemplate.value.id

    // Call backend API to export structure with RichText support
    const response = await fetch(`/api/v1/risk-assessment/templates/${templateId}/export-structure`, {
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
    a.download = `${managingTemplate.value.version_name}_架構_${new Date().toISOString().slice(0, 10)}.xlsx`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    window.URL.revokeObjectURL(url)

    closeAll()
    await showSuccess('匯出成功', `架構資料已匯出`)
  } catch (error) {
    console.error('Export structure error:', error)
    closeAll()
    await showError('匯出失敗', '匯出架構資料時發生錯誤')
  }
}

// Legacy frontend export method (kept for reference)
const exportStructureLegacy = async () => {
  if (!managingTemplate.value) return

  try {
    showLoading('正在匯出架構資料...')

    const templateId = managingTemplate.value.id
    const hasTopicLayer = managingTemplate.value.risk_topics_enabled ?? true

    // Fetch all structure data
    await templatesStore.fetchRiskCategories(templateId)
    if (hasTopicLayer) {
      await templatesStore.fetchRiskTopics(templateId)
    }
    await templatesStore.fetchRiskFactors(templateId)

    const categories = currentTemplateRiskCategories.value || []
    const topics = hasTopicLayer ? (currentTemplateRiskTopics.value || []) : []
    const factors = currentTemplateRiskFactors.value || []

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
    const filename = `${managingTemplate.value.version_name}_架構_${timestamp}.xlsx`

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
  if (!managingTemplate.value) return

  const hasTopicLayer = managingTemplate.value.risk_topics_enabled ?? true

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
  const filename = `${managingTemplate.value.version_name}_匯入範本_${timestamp}.xlsx`

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
  if (!selectedFile.value || !managingTemplate.value) return

  // Validate templateId
  const templateId = managingTemplate.value.id
  if (!templateId) {
    await showError('錯誤', '無法取得範本 ID，請重新開啟架構管理')
    return
  }

  isImporting.value = true
  importErrors.value = []

  try {
    showLoading('正在匯入架構資料...')

    // Create FormData for file upload
    const formData = new FormData()
    formData.append('file', selectedFile.value)

    // Upload file to backend for processing with RichText support
    const response = await $fetch(`/api/v1/risk-assessment/templates/${templateId}/import-structure`, {
      method: 'POST',
      body: formData
    })

    if (response.success) {
      // Refresh data
      await templatesStore.fetchRiskCategories(templateId)
      if (managingTemplate.value.risk_topics_enabled ?? true) {
        await templatesStore.fetchRiskTopics(templateId)
      }
      await templatesStore.fetchRiskFactors(templateId)

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
    console.error('Template ID:', templateId)
    console.error('Managing template:', managingTemplate.value)
    closeAll()

    let errorMessage = '匯入架構資料時發生錯誤'
    if (error.data?.message) {
      errorMessage += '：' + error.data.message
      // Show debug info if available
      if (error.data.debug) {
        console.error('Debug info:', error.data.debug)
        errorMessage += '\n\nDebug: ' + JSON.stringify(error.data.debug)
      }
    } else if (error.message) {
      errorMessage += '：' + error.message
    }

    await showError('匯入失敗', errorMessage)
    importErrors.value = [errorMessage]
  } finally {
    isImporting.value = false
  }
}


// Initialize store on mount
onMounted(async () => {
  try {
    await templatesStore.initialize()

    // Initialize risk categories for existing templates
    const templatesToInitialize = templatesStore.templates
    for (const template of templatesToInitialize) {
      try {
        await templatesStore.fetchRiskCategories(template.id)
      } catch (error) {
        console.warn(`Failed to fetch risk categories for template ${template.id}:`, error)
      }
    }
  } catch (error) {
    console.error('Failed to initialize templates:', error)
  }
})
</script>