<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
      <div class="w-full px-4 py-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">API 文檔與測試工具</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-300">系統API端點文檔與實時測試功能</p>
          </div>
          <div class="flex items-center space-x-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">
              Base URL: <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">{{ baseUrl }}</code>
            </div>
            <UButton
              icon="i-heroicons-arrow-path"
              @click="refreshData"
              :loading="isRefreshing"
              variant="outline"
            >
              重新整理
            </UButton>
          </div>
        </div>
      </div>
    </div>

    <div class="w-full px-4 py-6">
      <div class="grid grid-cols-1 xl:grid-cols-6 gap-6">
        <!-- Sidebar -->
        <div class="xl:col-span-1">
          <div class="sticky top-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
              <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">API 分類</h3>
              <nav class="space-y-2">
                <button
                  v-for="category in apiCategories"
                  :key="category.key"
                  @click="selectedCategory = category.key"
                  :class="[
                    'w-full text-left px-3 py-2 rounded-md text-sm font-medium transition-colors',
                    selectedCategory === category.key
                      ? 'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300'
                      : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700'
                  ]"
                >
                  {{ category.name }}
                  <span class="ml-2 text-xs bg-gray-200 dark:bg-gray-600 px-2 py-1 rounded-full">
                    {{ category.count }}
                  </span>
                </button>
              </nav>
            </div>

            <!-- 快速統計 -->
            <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
              <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">統計資訊</h3>
              <div class="space-y-3">
                <div class="flex justify-between">
                  <span class="text-gray-600 dark:text-gray-400">總API數量</span>
                  <span class="font-medium text-gray-900 dark:text-white">{{ totalApiCount }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600 dark:text-gray-400">GET 請求</span>
                  <span class="font-medium text-green-600">{{ methodCounts.GET || 0 }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600 dark:text-gray-400">POST 請求</span>
                  <span class="font-medium text-blue-600">{{ methodCounts.POST || 0 }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600 dark:text-gray-400">PUT 請求</span>
                  <span class="font-medium text-yellow-600">{{ methodCounts.PUT || 0 }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600 dark:text-gray-400">DELETE 請求</span>
                  <span class="font-medium text-red-600">{{ methodCounts.DELETE || 0 }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Content -->
        <div class="xl:col-span-5">
          <!-- Search -->
          <div class="mb-6">
            <UInput
              v-model="searchQuery"
              placeholder="搜尋 API 端點..."
              icon="i-heroicons-magnifying-glass"
              size="lg"
              class="w-full"
            />
          </div>

          <!-- API Endpoints -->
          <div class="space-y-6">
            <div
              v-for="endpoint in filteredEndpoints"
              :key="endpoint.id"
              class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
            >
              <div class="p-6">
                <!-- API Header -->
                <div class="flex items-start justify-between mb-4">
                  <div class="flex items-center space-x-3">
                    <UBadge
                      :color="getMethodColor(endpoint.method)"
                      variant="solid"
                      size="sm"
                      class="font-mono"
                    >
                      {{ endpoint.method }}
                    </UBadge>
                    <code class="text-lg font-mono text-gray-900 dark:text-white">{{ endpoint.path }}</code>
                  </div>
                  <UButton
                    @click="toggleEndpoint(endpoint.id)"
                    :icon="expandedEndpoints.has(endpoint.id) ? 'i-heroicons-chevron-up' : 'i-heroicons-chevron-down'"
                    variant="ghost"
                    size="sm"
                  />
                </div>

                <!-- API Description -->
                <p class="text-gray-600 dark:text-gray-300 mb-4">{{ endpoint.description }}</p>

                <!-- Tags -->
                <div class="flex flex-wrap gap-2 mb-4">
                  <UBadge
                    v-for="tag in endpoint.tags"
                    :key="tag"
                    variant="outline"
                    size="sm"
                  >
                    {{ tag }}
                  </UBadge>
                </div>

                <!-- Expanded Content -->
                <div v-if="expandedEndpoints.has(endpoint.id)" class="border-t border-gray-200 dark:border-gray-700 pt-6">
                  <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <!-- Request Information -->
                    <div>
                      <h4 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">請求資訊</h4>

                      <!-- Parameters -->
                      <div v-if="endpoint.parameters && endpoint.parameters.length > 0" class="mb-4">
                        <h5 class="font-medium mb-2 text-gray-700 dark:text-gray-300">參數</h5>
                        <div class="space-y-2">
                          <div
                            v-for="param in endpoint.parameters"
                            :key="param.name"
                            class="bg-gray-50 dark:bg-gray-700 p-3 rounded-md"
                          >
                            <div class="flex items-center justify-between">
                              <code class="text-sm font-mono text-blue-600 dark:text-blue-400">{{ param.name }}</code>
                              <UBadge
                                :color="param.required ? 'red' : 'gray'"
                                variant="outline"
                                size="xs"
                              >
                                {{ param.required ? '必需' : '可選' }}
                              </UBadge>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ param.description }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">類型: {{ param.type }}</p>
                          </div>
                        </div>
                      </div>

                      <!-- Request Body -->
                      <div v-if="endpoint.requestBody" class="mb-4">
                        <h5 class="font-medium mb-2 text-gray-700 dark:text-gray-300">請求主體</h5>
                        <pre class="bg-gray-50 dark:bg-gray-700 p-3 rounded-md text-sm overflow-x-auto"><code>{{ JSON.stringify(endpoint.requestBody, null, 2) }}</code></pre>
                      </div>

                      <!-- Test Form -->
                      <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-md">
                        <h5 class="font-medium mb-3 text-blue-900 dark:text-blue-100">測試此API</h5>

                        <!-- Path Parameters -->
                        <div v-if="endpoint.pathParams && endpoint.pathParams.length > 0" class="mb-3">
                          <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">路徑參數</label>
                          <div class="space-y-2">
                            <UInput
                              v-for="param in endpoint.pathParams"
                              :key="param"
                              v-model="testData[endpoint.id].pathParams[param]"
                              :placeholder="`輸入 ${param}`"
                              size="sm"
                            />
                          </div>
                        </div>

                        <!-- Query Parameters -->
                        <div v-if="endpoint.queryParams && endpoint.queryParams.length > 0" class="mb-3">
                          <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">查詢參數</label>
                          <div class="space-y-2">
                            <UInput
                              v-for="param in endpoint.queryParams"
                              :key="param.name"
                              v-model="testData[endpoint.id].queryParams[param.name]"
                              :placeholder="`輸入 ${param.name}`"
                              size="sm"
                            />
                          </div>
                        </div>

                        <!-- Request Body -->
                        <div v-if="['POST', 'PUT', 'PATCH'].includes(endpoint.method)" class="mb-3">
                          <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">請求主體 (JSON)</label>
                          <UTextarea
                            v-model="testData[endpoint.id].body"
                            placeholder="輸入 JSON 格式的請求主體"
                            :rows="4"
                            size="sm"
                          />
                        </div>

                        <UButton
                          @click="testEndpoint(endpoint)"
                          :loading="testingEndpoints.has(endpoint.id)"
                          color="blue"
                          size="sm"
                          icon="i-heroicons-play"
                        >
                          執行測試
                        </UButton>
                      </div>
                    </div>

                    <!-- Response Information -->
                    <div>
                      <h4 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">回應資訊</h4>

                      <!-- Response Example -->
                      <div v-if="endpoint.responseExample" class="mb-4">
                        <h5 class="font-medium mb-2 text-gray-700 dark:text-gray-300">回應範例</h5>
                        <pre class="bg-gray-50 dark:bg-gray-700 p-3 rounded-md text-sm overflow-x-auto"><code>{{ JSON.stringify(endpoint.responseExample, null, 2) }}</code></pre>
                      </div>

                      <!-- Test Result -->
                      <div v-if="testResults[endpoint.id]" class="bg-gray-50 dark:bg-gray-700 p-4 rounded-md">
                        <div class="flex items-center justify-between mb-3">
                          <h5 class="font-medium text-gray-700 dark:text-gray-300">測試結果</h5>
                          <UBadge
                            :color="testResults[endpoint.id].success ? 'green' : 'red'"
                            variant="solid"
                            size="sm"
                          >
                            {{ testResults[endpoint.id].status || (testResults[endpoint.id].success ? '成功' : '失敗') }}
                          </UBadge>
                        </div>

                        <div class="mb-3">
                          <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">請求URL:</p>
                          <code class="text-xs bg-white dark:bg-gray-800 p-2 rounded block">{{ testResults[endpoint.id].url }}</code>
                        </div>

                        <div>
                          <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">回應內容:</p>
                          <pre class="bg-white dark:bg-gray-800 p-3 rounded text-xs overflow-x-auto max-h-64"><code>{{ JSON.stringify(testResults[endpoint.id].data || testResults[endpoint.id].error, null, 2) }}</code></pre>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-if="filteredEndpoints.length === 0" class="text-center py-12">
            <div class="text-gray-400 dark:text-gray-500 mb-4">
              <Icon name="i-heroicons-document-magnifying-glass" class="w-16 h-16 mx-auto" />
            </div>
            <p class="text-gray-500 dark:text-gray-400">找不到符合條件的API端點</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'

// 設置頁面標題
useHead({
  title: 'API 文檔與測試工具'
})

// 防止此頁面被搜尋引擎索引
useServerSeoMeta({
  robots: 'noindex, nofollow'
})

const { $fetch } = useNuxtApp()
const { get, post, put, patch, delete: del } = useApi()

// 響應式狀態
const selectedCategory = ref('all')
const searchQuery = ref('')
const expandedEndpoints = ref(new Set())
const testingEndpoints = ref(new Set())
const testResults = ref({})
const testData = reactive({})
const isRefreshing = ref(false)
const baseUrl = ref('')

// 取得基礎URL
onMounted(() => {
  const config = useRuntimeConfig()
  baseUrl.value = config.public.apiBaseUrl || config.public.backendUrl || '/api/v1'
})

// API端點定義
const apiEndpoints = ref([
  // Risk Assessment Templates
  {
    id: 'templates-index',
    method: 'GET',
    path: '/api/v1/risk-assessment/templates',
    description: '取得所有風險評估範本',
    category: 'templates',
    tags: ['風險評估', '範本'],
    parameters: [],
    queryParams: [],
    pathParams: [],
    responseExample: {
      success: true,
      data: [
        {
          id: 1,
          version_name: '2024年風險評估範本',
          description: '年度風險評估使用範本',
          status: 'active',
          created_at: '2024-01-01T00:00:00Z',
          updated_at: '2024-01-01T00:00:00Z'
        }
      ]
    }
  },
  {
    id: 'templates-show',
    method: 'GET',
    path: '/api/v1/risk-assessment/templates/{id}',
    description: '取得指定ID的風險評估範本',
    category: 'templates',
    tags: ['風險評估', '範本'],
    parameters: [
      { name: 'id', type: 'integer', required: true, description: '範本ID' }
    ],
    queryParams: [],
    pathParams: ['id'],
    responseExample: {
      success: true,
      data: {
        id: 1,
        version_name: '2024年風險評估範本',
        description: '年度風險評估使用範本',
        status: 'active',
        created_at: '2024-01-01T00:00:00Z',
        updated_at: '2024-01-01T00:00:00Z'
      }
    }
  },
  {
    id: 'templates-create',
    method: 'POST',
    path: '/api/v1/risk-assessment/templates',
    description: '建立新的風險評估範本',
    category: 'templates',
    tags: ['風險評估', '範本', '建立'],
    parameters: [],
    queryParams: [],
    pathParams: [],
    requestBody: {
      version_name: '新範本名稱',
      description: '範本描述',
      status: 'active'
    },
    responseExample: {
      success: true,
      message: 'Template created successfully',
      data: {
        id: 2,
        version_name: '新範本名稱',
        description: '範本描述',
        status: 'active',
        created_at: '2024-01-01T00:00:00Z',
        updated_at: '2024-01-01T00:00:00Z'
      }
    }
  },
  {
    id: 'templates-update',
    method: 'PUT',
    path: '/api/v1/risk-assessment/templates/{id}',
    description: '更新指定ID的風險評估範本',
    category: 'templates',
    tags: ['風險評估', '範本', '更新'],
    parameters: [
      { name: 'id', type: 'integer', required: true, description: '範本ID' }
    ],
    queryParams: [],
    pathParams: ['id'],
    requestBody: {
      version_name: '更新的範本名稱',
      description: '更新的範本描述',
      status: 'active'
    },
    responseExample: {
      success: true,
      message: 'Template updated successfully',
      data: {
        id: 1,
        version_name: '更新的範本名稱',
        description: '更新的範本描述',
        status: 'active',
        created_at: '2024-01-01T00:00:00Z',
        updated_at: '2024-01-01T00:00:00Z'
      }
    }
  },
  {
    id: 'templates-delete',
    method: 'DELETE',
    path: '/api/v1/risk-assessment/templates/{id}',
    description: '刪除指定ID的風險評估範本',
    category: 'templates',
    tags: ['風險評估', '範本', '刪除'],
    parameters: [
      { name: 'id', type: 'integer', required: true, description: '範本ID' }
    ],
    queryParams: [],
    pathParams: ['id'],
    responseExample: {
      success: true,
      message: 'Template deleted successfully'
    }
  },

  // Template Categories
  {
    id: 'categories-index',
    method: 'GET',
    path: '/api/v1/risk-assessment/templates/{templateId}/categories',
    description: '取得指定範本的所有風險分類',
    category: 'categories',
    tags: ['風險分類', '範本'],
    parameters: [
      { name: 'templateId', type: 'integer', required: true, description: '範本ID' }
    ],
    queryParams: [],
    pathParams: ['templateId'],
    responseExample: {
      success: true,
      data: [
        {
          id: 1,
          template_id: 1,
          category_name: '財務風險',
          category_code: 'FINANCIAL',
          description: '與財務相關的風險評估',
          sort_order: 1
        }
      ]
    }
  },
  {
    id: 'categories-create',
    method: 'POST',
    path: '/api/v1/risk-assessment/templates/{templateId}/categories',
    description: '為指定範本建立新的風險分類',
    category: 'categories',
    tags: ['風險分類', '範本', '建立'],
    parameters: [
      { name: 'templateId', type: 'integer', required: true, description: '範本ID' }
    ],
    queryParams: [],
    pathParams: ['templateId'],
    requestBody: {
      category_name: '新風險分類',
      category_code: 'NEW_RISK',
      description: '新的風險分類描述',
      sort_order: 1
    },
    responseExample: {
      success: true,
      message: 'Category created successfully',
      data: {
        id: 2,
        template_id: 1,
        category_name: '新風險分類',
        category_code: 'NEW_RISK',
        description: '新的風險分類描述',
        sort_order: 1
      }
    }
  },

  // Company Assessments
  {
    id: 'company-assessments-index',
    method: 'GET',
    path: '/api/v1/risk-assessment/company-assessments',
    description: '取得所有公司評估記錄',
    category: 'assessments',
    tags: ['公司評估', '評估記錄'],
    parameters: [],
    queryParams: [
      { name: 'company_id', type: 'integer', required: false, description: '公司ID' },
      { name: 'template_id', type: 'integer', required: false, description: '範本ID' },
      { name: 'status', type: 'string', required: false, description: '評估狀態' }
    ],
    pathParams: [],
    responseExample: {
      success: true,
      data: [
        {
          id: 1,
          company_id: 1,
          template_id: 1,
          assessment_year: 2024,
          status: 'completed',
          total_score: 85.5,
          percentage_score: 85.5,
          risk_level: 'medium'
        }
      ]
    }
  },
  {
    id: 'company-assessments-by-company',
    method: 'GET',
    path: '/api/v1/risk-assessment/company-assessments/company/{companyId}',
    description: '取得指定公司的所有評估記錄',
    category: 'assessments',
    tags: ['公司評估', '評估記錄'],
    parameters: [
      { name: 'companyId', type: 'string', required: true, description: '公司ID或代碼' }
    ],
    queryParams: [],
    pathParams: ['companyId'],
    responseExample: {
      success: true,
      data: [
        {
          id: 1,
          company_id: 1,
          template_id: 1,
          assessment_year: 2024,
          status: 'completed',
          total_score: 85.5,
          percentage_score: 85.5,
          risk_level: 'medium'
        }
      ]
    }
  },

  // Local Companies
  {
    id: 'local-companies-index',
    method: 'GET',
    path: '/api/v1/risk-assessment/local-companies',
    description: '取得所有本地公司資料',
    category: 'companies',
    tags: ['公司管理', '本地公司'],
    parameters: [],
    queryParams: [
      { name: 'search', type: 'string', required: false, description: '搜尋關鍵字' },
      { name: 'status', type: 'string', required: false, description: '公司狀態' }
    ],
    pathParams: [],
    responseExample: {
      success: true,
      data: [
        {
          id: 1,
          company_name: '台積電股份有限公司',
          external_id: 'TSMC001',
          industry: '半導體',
          status: 'active'
        }
      ]
    }
  },
  {
    id: 'local-companies-create',
    method: 'POST',
    path: '/api/v1/risk-assessment/local-companies',
    description: '建立新的本地公司資料',
    category: 'companies',
    tags: ['公司管理', '本地公司', '建立'],
    parameters: [],
    queryParams: [],
    pathParams: [],
    requestBody: {
      company_name: '新公司名稱',
      external_id: 'NEW001',
      industry: '科技業',
      description: '公司描述',
      status: 'active'
    },
    responseExample: {
      success: true,
      message: 'Company created successfully',
      data: {
        id: 2,
        company_name: '新公司名稱',
        external_id: 'NEW001',
        industry: '科技業',
        description: '公司描述',
        status: 'active'
      }
    }
  },

  // Personnel Management
  {
    id: 'personnel-companies',
    method: 'GET',
    path: '/api/v1/personnel/companies',
    description: '取得所有有人員資料的公司清單',
    category: 'personnel',
    tags: ['人員管理', '公司'],
    parameters: [],
    queryParams: [],
    pathParams: [],
    responseExample: {
      success: true,
      data: [
        {
          id: 1,
          company_name: '台積電股份有限公司',
          external_id: 'TSMC001',
          personnel_count: 150
        }
      ]
    }
  },
  {
    id: 'personnel-by-company',
    method: 'GET',
    path: '/api/v1/personnel/companies/{companyId}/personnel',
    description: '取得指定公司的所有人員資料',
    category: 'personnel',
    tags: ['人員管理', '公司人員'],
    parameters: [
      { name: 'companyId', type: 'integer', required: true, description: '公司ID' }
    ],
    queryParams: [
      { name: 'department', type: 'string', required: false, description: '部門篩選' },
      { name: 'status', type: 'string', required: false, description: '人員狀態' }
    ],
    pathParams: ['companyId'],
    responseExample: {
      success: true,
      data: [
        {
          id: 1,
          personnel_name: '張三',
          department: '資訊部',
          position: '系統工程師',
          status: 'active'
        }
      ]
    }
  },

  // Personnel Assignments
  {
    id: 'personnel-assignments',
    method: 'GET',
    path: '/api/v1/personnel/companies/{companyId}/personnel-assignments',
    description: '取得指定公司的人員指派清單',
    category: 'assignments',
    tags: ['人員指派', '公司'],
    parameters: [
      { name: 'companyId', type: 'integer', required: true, description: '公司ID' }
    ],
    queryParams: [],
    pathParams: ['companyId'],
    responseExample: {
      success: true,
      data: [
        {
          id: 1,
          personnel_id: 1,
          personnel_name: '張三',
          department: '資訊部',
          position: '系統工程師',
          status: 'active'
        }
      ]
    }
  },
  {
    id: 'personnel-assignment-summary',
    method: 'GET',
    path: '/api/v1/personnel/companies/{companyId}/assessments/{assessmentId}/assignments',
    description: '取得指定公司和評估的指派摘要',
    category: 'assignments',
    tags: ['人員指派', '評估摘要'],
    parameters: [
      { name: 'companyId', type: 'integer', required: true, description: '公司ID' },
      { name: 'assessmentId', type: 'integer', required: true, description: '評估ID' }
    ],
    queryParams: [],
    pathParams: ['companyId', 'assessmentId'],
    responseExample: {
      success: true,
      data: {
        assignments: [
          {
            id: 1,
            personnel_id: 1,
            personnel_name: '張三',
            question_content_id: 1,
            assignment_status: 'assigned'
          }
        ],
        summary: {
          total_assignments: 5,
          assigned: 3,
          completed: 2
        }
      }
    }
  },

  // Question Management
  {
    id: 'question-assessment-structure',
    method: 'GET',
    path: '/api/v1/question-management/assessment/{assessmentId}/structure',
    description: '取得評估的題目結構',
    category: 'questions',
    tags: ['題目管理', '評估結構'],
    parameters: [
      { name: 'assessmentId', type: 'integer', required: true, description: '評估ID' }
    ],
    queryParams: [],
    pathParams: ['assessmentId'],
    responseExample: {
      success: true,
      data: {
        categories: [
          {
            id: 1,
            name: '財務風險',
            topics: [
              {
                id: 1,
                name: '流動性風險',
                factors: [
                  {
                    id: 1,
                    name: '現金流量',
                    contents: []
                  }
                ]
              }
            ]
          }
        ]
      }
    }
  },

  // Test Endpoints
  {
    id: 'test-index',
    method: 'GET',
    path: '/api/v1/risk-assessment/test',
    description: '測試API連線',
    category: 'test',
    tags: ['測試', 'API連線'],
    parameters: [],
    queryParams: [],
    pathParams: [],
    responseExample: {
      success: true,
      message: 'Test API is working',
      timestamp: '2024-01-01T00:00:00Z'
    }
  }
])

// 初始化測試資料
const initializeTestData = () => {
  apiEndpoints.value.forEach(endpoint => {
    testData[endpoint.id] = {
      pathParams: {},
      queryParams: {},
      body: endpoint.requestBody ? JSON.stringify(endpoint.requestBody, null, 2) : ''
    }

    // 初始化路徑參數
    if (endpoint.pathParams) {
      endpoint.pathParams.forEach(param => {
        testData[endpoint.id].pathParams[param] = ''
      })
    }

    // 初始化查詢參數
    if (endpoint.queryParams) {
      endpoint.queryParams.forEach(param => {
        testData[endpoint.id].queryParams[param.name] = ''
      })
    }
  })
}

// API分類
const apiCategories = computed(() => {
  const categories = [
    { key: 'all', name: '所有API', count: 0 },
    { key: 'templates', name: '範本管理', count: 0 },
    { key: 'categories', name: '風險分類', count: 0 },
    { key: 'assessments', name: '公司評估', count: 0 },
    { key: 'companies', name: '公司管理', count: 0 },
    { key: 'personnel', name: '人員管理', count: 0 },
    { key: 'assignments', name: '人員指派', count: 0 },
    { key: 'questions', name: '題目管理', count: 0 },
    { key: 'test', name: '測試API', count: 0 }
  ]

  // 計算每個分類的API數量
  categories.forEach(category => {
    if (category.key === 'all') {
      category.count = apiEndpoints.value.length
    } else {
      category.count = apiEndpoints.value.filter(api => api.category === category.key).length
    }
  })

  return categories
})

// 過濾的API端點
const filteredEndpoints = computed(() => {
  let filtered = apiEndpoints.value

  // 按分類過濾
  if (selectedCategory.value !== 'all') {
    filtered = filtered.filter(endpoint => endpoint.category === selectedCategory.value)
  }

  // 按搜尋關鍵字過濾
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(endpoint =>
      endpoint.path.toLowerCase().includes(query) ||
      endpoint.description.toLowerCase().includes(query) ||
      endpoint.method.toLowerCase().includes(query) ||
      endpoint.tags.some(tag => tag.toLowerCase().includes(query))
    )
  }

  return filtered
})

// 計算統計數據
const totalApiCount = computed(() => apiEndpoints.value.length)

const methodCounts = computed(() => {
  const counts = {}
  apiEndpoints.value.forEach(endpoint => {
    counts[endpoint.method] = (counts[endpoint.method] || 0) + 1
  })
  return counts
})

// 方法顏色映射
const getMethodColor = (method) => {
  const colors = {
    GET: 'green',
    POST: 'blue',
    PUT: 'yellow',
    PATCH: 'orange',
    DELETE: 'red'
  }
  return colors[method] || 'gray'
}

// 切換端點展開狀態
const toggleEndpoint = (endpointId) => {
  if (expandedEndpoints.value.has(endpointId)) {
    expandedEndpoints.value.delete(endpointId)
  } else {
    expandedEndpoints.value.add(endpointId)
  }
}

// 測試API端點
const testEndpoint = async (endpoint) => {
  testingEndpoints.value.add(endpoint.id)

  try {
    // 建構URL
    let url = endpoint.path
    const testParams = testData[endpoint.id]

    // 替換路徑參數
    if (endpoint.pathParams) {
      endpoint.pathParams.forEach(param => {
        const value = testParams.pathParams[param]
        if (value) {
          url = url.replace(`{${param}}`, value)
        }
      })
    }

    // 準備查詢參數
    const queryParams = {}
    if (endpoint.queryParams) {
      endpoint.queryParams.forEach(param => {
        const value = testParams.queryParams[param.name]
        if (value) {
          queryParams[param.name] = value
        }
      })
    }

    // 準備請求主體
    let body = null
    if (['POST', 'PUT', 'PATCH'].includes(endpoint.method) && testParams.body) {
      try {
        body = JSON.parse(testParams.body)
      } catch (e) {
        throw new Error('請求主體不是有效的JSON格式')
      }
    }

    // 執行API請求
    let response
    const fullUrl = url + (Object.keys(queryParams).length ? '?' + new URLSearchParams(queryParams).toString() : '')

    switch (endpoint.method) {
      case 'GET':
        response = await get(url, queryParams)
        break
      case 'POST':
        response = await post(url, body)
        break
      case 'PUT':
        response = await put(url, body)
        break
      case 'PATCH':
        response = await patch(url, body)
        break
      case 'DELETE':
        response = await del(url)
        break
      default:
        throw new Error(`不支援的HTTP方法: ${endpoint.method}`)
    }

    testResults.value[endpoint.id] = {
      success: response.success,
      status: response.success ? '200 OK' : `${response.error?.status || 500}`,
      data: response.data,
      error: response.error,
      url: fullUrl,
      timestamp: new Date().toISOString()
    }

  } catch (error) {
    testResults.value[endpoint.id] = {
      success: false,
      status: 'Error',
      error: {
        message: error.message || '請求失敗',
        details: error
      },
      url: endpoint.path,
      timestamp: new Date().toISOString()
    }
  } finally {
    testingEndpoints.value.delete(endpoint.id)
  }
}

// 重新整理資料
const refreshData = async () => {
  isRefreshing.value = true
  try {
    // 清除所有測試結果
    Object.keys(testResults.value).forEach(key => {
      delete testResults.value[key]
    })

    // 重新初始化測試資料
    initializeTestData()

    // 可以在這裡加入其他重新整理邏輯，例如從API動態載入端點列表

  } catch (error) {
    console.error('重新整理失敗:', error)
  } finally {
    isRefreshing.value = false
  }
}

// 初始化
onMounted(() => {
  initializeTestData()
})
</script>

<style scoped>
/* 自定義滾動條樣式 */
pre {
  scrollbar-width: thin;
  scrollbar-color: #d1d5db #f3f4f6;
}

pre::-webkit-scrollbar {
  height: 8px;
  width: 8px;
}

pre::-webkit-scrollbar-track {
  background: #f3f4f6;
  border-radius: 4px;
}

pre::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 4px;
}

pre::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}

/* 深色模式滾動條 */
.dark pre {
  scrollbar-color: #4b5563 #374151;
}

.dark pre::-webkit-scrollbar-track {
  background: #374151;
}

.dark pre::-webkit-scrollbar-thumb {
  background: #4b5563;
}

.dark pre::-webkit-scrollbar-thumb:hover {
  background: #6b7280;
}
</style>