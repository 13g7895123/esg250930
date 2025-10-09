<template>
  <!-- 綠色量表按鈕列 -->
  <div class="text-white px-6 py-3 rounded-2xl flex items-center justify-between" style="background-color: #059669;">
    <span class="font-bold text-white text-xl">
      請依上述資訊，整點公司致富相關之風險情況，並評估未來永續在各風險/機會情境
    </span>

    <!-- 量表按鈕 -->
    <button
      v-if="showButton"
      @click="$emit('open-scale')"
      :disabled="disabled"
      :class="[
        'px-4 py-2 bg-white font-bold rounded-full transition-colors duration-200 flex items-center space-x-2 whitespace-nowrap',
        disabled
          ? 'text-gray-400 opacity-50 cursor-not-allowed'
          : 'text-black hover:bg-gray-100'
      ]"
    >
      <span>{{ buttonText }}</span>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
        />
      </svg>
    </button>
  </div>
</template>

<script setup>
/**
 * EditorScaleBar - 量表按鈕區
 *
 * 顯示在 Section D 和 Section E/F 之間的綠色提示列
 *
 * @emits open-scale - 開啟量表 modal
 */

const props = defineProps({
  /**
   * 量表模式
   * - 'editor': 編輯模式 (完整功能)
   * - 'viewer': 檢視模式 (唯讀)
   * - 'viewer-compact': 精簡檢視模式 (預覽頁面)
   */
  mode: {
    type: String,
    default: 'editor',
    validator: (value) => ['editor', 'viewer', 'viewer-compact'].includes(value)
  },

  /**
   * 是否顯示按鈕
   */
  showButton: {
    type: Boolean,
    default: true
  },

  /**
   * 是否禁用按鈕
   */
  disabled: {
    type: Boolean,
    default: false
  },

  /**
   * 自訂按鈕文字
   */
  customButtonText: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['open-scale'])

// 計算按鈕文字
const buttonText = computed(() => {
  if (props.customButtonText) {
    return props.customButtonText
  }

  switch (props.mode) {
    case 'editor':
      return '量表檢視'
    case 'viewer':
      return '量表檢視'
    case 'viewer-compact':
      return '量表檢視'
    default:
      return '量表檢視'
  }
})
</script>

<style scoped>
/* 樣式已使用 Tailwind CSS */
</style>
