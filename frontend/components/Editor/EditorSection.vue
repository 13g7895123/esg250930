<template>
  <div class="bg-white assessment-rounded border border-gray-200 overflow-hidden">
    <!-- Section 標題列 -->
    <component
      :is="collapsible ? 'button' : 'div'"
      :class="[
        'w-full px-6 py-4 flex items-center justify-between text-left',
        collapsible ? 'hover:bg-gray-50 cursor-pointer' : ''
      ]"
      @click="collapsible && toggleSection()"
    >
      <div class="flex items-center space-x-3">
        <span class="font_title">{{ title }}</span>
        <span
          v-if="sectionId"
          class="bg-gray-200 text-gray-800 px-2 py-1 rounded text-base font-medium"
        >
          {{ sectionId }}
        </span>
      </div>

      <!-- 收折圖示 (僅可收折模式顯示) -->
      <ChevronUpIcon
        v-if="collapsible && isExpanded"
        class="w-5 h-5 text-gray-400"
      />
      <ChevronDownIcon
        v-else-if="collapsible && !isExpanded"
        class="w-5 h-5 text-gray-400"
      />
    </component>

    <!-- Section 內容區 -->
    <div v-show="isExpanded" class="px-6 pb-6">
      <!-- 分隔線 -->
      <div class="mb-3">
        <div class="border-t-2 border-gray-300 mb-4 rounded-full"></div>
      </div>

      <!-- Slot: 內容插槽 -->
      <slot />
    </div>
  </div>
</template>

<script setup>
import { ChevronUpIcon, ChevronDownIcon } from '@heroicons/vue/24/outline'

/**
 * EditorSection - 可收折的區塊容器
 *
 * 用於包裝 Section A, B 等區塊，支援可選的收折功能
 *
 * @emits update:expanded - 展開/收折狀態變更
 */

const props = defineProps({
  /**
   * Section 標題
   */
  title: {
    type: String,
    required: true
  },

  /**
   * Section 標識符 (如 'A', 'B', 'C' 等)
   */
  sectionId: {
    type: String,
    default: ''
  },

  /**
   * 是否可收折
   */
  collapsible: {
    type: Boolean,
    default: false
  },

  /**
   * 是否展開 (v-model)
   */
  expanded: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:expanded'])

// 內部展開狀態
const isExpanded = computed({
  get: () => props.expanded,
  set: (value) => emit('update:expanded', value)
})

// 切換展開/收折
const toggleSection = () => {
  isExpanded.value = !isExpanded.value
}
</script>

<style scoped>
.assessment-rounded {
  @apply rounded-2xl;
}

.font_title {
  @apply font-bold text-gray-900 text-xl;
}
</style>
