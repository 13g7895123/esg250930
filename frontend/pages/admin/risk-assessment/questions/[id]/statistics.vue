<template>
  <div class="p-6">
    <!-- Page Header -->
    <div class="mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
          評估表統計結果
        </h1>
        <p v-if="assessmentInfo" class="text-gray-600 dark:text-gray-400">
          {{ assessmentInfo.template_version }} - {{ assessmentInfo.year }}年
        </p>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
      <p class="text-red-800 dark:text-red-200">{{ error }}</p>
    </div>

    <!-- Data Table -->
    <DataTable
      v-else-if="results"
      :data="results"
      :columns="visibleColumns"
      search-placeholder="搜尋部門、姓名、描述..."
      :search-fields="['department', 'user_name', 'description', 'category_name', 'topic_name', 'factor_name']"
      empty-title="沒有統計資料"
      empty-message="此評估記錄尚無回答資料"
      no-search-results-title="沒有找到符合的資料"
      no-search-results-message="請嘗試其他搜尋關鍵字"
    >
      <!-- Action buttons in table header -->
      <template #actions>
        <div class="flex gap-2">
          <button
            @click="showColumnSelector = true"
            class="flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
            設定欄位
          </button>
          <button
            @click="loadStatistics"
            :disabled="loading"
            class="flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
          >
            <svg class="w-4 h-4 mr-2" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            重新載入
          </button>
        </div>
      </template>
      <!-- Custom cells for specific columns -->
      <template #cell-department_name="{ item }">
        <div class="text-sm">
          <div class="font-medium text-gray-900 dark:text-white">{{ item.department || '-' }}</div>
          <div class="text-gray-500 dark:text-gray-400">{{ item.user_name || '-' }}</div>
        </div>
      </template>

      <template #cell-c_risk_event_choice="{ item }">
        <span v-if="item.c_risk_event_choice" class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"
          :class="item.c_risk_event_choice.toLowerCase() === 'yes'
            ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300'
            : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
        >
          {{ item.c_risk_event_choice.toLowerCase() === 'yes' ? '是' : '否' }}
        </span>
        <span v-else class="text-gray-400">-</span>
      </template>

      <template #cell-d_counter_action_choice="{ item }">
        <span v-if="item.d_counter_action_choice" class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"
          :class="item.d_counter_action_choice.toLowerCase() === 'yes'
            ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300'
            : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
        >
          {{ item.d_counter_action_choice.toLowerCase() === 'yes' ? '是' : '否' }}
        </span>
        <span v-else class="text-gray-400">-</span>
      </template>

      <template #cell-e1_risk_probability="{ item }">
        {{ item.e1_risk_probability || '-' }}
      </template>

      <template #cell-e1_risk_impact="{ item }">
        {{ item.e1_risk_impact || '-' }}
      </template>

      <template #cell-e1_risk_calculation="{ item }">
        {{ item.e1_risk_calculation || '-' }}
      </template>

      <template #cell-f1_opportunity_probability="{ item }">
        {{ item.f1_opportunity_probability || '-' }}
      </template>

      <template #cell-f1_opportunity_impact="{ item }">
        {{ item.f1_opportunity_impact || '-' }}
      </template>

      <template #cell-f1_opportunity_calculation="{ item }">
        {{ item.f1_opportunity_calculation || '-' }}
      </template>

      <template #cell-g1_negative_impact_level="{ item }">
        {{ item.g1_negative_impact_level || '-' }}
      </template>

      <template #cell-h1_positive_impact_level="{ item }">
        {{ item.h1_positive_impact_level || '-' }}
      </template>

      <template #cell-description="{ item }">
        <div class="max-w-xs">
          <div class="line-clamp-2" :title="item.description">
            {{ item.description || '-' }}
          </div>
        </div>
      </template>
    </DataTable>

    <!-- Column Selector Modal -->
    <div v-if="showColumnSelector" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showColumnSelector = false"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
          <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                選擇顯示欄位
              </h3>
              <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500 dark:text-gray-400">
                  已選 {{ selectedColumnKeys.length }} / {{ allColumns.length }}
                </span>
                <button
                  @click="toggleSelectAll"
                  class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium"
                >
                  {{ isAllColumnsSelected ? '取消全選' : '全選' }}
                </button>
              </div>
            </div>

            <div class="space-y-4 max-h-[32rem] overflow-y-auto pr-2">
              <!-- 基本資訊欄位 -->
              <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center">
                  <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                  基本資訊
                </h4>
                <div class="grid grid-cols-2 gap-2">
                  <label v-for="column in groupedColumns.basic" :key="column.key" class="flex items-center space-x-2 p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded cursor-pointer">
                    <input
                      type="checkbox"
                      :checked="selectedColumnKeys.includes(column.key)"
                      @change="toggleColumn(column.key)"
                      class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                    />
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ column.label }}</span>
                  </label>
                </div>
              </div>

              <!-- 分組欄位 -->
              <div v-for="groupName in groupNames" :key="groupName" class="border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center">
                  <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                  {{ groupName }}
                </h4>
                <div class="grid grid-cols-2 gap-2">
                  <label v-for="column in groupedColumns[groupName]" :key="column.key" class="flex items-center space-x-2 p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded cursor-pointer">
                    <input
                      type="checkbox"
                      :checked="selectedColumnKeys.includes(column.key)"
                      @change="toggleColumn(column.key)"
                      class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                    />
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ column.label }}</span>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              type="button"
              @click="showColumnSelector = false"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm"
            >
              確定
            </button>
            <button
              type="button"
              @click="resetColumns"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              重設為預設
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import DataTable from '~/components/DataTable.vue'

definePageMeta({
  middleware: 'auth'
})

const route = useRoute()
const assessmentId = computed(() => route.params.id)

// Data
const loading = ref(true)
const error = ref(null)
const assessmentInfo = ref(null)
const results = ref([])
const summary = ref({
  total_contents: 0,
  total_responses: 0,
  total_records: 0
})

// Column selector
const showColumnSelector = ref(false)

// 定義所有可用的欄位（按照英文代碼分組）
const allColumns = [
  // 基本資訊欄位（無分組）
  { key: 'order', label: '編號', sortable: true, default: true },
  { key: 'department_name', label: '部門/姓名', sortable: true, default: true },
  { key: 'category_name', label: '風險類別', sortable: true, default: true },
  { key: 'topic_name', label: '風險主題', sortable: true, default: true },
  { key: 'factor_name', label: '風險因子', sortable: true, default: true },
  { key: 'description', label: '描述', sortable: false, default: true },

  // C 風險事件
  { key: 'c_risk_event_choice', label: 'C風險事件', sortable: true, default: true, group: 'C 風險事件' },
  { key: 'c_risk_event_description', label: 'C風險事件描述', sortable: false, default: false, group: 'C 風險事件' },

  // D 因應行動
  { key: 'd_counter_action_choice', label: 'D因應行動', sortable: true, default: true, group: 'D 因應行動' },
  { key: 'd_counter_action_description', label: 'D因應行動描述', sortable: false, default: false, group: 'D 因應行動' },

  // E1 風險評估
  { key: 'e1_risk_probability', label: 'E1風險可能性', sortable: true, default: true, group: 'E1 風險評估' },
  { key: 'e1_risk_impact', label: 'E1風險衝擊', sortable: true, default: true, group: 'E1 風險評估' },
  { key: 'e1_risk_calculation', label: 'E1風險分數', sortable: true, default: true, group: 'E1 風險評估' },
  { key: 'e1_risk_description', label: 'E1風險描述', sortable: false, default: false, group: 'E1 風險評估' },

  // F1 機會評估
  { key: 'f1_opportunity_probability', label: 'F1機會可能性', sortable: true, default: true, group: 'F1 機會評估' },
  { key: 'f1_opportunity_impact', label: 'F1機會衝擊', sortable: true, default: true, group: 'F1 機會評估' },
  { key: 'f1_opportunity_calculation', label: 'F1機會分數', sortable: true, default: true, group: 'F1 機會評估' },
  { key: 'f1_opportunity_description', label: 'F1機會描述', sortable: false, default: false, group: 'F1 機會評估' },

  // G1 對外負面衝擊
  { key: 'g1_negative_impact_level', label: '負面衝擊程度', sortable: true, default: true, group: '【G-1】對外負面衝擊' },
  { key: 'g1_negative_impact_description', label: '評分說明', sortable: false, default: false, group: '【G-1】對外負面衝擊' },

  // H1 對外正面影響
  { key: 'h1_positive_impact_level', label: '正面影響程度', sortable: true, default: true, group: '【H-1】對外正面影響' },
  { key: 'h1_positive_impact_description', label: '評分說明', sortable: false, default: false, group: '【H-1】對外正面影響' },
]

// 預設選中的欄位
const defaultColumnKeys = allColumns.filter(col => col.default).map(col => col.key)
const selectedColumnKeys = ref([...defaultColumnKeys])

// 計算顯示的欄位
const visibleColumns = computed(() => {
  return allColumns.filter(col => selectedColumnKeys.value.includes(col.key))
})

// 檢查是否全選
const isAllColumnsSelected = computed(() => {
  return selectedColumnKeys.value.length === allColumns.length
})

// 將欄位按 group 分組
const groupedColumns = computed(() => {
  const groups = {
    basic: []
  }

  allColumns.forEach(column => {
    if (!column.group) {
      groups.basic.push(column)
    } else {
      if (!groups[column.group]) {
        groups[column.group] = []
      }
      groups[column.group].push(column)
    }
  })

  return groups
})

// 取得所有 group 名稱（排除 basic）
const groupNames = computed(() => {
  return Object.keys(groupedColumns.value).filter(key => key !== 'basic')
})

// Toggle column visibility
const toggleColumn = (key) => {
  const index = selectedColumnKeys.value.indexOf(key)
  if (index > -1) {
    // 至少保留一個欄位
    if (selectedColumnKeys.value.length > 1) {
      selectedColumnKeys.value.splice(index, 1)
    }
  } else {
    selectedColumnKeys.value.push(key)
  }
}

// Toggle select all columns
const toggleSelectAll = () => {
  if (isAllColumnsSelected.value) {
    // 取消全選，恢復為預設
    selectedColumnKeys.value = [...defaultColumnKeys]
  } else {
    // 全選
    selectedColumnKeys.value = allColumns.map(col => col.key)
  }
}

// Reset to default columns
const resetColumns = () => {
  selectedColumnKeys.value = [...defaultColumnKeys]
}

// Load statistics data
const loadStatistics = async () => {
  try {
    loading.value = true
    error.value = null

    console.log('Loading statistics for assessment:', assessmentId.value)

    const response = await $fetch(`/api/v1/question-management/assessment/${assessmentId.value}/statistics-results`)

    console.log('Statistics API response:', response)

    if (response.success) {
      assessmentInfo.value = response.data.assessment
      results.value = response.data.results || []
      summary.value = response.data.summary || {
        total_contents: 0,
        total_responses: 0,
        total_records: 0
      }

      console.log('Statistics loaded successfully:', {
        assessment: assessmentInfo.value,
        recordsCount: results.value.length,
        summary: summary.value
      })
    } else {
      error.value = response.message || '載入統計資料失敗'
    }
  } catch (err) {
    console.error('Error loading statistics:', err)
    console.error('Error details:', {
      message: err.message,
      data: err.data,
      statusCode: err.statusCode
    })

    // 更詳細的錯誤訊息
    if (err.data?.message) {
      error.value = err.data.message
    } else if (err.statusCode === 404) {
      error.value = '找不到指定的評估記錄'
    } else if (err.statusCode === 500) {
      error.value = '伺服器錯誤，請稍後再試'
    } else {
      error.value = `載入統計資料時發生錯誤: ${err.message || '未知錯誤'}`
    }
  } finally {
    loading.value = false
  }
}

// Load on mount
onMounted(() => {
  loadStatistics()
})

// Set page title
const pageTitle = computed(() => `評估表統計結果`)
usePageTitle(pageTitle)
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
