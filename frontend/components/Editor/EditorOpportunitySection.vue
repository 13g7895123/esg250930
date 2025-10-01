<template>
  <div class="bg-white assessment-rounded border border-gray-200 p-6">
    <!-- Section 標題 -->
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center space-x-3">
        <span class="font_title">相關機會</span>
        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-base font-medium flex items-center">
          F-1
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
      <p class="text-base text-gray-600">
        公司未來潛在相關機會營清說明，未來潛在機會（收入增加）、費用減少於收益等不會定
      </p>

      <!-- 機會描述 -->
      <div>
        <label class="text-gray-600 mt-6 mb-1">機會描述</label>
        <textarea
          v-model="localOpportunityData.description"
          rows="3"
          :class="[
            'w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent',
            readonly ? 'bg-gray-50 text-gray-600 cursor-not-allowed' : ''
          ]"
          placeholder="集團持續導入資源效率管理、循環經濟及生物保護措施，以降低風險並最終可能導致業務擴張，可帶來更多客戶期待"
          :readonly="readonly"
          :disabled="readonly"
        />
      </div>

      <!-- F-2 說明文字 -->
      <div class="mb-3">
        <p class="text-xl font-bold text-gray-900">
          請依上述公司盤點之機會情境評估一旦發生機會對公司之財務影響
          <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded text-sm font-medium ml-2">F-2</span>
        </p>
      </div>

      <!-- 評估表單 -->
      <div class="border border-gray-300 rounded-2xl p-4 space-y-3">
        <div class="grid grid-cols-2 gap-4">
          <!-- 機會發生可能性 -->
          <div>
            <label class="block text-base text-gray-600 mb-1">*機會發生可能性</label>
            <select
              v-model="localOpportunityData.probability"
              :class="[
                'w-full border border-gray-300 rounded-2xl px-3 py-2 text-center focus:ring-2 focus:ring-green-500 focus:border-transparent',
                readonly ? 'bg-gray-50 text-gray-600 cursor-not-allowed' : ''
              ]"
              :disabled="readonly"
            >
              <option v-for="option in probabilityOptions" :key="option.value" :value="option.value">
                {{ option.text }}
              </option>
            </select>
          </div>

          <!-- 機會發生衝擊程度 -->
          <div>
            <label class="block text-base text-gray-600 mb-1">*機會發生衝擊程度</label>
            <select
              v-model="localOpportunityData.impactLevel"
              :class="[
                'w-full border border-gray-300 rounded-2xl px-3 py-2 text-center focus:ring-2 focus:ring-green-500 focus:border-transparent',
                readonly ? 'bg-gray-50 text-gray-600 cursor-not-allowed' : ''
              ]"
              :disabled="readonly"
            >
              <option v-for="option in impactOptions" :key="option.value" :value="option.value">
                {{ option.text }}
              </option>
            </select>
          </div>
        </div>

        <!-- 計算說明 -->
        <div>
          <label class="flex items-center text-base text-gray-600 mb-1">
            *計算說明
            <InformationCircleIcon class="w-4 h-4 ml-1" />
          </label>
          <textarea
            v-model="localOpportunityData.calculation"
            rows="3"
            :class="[
              'w-full px-3 py-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent text-base',
              readonly ? 'bg-gray-50 text-gray-600 cursor-not-allowed' : ''
            ]"
            placeholder="因持續符合客戶ESG要求，可望獲加公司營收機會以帶年增加5%計算，約可帶來30億的正面效益"
            :readonly="readonly"
            :disabled="readonly"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { InformationCircleIcon } from '@heroicons/vue/24/outline'

/**
 * EditorOpportunitySection - Section F: 相關機會評估區塊
 *
 * @emits update:modelValue - 機會資料變更
 * @emits edit-hover - 編輯 hover 文字 (僅 template 模式)
 */

const props = defineProps({
  /**
   * 機會資料物件
   * {
   *   description: string,
   *   probability: number,
   *   impactLevel: number,
   *   calculation: string
   * }
   */
  modelValue: {
    type: Object,
    default: () => ({
      description: '',
      probability: 1,
      impactLevel: 3,
      calculation: ''
    })
  },

  /**
   * 可能性選項
   */
  probabilityOptions: {
    type: Array,
    default: () => [
      { value: 1, text: '1 - 極低' },
      { value: 2, text: '2 - 低' },
      { value: 3, text: '3 - 中' },
      { value: 4, text: '4 - 高' },
      { value: 5, text: '5 - 極高' }
    ]
  },

  /**
   * 衝擊選項
   */
  impactOptions: {
    type: Array,
    default: () => [
      { value: 1, text: '1 - 極低' },
      { value: 2, text: '2 - 低' },
      { value: 3, text: '3 - 中' },
      { value: 4, text: '4 - 高' },
      { value: 5, text: '5 - 極高' }
    ]
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
    default: '相關機會說明：企業可能的機會評估相關資訊'
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
const localOpportunityData = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
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
