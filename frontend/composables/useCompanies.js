// External API endpoint for companies
const COMPANIES_API_URL = 'https://csr.cc-sustain.com/admin/api/risk/get_all_companies'

// Companies data with loading and error states
const companies = ref([])
const loading = ref(false)
const error = ref(null)

// Transform API data to internal format
const transformApiData = (apiData) => {
  return apiData.map(company => ({
    id: company.com_id,        // Convert com_id to id (keep as string for consistency with API)
    companyName: company.com_title,  // Convert com_title to companyName
    abbreviation: company.abbreviation || '' // Add abbreviation field
  }))
}

// Fetch companies from external API
const fetchCompanies = async () => {
  loading.value = true
  error.value = null
  
  try {
    const response = await $fetch(COMPANIES_API_URL)
    
    if (response.success && Array.isArray(response.data)) {
      companies.value = transformApiData(response.data)
    } else {
      throw new Error('Invalid API response format')
    }
  } catch (err) {
    error.value = err.message || 'Failed to fetch companies'
    console.error('Error fetching companies:', err)
    // Fallback to empty array on error
    companies.value = []
  } finally {
    loading.value = false
  }
}

// Initialize data on first load (client-side only)
if (process.client) {
  fetchCompanies().then(() => {
    console.log('Companies loaded successfully:', companies.value.length)
  }).catch(err => {
    console.error('Failed to load companies:', err)
  })
}

export const useCompanies = () => {
  // Get company by ID (handle both string and number IDs)
  const getCompanyById = (id) => {
    return companies.value.find(company => company.id === String(id))
  }

  // Get company name by ID
  const getCompanyName = (id) => {
    const company = getCompanyById(id)
    return company ? company.companyName : null
  }

  // Add new company (Note: External API is read-only, this maintains local state only)
  const addCompany = (companyData) => {
    const newCompany = {
      id: `local_${Date.now()}`, // Use string ID for consistency
      companyName: companyData.companyName,
      abbreviation: companyData.abbreviation || ''
    }
    companies.value.unshift(newCompany)
    
    // Note: This is a local-only addition since the external API is read-only
    // In a real scenario, this would need to call an API to persist the data
    
    return newCompany
  }

  // Update company (Local-only update since API is read-only)
  const updateCompany = (id, companyData) => {
    const company = companies.value.find(c => c.id === String(id))
    if (company) {
      company.companyName = companyData.companyName
      if (companyData.abbreviation !== undefined) {
        company.abbreviation = companyData.abbreviation
      }
      // Note: This is a local-only update since the external API is read-only
    }
  }

  // Delete company (Local-only deletion since API is read-only)
  const deleteCompany = (id) => {
    const index = companies.value.findIndex(c => c.id === String(id))
    if (index > -1) {
      companies.value.splice(index, 1)
      // Note: This is a local-only deletion since the external API is read-only
    }
  }

  // Refresh companies data from API
  const refreshCompanies = async () => {
    await fetchCompanies()
  }

  return {
    companies: readonly(companies),
    loading: readonly(loading),
    error: readonly(error),
    getCompanyById,
    getCompanyName,
    addCompany,
    updateCompany,
    deleteCompany,
    refreshCompanies,
    fetchCompanies
  }
}