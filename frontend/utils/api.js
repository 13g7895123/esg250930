/**
 * Enhanced API Client for Risk Assessment Management System
 * Compatible with enhanced backend API with improved error handling and response format
 */

class ApiClient {
  constructor() {
    // 使用 Nuxt 代理路徑，在開發環境會自動轉發到後端 (localhost:9218)
    // 在生產環境中，這個路徑會根據實際部署的後端地址進行調整
    this.baseURL = '/api/v1/risk-assessment'
    this.requestId = 0
  }

  /**
   * Generate unique request ID for tracking
   */
  generateRequestId() {
    return `req_${Date.now()}_${++this.requestId}`
  }

  /**
   * Enhanced request method with better error handling and logging
   */
  async request(endpoint, options = {}) {
    const requestId = this.generateRequestId()
    const url = `${this.baseURL}${endpoint}`
    const startTime = Date.now()

    const config = {
      ...options,
      headers: {
        'Content-Type': 'application/json',
        'X-Request-ID': requestId,
        ...options.headers,
      },
    }

    try {
      console.log(`[API ${requestId}] ${config.method || 'GET'} ${endpoint}`)

      const response = await $fetch(url, config)
      const endTime = Date.now()
      const duration = endTime - startTime

      // Log successful requests with performance info
      console.log(`[API ${requestId}] ✅ Success (${duration}ms)`, {
        endpoint,
        method: config.method || 'GET',
        duration,
        meta: response.meta || null
      })

      // Return the enhanced response format
      return {
        success: true,
        data: response.data || response,
        meta: response.meta || {
          api_version: '1.0',
          execution_time: duration,
          timestamp: new Date().toISOString(),
          request_id: requestId
        },
        message: response.message || 'Request successful'
      }
    } catch (error) {
      const endTime = Date.now()
      const duration = endTime - startTime

      // Enhanced error handling for the new backend error format
      const enhancedError = this.handleEnhancedError(error, requestId, duration, endpoint)

      console.error(`[API ${requestId}] ❌ Error (${duration}ms)`, enhancedError)
      throw enhancedError
    }
  }

  /**
   * Handle enhanced error responses from the backend
   */
  handleEnhancedError(error, requestId, duration, endpoint) {
    const baseError = {
      success: false,
      data: null,
      meta: {
        api_version: '1.0',
        execution_time: duration,
        timestamp: new Date().toISOString(),
        request_id: requestId,
        endpoint
      }
    }

    // Handle enhanced backend error format
    if (error.data && typeof error.data === 'object') {
      return {
        ...baseError,
        error: {
          code: error.data.error?.code || error.status || 'UNKNOWN_ERROR',
          message: error.data.error?.message || error.data.message || 'An unknown error occurred',
          details: error.data.error?.details || null,
          context: error.data.error?.context || null,
          suggestion: error.data.error?.suggestion || null,
          validation_errors: error.data.error?.validation_errors || null
        },
        meta: {
          ...baseError.meta,
          ...error.data.meta
        }
      }
    }

    // Handle standard errors
    return {
      ...baseError,
      error: {
        code: error.status || error.statusCode || 'NETWORK_ERROR',
        message: error.message || 'Network request failed',
        details: error.statusText || null,
        context: { url: error.url || endpoint },
        suggestion: 'Please check your internet connection and try again.'
      }
    }
  }

  // Templates API
  templates = {
    // Get all templates
    getAll: (params = {}) => {
      const queryString = new URLSearchParams(params).toString()
      const endpoint = `/templates${queryString ? `?${queryString}` : ''}`
      return this.request(endpoint, { method: 'GET' })
    },

    // Get single template
    getById: (id) => {
      return this.request(`/templates/${id}`, { method: 'GET' })
    },

    // Create new template
    create: (data) => {
      return this.request('/templates', {
        method: 'POST',
        body: data
      })
    },

    // Update template
    update: (id, data) => {
      return this.request(`/templates/${id}`, {
        method: 'PUT',
        body: data
      })
    },

    // Delete template
    delete: (id) => {
      return this.request(`/templates/${id}`, { method: 'DELETE' })
    },

    // Copy template
    copy: (id, data) => {
      return this.request(`/templates/${id}/copy`, {
        method: 'POST',
        body: data
      })
    }
  }

  // Categories API
  categories = {
    // Get categories for a template
    getByTemplate: (templateId) => {
      return this.request(`/templates/${templateId}/categories`, { method: 'GET' })
    },

    // Create category
    create: (templateId, data) => {
      return this.request(`/templates/${templateId}/categories`, {
        method: 'POST',
        body: data
      })
    },

    // Update category
    update: (templateId, id, data) => {
      return this.request(`/templates/${templateId}/categories/${id}`, {
        method: 'PUT',
        body: data
      })
    },

    // Delete category
    delete: (templateId, id) => {
      return this.request(`/templates/${templateId}/categories/${id}`, { method: 'DELETE' })
    }
  }

  // Template Contents API
  contents = {
    // Get contents for a template
    getByTemplate: (templateId, params = {}) => {
      const queryString = new URLSearchParams(params).toString()
      const endpoint = `/templates/${templateId}/contents${queryString ? `?${queryString}` : ''}`
      return this.request(endpoint, { method: 'GET' })
    },

    // Get single content
    getById: (templateId, id) => {
      return this.request(`/templates/${templateId}/contents/${id}`, { method: 'GET' })
    },

    // Create content
    create: (templateId, data) => {
      return this.request(`/templates/${templateId}/contents`, {
        method: 'POST',
        body: data
      })
    },

    // Update content
    update: (templateId, id, data) => {
      return this.request(`/templates/${templateId}/contents/${id}`, {
        method: 'PUT',
        body: data
      })
    },

    // Delete content
    delete: (templateId, id) => {
      return this.request(`/templates/${templateId}/contents/${id}`, { method: 'DELETE' })
    },

    // Reorder contents
    reorder: (templateId, data) => {
      return this.request(`/templates/${templateId}/contents/reorder`, {
        method: 'PUT',
        body: data
      })
    }
  }

  // Risk Topics API
  topics = {
    // Get topics for a template
    getByTemplate: (templateId, params = {}) => {
      const queryString = new URLSearchParams(params).toString()
      const endpoint = `/templates/${templateId}/topics${queryString ? `?${queryString}` : ''}`
      return this.request(endpoint, { method: 'GET' })
    },

    // Create topic
    create: (templateId, data) => {
      return this.request(`/templates/${templateId}/topics`, {
        method: 'POST',
        body: data
      })
    },

    // Update topic
    update: (templateId, id, data) => {
      return this.request(`/templates/${templateId}/topics/${id}`, {
        method: 'PUT',
        body: data
      })
    },

    // Delete topic
    delete: (templateId, id) => {
      return this.request(`/templates/${templateId}/topics/${id}`, { method: 'DELETE' })
    },

    // Reorder topics
    reorder: (templateId, data) => {
      return this.request(`/templates/${templateId}/topics/reorder`, {
        method: 'PUT',
        body: data
      })
    }
  }

  // Risk Factors API
  factors = {
    // Get factors for a template
    getByTemplate: (templateId, params = {}) => {
      const queryString = new URLSearchParams(params).toString()
      const endpoint = `/templates/${templateId}/factors${queryString ? `?${queryString}` : ''}`
      return this.request(endpoint, { method: 'GET' })
    },

    // Create factor
    create: (templateId, data) => {
      return this.request(`/templates/${templateId}/factors`, {
        method: 'POST',
        body: data
      })
    },

    // Update factor
    update: (templateId, id, data) => {
      return this.request(`/templates/${templateId}/factors/${id}`, {
        method: 'PUT',
        body: data
      })
    },

    // Delete factor
    delete: (templateId, id) => {
      return this.request(`/templates/${templateId}/factors/${id}`, { method: 'DELETE' })
    },

    // Reorder factors
    reorder: (templateId, data) => {
      return this.request(`/templates/${templateId}/factors/reorder`, {
        method: 'PUT',
        body: data
      })
    },

    // Get factor statistics
    stats: (templateId) => {
      return this.request(`/templates/${templateId}/factors/stats`, { method: 'GET' })
    }
  }
}

// Create singleton instance
const apiClient = new ApiClient()

export default apiClient