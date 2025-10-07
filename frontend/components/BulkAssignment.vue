<template>
  <div class="space-y-4">
    <!-- Personnel Selection -->
    <div>
      <div class="flex items-center justify-between mb-3">
        <h4 class="font-medium text-gray-900 dark:text-white">選擇參與指派的人員</h4>
        <button
          v-if="selectedUserIds.length > 0"
          @click="isUserSectionCollapsed = !isUserSectionCollapsed"
          class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 flex items-center gap-1"
        >
          <span>{{ isUserSectionCollapsed ? '展開' : '收折' }}</span>
          <svg
            :class="['w-4 h-4 transition-transform', isUserSectionCollapsed ? 'rotate-180' : '']"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
      </div>

      <!-- Collapsed view -->
      <div v-if="isUserSectionCollapsed && selectedUserIds.length > 0" class="border border-gray-200 dark:border-gray-600 rounded-lg p-3 bg-gray-50 dark:bg-gray-700">
        <div class="text-sm font-medium text-gray-900 dark:text-white mb-2">
          已選擇 <strong>{{ selectedUserIds.length }}</strong> 位人員
        </div>
        <div class="flex flex-wrap gap-2 mt-2">
          <div
            v-for="user in selectedUsers"
            :key="user.id"
            class="inline-flex items-center px-3 py-1.5 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg text-sm"
          >
            <div class="flex items-center space-x-2">
              <div class="w-6 h-6 bg-primary-100 dark:bg-primary-900/20 rounded-full flex items-center justify-center">
                <span class="text-primary-600 dark:text-primary-400 font-medium text-xs">
                  {{ user.name.charAt(0) }}
                </span>
              </div>
              <div class="flex flex-col">
                <span class="font-medium text-gray-900 dark:text-white">{{ user.name }}</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                  {{ user.department }} - {{ user.position }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Expanded view -->
      <div v-else>
        <!-- Search -->
        <div class="relative mb-3">
        <input
          v-model="userSearchQuery"
          type="text"
          placeholder="搜尋人員姓名、部門或職位..."
          class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
        />
        <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
      </div>

      <!-- Select All/None -->
      <div class="flex items-center space-x-4 mb-3">
        <button
          @click="selectAllUsers"
          class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
        >
          全選人員
        </button>
        <button
          @click="deselectAllUsers"
          class="text-sm text-gray-600 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
        >
          全部取消
        </button>
        <div class="text-sm text-gray-500 dark:text-gray-400">
          已選擇 {{ selectedUserIds.length }} / {{ filteredAvailableUsers.length }} 人
        </div>
      </div>

      <!-- User List -->
      <div class="max-h-48 overflow-y-auto space-y-2">
        <div
          v-for="user in filteredAvailableUsers"
          :key="user.id"
          class="border border-gray-200 dark:border-gray-600 rounded-lg p-3"
        >
          <label class="flex items-center space-x-3 cursor-pointer">
            <input
              v-model="selectedUserIds"
              :value="user.id"
              type="checkbox"
              class="form-checkbox h-4 w-4 text-primary-600"
            />
            <div class="w-8 h-8 bg-gray-200 dark:bg-gray-600 rounded-full flex items-center justify-center">
              <span class="text-gray-600 dark:text-gray-300 font-medium text-sm">
                {{ user.name.charAt(0) }}
              </span>
            </div>
            <div class="flex-1">
              <p class="font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ user.department }} - {{ user.position }}
              </p>
            </div>
          </label>
        </div>
      </div>
      </div>
    </div>

    <!-- Content Selection -->
    <div :class="isUserSectionCollapsed ? 'flex-1' : ''">
      <h4 class="font-medium text-gray-900 dark:text-white mb-3">選擇要指派的題項內容</h4>
      
      <!-- Select All/None -->
      <div class="flex items-center space-x-4 mb-3">
        <button
          @click="selectAllContent"
          class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
        >
          全選內容
        </button>
        <button
          @click="deselectAllContent"
          class="text-sm text-gray-600 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
        >
          全部取消
        </button>
        <div class="text-sm text-gray-500 dark:text-gray-400">
          已選擇 {{ selectedContentIds.length }} / {{ sortedContentSummary.length }} 項
        </div>
      </div>

      <!-- Content List -->
      <div :class="['overflow-y-auto space-y-2', isUserSectionCollapsed ? 'max-h-96' : 'max-h-48']">
        <div
          v-for="content in sortedContentSummary"
          :key="content.contentId"
          :class="[
            'border border-gray-200 dark:border-gray-600 rounded-lg p-3',
            content.isFullyAssigned ? 'opacity-50 bg-gray-50 dark:bg-gray-700/50' : ''
          ]"
        >
          <label :class="['flex items-start space-x-3', content.isFullyAssigned ? 'cursor-not-allowed' : 'cursor-pointer']">
            <input
              v-model="selectedContentIds"
              :value="content.contentId"
              type="checkbox"
              :disabled="content.isFullyAssigned"
              class="mt-1 form-checkbox h-4 w-4 text-primary-600 disabled:cursor-not-allowed disabled:opacity-50"
            />
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-1">
                <p
                  class="font-medium text-gray-900 dark:text-white cursor-help relative group"
                >
                  {{ truncateText(getFactorDescription(content.factorId), 10) || content.topic }}
                  <!-- Tooltip for full content with HTML rendering -->
                  <span
                    v-if="stripHtml(getFactorDescription(content.factorId)).length > 10"
                    class="absolute left-0 top-full mt-2 p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible z-[9999] min-w-[300px] max-w-[500px] whitespace-normal text-sm font-normal prose prose-sm dark:prose-invert max-w-none"
                    v-html="getFactorDescription(content.factorId)"
                  >
                  </span>
                </p>
                <span
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 cursor-help"
                  :title="getCategoryName(content.categoryId)"
                >
                  {{ truncateText(getCategoryName(content.categoryId), 6) }}
                </span>
                <span
                  v-if="getTopicName(content.topicId)"
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 cursor-help"
                  :title="getTopicName(content.topicId)"
                >
                  {{ truncateText(getTopicName(content.topicId), 6) }}
                </span>
                <span
                  v-if="getFactorName(content.factorId)"
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 cursor-help"
                  :title="getFactorName(content.factorId)"
                >
                  {{ truncateText(getFactorName(content.factorId), 6) }}
                </span>
              </div>
              <p class="text-sm text-gray-600 dark:text-gray-400">{{ content.description }}</p>
              <div class="mt-1 flex items-center gap-2">
                <span v-if="content.assignmentCount > 0" class="text-xs px-2 py-1 bg-yellow-100 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400 rounded-full">
                  已有 {{ content.assignmentCount }} 人指派
                </span>
                <span v-if="content.isFullyAssigned" class="text-xs px-2 py-1 bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400 rounded-full">
                  所有選中人員已指派此題目
                </span>
              </div>
            </div>
          </label>
        </div>
      </div>
    </div>

    <!-- Assignment Preview -->
    <div v-if="previewAssignments.length > 0" class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
      <h5 class="font-medium text-blue-900 dark:text-blue-200 mb-2">指派預覽</h5>
      <div class="text-sm text-blue-800 dark:text-blue-300 mb-3">
        將建立 <strong>{{ previewAssignments.length }}</strong> 個新指派
      </div>
      
      <div class="text-sm text-blue-700 dark:text-blue-300">
        每人都將分配到所有 {{ selectedContentIds.length }} 個題項內容
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex justify-end space-x-3 pt-4">
      <button
        @click="$emit('close')"
        class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors duration-200"
      >
        取消
      </button>
      <button
        :disabled="!canPerformBulkAssignment"
        @click="performBulkAssignment"
        class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors duration-200"
      >
        執行批量指派
      </button>
    </div>
  </div>
</template>

<script setup>
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  companyId: {
    type: [String, Number],
    required: true
  },
  questionId: {
    type: [String, Number],
    required: true
  },
  contentSummary: {
    type: Array,
    default: () => []
  },
  availableUsers: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['assignment-completed', 'close'])

// Assignment API composable
const { batchAssignPersonnel, isLoading } = usePersonnelAssignmentApi()

// Question structure management for categories, topics, and factors
const {
  categories: questionCategories,
  topics: questionTopics,
  factors: questionFactors,
  getCategories,
  getTopics,
  getFactors
} = useQuestionStructure()

// Reactive data
const userSearchQuery = ref('')
const selectedUserIds = ref([])
const selectedContentIds = ref([])
const isUserSectionCollapsed = ref(false)

// Computed properties
const filteredAvailableUsers = computed(() => {
  if (!userSearchQuery.value) return props.availableUsers
  
  const query = userSearchQuery.value.toLowerCase()
  return props.availableUsers.filter(user => 
    user.name.toLowerCase().includes(query) ||
    user.department.toLowerCase().includes(query) ||
    user.position.toLowerCase().includes(query)
  )
})

const selectedUsers = computed(() => 
  props.availableUsers.filter(user => selectedUserIds.value.includes(user.id))
)

const sortedContentSummary = computed(() => {
  return [...props.contentSummary].sort((a, b) => {
    const orderA = getCategoryOrder(a.categoryId)
    const orderB = getCategoryOrder(b.categoryId)
    return orderA - orderB
  }).map(content => {
    // Check if ALL selected users are already assigned to this content
    const allUsersAssigned = selectedUserIds.value.length > 0 &&
      selectedUserIds.value.every(userId =>
        content.assignedUsers?.some(assignedUser => assignedUser.userId === userId)
      )

    return {
      ...content,
      isFullyAssigned: allUsersAssigned
    }
  })
})

const selectedContents = computed(() => 
  props.contentSummary.filter(content => selectedContentIds.value.includes(content.contentId))
)

const canPerformBulkAssignment = computed(() => 
  selectedUserIds.value.length > 0 && selectedContentIds.value.length > 0
)

const previewAssignments = computed(() => {
  if (!canPerformBulkAssignment.value) return []
  
  const assignments = []
  const users = selectedUsers.value
  const contents = selectedContents.value
  
  // Always use all-to-all assignment strategy
  users.forEach(user => {
    contents.forEach(content => {
      assignments.push({
        user: user,
        content: content
      })
    })
  })
  
  return assignments
})

// Methods
const selectAllUsers = () => {
  selectedUserIds.value = filteredAvailableUsers.value.map(user => user.id)
}

const deselectAllUsers = () => {
  selectedUserIds.value = []
}

const selectAllContent = () => {
  selectedContentIds.value = sortedContentSummary.value.map(content => content.contentId)
}

const deselectAllContent = () => {
  selectedContentIds.value = []
}

const performBulkAssignment = async () => {
  if (!canPerformBulkAssignment.value) return

  const { $notify, $swal } = useNuxtApp()

  // Show loading with SweetAlert
  $swal.fire({
    title: '系統提示',
    html: '正在處理批量指派，請稍候...',
    allowOutsideClick: false,
    allowEscapeKey: false,
    didOpen: () => {
      $swal.showLoading()
    }
  })

  try {
    let totalAssigned = 0
    let totalSkipped = 0
    const errors = []

    // Group assignments by personnel (each user gets multiple contents)
    const assignmentsByPersonnel = {}
    previewAssignments.value.forEach(assignment => {
      const userId = assignment.user.id
      if (!assignmentsByPersonnel[userId]) {
        assignmentsByPersonnel[userId] = {
          user: assignment.user,
          contentIds: []
        }
      }
      assignmentsByPersonnel[userId].contentIds.push(assignment.content.contentId)
    })

    // Execute batch assignments for each personnel
    for (const [userId, data] of Object.entries(assignmentsByPersonnel)) {
      try {
        const batchData = {
          company_id: parseInt(props.companyId),
          assessment_id: parseInt(props.questionId),
          personnel_id: parseInt(userId),
          question_content_ids: data.contentIds
        }

        const result = await batchAssignPersonnel(batchData)

        if (result && result.assigned_count) {
          totalAssigned += result.assigned_count
          totalSkipped += result.skipped_count || 0
        }
      } catch (error) {
        console.error(`Error assigning to user ${userId}:`, error)
        errors.push({
          user: data.user.name,
          error: error.message
        })
      }
    }

    // Close loading and show result
    $swal.close()

    if (errors.length === 0) {
      await $swal.fire({
        icon: 'success',
        title: '批量指派完成',
        html: `成功指派 <strong>${totalAssigned}</strong> 筆${totalSkipped > 0 ? `<br>跳過 ${totalSkipped} 筆（已存在）` : ''}`,
        confirmButtonText: '確定'
      })

      // Reset form
      selectedUserIds.value = []
      selectedContentIds.value = []
      userSearchQuery.value = ''

      // Notify parent to refresh data
      emit('assignment-completed')
      emit('close')
    } else {
      await $swal.fire({
        icon: 'warning',
        title: '部分指派失敗',
        html: `成功: ${totalAssigned} 筆<br>失敗: ${errors.length} 筆<br><br>${errors.map(e => `• ${e.user}: ${e.error}`).join('<br>')}`,
        confirmButtonText: '確定'
      })

      // Still refresh data even if partial failure
      emit('assignment-completed')
    }

  } catch (error) {
    console.error('Bulk assignment error:', error)
    $swal.close()
    await $swal.fire({
      icon: 'error',
      title: '批量指派失敗',
      text: '批量指派時發生錯誤，請稍後再試',
      confirmButtonText: '確定'
    })
  }
}

// Helper method to get category name
const getCategoryName = (categoryId) => {
  if (!categoryId) return '未分類'

  if (questionCategories.value && questionCategories.value.length > 0) {
    const category = questionCategories.value.find(cat => cat.id === categoryId)
    return category ? category.category_name : '未知類別'
  }

  return '未分類'
}

const getTopicName = (topicId) => {
  if (!topicId) return ''
  if (questionTopics.value && questionTopics.value.length > 0) {
    const topic = questionTopics.value.find(t => t.id === topicId)
    return topic ? topic.topic_name : ''
  }
  return ''
}

const getFactorName = (factorId) => {
  if (!factorId) return ''
  if (questionFactors.value && questionFactors.value.length > 0) {
    const factor = questionFactors.value.find(f => f.id === factorId)
    return factor ? factor.factor_name : ''
  }
  return ''
}

const getFactorDescription = (factorId) => {
  if (!factorId) return ''
  if (questionFactors.value && questionFactors.value.length > 0) {
    const factor = questionFactors.value.find(f => f.id === factorId)
    return factor ? (factor.description || '') : ''
  }
  return ''
}

const stripHtml = (html) => {
  if (!html) return ''
  const tmp = document.createElement('div')
  tmp.innerHTML = html
  return tmp.textContent || tmp.innerText || ''
}

const truncateText = (text, maxLength = 10) => {
  if (!text) return ''
  const plainText = stripHtml(text)
  if (plainText.length <= maxLength) return plainText
  return plainText.substring(0, maxLength) + '...'
}

// Helper method to get category order (index in the categories array)
const getCategoryOrder = (categoryId) => {
  if (!categoryId) return 999

  if (questionCategories.value && questionCategories.value.length > 0) {
    const categoryIndex = questionCategories.value.findIndex(cat => cat.id === categoryId)
    return categoryIndex !== -1 ? categoryIndex : 999
  }

  return 999
}

// Watch for changes in selected users to automatically unselect fully assigned content
watch(selectedUserIds, () => {
  // Remove content IDs that are now fully assigned
  const fullyAssignedContentIds = sortedContentSummary.value
    .filter(content => content.isFullyAssigned)
    .map(content => content.contentId)

  if (fullyAssignedContentIds.length > 0) {
    selectedContentIds.value = selectedContentIds.value.filter(
      id => !fullyAssignedContentIds.includes(id)
    )
  }
})

// Load question structure data when component mounts
onMounted(async () => {
  try {
    if (props.questionId) {
      await Promise.all([
        getCategories(props.questionId),
        getTopics(props.questionId),
        getFactors(props.questionId)
      ])
    }
  } catch (error) {
    console.error('Error loading structure data in BulkAssignment:', error)
  }
})
</script>