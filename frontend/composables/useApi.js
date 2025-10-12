import { storage } from '~/utils/storage'

/**
 * Base API composable for handling HTTP requests
 */
export const useApi = () => {
  const config = useRuntimeConfig()

  // Authentication toggle - set to false to disable authentication checks
  // Change this to true when authentication is ready to be enabled
  const ENABLE_AUTH = false

  // Get base URL from environment - handle development vs production
  const getBaseURL = () => {
    // Always use the full API URL (no proxy in development)
    const apiBaseUrl = config.public.apiBaseUrl || config.public.backendUrl

    if (apiBaseUrl) {
      return apiBaseUrl
    }

    // Fallback for development - directly connect to backend
    const isDev = process.dev || process.env.NODE_ENV === 'development'
    if (isDev) {
      return '/api/v1'
    }

    // Ensure we never use relative paths in production
    return 'https://project.mercylife.cc/api'
  }

  const baseURL = getBaseURL()

  // Get authentication token using SSR-safe storage
  const getAuthToken = () => {
    return storage.getItem('auth_token')
  }
  
  // Get authentication headers
  const getAuthHeaders = () => {
    // If authentication is disabled, return empty headers
    if (!ENABLE_AUTH) {
      return {}
    }

    const token = getAuthToken()
    if (token) {
      return {
        'Authorization': `Bearer ${token}`
      }
    }
    return {}
  }

  /**
   * Generic API request handler
   */
  const apiRequest = async (endpoint, options = {}) => {
    const authHeaders = getAuthHeaders()
    
    const defaultOptions = {
      baseURL,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...authHeaders,
        ...options.headers
      },
      ...options
    }

    try {
      console.log(`[API] ${defaultOptions.method || 'GET'} ${baseURL}${endpoint}`)
      const response = await $fetch(endpoint, defaultOptions)
      console.log(`[API] Success: ${baseURL}${endpoint}`)
      return {
        success: true,
        data: response,
        error: null
      }
    } catch (error) {
      console.error(`[API] Error ${defaultOptions.method || 'GET'} ${baseURL}${endpoint}:`, error)
      console.error('[API] Full URL:', `${baseURL}${endpoint}`)
      console.error('[API] Options:', defaultOptions)
      
      // Handle authentication errors (only if authentication is enabled)
      if (ENABLE_AUTH && (error.status === 401 || error.statusCode === 401)) {
        console.warn('[API] Authentication error - clearing auth state')

        // Clear authentication data using SSR-safe storage
        storage.removeItem('auth_token')
        storage.removeItem('auth_user')

        // Redirect to login page (only if not already on login page)
        if (process.client && !window.location.pathname.includes('/auth/login')) {
          try {
            await navigateTo('/auth/login')
          } catch (navError) {
            console.warn('[API] Navigation failed, redirecting via location:', navError)
            window.location.href = '/auth/login'
          }
        }
      }
      
      // Provide more detailed error information
      const errorDetails = {
        message: error.data?.message || error.message || '請求失敗',
        status: error.status || error.statusCode || 500,
        statusText: error.statusText || error.statusMessage || 'Internal Server Error',
        url: `${baseURL}${endpoint}`,
        method: defaultOptions.method || 'GET',
        errors: error.data?.errors || null
      }
      
      // Special handling for different error status codes
      if (errorDetails.status === 404) {
        errorDetails.message = `API endpoint not found: ${errorDetails.url}`
      } else if (errorDetails.status === 401) {
        errorDetails.message = error.data?.message || '登入已過期，請重新登入'
      } else if (errorDetails.status === 403) {
        // Preserve the specific 403 error message from backend
        errorDetails.message = error.data?.message || '權限不足'
      } else if (errorDetails.status === 422) {
        errorDetails.message = error.data?.message || '表單驗證失敗'
      } else if (errorDetails.status >= 500) {
        errorDetails.message = error.data?.message || '伺服器錯誤，請稍後再試'
      }
      
      return {
        success: false,
        data: null,
        error: errorDetails
      }
    }
  }

  /**
   * GET request
   */
  const get = async (endpoint, params = {}) => {
    return await apiRequest(endpoint, {
      method: 'GET',
      params
    })
  }

  /**
   * POST request
   */
  const post = async (endpoint, body = {}) => {
    return await apiRequest(endpoint, {
      method: 'POST',
      body
    })
  }

  /**
   * PUT request
   */
  const put = async (endpoint, body = {}) => {
    return await apiRequest(endpoint, {
      method: 'PUT',
      body
    })
  }

  /**
   * PATCH request
   */
  const patch = async (endpoint, body = {}) => {
    return await apiRequest(endpoint, {
      method: 'PATCH',
      body
    })
  }

  /**
   * DELETE request
   */
  const del = async (endpoint) => {
    return await apiRequest(endpoint, {
      method: 'DELETE'
    })
  }

  // Token management utilities
  const setAuthToken = (token) => {
    if (process.client) {
      localStorage.setItem('auth_token', token)
    }
  }

  const clearAuthToken = () => {
    if (process.client) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
    }
  }

  /**
   * === Modular API Methods ===
   * Organized by domain for better maintainability
   */

  // Risk Assessment Module
  const riskAssessment = {
    // Company Assessments
    getByCompany: async (companyId, userId = null) => {
      const params = userId ? { userId } : {}
      return await get(`/risk-assessment/company-assessments/company/${companyId}`, params)
    },
    create: async (data) => await post('/risk-assessment/company-assessments', data),
    update: async (id, data) => await put(`/risk-assessment/company-assessments/${id}`, data),
    delete: async (id) => await del(`/risk-assessment/company-assessments/${id}`),
    copy: async (id, options) => await post(`/risk-assessment/company-assessments/${id}/copy`, options),
  }

  // Question Management Module
  const questionManagement = {
    // Structure
    getStructure: async (assessmentId) =>
      await get(`/question-management/assessment/${assessmentId}/structure`),
    syncFromTemplate: async (assessmentId) =>
      await post(`/question-management/assessment/${assessmentId}/sync-from-template`),
    clearStructure: async (assessmentId) =>
      await post(`/question-management/assessment/${assessmentId}/clear`),
    getStats: async (assessmentId) =>
      await get(`/question-management/assessment/${assessmentId}/stats`),

    // Categories
    getCategories: async (assessmentId) =>
      await get(`/question-management/assessment/${assessmentId}/categories`),
    createCategory: async (assessmentId, data) =>
      await post(`/question-management/assessment/${assessmentId}/categories`, data),
    updateCategory: async (categoryId, data) =>
      await put(`/question-management/categories/${categoryId}`, data),
    deleteCategory: async (categoryId) =>
      await del(`/question-management/categories/${categoryId}`),

    // Topics
    getTopics: async (assessmentId, params = {}) => {
      const queryParams = new URLSearchParams(params).toString()
      const endpoint = queryParams
        ? `/question-management/assessment/${assessmentId}/topics?${queryParams}`
        : `/question-management/assessment/${assessmentId}/topics`
      return await get(endpoint)
    },
    createTopic: async (assessmentId, data) =>
      await post(`/question-management/assessment/${assessmentId}/topics`, data),
    updateTopic: async (topicId, data) =>
      await put(`/question-management/topics/${topicId}`, data),
    deleteTopic: async (topicId) =>
      await del(`/question-management/topics/${topicId}`),

    // Factors
    getFactors: async (assessmentId, params = {}) => {
      const queryParams = new URLSearchParams(params).toString()
      const endpoint = queryParams
        ? `/question-management/assessment/${assessmentId}/factors?${queryParams}`
        : `/question-management/assessment/${assessmentId}/factors`
      return await get(endpoint)
    },
    createFactor: async (assessmentId, data) =>
      await post(`/question-management/assessment/${assessmentId}/factors`, data),
    updateFactor: async (factorId, data) =>
      await put(`/question-management/factors/${factorId}`, data),
    deleteFactor: async (factorId) =>
      await del(`/question-management/factors/${factorId}`),
    reorderFactors: async (assessmentId, orders) =>
      await put(`/question-management/assessment/${assessmentId}/factors/reorder`, { orders }),

    // Contents
    getContent: async (contentId) =>
      await get(`/question-management/contents/${contentId}`),
    updateContent: async (contentId, data) =>
      await put(`/question-management/contents/${contentId}`, data),
  }

  // Personnel Module
  const personnel = {
    getByCompany: async (companyId) =>
      await get(`/personnel/companies/${companyId}/personnel-assignments`),
    syncPersonnel: async (companyId) =>
      await post(`/personnel/companies/${companyId}/sync`),
    getAssignments: async (companyId, assessmentId) =>
      await get(`/personnel/companies/${companyId}/assessments/${assessmentId}/assignments`),
    createAssignment: async (data) =>
      await post('/personnel/assignments', data),
    batchCreateAssignments: async (data) =>
      await post('/personnel/assignments/batch', data),
    removeAssignment: async (data) =>
      await del('/personnel/assignments', data),
    removePersonnelFromAssessment: async (companyId, assessmentId, personnelId) =>
      await del(`/personnel/companies/${companyId}/assessments/${assessmentId}/personnel/${personnelId}`),
    updateAssignmentStatus: async (assignmentId, status) =>
      await put(`/personnel/assignments/${assignmentId}/status`, { status }),
  }

  // Companies Module
  const companies = {
    getAll: async (params = {}) => {
      // Filter out null/undefined values to prevent "null" string in query
      const filteredParams = Object.entries(params)
        .filter(([_, value]) => value !== null && value !== undefined && value !== '')
        .reduce((acc, [key, value]) => ({ ...acc, [key]: value }), {})

      const queryParams = new URLSearchParams(filteredParams).toString()
      const endpoint = queryParams ? `/local-companies?${queryParams}` : '/local-companies'
      return await get(endpoint)
    },
    getById: async (id) =>
      await get(`/local-companies/${id}`),
    getByExternalId: async (externalId) =>
      await get(`/local-companies/external/${externalId}`),
    create: async (data) =>
      await post('/local-companies', data),
    update: async (id, data) =>
      await put(`/local-companies/${id}`, data),
    delete: async (id) =>
      await del(`/local-companies/${id}`),
    getStats: async () =>
      await get('/local-companies/stats'),
    resolve: async (companyData) =>
      await post('/local-companies/resolve', companyData),
  }

  return {
    // Generic methods
    get,
    post,
    put,
    patch,
    delete: del,
    apiRequest,
    getAuthToken,
    getAuthHeaders,
    setAuthToken,
    clearAuthToken,

    // Modular API methods
    riskAssessment,
    questionManagement,
    personnel,
    companies,
  }
}