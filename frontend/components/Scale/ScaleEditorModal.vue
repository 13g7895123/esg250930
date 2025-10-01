<template>
  <Teleport to="body">
    <div
      v-if="modelValue"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
      @click.self="closeModal"
    >
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-6xl max-h-[90vh] overflow-y-auto m-4">
        <!-- Modal Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between z-10">
          <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ title }}</h2>
          <button
            @click="closeModal"
            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 dark:border-gray-700">
          <nav class="flex px-6" aria-label="Tabs">
            <button
              @click="activeTab = 'probability'"
              :class="[
                'py-4 px-6 text-sm font-medium border-b-2 transition-colors duration-200',
                activeTab === 'probability'
                  ? 'border-blue-600 text-blue-600 dark:text-blue-400'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
              ]"
            >
              風險發生可能性量表
            </button>
            <button
              @click="activeTab = 'impact'"
              :class="[
                'py-4 px-6 text-sm font-medium border-b-2 transition-colors duration-200',
                activeTab === 'impact'
                  ? 'border-blue-600 text-blue-600 dark:text-blue-400'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
              ]"
            >
              財務衝擊量表
            </button>
          </nav>
        </div>

        <!-- Modal Content -->
        <div class="px-6 py-6">
          <!-- Loading Spinner -->
          <div v-if="isLoading" class="flex items-center justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <span class="ml-3 text-gray-600 dark:text-gray-400">載入量表資料中...</span>
          </div>

          <!-- Probability Scale Tab -->
          <div v-show="activeTab === 'probability' && !isLoading">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">風險發生可能性量表</h3>
              <div v-if="isEditable" class="flex items-center space-x-2">
                <button
                  @click="$emit('add-probability-column')"
                  class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition-colors duration-200"
                >
                  + 新增欄位
                </button>
                <button
                  @click="$emit('add-probability-row')"
                  class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition-colors duration-200"
                >
                  + 新增列
                </button>
              </div>
            </div>

            <!-- Display Column Selector (only in editor mode) -->
            <div v-if="isEditable" class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                下拉選單預設顯示欄位
              </label>
              <select
                :value="selectedProbabilityDisplayColumn"
                @change="$emit('update:selected-probability-display-column', $event.target.value)"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="probability">發生可能性程度</option>
                <option v-for="column in probabilityColumns" :key="column.id" :value="column.id.toString()">
                  {{ column.name || '（未命名）' }}
                </option>
              </select>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                此欄位將顯示在 E-1 和 F-1 的「風險/機會發生可能性」下拉選單中
              </p>
            </div>

            <!-- Probability Scale Table -->
            <ScaleTable
              :columns="probabilityColumns"
              :rows="probabilityRows"
              :fixed-columns="probabilityFixedColumns"
              :readonly="!isEditable"
              @remove-column="$emit('remove-probability-column', $event)"
              @remove-row="$emit('remove-probability-row', $event)"
            />

            <!-- Description Text Section -->
            <!-- Description Text (only in editor mode) -->
            <template v-if="isEditable">
              <div v-if="showProbabilityDescription" class="mt-4 p-4 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                <div class="flex items-start justify-between mb-2">
                  <label class="text-sm font-medium text-gray-700 dark:text-gray-300">說明文字</label>
                  <button
                    @click="$emit('remove-probability-description')"
                    class="p-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                    title="刪除說明文字"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
                <textarea
                  :value="probabilityDescriptionText"
                  @input="$emit('update:probability-description-text', $event.target.value)"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                  placeholder="輸入說明文字"
                />
              </div>

              <!-- Add Description Button -->
              <div v-else class="mt-4">
                <button
                  @click="$emit('add-probability-description')"
                  class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200"
                >
                  + 新增說明文字
                </button>
              </div>
            </template>
          </div>

          <!-- Impact Scale Tab -->
          <div v-show="activeTab === 'impact' && !isLoading">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">財務衝擊量表</h3>
              <div v-if="isEditable" class="flex items-center space-x-2">
                <button
                  @click="$emit('add-impact-column')"
                  class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition-colors duration-200"
                >
                  + 新增欄位
                </button>
                <button
                  @click="$emit('add-impact-row')"
                  class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition-colors duration-200"
                >
                  + 新增列
                </button>
              </div>
            </div>

            <!-- Display Column Selector (only in editor mode) -->
            <div v-if="isEditable" class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                下拉選單預設顯示欄位
              </label>
              <select
                :value="selectedImpactDisplayColumn"
                @change="$emit('update:selected-impact-display-column', $event.target.value)"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="impactLevel">財務衝擊程度</option>
                <option v-for="column in impactColumns" :key="column.id" :value="column.id.toString()">
                  {{ column.name || '（未命名）' }}
                </option>
              </select>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                此欄位將顯示在 E-1 和 F-1 的「財務衝擊」下拉選單中
              </p>
            </div>

            <!-- Impact Scale Table -->
            <ScaleTable
              :columns="impactColumns"
              :rows="impactRows"
              :fixed-columns="impactFixedColumns"
              :readonly="!isEditable"
              @remove-column="$emit('remove-impact-column', $event)"
              @remove-row="$emit('remove-impact-row', $event)"
            />

            <!-- Description Text (only in editor mode) -->
            <template v-if="isEditable">
              <div v-if="showImpactDescription" class="mt-4 p-4 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700">
                <div class="flex items-start justify-between mb-2">
                  <label class="text-sm font-medium text-gray-700 dark:text-gray-300">說明文字</label>
                  <button
                    @click="$emit('remove-impact-description')"
                    class="p-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                    title="刪除說明文字"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
                <textarea
                  :value="impactDescriptionText"
                  @input="$emit('update:impact-description-text', $event.target.value)"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                  placeholder="輸入說明文字"
                />
              </div>

              <!-- Add Description Button -->
              <div v-else class="mt-4">
                <button
                  @click="$emit('add-impact-description')"
                  class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200"
                >
                  + 新增說明文字
                </button>
              </div>
            </template>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="sticky bottom-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-end space-x-3">
          <button
            @click="closeModal"
            class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200"
          >
            {{ isViewerMode ? '關閉' : '取消' }}
          </button>
          <button
            v-if="isEditable"
            @click="$emit('save')"
            class="px-4 py-2 text-sm text-white bg-blue-600 rounded hover:bg-blue-700 transition-colors duration-200"
          >
            儲存
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue'
import ScaleTable from './ScaleTable.vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  },
  title: {
    type: String,
    default: '量表編輯'
  },
  isLoading: {
    type: Boolean,
    default: false
  },
  // Probability Scale Props
  probabilityColumns: {
    type: Array,
    default: () => []
  },
  probabilityRows: {
    type: Array,
    default: () => []
  },
  selectedProbabilityDisplayColumn: {
    type: String,
    default: 'probability'
  },
  showProbabilityDescription: {
    type: Boolean,
    default: false
  },
  probabilityDescriptionText: {
    type: String,
    default: ''
  },
  // Impact Scale Props
  impactColumns: {
    type: Array,
    default: () => []
  },
  impactRows: {
    type: Array,
    default: () => []
  },
  selectedImpactDisplayColumn: {
    type: String,
    default: 'impactLevel'
  },
  showImpactDescription: {
    type: Boolean,
    default: false
  },
  impactDescriptionText: {
    type: String,
    default: ''
  },
  // Mode prop to control edit/view behavior
  mode: {
    type: String,
    default: 'editor', // 'editor' | 'viewer' | 'viewer-compact'
    validator: (value) => ['editor', 'viewer', 'viewer-compact'].includes(value)
  }
})

const emit = defineEmits([
  'update:modelValue',
  'update:selected-probability-display-column',
  'update:selected-impact-display-column',
  'update:probability-description-text',
  'update:impact-description-text',
  'add-probability-column',
  'remove-probability-column',
  'add-probability-row',
  'remove-probability-row',
  'add-probability-description',
  'remove-probability-description',
  'add-impact-column',
  'remove-impact-column',
  'add-impact-row',
  'remove-impact-row',
  'add-impact-description',
  'remove-impact-description',
  'save'
])

const activeTab = ref('probability')

// Check if current mode allows editing
const isEditable = computed(() => props.mode === 'editor')
const isViewerMode = computed(() => props.mode === 'viewer' || props.mode === 'viewer-compact')

const probabilityFixedColumns = [
  { key: 'probability', label: '發生可能性程度', placeholder: '例：極低 (1-5%)', type: 'text' },
  { key: 'scoreRange', label: '分數級距', placeholder: '數字', type: 'number' }
]

const impactFixedColumns = [
  { key: 'impactLevel', label: '財務衝擊程度', placeholder: '例：輕微', type: 'text' },
  { key: 'scoreRange', label: '分數級距', placeholder: '數字', type: 'number' }
]

const closeModal = () => {
  emit('update:modelValue', false)
}
</script>
