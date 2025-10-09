<template>
  <span
    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium cursor-help relative group"
    :class="variantClasses"
  >
    {{ displayText }}
    <!-- Custom CSS Tooltip -->
    <span
      v-if="shouldShowTooltip"
      class="absolute left-0 top-full mt-2 p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible z-[9999] whitespace-nowrap text-sm font-normal"
      :class="tooltipClass"
    >
      {{ text }}
    </span>
  </span>
</template>

<script setup>
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
    factor: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
  }
  return variants[props.variant] || variants.category
})
</script>
