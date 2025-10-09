<template>
  <div class="space-y-4">
    <!-- Step 1: Select User -->
    <div>
      <div class="flex items-center justify-between mb-3">
        <h4 class="font-medium text-gray-900 dark:text-white">選擇要指派的人員</h4>
        <div v-if="selectedUser" class="text-sm text-gray-600 dark:text-gray-400">
          已選擇：{{ selectedUser.name }}
        </div>
      </div>

      <!-- Collapsed view when user is selected -->
      <div v-if="selectedUser && isUserSectionCollapsed" class="border border-gray-200 dark:border-gray-600 rounded-lg p-3 bg-gray-50 dark:bg-gray-700">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900/20 rounded-full flex items-center justify-center">
              <span class="text-primary-600 dark:text-primary-400 font-medium text-sm">
                {{ selectedUser.name.charAt(0) }}
              </span>
            </div>
            <div>
              <p class="font-medium text-gray-900 dark:text-white">{{ selectedUser.name }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ selectedUser.department }} - {{ selectedUser.position }}
              </p>
            </div>
          </div>
          <button
            @click="isUserSectionCollapsed = false"
            class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400"
          >
            變更
          </button>
        </div>
      </div>

      <!-- Expanded view -->
      <div v-else>
        <!-- User Search -->
        <div class="relative mb-3">
          <input
            v-model="userSearchQuery"
            type="text"
            placeholder="搜尋人員姓名、部門或職位..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
          />
          <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
        </div>

        <!-- Available Users -->
        <div class="max-h-48 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg">
        <div v-if="filteredAvailableUsers.length === 0" class="p-4 text-center text-gray-500 dark:text-gray-400">
          <p>沒有找到符合條件的人員</p>
        </div>
        <div v-else class="divide-y divide-gray-200 dark:divide-gray-600">
          <div
            v-for="user in filteredAvailableUsers"
            :key="user.id"
            class="p-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors duration-200"
            :class="{ 'bg-primary-50 dark:bg-primary-900/20': selectedUser?.id === user.id }"
            @click="selectUser(user)"
          >
            <div class="flex items-center space-x-3">
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
              <div v-if="selectedUser?.id === user.id" class="text-primary-600 dark:text-primary-400">
                <CheckIcon class="w-5 h-5" />
              </div>
            </div>
          </div>
        </div>
      </div>
      </div>
    </div>

    <!-- Step 2: Select Content Items -->
    <div v-if="selectedUser" :class="isUserSectionCollapsed ? 'flex-1' : ''">
      <h4 class="font-medium text-gray-900 dark:text-white mb-3">
        選擇要指派給 {{ selectedUser.name }} 的題項內容
      </h4>
      
      <!-- Select All/None -->
      <div class="flex items-center space-x-4 mb-3">
        <button
          @click="selectAllContent"
          class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
        >
          全選
        </button>
        <button
          @click="deselectAllContent"
          class="text-sm text-gray-600 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
        >
          全部取消
        </button>
        <div class="text-sm text-gray-500 dark:text-gray-400">
          已選擇 {{ selectedContentIds.length }} / {{ availableContentItems.length }} 項
        </div>
      </div>

      <!-- Content Items -->
      <div :class="['overflow-y-auto space-y-2', isUserSectionCollapsed ? 'max-h-96' : 'max-h-64']">
        <div
          v-for="content in availableContentItems"
          :key="content.contentId"
          :class="[
            'border rounded-lg p-3',
            content.alreadyAssigned
              ? 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 opacity-60'
              : 'border-gray-200 dark:border-gray-600'
          ]"
        >
          <label :class="['flex items-start space-x-3', content.alreadyAssigned ? 'cursor-not-allowed' : 'cursor-pointer']">
            <input
              v-model="selectedContentIds"
              :value="content.contentId"
              :disabled="content.alreadyAssigned"
              type="checkbox"
              :class="[
                'mt-1 form-checkbox h-4 w-4',
                content.alreadyAssigned ? 'text-gray-400 cursor-not-allowed' : 'text-primary-600'
              ]"
            />
            <div class="flex-1 min-w-0">
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
                <BadgeWithTooltip
                  :text="getCategoryName(content.categoryId || content.category_id)"
                  variant="category"
                />
                <BadgeWithTooltip
                  v-if="getTopicName(content.topicId)"
                  :text="getTopicName(content.topicId)"
                  variant="topic"
                />
                <BadgeWithTooltip
                  v-if="getFactorName(content.factorId)"
                  :text="getFactorName(content.factorId)"
                  variant="factor"
                />
              </div>
              <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ content.description }}</p>
              <div class="mt-2 flex gap-2">
                <span v-if="content.alreadyAssigned" class="text-xs px-2 py-1 bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400 rounded-full">
                  已指派給此人員
                </span>
                <span v-else-if="content.assignmentCount > 0" class="text-xs px-2 py-1 bg-yellow-100 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400 rounded-full">
                  已有 {{ content.assignmentCount }} 人指派
                </span>
              </div>
            </div>
          </label>
        </div>
      </div>
    </div>

    <!-- Assignment Preview -->
    <div v-if="selectedUser && selectedContentIds.length > 0" class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
      <h5 class="font-medium text-blue-900 dark:text-blue-200 mb-2">指派預覽</h5>
      <div class="text-sm text-blue-800 dark:text-blue-300">
        將指派 <strong>{{ selectedUser.name }}</strong> 到 <strong>{{ selectedContentIds.length }}</strong> 個題項內容
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
        :disabled="!canAssign || isLoading"
        @click="performAssignment"
        class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors duration-200"
      >
        <svg v-if="isLoading" class="animate-spin w-4 h-4 mr-2 inline" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        {{ isLoading ? '處理中...' : '確認指派' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { MagnifyingGlassIcon, CheckIcon } from '@heroicons/vue/24/outline'

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

// Assignment composable (using API instead of local storage)
const { batchAssignPersonnel, isLoading, isPersonnelAssignedToContent } = usePersonnelAssignmentApi()

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
const selectedUser = ref(null)
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

const availableContentItems = computed(() => {
  if (!selectedUser.value) return props.contentSummary

  // Show all content items, but indicate which ones are already assigned to this user
  const contentWithAssignment = props.contentSummary.map(content => {
    return {
      ...content,
      alreadyAssigned: selectedUser.value ? isPersonnelAssignedToContent(
        props.companyId,
        props.questionId,
        content.contentId,
        selectedUser.value.id
      ) : false
    }
  })

  // 直接使用後端回傳的資料順序，不再進行前端排序
  return contentWithAssignment
})

const canAssign = computed(() => 
  selectedUser.value && selectedContentIds.value.length > 0
)

// Watch for user selection to auto-collapse
watch(selectedUser, (newUser) => {
  if (newUser) {
    // Auto-collapse when user is selected
    isUserSectionCollapsed.value = true
  } else {
    // Expand when user is cleared
    isUserSectionCollapsed.value = false
  }
})

// Methods
const selectUser = (user) => {
  selectedUser.value = user
  selectedContentIds.value = []
  userSearchQuery.value = ''
}

const selectAllContent = () => {
  selectedContentIds.value = availableContentItems.value
    .filter(content => !content.alreadyAssigned)
    .map(content => content.contentId)
}

const deselectAllContent = () => {
  selectedContentIds.value = []
}

const getContentById = (contentId) => {
  return props.contentSummary.find(content => content.contentId === contentId)
}

const performAssignment = async () => {
  if (!canAssign.value) return

  try {
    // 驗證選中的內容ID - contentId 是字串格式，不需要轉換成整數
    const validContentIds = selectedContentIds.value.filter(id => id && id.length > 0)

    if (validContentIds.length === 0) {
      console.error('No valid content IDs selected')
      return
    }

    const batchData = {
      company_id: parseInt(props.companyId),
      assessment_id: parseInt(props.questionId),
      question_content_ids: validContentIds,
      personnel_id: selectedUser.value.id
    }

    const result = await batchAssignPersonnel(batchData)

    if (result) {
      // Reset form
      selectedUser.value = null
      selectedContentIds.value = []
      userSearchQuery.value = ''

      // Notify parent
      emit('assignment-completed')
    }
  } catch (error) {
    console.error('Assignment failed:', error)
    // You could add a toast notification here
  }
}

// Helper method to get category name
const getCategoryName = (categoryId) => {
  if (!categoryId) return '未分類'

  // Use categories from API instead of localStorage
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
  if (!categoryId) return 999 // Put uncategorized items at the end

  // Use categories from API instead of localStorage
  if (questionCategories.value && questionCategories.value.length > 0) {
    const categoryIndex = questionCategories.value.findIndex(cat => cat.id === categoryId)
    return categoryIndex !== -1 ? categoryIndex : 999
  }

  return 999
}

// Load question structure data when component mounts
onMounted(async () => {
  try {
    // Load structure data (categories, topics, factors) for the assessment
    if (props.questionId) {
      await Promise.all([
        getCategories(props.questionId),
        getTopics(props.questionId),
        getFactors(props.questionId)
      ])
    }
  } catch (error) {
    console.error('Error loading structure data in IndividualAssignment:', error)
  }
})
</script>