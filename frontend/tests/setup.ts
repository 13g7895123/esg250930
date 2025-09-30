import { vi } from 'vitest'

// Mock Vue Composition API functions
global.ref = vi.fn((value) => ({ value }))
global.computed = vi.fn((fn) => ({ value: fn() }))
global.reactive = vi.fn((obj) => obj)
global.onMounted = vi.fn()
global.onUnmounted = vi.fn()
global.watch = vi.fn()
global.watchEffect = vi.fn()
global.readonly = vi.fn((obj) => obj)

// Mock $fetch globally
global.$fetch = vi.fn()

// Mock navigateTo globally  
global.navigateTo = vi.fn()

// Mock definePageMeta globally
global.definePageMeta = vi.fn()

// Mock usePageTitle
global.usePageTitle = vi.fn()

// Mock useRoute
global.useRoute = vi.fn(() => ({
  query: {}
}))

// Mock useRuntimeConfig
global.useRuntimeConfig = vi.fn(() => ({
  public: {
    apiBaseUrl: 'http://localhost:8000/api'
  }
}))

// Mock useApi globally to fix import issues
global.useApi = vi.fn(() => ({
  get: vi.fn(),
  post: vi.fn(),
  put: vi.fn(),
  patch: vi.fn(),
  delete: vi.fn(),
  apiRequest: vi.fn(),
  getAuthToken: vi.fn(),
  getAuthHeaders: vi.fn(),
  setAuthToken: vi.fn(),
  clearAuthToken: vi.fn()
}))

// Mock useTemplatesStore
global.useTemplatesStore = vi.fn(() => ({
  templates: { value: [] },
  loading: { value: false },
  error: { value: null },
  isAddingTemplate: { value: false },
  isUpdatingTemplate: { value: false },
  isDeletingTemplate: { value: false },
  isCopyingTemplate: { value: false },
  isFetchingTemplates: { value: false },
  fetchTemplates: vi.fn(),
  addTemplate: vi.fn(),
  updateTemplate: vi.fn(),
  deleteTemplate: vi.fn(),
  copyTemplate: vi.fn(),
  initialize: vi.fn()
}))

// Mock other Pinia stores
global.useAuthStore = vi.fn(() => ({
  user: { value: null },
  isLoggedIn: { value: false },
  login: vi.fn(),
  logout: vi.fn()
}))

// Mock localStorage
Object.defineProperty(window, 'localStorage', {
  value: {
    getItem: vi.fn(),
    setItem: vi.fn(),
    removeItem: vi.fn(),
    clear: vi.fn(),
  },
  writable: true
})

// Mock process
global.process = {
  client: true,
  dev: false,
  env: {
    NODE_ENV: 'test'
  }
}