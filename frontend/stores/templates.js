import { defineStore } from 'pinia'
import apiClient from '~/utils/api.js'

export const useTemplatesStore = defineStore('templates', () => {
  // State
  const templates = ref([])
  const templateContent = ref({})
  const riskCategories = ref({})
  const riskTopics = ref({})
  const riskFactors = ref({})
  const loading = ref(false)
  const error = ref(null)

  // Loading states for specific operations
  const isAddingTemplate = ref(false)
  const isUpdatingTemplate = ref(false)
  const isDeletingTemplate = ref(false)
  const isCopyingTemplate = ref(false)
  const isFetchingTemplates = ref(false)

  // Getters
  const getTemplateById = (id) => {
    return computed(() => templates.value.find(t => parseInt(t.id) === parseInt(id)))
  }

  const getTemplateContent = (templateId) => {
    return computed(() => templateContent.value[templateId] || [])
  }

  const getRiskCategories = (templateId) => {
    return computed(() => riskCategories.value[templateId] || [])
  }

  const getRiskTopics = (templateId) => {
    return computed(() => riskTopics.value[templateId] || [])
  }

  const getRiskFactors = (templateId) => {
    return computed(() => riskFactors.value[templateId] || [])
  }

  // Enhanced error handling for new API format
  const handleError = (err, defaultMessage = 'An error occurred') => {
    console.error('Store error:', err)

    // Handle enhanced API error format
    if (err.error && typeof err.error === 'object') {
      error.value = err.error.message || defaultMessage

      // Log additional error information for debugging
      if (err.error.suggestion) {
        console.info('API Suggestion:', err.error.suggestion)
      }
      if (err.error.validation_errors) {
        console.warn('Validation Errors:', err.error.validation_errors)
      }
    }
    // Handle legacy error format
    else if (err.response?.data?.message) {
      error.value = err.response.data.message
    }
    // Handle standard error format
    else {
      error.value = err.message || defaultMessage
    }
  }

  const clearError = () => {
    error.value = null
  }

  // API Actions
  const fetchTemplates = async (params = {}) => {
    isFetchingTemplates.value = true
    loading.value = true
    clearError()
    
    try {
      const response = await apiClient.templates.getAll(params)
      
      if (response.success && response.data) {
        // Handle both formats: direct array or nested templates array
        const templatesArray = Array.isArray(response.data)
          ? response.data
          : (response.data.templates || [])

        // Ensure all templates have risk_topics_enabled field (default to false)
        // Convert database 1/0 to boolean true/false
        const templatesWithDefaults = templatesArray.map(template => {
          const rawValue = template.risk_topics_enabled
          let riskTopicsEnabled = false

          // Check for truthy values: 1, true, "1", "true"
          if (rawValue === 1 || rawValue === true || rawValue === '1' || rawValue === 'true') {
            riskTopicsEnabled = true
          }
          // Check for falsy values: 0, false, "0", "false", null, undefined
          else if (rawValue === 0 || rawValue === false || rawValue === '0' || rawValue === 'false' || rawValue === null || rawValue === undefined) {
            riskTopicsEnabled = false
          }

          return {
            ...template,
            risk_topics_enabled: riskTopicsEnabled
          }
        })
        templates.value = templatesWithDefaults
        return response.data
      } else {
        throw new Error('Invalid response format')
      }
    } catch (err) {
      handleError(err, 'Failed to fetch templates')
      throw err
    } finally {
      isFetchingTemplates.value = false
      loading.value = false
    }
  }

  const addTemplate = async (templateData) => {
    isAddingTemplate.value = true
    clearError()
    
    // Optimistic update
    const tempId = Date.now()
    const optimisticTemplate = {
      id: tempId,
      version_name: templateData.versionName,
      description: templateData.description || '',
      status: templateData.status || 'active',
      risk_topics_enabled: templateData.risk_topics_enabled ?? true,
      content_count: 0,
      category_count: 0,
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString()
    }
    
    templates.value.unshift(optimisticTemplate)
    
    try {
      const response = await apiClient.templates.create({
        version_name: templateData.versionName,
        description: templateData.description,
        status: templateData.status
      })
      
      if (response.success && response.data) {
        // Replace optimistic template with real data
        const index = templates.value.findIndex(t => t.id === tempId)
        if (index !== -1) {
          templates.value[index] = response.data
        }
        
        // Initialize empty content and categories for new template
        templateContent.value[response.data.id] = []
        riskCategories.value[response.data.id] = []
        
        return response.data
      } else {
        throw new Error('Failed to create template')
      }
    } catch (err) {
      // Rollback optimistic update
      const index = templates.value.findIndex(t => t.id === tempId)
      if (index !== -1) {
        templates.value.splice(index, 1)
      }
      
      handleError(err, 'Failed to create template')
      throw err
    } finally {
      isAddingTemplate.value = false
    }
  }

  const updateTemplate = async (id, templateData) => {
    isUpdatingTemplate.value = true
    clearError()
    
    // Find template and store original data for rollback
    const template = templates.value.find(t => t.id === id)
    if (!template) {
      throw new Error('Template not found')
    }
    
    const originalData = { ...template }
    
    // Optimistic update
    Object.assign(template, {
      version_name: templateData.versionName,
      description: templateData.description !== undefined ? templateData.description : template.description,
      status: templateData.status !== undefined ? templateData.status : template.status,
      updated_at: new Date().toISOString()
    })
    
    try {
      const response = await apiClient.templates.update(id, {
        version_name: templateData.versionName,
        description: templateData.description,
        status: templateData.status
      })
      
      if (response.success && response.data) {
        // Update with server response, ensuring boolean conversion for risk_topics_enabled
        const rawValue = response.data.risk_topics_enabled
        let riskTopicsEnabled = false

        // Check for truthy values: 1, true, "1", "true"
        if (rawValue === 1 || rawValue === true || rawValue === '1' || rawValue === 'true') {
          riskTopicsEnabled = true
        }
        // Check for falsy values: 0, false, "0", "false"
        else if (rawValue === 0 || rawValue === false || rawValue === '0' || rawValue === 'false') {
          riskTopicsEnabled = false
        }

        const updatedData = {
          ...response.data,
          risk_topics_enabled: riskTopicsEnabled
        }
        Object.assign(template, updatedData)
        return updatedData
      } else {
        throw new Error('Failed to update template')
      }
    } catch (err) {
      // Rollback optimistic update
      Object.assign(template, originalData)
      
      handleError(err, 'Failed to update template')
      throw err
    } finally {
      isUpdatingTemplate.value = false
    }
  }

  const deleteTemplate = async (id) => {
    isDeletingTemplate.value = true
    clearError()
    
    // Find template and store for rollback
    const index = templates.value.findIndex(t => t.id === id)
    if (index === -1) {
      throw new Error('Template not found')
    }
    
    const template = templates.value[index]
    const contentBackup = templateContent.value[id]
    const categoriesBackup = riskCategories.value[id]
    
    // Optimistic removal
    templates.value.splice(index, 1)
    delete templateContent.value[id]
    delete riskCategories.value[id]
    
    try {
      const response = await apiClient.templates.delete(id)
      
      if (response.success) {
        return response
      } else {
        throw new Error('Failed to delete template')
      }
    } catch (err) {
      // Rollback optimistic removal
      templates.value.splice(index, 0, template)
      if (contentBackup) templateContent.value[id] = contentBackup
      if (categoriesBackup) riskCategories.value[id] = categoriesBackup
      
      handleError(err, 'Failed to delete template')
      throw err
    } finally {
      isDeletingTemplate.value = false
    }
  }

  const copyTemplate = async (id, newVersionName) => {
    isCopyingTemplate.value = true
    clearError()
    
    const original = templates.value.find(t => t.id === id)
    if (!original) {
      throw new Error('Original template not found')
    }
    
    try {
      const response = await apiClient.templates.copy(id, {
        version_name: newVersionName,
        description: original.description
      })
      
      if (response.success && response.data) {
        // Convert risk_topics_enabled to boolean
        const rawValue = response.data.risk_topics_enabled
        let riskTopicsEnabled = false

        if (rawValue === 1 || rawValue === true || rawValue === '1' || rawValue === 'true') {
          riskTopicsEnabled = true
        } else if (rawValue === 0 || rawValue === false || rawValue === '0' || rawValue === 'false') {
          riskTopicsEnabled = false
        }

        const copiedTemplate = {
          ...response.data,
          risk_topics_enabled: riskTopicsEnabled
        }

        // Add new template to the list
        templates.value.unshift(copiedTemplate)

        // Initialize empty content and categories for new template
        templateContent.value[copiedTemplate.id] = []
        riskCategories.value[copiedTemplate.id] = []

        return copiedTemplate
      } else {
        // Extract API error message
        const apiMessage = response.message || response.error?.message || 'Failed to copy template'
        throw new Error(apiMessage)
      }
    } catch (err) {
      handleError(err, 'Failed to copy template')
      // Extract and throw the actual error message from API response
      // API client returns error in format: { error: { message: '...' } }
      const errorMessage = err.error?.message || err.data?.message || err.message || 'Failed to copy template'
      throw new Error(errorMessage)
    } finally {
      isCopyingTemplate.value = false
    }
  }

  // Template Content CRUD operations (these will use API in future)
  const fetchTemplateContent = async (templateId, params = {}) => {
    try {
      const response = await apiClient.contents.getByTemplate(templateId, params)

      if (response.success && response.data) {
        const contents = response.data.contents || response.data.factors || []
        console.log(`[Store] Fetched ${contents.length} contents for template ${templateId}`)
        console.log('[Store] API pagination info:', response.data.pagination)
        templateContent.value[templateId] = contents
        return response.data
      }
    } catch (err) {
      handleError(err, 'Failed to fetch template content')
      throw err
    }
  }

  const addTemplateContent = async (templateId, contentData) => {
    try {
      const response = await apiClient.contents.create(templateId, contentData)
      
      if (response.success && response.data) {
        if (!templateContent.value[templateId]) {
          templateContent.value[templateId] = []
        }
        templateContent.value[templateId].push(response.data)
        return response.data
      }
    } catch (err) {
      handleError(err, 'Failed to add template content')
      throw err
    }
  }

  const updateTemplateContent = async (templateId, contentId, contentData) => {
    try {
      const response = await apiClient.contents.update(templateId, contentId, contentData)
      
      if (response.success && response.data) {
        const content = templateContent.value[templateId]?.find(c => c.id === contentId)
        if (content) {
          Object.assign(content, response.data)
        }
        return response.data
      }
    } catch (err) {
      handleError(err, 'Failed to update template content')
      throw err
    }
  }

  const deleteTemplateContent = async (templateId, contentId) => {
    try {
      const response = await apiClient.contents.delete(templateId, contentId)
      
      if (response.success) {
        const index = templateContent.value[templateId]?.findIndex(c => c.id === contentId)
        if (index > -1) {
          templateContent.value[templateId].splice(index, 1)
        }
        return response
      }
    } catch (err) {
      handleError(err, 'Failed to delete template content')
      throw err
    }
  }

  const reorderTemplateContent = async (templateId, orders) => {
    try {
      // Format the orders array with sort_order for each item
      const formattedOrders = orders.map((item, index) => ({
        id: item.id,
        sort_order: index + 1
      }))

      const response = await apiClient.contents.reorder(templateId, { orders: formattedOrders })

      if (response.success) {
        // Update local order based on the orders array
        if (templateContent.value[templateId]) {
          templateContent.value[templateId] = orders.map((item, index) => ({
            ...item,
            sort_order: index + 1
          }))
        }
        return response
      }
    } catch (err) {
      handleError(err, 'Failed to reorder template content')
      throw err
    }
  }

  const batchImportTemplateContent = async (templateId, items) => {
    try {
      const response = await apiClient.contents.batchImport(templateId, { items })

      if (response.success) {
        // Refresh the template content after successful import
        await fetchTemplateContent(templateId)
        return response
      }
    } catch (err) {
      handleError(err, 'Failed to batch import template content')
      throw err
    }
  }

  // Risk Categories CRUD operations (these will use API in future)
  const fetchRiskCategories = async (templateId) => {
    try {
      const response = await apiClient.categories.getByTemplate(templateId)
      
      if (response.success && response.data) {
        riskCategories.value[templateId] = response.data.categories || []
        return response.data
      }
    } catch (err) {
      handleError(err, 'Failed to fetch risk categories')
      throw err
    }
  }

  const addRiskCategory = async (templateId, categoryData) => {
    try {
      const response = await apiClient.categories.create(templateId, categoryData)
      
      if (response.success && response.data) {
        if (!riskCategories.value[templateId]) {
          riskCategories.value[templateId] = []
        }
        riskCategories.value[templateId].push(response.data)
        return response.data
      }
    } catch (err) {
      handleError(err, 'Failed to add risk category')
      throw err
    }
  }

  const updateRiskCategory = async (templateId, categoryId, categoryData) => {
    try {
      const response = await apiClient.categories.update(templateId, categoryId, categoryData)
      
      if (response.success && response.data) {
        const category = riskCategories.value[templateId]?.find(c => c.id === categoryId)
        if (category) {
          Object.assign(category, response.data)
        }
        return response.data
      }
    } catch (err) {
      handleError(err, 'Failed to update risk category')
      throw err
    }
  }

  const deleteRiskCategory = async (templateId, categoryId) => {
    try {
      const response = await apiClient.categories.delete(templateId, categoryId)

      if (response.success) {
        const index = riskCategories.value[templateId]?.findIndex(c => c.id === categoryId)
        if (index > -1) {
          riskCategories.value[templateId].splice(index, 1)
        }
        return response
      }
    } catch (err) {
      handleError(err, 'Failed to delete risk category')
      throw err
    }
  }

  // Risk Topics CRUD operations
  const fetchRiskTopics = async (templateId) => {
    try {
      const response = await apiClient.topics.getByTemplate(templateId)

      if (response.success && response.data) {
        riskTopics.value[templateId] = response.data.topics || []
        return response.data
      }
    } catch (err) {
      handleError(err, 'Failed to fetch risk topics')
      throw err
    }
  }

  const addRiskTopic = async (templateId, topicData) => {
    try {
      const response = await apiClient.topics.create(templateId, topicData)

      if (response.success && response.data) {
        if (!riskTopics.value[templateId]) {
          riskTopics.value[templateId] = []
        }
        riskTopics.value[templateId].push(response.data)
        return response.data
      }
    } catch (err) {
      handleError(err, 'Failed to add risk topic')
      throw err
    }
  }

  const updateRiskTopic = async (templateId, topicId, topicData) => {
    try {
      const response = await apiClient.topics.update(templateId, topicId, topicData)

      if (response.success && response.data) {
        const topic = riskTopics.value[templateId]?.find(t => t.id === topicId)
        if (topic) {
          Object.assign(topic, response.data)
        }
        return response.data
      }
    } catch (err) {
      handleError(err, 'Failed to update risk topic')
      throw err
    }
  }

  const deleteRiskTopic = async (templateId, topicId) => {
    try {
      const response = await apiClient.topics.delete(templateId, topicId)

      if (response.success) {
        const index = riskTopics.value[templateId]?.findIndex(t => t.id === topicId)
        if (index > -1) {
          riskTopics.value[templateId].splice(index, 1)
        }
        return response
      }
    } catch (err) {
      handleError(err, 'Failed to delete risk topic')
      throw err
    }
  }

  const reorderRiskTopics = async (templateId, orders) => {
    try {
      const response = await apiClient.topics.reorder(templateId, { orders })

      if (response.success) {
        // Update local order based on the orders array
        if (riskTopics.value[templateId]) {
          riskTopics.value[templateId] = orders.map((item, index) => ({
            ...item,
            sort_order: index + 1
          }))
        }
        return response
      }
    } catch (err) {
      handleError(err, 'Failed to reorder risk topics')
      throw err
    }
  }

  // Risk Factors CRUD operations
  const fetchRiskFactors = async (templateId, params = {}) => {
    try {
      console.log('[Store] Fetching risk factors with params:', params)
      const response = await apiClient.factors.getByTemplate(templateId, params)

      if (response.success && response.data) {
        riskFactors.value[templateId] = response.data.factors || []
        console.log('[Store] Risk factors loaded:', riskFactors.value[templateId].length, 'factors')
        return response.data
      }
    } catch (err) {
      handleError(err, 'Failed to fetch risk factors')
      throw err
    }
  }

  const addRiskFactor = async (templateId, factorData) => {
    try {
      const response = await apiClient.factors.create(templateId, factorData)

      if (response.success && response.data) {
        if (!riskFactors.value[templateId]) {
          riskFactors.value[templateId] = []
        }
        riskFactors.value[templateId].push(response.data)
        return response.data
      }
    } catch (err) {
      handleError(err, 'Failed to add risk factor')
      throw err
    }
  }

  const updateRiskFactor = async (templateId, factorId, factorData) => {
    try {
      const response = await apiClient.factors.update(templateId, factorId, factorData)

      if (response.success && response.data) {
        const factor = riskFactors.value[templateId]?.find(f => f.id === factorId)
        if (factor) {
          Object.assign(factor, response.data)
        }
        return response.data
      }
    } catch (err) {
      handleError(err, 'Failed to update risk factor')
      throw err
    }
  }

  const deleteRiskFactor = async (templateId, factorId) => {
    try {
      const response = await apiClient.factors.delete(templateId, factorId)

      if (response.success) {
        const index = riskFactors.value[templateId]?.findIndex(f => f.id === factorId)
        if (index > -1) {
          riskFactors.value[templateId].splice(index, 1)
        }
        return response
      }
    } catch (err) {
      handleError(err, 'Failed to delete risk factor')
      throw err
    }
  }

  const reorderRiskFactors = async (templateId, orders) => {
    try {
      const response = await apiClient.factors.reorder(templateId, { orders })

      if (response.success) {
        // Update local order based on the orders array
        if (riskFactors.value[templateId]) {
          riskFactors.value[templateId] = orders.map((item, index) => ({
            ...item,
            sort_order: index + 1
          }))
        }
        return response
      }
    } catch (err) {
      handleError(err, 'Failed to reorder risk factors')
      throw err
    }
  }

  const getRiskFactorStats = async (templateId) => {
    try {
      const response = await apiClient.factors.stats(templateId)

      if (response.success && response.data) {
        return response.data
      }
    } catch (err) {
      handleError(err, 'Failed to fetch risk factor statistics')
      throw err
    }
  }

  // Toggle risk topics for a template
  const toggleRiskTopics = async (templateId) => {
    const template = templates.value.find(t => t.id === templateId)
    if (!template) {
      throw new Error('Template not found')
    }

    // Store original value for rollback (ensure boolean)
    const originalValue = template.risk_topics_enabled === true || template.risk_topics_enabled === 1
    console.log(`[Toggle] Original value:`, originalValue, `(raw: ${template.risk_topics_enabled})`)

    // Optimistic update - toggle the boolean
    const newValue = !originalValue
    const index = templates.value.findIndex(t => t.id === templateId)
    if (index !== -1) {
      templates.value[index] = {
        ...templates.value[index],
        risk_topics_enabled: newValue
      }
    }
    console.log(`[Toggle] New value (optimistic):`, newValue)

    try {
      // Call API to persist the change
      console.log(`[Toggle] Sending to API:`, {
        version_name: template.version_name,
        risk_topics_enabled: newValue
      })

      const response = await apiClient.templates.update(templateId, {
        version_name: template.version_name,
        description: template.description,
        status: template.status,
        risk_topics_enabled: newValue
      })

      console.log(`[Toggle] API Response:`, response.data)

      if (response.success && response.data) {
        // Convert server response (1/0/string) to boolean (true/false)
        const rawValue = response.data.risk_topics_enabled
        let serverValue = false

        // Check for truthy values: 1, true, "1", "true"
        if (rawValue === 1 || rawValue === true || rawValue === '1' || rawValue === 'true') {
          serverValue = true
        }
        // Check for falsy values: 0, false, "0", "false"
        else if (rawValue === 0 || rawValue === false || rawValue === '0' || rawValue === 'false') {
          serverValue = false
        }

        // Force update by finding the template again and reassigning
        const index = templates.value.findIndex(t => t.id === templateId)
        if (index !== -1) {
          templates.value[index] = {
            ...templates.value[index],
            risk_topics_enabled: serverValue
          }
        }
        console.log(`[Toggle] Final value from server:`, serverValue, `(raw: ${rawValue}, type: ${typeof rawValue})`)
        console.log(`[Toggle] Updated template in store:`, templates.value[index])
      } else {
        throw new Error('Failed to update risk topics setting')
      }
    } catch (err) {
      // Rollback on error
      console.log(`[Toggle] Error, rolling back to:`, originalValue)
      const index = templates.value.findIndex(t => t.id === templateId)
      if (index !== -1) {
        templates.value[index] = {
          ...templates.value[index],
          risk_topics_enabled: originalValue
        }
      }
      handleError(err, 'Failed to toggle risk topics')
      throw err
    }
  }

  // Initialize store by fetching templates
  const initialize = async () => {
    try {
      await fetchTemplates()
    } catch (err) {
      console.warn('Failed to initialize templates store:', err)
    }
  }

  return {
    // State
    templates: readonly(templates),
    templateContent: readonly(templateContent),
    riskCategories: readonly(riskCategories),
    riskTopics: readonly(riskTopics),
    riskFactors: readonly(riskFactors),
    loading: readonly(loading),
    error: readonly(error),

    // Loading states
    isAddingTemplate: readonly(isAddingTemplate),
    isUpdatingTemplate: readonly(isUpdatingTemplate),
    isDeletingTemplate: readonly(isDeletingTemplate),
    isCopyingTemplate: readonly(isCopyingTemplate),
    isFetchingTemplates: readonly(isFetchingTemplates),

    // Getters
    getTemplateById,
    getTemplateContent,
    getRiskCategories,
    getRiskTopics,
    getRiskFactors,

    // Actions
    fetchTemplates,
    addTemplate,
    updateTemplate,
    deleteTemplate,
    copyTemplate,
    toggleRiskTopics,

    // Content operations
    fetchTemplateContent,
    addTemplateContent,
    updateTemplateContent,
    deleteTemplateContent,
    reorderTemplateContent,
    batchImportTemplateContent,

    // Category operations
    fetchRiskCategories,
    addRiskCategory,
    updateRiskCategory,
    deleteRiskCategory,

    // Topic operations
    fetchRiskTopics,
    addRiskTopic,
    updateRiskTopic,
    deleteRiskTopic,
    reorderRiskTopics,

    // Factor operations
    fetchRiskFactors,
    addRiskFactor,
    updateRiskFactor,
    deleteRiskFactor,
    reorderRiskFactors,
    getRiskFactorStats,

    // Utility
    clearError,
    initialize
  }
})