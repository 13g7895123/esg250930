<template>
  <Modal
    :model-value="modelValue"
    :title="title"
    size="lg"
    @update:model-value="$emit('update:modelValue', $event)"
    @close="$emit('close')"
  >
    <div class="space-y-6">
      <!-- Structure Preview -->
      <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 p-4 rounded-lg">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
          <h4 class="text-lg font-medium text-gray-900 dark:text-white break-words">目前架構</h4>

          <!-- Topic Layer Toggle -->
          <div class="flex items-center gap-2 flex-shrink-0">
            <span class="text-sm text-gray-600 dark:text-gray-400 break-words">風險主題層級</span>
            <UToggle
              :model-value="riskTopicsEnabled"
              @update:model-value="$emit('toggle-risk-topics', $event)"
            />
          </div>
        </div>

        <div class="space-y-2 text-sm">
          <div class="flex items-start text-blue-600 dark:text-blue-400">
            <TagIcon class="w-4 h-4 mr-2 flex-shrink-0 mt-0.5" />
            <span class="break-words">風險類別 (Risk Categories)</span>
          </div>

          <div
            v-if="riskTopicsEnabled"
            class="flex items-start text-purple-600 dark:text-purple-400 ml-4 transition-all duration-200"
          >
            <ArrowDownIcon class="w-4 h-4 mr-2 flex-shrink-0 mt-0.5" />
            <span class="break-words">風險主題 (Risk Topics)</span>
            <span class="ml-2 px-2 py-1 text-xs bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-full flex-shrink-0">
              已啟用
            </span>
          </div>

          <div
            class="flex items-start text-orange-600 dark:text-orange-400 transition-all duration-200"
            :class="riskTopicsEnabled ? 'ml-8' : 'ml-4'"
          >
            <ArrowDownIcon class="w-4 h-4 mr-2 flex-shrink-0 mt-0.5" />
            <span class="break-words">風險因子 (Risk Factors)</span>
          </div>

          <!-- Layer Description -->
          <div class="mt-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <p class="text-xs text-gray-600 dark:text-gray-400 break-words">
              <strong>架構說明：</strong>
              <span v-if="riskTopicsEnabled">
                三層架構 - 風險類別可包含多個風險主題，風險主題可包含多個風險因子
              </span>
              <span v-else>
                二層架構 - 風險類別直接包含風險因子，跳過主題層級
              </span>
            </p>
          </div>
        </div>
      </div>

      <!-- Management Actions -->
      <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 p-4 rounded-lg">
        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-3 break-words">管理功能</h4>
        <div class="grid grid-cols-1 gap-3">
          <!-- Manage Categories -->
          <button
            @click="$emit('open-category-management')"
            class="flex items-start p-3 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors text-left w-full"
          >
            <TagIcon class="w-5 h-5 mr-3 flex-shrink-0 mt-0.5" />
            <div class="flex-1 min-w-0">
              <div class="font-medium break-words">管理風險類別</div>
              <div class="text-sm opacity-75 break-words">新增、編輯、刪除風險分類</div>
            </div>
          </button>

          <!-- Manage Topics (Enabled) -->
          <button
            v-if="riskTopicsEnabled"
            @click="$emit('open-topic-management')"
            class="flex items-start p-3 bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors text-left w-full"
          >
            <ChatBubbleLeftRightIcon class="w-5 h-5 mr-3 flex-shrink-0 mt-0.5" />
            <div class="flex-1 min-w-0">
              <div class="font-medium break-words">管理風險主題</div>
              <div class="text-sm opacity-75 break-words">新增、編輯、刪除風險主題</div>
            </div>
          </button>

          <!-- Manage Topics (Disabled) -->
          <div
            v-if="!riskTopicsEnabled"
            class="flex items-start p-3 bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 rounded-lg cursor-not-allowed opacity-60"
          >
            <ChatBubbleLeftRightIcon class="w-5 h-5 mr-3 flex-shrink-0 mt-0.5" />
            <div class="flex-1 min-w-0">
              <div class="font-medium break-words">管理風險主題</div>
              <div class="text-sm opacity-75 break-words">已停用（請啟用風險主題層級）</div>
            </div>
          </div>

          <!-- Manage Factors -->
          <button
            @click="$emit('open-factor-management')"
            class="flex items-start p-3 bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-300 rounded-lg hover:bg-orange-100 dark:hover:bg-orange-900/30 transition-colors text-left w-full"
          >
            <ExclamationTriangleIcon class="w-5 h-5 mr-3 flex-shrink-0 mt-0.5" />
            <div class="flex-1 min-w-0">
              <div class="font-medium break-words">管理風險因子</div>
              <div class="text-sm opacity-75 break-words">新增、編輯、刪除風險因子</div>
            </div>
          </button>

          <!-- Sync from Template (Questions only) -->
          <button
            v-if="managementType === 'question'"
            @click="$emit('sync-from-template')"
            class="flex items-start p-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors text-left w-full"
          >
            <ArrowDownIcon class="w-5 h-5 mr-3 flex-shrink-0 mt-0.5" />
            <div class="flex-1 min-w-0">
              <div class="font-medium break-words">從範本同步</div>
              <div class="text-sm opacity-75 break-words">重新從原始範本載入架構</div>
            </div>
          </button>

          <!-- Go to Content -->
          <button
            @click="$emit('go-to-content')"
            class="flex items-start p-3 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors text-left w-full"
          >
            <DocumentTextIcon class="w-5 h-5 mr-3 flex-shrink-0 mt-0.5" />
            <div class="flex-1 min-w-0">
              <div class="font-medium break-words">
                {{ managementType === 'template' ? '前往範本內容' : '前往題項內容' }}
              </div>
              <div class="text-sm opacity-75 break-words">管理題目內容</div>
            </div>
          </button>
        </div>
      </div>

      <!-- Import/Export Actions -->
      <div
        v-if="showExportImport"
        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 p-4 rounded-lg"
      >
        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-3 break-words">匯入/匯出</h4>
        <div class="grid grid-cols-2 gap-3">
          <button
            @click="$emit('export-structure')"
            class="flex items-center justify-center p-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors"
          >
            <ArrowDownTrayIcon class="w-5 h-5 mr-2 flex-shrink-0" />
            <span class="font-medium break-words">匯出 Excel</span>
          </button>
          <button
            @click="$emit('import-structure')"
            class="flex items-center justify-center p-3 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors"
          >
            <ArrowUpTrayIcon class="w-5 h-5 mr-2 flex-shrink-0" />
            <span class="font-medium break-words">匯入 Excel</span>
          </button>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import {
  TagIcon,
  ArrowDownIcon,
  ChatBubbleLeftRightIcon,
  ExclamationTriangleIcon,
  DocumentTextIcon,
  ArrowDownTrayIcon,
  ArrowUpTrayIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  },
  title: {
    type: String,
    required: true
  },
  itemName: {
    type: String,
    default: ''
  },
  riskTopicsEnabled: {
    type: Boolean,
    default: true
  },
  showExportImport: {
    type: Boolean,
    default: true
  },
  managementType: {
    type: String,
    default: 'template', // 'template' or 'question'
    validator: (value) => ['template', 'question'].includes(value)
  }
})

// Emits
const emit = defineEmits([
  'update:modelValue',
  'close',
  'toggle-risk-topics',
  'open-category-management',
  'open-topic-management',
  'open-factor-management',
  'sync-from-template',
  'go-to-content',
  'export-structure',
  'import-structure'
])
</script>
