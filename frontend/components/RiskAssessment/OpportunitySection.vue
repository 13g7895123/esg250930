<template>
  <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-6 rounded-2xl">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center space-x-3">
        <span class="font-bold text-gray-900 dark:text-white text-xl"><span class="text-red-500">*</span>機會情境說明</span>
        <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 px-2 py-1 rounded text-base font-medium flex items-center">F-1</span>
        <div
          class="relative group w-5 h-5 ml-2 text-white rounded-full flex items-center justify-center text-sm font-serif font-bold cursor-pointer hover:scale-110 transition-transform duration-200"
          style="background-color: #059669;"
          @click="$emit('edit-info', 'F1')"
        >
          <span class="italic">i</span>
          <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-800 dark:bg-gray-700 text-white text-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-20 max-w-xs">
            {{ infoText || '相關機會說明：企業可能的機會評估相關資訊' }}
            <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-800 dark:border-t-gray-700"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="space-y-4">
      <p class="text-base text-gray-600 dark:text-gray-400">公司未來潛在機會情境，包含對公司營運、獲利與聲譽可能造成的正面影響(例如收入增加、費用減少、靭性提高等)。</p>
      <div>
        <label class="text-gray-600 dark:text-gray-400 mt-6 mb-1">機會描述</label>
        <textarea
          :value="f1OpportunityDescription"
          @input="$emit('update:f1OpportunityDescription', $event.target.value)"
          :disabled="disabled"
          rows="3"
          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-2xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          :placeholder="descriptionPlaceholder"
        ></textarea>
      </div>

      <!-- F-2 說明文字（在框框外面） -->
      <div class="mb-3">
        <p class="text-xl font-bold text-gray-900 dark:text-white">
          <span class="text-red-500">*</span>請依上述公司盤點之機會情境評估一旦發生機會對公司之財務影響
          <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 px-2 py-1 rounded text-base font-medium ml-2">F-2</span>
        </p>
      </div>

      <div class="border border-gray-300 dark:border-gray-600 rounded-2xl p-4 space-y-3">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-base text-gray-600 dark:text-gray-400 mb-1">機會發生可能性</label>
            <select
              :value="f2OpportunityProbability"
              @change="$emit('update:f2OpportunityProbability', parseInt($event.target.value))"
              :disabled="disabled"
              class="w-full border border-gray-300 dark:border-gray-600 rounded-2xl px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <option value="">請選擇</option>
              <option v-for="option in probabilityOptions" :key="option.value" :value="option.value">
                {{ option.text }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-base text-gray-600 dark:text-gray-400 mb-1">機會發生衝擊程度</label>
            <select
              :value="f2OpportunityImpact"
              @change="$emit('update:f2OpportunityImpact', parseInt($event.target.value))"
              :disabled="disabled"
              class="w-full border border-gray-300 dark:border-gray-600 rounded-2xl px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <option value="">請選擇</option>
              <option v-for="option in impactOptions" :key="option.value" :value="option.value">
                {{ option.text }}
              </option>
            </select>
          </div>
        </div>
        <div>
          <label class="flex items-center text-base text-gray-600 dark:text-gray-400 mb-1">計算說明</label>
          <textarea
            :value="f2OpportunityCalculation"
            @input="$emit('update:f2OpportunityCalculation', $event.target.value)"
            :disabled="disabled"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-2xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            :placeholder="calculationPlaceholder"
          ></textarea>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  f1OpportunityDescription: String,
  f2OpportunityProbability: [String, Number],
  f2OpportunityImpact: [String, Number],
  f2OpportunityCalculation: String,
  probabilityOptions: {
    type: Array,
    default: () => []
  },
  impactOptions: {
    type: Array,
    default: () => []
  },
  disabled: {
    type: Boolean,
    default: false
  },
  infoText: {
    type: String,
    default: ''
  },
  descriptionPlaceholder: {
    type: String,
    default: '請描述機會'
  },
  calculationPlaceholder: {
    type: String,
    default: '請說明計算方式'
  }
})

defineEmits([
  'update:f1OpportunityDescription',
  'update:f2OpportunityProbability',
  'update:f2OpportunityImpact',
  'update:f2OpportunityCalculation',
  'edit-info'
])
</script>
