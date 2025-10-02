// Format date to display format (2024/01/15 上午08:00)
const formatDateForDisplay = (date) => {
  if (!date) return ''

  // Ensure date is a Date object
  const dateObj = date instanceof Date ? date : new Date(date)

  // Check if date is valid
  if (isNaN(dateObj.getTime())) return ''

  // Manual formatting to ensure exact format: 2024/01/15 上午08:00
  const year = dateObj.getFullYear()
  const month = String(dateObj.getMonth() + 1).padStart(2, '0')
  const day = String(dateObj.getDate()).padStart(2, '0')
  const hours = dateObj.getHours()
  const minutes = String(dateObj.getMinutes()).padStart(2, '0')

  // Convert to 12-hour format with 上午/下午
  let period = '上午'
  let displayHours = hours

  if (hours === 0) {
    displayHours = 12
    period = '上午'
  } else if (hours < 12) {
    period = '上午'
  } else if (hours === 12) {
    period = '下午'
  } else {
    displayHours = hours - 12
    period = '下午'
  }

  const formattedHours = String(displayHours).padStart(2, '0')

  return `${year}/${month}/${day} ${period}${formattedHours}:${minutes}`
}

export const useQuestionManagement = () => {
  // Initialize API inside the composable function (SSR safe)
  const api = useApi()

  // Use useState for SSR-safe reactive state
  const questionManagement = useState('questionManagement', () => ({}))

  // Load data from API for a specific company, optionally filtered by userId
  const loadCompanyAssessments = async (companyId, userId = null) => {
    if (!process.client) return

    try {
      const result = await api.riskAssessment.getByCompany(companyId, userId)
      const response = result.data
      if (response.success && response.data.assessments) {
        let assessments = response.data.assessments

        // If userId is provided, filter assessments by user assignments
        if (userId) {
          const filteredAssessments = []

          for (const assessment of assessments) {
            try {
              const assignmentResult = await api.personnel.getAssignments(companyId, assessment.id)
              const assignmentResponse = assignmentResult.data

              if (assignmentResponse.success && assignmentResponse.data) {
                const assignments = assignmentResponse.data.assignments || []

                // Check if current user is assigned to any questions in this assessment
                const userAssignments = assignments.filter(assignment =>
                  assignment.personnel_id === userId
                )

                if (userAssignments.length > 0) {
                  filteredAssessments.push(assessment)
                }
              }
            } catch (error) {
              console.error(`Error checking assignments for assessment ${assessment.id}:`, error)
              // Include assessment if we can't check assignments (safe fallback)
              filteredAssessments.push(assessment)
            }
          }

          assessments = filteredAssessments
        }

        // Transform API data to match localStorage format
        const transformedData = assessments.map(assessment => ({
          id: assessment.id,
          templateId: assessment.template_id,
          templateVersion: assessment.template_version || assessment.template_version_name,
          year: assessment.assessment_year,
          createdAt: formatDateForDisplay(new Date(assessment.created_at)),
          status: assessment.status,
          copiedFrom: assessment.copied_from,
          includeQuestions: assessment.include_questions,
          includeResults: assessment.include_results
        }))

        // Store with a key that includes userId if filtering
        const cacheKey = userId ? `${parseInt(companyId)}_user_${userId}` : parseInt(companyId)
        questionManagement.value[cacheKey] = transformedData
      }
    } catch (error) {
      console.error('Error loading company assessments:', error)
      // Keep existing data on error
    }
  }

  // Save individual assessment to API
  const saveAssessmentToAPI = async (assessmentData) => {
    if (!process.client) {
      return null
    }

    try {
      const result = await api.riskAssessment.create(assessmentData)

      if (result.success && result.data) {
        return result.data.success ? result.data.data : null
      }
      return null
    } catch (error) {
      console.error('Error saving assessment to API:', error)
      return null
    }
  }

  // Get question management items for a specific company, optionally filtered by userId
  const getQuestionManagementByCompany = async (companyId, userId = null) => {
    const cacheKey = userId ? `${parseInt(companyId)}_user_${userId}` : parseInt(companyId)

    // If data doesn't exist in cache, load from API
    if (!questionManagement.value[cacheKey]) {
      await loadCompanyAssessments(companyId, userId)
    }

    return questionManagement.value[cacheKey] || []
  }

  // Add question management item for a company
  const addQuestionManagementItem = async (companyId, itemData) => {
    const id = parseInt(companyId)

    try {
      // Prepare data for API
      const apiData = {
        company_id: companyId.toString(),
        template_id: itemData.templateId,
        template_version: itemData.templateVersion,
        assessment_year: itemData.year
      }

      // Save to API
      const savedAssessment = await saveAssessmentToAPI(apiData)

      if (savedAssessment) {
        // 後端應該已經自動同步範本架構，但為了確保同步成功，我們在前端也檢查一下
        try {
          // 檢查是否有架構資料，如果沒有則手動同步
          const structureResult = await api.questionManagement.getStructure(savedAssessment.id)
          const structureResponse = structureResult.data

          if (structureResponse.success) {
            const structure = structureResponse.data.structure
            const hasStructure = (structure.categories && structure.categories.length > 0) ||
                               (structure.topics && structure.topics.length > 0) ||
                               (structure.factors && structure.factors.length > 0)

            if (!hasStructure) {
              const syncResult = await api.questionManagement.syncFromTemplate(savedAssessment.id)
              const syncResponse = syncResult.data
            }
          }
        } catch (syncError) {
          console.error('Template structure sync check failed:', syncError)
          // 不影響主要流程，只記錄警告
        }

        // Transform API response to match localStorage format
        const newItem = {
          id: savedAssessment.id,
          templateId: savedAssessment.template_id,
          templateVersion: savedAssessment.template_version || savedAssessment.template_version_name,
          year: savedAssessment.assessment_year,
          createdAt: formatDateForDisplay(new Date(savedAssessment.created_at)),
          status: savedAssessment.status
        }

        // Update local cache
        if (!questionManagement.value[id]) {
          questionManagement.value[id] = []
        }
        questionManagement.value[id].unshift(newItem)

        return newItem
      }
    } catch (error) {
      console.error('Error adding question management item:', error)
    }

    return null
  }

  // Update question management item
  const updateQuestionManagementItem = async (companyId, itemId, itemData) => {
    const id = parseInt(companyId)

    try {
      // Prepare data for API
      const apiData = {
        template_id: itemData.templateId,
        template_version: itemData.templateVersion,
        assessment_year: itemData.year
      }

      // Update via API
      const result = await api.riskAssessment.update(itemId, apiData)
      const response = result.data

      if (response.success && response.data) {
        // Update local cache
        const items = questionManagement.value[id]
        if (items) {
          const item = items.find(item => item.id === itemId)
          if (item) {
            item.templateId = response.data.template_id
            item.templateVersion = response.data.template_version || response.data.template_version_name
            item.year = response.data.assessment_year
            return item
          }
        }
      }
    } catch (error) {
      console.error('Error updating question management item:', error)
    }

    return null
  }

  // Delete question management item
  const deleteQuestionManagementItem = async (companyId, itemId) => {
    const id = parseInt(companyId)

    try {
      // Delete from API
      const result = await api.riskAssessment.delete(itemId)
      const response = result.data

      if (response.success) {
        // Update local cache
        const items = questionManagement.value[id]
        if (items) {
          const index = items.findIndex(item => item.id === itemId)
          if (index > -1) {
            items.splice(index, 1)
            return true
          }
        }
      }
    } catch (error) {
      console.error('Error deleting question management item:', error)
    }

    return false
  }

  // Copy question management item
  const copyQuestionManagementItem = async (companyId, itemId, copyOptions) => {
    const id = parseInt(companyId)

    try {
      // Prepare data for API copy
      const copyData = {
        company_id: companyId.toString(),
        include_questions: copyOptions.includeQuestions ? 1 : 0,
        include_results: copyOptions.includeResults ? 1 : 0
      }

      // Copy via API
      const result = await api.riskAssessment.copy(itemId, copyData)
      const response = result.data

      if (response.success && response.data) {
        // Transform API response to match localStorage format
        const newItem = {
          id: response.data.id,
          templateId: response.data.template_id,
          templateVersion: response.data.template_version || response.data.template_version_name,
          year: response.data.assessment_year,
          createdAt: formatDateForDisplay(new Date(response.data.created_at)),
          copiedFrom: response.data.copied_from,
          includeQuestions: response.data.include_questions,
          includeResults: response.data.include_results,
          status: response.data.status
        }

        // Update local cache
        if (!questionManagement.value[id]) {
          questionManagement.value[id] = []
        }
        questionManagement.value[id].unshift(newItem)

        return newItem
      }
    } catch (error) {
      console.error('Error copying question management item:', error)
    }

    return null
  }

  // Initialize empty array for new company (API version - just ensure local cache exists)
  const initializeCompanyQuestionManagement = async (companyId, userId = null) => {
    const cacheKey = userId ? `${parseInt(companyId)}_user_${userId}` : parseInt(companyId)
    if (!questionManagement.value[cacheKey]) {
      await loadCompanyAssessments(companyId, userId)
    }
  }

  // Check if company has any question management items
  const hasQuestionManagementItems = async (companyId, userId = null) => {
    const items = await getQuestionManagementByCompany(companyId, userId)
    return items.length > 0
  }

  // Refresh data from API
  const refreshCompanyAssessments = async (companyId, userId = null) => {
    await loadCompanyAssessments(companyId, userId)
  }

  return {
    questionManagement: readonly(questionManagement),
    getQuestionManagementByCompany,
    addQuestionManagementItem,
    updateQuestionManagementItem,
    deleteQuestionManagementItem,
    copyQuestionManagementItem,
    initializeCompanyQuestionManagement,
    hasQuestionManagementItems,
    refreshCompanyAssessments,
    loadCompanyAssessments
  }
}