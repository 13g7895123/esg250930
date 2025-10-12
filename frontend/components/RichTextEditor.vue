<template>
  <div class="rich-text-editor-wrapper">
    <!-- 富文本編輯工具列 -->
    <div v-if="!readonly" class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-t-xl px-4 py-2">
      <div class="flex items-center flex-wrap gap-1">
        <!-- 格式化工具組 -->
        <div class="flex items-center bg-white dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
          <button
            @click="formatText('bold')"
            class="p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-l-lg transition-colors group"
            title="粗體 (Ctrl+B)"
            type="button"
          >
            <span class="text-sm font-bold text-gray-700 dark:text-gray-200 group-hover:text-gray-900 dark:group-hover:text-white">B</span>
          </button>
          <button
            @click="formatText('italic')"
            class="p-2 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors group"
            title="斜體 (Ctrl+I)"
            type="button"
          >
            <span class="text-sm italic text-gray-700 dark:text-gray-200 group-hover:text-gray-900 dark:group-hover:text-white">I</span>
          </button>
          <button
            @click="formatText('underline')"
            class="p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-r-lg transition-colors group"
            title="底線 (Ctrl+U)"
            type="button"
          >
            <span class="text-sm underline text-gray-700 dark:text-gray-200 group-hover:text-gray-900 dark:group-hover:text-white">U</span>
          </button>
        </div>

        <!-- 字體大小工具組 -->
        <div class="flex items-center bg-white dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
          <div class="flex items-center px-3 py-2 space-x-2">
            <label class="text-xs font-medium text-gray-600 dark:text-gray-300">字體</label>
            <select
              @change="handleFontSizeChange($event)"
              class="text-xs border border-gray-300 dark:border-gray-500 rounded px-2 py-1 bg-white dark:bg-gray-600 text-gray-700 dark:text-gray-200"
              title="字體大小"
            >
              <option value="">選擇大小</option>
              <option value="12px">12px</option>
              <option value="14px">14px</option>
              <option value="16px">16px</option>
              <option value="18px">18px</option>
              <option value="20px">20px</option>
              <option value="24px">24px</option>
              <option value="28px">28px</option>
              <option value="32px">32px</option>
            </select>
          </div>
        </div>

        <!-- 顏色工具組 -->
        <div class="flex items-center bg-white dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
          <div class="flex items-center px-3 py-2 space-x-2">
            <label class="text-xs font-medium text-gray-600 dark:text-gray-300">文字</label>
            <UPopover
              v-model:open="isTextColorPopoverOpen"
              :popper="{ placement: 'bottom', strategy: 'fixed', offsetDistance: 8 }"
              mode="click"
              :ui="{
                width: 'w-auto',
                background: 'bg-white dark:bg-gray-800',
                ring: 'ring-1 ring-gray-200 dark:ring-gray-700',
                rounded: 'rounded-lg',
                shadow: 'shadow-lg'
              }"
            >
              <button
                @click="saveSelection"
                type="button"
                class="w-6 h-6 border border-gray-300 dark:border-gray-500 rounded cursor-pointer"
                :style="{ backgroundColor: textColor }"
                title="文字顏色"
              >
                <span class="sr-only">選擇文字顏色</span>
              </button>
              <template #panel="{ close }">
                <div class="p-3">
                  <ColorPicker
                    v-if="isTextColorPopoverOpen"
                    :key="`text-color-${isTextColorPopoverOpen}`"
                    v-model="textColor"
                    @changeColor="formatTextColor"
                    theme="light"
                    :sucker-hide="true"
                    :sucker-canvas="null"
                    :sucker-area="[]"
                  />
                  <div class="mt-3 flex justify-end gap-2">
                    <button
                      @click="close"
                      type="button"
                      class="px-3 py-1.5 text-sm bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 rounded transition-colors"
                    >
                      完成
                    </button>
                  </div>
                </div>
              </template>
            </UPopover>
          </div>
          <div class="w-px h-6 bg-gray-200 dark:bg-gray-600"></div>
          <div class="flex items-center px-3 py-2 space-x-2">
            <label class="text-xs font-medium text-gray-600 dark:text-gray-300">背景</label>
            <UPopover
              v-model:open="isBackgroundColorPopoverOpen"
              :popper="{ placement: 'bottom', strategy: 'fixed', offsetDistance: 8 }"
              mode="click"
              :ui="{
                width: 'w-auto',
                background: 'bg-white dark:bg-gray-800',
                ring: 'ring-1 ring-gray-200 dark:ring-gray-700',
                rounded: 'rounded-lg',
                shadow: 'shadow-lg'
              }"
            >
              <button
                @click="saveSelection"
                type="button"
                class="w-6 h-6 border border-gray-300 dark:border-gray-500 rounded cursor-pointer"
                :style="{ backgroundColor: backgroundColor }"
                title="背景顏色"
              >
                <span class="sr-only">選擇背景顏色</span>
              </button>
              <template #panel="{ close }">
                <div class="p-3">
                  <ColorPicker
                    v-if="isBackgroundColorPopoverOpen"
                    :key="`bg-color-${isBackgroundColorPopoverOpen}`"
                    v-model="backgroundColor"
                    @changeColor="formatBackgroundColor"
                    theme="light"
                    :sucker-hide="true"
                    :sucker-canvas="null"
                    :sucker-area="[]"
                  />
                  <div class="mt-3 flex justify-end gap-2">
                    <button
                      @click="close"
                      type="button"
                      class="px-3 py-1.5 text-sm bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 rounded transition-colors"
                    >
                      完成
                    </button>
                  </div>
                </div>
              </template>
            </UPopover>
          </div>
        </div>

        <!-- 清除格式 -->
        <button
          @click="formatText('removeFormat')"
          class="p-2 bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg border border-gray-200 dark:border-gray-600 transition-colors group"
          title="清除格式"
          type="button"
        >
          <svg class="w-4 h-4 text-gray-700 dark:text-gray-200 group-hover:text-gray-900 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20">
            <path d="M8 4V2h4v2l-1 1h1v2h-2l-2 6H6l2-6H6V5h1L8 4zM3 17l2-2 2 2-2 2-2-2zm12-2l2 2-2 2-2-2 2-2z"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- 編輯區域 -->
    <div
      ref="editorRef"
      :class="[
        'w-full min-h-[200px] px-4 py-3 border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800',
        readonly ? 'rounded-xl' : 'border-t-0 rounded-b-xl focus-within:ring-2 focus-within:ring-green-500 focus-within:border-transparent',
        readonly ? 'cursor-default' : ''
      ]"
      :contenteditable="!readonly"
      @input="handleInput"
      @blur="handleBlur"
      @focus="handleFocus"
      style="outline: none;"
    >
      <!-- 內容會動態設置 -->
    </div>

    <!-- HTML格式確認區域（可選，由prop控制） -->
    <div v-if="showHtmlInfo" class="mt-2 p-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-xs">
      <div class="flex items-center justify-between">
        <span class="text-gray-600 dark:text-gray-400">
          送出格式: <strong class="text-green-600 dark:text-green-400">HTML</strong> |
          內容長度: {{ htmlContent.length }} 字元
        </span>
        <button
          @click="showHTMLPreview = !showHTMLPreview"
          class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
          type="button"
        >
          {{ showHTMLPreview ? '隱藏' : '顯示' }}HTML預覽
        </button>
      </div>
      <!-- HTML預覽 -->
      <div v-if="showHTMLPreview" class="mt-2 p-2 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-600">
        <pre class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-wrap break-all">{{ htmlContent }}</pre>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, computed, watch } from 'vue'
import { ColorPicker } from 'vue-color-kit'
import 'vue-color-kit/dist/vue-color-kit.css'

// Props
const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: '請輸入內容...'
  },
  showHtmlInfo: {
    type: Boolean,
    default: false
  },
  minHeight: {
    type: String,
    default: '200px'
  },
  readonly: {
    type: Boolean,
    default: false
  }
})

// Emits
const emit = defineEmits(['update:modelValue', 'blur', 'focus', 'change'])

// Refs
const editorRef = ref(null)
const showHTMLPreview = ref(false)
const savedSelection = ref(null) // 保存文字選取範圍
const textColor = ref('#000000') // 文字顏色
const backgroundColor = ref('#FFFFFF') // 背景顏色
const isTextColorPopoverOpen = ref(false) // 控制文字顏色 popover 開關
const isBackgroundColorPopoverOpen = ref(false) // 控制背景顏色 popover 開關

// 計算屬性
const htmlContent = computed(() => props.modelValue || '')

// Watch modelValue changes
watch(() => props.modelValue, (newValue) => {
  if (editorRef.value && editorRef.value.innerHTML !== newValue) {
    editorRef.value.innerHTML = newValue || ''
  }
})

// Rich text editor methods - 簡單可靠的實現
const formatText = (command, value = null) => {
  if (!editorRef.value) return

  // 確保編輯器有焦點
  editorRef.value.focus()

  const selection = window.getSelection()
  const selectedText = selection.toString()

  // 如果沒有選取文字，提示用戶
  if (!selectedText || selectedText.length === 0) {
    const toast = useToast()
    toast.add({
      title: '請先選取文字',
      description: '請選取要格式化的文字內容',
      color: 'blue'
    })
    return
  }

  // 保存當前內容作為備份
  const currentContent = editorRef.value.innerHTML

  try {
    let success = false

    // 使用簡單的execCommand方法
    switch (command) {
      case 'bold':
        success = document.execCommand('bold', false, null)
        break
      case 'italic':
        success = document.execCommand('italic', false, null)
        break
      case 'underline':
        success = document.execCommand('underline', false, null)
        break
      case 'removeFormat':
        success = document.execCommand('removeFormat', false, null)
        break
      case 'foreColor':
        if (value) {
          success = document.execCommand('foreColor', false, value)
        }
        break
      case 'hiliteColor':
        if (value) {
          success = document.execCommand('backColor', false, value)
        }
        break
      default:
        console.warn('不支援的命令:', command)
        return
    }

    if (success) {
      // 更新內容
      updateContent()
      console.log('格式化成功:', command, value || '')
    } else {
      // 如果execCommand失敗，嘗試手動實現
      manualFormatting(command, value, selection)
    }

  } catch (error) {
    console.error('格式化錯誤:', error)
    // 恢復原始內容
    editorRef.value.innerHTML = currentContent
  }

  // 保持焦點
  editorRef.value.focus()
}

// 手動格式化備用方法
const manualFormatting = (command, value, selection) => {
  if (selection.rangeCount === 0) return

  const range = selection.getRangeAt(0)
  const selectedContent = range.extractContents()

  // 對於顏色相關的格式化，需要先清理樣式避免嵌套
  if (command === 'foreColor' || command === 'hiliteColor') {
    const tempDiv = document.createElement('div')
    tempDiv.appendChild(selectedContent)

    const spans = tempDiv.querySelectorAll('span')
    spans.forEach(span => {
      if (command === 'foreColor' && span.style.color) {
        span.style.color = ''
      } else if (command === 'hiliteColor' && span.style.backgroundColor) {
        span.style.backgroundColor = ''
      }
      // 如果 span 沒有其他樣式，移除 span 標籤
      if (!span.style.cssText || span.style.cssText.trim() === '') {
        const parent = span.parentNode
        while (span.firstChild) {
          parent.insertBefore(span.firstChild, span)
        }
        parent.removeChild(span)
      }
    })

    const wrapper = document.createElement('span')
    if (command === 'foreColor') {
      wrapper.style.color = value
    } else if (command === 'hiliteColor') {
      wrapper.style.backgroundColor = value
    }
    while (tempDiv.firstChild) {
      wrapper.appendChild(tempDiv.firstChild)
    }
    range.insertNode(wrapper)
  } else {
    // 其他格式化（粗體、斜體、底線）直接應用
    let wrapper
    switch (command) {
      case 'bold':
        wrapper = document.createElement('strong')
        break
      case 'italic':
        wrapper = document.createElement('em')
        break
      case 'underline':
        wrapper = document.createElement('u')
        break
      default:
        range.insertNode(selectedContent)
        return
    }

    wrapper.appendChild(selectedContent)
    range.insertNode(wrapper)
  }

  // 更新內容
  updateContent()
}

// Handle font size change
const handleFontSizeChange = (event) => {
  const size = event.target.value
  if (size) {
    formatFontSize(size)
    // 重置選單到預設選項
    event.target.value = ''
  }
}

// Font size formatting function
const formatFontSize = (size) => {
  if (!editorRef.value) return

  editorRef.value.focus()

  const selection = window.getSelection()
  const selectedText = selection.toString()

  if (!selectedText || selectedText.length === 0) {
    const toast = useToast()
    toast.add({
      title: '請先選取文字',
      description: '請選取要變更字體大小的文字內容',
      color: 'blue'
    })
    return
  }

  const currentContent = editorRef.value.innerHTML

  try {
    const success = document.execCommand('fontSize', false, '7')

    if (success) {
      const fontElements = editorRef.value.querySelectorAll('font[size="7"]')
      fontElements.forEach(font => {
        const span = document.createElement('span')
        span.style.fontSize = size
        span.innerHTML = font.innerHTML
        font.parentNode.replaceChild(span, font)
      })

      updateContent()
      console.log('字體大小設定成功:', size)
    } else {
      if (selection.rangeCount > 0) {
        const range = selection.getRangeAt(0)
        const selectedContent = range.extractContents()

        // 移除所有內層的字體大小樣式，避免嵌套
        const tempDiv = document.createElement('div')
        tempDiv.appendChild(selectedContent)

        // 清除所有 span 的 fontSize 樣式
        const spans = tempDiv.querySelectorAll('span')
        spans.forEach(span => {
          if (span.style.fontSize) {
            span.style.fontSize = ''
          }
          // 如果 span 沒有其他樣式，移除 span 標籤，只保留內容
          if (!span.style.cssText || span.style.cssText.trim() === '') {
            const parent = span.parentNode
            while (span.firstChild) {
              parent.insertBefore(span.firstChild, span)
            }
            parent.removeChild(span)
          }
        })

        const newSpan = document.createElement('span')
        newSpan.style.fontSize = size
        // 將清理後的內容移到新的 span
        while (tempDiv.firstChild) {
          newSpan.appendChild(tempDiv.firstChild)
        }
        range.insertNode(newSpan)

        updateContent()
        console.log('字體大小設定成功 (手動):', size)
      }
    }

  } catch (error) {
    console.error('字體大小設定失敗:', error)
    editorRef.value.innerHTML = currentContent
  }

  editorRef.value.focus()
}

// 保存選取範圍函數
const saveSelection = () => {
  if (!editorRef.value) return

  const selection = window.getSelection()
  if (selection.rangeCount > 0) {
    savedSelection.value = selection.getRangeAt(0).cloneRange()
  }
}

// Color formatting functions - 使用 style 屬性而非 HTML 屬性
const formatTextColor = (colorData) => {
  if (!editorRef.value) return

  // 從 vue-color-kit 回傳的物件中取得顏色值
  const color = typeof colorData === 'string' ? colorData : (colorData?.hex || colorData?.rgba?.a !== undefined ? `rgba(${colorData.rgba.r}, ${colorData.rgba.g}, ${colorData.rgba.b}, ${colorData.rgba.a})` : colorData?.hex)

  editorRef.value.focus()

  // 如果有保存的選取範圍，使用它
  let selection = window.getSelection()
  if (savedSelection.value) {
    try {
      selection.removeAllRanges()
      selection.addRange(savedSelection.value.cloneRange())
    } catch (error) {
      console.error('恢復選取範圍失敗:', error)
    }
  }

  const selectedText = selection.toString()

  if (!selectedText || selectedText.length === 0) {
    const toast = useToast()
    toast.add({
      title: '請先選取文字',
      description: '請選取要變更顏色的文字內容',
      color: 'blue'
    })
    return
  }

  if (selection.rangeCount === 0) return

  const range = selection.getRangeAt(0)
  const selectedContent = range.extractContents()

  // 移除所有內層的顏色樣式，避免嵌套
  const tempDiv = document.createElement('div')
  tempDiv.appendChild(selectedContent)

  // 清除所有 span 的 color 樣式
  const spans = tempDiv.querySelectorAll('span')
  spans.forEach(span => {
    if (span.style.color) {
      span.style.color = ''
    }
    // 如果 span 沒有其他樣式，移除 span 標籤，只保留內容
    if (!span.style.cssText || span.style.cssText.trim() === '') {
      const parent = span.parentNode
      while (span.firstChild) {
        parent.insertBefore(span.firstChild, span)
      }
      parent.removeChild(span)
    }
  })

  // 使用 span 標籤和 style 屬性
  const newSpan = document.createElement('span')
  newSpan.style.color = color
  // 將清理後的內容移到新的 span
  while (tempDiv.firstChild) {
    newSpan.appendChild(tempDiv.firstChild)
  }
  range.insertNode(newSpan)

  // 更新內容
  updateContent()
  console.log('文字顏色設定成功 (style):', color)

  editorRef.value.focus()
}

const formatBackgroundColor = (colorData) => {
  if (!editorRef.value) return

  // 從 vue-color-kit 回傳的物件中取得顏色值
  const color = typeof colorData === 'string' ? colorData : (colorData?.hex || colorData?.rgba?.a !== undefined ? `rgba(${colorData.rgba.r}, ${colorData.rgba.g}, ${colorData.rgba.b}, ${colorData.rgba.a})` : colorData?.hex)

  editorRef.value.focus()

  // 如果有保存的選取範圍，使用它
  let selection = window.getSelection()
  if (savedSelection.value) {
    try {
      selection.removeAllRanges()
      selection.addRange(savedSelection.value.cloneRange())
    } catch (error) {
      console.error('恢復選取範圍失敗:', error)
    }
  }

  const selectedText = selection.toString()

  if (!selectedText || selectedText.length === 0) {
    const toast = useToast()
    toast.add({
      title: '請先選取文字',
      description: '請選取要變更背景顏色的文字內容',
      color: 'blue'
    })
    return
  }

  if (selection.rangeCount === 0) return

  const range = selection.getRangeAt(0)
  const selectedContent = range.extractContents()

  // 移除所有內層的背景顏色樣式，避免嵌套
  const tempDiv = document.createElement('div')
  tempDiv.appendChild(selectedContent)

  // 清除所有 span 的 backgroundColor 樣式
  const spans = tempDiv.querySelectorAll('span')
  spans.forEach(span => {
    if (span.style.backgroundColor) {
      span.style.backgroundColor = ''
    }
    // 如果 span 沒有其他樣式，移除 span 標籤，只保留內容
    if (!span.style.cssText || span.style.cssText.trim() === '') {
      const parent = span.parentNode
      while (span.firstChild) {
        parent.insertBefore(span.firstChild, span)
      }
      parent.removeChild(span)
    }
  })

  // 使用 span 標籤和 style 屬性
  const newSpan = document.createElement('span')
  newSpan.style.backgroundColor = color
  // 將清理後的內容移到新的 span
  while (tempDiv.firstChild) {
    newSpan.appendChild(tempDiv.firstChild)
  }
  range.insertNode(newSpan)

  // 更新內容
  updateContent()
  console.log('背景顏色設定成功 (style):', color)

  editorRef.value.focus()
}

// Update content and emit
const updateContent = () => {
  if (editorRef.value) {
    const htmlContent = editorRef.value.innerHTML
    emit('update:modelValue', htmlContent)
    emit('change', htmlContent)
  }
}

// Event handlers
const handleInput = () => {
  updateContent()
}

const handleBlur = () => {
  updateContent()
  emit('blur')
}

const handleFocus = () => {
  emit('focus')
}

// 公開方法給父組件使用
defineExpose({
  focus: () => editorRef.value?.focus(),
  getHTML: () => editorRef.value?.innerHTML || '',
  setHTML: (html) => {
    if (editorRef.value) {
      editorRef.value.innerHTML = html
      updateContent()
    }
  },
  clear: () => {
    if (editorRef.value) {
      editorRef.value.innerHTML = ''
      updateContent()
    }
  }
})

// Lifecycle
onMounted(async () => {
  await nextTick()

  // 初始化編輯器內容
  if (editorRef.value) {
    editorRef.value.innerHTML = props.modelValue || props.placeholder
  }
})
</script>

<style scoped>
.rich-text-editor-wrapper {
  position: relative;
}

/* 確保編輯器中的內容保持格式 */
.rich-text-editor-wrapper [contenteditable="true"] {
  white-space: pre-wrap;
  word-wrap: break-word;
}

/* 自定義滾動條 */
.rich-text-editor-wrapper [contenteditable="true"]::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

.rich-text-editor-wrapper [contenteditable="true"]::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.rich-text-editor-wrapper [contenteditable="true"]::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

.rich-text-editor-wrapper [contenteditable="true"]::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>