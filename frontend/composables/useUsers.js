// Store for users data indexed by company ID
const usersByCompany = ref({})
const isLoading = ref(false)
const loadingCompanies = ref(new Set())

export const useUsers = () => {
  // Get users by company ID with API call
  const getUsersByCompanyId = (companyId) => {
    const numericCompanyId = parseInt(companyId)

    // Return cached data if available
    if (usersByCompany.value[numericCompanyId]) {
      return usersByCompany.value[numericCompanyId]
    }

    // Start loading if not already loading
    if (!loadingCompanies.value.has(numericCompanyId)) {
      loadUsersForCompany(numericCompanyId)
    }

    // Return empty array while loading
    return []
  }

  // Load users for a specific company from API
  const loadUsersForCompany = async (companyId) => {
    const numericCompanyId = parseInt(companyId)

    if (loadingCompanies.value.has(numericCompanyId)) {
      return // Already loading
    }

    try {
      loadingCompanies.value.add(numericCompanyId)
      isLoading.value = true

      console.log(`Loading users for company ID: ${numericCompanyId}`)

      // Call the personnel API
      const response = await $fetch(`/api/v1/personnel/companies/${numericCompanyId}/personnel-assignments`)

      if (response.success) {
        // Handle different response structures
        let personnelData = []

        if (response.data) {
          if (Array.isArray(response.data)) {
            personnelData = response.data
          } else if (response.data.personnel) {
            personnelData = response.data.personnel
          }
        }

        // Map the API response to our expected format
        const mappedUsers = personnelData.map(user => ({
          ...user,
          companyId: numericCompanyId, // Ensure companyId is set
          // Map alternative field names
          id: user.id || user.personnel_id,
          name: user.name || user.personnel_name || user.user_name,
          department: user.department || user.dept_title,
          position: user.position || user.com_title
        }))

        // Store the users data
        if (!usersByCompany.value[numericCompanyId]) {
          usersByCompany.value[numericCompanyId] = []
        }
        usersByCompany.value[numericCompanyId] = mappedUsers

        console.log(`Loaded ${mappedUsers.length} users for company ${numericCompanyId}:`, mappedUsers)
      } else {
        console.warn('API returned unsuccessful response:', response)
        usersByCompany.value[numericCompanyId] = []
      }

    } catch (error) {
      console.error(`Error loading users for company ${numericCompanyId}:`, error)

      // Set empty array on error
      usersByCompany.value[numericCompanyId] = []

      // Show user-friendly error message
      const toast = useToast?.()
      if (toast) {
        toast.add({
          title: '載入失敗',
          description: '無法載入人員資料，請稍後再試',
          color: 'red'
        })
      }
    } finally {
      loadingCompanies.value.delete(numericCompanyId)
      isLoading.value = loadingCompanies.value.size > 0
    }
  }

  // Get user by ID (search across all loaded companies)
  const getUserById = (id) => {
    const numericId = parseInt(id)
    for (const companyUsers of Object.values(usersByCompany.value)) {
      const user = companyUsers.find(user => user.id === numericId)
      if (user) return user
    }
    return null
  }

  // Get all loaded users
  const getAllUsers = () => {
    return Object.values(usersByCompany.value).flat()
  }

  // Force reload users for a company
  const reloadUsersForCompany = async (companyId) => {
    const numericCompanyId = parseInt(companyId)

    // Clear cached data
    if (usersByCompany.value[numericCompanyId]) {
      delete usersByCompany.value[numericCompanyId]
    }

    // Clear loading state
    loadingCompanies.value.delete(numericCompanyId)

    // Reload
    await loadUsersForCompany(numericCompanyId)
  }

  // Check if users are being loaded for a company
  const isLoadingForCompany = (companyId) => {
    return loadingCompanies.value.has(parseInt(companyId))
  }

  // Clear all cached data
  const clearCache = () => {
    usersByCompany.value = {}
    loadingCompanies.value.clear()
    isLoading.value = false
  }

  return {
    users: readonly(usersByCompany),
    isLoading: readonly(isLoading),
    getUsersByCompanyId,
    getUserById,
    getAllUsers,
    loadUsersForCompany,
    reloadUsersForCompany,
    isLoadingForCompany,
    clearCache
  }
}