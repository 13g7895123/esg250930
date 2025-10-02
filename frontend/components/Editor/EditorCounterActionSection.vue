<template>
  <div class="bg-white border border-gray-200 p-6 rounded-2xl">
    <!-- Section 標題 -->
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center space-x-3">
        <span class="font-bold text-gray-900 text-xl">
          公司報導年度是否有相關對應作為
        </span>
        <span class="bg-gray-200 text-gray-800 px-2 py-1 rounded text-base font-medium">
          D
        </span>
      </div>
      <button
        v-if="!readonly"
        class="px-3 py-1 text-white text-base rounded-full"
        style="background-color: #059669;"
      >
        紀錄
      </button>
    </div>

    <!-- 內容區 -->
    <div class="space-y-4">
      <!-- Radio 選項 -->
      <div class="grid grid-cols-2 gap-6">
        <label
          class="radio-card-option radio-card-no"
          :class="{ 'selected': localChoice === 'yes', 'readonly': readonly }"
        >
          <input
            v-model="localChoice"
            type="radio"
            value="yes"
            name="counterAction"
            class="sr-only"
            :disabled="readonly"
          />
          <div class="radio-card-content">
            <div class="radio-card-icon">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
            </div>
            <span class="radio-card-text">是</span>
          </div>
        </label>

        <label
          class="radio-card-option radio-card-yes"
          :class="{ 'selected': localChoice === 'no', 'readonly': readonly }"
        >
          <input
            v-model="localChoice"
            type="radio"
            value="no"
            name="counterAction"
            class="sr-only"
            :disabled="readonly"
          />
          <div class="radio-card-content">
            <div class="radio-card-icon">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </div>
            <span class="radio-card-text">否</span>
          </div>
        </label>
      </div>

      <!-- 對應措施描述 -->
      <div>
        <label class="text-gray-600 mt-6 mb-1">*請描述</label>
        <textarea
          v-model="localDescription"
          rows="3"
          :class="[
            'w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent',
            readonly ? 'bg-gray-50 text-gray-600 cursor-not-allowed' : ''
          ]"
          placeholder="導入TNFD請專業，進一步了解致利納於自然資源保護性"
          :readonly="readonly"
          :disabled="readonly"
        />
      </div>

      <!-- 對策費用 -->
      <div>
        <label class="text-gray-600 mt-6 mb-1">*上述對策費用</label>
        <textarea
          v-model="localCost"
          rows="2"
          :class="[
            'w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent',
            readonly ? 'bg-gray-50 text-gray-600 cursor-not-allowed' : ''
          ]"
          placeholder="顧問費用約80萬(可初估)"
          :readonly="readonly"
          :disabled="readonly"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
/**
 * EditorCounterActionSection - Section D: 對應作為區塊
 *
 * @emits update:choice - 選擇變更 (yes/no)
 * @emits update:description - 描述文字變更
 * @emits update:cost - 費用文字變更
 */

const props = defineProps({
  /**
   * 選擇值 (yes/no)
   */
  choice: {
    type: String,
    default: 'yes'
  },

  /**
   * 對應措施描述
   */
  description: {
    type: String,
    default: ''
  },

  /**
   * 對策費用
   */
  cost: {
    type: String,
    default: ''
  },

  /**
   * 是否唯讀 (preview 模式)
   */
  readonly: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:choice', 'update:description', 'update:cost'])

// 雙向綁定
const localChoice = computed({
  get: () => props.choice,
  set: (value) => emit('update:choice', value)
})

const localDescription = computed({
  get: () => props.description,
  set: (value) => emit('update:description', value)
})

const localCost = computed({
  get: () => props.cost,
  set: (value) => emit('update:cost', value)
})
</script>

<style scoped>
/* 使用與 EditorRiskEventSection 相同的樣式 */
.radio-card-option {
  @apply relative cursor-pointer block border rounded-2xl px-4 py-3 transition-all duration-200 ease-in-out;
  @apply bg-white border-gray-300 hover:shadow-md;
}

.radio-card-option.readonly {
  @apply cursor-not-allowed opacity-75;
}

.radio-card-option.radio-card-yes {
  @apply hover:border-red-400;
}

.radio-card-option.radio-card-no {
  @apply hover:border-green-400;
}

.radio-card-option.selected.radio-card-yes {
  @apply border-red-500 shadow-lg;
}

.radio-card-option.selected.radio-card-no {
  @apply border-green-500 shadow-lg;
}

.radio-card-content {
  @apply flex items-center justify-center space-x-3;
}

.radio-card-icon {
  @apply w-7 h-7 flex items-center justify-center rounded-full;
  @apply transition-all duration-200 border;
}

.radio-card-option:not(.selected) .radio-card-icon {
  @apply bg-gray-100 border-gray-300 text-gray-500;
}

.radio-card-option.selected.radio-card-yes .radio-card-icon {
  @apply bg-red-50 border-red-500 text-red-600;
}

.radio-card-option.selected.radio-card-no .radio-card-icon {
  @apply bg-green-50 border-green-500 text-green-600;
}

.radio-card-text {
  @apply text-lg font-semibold;
}

.radio-card-option:not(.selected) .radio-card-text {
  @apply text-gray-700;
}

.radio-card-option.selected.radio-card-yes .radio-card-text {
  @apply text-red-600;
}

.radio-card-option.selected.radio-card-no .radio-card-text {
  @apply text-green-600;
}

.radio-card-option.radio-card-yes:hover:not(.selected):not(.readonly) .radio-card-icon {
  @apply bg-red-50 border-red-400 text-red-500;
}

.radio-card-option.radio-card-no:hover:not(.selected):not(.readonly) .radio-card-icon {
  @apply bg-green-50 border-green-400 text-green-500;
}

@media (max-width: 640px) {
  .radio-card-content {
    @apply flex-col space-x-0 space-y-2;
  }

  .radio-card-text {
    @apply text-base;
  }
}
</style>
