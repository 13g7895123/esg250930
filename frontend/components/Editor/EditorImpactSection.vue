<template>
  <div class="bg-white assessment-rounded border border-gray-200 p-6">
    <!-- Section 標題 -->
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center space-x-3">
        <span class="font_title">{{ title }}</span>
        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-base font-medium flex items-center">
          {{ sectionId }}
        </span>

        <!-- Hover 資訊圖示 -->
        <div
          v-if="showHoverIcon"
          class="relative group w-5 h-5 ml-2 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-serif font-bold cursor-pointer hover:scale-110 transition-transform duration-200"
          :class="{ 'cursor-pointer': editableHover, 'cursor-help': !editableHover }"
          @click="editableHover && $emit('edit-hover')"
        >
          <span class="italic">i</span>
          <!-- Hover Tooltip -->
          <div
            v-if="!editableHover"
            class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-800 text-white text-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-20"
          >
            {{ hoverText }}
            <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-800"></div>
          </div>
        </div>
      </div>

      <button
        v-if="!readonly"
        class="px-3 py-1 bg-green-600 text-white text-base rounded-full"
      >
        紀錄
      </button>
    </div>

    <!-- 內容區 -->
    <div class="space-y-4">
      <!-- 衝擊/影響程度 -->
      <div>
        <label class="block text-base text-gray-600 mb-1">{{ levelLabel }}</label>
        <select
          v-model="localImpactData.level"
          :class="[
            'w-full border border-gray-300 rounded-2xl px-3 py-2 text-center font-medium focus:ring-2 focus:ring-green-500 focus:border-transparent',
            readonly ? 'bg-gray-50 text-gray-600 cursor-not-allowed' : ''
          ]"
          :disabled="readonly"
        >
          <option value="1">1 (極低{{ levelSuffix }})</option>
          <option value="2">2 (低{{ levelSuffix }})</option>
          <option value="3">3 (中等{{ levelSuffix }})</option>
          <option value="4">4 (高{{ levelSuffix }})</option>
          <option value="5">5 (極高{{ levelSuffix }})</option>
        </select>
      </div>

      <!-- 評分說明 -->
      <div>
        <label class="block text-base text-gray-600 mb-1">評分說明</label>
        <textarea
          v-model="localImpactData.description"
          rows="4"
          :class="[
            'w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent text-base',
            readonly ? 'bg-gray-50 text-gray-600 cursor-not-allowed' : ''
          ]"
          :placeholder="descriptionPlaceholder"
          :readonly="readonly"
          :disabled="readonly"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
/**
 * EditorImpactSection - Section G/H: 對外衝擊/影響評估區塊 (通用元件)
 *
 * 可用於 Section G (負面衝擊) 和 Section H (正面影響)
 *
 * @emits update:modelValue - 衝擊資料變更
 * @emits edit-hover - 編輯 hover 文字 (僅 template 模式)
 */

const props = defineProps({
  /**
   * 衝擊類型 ('negative' | 'positive')
   */
  type: {
    type: String,
    required: true,
    validator: (value) => ['negative', 'positive'].includes(value)
  },

  /**
   * 衝擊資料物件
   * {
   *   level: number,
   *   description: string
   * }
   */
  modelValue: {
    type: Object,
    default: () => ({
      level: 2,
      description: ''
    })
  },

  /**
   * 是否唯讀
   */
  readonly: {
    type: Boolean,
    default: false
  },

  /**
   * Hover 文字
   */
  hoverText: {
    type: String,
    default: ''
  },

  /**
   * 是否顯示 hover 圖示
   */
  showHoverIcon: {
    type: Boolean,
    default: true
  },

  /**
   * Hover 文字是否可編輯
   */
  editableHover: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'edit-hover'])

// 雙向綁定
const localImpactData = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

// 根據類型計算顯示文字
const title = computed(() => {
  return props.type === 'negative' ? '對外負面衝擊' : '對外正面影響'
})

const sectionId = computed(() => {
  return props.type === 'negative' ? 'G-1' : 'H-1'
})

const levelLabel = computed(() => {
  return props.type === 'negative' ? '負面衝擊程度' : '正面影響程度'
})

const levelSuffix = computed(() => {
  return props.type === 'negative' ? '衝擊' : '影響'
})

const descriptionPlaceholder = computed(() => {
  return props.type === 'negative'
    ? '致使集團為士範生產據點位於水稀缺響景象風險政策一定程度之衝擊，但透過確實四有著含高地調評湖法，且覺讓落實未來高污染活道，所以負面衝擊不至於太高'
    : '50年-60年，並且目前已導入TNFD專業，對外部自然環境應常來正面影響'
})

// 預設 hover 文字
const defaultHoverText = computed(() => {
  return props.type === 'negative'
    ? '對外負面衝擊說明：企業對外部環境可能造成的負面影響'
    : '對外正面影響說明：企業對外部環境可能產生的正面影響'
})
</script>

<style scoped>
.assessment-rounded {
  @apply rounded-2xl;
}

.font_title {
  @apply font-bold text-gray-900 text-xl;
}
</style>
