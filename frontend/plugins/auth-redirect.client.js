export default defineNuxtPlugin(() => {
  const router = useRouter()
  const config = useRuntimeConfig()

  // Only run on client side
  if (!process.client) return

  // Ensure router is available and has the beforeEach method
  if (!router || typeof router.beforeEach !== 'function') return

  // Check if authentication is disabled
  if (!config.public.requireAuth) {
    // If current page is login, redirect to dashboard (home)
    router.beforeEach((to, from, next) => {
      if (to.path === '/auth/login') {
        next('/')
      } else {
        next()
      }
    })
  }
})