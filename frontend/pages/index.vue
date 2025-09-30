<script setup>
// Redirect to dashboard - only on client side for SSR compatibility
if (process.client) {
  await navigateTo('/dashboard')
} else {
  // Server-side redirect
  throw createError({
    statusCode: 302,
    statusMessage: 'Redirect to dashboard',
    headers: {
      location: '/dashboard'
    }
  })
}
</script>

<template>
  <div class="flex items-center justify-center min-h-screen">
    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-500"></div>
  </div>
</template>