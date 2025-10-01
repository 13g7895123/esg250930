<template>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-gray-700">
      <thead class="bg-gray-50 dark:bg-gray-700">
        <tr>
          <!-- 變動欄位 headers -->
          <th
            v-for="column in columns"
            :key="column.id"
            class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-600"
          >
            <div class="flex items-center justify-between">
              <input
                v-model="column.name"
                type="text"
                class="flex-1 px-2 py-1 border border-gray-300 dark:border-gray-500 rounded bg-white dark:bg-gray-600 text-gray-900 dark:text-white text-sm"
                :placeholder="columnPlaceholder"
              />
              <button
                v-if="column.removable && !readonly"
                @click="$emit('remove-column', column.id)"
                class="ml-2 p-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <!-- 額外欄位資訊（如金額說明） -->
            <slot name="column-extra" :column="column"></slot>
          </th>

          <!-- 固定欄位 headers -->
          <th
            v-for="fixedColumn in fixedColumns"
            :key="fixedColumn.key"
            class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-600"
          >
            {{ fixedColumn.label }}
          </th>

          <!-- 操作欄 -->
          <th v-if="!readonly" class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-600">
            操作
          </th>
        </tr>
      </thead>

      <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
        <tr v-for="(row, rowIndex) in rows" :key="rowIndex">
          <!-- 變動欄位 cells -->
          <td
            v-for="column in columns"
            :key="column.id"
            class="px-4 py-3 text-sm text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-600"
          >
            <input
              v-model="row.dynamicFields[column.id]"
              type="text"
              class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              placeholder="輸入值"
            />
          </td>

          <!-- 固定欄位 cells -->
          <td
            v-for="fixedColumn in fixedColumns"
            :key="fixedColumn.key"
            class="px-4 py-3 text-sm text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-600"
          >
            <input
              v-model="row[fixedColumn.key]"
              :type="fixedColumn.type || 'text'"
              class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              :placeholder="fixedColumn.placeholder"
            />
          </td>

          <!-- 操作欄 -->
          <td v-if="!readonly" class="px-4 py-3 text-sm text-center">
            <button
              @click="$emit('remove-row', rowIndex)"
              :disabled="rows.length <= 1"
              class="p-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 disabled:opacity-30 disabled:cursor-not-allowed"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
const props = defineProps({
  columns: {
    type: Array,
    required: true,
    default: () => []
  },
  rows: {
    type: Array,
    required: true,
    default: () => []
  },
  fixedColumns: {
    type: Array,
    required: true,
    default: () => []
    // Example: [
    //   { key: 'probability', label: '發生可能性程度', placeholder: '例：極低 (1-5%)', type: 'text' },
    //   { key: 'scoreRange', label: '分數級距', placeholder: '數字', type: 'number' }
    // ]
  },
  columnPlaceholder: {
    type: String,
    default: '欄位名稱'
  },
  readonly: {
    type: Boolean,
    default: false
  }
})

defineEmits(['remove-column', 'remove-row'])
</script>
