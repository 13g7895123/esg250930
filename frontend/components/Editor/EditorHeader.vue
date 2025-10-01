<template>
  <div class="mb-6">
    <div class="flex items-center justify-between">
      <!-- 左側：標題資訊 -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
          {{ title }}
        </h1>
        <p class="mt-1 text-base text-gray-500 dark:text-gray-400">
          {{ subtitle }}
        </p>
      </div>

      <!-- 右側：操作按鈕區 -->
      <div class="flex space-x-3">
        <!-- 填入測試資料按鈕 (僅 template 模式) -->
        <button
          v-if="features.showTestDataButton"
          @click="$emit('fill-test-data')"
          class="px-4 py-2 text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-900/20 border border-purple-300 dark:border-purple-600 rounded-2xl hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors duration-200"
        >
          填入測試資料
        </button>

        <!-- 返回按鈕 -->
        <button
          v-if="features.showBackButton"
          @click="$emit('back')"
          class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-2xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200"
        >
          {{ features.backButtonText }}
        </button>

        <!-- 預覽按鈕 -->
        <button
          v-if="features.showPreviewButton"
          @click="$emit('preview')"
          class="px-4 py-2 text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/20 border border-blue-300 dark:border-blue-600 rounded-2xl hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors duration-200"
        >
          預覽
        </button>

        <!-- 儲存按鈕 -->
        <button
          v-if="features.showSaveButton"
          @click="$emit('save')"
          :disabled="isSaving"
          class="px-4 py-2 bg-green-600 text-white rounded-2xl hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors duration-200"
        >
          <span v-if="isSaving" class="flex items-center">
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            儲存中...
          </span>
          <span v-else>儲存題目</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
/**
 * EditorHeader - 編輯器頁面標題與操作按鈕區
 *
 * @emits back - 返回列表頁
 * @emits preview - 開啟預覽
 * @emits save - 儲存題目
 * @emits fill-test-data - 填入測試資料 (僅 template 模式)
 */

const props = defineProps({
  /**
   * 頁面標題
   */
  title: {
    type: String,
    default: '題目編輯'
  },

  /**
   * 副標題
   */
  subtitle: {
    type: String,
    default: '編輯完整的ESG評估題目內容'
  },

  /**
   * 功能開關配置 (來自 useEditorFeatures)
   */
  features: {
    type: Object,
    required: true
  },

  /**
   * 是否正在儲存
   */
  isSaving: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['back', 'preview', 'save', 'fill-test-data'])
</script>

<style scoped>
/* 按鈕樣式已使用 Tailwind CSS，無需額外樣式 */
</style>
