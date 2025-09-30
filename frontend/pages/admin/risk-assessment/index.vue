<template>
  <div class="p-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-3">風險評估表</h1>
      <p class="text-gray-600 dark:text-gray-400 text-lg">
        管理風險評估範本與題項，建立全面的風險評估體系
      </p>
    </div>

    <!-- Feature Cards Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <!-- Template Management Card -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow duration-200">
        <div class="p-6">
          <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center mr-4">
              <DocumentTextIcon class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
            <div>
              <h3 class="text-xl font-semibold text-gray-900 dark:text-white">範本管理</h3>
              <p class="text-gray-500 dark:text-gray-400 text-sm">版本管理與範本設定</p>
            </div>
          </div>
          
          <p class="text-gray-600 dark:text-gray-300 mb-6 leading-relaxed">
            管理不同版本的風險評估範本，包含建立、編輯、複製和刪除功能。追蹤每個範本的建立日期和版本資訊。
          </p>
          
          <div class="space-y-2 mb-6">
            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
              <CheckCircleIcon class="w-4 h-4 text-green-500 mr-2" />
              範本版本管理
            </div>
            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
              <CheckCircleIcon class="w-4 h-4 text-green-500 mr-2" />
              CRUD 完整功能
            </div>
            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
              <CheckCircleIcon class="w-4 h-4 text-green-500 mr-2" />
              搜尋與複製功能
            </div>
          </div>
          
          <NuxtLink
            to="/admin/risk-assessment/templates"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200"
          >
            進入範本管理
            <ArrowRightIcon class="w-4 h-4 ml-2" />
          </NuxtLink>
        </div>
      </div>

      <!-- Question Management Card -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow duration-200">
        <div class="p-6">
          <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/20 rounded-lg flex items-center justify-center mr-4">
              <ClipboardDocumentCheckIcon class="w-6 h-6 text-purple-600 dark:text-purple-400" />
            </div>
            <div>
              <h3 class="text-xl font-semibold text-gray-900 dark:text-white">題項管理</h3>
              <p class="text-gray-500 dark:text-gray-400 text-sm">評估題項與內容設定</p>
            </div>
          </div>
          
          <p class="text-gray-600 dark:text-gray-300 mb-6 leading-relaxed">
            管理風險評估的題項內容，包含題目分類、評分標準設定，以及批量匯入匯出功能。
          </p>
          
          <div class="space-y-2 mb-6">
            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
              <ClockIcon class="w-4 h-4 text-yellow-500 mr-2" />
              題項分類管理 (開發中)
            </div>
            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
              <ClockIcon class="w-4 h-4 text-yellow-500 mr-2" />
              評分標準設定 (開發中)
            </div>
            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
              <ClockIcon class="w-4 h-4 text-yellow-500 mr-2" />
              批量匯入/匯出 (開發中)
            </div>
          </div>
          
          <NuxtLink
            to="/admin/risk-assessment/questions"
            class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200"
          >
            進入題項管理
            <ArrowRightIcon class="w-4 h-4 ml-2" />
          </NuxtLink>
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-lg p-6 border border-blue-200 dark:border-blue-800">
        <div class="flex items-center">
          <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center mr-4">
            <DocumentTextIcon class="w-5 h-5 text-white" />
          </div>
          <div>
            <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">3</div>
            <div class="text-blue-600 dark:text-blue-400 text-sm">活躍範本</div>
          </div>
        </div>
      </div>

      <div class="bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-lg p-6 border border-purple-200 dark:border-purple-800">
        <div class="flex items-center">
          <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center mr-4">
            <ClipboardDocumentCheckIcon class="w-5 h-5 text-white" />
          </div>
          <div>
            <div class="text-2xl font-bold text-purple-700 dark:text-purple-300">-</div>
            <div class="text-purple-600 dark:text-purple-400 text-sm">評估題項</div>
          </div>
        </div>
      </div>

      <div class="bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-lg p-6 border border-green-200 dark:border-green-800">
        <div class="flex items-center">
          <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center mr-4">
            <ChartBarIcon class="w-5 h-5 text-white" />
          </div>
          <div>
            <div class="text-2xl font-bold text-green-700 dark:text-green-300">-</div>
            <div class="text-green-600 dark:text-green-400 text-sm">完成評估</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
      <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">最近活動</h3>
      </div>
      <div class="p-6">
        <div class="space-y-4">
          <div class="flex items-center text-sm">
            <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
            <span class="text-gray-600 dark:text-gray-400">建立了新範本「基礎風險評估 v1.0」</span>
            <span class="text-gray-400 dark:text-gray-500 ml-auto">2024-01-15</span>
          </div>
          <div class="flex items-center text-sm">
            <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
            <span class="text-gray-600 dark:text-gray-400">更新了範本「進階風險分析 v2.1」</span>
            <span class="text-gray-400 dark:text-gray-500 ml-auto">2024-02-20</span>
          </div>
          <div class="flex items-center text-sm">
            <div class="w-2 h-2 bg-purple-500 rounded-full mr-3"></div>
            <span class="text-gray-600 dark:text-gray-400">複製了範本「企業風險管控 v1.5」</span>
            <span class="text-gray-400 dark:text-gray-500 ml-auto">2024-03-10</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  middleware: 'auth'
})

import {
  DocumentTextIcon,
  ClipboardDocumentCheckIcon,
  ArrowRightIcon,
  CheckCircleIcon,
  ClockIcon,
  ChartBarIcon
} from '@heroicons/vue/24/outline'

usePageTitle('風險評估表')
</script>