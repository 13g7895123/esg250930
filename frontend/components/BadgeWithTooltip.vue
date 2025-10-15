<template>
  <span
    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium cursor-help relative"
    :class="variantClasses"
    @mouseenter="handleMouseEnter"
    @mouseleave="handleMouseLeave"
  >
    {{ displayText }}
    <!-- Teleport Tooltip to body -->
    <Teleport to="body">
      <div
        v-if="tooltipVisible && shouldShowTooltip"
        :style="{
          position: 'fixed',
          left: tooltipPosition.x + 'px',
          top: tooltipPosition.y + 'px',
          zIndex: 9999
        }"
        class="p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg whitespace-nowrap text-sm font-normal"
        :class="tooltipClass"
        @mouseenter="keepTooltipOpen"
        @mouseleave="handleMouseLeave"
      >
        {{ text }}
        <!-- 三角箭頭指示器 -->
        <div
          class="absolute -top-2 left-4 w-4 h-4 bg-white dark:bg-gray-800 border-l border-t border-gray-200 dark:border-gray-700 transform rotate-45"
        ></div>
      </div>
    </Teleport>
  </span>
</template>

<script setup>
import { ref, computed, onUnmounted } from 'vue'

const props = defineProps({
  /**
   * 完整文字內容
   */
  text: {
    type: String,
    required: true
  },
  /**
   * 截斷長度，預設為 6
   */
  truncateLength: {
    type: Number,
    default: 6
  },
  /**
   * Badge 顏色變體
   * @values category, topic, factor
   */
  variant: {
    type: String,
    default: 'category',
    validator: (value) => ['category', 'topic', 'factor'].includes(value)
  },
  /**
   * 自訂 tooltip 的額外 class
   */
  tooltipClass: {
    type: String,
    default: ''
  }
})

// Tooltip 狀態
const tooltipVisible = ref(false)
const tooltipPosition = ref({ x: 0, y: 0 })
let tooltipTimeout = null

// 計算顯示文字（截斷）
const displayText = computed(() => {
  if (!props.text) return ''
  if (props.text.length <= props.truncateLength) return props.text
  return props.text.substring(0, props.truncateLength) + '...'
})

// 判斷是否需要顯示 tooltip
const shouldShowTooltip = computed(() => {
  return props.text && props.text.length > props.truncateLength
})

// 根據 variant 決定顏色 class
const variantClasses = computed(() => {
  const variants = {
    category: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    topic: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
    factor: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200'
  }
  return variants[props.variant] || variants.category
})

// 顯示 tooltip
const handleMouseEnter = (event) => {
  if (!shouldShowTooltip.value) return

  // 清除任何現有的延遲隱藏計時器
  if (tooltipTimeout) {
    clearTimeout(tooltipTimeout)
  }

  // 取得元素的位置
  const rect = event.target.getBoundingClientRect()

  // 定位 tooltip 在元素下方
  tooltipPosition.value = {
    x: rect.left,
    y: rect.bottom + 8 // 8px 間距
  }

  tooltipVisible.value = true
}

// 隱藏 tooltip（延遲執行）
const handleMouseLeave = () => {
  // 添加小延遲，允許滑鼠移到 tooltip 上
  tooltipTimeout = setTimeout(() => {
    tooltipVisible.value = false
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
