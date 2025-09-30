<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white py-8">
      <div class="container mx-auto px-4">
        <h1 class="text-4xl font-bold mb-2">風險評估管理系統</h1>
        <p class="text-xl opacity-90">API 文檔與測試工具</p>
      </div>
    </div>

    <div class="container mx-auto px-4 py-8">
      <!-- Search Box -->
      <div class="mb-6">
        <UInput
          v-model="searchQuery"
          placeholder="搜尋 API 端點..."
          icon="i-heroicons-magnifying-glass"
          size="lg"
          class="w-full"
        />
      </div>

      <!-- Navigation Tabs -->
      <UTabs v-model="selectedTab" :items="tabs" class="mb-8">
        <!-- Risk Assessment APIs -->
        <template #risk-assessment="{ item }">
          <div class="space-y-6">
            <!-- Templates Management -->
            <APIGroup
              title="範本管理 API"
              description="風險評估範本的增刪改查操作"
              :endpoints="riskAssessmentAPIs.templates"
              :search-query="searchQuery"
            />

            <!-- Categories Management -->
            <APIGroup
              title="範本分類管理 API"
              description="風險評估範本分類的管理操作"
              :endpoints="riskAssessmentAPIs.categories"
              :search-query="searchQuery"
            />

            <!-- Content Management -->
            <APIGroup
              title="評估內容管理 API"
              description="範本評估內容項目的管理操作"
              :endpoints="riskAssessmentAPIs.contents"
              :search-query="searchQuery"
            />

            <!-- Company Assessments -->
            <APIGroup
              title="公司評估管理 API"
              description="公司風險評估記錄的管理操作"
              :endpoints="riskAssessmentAPIs.assessments"
              :search-query="searchQuery"
            />
          </div>
        </template>

        <!-- Question Management APIs -->
        <template #question-management="{ item }">
          <div class="space-y-6">
            <APIGroup
              title="題項管理 API"
              description="獨立的題項管理系統，用於評估問題的組織和管理"
              :endpoints="questionManagementAPIs"
              :search-query="searchQuery"
            />
          </div>
        </template>

        <!-- Personnel APIs -->
        <template #personnel="{ item }">
          <div class="space-y-6">
            <APIGroup
              title="人員管理 API"
              description="公司人員信息管理和指派操作"
              :endpoints="personnelAPIs"
              :search-query="searchQuery"
            />
          </div>
        </template>

        <!-- Companies APIs -->
        <template #companies="{ item }">
          <div class="space-y-6">
            <APIGroup
              title="公司管理 API"
              description="公司基本資料的增刪改查操作"
              :endpoints="companiesAPIs"
              :search-query="searchQuery"
            />
          </div>
        </template>

        <!-- Admin APIs -->
        <template #admin="{ item }">
          <div class="space-y-6">
            <APIGroup
              title="檔案上傳 API"
              description="範本檔案上傳和處理功能"
              :endpoints="adminAPIs.upload"
              :search-query="searchQuery"
            />

            <APIGroup
              title="NAS 整合 API"
              description="網路附加儲存系統整合功能"
              :endpoints="adminAPIs.nas"
              :search-query="searchQuery"
            />
          </div>
        </template>
      </UTabs>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

// Meta tags for the page
useHead({
  title: 'API 文檔 - 風險評估管理系統',
  meta: [
    { name: 'description', content: '風險評估管理系統的完整 API 文檔與測試工具' }
  ]
})

// Reactive state
const searchQuery = ref('')
const selectedTab = ref(0)

// Tab configuration
const tabs = [
  { key: 'risk-assessment', label: '風險評估', icon: 'i-heroicons-shield-check' },
  { key: 'question-management', label: '題項管理', icon: 'i-heroicons-question-mark-circle' },
  { key: 'personnel', label: '人員管理', icon: 'i-heroicons-users' },
  { key: 'companies', label: '公司管理', icon: 'i-heroicons-building-office' },
  { key: 'admin', label: '管理功能', icon: 'i-heroicons-cog-6-tooth' }
]

// API definitions
const riskAssessmentAPIs = {
  templates: [
    {
      method: 'GET',
      path: '/api/v1/risk-assessment/templates',
      title: '取得範本列表',
      description: '取得所有風險評估範本列表，包含範本基本資訊和統計數據',
      parameters: [
        { name: 'search', type: 'string', required: false, description: '搜尋範本名稱' },
        { name: 'page', type: 'integer', required: false, description: '頁數 (預設: 1)' },
        { name: 'limit', type: 'integer', required: false, description: '每頁筆數 (預設: 10)' }
      ],
      example: {
        success: true,
        data: {
          templates: [{
            id: 1,
            version_name: "2024年度風險評估範本",
            description: "年度標準風險評估範本",
            status: "active",
            content_count: 25,
            category_count: 5,
            created_at: "2024-01-01 00:00:00"
          }],
          pagination: { total: 1, page: 1, limit: 10 }
        }
      }
    },
    {
      method: 'POST',
      path: '/api/v1/risk-assessment/templates',
      title: '建立範本',
      description: '建立新的風險評估範本',
      parameters: [
        { name: 'version_name', type: 'string', required: true, description: '範本名稱' },
        { name: 'description', type: 'string', required: false, description: '範本描述' },
        { name: 'assessment_year', type: 'integer', required: false, description: '評估年度' }
      ]
    },
    {
      method: 'POST',
      path: '/api/v1/risk-assessment/templates/{id}/copy',
      title: '複製範本',
      description: '複製現有範本建立新版本',
      pathParams: [
        { name: 'id', type: 'integer', required: true, description: '要複製的範本 ID' }
      ],
      parameters: [
        { name: 'version_name', type: 'string', required: true, description: '新範本名稱' },
        { name: 'description', type: 'string', required: false, description: '新範本描述' }
      ]
    }
  ],
  categories: [
    {
      method: 'GET',
      path: '/api/v1/risk-assessment/templates/{id}/categories',
      title: '取得分類列表',
      description: '取得指定範本的風險分類列表',
      pathParams: [
        { name: 'id', type: 'integer', required: true, description: '範本 ID' }
      ]
    },
    {
      method: 'POST',
      path: '/api/v1/risk-assessment/templates/{id}/categories',
      title: '建立分類',
      description: '為指定範本建立新的風險分類',
      pathParams: [
        { name: 'id', type: 'integer', required: true, description: '範本 ID' }
      ],
      parameters: [
        { name: 'category_name', type: 'string', required: true, description: '分類名稱' },
        { name: 'category_code', type: 'string', required: false, description: '分類代碼' },
        { name: 'description', type: 'string', required: false, description: '分類描述' },
        { name: 'sort_order', type: 'integer', required: false, description: '排序順序' }
      ]
    }
  ],
  contents: [
    {
      method: 'GET',
      path: '/api/v1/risk-assessment/templates/{id}/contents',
      title: '取得評估內容',
      description: '取得指定範本的評估內容項目列表',
      pathParams: [
        { name: 'id', type: 'integer', required: true, description: '範本 ID' }
      ]
    },
    {
      method: 'POST',
      path: '/api/v1/risk-assessment/templates/{id}/contents',
      title: '建立評估內容',
      description: '為指定範本建立新的評估內容項目',
      pathParams: [
        { name: 'id', type: 'integer', required: true, description: '範本 ID' }
      ],
      parameters: [
        { name: 'topic', type: 'string', required: true, description: '評估主題' },
        { name: 'category_id', type: 'integer', required: false, description: '所屬分類 ID' },
        { name: 'description', type: 'string', required: false, description: '項目描述' },
        { name: 'scoring_method', type: 'string', required: false, description: '評分方式 (binary, scale_1_5, scale_1_10, percentage)' },
        { name: 'weight', type: 'decimal', required: false, description: '權重' }
      ]
    }
  ],
  assessments: [
    {
      method: 'GET',
      path: '/api/v1/risk-assessment/company-assessments',
      title: '取得評估列表',
      description: '取得所有公司評估記錄列表'
    },
    {
      method: 'GET',
      path: '/api/v1/risk-assessment/company-assessments/company/{companyId}',
      title: '取得公司評估',
      description: '取得指定公司的評估記錄',
      pathParams: [
        { name: 'companyId', type: 'string', required: true, description: '公司 ID' }
      ]
    }
  ]
}

const questionManagementAPIs = [
  {
    method: 'GET',
    path: '/api/v1/question-management/assessment/{id}/structure',
    title: '取得評估結構',
    description: '取得指定評估的完整結構，包含分類、主題、因子等',
    pathParams: [
      { name: 'id', type: 'integer', required: true, description: '評估 ID' }
    ]
  },
  {
    method: 'GET',
    path: '/api/v1/question-management/assessment/{id}/categories',
    title: '取得評估分類',
    description: '取得指定評估的分類列表',
    pathParams: [
      { name: 'id', type: 'integer', required: true, description: '評估 ID' }
    ]
  },
  {
    method: 'GET',
    path: '/api/v1/question-management/assessment/{id}/responses',
    title: '取得評估回答',
    description: '取得指定評估的回答記錄',
    pathParams: [
      { name: 'id', type: 'integer', required: true, description: '評估 ID' }
    ]
  }
]

const personnelAPIs = [
  {
    method: 'GET',
    path: '/api/v1/personnel/companies',
    title: '取得公司列表',
    description: '取得所有公司基本資訊列表'
  },
  {
    method: 'GET',
    path: '/api/v1/personnel/companies/{id}/personnel',
    title: '取得公司人員',
    description: '取得指定公司的人員列表',
    pathParams: [
      { name: 'id', type: 'integer', required: true, description: '公司 ID' }
    ]
  },
  {
    method: 'POST',
    path: '/api/v1/personnel/assignments',
    title: '建立人員指派',
    description: '建立新的人員指派記錄',
    parameters: [
      { name: 'company_id', type: 'integer', required: true, description: '公司 ID' },
      { name: 'assessment_id', type: 'integer', required: true, description: '評估 ID' },
      { name: 'personnel_id', type: 'integer', required: true, description: '人員 ID' },
      { name: 'role', type: 'string', required: false, description: '角色' }
    ]
  }
]

const companiesAPIs = [
  {
    method: 'GET',
    path: '/api/companies',
    title: '取得公司列表',
    description: '取得所有公司列表'
  },
  {
    method: 'GET',
    path: '/api/v1/risk-assessment/local-companies',
    title: '取得本地公司',
    description: '取得本地公司資料列表'
  },
  {
    method: 'GET',
    path: '/api/v1/risk-assessment/local-companies/stats',
    title: '公司統計資料',
    description: '取得公司統計資料'
  }
]

const adminAPIs = {
  upload: [
    {
      method: 'POST',
      path: '/admin/api/template/upload_file',
      title: '上傳檔案',
      description: '上傳範本檔案進行處理',
      contentType: 'multipart/form-data',
      parameters: [
        { name: 'file', type: 'file', required: true, description: '要上傳的檔案' }
      ]
    }
  ],
  nas: [
    {
      method: 'GET',
      path: '/api/nas/test',
      title: 'NAS 連線測試',
      description: '測試 NAS 系統連線狀態'
    },
    {
      method: 'GET',
      path: '/api/nas/folders',
      title: '取得資料夾列表',
      description: '取得 NAS 上的資料夾列表'
    },
    {
      method: 'GET',
      path: '/api/nas/files',
      title: '取得檔案列表',
      description: '取得 NAS 上的檔案列表'
    }
  ]
}
</script>

<style scoped>
.container {
  max-width: 1200px;
}
</style>