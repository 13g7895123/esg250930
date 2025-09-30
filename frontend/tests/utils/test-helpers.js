import { vi } from 'vitest'

/**
 * Test utilities and helpers for the frontend tests
 */

/**
 * Create a mock API response with the standard format
 */
export const createMockApiResponse = (data, success = true, message = '') => {
  return {
    success,
    message,
    data
  }
}

/**
 * Create a mock template object
 */
export const createMockTemplate = (overrides = {}) => {
  return {
    id: 1,
    version_name: 'Test Template',
    description: 'Test description',
    status: 'active',
    content_count: 0,
    category_count: 0,
    created_at: '2024-01-15 10:30:00',
    updated_at: '2024-01-15 10:30:00',
    ...overrides
  }
}

/**
 * Create a mock template content object
 */
export const createMockTemplateContent = (overrides = {}) => {
  return {
    id: 1,
    template_id: 1,
    category_id: 1,
    topic: 'Test Topic',
    description: 'Test description',
    scoring_method: 'scale_1_5',
    weight: 1.0,
    sort_order: 1,
    created_at: '2024-01-15 10:30:00',
    updated_at: '2024-01-15 10:30:00',
    ...overrides
  }
}

/**
 * Create a mock risk category object
 */
export const createMockRiskCategory = (overrides = {}) => {
  return {
    id: 1,
    template_id: 1,
    category_name: 'Test Category',
    category_code: 'TEST',
    description: 'Test category description',
    sort_order: 1,
    created_at: '2024-01-15 10:30:00',
    updated_at: '2024-01-15 10:30:00',
    ...overrides
  }
}

/**
 * Create a mock paginated response
 */
export const createMockPaginatedResponse = (items, pagination = {}) => {
  const defaultPagination = {
    current_page: 1,
    per_page: 20,
    total: items.length,
    total_pages: Math.ceil(items.length / 20),
    has_next: false,
    has_prev: false,
    ...pagination
  }

  return createMockApiResponse({
    templates: items,
    pagination: defaultPagination
  })
}

/**
 * Create a mock error response
 */
export const createMockErrorResponse = (message = 'An error occurred', errors = []) => {
  return {
    success: false,
    message,
    errors
  }
}

/**
 * Mock localStorage for tests
 */
export const createMockLocalStorage = () => {
  const store = {}
  
  return {
    getItem: vi.fn((key) => store[key] || null),
    setItem: vi.fn((key, value) => {
      store[key] = value
    }),
    removeItem: vi.fn((key) => {
      delete store[key]
    }),
    clear: vi.fn(() => {
      Object.keys(store).forEach(key => delete store[key])
    }),
    get length() {
      return Object.keys(store).length
    },
    key: vi.fn((index) => Object.keys(store)[index] || null),
    // Internal store for testing
    __store: store
  }
}

/**
 * Mock $fetch function with configurable responses
 */
export const createMock$fetch = () => {
  const mock = vi.fn()
  
  // Helper methods to configure responses
  mock.mockResolveWith = (response) => {
    mock.mockResolvedValue(response)
    return mock
  }
  
  mock.mockRejectWith = (error) => {
    mock.mockRejectedValue(error)
    return mock
  }
  
  mock.mockResolveOnce = (response) => {
    mock.mockResolvedValueOnce(response)
    return mock
  }
  
  mock.mockRejectOnce = (error) => {
    mock.mockRejectedValueOnce(error)
    return mock
  }
  
  return mock
}

/**
 * Mock Nuxt composables
 */
export const createMockNuxtComposables = () => {
  return {
    useRoute: vi.fn(() => ({
      query: {},
      params: {},
      path: '/test'
    })),
    useRouter: vi.fn(() => ({
      push: vi.fn(),
      replace: vi.fn(),
      back: vi.fn(),
      forward: vi.fn()
    })),
    navigateTo: vi.fn(),
    useRuntimeConfig: vi.fn(() => ({
      public: {
        apiBaseUrl: 'http://localhost:8000/api'
      }
    })),
    usePageTitle: vi.fn()
  }
}

/**
 * Create a mock Pinia store for templates
 */
export const createMockTemplatesStore = (initialState = {}) => {
  const defaultState = {
    templates: [],
    templateContent: {},
    riskCategories: {},
    pagination: null,
    isLoading: false,
    isCreating: false,
    isUpdating: false,
    isDeleting: false,
    error: null
  }

  const state = { ...defaultState, ...initialState }

  return {
    ...state,
    // Actions
    fetchTemplates: vi.fn().mockResolvedValue(),
    addTemplate: vi.fn().mockResolvedValue(createMockTemplate()),
    updateTemplate: vi.fn().mockResolvedValue(createMockTemplate()),
    deleteTemplate: vi.fn().mockResolvedValue(),
    copyTemplate: vi.fn().mockResolvedValue(createMockTemplate({ id: 2 })),
    fetchTemplateContent: vi.fn().mockResolvedValue(),
    fetchRiskCategories: vi.fn().mockResolvedValue(),
    
    // Getters
    getTemplateById: vi.fn((id) => state.templates.find(t => t.id === id)),
    activeTemplates: state.templates.filter(t => t.status === 'active'),
    templatesCount: state.templates.length,
    getTemplateContent: vi.fn((templateId) => state.templateContent[templateId] || []),
    getRiskCategories: vi.fn((templateId) => state.riskCategories[templateId] || [])
  }
}

/**
 * Create a mock Vue component for testing
 */
export const createMockComponent = (name, template = '<div></div>', props = []) => {
  return {
    name,
    template,
    props,
    emits: ['update:modelValue']
  }
}

/**
 * Wait for all pending promises to resolve
 */
export const flushPromises = () => {
  return new Promise(resolve => setTimeout(resolve, 0))
}

/**
 * Simulate async operation delay
 */
export const delay = (ms = 100) => {
  return new Promise(resolve => setTimeout(resolve, ms))
}

/**
 * Create a controlled promise for testing async operations
 */
export const createControlledPromise = () => {
  let resolver, rejector
  
  const promise = new Promise((resolve, reject) => {
    resolver = resolve
    rejector = reject
  })
  
  return {
    promise,
    resolve: resolver,
    reject: rejector
  }
}

/**
 * Generate test data for templates
 */
export const generateTemplates = (count = 5) => {
  return Array.from({ length: count }, (_, index) => 
    createMockTemplate({
      id: index + 1,
      version_name: `Template ${index + 1}`,
      description: `Description for template ${index + 1}`,
      content_count: Math.floor(Math.random() * 10),
      category_count: Math.floor(Math.random() * 5),
      created_at: new Date(2024, 0, index + 1).toISOString()
    })
  )
}

/**
 * Generate test data for template content
 */
export const generateTemplateContent = (templateId, count = 3) => {
  return Array.from({ length: count }, (_, index) =>
    createMockTemplateContent({
      id: index + 1,
      template_id: templateId,
      topic: `Topic ${index + 1}`,
      description: `Description for topic ${index + 1}`,
      sort_order: index + 1
    })
  )
}

/**
 * Generate test data for risk categories
 */
export const generateRiskCategories = (templateId, count = 4) => {
  const categoryNames = ['財務風險', '營運風險', '市場風險', '法規風險']
  
  return Array.from({ length: count }, (_, index) =>
    createMockRiskCategory({
      id: index + 1,
      template_id: templateId,
      category_name: categoryNames[index] || `Category ${index + 1}`,
      category_code: `CAT${index + 1}`,
      sort_order: index + 1
    })
  )
}

/**
 * Mock console methods to prevent noise in tests
 */
export const mockConsole = () => {
  const originalConsole = { ...console }
  
  console.log = vi.fn()
  console.warn = vi.fn()
  console.error = vi.fn()
  console.info = vi.fn()
  console.debug = vi.fn()
  
  return {
    restore: () => {
      Object.assign(console, originalConsole)
    },
    getLogs: () => console.log.mock.calls,
    getWarnings: () => console.warn.mock.calls,
    getErrors: () => console.error.mock.calls
  }
}

/**
 * Assert that an error was thrown with a specific message
 */
export const expectError = async (fn, expectedMessage) => {
  try {
    await fn()
    throw new Error('Expected function to throw an error')
  } catch (error) {
    expect(error.message).toBe(expectedMessage)
  }
}

/**
 * Test data sets for common scenarios
 */
export const testData = {
  templates: {
    empty: [],
    single: [createMockTemplate()],
    multiple: generateTemplates(3),
    withContent: generateTemplates(2).map(template => ({
      ...template,
      content_count: 5,
      category_count: 3
    }))
  },
  
  apiResponses: {
    success: createMockApiResponse({ templates: generateTemplates(3) }),
    empty: createMockApiResponse({ templates: [], pagination: { total: 0 } }),
    error: createMockErrorResponse('Server error'),
    networkError: new Error('Network error'),
    validationError: createMockErrorResponse('Validation failed', ['Version name is required'])
  }
}