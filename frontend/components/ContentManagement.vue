<template>
  <div class="p-6">
    <!-- Page Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ title }}</h1>
      <p class="text-gray-600 dark:text-gray-400">{{ description }}</p>
    </div>

    <!-- Data Table with Pagination -->
    <DataTable
      :data="contentData"
      :columns="columns"
      search-placeholder="搜尋題目..."
      :search-fields="['description', 'topic', 'risk_factor']"
      empty-title="還沒有題目內容"
      empty-message="開始建立您的第一個題目內容"
      no-search-results-title="沒有找到符合的題目"
      no-search-results-message="請嘗試其他搜尋關鍵字"
    >
      <!-- Actions Slot -->
      <template #actions>
        <div class="flex items-center space-x-3">
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
            {{ isRefreshing ? '重新整理中...' : '重新整理' }}
          </button>

          <!-- Add Button -->
          <button
            @click="showAddModal = true"
            class="flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200"
          >
            <PlusIcon class="w-4 h-4 mr-2" />
            新增題目
          </button>
        </div>
      </template>

      <!-- Custom Actions Cell -->
      <template #cell-actions="{ item }">
        <div class="flex items-center space-x-2">
          <!-- Drag Handle (Note: Drag & drop functionality will be limited in DataTable) -->
          <div class="relative group cursor-move drag-handle">
            <div class="p-2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 rounded-lg transition-colors duration-200">
              <Bars3Icon class="w-4 h-4" />
            </div>
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
              排序
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

    <!-- Add/Edit Modal -->
    <div
      v-if="showAddModal || showEditModal"
      class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
      @click="closeModals"
    >
      <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md"
        @click.stop
      >
        <div class="p-6">
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

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                風險因子描述
              </label>
              <textarea
                v-model="formData.description"
                rows="4"
                required
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                placeholder="請輸入風險因子描述"
              ></textarea>
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
  ArrowPathIcon
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

const formData = ref({
  categoryId: '',
  topicId: '',
  riskFactorId: '',
  topic: '',
  description: ''
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
      key: 'description',
      label: '風險因子描述',
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
    console.log('Sample factor:', JSON.stringify(factors[0], null, 2))
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
    console.log('Factors:', factors.map(f => ({ id: f.id, name: f.factor_name, topic_id: f.topic_id, category_id: f.category_id })))
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
  formData.value.description = content.description

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
      } else {
        // 載入該 category 的 factors
        await fetchFactorsForCategory(content.category_id)
      }

      // 等待一個 tick 確保 factors 資料已載入
      await nextTick()

      // 最後設置 riskFactorId
      if (originalRiskFactorId) {
        formData.value.riskFactorId = originalRiskFactorId
      }
    } catch (error) {
      console.error('Failed to load dropdown data for edit:', error)
    }
  } else {
    // 如果沒有 category_id，直接設置空值
    formData.value.categoryId = ''
    formData.value.topicId = ''
    formData.value.riskFactorId = ''
  }

  showEditModal.value = true
}

const deleteContent = (content) => {
  contentToDelete.value = content
  showDeleteModal.value = true
}

const confirmDelete = () => {
  if (contentToDelete.value) {
    emit('delete-content', contentToDelete.value.id)
  }
  showDeleteModal.value = false
  contentToDelete.value = null
}

const submitForm = () => {
  // 只傳送表單實際填寫的欄位，其他由後端處理預設值
  const submitData = {
    categoryId: formData.value.categoryId,
    description: formData.value.description,
    riskFactorId: formData.value.riskFactorId || null
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
  formData.value.description = ''
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