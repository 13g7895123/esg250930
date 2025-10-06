/**
 * 人員指派API組合式函數
 *
 * 使用API替代localStorage進行人員指派管理
 * 提供與後端資料庫同步的人員指派功能
 *
 * @author Claude Code Assistant
 * @version 1.0
 * @since 2025-09-24
 */

import { ref, computed } from 'vue'
import { personnelAssignmentApi, handleApiError } from '~/services/personnelAssignmentApi'

// 全域狀態管理
const assignments = ref([])
const personnel = ref([])
const isLoading = ref(false)
const error = ref(null)

export const usePersonnelAssignmentApi = () => {

  /**
   * 載入公司人員列表
   * @param {number} companyId - 公司ID
   * @returns {Promise<Array>} 人員列表
   */
  const loadPersonnel = async (companyId) => {
    try {
      isLoading.value = true
      error.value = null

      const response = await personnelAssignmentApi.getPersonnelByCompany(companyId)

      if (response.success) {
        personnel.value = response.data
        return response.data
      } else {
        throw new Error(response.message || '載入人員資料失敗')
      }

    } catch (err) {
      error.value = handleApiError(err, '載入人員資料時發生錯誤')
      throw err
    } finally {
      isLoading.value = false
    }
  }

  /**
   * 同步人員資料
   * @param {number} companyId - 公司ID
   * @returns {Promise<Object>} 同步結果
   */
  const syncPersonnel = async (companyId) => {
    try {
      isLoading.value = true
      error.value = null

      const response = await personnelAssignmentApi.syncPersonnel(companyId)

      if (response.success) {
        // 重新載入人員資料
        await loadPersonnel(companyId)
        return response.data
      } else {
        throw new Error(response.message || '同步人員資料失敗')
      }

    } catch (err) {
      error.value = handleApiError(err, '同步人員資料時發生錯誤')
      throw err
    } finally {
      isLoading.value = false
    }
  }

  /**
   * 載入指派摘要
   * @param {number} companyId - 公司ID
   * @param {number} assessmentId - 評估ID
   * @returns {Promise<Object>} 指派摘要資料
   */
  const loadAssignmentSummary = async (companyId, assessmentId) => {
    try {
      isLoading.value = true
      error.value = null

      const response = await personnelAssignmentApi.getAssignmentSummary(companyId, assessmentId)

      if (response.success) {
        assignments.value = response.data.assignments
        return response.data
      } else {
        throw new Error(response.message || '載入指派摘要失敗')
      }

    } catch (err) {
      error.value = handleApiError(err, '載入指派摘要時發生錯誤')
      throw err
    } finally {
      isLoading.value = false
    }
  }

  /**
   * 指派人員到題項內容
   * @param {Object} assignmentData - 指派資料
   * @returns {Promise<boolean>} 是否成功
   */
  const assignPersonnelToContent = async (assignmentData) => {
    try {
      isLoading.value = true
      error.value = null

      const response = await personnelAssignmentApi.createAssignment(assignmentData)

      if (response.success) {
        // 更新本地狀態
        await loadAssignmentSummary(assignmentData.company_id, assignmentData.assessment_id)
        return true
      } else {
        throw new Error(response.message || '指派人員失敗')
      }

    } catch (err) {
      error.value = handleApiError(err, '指派人員時發生錯誤')
      return false
    } finally {
      isLoading.value = false
    }
  }

  /**
   * 批量指派人員
   * @param {Object} batchData - 批量指派資料
   * @returns {Promise<Object>} 批量指派結果
   */
  const batchAssignPersonnel = async (batchData) => {
    try {
      isLoading.value = true
      error.value = null

      const response = await personnelAssignmentApi.batchCreateAssignments(batchData)

      if (response.success) {
        // 更新本地狀態
        await loadAssignmentSummary(batchData.company_id, batchData.assessment_id)
        return response.data
      } else {
        throw new Error(response.message || '批量指派失敗')
      }

    } catch (err) {
      error.value = handleApiError(err, '批量指派時發生錯誤')
      throw err
    } finally {
      isLoading.value = false
    }
  }

  /**
   * 移除人員指派
   * @param {Object} assignmentData - 指派資料
   * @returns {Promise<boolean>} 是否成功
   */
  const removeAssignment = async (assignmentData) => {
    try {
      isLoading.value = true
      error.value = null

      const response = await personnelAssignmentApi.removeAssignment(assignmentData)

      if (response.success) {
        // 更新本地狀態
        await loadAssignmentSummary(assignmentData.company_id, assignmentData.assessment_id)
        return true
      } else {
        throw new Error(response.message || '移除指派失敗')
      }

    } catch (err) {
      error.value = handleApiError(err, '移除指派時發生錯誤')
      return false
    } finally {
      isLoading.value = false
    }
  }

  /**
   * 移除人員在評估中的所有指派
   * @param {number} companyId - 公司ID
   * @param {number} assessmentId - 評估ID
   * @param {number} personnelId - 人員ID
   * @returns {Promise<boolean>} 是否成功
   */
  const removePersonnelFromAssessment = async (companyId, assessmentId, personnelId) => {
    try {
      isLoading.value = true
      error.value = null

      const response = await personnelAssignmentApi.removePersonnelFromAssessment(
        companyId,
        assessmentId,
        personnelId
      )

      if (response.success) {
        // 更新本地狀態
        await loadAssignmentSummary(companyId, assessmentId)
        return true
      } else {
        throw new Error(response.message || '移除人員指派失敗')
      }

    } catch (err) {
      error.value = handleApiError(err, '移除人員指派時發生錯誤')
      return false
    } finally {
      isLoading.value = false
    }
  }

  /**
   * 更新指派狀態
   * @param {number} assignmentId - 指派ID
   * @param {string} status - 新狀態
   * @returns {Promise<boolean>} 是否成功
   */
  const updateAssignmentStatus = async (assignmentId, status) => {
    try {
      isLoading.value = true
      error.value = null

      const response = await personnelAssignmentApi.updateAssignmentStatus(assignmentId, status)

      if (response.success) {
        // 更新本地狀態中的指派記錄
        const assignmentIndex = assignments.value.findIndex(a => a.id === assignmentId)
        if (assignmentIndex !== -1) {
          assignments.value[assignmentIndex].assignment_status = status

          // 更新時間戳記
          const now = new Date()
          switch (status) {
            case 'accepted':
              assignments.value[assignmentIndex].accepted_at = now
              break
            case 'completed':
              assignments.value[assignmentIndex].completed_at = now
              break
          }
        }
        return true
      } else {
        throw new Error(response.message || '更新狀態失敗')
      }

    } catch (err) {
      error.value = handleApiError(err, '更新指派狀態時發生錯誤')
      return false
    } finally {
      isLoading.value = false
    }
  }

  /**
   * 轉換API資料格式以相容現有前端組件
   */
  const getCompatibleAssignmentSummary = (companyId, assessmentId, questionContent) => {
    const currentAssignments = assignments.value.filter(
      a => a.company_id === companyId && a.assessment_id === assessmentId
    )

    // 確保 questionContent 是陣列
    if (!Array.isArray(questionContent)) {
      return []
    }

    return questionContent.map(content => {
      // 檢查每個內容項目的 ID
      const contentId = content.id

      const contentAssignments = currentAssignments.filter(
        a => a.question_content_id === contentId
      )

      return {
        contentId: contentId,
        topic: content.topic || '',
        description: content.description || '',
        categoryId: content.categoryId || content.category_id,
        topicId: content.topicId || content.topic_id,
        factorId: content.factorId || content.risk_factor_id || content.factor_id,
        assignedUsers: contentAssignments.map(assignment => ({
          userId: assignment.personnel_id,
          personnelName: assignment.personnel_name,
          department: assignment.personnel_department,
          position: assignment.personnel_position,
          assignedAt: assignment.assigned_at,
          status: assignment.assignment_status
        })),
        assignmentCount: contentAssignments.length
      }
    })
  }

  /**
   * 取得已指派人員清單（相容現有格式）
   */
  const getCompatibleAssignedPersonnel = (companyId, assessmentId) => {
    const currentAssignments = assignments.value.filter(
      a => a.company_id === companyId && a.assessment_id === assessmentId
    )

    const uniquePersonnel = new Map()

    currentAssignments.forEach(assignment => {
      const personnelId = assignment.personnel_id

      if (!uniquePersonnel.has(personnelId)) {
        uniquePersonnel.set(personnelId, {
          id: personnelId,
          personnelName: assignment.personnel_name,
          department: assignment.personnel_department,
          position: assignment.personnel_position,
          assignedContentIds: [assignment.question_content_id],
          assignmentCount: 1,
          firstAssignedAt: assignment.assigned_at
        })
      } else {
        const person = uniquePersonnel.get(personnelId)
        person.assignedContentIds.push(assignment.question_content_id)
        person.assignmentCount += 1
      }
    })

    return Array.from(uniquePersonnel.values())
  }

  /**
   * 檢查是否已指派
   */
  const isPersonnelAssignedToContent = (companyId, assessmentId, contentId, personnelId) => {
    return assignments.value.some(assignment =>
      assignment.company_id === companyId &&
      assignment.assessment_id === assessmentId &&
      assignment.question_content_id === contentId &&
      assignment.personnel_id === personnelId
    )
  }

  // 計算屬性
  const availablePersonnel = computed(() => {
    return personnel.value.filter(p => p.status === 'active')
  })

  const assignmentStatistics = computed(() => {
    if (!assignments.value.length) return null

    const stats = {
      total: assignments.value.length,
      assigned: 0,
      accepted: 0,
      declined: 0,
      completed: 0
    }

    assignments.value.forEach(assignment => {
      stats[assignment.assignment_status]++
    })

    return stats
  })

  return {
    // 狀態
    assignments: readonly(assignments),
    personnel: readonly(personnel),
    availablePersonnel,
    isLoading: readonly(isLoading),
    error: readonly(error),
    assignmentStatistics,

    // 方法
    loadPersonnel,
    syncPersonnel,
    loadAssignmentSummary,
    assignPersonnelToContent,
    batchAssignPersonnel,
    removeAssignment,
    removePersonnelFromAssessment,
    updateAssignmentStatus,

    // 相容性方法
    getCompatibleAssignmentSummary,
    getCompatibleAssignedPersonnel,
    isPersonnelAssignedToContent,

    // 清除錯誤
    clearError: () => { error.value = null }
  }
}