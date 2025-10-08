<template>
  <div>
    <!-- Modal Overlay -->
    <div
      v-if="modelValue"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="closeModal"
    >
      <!-- Modal Container -->
      <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-4xl flex flex-col"
        style="max-height: 90vh;"
      >

        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
            指派歷史記錄
          </h3>
          <button
            @click="closeModal"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Filters -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
          <div class="flex gap-4 flex-wrap">
            <!-- Action Type Filter -->
            <select
              v-model="filters.action_type"
              class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              @change="loadHistory"
            >
              <option value="">全部操作</option>
              <option value="created">新增</option>
              <option value="removed">移除</option>
              <option value="updated">更新</option>
            </select>

            <!-- Statistics Badge -->
            <div v-if="statistics" class="flex gap-2 items-center">
              <span class="px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full text-sm">
                新增: {{ statistics.created }}
              </span>
              <span class="px-3 py-1 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-full text-sm">
                移除: {{ statistics.removed }}
              </span>
              <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm">
                更新: {{ statistics.updated }}
              </span>
            </div>
          </div>
        </div>

        <!-- Modal Body -->
        <div class="flex-1 overflow-y-auto p-6">
          <!-- Loading State -->
          <div v-if="loading" class="flex items-center justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
          </div>

          <!-- No Data -->
          <div v-else-if="!history || history.length === 0" class="text-center py-12 text-gray-500 dark:text-gray-400">
            暫無歷史記錄
          </div>

          <!-- History Timeline -->
          <div v-else class="space-y-4">
            <div
              v-for="record in history"
              :key="record.id"
              class="flex gap-4 relative"
            >
              <!-- Timeline Line -->
              <div class="flex flex-col items-center">
                <!-- Icon -->
                <div
                  class="w-10 h-10 rounded-full flex items-center justify-center"
                  :class="getActionColor(record.action_type)"
                >
                  <component :is="getActionIcon(record.action_type)" class="w-5 h-5" />
                </div>
                <!-- Vertical Line -->
                <div
                  v-if="record !== history[history.length - 1]"
                  class="w-0.5 flex-1 bg-gray-200 dark:bg-gray-700 mt-2"
                  style="min-height: 20px;"
                ></div>
              </div>

              <!-- Content -->
              <div class="flex-1 pb-8">
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                  <!-- Action Header -->
                  <div class="flex items-start justify-between mb-2">
                    <div>
                      <span
                        class="inline-block px-2 py-1 rounded text-sm font-medium"
                        :class="getActionBadgeClass(record.action_type)"
                      >
                        {{ getActionLabel(record.action_type) }}
                      </span>
                      <h4 class="mt-2 font-medium text-gray-900 dark:text-white">
                        {{ record.personnel_name }}
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                          ({{ record.personnel_department || '未指定部門' }})
                        </span>
                      </h4>
                    </div>
                    <time class="text-sm text-gray-500 dark:text-gray-400">
                      {{ formatDateTime(record.action_at) }}
                    </time>
                  </div>

                  <!-- Record Details -->
                  <div class="mt-3 text-sm text-gray-600 dark:text-gray-300 space-y-1">
                    <div v-if="record.action_by_name">
                      操作人員：{{ record.action_by_name }}
                    </div>
                    <div v-if="record.action_reason">
                      原因：{{ record.action_reason }}
                    </div>
                    <div v-if="record.assignment_note">
                      備註：{{ record.assignment_note }}
                    </div>
                    <div v-if="record.original_assigned_at && record.action_type === 'removed'">
                      原始指派時間：{{ formatDateTime(record.original_assigned_at) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end gap-3 p-6 border-t border-gray-200 dark:border-gray-700">
          <button
            @click="closeModal"
            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600"
          >
            關閉
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { usePersonnelAssignmentApi } from '~/composables/usePersonnelAssignmentApi'
import { PlusCircleIcon, MinusCircleIcon, PencilSquareIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  companyId: {
    type: Number,
    required: true
  },
  assessmentId: {
    type: Number,
    required: true
  },
  contentId: {
    type: String,
    default: null
  },
  personnelId: {
    type: Number,
    default: null
  }
})

const emit = defineEmits(['update:modelValue', 'close'])

const { getAssignmentHistory, getContentHistory, getPersonnelHistory, getHistoryStatistics } = usePersonnelAssignmentApi()

const loading = ref(false)
const history = ref([])
const statistics = ref(null)
const filters = ref({
  action_type: ''
})

// Load history when modal opens
watch(() => props.modelValue, async (newValue) => {
  if (newValue) {
    await loadHistory()
    await loadStatistics()
  }
})

const loadHistory = async () => {
  if (!props.companyId || !props.assessmentId) return

  loading.value = true
  try {
    let data = []

    if (props.contentId) {
      // Load content-specific history
      data = await getContentHistory(props.companyId, props.assessmentId, props.contentId)
    } else if (props.personnelId) {
      // Load personnel-specific history
      data = await getPersonnelHistory(props.companyId, props.assessmentId, props.personnelId)
    } else {
      // Load all history with filters
      data = await getAssignmentHistory(props.companyId, props.assessmentId, filters.value)
    }

    history.value = data
  } catch (error) {
    console.error('載入歷史記錄失敗:', error)
  } finally {
    loading.value = false
  }
}

const loadStatistics = async () => {
  if (!props.companyId || !props.assessmentId) return

  try {
    statistics.value = await getHistoryStatistics(props.companyId, props.assessmentId)
  } catch (error) {
    console.error('載入統計資訊失敗:', error)
  }
}

const closeModal = () => {
  emit('update:modelValue', false)
  emit('close')
}

const getActionIcon = (actionType) => {
  switch (actionType) {
    case 'created':
      return PlusCircleIcon
    case 'removed':
      return MinusCircleIcon
    case 'updated':
      return PencilSquareIcon
    default:
      return PencilSquareIcon
  }
}

const getActionColor = (actionType) => {
  switch (actionType) {
    case 'created':
      return 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-300'
    case 'removed':
      return 'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-300'
    case 'updated':
      return 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300'
    default:
      return 'bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-300'
  }
}

const getActionBadgeClass = (actionType) => {
  switch (actionType) {
    case 'created':
      return 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'
    case 'removed':
      return 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'
    case 'updated':
      return 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200'
    default:
      return 'bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200'
  }
}

const getActionLabel = (actionType) => {
  switch (actionType) {
    case 'created':
      return '新增指派'
    case 'removed':
      return '移除指派'
    case 'updated':
      return '更新指派'
    default:
      return '未知操作'
  }
}

const formatDateTime = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleString('zh-TW', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>
