<template>
  <div
    class="relative group"
    @mouseenter="handleMouseEnter"
    @mouseleave="handleMouseLeave"
  >
    <!-- 觸發元素：顯示截斷的文字 -->
    <div
      :class="textClass || 'text-base text-gray-500 dark:text-gray-400 cursor-pointer truncate'"
    >
      {{ displayContent }}
    </div>

    <!-- Teleport Tooltip to body -->
    <Teleport to="body">
      <div
        v-if="tooltipState.visible && content"
        :style="{
          position: 'fixed',
          left: tooltipState.x + 'px',
          top: tooltipState.y + 'px',
          zIndex: 9999
        }"
        :class="maxWidth"
        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-2xl p-4 max-h-96 overflow-y-auto"
        @mouseenter="keepTooltipOpen"
        @mouseleave="handleMouseLeave"
      >
        <!-- Tooltip 內容 -->
        <div
          class="text-sm text-gray-700 dark:text-gray-300 prose prose-sm dark:prose-invert max-w-none"
          v-html="content"
        ></div>

        <!-- 三角箭頭指示器 -->
        <div
          class="absolute -top-2 left-4 w-4 h-4 bg-white dark:bg-gray-800 border-l border-t border-gray-200 dark:border-gray-700 transform rotate-45"
        ></div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
const props = defineProps({
  // Tooltip 的 HTML 內容
  content: {
    type: String,
    required: true
  },

  // 觸發元素顯示的文字（可選，如果不提供則從 content 截取）
  displayText: {
    type: String,
    default: null
  },

  // 顯示文字的截斷長度
  truncateLength: {
    type: Number,
    default: 20
  },

  // Tooltip 最大寬度類別
  maxWidth: {
    type: String,
    default: 'max-w-2xl w-96'
  },

  // 自定義觸發元素樣式
  textClass: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['show', 'hide'])

// Tooltip 狀態
const tooltipState = ref({
  visible: false,
  x: 0,
  y: 0
})

let tooltipTimeout = null

// 計算顯示的內容（截斷後的文字）
const displayContent = computed(() => {
  if (props.displayText) {
    return props.displayText
  }

  return stripHtmlTags(props.content, props.truncateLength)
})

// 去除 HTML 標籤並截斷文字
const stripHtmlTags = (html, maxLength = 100) => {
  if (!html) return ''

  // 建立臨時 div 來解析 HTML
  const tmp = document.createElement('div')
  tmp.innerHTML = html

  // 取得純文字內容（去除所有 HTML 標籤）
  const text = tmp.textContent || tmp.innerText || ''

  // 如果需要截斷
  if (text.length > maxLength) {
    return text.substring(0, maxLength) + '...'
  }

  return text
}

// 顯示 tooltip
const handleMouseEnter = (event) => {
  if (!props.content) return

  // 清除任何現有的延遲隱藏計時器
  if (tooltipTimeout) {
    clearTimeout(tooltipTimeout)
  }

  // 取得元素的位置
  const rect = event.target.getBoundingClientRect()

  // 定位 tooltip 在元素下方
  tooltipState.value = {
    visible: true,
    x: rect.left,
    y: rect.bottom + 8 // 8px 間距
  }

  // 發送 show 事件
  emit('show')
}

// 隱藏 tooltip（延遲執行）
const handleMouseLeave = () => {
  // 添加小延遲，允許滑鼠移到 tooltip 上
  tooltipTimeout = setTimeout(() => {
    tooltipState.value.visible = false

    // 發送 hide 事件
    emit('hide')
  }, 100)
}

// 保持 tooltip 開啟（當滑鼠進入 tooltip 時）
const keepTooltipOpen = () => {
  if (tooltipTimeout) {
    clearTimeout(tooltipTimeout)
  }
}

// 清理計時器
onUnmounted(() => {
  if (tooltipTimeout) {
    clearTimeout(tooltipTimeout)
  }
})
</script>
