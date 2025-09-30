// Storage key for localStorage
const STORAGE_KEY = 'esg-question-assignments'

// Question assignments data (personnel to question content many-to-many relationship)
const assignments = ref([])

// Load data from localStorage
const loadFromStorage = () => {
  if (process.client) {
    const stored = localStorage.getItem(STORAGE_KEY)
    if (stored) {
      try {
        assignments.value = JSON.parse(stored)
      } catch (error) {
        console.error('Error loading assignments from storage:', error)
        assignments.value = []
      }
    }
  }
}

// Save data to localStorage
const saveToStorage = () => {
  if (process.client) {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(assignments.value))
    } catch (error) {
      console.error('Error saving assignments to storage:', error)
    }
  }
}

// Initialize data on first load
if (process.client) {
  loadFromStorage()
}

export const useQuestionAssignments = () => {
  // Get assignments for a specific company and question
  const getAssignmentsByCompanyAndQuestion = (companyId, questionId) => {
    return assignments.value.filter(assignment => 
      assignment.companyId === parseInt(companyId) && 
      assignment.questionId === parseInt(questionId)
    )
  }

  // Get assignments for a specific question content item
  const getAssignmentsByContent = (companyId, questionId, contentId) => {
    return assignments.value.filter(assignment => 
      assignment.companyId === parseInt(companyId) && 
      assignment.questionId === parseInt(questionId) &&
      assignment.contentId === contentId
    )
  }

  // Get all assigned personnel for a question (unique users)
  const getAssignedPersonnel = (companyId, questionId) => {
    const questionAssignments = getAssignmentsByCompanyAndQuestion(companyId, questionId)
    const uniqueUsers = new Map()
    
    questionAssignments.forEach(assignment => {
      if (!uniqueUsers.has(assignment.userId)) {
        uniqueUsers.set(assignment.userId, {
          id: assignment.userId,
          personnelName: assignment.personnelName,
          department: assignment.department,
          position: assignment.position,
          assignedContentIds: [assignment.contentId],
          assignmentCount: 1,
          firstAssignedAt: assignment.createdAt
        })
      } else {
        const user = uniqueUsers.get(assignment.userId)
        user.assignedContentIds.push(assignment.contentId)
        user.assignmentCount += 1
      }
    })
    
    return Array.from(uniqueUsers.values())
  }

  // Check if a user is assigned to any content in a question
  const isUserAssignedToQuestion = (companyId, questionId, userId) => {
    return assignments.value.some(assignment =>
      assignment.companyId === parseInt(companyId) &&
      assignment.questionId === parseInt(questionId) &&
      assignment.userId === parseInt(userId)
    )
  }

  // Check if a user is assigned to a specific content item
  const isUserAssignedToContent = (companyId, questionId, contentId, userId) => {
    return assignments.value.some(assignment =>
      assignment.companyId === parseInt(companyId) &&
      assignment.questionId === parseInt(questionId) &&
      assignment.contentId === contentId &&
      assignment.userId === parseInt(userId)
    )
  }

  // Assign user to specific question content
  const assignUserToContent = (companyId, questionId, contentId, user) => {
    // Check if assignment already exists
    if (isUserAssignedToContent(companyId, questionId, contentId, user.id)) {
      return false // Already assigned
    }

    const newAssignment = {
      id: Date.now() + Math.random(), // Ensure unique ID
      companyId: parseInt(companyId),
      questionId: parseInt(questionId),
      contentId: contentId,
      userId: user.id,
      personnelName: user.name,
      department: user.department,
      position: user.position,
      createdAt: new Date()
    }

    assignments.value.push(newAssignment)
    saveToStorage()
    return true
  }

  // Assign user to multiple content items
  const assignUserToMultipleContents = (companyId, questionId, contentIds, user) => {
    let assignedCount = 0
    contentIds.forEach(contentId => {
      if (assignUserToContent(companyId, questionId, contentId, user)) {
        assignedCount++
      }
    })
    return assignedCount
  }

  // Remove user assignment from specific content
  const removeUserFromContent = (companyId, questionId, contentId, userId) => {
    const index = assignments.value.findIndex(assignment =>
      assignment.companyId === parseInt(companyId) &&
      assignment.questionId === parseInt(questionId) &&
      assignment.contentId === contentId &&
      assignment.userId === parseInt(userId)
    )
    
    if (index > -1) {
      assignments.value.splice(index, 1)
      saveToStorage()
      return true
    }
    return false
  }

  // Remove user from all content items in a question
  const removeUserFromQuestion = (companyId, questionId, userId) => {
    const initialLength = assignments.value.length
    assignments.value = assignments.value.filter(assignment =>
      !(assignment.companyId === parseInt(companyId) &&
        assignment.questionId === parseInt(questionId) &&
        assignment.userId === parseInt(userId))
    )
    
    if (assignments.value.length < initialLength) {
      saveToStorage()
      return true
    }
    return false
  }

  // Get content assignment summary for a question
  const getContentAssignmentSummary = (companyId, questionId, questionContent) => {
    return questionContent.map(content => {
      const contentAssignments = getAssignmentsByContent(companyId, questionId, content.id)
      return {
        contentId: content.id,
        topic: content.topic,
        description: content.description,
        categoryId: content.categoryId,
        assignedUsers: contentAssignments.map(assignment => ({
          userId: assignment.userId,
          personnelName: assignment.personnelName,
          department: assignment.department,
          position: assignment.position,
          assignedAt: assignment.createdAt
        })),
        assignmentCount: contentAssignments.length
      }
    })
  }

  return {
    assignments: readonly(assignments),
    getAssignmentsByCompanyAndQuestion,
    getAssignmentsByContent,
    getAssignedPersonnel,
    isUserAssignedToQuestion,
    isUserAssignedToContent,
    assignUserToContent,
    assignUserToMultipleContents,
    removeUserFromContent,
    removeUserFromQuestion,
    getContentAssignmentSummary,
    loadFromStorage,
    saveToStorage
  }
}