import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useTemplatesStore } from '@/stores/templates'
import apiClient from '@/utils/api'

// Mock the API client
vi.mock('@/utils/api', () => ({
  default: {
    templates: {
      getAll: vi.fn(),
      getById: vi.fn(),
      create: vi.fn(),
      update: vi.fn(),
      delete: vi.fn(),
      copy: vi.fn()
    },
    categories: {
      getByTemplate: vi.fn(),
      create: vi.fn(),
      update: vi.fn(),
      delete: vi.fn()
    },
    contents: {
      getByTemplate: vi.fn(),
      getById: vi.fn(),
      create: vi.fn(),
      update: vi.fn(),
      delete: vi.fn(),
      reorder: vi.fn()
    }
  }
}))

describe('Templates Store', () => {
  let store

  beforeEach(() => {
    // Create a fresh Pinia instance and activate it
    setActivePinia(createPinia())
    store = useTemplatesStore()
    
    // Reset all mocks
    vi.resetAllMocks()
    
    // Mock localStorage
    const localStorageMock = {
      getItem: vi.fn(),
      setItem: vi.fn(),
      removeItem: vi.fn(),
      clear: vi.fn()
    }
    Object.defineProperty(window, 'localStorage', {
      value: localStorageMock,
      writable: true
    })
  })

  afterEach(() => {
    vi.restoreAllMocks()
  })

  describe('store initialization', () => {
    it('should initialize with empty state', () => {
      expect(store.templates).toEqual([])
      expect(store.templateContent).toEqual({})
      expect(store.riskCategories).toEqual({})
    })

    it('should initialize loading states', () => {
      expect(store.isLoading).toBe(false)
      expect(store.isCreating).toBe(false)
      expect(store.isUpdating).toBe(false)
      expect(store.isDeleting).toBe(false)
    })

    it('should initialize error state', () => {
      expect(store.error).toBe(null)
    })
  })

  describe('fetchTemplates action', () => {
    it('should fetch templates successfully', async () => {
      const mockApiResponse = {
        success: true,
        data: {
          templates: [
            {
              id: 1,
              version_name: 'Template 1',
              description: 'Description 1',
              status: 'active',
              content_count: 5,
              category_count: 3,
              created_at: '2024-01-15 10:30:00',
              updated_at: '2024-01-15 10:30:00'
            },
            {
              id: 2,
              version_name: 'Template 2',
              description: 'Description 2',
              status: 'active',
              content_count: 8,
              category_count: 4,
              created_at: '2024-02-20 14:20:00',
              updated_at: '2024-02-20 14:20:00'
            }
          ],
          pagination: {
            current_page: 1,
            per_page: 20,
            total: 2,
            total_pages: 1,
            has_next: false,
            has_prev: false
          }
        }
      }

      apiClient.templates.getAll.mockResolvedValue(mockApiResponse)

      await store.fetchTemplates()

      expect(store.isLoading).toBe(false)
      expect(store.error).toBe(null)
      expect(store.templates).toHaveLength(2)
      expect(store.templates[0]).toEqual({
        id: 1,
        version_name: 'Template 1',
        description: 'Description 1',
        status: 'active',
        content_count: 5,
        category_count: 3,
        created_at: '2024-01-15 10:30:00',
        updated_at: '2024-01-15 10:30:00'
      })
      expect(store.pagination).toEqual(mockApiResponse.data.pagination)
      expect(apiClient.templates.getAll).toHaveBeenCalledWith()
    })

    it('should fetch templates with query parameters', async () => {
      const mockApiResponse = {
        success: true,
        data: {
          templates: [],
          pagination: { current_page: 1, per_page: 10, total: 0 }
        }
      }

      apiClient.templates.getAll.mockResolvedValue(mockApiResponse)

      const params = { page: 2, per_page: 10, search: 'test' }
      await store.fetchTemplates(params)

      expect(apiClient.templates.getAll).toHaveBeenCalledWith(params)
    })

    it('should set loading state during fetch', async () => {
      const mockApiResponse = {
        success: true,
        data: { templates: [], pagination: {} }
      }

      // Create a promise that we can control
      let resolvePromise
      const mockPromise = new Promise((resolve) => {
        resolvePromise = resolve
      })

      apiClient.templates.getAll.mockReturnValue(mockPromise)

      // Start the fetch operation
      const fetchPromise = store.fetchTemplates()

      // Check loading state is true during fetch
      expect(store.isLoading).toBe(true)

      // Resolve the API call
      resolvePromise(mockApiResponse)
      await fetchPromise

      // Check loading state is false after fetch
      expect(store.isLoading).toBe(false)
    })

    it('should handle API errors gracefully', async () => {
      const apiError = new Error('Failed to fetch templates')
      apiClient.templates.getAll.mockRejectedValue(apiError)

      await store.fetchTemplates()

      expect(store.isLoading).toBe(false)
      expect(store.error).toBe('Failed to fetch templates')
      expect(store.templates).toEqual([])
    })

    it('should handle network errors', async () => {
      const networkError = new Error('Network error')
      apiClient.templates.getAll.mockRejectedValue(networkError)

      await store.fetchTemplates()

      expect(store.error).toBe('Network error')
    })
  })

  describe('addTemplate action', () => {
    it('should add template with optimistic update', async () => {
      const templateData = {
        version_name: 'New Template',
        description: 'New description'
      }

      const mockApiResponse = {
        success: true,
        data: {
          template: {
            id: 3,
            version_name: 'New Template',
            description: 'New description',
            status: 'active',
            content_count: 0,
            category_count: 0,
            created_at: '2024-03-01 09:00:00',
            updated_at: '2024-03-01 09:00:00'
          }
        }
      }

      apiClient.templates.create.mockResolvedValue(mockApiResponse)

      const result = await store.addTemplate(templateData)

      expect(store.isCreating).toBe(false)
      expect(store.error).toBe(null)
      expect(store.templates).toHaveLength(1)
      expect(store.templates[0]).toEqual(mockApiResponse.data.template)
      expect(result).toEqual(mockApiResponse.data.template)
      expect(apiClient.templates.create).toHaveBeenCalledWith(templateData)
    })

    it('should set creating state during add operation', async () => {
      const templateData = { version_name: 'New Template' }
      const mockApiResponse = {
        success: true,
        data: { template: { id: 1, ...templateData } }
      }

      let resolvePromise
      const mockPromise = new Promise((resolve) => {
        resolvePromise = resolve
      })

      apiClient.templates.create.mockReturnValue(mockPromise)

      // Start the add operation
      const addPromise = store.addTemplate(templateData)

      // Check creating state is true during operation
      expect(store.isCreating).toBe(true)

      // Resolve the API call
      resolvePromise(mockApiResponse)
      await addPromise

      // Check creating state is false after operation
      expect(store.isCreating).toBe(false)
    })

    it('should handle creation errors and rollback optimistic update', async () => {
      const templateData = { version_name: 'New Template' }
      const apiError = new Error('Creation failed')
      apiClient.templates.create.mockRejectedValue(apiError)

      // Store initial state
      const initialTemplatesCount = store.templates.length

      await store.addTemplate(templateData)

      expect(store.isCreating).toBe(false)
      expect(store.error).toBe('Creation failed')
      expect(store.templates).toHaveLength(initialTemplatesCount) // Should be rolled back
    })

    it('should validate required fields before API call', async () => {
      const invalidData = { description: 'Missing version name' }

      await expect(store.addTemplate(invalidData)).rejects.toThrow('version_name is required')
      expect(apiClient.templates.create).not.toHaveBeenCalled()
    })
  })

  describe('updateTemplate action', () => {
    beforeEach(() => {
      // Set up initial template in store
      store.templates = [
        {
          id: 1,
          version_name: 'Original Template',
          description: 'Original description',
          status: 'active'
        }
      ]
    })

    it('should update template successfully', async () => {
      const templateId = 1
      const updateData = {
        version_name: 'Updated Template',
        description: 'Updated description'
      }

      const mockApiResponse = {
        success: true,
        data: {
          template: {
            id: 1,
            version_name: 'Updated Template',
            description: 'Updated description',
            status: 'active',
            updated_at: '2024-03-01 10:00:00'
          }
        }
      }

      apiClient.templates.update.mockResolvedValue(mockApiResponse)

      const result = await store.updateTemplate(templateId, updateData)

      expect(store.isUpdating).toBe(false)
      expect(store.error).toBe(null)
      expect(store.templates[0].version_name).toBe('Updated Template')
      expect(store.templates[0].description).toBe('Updated description')
      expect(result).toEqual(mockApiResponse.data.template)
      expect(apiClient.templates.update).toHaveBeenCalledWith(templateId, updateData)
    })

    it('should set updating state during update operation', async () => {
      const templateId = 1
      const updateData = { version_name: 'Updated Template' }
      const mockApiResponse = {
        success: true,
        data: { template: { id: 1, ...updateData } }
      }

      let resolvePromise
      const mockPromise = new Promise((resolve) => {
        resolvePromise = resolve
      })

      apiClient.templates.update.mockReturnValue(mockPromise)

      // Start the update operation
      const updatePromise = store.updateTemplate(templateId, updateData)

      // Check updating state is true during operation
      expect(store.isUpdating).toBe(true)

      // Resolve the API call
      resolvePromise(mockApiResponse)
      await updatePromise

      // Check updating state is false after operation
      expect(store.isUpdating).toBe(false)
    })

    it('should handle update errors and rollback changes', async () => {
      const templateId = 1
      const updateData = { version_name: 'Updated Template' }
      const originalTemplate = { ...store.templates[0] }
      const apiError = new Error('Update failed')

      apiClient.templates.update.mockRejectedValue(apiError)

      await store.updateTemplate(templateId, updateData)

      expect(store.isUpdating).toBe(false)
      expect(store.error).toBe('Update failed')
      expect(store.templates[0]).toEqual(originalTemplate) // Should be rolled back
    })

    it('should handle non-existent template ID', async () => {
      const nonExistentId = 999
      const updateData = { version_name: 'Updated Template' }

      await expect(store.updateTemplate(nonExistentId, updateData))
        .rejects.toThrow('Template not found')
      expect(apiClient.templates.update).not.toHaveBeenCalled()
    })
  })

  describe('deleteTemplate action', () => {
    beforeEach(() => {
      // Set up initial templates in store
      store.templates = [
        { id: 1, version_name: 'Template 1' },
        { id: 2, version_name: 'Template 2' }
      ]
      store.templateContent = { 1: [], 2: [] }
      store.riskCategories = { 1: [], 2: [] }
    })

    it('should delete template successfully', async () => {
      const templateId = 1
      const mockApiResponse = { success: true, message: 'Template deleted' }

      apiClient.templates.delete.mockResolvedValue(mockApiResponse)

      await store.deleteTemplate(templateId)

      expect(store.isDeleting).toBe(false)
      expect(store.error).toBe(null)
      expect(store.templates).toHaveLength(1)
      expect(store.templates[0].id).toBe(2)
      expect(store.templateContent[1]).toBeUndefined()
      expect(store.riskCategories[1]).toBeUndefined()
      expect(apiClient.templates.delete).toHaveBeenCalledWith(templateId)
    })

    it('should set deleting state during delete operation', async () => {
      const templateId = 1
      const mockApiResponse = { success: true, message: 'Template deleted' }

      let resolvePromise
      const mockPromise = new Promise((resolve) => {
        resolvePromise = resolve
      })

      apiClient.templates.delete.mockReturnValue(mockPromise)

      // Start the delete operation
      const deletePromise = store.deleteTemplate(templateId)

      // Check deleting state is true during operation
      expect(store.isDeleting).toBe(true)

      // Resolve the API call
      resolvePromise(mockApiResponse)
      await deletePromise

      // Check deleting state is false after operation
      expect(store.isDeleting).toBe(false)
    })

    it('should handle delete errors and rollback deletion', async () => {
      const templateId = 1
      const originalTemplates = [...store.templates]
      const originalContent = { ...store.templateContent }
      const originalCategories = { ...store.riskCategories }
      const apiError = new Error('Delete failed')

      apiClient.templates.delete.mockRejectedValue(apiError)

      await store.deleteTemplate(templateId)

      expect(store.isDeleting).toBe(false)
      expect(store.error).toBe('Delete failed')
      expect(store.templates).toEqual(originalTemplates) // Should be rolled back
      expect(store.templateContent).toEqual(originalContent)
      expect(store.riskCategories).toEqual(originalCategories)
    })

    it('should handle non-existent template ID', async () => {
      const nonExistentId = 999

      await expect(store.deleteTemplate(nonExistentId))
        .rejects.toThrow('Template not found')
      expect(apiClient.templates.delete).not.toHaveBeenCalled()
    })
  })

  describe('copyTemplate action', () => {
    beforeEach(() => {
      // Set up initial template in store
      store.templates = [
        {
          id: 1,
          version_name: 'Original Template',
          description: 'Original description'
        }
      ]
      store.templateContent = {
        1: [
          { id: 'content1', topic: 'Topic 1', category_id: 'cat1' }
        ]
      }
      store.riskCategories = {
        1: [
          { id: 'cat1', category: 'Category 1' }
        ]
      }
    })

    it('should copy template successfully', async () => {
      const templateId = 1
      const newVersionName = 'Copied Template'

      const mockApiResponse = {
        success: true,
        data: {
          template: {
            id: 2,
            version_name: 'Copied Template',
            description: 'Original description',
            status: 'active',
            copied_from: 1,
            created_at: '2024-03-01 11:00:00',
            updated_at: '2024-03-01 11:00:00'
          }
        }
      }

      apiClient.templates.copy.mockResolvedValue(mockApiResponse)

      const result = await store.copyTemplate(templateId, newVersionName)

      expect(store.error).toBe(null)
      expect(store.templates).toHaveLength(2)
      expect(store.templates[0]).toEqual(mockApiResponse.data.template) // Should be added to beginning
      expect(result).toEqual(mockApiResponse.data.template)
      expect(apiClient.templates.copy).toHaveBeenCalledWith(templateId, {
        version_name: newVersionName
      })
    })

    it('should handle copy errors', async () => {
      const templateId = 1
      const newVersionName = 'Copied Template'
      const apiError = new Error('Copy failed')

      apiClient.templates.copy.mockRejectedValue(apiError)

      await expect(store.copyTemplate(templateId, newVersionName))
        .rejects.toThrow('Copy failed')
      expect(store.error).toBe('Copy failed')
      expect(store.templates).toHaveLength(1) // Should remain unchanged
    })

    it('should handle non-existent template ID', async () => {
      const nonExistentId = 999
      const newVersionName = 'Copied Template'

      await expect(store.copyTemplate(nonExistentId, newVersionName))
        .rejects.toThrow('Template not found')
      expect(apiClient.templates.copy).not.toHaveBeenCalled()
    })

    it('should validate new version name', async () => {
      const templateId = 1
      const emptyVersionName = ''

      await expect(store.copyTemplate(templateId, emptyVersionName))
        .rejects.toThrow('New version name is required')
      expect(apiClient.templates.copy).not.toHaveBeenCalled()
    })
  })

  describe('fetchTemplateContent action', () => {
    it('should fetch template content successfully', async () => {
      const templateId = 1
      const mockApiResponse = {
        success: true,
        data: {
          contents: [
            {
              id: 1,
              template_id: 1,
              category_id: 1,
              topic: 'Content Topic',
              description: 'Content description',
              sort_order: 1
            }
          ]
        }
      }

      apiClient.contents.getByTemplate.mockResolvedValue(mockApiResponse)

      await store.fetchTemplateContent(templateId)

      expect(store.templateContent[templateId]).toEqual(mockApiResponse.data.contents)
      expect(apiClient.contents.getByTemplate).toHaveBeenCalledWith(templateId)
    })

    it('should handle fetch content errors', async () => {
      const templateId = 1
      const apiError = new Error('Failed to fetch content')

      apiClient.contents.getByTemplate.mockRejectedValue(apiError)

      await store.fetchTemplateContent(templateId)

      expect(store.error).toBe('Failed to fetch content')
      expect(store.templateContent[templateId]).toBeUndefined()
    })
  })

  describe('fetchRiskCategories action', () => {
    it('should fetch risk categories successfully', async () => {
      const templateId = 1
      const mockApiResponse = {
        success: true,
        data: {
          categories: [
            {
              id: 1,
              template_id: 1,
              category_name: 'Financial Risk',
              category_code: 'FIN',
              sort_order: 1
            }
          ]
        }
      }

      apiClient.categories.getByTemplate.mockResolvedValue(mockApiResponse)

      await store.fetchRiskCategories(templateId)

      expect(store.riskCategories[templateId]).toEqual(mockApiResponse.data.categories)
      expect(apiClient.categories.getByTemplate).toHaveBeenCalledWith(templateId)
    })

    it('should handle fetch categories errors', async () => {
      const templateId = 1
      const apiError = new Error('Failed to fetch categories')

      apiClient.categories.getByTemplate.mockRejectedValue(apiError)

      await store.fetchRiskCategories(templateId)

      expect(store.error).toBe('Failed to fetch categories')
      expect(store.riskCategories[templateId]).toBeUndefined()
    })
  })

  describe('computed getters', () => {
    beforeEach(() => {
      store.templates = [
        { id: 1, version_name: 'Template 1', status: 'active' },
        { id: 2, version_name: 'Template 2', status: 'inactive' },
        { id: 3, version_name: 'Template 3', status: 'active' }
      ]
    })

    it('should get template by ID', () => {
      const template = store.getTemplateById(1)
      expect(template).toEqual({
        id: 1,
        version_name: 'Template 1',
        status: 'active'
      })
    })

    it('should return undefined for non-existent template ID', () => {
      const template = store.getTemplateById(999)
      expect(template).toBeUndefined()
    })

    it('should get active templates only', () => {
      const activeTemplates = store.activeTemplates
      expect(activeTemplates).toHaveLength(2)
      expect(activeTemplates.every(t => t.status === 'active')).toBe(true)
    })

    it('should get templates count', () => {
      expect(store.templatesCount).toBe(3)
    })

    it('should get template content for specific template', () => {
      store.templateContent = {
        1: [{ id: 'content1', topic: 'Topic 1' }],
        2: [{ id: 'content2', topic: 'Topic 2' }]
      }

      const content = store.getTemplateContent(1)
      expect(content).toEqual([{ id: 'content1', topic: 'Topic 1' }])
    })

    it('should return empty array for template with no content', () => {
      const content = store.getTemplateContent(999)
      expect(content).toEqual([])
    })

    it('should get risk categories for specific template', () => {
      store.riskCategories = {
        1: [{ id: 'cat1', category: 'Category 1' }],
        2: [{ id: 'cat2', category: 'Category 2' }]
      }

      const categories = store.getRiskCategories(1)
      expect(categories).toEqual([{ id: 'cat1', category: 'Category 1' }])
    })

    it('should return empty array for template with no categories', () => {
      const categories = store.getRiskCategories(999)
      expect(categories).toEqual([])
    })
  })

  describe('error handling', () => {
    it('should clear errors when starting new operation', async () => {
      // Set initial error
      store.error = 'Previous error'

      const mockApiResponse = {
        success: true,
        data: { templates: [], pagination: {} }
      }
      apiClient.templates.getAll.mockResolvedValue(mockApiResponse)

      await store.fetchTemplates()

      expect(store.error).toBe(null)
    })

    it('should handle malformed API responses', async () => {
      const malformedResponse = { invalid: 'response' }
      apiClient.templates.getAll.mockResolvedValue(malformedResponse)

      await store.fetchTemplates()

      expect(store.error).toBe('Invalid API response format')
      expect(store.templates).toEqual([])
    })

    it('should handle API responses with success: false', async () => {
      const errorResponse = {
        success: false,
        message: 'Operation failed',
        errors: ['Validation error']
      }
      apiClient.templates.getAll.mockResolvedValue(errorResponse)

      await store.fetchTemplates()

      expect(store.error).toBe('Operation failed')
      expect(store.templates).toEqual([])
    })
  })

  describe('optimistic updates and rollback', () => {
    it('should perform optimistic update for template creation', async () => {
      const templateData = { version_name: 'New Template' }

      // Simulate slow API response
      let resolvePromise
      const mockPromise = new Promise((resolve) => {
        resolvePromise = resolve
      })
      apiClient.templates.create.mockReturnValue(mockPromise)

      // Start the operation (don't await yet)
      const createPromise = store.addTemplate(templateData)

      // Template should be optimistically added
      expect(store.templates).toHaveLength(1)
      expect(store.templates[0].version_name).toBe('New Template')

      // Resolve the API call
      const apiResponse = {
        success: true,
        data: {
          template: { id: 1, version_name: 'New Template', status: 'active' }
        }
      }
      resolvePromise(apiResponse)
      await createPromise

      // Template should be updated with server response
      expect(store.templates[0]).toEqual(apiResponse.data.template)
    })

    it('should rollback optimistic update on API failure', async () => {
      const templateData = { version_name: 'New Template' }
      const initialCount = store.templates.length

      apiClient.templates.create.mockRejectedValue(new Error('Creation failed'))

      await store.addTemplate(templateData)

      // Should be rolled back to original state
      expect(store.templates).toHaveLength(initialCount)
      expect(store.error).toBe('Creation failed')
    })
  })
})