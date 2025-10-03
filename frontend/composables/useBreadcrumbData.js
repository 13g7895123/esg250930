/**
 * Breadcrumb data management composable
 * Handles loading and caching of data needed for breadcrumbs
 */
export const useBreadcrumbData = () => {
  // Cache for company names
  const breadcrumbCompanyNames = useState('breadcrumbCompanyNames', () => ({}))

  // Cache for assessment to company mapping (includes year)
  const breadcrumbAssessmentMap = useState('breadcrumbAssessmentMap', () => ({}))

  // Cache for question content info (includes company_id, assessment_id, year)
  const breadcrumbContentInfo = useState('breadcrumbContentInfo', () => ({}))

  const { getCompanyNameById } = useLocalCompanies()

  /**
   * Load company name by ID
   */
  const loadCompanyName = async (companyId) => {
    if (breadcrumbCompanyNames.value[companyId]) return

    try {
      const companyName = await getCompanyNameById(companyId)
      if (companyName) {
        breadcrumbCompanyNames.value[companyId] = companyName
      }
    } catch (error) {
      console.error('Error loading company name:', error)
    }
  }

  /**
   * Load assessment data to get company ID and year
   */
  const loadAssessmentData = async (assessmentId) => {
    if (breadcrumbAssessmentMap.value[assessmentId]) return

    try {
      // Try assignments API first
      let response = await $fetch(`/api/v1/question-management/assessment/${assessmentId}/assignments`).catch(() => null)

      // If assignments API fails, try statistics API
      if (!response) {
        response = await $fetch(`/api/v1/question-management/assessment/${assessmentId}/statistics-results`).catch(() => null)
      }

      if (response && response.success) {
        const companyId = response.data.assessment_info?.company_id || response.data.assessment?.company_id
        const year = response.data.assessment_info?.year || response.data.assessment?.year

        if (companyId) {
          breadcrumbAssessmentMap.value[assessmentId] = { companyId, year }
          // Also load company name
          await loadCompanyName(companyId)
        }
      }
    } catch (error) {
      console.error('Error loading assessment data:', error)
    }
  }

  /**
   * Load question content data to get company ID, assessment ID, and year
   */
  const loadContentData = async (contentId) => {
    if (breadcrumbContentInfo.value[contentId]) return

    try {
      const contentResponse = await $fetch(`/api/v1/question-management/contents/${contentId}`)

      if (contentResponse.success && contentResponse.data?.content) {
        const content = contentResponse.data.content
        const assessmentId = content.assessment_id

        // Load assessment data to get year
        if (assessmentId) {
          await loadAssessmentData(assessmentId)

          const assessmentData = breadcrumbAssessmentMap.value[assessmentId]
          breadcrumbContentInfo.value[contentId] = {
            companyId: content.company_id,
            assessmentId: assessmentId,
            year: assessmentData?.year
          }

          // Also load company name
          if (content.company_id) {
            await loadCompanyName(content.company_id)
          }
        }
      }
    } catch (error) {
      console.error('Error loading content data:', error)
    }
  }

  return {
    breadcrumbCompanyNames,
    breadcrumbAssessmentMap,
    breadcrumbContentInfo,
    loadCompanyName,
    loadAssessmentData,
    loadContentData
  }
}
