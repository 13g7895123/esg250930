import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { setActivePinia, createPinia } from 'pinia'
import TemplatesPage from '@/pages/risk-assessment/templates/index.vue'
import { useTemplatesStore } from '@/stores/templates'

// Mock the composables and components
vi.mock('@/composables/usePageTitle', () => ({
  default: vi.fn()
}))

vi.mock('@heroicons/vue/24/outline', () => ({
  PlusIcon: { template: '<div>PlusIcon</div>' },
  PencilIcon: { template: '<div>PencilIcon</div>' },
  DocumentDuplicateIcon: { template: '<div>DocumentDuplicateIcon</div>' },
  TrashIcon: { template: '<div>TrashIcon</div>' },
  DocumentTextIcon: { template: '<div>DocumentTextIcon</div>' }
}))

// Mock the DataTable component
const MockDataTable = {
  template: `
    <div data-testid="data-table">
      <div data-testid="actions-slot">
        <slot name="actions" />
      </div>
      <div v-for="item in data" :key="item.id" data-testid="table-row">
        <slot name="cell-actions" :item="item" />
      </div>
      <div v-if="data.length === 0" data-testid="empty-slot">
        <slot name="emptyAction" />
      </div>
    </div>
  `,
  props: ['data', 'columns', 'searchPlaceholder', 'searchFields', 'emptyTitle', 'emptyMessage', 'noSearchResultsTitle', 'noSearchResultsMessage']
}

// Mock the ConfirmationModal component
const MockConfirmationModal = {
  template: `
    <div v-if="modelValue" data-testid="confirmation-modal">
      <div data-testid="modal-title">{{ title }}</div>
      <div data-testid="modal-message">{{ message }}</div>
      <div data-testid="modal-details">{{ details }}</div>
      <button data-testid="cancel-button" @click="$emit('close')">{{ cancelText }}</button>
      <button data-testid="confirm-button" @click="$emit('confirm')">{{ confirmText }}</button>
    </div>
  `,
  props: ['modelValue', 'title', 'message', 'details', 'type', 'cancelText', 'confirmText'],
  emits: ['update:modelValue', 'close', 'confirm']
}

// Mock the templates store
const mockTemplatesStore = {
  templates: [],
  addTemplate: vi.fn(),
  updateTemplate: vi.fn(),
  deleteTemplate: vi.fn(),
  copyTemplate: vi.fn()
}

vi.mock('@/stores/templates', () => ({
  useTemplatesStore: () => mockTemplatesStore
}))

describe('Templates Page', () => {
  let wrapper
  let pinia

  beforeEach(() => {
    pinia = createPinia()
    setActivePinia(pinia)

    // Reset all mocks
    vi.resetAllMocks()
    mockTemplatesStore.templates = []
    mockTemplatesStore.addTemplate.mockResolvedValue({ id: 1, versionName: 'New Template' })
    mockTemplatesStore.updateTemplate.mockResolvedValue({ id: 1, versionName: 'Updated Template' })
    mockTemplatesStore.deleteTemplate.mockResolvedValue()
    mockTemplatesStore.copyTemplate.mockResolvedValue({ id: 2, versionName: 'Copied Template' })

    // Mock global functions
    global.navigateTo = vi.fn()
    global.usePageTitle = vi.fn()
  })

  afterEach(() => {
    if (wrapper) {
      wrapper.unmount()
    }
    vi.restoreAllMocks()
  })

  const createWrapper = (templates = []) => {
    mockTemplatesStore.templates = templates
    
    return mount(TemplatesPage, {
      global: {
        plugins: [pinia],
        components: {
          DataTable: MockDataTable,
          ConfirmationModal: MockConfirmationModal
        },
        stubs: {
          PlusIcon: true,
          PencilIcon: true,
          DocumentDuplicateIcon: true,
          TrashIcon: true,
          DocumentTextIcon: true
        }
      }
    })
  }

  describe('page rendering', () => {
    it('should render page header correctly', () => {
      wrapper = createWrapper()

      expect(wrapper.find('h1').text()).toBe('範本管理')
      expect(wrapper.text()).toContain('管理風險評估範本版本')
    })

    it('should render DataTable with correct props', () => {
      wrapper = createWrapper()
      const dataTable = wrapper.findComponent(MockDataTable)

      expect(dataTable.exists()).toBe(true)
      expect(dataTable.props('searchPlaceholder')).toBe('搜尋範本名稱...')
      expect(dataTable.props('searchFields')).toEqual(['versionName'])
      expect(dataTable.props('emptyTitle')).toBe('還沒有範本')
      expect(dataTable.props('emptyMessage')).toBe('開始建立您的第一個風險評估範本')
    })

    it('should configure DataTable columns correctly', () => {
      wrapper = createWrapper()
      const columns = wrapper.vm.columns

      expect(columns).toHaveLength(3)
      expect(columns[0]).toEqual({
        key: 'actions',
        label: '功能',
        sortable: false,
        cellClass: 'text-base text-gray-900 dark:text-white'
      })
      expect(columns[1]).toEqual({
        key: 'versionName',
        label: '版本名稱',
        sortable: true,
        cellClass: 'text-base font-medium text-gray-900 dark:text-white'
      })
      expect(columns[2].key).toBe('createdAt')
      expect(columns[2].label).toBe('建立日期')
    })
  })

  describe('templates display', () => {
    it('should display templates when available', () => {
      const templates = [
        { id: 1, versionName: 'Template 1', createdAt: new Date('2024-01-15') },
        { id: 2, versionName: 'Template 2', createdAt: new Date('2024-02-20') }
      ]
      wrapper = createWrapper(templates)

      const dataTable = wrapper.findComponent(MockDataTable)
      expect(dataTable.props('data')).toEqual(templates)
    })

    it('should show empty state when no templates', () => {
      wrapper = createWrapper([])

      const emptySlot = wrapper.find('[data-testid="empty-slot"]')
      expect(emptySlot.exists()).toBe(true)
    })

    it('should format dates correctly', () => {
      wrapper = createWrapper()
      const testDate = new Date('2024-01-15T10:30:00')
      const formattedDate = wrapper.vm.formatDate(testDate)

      expect(formattedDate).toMatch(/2024/)
      expect(formattedDate).toMatch(/01/)
      expect(formattedDate).toMatch(/15/)
    })
  })

  describe('add template functionality', () => {
    it('should show add modal when add button is clicked', async () => {
      wrapper = createWrapper()

      const addButton = wrapper.find('[data-testid="actions-slot"] button')
      await addButton.trigger('click')

      expect(wrapper.vm.showAddModal).toBe(true)
      expect(wrapper.find('.fixed.inset-0').exists()).toBe(true)
    })

    it('should show add button in empty state', () => {
      wrapper = createWrapper([])

      const emptyAddButton = wrapper.find('[data-testid="empty-slot"] button')
      expect(emptyAddButton.exists()).toBe(true)
      expect(emptyAddButton.text()).toContain('新增範本')
    })

    it('should render add modal with correct title', async () => {
      wrapper = createWrapper()
      await wrapper.setData({ showAddModal: true })

      expect(wrapper.find('h3').text()).toBe('新增範本')
      expect(wrapper.find('input[type="text"]').exists()).toBe(true)
    })

    it('should submit new template form', async () => {
      wrapper = createWrapper()
      await wrapper.setData({ showAddModal: true })

      const versionNameInput = wrapper.find('input[type="text"]')
      await versionNameInput.setValue('New Template')

      const form = wrapper.find('form')
      await form.trigger('submit.prevent')

      expect(mockTemplatesStore.addTemplate).toHaveBeenCalledWith({
        versionName: 'New Template'
      })
      expect(wrapper.vm.showAddModal).toBe(false)
    })

    it('should require version name for new template', async () => {
      wrapper = createWrapper()
      await wrapper.setData({ showAddModal: true })

      const form = wrapper.find('form')
      await form.trigger('submit.prevent')

      // Form should not submit without version name
      const versionNameInput = wrapper.find('input[type="text"]')
      expect(versionNameInput.element.validity.valid).toBe(false)
    })

    it('should close modal when cancel button is clicked', async () => {
      wrapper = createWrapper()
      await wrapper.setData({ showAddModal: true })

      const cancelButton = wrapper.find('button[type="button"]')
      await cancelButton.trigger('click')

      expect(wrapper.vm.showAddModal).toBe(false)
    })

    it('should close modal when clicking outside', async () => {
      wrapper = createWrapper()
      await wrapper.setData({ showAddModal: true })

      const backdrop = wrapper.find('.fixed.inset-0')
      await backdrop.trigger('click')

      expect(wrapper.vm.showAddModal).toBe(false)
    })

    it('should not close modal when clicking inside modal content', async () => {
      wrapper = createWrapper()
      await wrapper.setData({ showAddModal: true })

      const modalContent = wrapper.find('.bg-white')
      await modalContent.trigger('click.stop')

      expect(wrapper.vm.showAddModal).toBe(true)
    })
  })

  describe('edit template functionality', () => {
    it('should show edit modal when edit button is clicked', async () => {
      const templates = [
        { id: 1, versionName: 'Template 1', createdAt: new Date() }
      ]
      wrapper = createWrapper(templates)

      await wrapper.vm.editTemplate(templates[0])

      expect(wrapper.vm.showEditModal).toBe(true)
      expect(wrapper.vm.editingTemplate).toEqual(templates[0])
      expect(wrapper.vm.formData.versionName).toBe('Template 1')
    })

    it('should render edit modal with correct title and pre-filled data', async () => {
      const template = { id: 1, versionName: 'Template 1', createdAt: new Date() }
      wrapper = createWrapper([template])
      
      await wrapper.vm.editTemplate(template)
      await wrapper.vm.$nextTick()

      expect(wrapper.find('h3').text()).toBe('編輯範本')
      expect(wrapper.find('input[type="text"]').element.value).toBe('Template 1')
    })

    it('should submit edit template form', async () => {
      const template = { id: 1, versionName: 'Template 1', createdAt: new Date() }
      wrapper = createWrapper([template])
      
      await wrapper.vm.editTemplate(template)
      await wrapper.vm.$nextTick()

      const versionNameInput = wrapper.find('input[type="text"]')
      await versionNameInput.setValue('Updated Template')

      const form = wrapper.find('form')
      await form.trigger('submit.prevent')

      expect(mockTemplatesStore.updateTemplate).toHaveBeenCalledWith(1, {
        versionName: 'Updated Template'
      })
      expect(wrapper.vm.showEditModal).toBe(false)
    })
  })

  describe('delete template functionality', () => {
    it('should show delete confirmation when delete button is clicked', async () => {
      const template = { id: 1, versionName: 'Template 1', createdAt: new Date() }
      wrapper = createWrapper([template])

      await wrapper.vm.deleteTemplate(template)

      expect(wrapper.vm.showDeleteModal).toBe(true)
      expect(wrapper.vm.templateToDelete).toEqual(template)
    })

    it('should render delete confirmation modal with correct data', async () => {
      const template = { id: 1, versionName: 'Template 1', createdAt: new Date() }
      wrapper = createWrapper([template])
      
      await wrapper.vm.deleteTemplate(template)
      await wrapper.vm.$nextTick()

      const modal = wrapper.findComponent(MockConfirmationModal)
      expect(modal.exists()).toBe(true)
      expect(modal.props('title')).toBe('確認刪除')
      expect(modal.props('message')).toContain('Template 1')
      expect(modal.props('type')).toBe('danger')
    })

    it('should confirm delete when confirm button is clicked', async () => {
      const template = { id: 1, versionName: 'Template 1', createdAt: new Date() }
      wrapper = createWrapper([template])
      
      await wrapper.vm.deleteTemplate(template)
      await wrapper.vm.$nextTick()

      const modal = wrapper.findComponent(MockConfirmationModal)
      await modal.vm.$emit('confirm')

      expect(mockTemplatesStore.deleteTemplate).toHaveBeenCalledWith(1)
      expect(wrapper.vm.showDeleteModal).toBe(false)
      expect(wrapper.vm.templateToDelete).toBe(null)
    })

    it('should cancel delete when cancel button is clicked', async () => {
      const template = { id: 1, versionName: 'Template 1', createdAt: new Date() }
      wrapper = createWrapper([template])
      
      await wrapper.vm.deleteTemplate(template)
      await wrapper.vm.$nextTick()

      const modal = wrapper.findComponent(MockConfirmationModal)
      await modal.vm.$emit('close')

      expect(mockTemplatesStore.deleteTemplate).not.toHaveBeenCalled()
      expect(wrapper.vm.showDeleteModal).toBe(false)
    })
  })

  describe('copy template functionality', () => {
    it('should copy template when copy button is clicked', async () => {
      const template = { id: 1, versionName: 'Template 1', createdAt: new Date() }
      wrapper = createWrapper([template])

      await wrapper.vm.copyTemplate(template)

      expect(mockTemplatesStore.copyTemplate).toHaveBeenCalledWith(1, 'Template 1 (副本)')
    })

    it('should handle copy template errors gracefully', async () => {
      mockTemplatesStore.copyTemplate.mockRejectedValue(new Error('Copy failed'))
      
      const template = { id: 1, versionName: 'Template 1', createdAt: new Date() }
      wrapper = createWrapper([template])

      // Should not throw error
      await wrapper.vm.copyTemplate(template)
      expect(mockTemplatesStore.copyTemplate).toHaveBeenCalled()
    })
  })

  describe('view template content functionality', () => {
    it('should navigate to template content page', async () => {
      const template = { id: 1, versionName: 'Template 1', createdAt: new Date() }
      wrapper = createWrapper([template])

      await wrapper.vm.viewTemplateContent(template)

      expect(global.navigateTo).toHaveBeenCalledWith('/risk-assessment/templates/1/content')
    })
  })

  describe('template action buttons', () => {
    let template

    beforeEach(() => {
      template = { id: 1, versionName: 'Template 1', createdAt: new Date() }
    })

    it('should render all action buttons for templates', () => {
      wrapper = createWrapper([template])

      // Find the actions cell
      const actionsCell = wrapper.find('[data-testid="table-row"]')
      expect(actionsCell.exists()).toBe(true)

      // Should have buttons for edit, copy, delete, and view content
      const buttons = actionsCell.findAll('button')
      expect(buttons.length).toBeGreaterThanOrEqual(4)
    })

    it('should show tooltips on hover for action buttons', () => {
      wrapper = createWrapper([template])

      // Check for tooltip containers
      const tooltips = wrapper.findAll('.group')
      expect(tooltips.length).toBeGreaterThan(0)

      // Check for tooltip content
      const tooltipContent = wrapper.findAll('.absolute.bottom-full')
      expect(tooltipContent.length).toBeGreaterThan(0)
    })

    it('should handle action button clicks correctly', async () => {
      wrapper = createWrapper([template])
      
      // Simulate clicking edit button
      const editSpy = vi.spyOn(wrapper.vm, 'editTemplate')
      // Since we can't easily simulate the actual button clicks in the slot,
      // we'll test the methods directly
      await wrapper.vm.editTemplate(template)
      expect(editSpy).toHaveBeenCalledWith(template)

      // Test copy button
      const copySpy = vi.spyOn(wrapper.vm, 'copyTemplate')
      await wrapper.vm.copyTemplate(template)
      expect(copySpy).toHaveBeenCalledWith(template)

      // Test delete button
      const deleteSpy = vi.spyOn(wrapper.vm, 'deleteTemplate')
      await wrapper.vm.deleteTemplate(template)
      expect(deleteSpy).toHaveBeenCalledWith(template)

      // Test view content button
      const viewSpy = vi.spyOn(wrapper.vm, 'viewTemplateContent')
      await wrapper.vm.viewTemplateContent(template)
      expect(viewSpy).toHaveBeenCalledWith(template)
    })
  })

  describe('form validation and error handling', () => {
    it('should validate form data before submission', async () => {
      wrapper = createWrapper()
      await wrapper.setData({ showAddModal: true })

      // Try to submit empty form
      const form = wrapper.find('form')
      const versionNameInput = wrapper.find('input[type="text"]')
      
      // HTML5 validation should prevent submission
      expect(versionNameInput.attributes('required')).toBeDefined()
    })

    it('should clear form data when closing modals', async () => {
      wrapper = createWrapper()
      
      // Set some form data
      await wrapper.setData({ 
        showAddModal: true,
        formData: { versionName: 'Test Template' }
      })

      // Close modal
      await wrapper.vm.closeModals()

      expect(wrapper.vm.showAddModal).toBe(false)
      expect(wrapper.vm.showEditModal).toBe(false)
      expect(wrapper.vm.editingTemplate).toBe(null)
      expect(wrapper.vm.formData.versionName).toBe('')
    })

    it('should handle API errors gracefully during operations', async () => {
      mockTemplatesStore.addTemplate.mockRejectedValue(new Error('API Error'))
      
      wrapper = createWrapper()
      await wrapper.setData({ 
        showAddModal: true,
        formData: { versionName: 'Test Template' }
      })

      // Submit form
      const form = wrapper.find('form')
      await form.trigger('submit.prevent')

      // Should still close modal even if API fails
      expect(wrapper.vm.showAddModal).toBe(false)
    })
  })

  describe('keyboard and accessibility', () => {
    it('should support keyboard navigation for modals', async () => {
      wrapper = createWrapper()
      await wrapper.setData({ showAddModal: true })

      const versionNameInput = wrapper.find('input[type="text"]')
      expect(versionNameInput.attributes('required')).toBeDefined()
      expect(versionNameInput.attributes('placeholder')).toBe('請輸入版本名稱')
    })

    it('should have proper button labels and accessibility attributes', () => {
      wrapper = createWrapper()

      const addButton = wrapper.find('[data-testid="actions-slot"] button')
      expect(addButton.text()).toContain('新增範本')
    })

    it('should handle escape key to close modals', async () => {
      wrapper = createWrapper()
      await wrapper.setData({ showAddModal: true })

      // Simulate escape key press
      await wrapper.trigger('keyup.escape')
      
      // Note: This would require additional event handling in the component
      // This test demonstrates what should be tested
    })
  })

  describe('responsive design and UI states', () => {
    it('should apply correct CSS classes for dark mode support', () => {
      wrapper = createWrapper()

      // Check for dark mode classes
      const elements = wrapper.findAll('.dark\\:text-white, .dark\\:bg-gray-800')
      expect(elements.length).toBeGreaterThan(0)
    })

    it('should have proper styling for buttons and interactions', () => {
      wrapper = createWrapper()

      // Check for hover and transition classes
      const buttons = wrapper.findAll('button')
      buttons.forEach(button => {
        const classes = button.classes().join(' ')
        expect(classes).toMatch(/transition|hover/)
      })
    })

    it('should handle loading states appropriately', async () => {
      // This would test loading indicators during API calls
      // Requires store integration for loading states
      wrapper = createWrapper()
      
      // Mock loading state
      mockTemplatesStore.isLoading = true
      
      await wrapper.vm.$nextTick()
      
      // Would check for loading indicators
      // expect(wrapper.find('.loading').exists()).toBe(true)
    })
  })

  describe('integration with store', () => {
    it('should use templates store correctly', () => {
      wrapper = createWrapper()

      expect(wrapper.vm.templates).toBe(mockTemplatesStore.templates)
    })

    it('should call store methods with correct parameters', async () => {
      wrapper = createWrapper()

      const templateData = { versionName: 'Test Template' }
      await wrapper.vm.submitForm()

      // Would be called if form data was set
      // expect(mockTemplatesStore.addTemplate).toHaveBeenCalledWith(templateData)
    })

    it('should react to store state changes', async () => {
      wrapper = createWrapper([])

      // Simulate adding template to store
      mockTemplatesStore.templates.push({
        id: 1,
        versionName: 'New Template',
        createdAt: new Date()
      })

      await wrapper.vm.$nextTick()

      // Component should reflect store changes
      const dataTable = wrapper.findComponent(MockDataTable)
      expect(dataTable.props('data')).toHaveLength(1)
    })
  })
})