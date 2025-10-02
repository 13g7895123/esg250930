// 本地公司管理 composable
// 負責與後端 API 整合，取代 localStorage 存儲

// 使用 Nuxt 代理路徑，在開發環境會自動轉發到後端
const API_BASE_URL = '/api/v1/risk-assessment'

// Reactive state for local companies
const localCompanies = ref([])
const loading = ref(false)
const error = ref(null)

// Load companies from API
const loadLocalCompanies = async (search = null, page = 1, limit = 20, sort = 'created_at', order = 'desc') => {
  if (!process.client) return

  loading.value = true
  error.value = null

  try {
    const params = new URLSearchParams()
    if (search) params.append('search', search)
    if (page) params.append('page', page)
    if (limit) params.append('limit', limit)
    if (sort) params.append('sort', sort)
    if (order) params.append('order', order)

    const fullApiUrl = `${API_BASE_URL}/local-companies?${params.toString()}`
    console.log('=== 🚀 API CALL ===')
    console.log('📁 Frontend File: /frontend/composables/useLocalCompanies.js')
    console.log('⚙️  Function: loadLocalCompanies')
    console.log('🌐 Full API URL:', fullApiUrl)
    console.log('🔧 Backend File: /backend/app/Controllers/Api/V1/LocalCompaniesController.php')
    console.log('📝 Backend Method: index (GET)')
    console.log('===================')

    const response = await $fetch(`${API_BASE_URL}/local-companies?${params.toString()}`)
    
    if (response.success && response.data) {
      // Transform API response to match frontend format
      const transformedCompanies = (response.data.companies || []).map(company => ({
        id: company.id,
        companyName: company.company_name,
        externalId: company.external_id,
        abbreviation: company.abbreviation || ''
      }))
      
      localCompanies.value = transformedCompanies
      return {
        companies: transformedCompanies,
        pagination: response.data.pagination || {}
      }
    }
    
    return { companies: [], pagination: {} }
  } catch (err) {
    console.error('Error loading local companies:', err)
    error.value = err.message || '載入公司列表失敗'
    return { companies: [], pagination: {} }
  } finally {
    loading.value = false
  }
}

// Add a new local company
const addLocalCompany = async (companyData) => {
  if (!process.client) return null

  loading.value = true
  error.value = null
  
  try {
    const response = await $fetch(`${API_BASE_URL}/local-companies`, {
      method: 'POST',
      body: {
        company_name: companyData.company_name || companyData.companyName,
        external_id: companyData.external_id || companyData.externalId,
        abbreviation: companyData.abbreviation || ''
      }
    })
    
    if (response.success && response.data) {
      // Transform API response to match frontend format
      const newCompany = {
        id: response.data.id,
        companyName: response.data.company_name,
        externalId: response.data.external_id,
        abbreviation: response.data.abbreviation || ''
      }
      
      // Add to local state
      localCompanies.value.unshift(newCompany)
      
      return newCompany
    }
    
    return null
  } catch (err) {
    console.error('Error adding local company:', err)
    error.value = err.message || '新增公司失敗'
    return null
  } finally {
    loading.value = false
  }
}

// Update a local company
const updateLocalCompany = async (id, companyData) => {
  if (!process.client) return null

  loading.value = true
  error.value = null
  
  try {
    const response = await $fetch(`${API_BASE_URL}/local-companies/${id}`, {
      method: 'PUT',
      body: {
        company_name: companyData.company_name || companyData.companyName,
        abbreviation: companyData.abbreviation || ''
      }
    })
    
    if (response.success && response.data) {
      // Transform API response to match frontend format
      const updatedCompany = {
        id: response.data.id,
        companyName: response.data.company_name,
        externalId: response.data.external_id,
        abbreviation: response.data.abbreviation || ''
      }
      
      // Update local state
      const index = localCompanies.value.findIndex(company => company.id == id)
      if (index !== -1) {
        localCompanies.value[index] = updatedCompany
      }
      
      return updatedCompany
    }
    
    return null
  } catch (err) {
    console.error('Error updating local company:', err)
    error.value = err.message || '更新公司失敗'
    return null
  } finally {
    loading.value = false
  }
}

// Delete a local company
const deleteLocalCompany = async (id) => {
  if (!process.client) return false

  loading.value = true
  error.value = null
  
  try {
    const response = await $fetch(`${API_BASE_URL}/local-companies/${id}`, {
      method: 'DELETE'
    })
    
    if (response.success) {
      // Remove from local state
      localCompanies.value = localCompanies.value.filter(company => company.id != id)
      return true
    }
    
    return false
  } catch (err) {
    console.error('Error deleting local company:', err)
    error.value = err.message || '刪除公司失敗'
    return false
  } finally {
    loading.value = false
  }
}

// Find company by external ID
const findByExternalId = async (externalId) => {
  if (!process.client) return null

  try {
    const response = await $fetch(`${API_BASE_URL}/local-companies/external/${externalId}`)
    
    if (response.success && response.data) {
      return {
        id: response.data.id,
        companyName: response.data.company_name,
        externalId: response.data.external_id,
        abbreviation: response.data.abbreviation || ''
      }
    }
    
    return null
  } catch (err) {
    // Company not found is expected for new companies
    if (err.statusCode === 404) {
      return null
    }
    console.error('Error finding company by external ID:', err)
    return null
  }
}

// Get company statistics
const getCompanyStats = async () => {
  if (!process.client) return null

  try {
    const response = await $fetch(`${API_BASE_URL}/local-companies/stats`)
    
    if (response.success && response.data) {
      return response.data
    }
    
    return null
  } catch (err) {
    console.error('Error getting company stats:', err)
    return null
  }
}

// Refresh companies data
const refreshLocalCompanies = async () => {
  return await loadLocalCompanies()
}

// Find company by internal ID
const findById = async (id) => {
  if (!process.client) return null

  // Try to find in cached data first
  const cachedCompany = localCompanies.value.find(company => company.id == id)
  if (cachedCompany) {
    return cachedCompany
  }

  // If not found in cache, load from API
  try {
    const response = await $fetch(`${API_BASE_URL}/local-companies/${id}`)
    
    if (response.success && response.data) {
      return {
        id: response.data.id,
        companyName: response.data.company_name,
        externalId: response.data.external_id,
        abbreviation: response.data.abbreviation || ''
      }
    }
    
    return null
  } catch (err) {
    // Company not found
    if (err.statusCode === 404) {
      return null
    }
    console.error('Error finding company by ID:', err)
    return null
  }
}

// Get company name by ID (helper function)
const getCompanyNameById = async (id) => {
  const company = await findById(id)
  return company ? company.companyName : null
}

// Clear error state
const clearError = () => {
  error.value = null
}

// Resolve company by external ID (auto-create if not exists)
const resolveCompany = async (companyId) => {
  if (!process.client) return null

  loading.value = true
  error.value = null

  try {
    const response = await $fetch(`${API_BASE_URL}/local-companies/resolve`, {
      method: 'POST',
      body: {
        company_id: companyId
      }
    })

    if (response.success && response.data) {
      const company = {
        id: response.data.id,
        companyName: response.data.company_name,
        externalId: response.data.external_id,
        abbreviation: response.data.abbreviation || '',
        source: response.data.source // 'existing' or 'created'
      }

      // Add to local state if not already there
      const existingIndex = localCompanies.value.findIndex(c => c.id == company.id)
      if (existingIndex === -1) {
        localCompanies.value.unshift(company)
      }

      return company
    }

    return null
  } catch (err) {
    console.error('Error resolving company:', err)
    error.value = err.message || '解析公司失敗'
    return null
  } finally {
    loading.value = false
  }
}

export const useLocalCompanies = () => {
  return {
    // State
    localCompanies: readonly(localCompanies),
    loading: readonly(loading),
    error: readonly(error),

    // Actions
    loadLocalCompanies,
    addLocalCompany,
    updateLocalCompany,
    deleteLocalCompany,
    findByExternalId,
    findById,
    getCompanyNameById,
    getCompanyStats,
    refreshLocalCompanies,
    resolveCompany,
    clearError
  }
}