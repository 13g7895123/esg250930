<template>
  <div class="min-h-screen bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
      <h1 class="text-3xl font-bold mb-8">Modal Teleport 測試頁面</h1>

      <div class="bg-white rounded-lg shadow p-6 mb-4">
        <h2 class="text-xl font-semibold mb-4">測試說明</h2>
        <ul class="list-disc list-inside space-y-2 text-gray-700">
          <li>陰影層通過 Teleport 傳送到 body</li>
          <li>彈窗容器通過 Teleport 傳送到 body</li>
          <li>兩者完全分離，z-index 獨立控制</li>
          <li>彈窗永遠相對於 viewport 置中</li>
          <li>點擊陰影可關閉彈窗</li>
        </ul>
      </div>

      <button
        @click="isModalOpen = true"
        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
      >
        開啟測試彈窗
      </button>

      <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
        <p class="text-yellow-800 text-sm">
          💡 提示：請滾動頁面到最下方，使用右下角的浮動按鈕開啟彈窗，測試彈窗是否永遠在可視窗口中央。
        </p>
      </div>

      <!-- 模擬長頁面 -->
      <div class="mt-8 space-y-4">
        <div v-for="i in 50" :key="i" class="bg-white rounded-lg shadow p-4">
          <p class="text-gray-600">測試內容區塊 {{ i }}</p>
          <p class="text-sm text-gray-400">請滾動頁面測試彈窗是否永遠保持置中</p>
          <p v-if="i === 25" class="mt-2 text-blue-600 font-medium">
            👇 你現在在頁面中間，點擊右下角的藍色圓形按鈕開啟彈窗
          </p>
          <p v-if="i === 50" class="mt-2 text-green-600 font-medium">
            🎯 你現在在頁面最下方，點擊右下角的藍色圓形按鈕開啟彈窗，測試是否在可視窗口正中央
          </p>
        </div>
      </div>
    </div>

    <!-- 固定浮動按鈕（無論滾動到哪裡都能看到） -->
    <button
      @click="isModalOpen = true"
      class="fixed bottom-8 right-8 w-16 h-16 bg-blue-600 text-white rounded-full shadow-2xl hover:bg-blue-700 transition-all hover:scale-110 flex items-center justify-center z-40"
      title="開啟測試彈窗"
    >
      <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
    </button>

    <!-- Teleport 1: 陰影層 -->
    <Teleport to="body">
      <Transition name="fade">
        <div
          v-if="isModalOpen"
          class="fixed inset-0 bg-black bg-opacity-50"
          style="z-index: 9998;"
          @click="closeModal"
        ></div>
      </Transition>
    </Teleport>

    <!-- Teleport 2: 彈窗容器 -->
    <Teleport to="body">
      <Transition name="modal">
        <div
          v-if="isModalOpen"
          class="fixed inset-0 flex items-center justify-center px-4"
          style="z-index: 9999; pointer-events: none;"
        >
          <!-- Modal 內容 -->
          <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl flex flex-col"
            style="max-height: 90vh; pointer-events: auto;"
            @click.stop
          >
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                Teleport Modal 測試
              </h3>
              <button
                @click="closeModal"
                class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto flex-1">
              <div class="space-y-4">
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                  <h4 class="font-medium text-blue-900 dark:text-blue-300 mb-2">✅ 陰影層（Teleport 1）</h4>
                  <ul class="text-sm text-blue-800 dark:text-blue-400 space-y-1">
                    <li>• 通過 Teleport 傳送到 body</li>
                    <li>• z-index: 9998</li>
                    <li>• 點擊可關閉彈窗</li>
                  </ul>
                </div>

                <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                  <h4 class="font-medium text-green-900 dark:text-green-300 mb-2">✅ 彈窗容器（Teleport 2）</h4>
                  <ul class="text-sm text-green-800 dark:text-green-400 space-y-1">
                    <li>• 通過 Teleport 傳送到 body</li>
                    <li>• z-index: 9999</li>
                    <li>• fixed inset-0 + flex 置中</li>
                    <li>• pointer-events: none（點擊穿透）</li>
                    <li>• Modal 內容 pointer-events: auto</li>
                  </ul>
                </div>

                <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg">
                  <h4 class="font-medium text-purple-900 dark:text-purple-300 mb-2">🎯 測試重點</h4>
                  <ul class="text-sm text-purple-800 dark:text-purple-400 space-y-1">
                    <li>• 滾動頁面，彈窗是否永遠保持在可視窗口中央？</li>
                    <li>• 點擊陰影背景，彈窗是否正常關閉？</li>
                    <li>• 點擊彈窗內容，是否不會關閉？</li>
                    <li>• 彈窗高度是否限制在 90vh？</li>
                  </ul>
                </div>

                <!-- 模擬長內容 -->
                <div class="border-t border-gray-200 pt-4">
                  <h4 class="font-medium mb-2">模擬長內容</h4>
                  <div class="space-y-2">
                    <p v-for="i in 10" :key="i" class="text-gray-600 text-sm">
                      這是測試內容第 {{ i }} 行，用於測試彈窗內容滾動功能。
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end gap-3 p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
              <button
                @click="closeModal"
                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600"
              >
                關閉
              </button>
              <button
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
              >
                確認
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const isModalOpen = ref(false)

const closeModal = () => {
  isModalOpen.value = false
}

// 管理 body scroll
watch(isModalOpen, (newValue) => {
  if (process.client) {
    if (newValue) {
      document.body.style.overflow = 'hidden'
    } else {
      document.body.style.overflow = ''
    }
  }
})

// Cleanup on unmount
onUnmounted(() => {
  if (process.client) {
    document.body.style.overflow = ''
  }
})
</script>

<style scoped>
/* Fade transition for backdrop */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Modal transition */
.modal-enter-active,
.modal-leave-active {
  transition: all 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>
