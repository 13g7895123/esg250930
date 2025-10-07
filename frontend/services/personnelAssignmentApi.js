/**
 * 人員指派API服務
 *
 * 提供與後端人員指派API的通訊功能
 * 替代原有的localStorage存儲方式
 *
 * @author Claude Code Assistant
 * @version 1.0
 * @since 2025-09-24
 */

import { $fetch } from 'ofetch'

const API_BASE_URL = '/api/v1/personnel'

// In-memory storage for mock API data
const mockStorage = {
  assignments: [],
  nextAssignmentId: 1
}

/**
 * API請求封裝函數
 */
const apiRequest = async (endpoint, options = {}) => {
  try {
    const response = await $fetch(`${API_BASE_URL}${endpoint}`, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...options.headers
      },
      ...options
    })

    return response
  } catch (error) {
    console.error('API請求失敗:', error)

    // Temporary fallback for development - return mock data when API fails
    if (process.env.NODE_ENV === 'development') {
      console.warn('Using mock data due to API failure')
      return getMockResponse(endpoint, options)
    }

    throw error
  }
}

/**
 * Mock response for development when API fails
 */
const getMockResponse = (endpoint, options) => {
  console.log('Generating mock response for:', endpoint, 'method:', options.method || 'GET')

  // Mock personnel data
  if (endpoint.includes('/personnel-assignments')) {
    return {
      success: true,
      message: '成功取得人員列表 (模擬資料)',
      data: [
        {
          id: 1,
          external_id: 'emp001',
          name: '王小明',
          email: 'wang@example.com',
          department: '人資部',
          position: '經理',
          status: 'active'
        },
        {
          id: 2,
          external_id: 'emp002',
          name: '李美玲',
          email: 'li@example.com',
          department: '財務部',
          position: '專員',
          status: 'active'
        },
        {
          id: 3,
          external_id: 'emp003',
          name: '陳大華',
          email: 'chen@example.com',
          department: '業務部',
          position: '主管',
          status: 'active'
        }
      ],
      meta: {
        company_id: 1,
        company_name: '測試公司',
        personnel_count: 3
      }
    }
  }

  // Mock sync response
  if (endpoint.includes('/sync')) {
    return {
      success: true,
      message: '人員資料同步完成 (模擬)',
      data: {
        company_id: 1,
        synced_count: 3,
        synced_at: new Date().toISOString()
      }
    }
  }

  // Mock batch create assignments - POST /assignments/batch
  if (endpoint.includes('/assignments/batch') && options.method === 'POST') {
    const batchData = options.body
    const createdAssignments = []

    // Create assignment records for each content ID
    batchData.question_content_ids.forEach(contentId => {
      const newAssignment = {
        id: mockStorage.nextAssignmentId++,
        company_id: batchData.company_id,
        assessment_id: batchData.assessment_id,
        question_content_id: contentId,
        personnel_id: batchData.personnel_id,
        personnel_name: `人員 ${batchData.personnel_id}`,
        personnel_department: '測試部門',
        personnel_position: '測試職位',
        assignment_status: 'assigned',
        assigned_at: new Date().toISOString(),
        accepted_at: null,
        completed_at: null
      }

      // Check if assignment already exists
      const exists = mockStorage.assignments.some(a =>
        a.company_id === newAssignment.company_id &&
        a.assessment_id === newAssignment.assessment_id &&
        a.question_content_id === newAssignment.question_content_id &&
        a.personnel_id === newAssignment.personnel_id
      )

      if (!exists) {
        mockStorage.assignments.push(newAssignment)
        createdAssignments.push(newAssignment)
      }
    })

    console.log('Mock: Created', createdAssignments.length, 'assignments, skipped', batchData.question_content_ids.length - createdAssignments.length)
    console.log('Mock: Total assignments in storage:', mockStorage.assignments.length)

    return {
      success: true,
      message: '批量指派成功 (模擬)',
      data: {
        assigned_count: createdAssignments.length,
        skipped_count: batchData.question_content_ids.length - createdAssignments.length,
        assignments: createdAssignments
      }
    }
  }

  // Mock assignment summary - GET /assignments
  if (endpoint.includes('/assignments') && (!options.method || options.method === 'GET')) {
    // Extract companyId and assessmentId from endpoint
    // Format: /companies/{companyId}/assessments/{assessmentId}/assignments
    const match = endpoint.match(/\/companies\/(\d+)\/assessments\/(\d+)\/assignments/)

    let filteredAssignments = mockStorage.assignments

    if (match) {
      const companyId = parseInt(match[1])
      const assessmentId = parseInt(match[2])

      filteredAssignments = mockStorage.assignments.filter(a =>
        a.company_id === companyId && a.assessment_id === assessmentId
      )

      console.log('Mock: Filtering assignments for company', companyId, 'assessment', assessmentId)
      console.log('Mock: Found', filteredAssignments.length, 'assignments')
    }

    return {
      success: true,
      message: '成功取得指派摘要 (模擬)',
      data: {
        assignments: filteredAssignments,
        personnel_summary: [],
        statistics: {
          total_assignments: filteredAssignments.length,
          unique_personnel: 0,
          unique_contents: 0,
          status_counts: {
            assigned: filteredAssignments.filter(a => a.assignment_status === 'assigned').length,
            accepted: 0,
            declined: 0,
            completed: 0
          }
        }
      }
    }
  }

  // Mock remove assignment - DELETE /assignments
  if (endpoint.includes('/assignments') && options.method === 'DELETE') {
    const removeData = options.body

    // Remove assignment from storage
    const beforeCount = mockStorage.assignments.length
    mockStorage.assignments = mockStorage.assignments.filter(a =>
      !(a.company_id === removeData.company_id &&
        a.assessment_id === removeData.assessment_id &&
        a.question_content_id === removeData.question_content_id &&
        a.personnel_id === removeData.personnel_id)
    )
    const removedCount = beforeCount - mockStorage.assignments.length

    console.log('Mock: Removed', removedCount, 'assignment(s)')

    return {
      success: true,
      message: '移除指派成功 (模擬)',
      data: {
        removed_count: removedCount
      }
    }
  }

  // Mock remove personnel from assessment - DELETE /companies/{companyId}/assessments/{assessmentId}/personnel/{personnelId}
  if (endpoint.match(/\/companies\/\d+\/assessments\/\d+\/personnel\/\d+/) && options.method === 'DELETE') {
    const match = endpoint.match(/\/companies\/(\d+)\/assessments\/(\d+)\/personnel\/(\d+)/)
    const companyId = parseInt(match[1])
    const assessmentId = parseInt(match[2])
    const personnelId = parseInt(match[3])

    const beforeCount = mockStorage.assignments.length
    mockStorage.assignments = mockStorage.assignments.filter(a =>
      !(a.company_id === companyId &&
        a.assessment_id === assessmentId &&
        a.personnel_id === personnelId)
    )
    const removedCount = beforeCount - mockStorage.assignments.length

    console.log('Mock: Removed all assignments for personnel', personnelId, '- removed', removedCount, 'assignment(s)')

    return {
      success: true,
      message: '移除人員所有指派成功 (模擬)',
      data: {
        removed_count: removedCount
      }
    }
  }

  // Default mock response
  return {
    success: false,
    message: '模擬API - 未實現的端點',
    data: null
  }
}

/**
 * 人員指派API服務
 */
export const personnelAssignmentApi = {

  /**
   * 取得公司的人員列表
   * @param {number} companyId - 公司ID
   * @returns {Promise<Object>} API回應
   */
  async getPersonnelByCompany(companyId) {
    return await apiRequest(`/companies/${companyId}/personnel-assignments`)
  },

  /**
   * 強制同步公司人員資料
   * @param {number} companyId - 公司ID
   * @returns {Promise<Object>} API回應
   */
  async syncPersonnel(companyId) {
    return await apiRequest(`/companies/${companyId}/sync`, {
      method: 'POST'
    })
  },

  /**
   * 取得評估的指派摘要
   * @param {number} companyId - 公司ID
   * @param {number} assessmentId - 評估ID
   * @returns {Promise<Object>} API回應
   */
  async getAssignmentSummary(companyId, assessmentId) {
    return await apiRequest(`/companies/${companyId}/assessments/${assessmentId}/assignments`)
  },

  /**
   * 指派人員到題項內容
   * @param {Object} assignmentData - 指派資料
   * @param {number} assignmentData.company_id - 公司ID
   * @param {number} assignmentData.assessment_id - 評估ID
   * @param {number} assignmentData.question_content_id - 題項內容ID
   * @param {number} assignmentData.personnel_id - 人員ID
   * @param {number} [assignmentData.assigned_by] - 指派人員ID
   * @returns {Promise<Object>} API回應
   */
  async createAssignment(assignmentData) {
    return await apiRequest('/assignments', {
      method: 'POST',
      body: assignmentData
    })
  },

  /**
   * 批量指派人員到多個題項內容
   * @param {Object} batchData - 批量指派資料
   * @param {number} batchData.company_id - 公司ID
   * @param {number} batchData.assessment_id - 評估ID
   * @param {Array} batchData.question_content_ids - 題項內容ID陣列
   * @param {number} batchData.personnel_id - 人員ID
   * @param {number} [batchData.assigned_by] - 指派人員ID
   * @returns {Promise<Object>} API回應
   */
  async batchCreateAssignments(batchData) {
    return await apiRequest('/assignments/batch', {
      method: 'POST',
      body: batchData
    })
  },

  /**
   * 移除人員指派
   * @param {Object} assignmentData - 指派資料
   * @param {number} assignmentData.company_id - 公司ID
   * @param {number} assignmentData.assessment_id - 評估ID
   * @param {number} assignmentData.question_content_id - 題項內容ID
   * @param {number} assignmentData.personnel_id - 人員ID
   * @returns {Promise<Object>} API回應
   */
  async removeAssignment(assignmentData) {
    return await apiRequest('/assignments', {
      method: 'DELETE',
      body: assignmentData
    })
  },

  /**
   * 移除人員在整個評估中的所有指派
   * @param {number} companyId - 公司ID
   * @param {number} assessmentId - 評估ID
   * @param {number} personnelId - 人員ID
   * @returns {Promise<Object>} API回應
   */
  async removePersonnelFromAssessment(companyId, assessmentId, personnelId) {
    return await apiRequest(`/companies/${companyId}/assessments/${assessmentId}/personnel/${personnelId}`, {
      method: 'DELETE'
    })
  },

  /**
   * 更新指派狀態
   * @param {number} assignmentId - 指派ID
   * @param {string} status - 新狀態 (assigned, accepted, declined, completed)
   * @returns {Promise<Object>} API回應
   */
  async updateAssignmentStatus(assignmentId, status) {
    return await apiRequest(`/assignments/${assignmentId}/status`, {
      method: 'PUT',
      body: { status }
    })
  }
}

/**
 * 將localStorage資料遷移到API的輔助函數
 */
export const migrateLocalStorageToApi = {

  /**
   * 取得localStorage中的指派資料
   * @returns {Array} localStorage中的指派資料
   */
  getLocalStorageAssignments() {
    if (process.client) {
      try {
        const stored = localStorage.getItem('esg-question-assignments')
        return stored ? JSON.parse(stored) : []
      } catch (error) {
        console.error('Error reading localStorage assignments:', error)
        return []
      }
    }
    return []
  },

  /**
   * 將localStorage指派資料遷移到API
   * @param {number} companyId - 公司ID
   * @param {number} assessmentId - 評估ID
   * @returns {Promise<Object>} 遷移結果
   */
  async migrateAssignmentsToApi(companyId, assessmentId) {
    try {
      const localAssignments = this.getLocalStorageAssignments()

      // 篩選出指定公司和評估的指派資料
      const relevantAssignments = localAssignments.filter(assignment =>
        assignment.companyId === companyId &&
        assignment.questionId === assessmentId
      )

      if (relevantAssignments.length === 0) {
        return {
          success: true,
          message: '沒有需要遷移的資料',
          migrated: 0
        }
      }

      let successCount = 0
      let errorCount = 0
      const errors = []

      // 逐筆遷移指派資料
      for (const assignment of relevantAssignments) {
        try {
          await personnelAssignmentApi.createAssignment({
            company_id: assignment.companyId,
            assessment_id: assignment.questionId,
            question_content_id: assignment.contentId,
            personnel_id: assignment.userId,
            // 從localStorage資料構造人員資料
            personnel_name: assignment.personnelName,
            personnel_department: assignment.department,
            personnel_position: assignment.position
          })
          successCount++
        } catch (error) {
          errorCount++
          errors.push({
            assignment: assignment,
            error: error.message
          })
        }
      }

      return {
        success: errorCount === 0,
        message: `遷移完成：成功 ${successCount} 筆，失敗 ${errorCount} 筆`,
        migrated: successCount,
        failed: errorCount,
        errors: errors
      }

    } catch (error) {
      console.error('Migration failed:', error)
      return {
        success: false,
        message: '遷移過程發生錯誤',
        error: error.message
      }
    }
  },

  /**
   * 清除localStorage中已成功遷移的資料
   * @param {number} companyId - 公司ID
   * @param {number} assessmentId - 評估ID
   */
  clearMigratedLocalStorageData(companyId, assessmentId) {
    if (process.client) {
      try {
        const assignments = this.getLocalStorageAssignments()
        const remaining = assignments.filter(assignment =>
          !(assignment.companyId === companyId && assignment.questionId === assessmentId)
        )

        localStorage.setItem('esg-question-assignments', JSON.stringify(remaining))
        console.log(`已清除 localStorage 中公司 ${companyId} 評估 ${assessmentId} 的資料`)
      } catch (error) {
        console.error('Error clearing localStorage data:', error)
      }
    }
  },

  /**
   * 取得指派歷史記錄
   * @param {number} companyId - 公司ID
   * @param {number} assessmentId - 評估ID
   * @param {Object} filters - 篩選條件
   */
  async getAssignmentHistory(companyId, assessmentId, filters = {}) {
    const queryParams = new URLSearchParams()
    if (filters.personnel_id) queryParams.append('personnel_id', filters.personnel_id)
    if (filters.question_content_id) queryParams.append('question_content_id', filters.question_content_id)
    if (filters.action_type) queryParams.append('action_type', filters.action_type)
    if (filters.start_date) queryParams.append('start_date', filters.start_date)
    if (filters.end_date) queryParams.append('end_date', filters.end_date)

    const queryString = queryParams.toString()
    const endpoint = `/companies/${companyId}/assessments/${assessmentId}/history${queryString ? '?' + queryString : ''}`

    return await apiRequest(endpoint, {
      method: 'GET'
    })
  },

  /**
   * 取得題項內容的指派歷史
   * @param {number} companyId - 公司ID
   * @param {number} assessmentId - 評估ID
   * @param {string} contentId - 題項內容ID
   */
  async getContentHistory(companyId, assessmentId, contentId) {
    return await apiRequest(`/companies/${companyId}/assessments/${assessmentId}/contents/${contentId}/history`, {
      method: 'GET'
    })
  },

  /**
   * 取得人員的指派歷史
   * @param {number} companyId - 公司ID
   * @param {number} assessmentId - 評估ID
   * @param {number} personnelId - 人員ID
   */
  async getPersonnelHistory(companyId, assessmentId, personnelId) {
    return await apiRequest(`/companies/${companyId}/assessments/${assessmentId}/personnel/${personnelId}/history`, {
      method: 'GET'
    })
  },

  /**
   * 取得歷史統計資訊
   * @param {number} companyId - 公司ID
   * @param {number} assessmentId - 評估ID
   */
  async getHistoryStatistics(companyId, assessmentId) {
    return await apiRequest(`/companies/${companyId}/assessments/${assessmentId}/history/statistics`, {
      method: 'GET'
    })
  }
}

/**
 * 錯誤處理輔助函數
 */
export const handleApiError = (error, defaultMessage = '操作失敗') => {
  console.error('API錯誤:', error)

  if (error.data?.message) {
    return error.data.message
  }

  if (error.message) {
    return error.message
  }

  return defaultMessage
}