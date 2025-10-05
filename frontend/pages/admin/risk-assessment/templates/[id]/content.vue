<template>
  <div v-if="!isTemplateLoaded">
    <div class="p-6">
      <p>載入中...</p>
    </div>
  </div>
  <ContentManagement
    v-else
    :title="`範本內容管理 - ${templateInfo?.version_name || templateInfo?.versionName || '未知範本'}`"
    description="管理風險評估範本的內容"
    :show-back-button="true"
    :content-data="templateContent"
    :risk-categories="riskCategories"
    :risk-topics="riskTopics"
    :risk-factors="riskFactors"
    :risk-topics-enabled="riskTopicsEnabled"
    :show-management-buttons="false"
    content-type="template"
    :parent-id="templateId"
    :is-refreshing="isRefreshing"
    @add-content="addTemplateContent"
    @update-content="updateTemplateContent"
    @delete-content="deleteTemplateContent"
    @reorder-content="reorderTemplateContent"
    @fetch-topics="fetchTopicsForCategory"
    @fetch-factors="fetchFactorsForCategoryOrTopic"
    @refresh-content="refreshContent"
  />
</template>

<script setup>
definePageMeta({
  middleware: 'auth'
})

const route = useRoute()
const templateId = parseInt(route.params.id)

// Use templates store
const templatesStore = useTemplatesStore()

// Template information for breadcrumb - getTemplateById already returns a computed ref
const templateInfo = templatesStore.getTemplateById(templateId)

// Ensure template data is available before rendering
const isTemplateLoaded = computed(() => templateInfo.value != null)

usePageTitle('範本內容管理')

// Get template content and related data from store
const templateContent = templatesStore.getTemplateContent(templateId)
const riskCategories = templatesStore.getRiskCategories(templateId)
const riskTopics = templatesStore.getRiskTopics(templateId)
const riskFactors = templatesStore.getRiskFactors(templateId)

// Debug: Watch templateContent changes
watch(templateContent, (newValue) => {
  console.log(`[Page] Template content updated: ${newValue.length} items`)
}, { immediate: true })

// Get risk topics enabled state from template settings
const riskTopicsEnabled = computed(() => {
  const value = templateInfo.value?.risk_topics_enabled
  // Convert string "1"/"0" or number 1/0 to boolean
  if (value === '1' || value === 1 || value === true) return true
  if (value === '0' || value === 0 || value === false) return false
  return true // default to true
})

// Refresh state
const isRefreshing = ref(false)

// Content management methods
const addTemplateContent = async (contentData) => {
  try {
    await templatesStore.addTemplateContent(templateId, contentData)
    // 新增成功後重新載入列表（靜默重新載入）
    await refreshContent(false)

    // 顯示成功通知
    const toast = useToast()
    toast.add({
      title: '新增成功',
      description: '題目已成功新增',
      color: 'green'
    })
  } catch (error) {
    console.error('Failed to add template content:', error)
    const toast = useToast()
    toast.add({
      title: '新增失敗',
      description: '無法新增題目，請稍後再試',
      color: 'red'
    })
  }
}

const updateTemplateContent = async (contentId, contentData) => {
  try {
    await templatesStore.updateTemplateContent(templateId, contentId, contentData)
    // 更新成功後重新載入列表（靜默重新載入）
    await refreshContent(false)

    // 顯示成功通知
    const toast = useToast()
    toast.add({
      title: '更新成功',
      description: '題目已成功更新',
      color: 'green'
    })
  } catch (error) {
    console.error('Failed to update template content:', error)
    const toast = useToast()
    toast.add({
      title: '更新失敗',
      description: '無法更新題目，請稍後再試',
      color: 'red'
    })
  }
}

const deleteTemplateContent = async (contentId) => {
  try {
    await templatesStore.deleteTemplateContent(templateId, contentId)
    // 刪除成功後重新載入列表（靜默重新載入）
    await refreshContent(false)

    // 顯示成功通知
    const toast = useToast()
    toast.add({
      title: '刪除成功',
      description: '題目已成功刪除',
      color: 'green'
    })
  } catch (error) {
    console.error('Failed to delete template content:', error)
    const toast = useToast()
    toast.add({
      title: '刪除失敗',
      description: '無法刪除題目，請稍後再試',
      color: 'red'
    })
  }
}

const reorderTemplateContent = async (newOrder) => {
  try {
    await templatesStore.reorderTemplateContent(templateId, newOrder)
    // 重新排序成功後重新載入列表（靜默重新載入）
    await refreshContent(false)

    // 顯示成功通知
    const toast = useToast()
    toast.add({
      title: '排序成功',
      description: '題目順序已更新',
      color: 'green'
    })
  } catch (error) {
    console.error('Failed to reorder template content:', error)
    const toast = useToast()
    toast.add({
      title: '排序失敗',
      description: '無法重新排序題目，請稍後再試',
      color: 'red'
    })
  }
}

// Dynamic fetch methods for cascading dropdowns
const fetchTopicsForCategory = async (categoryId) => {
  try {
    await templatesStore.fetchRiskTopics(templateId, { category_id: categoryId })
  } catch (error) {
    console.error('Failed to fetch topics for category:', error)
  }
}

const fetchFactorsForCategoryOrTopic = async (params) => {
  try {
    await templatesStore.fetchRiskFactors(templateId, params)
  } catch (error) {
    console.error('Failed to fetch factors:', error)
  }
}

// Refresh all content data with optional notification
const refreshContent = async (showNotification = true) => {
  if (isRefreshing.value) return

  isRefreshing.value = true
  console.log('Refreshing all content data for template:', templateId)

  try {
    // Force clear existing data first to ensure fresh fetch
    console.log('Clearing existing store data...')

    // Clear store data for this template
    if (templatesStore.templateContent.value) {
      templatesStore.templateContent.value[templateId] = []
    }
    if (templatesStore.riskCategories.value) {
      templatesStore.riskCategories.value[templateId] = []
    }
    if (templatesStore.riskTopics.value) {
      templatesStore.riskTopics.value[templateId] = []
    }
    if (templatesStore.riskFactors.value) {
      templatesStore.riskFactors.value[templateId] = []
    }

    // Now fetch fresh data from API
    console.log('Fetching fresh template content...')
    // Pass limit: 0 to get all contents without pagination (default backend limit is 20)
    await templatesStore.fetchTemplateContent(templateId, { limit: 0 })

    console.log('Fetching fresh risk categories...')
    await templatesStore.fetchRiskCategories(templateId)

    console.log('Fetching fresh risk topics...')
    await templatesStore.fetchRiskTopics(templateId)

    console.log('Fetching fresh risk factors...')
    await templatesStore.fetchRiskFactors(templateId)

    // Show success notification only if requested
    if (showNotification) {
      const toast = useToast()
      toast.add({
        title: '重新整理完成',
        description: '內容資料已更新',
        color: 'green'
      })
    }

    console.log('Content refresh completed successfully')
  } catch (error) {
    console.error('Refresh content error:', error)
    const toast = useToast()
    toast.add({
      title: '重新整理失敗',
      description: '無法更新內容資料，請稍後再試',
      color: 'red'
    })
  } finally {
    isRefreshing.value = false
  }
}


onMounted(async () => {
  console.log('Template content page mounted for template ID:', templateId)

  // Initialize store if not already done
  if (!templatesStore.templates || templatesStore.templates.length === 0) {
    await templatesStore.initialize()
  }

  // Always fetch fresh data from API to ensure data consistency with database
  try {
    console.log('Fetching fresh template content from API...')
    // Pass limit: 0 to get all contents without pagination (default backend limit is 20)
    await templatesStore.fetchTemplateContent(templateId, { limit: 0 })
  } catch (error) {
    console.error('Failed to fetch template content:', error)
  }

  try {
    console.log('Fetching fresh risk categories from API...')
    await templatesStore.fetchRiskCategories(templateId)
  } catch (error) {
    console.error('Failed to fetch risk categories:', error)
  }

  try {
    console.log('Fetching fresh risk topics from API...')
    await templatesStore.fetchRiskTopics(templateId)
  } catch (error) {
    console.error('Failed to fetch risk topics:', error)
  }

  try {
    console.log('Fetching fresh risk factors from API...')
    await templatesStore.fetchRiskFactors(templateId)
  } catch (error) {
    console.error('Failed to fetch risk factors:', error)
  }
})
</script>