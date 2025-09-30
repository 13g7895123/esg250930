import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import apiClient from '@/utils/api'

// Mock $fetch globally
global.$fetch = vi.fn()

describe('API Client', () => {
  beforeEach(() => {
    vi.resetAllMocks()
  })

  afterEach(() => {
    vi.restoreAllMocks()
  })

  describe('initialization', () => {
    it('should initialize with correct base URL', () => {
      expect(apiClient.baseURL).toBe('/api/v1/risk-assessment')
    })

    it('should have templates, categories, and contents API endpoints', () => {
      expect(apiClient.templates).toBeDefined()
      expect(apiClient.categories).toBeDefined()
      expect(apiClient.contents).toBeDefined()
    })
  })

  describe('request method', () => {
    it('should make request with correct URL and default headers', async () => {
      const mockResponse = { success: true, data: {} }
      global.$fetch.mockResolvedValue(mockResponse)

      const result = await apiClient.request('/test')

      expect(global.$fetch).toHaveBeenCalledWith('/api/v1/risk-assessment/test', {
        headers: {
          'Content-Type': 'application/json'
        }
      })
      expect(result).toEqual(mockResponse)
    })

    it('should merge custom headers with default headers', async () => {
      const mockResponse = { success: true }
      global.$fetch.mockResolvedValue(mockResponse)

      await apiClient.request('/test', {
        headers: { 'Authorization': 'Bearer token123' }
      })

      const callArgs = global.$fetch.mock.calls[0]
      expect(callArgs[0]).toBe('/api/v1/risk-assessment/test')
      
      // The API client should merge headers properly
      expect(callArgs[1].headers).toHaveProperty('Authorization', 'Bearer token123')
      expect(callArgs[1].headers).toHaveProperty('Content-Type', 'application/json')
    })

    it('should pass through other options', async () => {
      const mockResponse = { success: true }
      global.$fetch.mockResolvedValue(mockResponse)

      await apiClient.request('/test', {
        method: 'POST',
        body: { data: 'test' }
      })

      expect(global.$fetch).toHaveBeenCalledWith('/api/v1/risk-assessment/test', {
        headers: {
          'Content-Type': 'application/json'
        },
        method: 'POST',
        body: { data: 'test' }
      })
    })

    it('should handle and rethrow API errors', async () => {
      const apiError = new Error('Network error')
      global.$fetch.mockRejectedValue(apiError)
      
      // Spy on console.error to verify error logging
      const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {})

      await expect(apiClient.request('/test')).rejects.toThrow('Network error')
      expect(consoleSpy).toHaveBeenCalledWith('API Request failed:', apiError)
      
      consoleSpy.mockRestore()
    })
  })

  describe('templates API', () => {
    describe('getAll', () => {
      it('should fetch all templates without query parameters', async () => {
        const mockResponse = {
          success: true,
          data: {
            templates: [
              { id: 1, version_name: 'Template 1' },
              { id: 2, version_name: 'Template 2' }
            ]
          }
        }
        global.$fetch.mockResolvedValue(mockResponse)

        const result = await apiClient.templates.getAll()

        expect(global.$fetch).toHaveBeenCalledWith('/api/v1/risk-assessment/templates', {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json'
          }
        })
        expect(result).toEqual(mockResponse)
      })

      it('should fetch templates with query parameters', async () => {
        const mockResponse = { success: true, data: { templates: [] } }
        global.$fetch.mockResolvedValue(mockResponse)

        await apiClient.templates.getAll({ 
          page: 1, 
          per_page: 20, 
          search: 'test' 
        })

        expect(global.$fetch).toHaveBeenCalledWith(
          '/api/v1/risk-assessment/templates?page=1&per_page=20&search=test',
          {
            method: 'GET',
            headers: {
              'Content-Type': 'application/json'
            }
          }
        )
      })

      it('should handle empty query parameters', async () => {
        const mockResponse = { success: true, data: { templates: [] } }
        global.$fetch.mockResolvedValue(mockResponse)

        await apiClient.templates.getAll({})

        expect(global.$fetch).toHaveBeenCalledWith('/api/v1/risk-assessment/templates', {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json'
          }
        })
      })

      it('should handle API errors gracefully', async () => {
        const apiError = new Error('Server error')
        global.$fetch.mockRejectedValue(apiError)

        await expect(apiClient.templates.getAll()).rejects.toThrow('Server error')
      })
    })

    describe('getById', () => {
      it('should fetch single template by ID', async () => {
        const mockResponse = {
          success: true,
          data: {
            template: { id: 1, version_name: 'Template 1' }
          }
        }
        global.$fetch.mockResolvedValue(mockResponse)

        const result = await apiClient.templates.getById(1)

        expect(global.$fetch).toHaveBeenCalledWith('/api/v1/risk-assessment/templates/1', {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json'
          }
        })
        expect(result).toEqual(mockResponse)
      })

      it('should handle non-existent template ID', async () => {
        const apiError = new Error('Template not found')
        global.$fetch.mockRejectedValue(apiError)

        await expect(apiClient.templates.getById(999)).rejects.toThrow('Template not found')
      })
    })

    describe('create', () => {
      it('should create new template with POST request', async () => {
        const templateData = {
          version_name: 'New Template',
          description: 'Template description'
        }
        const mockResponse = {
          success: true,
          data: {
            template: { id: 1, ...templateData }
          }
        }
        global.$fetch.mockResolvedValue(mockResponse)

        const result = await apiClient.templates.create(templateData)

        expect(global.$fetch).toHaveBeenCalledWith('/api/v1/risk-assessment/templates', {
          method: 'POST',
          body: templateData,
          headers: {
            'Content-Type': 'application/json'
          }
        })
        expect(result).toEqual(mockResponse)
      })

      it('should handle validation errors during creation', async () => {
        const templateData = { version_name: '' }
        const validationError = new Error('Validation failed')
        global.$fetch.mockRejectedValue(validationError)

        await expect(apiClient.templates.create(templateData)).rejects.toThrow('Validation failed')
      })
    })

    describe('update', () => {
      it('should update template with PUT request', async () => {
        const templateData = {
          version_name: 'Updated Template',
          description: 'Updated description'
        }
        const mockResponse = {
          success: true,
          data: {
            template: { id: 1, ...templateData }
          }
        }
        global.$fetch.mockResolvedValue(mockResponse)

        const result = await apiClient.templates.update(1, templateData)

        expect(global.$fetch).toHaveBeenCalledWith('/api/v1/risk-assessment/templates/1', {
          method: 'PUT',
          body: templateData,
          headers: {
            'Content-Type': 'application/json'
          }
        })
        expect(result).toEqual(mockResponse)
      })

      it('should handle update errors', async () => {
        const templateData = { version_name: 'Updated Template' }
        const updateError = new Error('Update failed')
        global.$fetch.mockRejectedValue(updateError)

        await expect(apiClient.templates.update(1, templateData)).rejects.toThrow('Update failed')
      })
    })

    describe('delete', () => {
      it('should delete template with DELETE request', async () => {
        const mockResponse = { success: true, message: 'Template deleted' }
        global.$fetch.mockResolvedValue(mockResponse)

        const result = await apiClient.templates.delete(1)

        expect(global.$fetch).toHaveBeenCalledWith('/api/v1/risk-assessment/templates/1', {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json'
          }
        })
        expect(result).toEqual(mockResponse)
      })

      it('should handle delete errors', async () => {
        const deleteError = new Error('Delete failed')
        global.$fetch.mockRejectedValue(deleteError)

        await expect(apiClient.templates.delete(1)).rejects.toThrow('Delete failed')
      })
    })

    describe('copy', () => {
      it('should copy template with POST request', async () => {
        const copyData = { version_name: 'Copied Template' }
        const mockResponse = {
          success: true,
          data: {
            template: { id: 2, ...copyData, copied_from: 1 }
          }
        }
        global.$fetch.mockResolvedValue(mockResponse)

        const result = await apiClient.templates.copy(1, copyData)

        expect(global.$fetch).toHaveBeenCalledWith('/api/v1/risk-assessment/templates/1/copy', {
          method: 'POST',
          body: copyData,
          headers: {
            'Content-Type': 'application/json'
          }
        })
        expect(result).toEqual(mockResponse)
      })

      it('should handle copy errors', async () => {
        const copyData = { version_name: 'Copied Template' }
        const copyError = new Error('Copy failed')
        global.$fetch.mockRejectedValue(copyError)

        await expect(apiClient.templates.copy(1, copyData)).rejects.toThrow('Copy failed')
      })
    })
  })

  describe('categories API', () => {
    describe('getByTemplate', () => {
      it('should fetch categories for a template', async () => {
        const mockResponse = {
          success: true,
          data: {
            categories: [
              { id: 1, category_name: 'Financial Risk' },
              { id: 2, category_name: 'Operational Risk' }
            ]
          }
        }
        global.$fetch.mockResolvedValue(mockResponse)

        const result = await apiClient.categories.getByTemplate(1)

        expect(global.$fetch).toHaveBeenCalledWith('/api/v1/risk-assessment/templates/1/categories', {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json'
          }
        })
        expect(result).toEqual(mockResponse)
      })
    })

    describe('create', () => {
      it('should create category for a template', async () => {
        const categoryData = { category_name: 'New Category' }
        const mockResponse = {
          success: true,
          data: {
            category: { id: 1, template_id: 1, ...categoryData }
          }
        }
        global.$fetch.mockResolvedValue(mockResponse)

        const result = await apiClient.categories.create(1, categoryData)

        expect(global.$fetch).toHaveBeenCalledWith('/api/v1/risk-assessment/templates/1/categories', {
          method: 'POST',
          body: categoryData,
          headers: {
            'Content-Type': 'application/json'
          }
        })
        expect(result).toEqual(mockResponse)
      })
    })

    describe('update', () => {
      it('should update category', async () => {
        const categoryData = { category_name: 'Updated Category' }
        const mockResponse = {
          success: true,
          data: {
            category: { id: 1, template_id: 1, ...categoryData }
          }
        }
        global.$fetch.mockResolvedValue(mockResponse)

        const result = await apiClient.categories.update(1, 1, categoryData)

        expect(global.$fetch).toHaveBeenCalledWith('/api/v1/risk-assessment/templates/1/categories/1', {
          method: 'PUT',
          body: categoryData,
          headers: {
            'Content-Type': 'application/json'
          }
        })
        expect(result).toEqual(mockResponse)
      })
    })

    describe('delete', () => {
      it('should delete category', async () => {
        const mockResponse = { success: true, message: 'Category deleted' }
        global.$fetch.mockResolvedValue(mockResponse)

        const result = await apiClient.categories.delete(1, 1)

        expect(global.$fetch).toHaveBeenCalledWith('/api/v1/risk-assessment/templates/1/categories/1', {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json'
          }
        })
        expect(result).toEqual(mockResponse)
      })
    })
  })

  describe('contents API', () => {
    describe('getByTemplate', () => {
      it('should fetch contents for a template', async () => {
        const mockResponse = {
          success: true,
          data: {
            contents: [
              { id: 1, topic: 'Content 1' },
              { id: 2, topic: 'Content 2' }
            ]
          }
        }
        global.$fetch.mockResolvedValue(mockResponse)

        const result = await apiClient.contents.getByTemplate(1)

        expect(global.$fetch).toHaveBeenCalledWith('/api/v1/risk-assessment/templates/1/contents', {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json'
          }
        })
        expect(result).toEqual(mockResponse)
      })

      it('should fetch contents with query parameters', async () => {
        const mockResponse = { success: true, data: { contents: [] } }
        global.$fetch.mockResolvedValue(mockResponse)

        await apiClient.contents.getByTemplate(1, { 
          category_id: 2,
          sort: 'sort_order'
        })

        expect(global.$fetch).toHaveBeenCalledWith(
          '/api/v1/risk-assessment/templates/1/contents?category_id=2&sort=sort_order',
          {
            method: 'GET',
            headers: {
              'Content-Type': 'application/json'
            }
          }
        )
      })
    })

    describe('getById', () => {
      it('should fetch single content by ID', async () => {
        const mockResponse = {
          success: true,
          data: {
            content: { id: 1, topic: 'Content 1' }
          }
        }
        global.$fetch.mockResolvedValue(mockResponse)

        const result = await apiClient.contents.getById(1, 1)

        expect(global.$fetch).toHaveBeenCalledWith('/api/v1/risk-assessment/templates/1/contents/1', {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json'
          }
        })
        expect(result).toEqual(mockResponse)
      })
    })

    describe('create', () => {
      it('should create content for a template', async () => {
        const contentData = {
          topic: 'New Content',
          description: 'Content description',
          category_id: 1
        }
        const mockResponse = {
          success: true,
          data: {
            content: { id: 1, template_id: 1, ...contentData }
          }
        }
        global.$fetch.mockResolvedValue(mockResponse)

        const result = await apiClient.contents.create(1, contentData)

        expect(global.$fetch).toHaveBeenCalledWith('/api/v1/risk-assessment/templates/1/contents', {
          method: 'POST',
          body: contentData,
          headers: {
            'Content-Type': 'application/json'
          }
        })
        expect(result).toEqual(mockResponse)
      })
    })

    describe('update', () => {
      it('should update content', async () => {
        const contentData = {
          topic: 'Updated Content',
          description: 'Updated description'
        }
        const mockResponse = {
          success: true,
          data: {
            content: { id: 1, template_id: 1, ...contentData }
          }
        }
        global.$fetch.mockResolvedValue(mockResponse)

        const result = await apiClient.contents.update(1, 1, contentData)

        expect(global.$fetch).toHaveBeenCalledWith('/api/v1/risk-assessment/templates/1/contents/1', {
          method: 'PUT',
          body: contentData,
          headers: {
            'Content-Type': 'application/json'
          }
        })
        expect(result).toEqual(mockResponse)
      })
    })

    describe('delete', () => {
      it('should delete content', async () => {
        const mockResponse = { success: true, message: 'Content deleted' }
        global.$fetch.mockResolvedValue(mockResponse)

        const result = await apiClient.contents.delete(1, 1)

        expect(global.$fetch).toHaveBeenCalledWith('/api/v1/risk-assessment/templates/1/contents/1', {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json'
          }
        })
        expect(result).toEqual(mockResponse)
      })
    })

    describe('reorder', () => {
      it('should reorder contents', async () => {
        const reorderData = {
          items: [
            { id: 1, sort_order: 1 },
            { id: 2, sort_order: 2 }
          ]
        }
        const mockResponse = { success: true, message: 'Contents reordered' }
        global.$fetch.mockResolvedValue(mockResponse)

        const result = await apiClient.contents.reorder(1, reorderData)

        expect(global.$fetch).toHaveBeenCalledWith('/api/v1/risk-assessment/templates/1/contents/reorder', {
          method: 'PUT',
          body: reorderData,
          headers: {
            'Content-Type': 'application/json'
          }
        })
        expect(result).toEqual(mockResponse)
      })
    })
  })

  describe('network error handling', () => {
    it('should handle network timeout errors', async () => {
      const timeoutError = new Error('Request timeout')
      global.$fetch.mockRejectedValue(timeoutError)

      await expect(apiClient.templates.getAll()).rejects.toThrow('Request timeout')
    })

    it('should handle 404 errors', async () => {
      const notFoundError = new Error('Not found')
      global.$fetch.mockRejectedValue(notFoundError)

      await expect(apiClient.templates.getById(999)).rejects.toThrow('Not found')
    })

    it('should handle 500 server errors', async () => {
      const serverError = new Error('Internal server error')
      global.$fetch.mockRejectedValue(serverError)

      await expect(apiClient.templates.getAll()).rejects.toThrow('Internal server error')
    })

    it('should handle network connection errors', async () => {
      const connectionError = new Error('Network error')
      global.$fetch.mockRejectedValue(connectionError)

      await expect(apiClient.templates.getAll()).rejects.toThrow('Network error')
    })
  })
})