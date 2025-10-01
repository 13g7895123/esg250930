<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    <!-- Table Header with Search and Actions -->
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <!-- Search (moved to left) -->
        <div class="relative" v-if="searchable">
          <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input
            v-model="searchQuery"
            type="text"
            :placeholder="searchPlaceholder"
            class="pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white w-full sm:w-64"
            @input="handleSearch"
          />
        </div>

        <!-- Action Buttons Slot (kept on right) -->
        <div v-if="$slots.actions" class="flex gap-2">
          <slot name="actions" :selected-rows="selectedRows" />
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full">
        <!-- Table Header -->
        <thead class="bg-gray-50 dark:bg-gray-700">
          <!-- Group Header Row (if groups exist and have multiple columns) -->
          <tr v-if="shouldShowGroupHeader" class="bg-gray-50 dark:bg-gray-700">
            <th v-if="selectable" class="px-6 py-3 border-b border-gray-300 dark:border-gray-600" rowspan="2">
              <input
                type="checkbox"
                :checked="isAllSelected"
                @change="toggleSelectAll"
                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
              />
            </th>
            <template v-for="(column, index) in columns" :key="`group-${column.key}`">
              <!-- Group header for columns with groups (only show once per group) -->
              <th
                v-if="column.group && isFirstInGroup(index)"
                :colspan="getGroupColspan(column.group, index)"
                class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wide border-b border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 border-l border-r border-gray-300 dark:border-gray-600"
              >
                {{ column.group }}
              </th>
              <!-- Non-grouped column spans 2 rows -->
              <th
                v-if="!column.group"
                rowspan="2"
                :class="[
                  'px-6 py-3 text-left text-sm font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider border-b border-gray-200 dark:border-gray-600 whitespace-nowrap',
                  column.sortable ? 'cursor-pointer hover:text-gray-700 dark:hover:text-gray-100' : ''
                ]"
                @click="column.sortable ? handleSort(column.key) : null"
              >
                <div class="flex items-center space-x-1">
                  <span>{{ column.label }}</span>
                  <div v-if="column.sortable" class="flex flex-col">
                    <ChevronUpIcon
                      :class="[
                        'w-3 h-3',
                        sortField === column.key && sortOrder === 'asc'
                          ? 'text-primary-600'
                          : 'text-gray-400'
                      ]"
                    />
                    <ChevronDownIcon
                      :class="[
                        'w-3 h-3 -mt-1',
                        sortField === column.key && sortOrder === 'desc'
                          ? 'text-primary-600'
                          : 'text-gray-400'
                      ]"
                    />
                  </div>
                </div>
              </th>
            </template>
          </tr>

          <!-- Column Header Row -->
          <tr v-if="!shouldShowGroupHeader || hasGroups">
            <!-- Select All Checkbox (only show if no group header) -->
            <th v-if="selectable && !shouldShowGroupHeader" class="px-6 py-3 text-left">
              <input
                type="checkbox"
                :checked="isAllSelected"
                @change="toggleSelectAll"
                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
              />
            </th>

            <!-- Column Headers -->
            <template v-for="(column, index) in columns" :key="column.key">
              <!-- Only show grouped columns in second row when group header exists -->
              <th
                v-if="!shouldShowGroupHeader || column.group"
                :class="[
                  'px-6 py-3 text-left text-sm font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider border-b border-gray-200 dark:border-gray-600 whitespace-nowrap',
                  column.sortable ? 'cursor-pointer hover:text-gray-700 dark:hover:text-gray-100' : '',
                  hasGroups && isFirstColumnInGroup(index) ? 'border-l border-gray-300 dark:border-gray-600' : (hasGroups && column.group ? 'border-l border-gray-200 dark:border-gray-600' : ''),
                  hasGroups && column.group ? 'bg-gray-100/50 dark:bg-gray-600/30' : ''
                ]"
                @click="column.sortable ? handleSort(column.key) : null"
              >
                <div class="flex items-center space-x-1">
                  <span>{{ column.label }}</span>
                  <div v-if="column.sortable" class="flex flex-col">
                    <ChevronUpIcon
                      :class="[
                        'w-3 h-3',
                        sortField === column.key && sortOrder === 'asc'
                          ? 'text-primary-600'
                          : 'text-gray-400'
                      ]"
                    />
                    <ChevronDownIcon
                      :class="[
                        'w-3 h-3 -mt-1',
                        sortField === column.key && sortOrder === 'desc'
                          ? 'text-primary-600'
                          : 'text-gray-400'
                      ]"
                    />
                  </div>
                </div>
              </th>
            </template>
          </tr>
        </thead>

        <!-- Table Body -->
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <tr 
            v-for="(item, index) in paginatedData" 
            :key="getRowKey(item, index)"
            :class="[
              'hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200',
              selectedRows.includes(getRowKey(item, index)) ? 'bg-primary-50 dark:bg-primary-900/20' : ''
            ]"
          >
            <!-- Select Checkbox -->
            <td v-if="selectable" class="px-6 py-4">
              <input
                type="checkbox"
                :checked="selectedRows.includes(getRowKey(item, index))"
                @change="toggleSelectRow(getRowKey(item, index))"
                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
              />
            </td>

            <!-- Data Cells -->
            <td
              v-for="(column, colIndex) in columns"
              :key="column.key"
              :class="[
                'px-6 py-4 whitespace-nowrap',
                hasGroups && isFirstColumnInGroup(colIndex) ? 'border-l border-gray-300 dark:border-gray-600' : (hasGroups && column.group ? 'border-l border-gray-200 dark:border-gray-700' : ''),
                hasGroups && column.group ? 'bg-gray-50/50 dark:bg-gray-700/30' : ''
              ]"
            >
              <!-- Custom Cell Content -->
              <div v-if="$slots[`cell-${column.key}`]">
                <slot :name="`cell-${column.key}`" :item="item" :value="getNestedValue(item, column.key)" />
              </div>
              
              <!-- Default Cell Content -->
              <div v-else :class="column.cellClass || 'text-base text-gray-900 dark:text-white'">
                {{ formatCellValue(item, column) }}
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Empty State -->
      <div v-if="paginatedData.length === 0" class="p-12 text-center">
        <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
          <DocumentTextIcon class="w-8 h-8 text-gray-400" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
          {{ filteredData.length === 0 && searchQuery ? noSearchResultsTitle : emptyTitle }}
        </h3>
        <p class="text-gray-500 dark:text-gray-400 mb-4">
          {{ filteredData.length === 0 && searchQuery ? noSearchResultsMessage : emptyMessage }}
        </p>
        <div v-if="$slots.emptyAction">
          <slot name="emptyAction" />
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="filteredData.length > 0" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <!-- Left Side: Data Count Info and Page Size Selector -->
        <div class="flex items-center space-x-4 text-sm">
          <!-- Results Info -->
          <div class="text-gray-500 dark:text-gray-400">
            顯示 {{ (currentPage - 1) * pageSize + 1 }} 到 {{ Math.min(currentPage * pageSize, filteredData.length) }} 
            共 {{ filteredData.length }} 筆資料
          </div>
          
          <!-- Page Size Selector -->
          <div class="flex items-center space-x-2">
            <span class="text-gray-500 dark:text-gray-400">每頁顯示</span>
            <select
              v-model="pageSize"
              @change="handlePageSizeChange"
              class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            >
              <option v-for="(size, index) in pageSizeOptions" :key="size" :value="size">
                {{ index === pageSizeOptions.length - 1 ? 'ALL' : size }}
              </option>
            </select>
            <span class="text-gray-500 dark:text-gray-400">筆</span>
          </div>
        </div>

        <!-- Right Side: Complete Pagination Controls -->
        <div class="flex items-center space-x-1">
          <!-- First Page Button -->
          <button
            @click="goToPage(1)"
            :disabled="currentPage === 1"
            class="w-8 h-8 text-sm font-medium text-gray-500 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-full hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 flex items-center justify-center"
            title="第一頁"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
            </svg>
          </button>

          <!-- Previous Button -->
          <button
            @click="goToPage(currentPage - 1)"
            :disabled="currentPage === 1"
            class="w-8 h-8 text-sm font-medium text-gray-500 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-full hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 flex items-center justify-center"
            title="上一頁"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>

          <!-- Page Numbers -->
          <div class="hidden sm:flex items-center space-x-1">
            <button
              v-for="page in visiblePages"
              :key="page"
              @click="goToPage(page)"
              :class="[
                'w-8 h-8 text-sm font-medium rounded-full transition-colors duration-200 flex items-center justify-center',
                page === currentPage
                  ? 'bg-primary-600 text-white'
                  : 'text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
              ]"
            >
              {{ page }}
            </button>
          </div>

          <!-- Next Button -->
          <button
            @click="goToPage(currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="w-8 h-8 text-sm font-medium text-gray-500 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-full hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 flex items-center justify-center"
            title="下一頁"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>

          <!-- Last Page Button -->
          <button
            @click="goToPage(totalPages)"
            :disabled="currentPage === totalPages"
            class="w-8 h-8 text-sm font-medium text-gray-500 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-full hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 flex items-center justify-center"
            title="最後一頁"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  MagnifyingGlassIcon,
  ChevronUpIcon,
  ChevronDownIcon,
  DocumentTextIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  // Data
  data: {
    type: Array,
    default: () => []
  },
  columns: {
    type: Array,
    required: true
  },
  
  // Table Configuration
  title: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  },
  rowKey: {
    type: String,
    default: 'id'
  },
  
  // Features
  searchable: {
    type: Boolean,
    default: true
  },
  selectable: {
    type: Boolean,
    default: false
  },
  sortable: {
    type: Boolean,
    default: true
  },
  
  // Search
  searchPlaceholder: {
    type: String,
    default: '搜尋...'
  },
  searchFields: {
    type: Array,
    default: () => []
  },
  
  // Pagination
  pageSize: {
    type: Number,
    default: 10
  },
  pageSizeOptions: {
    type: Array,
    default: () => [5, 10, 20, 50, 100]
  },
  
  // Empty State
  emptyTitle: {
    type: String,
    default: '暫無資料'
  },
  emptyMessage: {
    type: String,
    default: '目前沒有任何資料可以顯示'
  },
  noSearchResultsTitle: {
    type: String,
    default: '沒有找到符合的資料'
  },
  noSearchResultsMessage: {
    type: String,
    default: '請嘗試其他搜尋關鍵字'
  }
})

const emit = defineEmits(['search', 'sort', 'select', 'page-change'])

// Reactive data
const searchQuery = ref('')
const selectedRows = ref([])
const currentPage = ref(1)
const pageSize = ref(props.pageSize)
const sortField = ref('')
const sortOrder = ref('asc')

// Computed properties

// Check if any column has a group
const hasGroups = computed(() => {
  return props.columns.some(col => col.group)
})

// Calculate column groups for header
const columnGroups = computed(() => {
  if (!hasGroups.value) return []

  const groups = []
  let currentGroup = null
  let colspan = 0

  props.columns.forEach((column, index) => {
    if (column.group) {
      if (currentGroup === column.group) {
        // Same group, increase colspan
        colspan++
      } else {
        // New group, save previous group if exists
        if (currentGroup !== null) {
          groups.push({
            name: currentGroup,
            label: currentGroup,
            colspan: colspan
          })
        }
        // Start new group
        currentGroup = column.group
        colspan = 1
      }
    } else {
      // No group, save previous group if exists
      if (currentGroup !== null) {
        groups.push({
          name: currentGroup,
          label: currentGroup,
          colspan: colspan
        })
        currentGroup = null
        colspan = 0
      }
      // Add empty group for non-grouped column
      groups.push({
        name: `no-group-${index}`,
        label: '',
        colspan: 1
      })
    }
  })

  // Don't forget the last group
  if (currentGroup !== null) {
    groups.push({
      name: currentGroup,
      label: currentGroup,
      colspan: colspan
    })
  }

  return groups
})

// Check if we should show the group header row
// Only show if there are groups with multiple columns (colspan > 1)
const shouldShowGroupHeader = computed(() => {
  if (!hasGroups.value) return false
  return columnGroups.value.some(group => group.label && group.colspan > 1)
})

// Check if column is the first in its group
const isFirstColumnInGroup = (index) => {
  if (index === 0) return false
  const currentColumn = props.columns[index]
  const previousColumn = props.columns[index - 1]

  // First column with a group, or group changed
  return currentColumn.group && currentColumn.group !== previousColumn?.group
}

// Check if this is the first column in a group (for group header display)
const isFirstInGroup = (index) => {
  if (index === 0) return true
  const currentColumn = props.columns[index]
  const previousColumn = props.columns[index - 1]

  // First occurrence of this group
  return currentColumn.group && (!previousColumn || previousColumn.group !== currentColumn.group)
}

// Get colspan for a group starting at given index
const getGroupColspan = (groupName, startIndex) => {
  let count = 0
  for (let i = startIndex; i < props.columns.length; i++) {
    if (props.columns[i].group === groupName) {
      count++
    } else {
      break
    }
  }
  return count
}

const filteredData = computed(() => {
  if (!searchQuery.value) return props.data

  const query = searchQuery.value.toLowerCase()
  const fieldsToSearch = props.searchFields.length > 0
    ? props.searchFields
    : props.columns.map(col => col.key)

  return props.data.filter(item => {
    return fieldsToSearch.some(field => {
      const value = getNestedValue(item, field)
      return String(value).toLowerCase().includes(query)
    })
  })
})

const sortedData = computed(() => {
  if (!sortField.value) return filteredData.value

  return [...filteredData.value].sort((a, b) => {
    const aValue = getNestedValue(a, sortField.value)
    const bValue = getNestedValue(b, sortField.value)

    let comparison = 0
    if (aValue < bValue) comparison = -1
    if (aValue > bValue) comparison = 1

    return sortOrder.value === 'desc' ? -comparison : comparison
  })
})

const totalPages = computed(() => Math.ceil(sortedData.value.length / pageSize.value))

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  const end = start + pageSize.value
  return sortedData.value.slice(start, end)
})

const visiblePages = computed(() => {
  const pages = []
  const total = totalPages.value
  const current = currentPage.value

  if (total <= 7) {
    for (let i = 1; i <= total; i++) {
      pages.push(i)
    }
  } else {
    if (current <= 4) {
      for (let i = 1; i <= 5; i++) pages.push(i)
      pages.push('...')
      pages.push(total)
    } else if (current >= total - 3) {
      pages.push(1)
      pages.push('...')
      for (let i = total - 4; i <= total; i++) pages.push(i)
    } else {
      pages.push(1)
      pages.push('...')
      for (let i = current - 1; i <= current + 1; i++) pages.push(i)
      pages.push('...')
      pages.push(total)
    }
  }

  return pages.filter(p => p !== '...')
})

const isAllSelected = computed(() => {
  return paginatedData.value.length > 0 && 
         selectedRows.value.length === paginatedData.value.length
})

// Methods
const getRowKey = (item, index) => {
  return item[props.rowKey] || index
}

const getNestedValue = (obj, path) => {
  return path.split('.').reduce((current, key) => current?.[key], obj) ?? ''
}

const formatCellValue = (item, column) => {
  const value = getNestedValue(item, column.key)
  
  if (column.formatter && typeof column.formatter === 'function') {
    return column.formatter(value, item)
  }
  
  return value
}

const handleSearch = () => {
  currentPage.value = 1
  emit('search', searchQuery.value)
}

const handleSort = (field) => {
  if (sortField.value === field) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortOrder.value = 'asc'
  }
  
  emit('sort', { field: sortField.value, order: sortOrder.value })
}

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedRows.value = []
  } else {
    selectedRows.value = paginatedData.value.map((item, index) => getRowKey(item, index))
  }
  
  emit('select', selectedRows.value)
}

const toggleSelectRow = (key) => {
  const index = selectedRows.value.indexOf(key)
  if (index > -1) {
    selectedRows.value.splice(index, 1)
  } else {
    selectedRows.value.push(key)
  }
  
  emit('select', selectedRows.value)
}

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    emit('page-change', page)
  }
}

const handlePageSizeChange = () => {
  currentPage.value = 1
  emit('page-change', 1)
}

// Watch for data changes
watch(() => props.data, () => {
  currentPage.value = 1
  selectedRows.value = []
}, { deep: true })
</script>