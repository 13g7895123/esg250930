<template>
  <span
    class="relative group cursor-help inline-block"
    :class="customClass"
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
   * 截斷長度，預設為 20（不截斷則不顯示 tooltip）
   */
  truncateLength: {
    type: Number,
    default: 0
  },
  /**
   * 自訂文字的 class
   */
  customClass: {
    type: String,
    default: ''
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
  if (props.truncateLength === 0 || props.text.length <= props.truncateLength) {
    return props.text
  }
  return props.text.substring(0, props.truncateLength) + '...'
})

// 判斷是否需要顯示 tooltip
const shouldShowTooltip = computed(() => {
  // 如果沒有設定截斷長度，則總是顯示 tooltip（用於 hover 顯示完整內容）
  if (props.truncateLength === 0) return true
  // 如果有設定截斷長度，則只在文字超過長度時顯示
  return props.text && props.text.length > props.truncateLength
})
</script>
