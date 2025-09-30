export default defineNuxtRouteMiddleware((to, from) => {
  // Handle client-side navigation after refresh
  if (process.client) {
    // Store current route in sessionStorage for refresh handling
    try {
      if (to.path && typeof sessionStorage !== 'undefined') {
        sessionStorage.setItem('lastVisitedRoute', to.path)
      }
    } catch (error) {
      console.warn('Failed to access sessionStorage:', error)
    }
    
    // Handle specific route patterns that might need special handling
    const path = to.path
    
    // Ensure risk assessment routes are properly handled
    if (path.startsWith('/risk-assessment')) {
      // Dynamic route handling for template content
      if (path.includes('/templates/') && path.includes('/content')) {
        const templateId = path.split('/templates/')[1]?.split('/content')[0]
        if (templateId && !isNaN(templateId)) {
          // Valid template ID, allow navigation
          return
        } else {
          // Invalid template ID, redirect to templates list
          return navigateTo('/risk-assessment/templates')
        }
      }
      
      // Dynamic route handling for question management
      if (path.includes('/questions/') && path.includes('/management')) {
        const companyId = path.split('/questions/')[1]?.split('/management')[0]
        if (companyId && !isNaN(companyId)) {
          // Valid company ID, allow navigation
          return
        } else {
          // Invalid company ID, redirect to questions list
          return navigateTo('/risk-assessment/questions')
        }
      }
    }
  }
})