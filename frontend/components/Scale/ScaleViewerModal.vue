<template>
  <!-- Scale Viewer Modal (Read-only with Compact Mode) -->
  <Teleport to="body">
    <div
      v-if="modelValue"
      :class="[
        'fixed z-50',
        isCompactMode ? '' : 'inset-0 flex items-center justify-center bg-black bg-opacity-50'
      ]"
      :style="isCompactMode ? { top: modalPosition.y + 'px', left: modalPosition.x + 'px' } : {}"
      @click.self="!isCompactMode && closeModal()"
    >
      <div
        :class="[
          'bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-y-auto modal-draggable',
          isCompactMode ? 'max-w-3xl' : 'w-full max-w-6xl max-h-[90vh] m-4'
        ]"
        :style="isCompactMode ? { maxHeight: '80vh' } : {}"
      >
        <!-- Modal Header -->
        <div
          :class="[
            'p-6 border-b border-gray-200 dark:border-gray-700',
            isCompactMode ? 'cursor-move bg-gray-50 dark:bg-gray-700' : ''
          ]"
          @mousedown="startDrag"
        >
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">量表檢視</h2>
            <div class="flex items-center space-x-2">
              <!-- 切換精簡模式按鈕 -->
              <button
                @click="toggleCompactMode"
                class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                :title="isCompactMode ? '展開' : '精簡化'"
              >
                <svg v-if="!isCompactMode" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                </svg>
              </button>
              <!-- 關閉按鈕 -->
              <button
                @click="closeModal"
                class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center p-12">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
        </div>

        <!-- Modal Content (Tabs) -->
        <div v-else class="p-6">
          <!-- Tab Navigation (完整模式) -->
          <div v-if="!isCompactMode" class="border-b border-gray-200 dark:border-gray-700 mb-6">
            <nav class="-mb-px flex items-center justify-between">
              <div class="flex space-x-8">
                <button
                  @click="activeTab = 'probability'"
                  :class="[
                    activeTab === 'probability'
                      ? 'border-green-600 text-green-600'
                      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                  ]"
                >
                  風險發生可能性量表
                </button>
                <button
                  @click="activeTab = 'impact'"
                  :class="[
                    activeTab === 'impact'
                      ? 'border-green-600 text-green-600'
                      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                  ]"
                >
                  財務衝擊量表
                </button>
              </div>
              <!-- 切換編輯模式按鈕 -->
              <button
                v-if="showEditToggle"
                @click="$emit('toggle-edit')"
                class="py-2 px-4 text-sm text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/20 border border-blue-300 dark:border-blue-600 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors duration-200 my-2"
              >
                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                切換到編輯模式
              </button>
            </nav>
          </div>

          <!-- Tab Navigation (精簡模式) -->
          <div v-else class="mb-4 flex items-center justify-between">
            <div class="inline-flex rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-1">
              <button
                @click="activeTab = 'probability'"
                :class="[
                  'px-3 py-1 rounded-md text-xs font-medium transition-all duration-200',
                  activeTab === 'probability'
                    ? 'bg-green-600 text-white shadow-sm'
                    : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white'
                ]"
              >
                可能性量表
              </button>
              <button
                @click="activeTab = 'impact'"
                :class="[
                  'px-3 py-1 rounded-md text-xs font-medium transition-all duration-200',
                  activeTab === 'impact'
                    ? 'bg-green-600 text-white shadow-sm'
                    : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white'
                ]"
              >
                財務衝擊量表
              </button>
            </div>
            <!-- 切換編輯模式按鈕 (精簡模式) -->
            <button
              v-if="showEditToggle"
              @click="$emit('toggle-edit')"
              class="py-1 px-3 text-xs text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/20 border border-blue-300 dark:border-blue-600 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors duration-200"
            >
              <svg class="w-3 h-3 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              編輯
            </button>
          </div>

          <!-- Probability Scale Tab Content -->
          <div v-show="activeTab === 'probability'">
            <div v-if="probabilityScaleColumns.length === 0 && probabilityScaleRows.length === 0" class="p-6 border-2 border-yellow-300 dark:border-yellow-600 rounded-lg bg-yellow-50 dark:bg-yellow-900/10">
              <div class="text-center text-gray-600 dark:text-gray-400">
                <svg class="mx-auto h-12 w-12 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-white">尚未設定量表資料</h3>
                <p class="mt-1 text-sm">請先在編輯頁面中設定並儲存可能性量表資料</p>
              </div>
            </div>
            <div v-else class="p-6 border-2 border-blue-300 dark:border-blue-600 rounded-lg bg-blue-50 dark:bg-blue-900/10">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                  <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr>
                      <th
                        v-for="col in probabilityScaleColumns"
                        :key="col.id"
                        class="px-4 py-3 text-left text-base font-semibold text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-700"
                      >
                        {{ col.name }}
                      </th>
                      <th class="px-4 py-3 text-left text-base font-semibold text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-700">
                        發生可能性程度
                      </th>
                      <th class="px-4 py-3 text-left text-base font-semibold text-gray-900 dark:text-white">
                        分數級距
                      </th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="(row, index) in probabilityScaleRows" :key="index">
                      <td
                        v-for="col in probabilityScaleColumns"
                        :key="col.id"
                        class="px-4 py-3 text-base text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700"
                      >
                        {{ row.dynamicFields[col.id] || '-' }}
                      </td>
                      <td class="px-4 py-3 text-base text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700">
                        {{ row.probability || '-' }}
                      </td>
                      <td class="px-4 py-3 text-base text-gray-700 dark:text-gray-300">
                        {{ row.scoreRange || '-' }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Description Text Display (if exists) -->
              <div v-if="showDescriptionText && descriptionText" class="mt-0 p-4 bg-gray-50 dark:bg-gray-900/50 border border-t-0 border-gray-200 dark:border-gray-700 text-sm text-gray-700 dark:text-gray-300">
                <div class="whitespace-pre-wrap">{{ descriptionText }}</div>
              </div>
            </div>
          </div>

          <!-- Impact Scale Tab Content -->
          <div v-show="activeTab === 'impact'">
            <div v-if="impactScaleColumns.length === 0 && impactScaleRows.length === 0" class="p-6 border-2 border-yellow-300 dark:border-yellow-600 rounded-lg bg-yellow-50 dark:bg-yellow-900/10">
              <div class="text-center text-gray-600 dark:text-gray-400">
                <svg class="mx-auto h-12 w-12 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-white">尚未設定量表資料</h3>
                <p class="mt-1 text-sm">請先在編輯頁面中設定並儲存財務衝擊量表資料</p>
              </div>
            </div>
            <div v-else class="p-6 border-2 border-blue-300 dark:border-blue-600 rounded-lg bg-blue-50 dark:bg-blue-900/10">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                  <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr>
                      <!-- 變動欄位 header -->
                      <th
                        v-for="col in impactScaleColumns"
                        :key="col.id"
                        class="px-4 py-3 text-left text-base font-semibold text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-700"
                      >
                        <div>{{ col.name }}</div>
                        <div v-if="col.amountNote" class="text-sm font-normal text-blue-600 dark:text-blue-400 mt-1">
                          {{ col.amountNote }}
                        </div>
                      </th>
                      <!-- 固定欄位 header -->
                      <th class="px-4 py-3 text-left text-base font-semibold text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-700">
                        財務衝擊程度
                      </th>
                      <th class="px-4 py-3 text-left text-base font-semibold text-gray-900 dark:text-white">
                        分數級距
                      </th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="(row, index) in impactScaleRows" :key="index">
                      <!-- 變動欄位 cells -->
                      <td
                        v-for="col in impactScaleColumns"
                        :key="col.id"
                        class="px-4 py-3 text-base text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700"
                      >
                        {{ row.dynamicFields[col.id] || '-' }}
                      </td>
                      <!-- 固定欄位 cells -->
                      <td class="px-4 py-3 text-base text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700">
                        {{ row.impactLevel || '-' }}
                      </td>
                      <td class="px-4 py-3 text-base text-gray-700 dark:text-gray-300">
                        {{ row.scoreRange || '-' }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Footer (隱藏於精簡模式) -->
          <div v-if="!isCompactMode" class="flex justify-end items-center pt-6 border-t border-gray-200 dark:border-gray-700 mt-6">
            <button
              @click="closeModal"
              class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200"
            >
              關閉
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  probabilityScaleColumns: {
    type: Array,
    default: () => []
  },
  probabilityScaleRows: {
    type: Array,
    default: () => []
  },
  impactScaleColumns: {
    type: Array,
    default: () => []
  },
  impactScaleRows: {
    type: Array,
    default: () => []
  },
  showDescriptionText: {
    type: Boolean,
    default: false
  },
  descriptionText: {
    type: String,
    default: ''
  },
  defaultCompactMode: {
    type: Boolean,
    default: false
  },
  showEditToggle: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'toggle-edit'])

// Modal state
const activeTab = ref('probability')
const isCompactMode = ref(false)
const modalPosition = ref({ x: 0, y: 0 })
const isDragging = ref(false)
const dragOffset = ref({ x: 0, y: 0 })

// 計算畫面中間位置
const centerModal = () => {
  const modalWidth = 768 // max-w-3xl = 768px
  const modalHeight = window.innerHeight * 0.8 // 80vh

  modalPosition.value = {
    x: (window.innerWidth - modalWidth) / 2,
    y: (window.innerHeight - modalHeight) / 2
  }
}

// 監聽 modal 開啟，設定預設模式和重置狀態
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    // 重置 tab 到可能性量表
    activeTab.value = 'probability'

    // 根據 defaultCompactMode 設定初始模式
    isCompactMode.value = props.defaultCompactMode

    // 如果是精簡模式，設定在畫面中間
    if (props.defaultCompactMode) {
      centerModal()
    }
  }
}, { immediate: true })

// 切換精簡模式
const toggleCompactMode = () => {
  isCompactMode.value = !isCompactMode.value

  // 切換到精簡模式時，設定在畫面中間
  if (isCompactMode.value) {
    centerModal()
  }
}

// 拖曳功能
const startDrag = (event) => {
  if (!isCompactMode.value) return

  isDragging.value = true
  const modalElement = event.target.closest('.modal-draggable')
  const rect = modalElement.getBoundingClientRect()

  dragOffset.value = {
    x: event.clientX - rect.left,
    y: event.clientY - rect.top
  }

  document.addEventListener('mousemove', onDrag)
  document.addEventListener('mouseup', stopDrag)
}

const onDrag = (event) => {
  if (!isDragging.value) return

  modalPosition.value = {
    x: event.clientX - dragOffset.value.x,
    y: event.clientY - dragOffset.value.y
  }
}

const stopDrag = () => {
  isDragging.value = false
  document.removeEventListener('mousemove', onDrag)
  document.removeEventListener('mouseup', stopDrag)
}

// 關閉視窗
const closeModal = () => {
  emit('update:modelValue', false)
  // 不重置狀態，保持使用者偏好，下次開啟時會重新初始化
}
</script>

<style scoped>
/* 拖曳相關樣式 */
.modal-draggable {
  user-select: none;
}

.cursor-move {
  cursor: move;
}

/* 精簡模式時的陰影效果 */
.modal-draggable.shadow-xl {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
</style>
