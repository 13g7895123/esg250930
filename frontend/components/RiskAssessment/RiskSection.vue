<template>
  <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-6 rounded-2xl">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center space-x-3">
        <span class="font-bold text-gray-900 dark:text-white text-xl"><span class="text-red-500">*</span>風險情境說明</span>
        <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 px-2 py-1 rounded text-base font-medium flex items-center">E-1</span>
        <div
          class="relative group w-5 h-5 ml-2 text-white rounded-full flex items-center justify-center text-sm font-serif font-bold cursor-pointer hover:scale-110 transition-transform duration-200"
          style="background-color: #059669;"
          @click="$emit('edit-info', 'E1')"
        >
          <span class="italic">i</span>
          <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-800 dark:bg-gray-700 text-white text-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-20 max-w-xs">
            {{ infoText || '相關風險說明：企業面臨的風險評估相關資訊' }}
            <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-800 dark:border-t-gray-700"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="space-y-4">
      <p class="text-base text-gray-600 dark:text-gray-400">公司未來潛在風險情境，包含對公司營運、獲利與聲譽可能造成的負面衝擊(例如收入減少、費用增加等)。</p>
      <div>
        <label class="text-gray-600 dark:text-gray-400 mt-6 mb-1">風險描述</label>
        <textarea
          :value="e1RiskDescription"
          @input="$emit('update:e1RiskDescription', $event.target.value)"
          :disabled="disabled"
          rows="3"
          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-2xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          :placeholder="descriptionPlaceholder"
        ></textarea>
      </div>

      <!-- E-2 說明文字（在框框外面） -->
      <div class="mb-3">
        <p class="text-xl font-bold text-gray-900 dark:text-white">
          <span class="text-red-500">*</span>請依上述公司盤點之風險情境評估一旦發生風險對公司之財務影響
          <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 px-2 py-1 rounded text-base font-medium ml-2">E-2</span>
        </p>
      </div>

      <div class="border border-gray-300 dark:border-gray-600 rounded-2xl p-4 space-y-3">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-base text-gray-600 dark:text-gray-400 mb-1">風險發生可能性</label>
            <select
              :value="e2RiskProbability"
              @change="$emit('update:e2RiskProbability', parseInt($event.target.value))"
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
            <label class="block text-base text-gray-600 dark:text-gray-400 mb-1">風險發生衝擊程度</label>
            <select
              :value="e2RiskImpact"
              @change="$emit('update:e2RiskImpact', parseInt($event.target.value))"
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
            :value="e2RiskCalculation"
            @input="$emit('update:e2RiskCalculation', $event.target.value)"
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
import { onMounted } from 'vue';

const props = defineProps({
  e1RiskDescription: String,
  e2RiskProbability: [String, Number],
  e2RiskImpact: [String, Number],
  e2RiskCalculation: String,
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
    default: '請描述風險'
  },
  calculationPlaceholder: {
    type: String,
    default: '請說明計算方式'
  }
})

defineEmits([
  'update:e1RiskDescription',
  'update:e2RiskProbability',
  'update:e2RiskImpact',
  'update:e2RiskCalculation',
  'edit-info'
])

onMounted(() => {
  console.log('test' + props.e1RiskDescription)
})
</script>
