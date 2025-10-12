<template>
  <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-6 rounded-2xl">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center space-x-3">
        <span class="font-bold text-gray-900 dark:text-white text-xl">對外負面衝擊</span>
        <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 px-2 py-1 rounded text-base font-medium flex items-center">G-1</span>
        <div
          class="relative group w-5 h-5 ml-2 text-white rounded-full flex items-center justify-center text-sm font-serif font-bold cursor-pointer hover:scale-110 transition-transform duration-200"
          style="background-color: #059669;"
          @click="$emit('edit-info', 'G1')"
        >
          <span class="italic">i</span>
          <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-800 dark:bg-gray-700 text-white text-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-20 max-w-xs">
            {{ infoText || '根據公司與該議題相關性【A】、【B】內容及上述風險情境【C】、 【E-1】內容，綜合評估公司之營運對此議題可能造成的「外部」負面衝擊（對外部經濟、環境、人群(包含其人權)之負面影響），並盡可能簡述其評分說明' }}
            <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-800 dark:border-t-gray-700"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="space-y-4">
      <div>
        <label class="block text-base text-gray-600 dark:text-gray-400 mb-1">負面衝擊程度</label>
        <select
          :value="g1NegativeImpactLevel"
          @change="$emit('update:g1NegativeImpactLevel', parseInt($event.target.value))"
          :disabled="disabled"
          class="w-full border border-gray-300 dark:border-gray-600 rounded-2xl px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <option value="">請選擇</option>
          <option value="1">1</option>
          <option value="2">2</option>
          <option value="3">3</option>
          <option value="4">4</option>
          <option value="5">5</option>
        </select>
      </div>
      <div>
        <label class="block text-base text-gray-600 dark:text-gray-400 mb-1">評分說明</label>
        <textarea
          :value="g1NegativeImpactDescription"
          @input="$emit('update:g1NegativeImpactDescription', $event.target.value)"
          :disabled="disabled"
          rows="4"
          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-2xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          :placeholder="descriptionPlaceholder"
        ></textarea>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  g1NegativeImpactLevel: [String, Number],
  g1NegativeImpactDescription: String,
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
    default: '請說明負面衝擊'
  }
})

defineEmits([
  'update:g1NegativeImpactLevel',
  'update:g1NegativeImpactDescription',
  'edit-info'
])
</script>
