export default defineNuxtPlugin(() => {
  const router = useRouter()

  // Ensure router is available
  if (!router) return

  // Handle navigation state after page refresh
  if (process.client) {
    // Restore last visited route if page was refreshed
    const handlePageLoad = () => {
      const lastRoute = sessionStorage.getItem('lastVisitedRoute')
      const currentPath = window.location.pathname
      
      // If current path doesn't match stored route and we have a stored route
      if (lastRoute && lastRoute !== currentPath && currentPath === '/') {
        // Validate the stored route before redirecting
        const validRoutes = [
          '/risk-assessment',
          '/risk-assessment/templates',
          '/risk-assessment/questions',
          '/dashboard',
          '/settings'
        ]
        
        // Check if it's a valid static route or valid dynamic route
        const isValidRoute = validRoutes.includes(lastRoute) || 
                            (lastRoute.startsWith('/risk-assessment/templates/') && lastRoute.includes('/content')) ||
                            (lastRoute.startsWith('/risk-assessment/questions/') && lastRoute.includes('/management'))
        
        if (isValidRoute) {
          router.push(lastRoute)
        }
      }
    }
    
    // Handle browser back/forward navigation
    const handlePopState = (event) => {
      // Update stored route when user navigates via browser buttons
      if (window.location.pathname) {
        sessionStorage.setItem('lastVisitedRoute', window.location.pathname)
      }
    }
    
    // Set up event listeners
    window.addEventListener('load', handlePageLoad)
    window.addEventListener('popstate', handlePopState)
    
    // Also handle router navigation - check if afterEach exists
    if (router.afterEach && typeof router.afterEach === 'function') {
      router.afterEach((to) => {
        if (to.path) {
          sessionStorage.setItem('lastVisitedRoute', to.path)
        }
      })
    }
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
      // Keep the route stored for refresh handling
      if (router.currentRoute && router.currentRoute.value && router.currentRoute.value.path) {
        sessionStorage.setItem('lastVisitedRoute', router.currentRoute.value.path)
      }
    })
  }
})