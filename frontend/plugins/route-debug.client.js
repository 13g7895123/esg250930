export default defineNuxtPlugin(() => {
  const router = useRouter()
  
  if (process.client && process.env.NODE_ENV === 'development' && router) {
    // Debug route navigation
    router.beforeEach((to, from) => {
      console.log('🚀 Navigating from:', from?.path || 'undefined', 'to:', to?.path || 'undefined')
      
      // Check for potential route issues
      if (to?.path?.includes('/risk-assessment')) {
        console.log('📋 Risk assessment route detected:', to.path)
        
        // Check for dynamic routes
        if (to.path.includes('/templates/') && to.path.includes('/content')) {
          const templateId = to.path.split('/templates/')[1]?.split('/content')[0]
          console.log('📄 Template content route - ID:', templateId)
        }
        
        if (to.path.includes('/questions/') && to.path.includes('/management')) {
          const companyId = to.path.split('/questions/')[1]?.split('/management')[0]
          console.log('❓ Question management route - Company ID:', companyId)
        }
      }
      
      return true
    })
    
    router.afterEach((to, from) => {
      console.log('✅ Navigation completed:', to?.path || 'undefined')
    })
    
    router.onError((error) => {
      console.error('❌ Router error:', error)
    })
  }
})