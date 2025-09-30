/**
 * ApexCharts plugin for client-side only
 * 修復重複導入問題：確保只載入一次
 */

// Global flag to prevent duplicate loading
const APEXCHARTS_LOADED_KEY = '__NUXT_APEXCHARTS_LOADED__'

export default defineNuxtPlugin({
  name: 'apexcharts-plugin',
  enforce: 'default', // Load at default priority
  setup: async (nuxtApp) => {
    // Only run on client side
    if (!process.client) {
      // Server-side: provide null
      return {
        provide: {
          apexCharts: null
        }
      }
    }

    try {
      // Global flag check - prevent multiple plugin executions
      if (typeof window !== 'undefined' && window[APEXCHARTS_LOADED_KEY]) {
        console.log('ApexCharts plugin already executed, skipping...')
        return {
          provide: {
            apexCharts: window[APEXCHARTS_LOADED_KEY]
          }
        }
      }

      // Check if ApexCharts component is already registered
      if (nuxtApp.vueApp.component('apexchart')) {
        console.log('ApexCharts component already registered, skipping...')
        const existingComponent = nuxtApp.vueApp.component('apexchart')
        if (typeof window !== 'undefined') {
          window[APEXCHARTS_LOADED_KEY] = existingComponent
        }
        return {
          provide: {
            apexCharts: existingComponent
          }
        }
      }

      console.log('Loading ApexCharts plugin...')

      // Dynamic import with singleton pattern
      let VueApexCharts

      // Try to get from cache first
      if (typeof window !== 'undefined' && window.__VUE_APEXCHARTS_CACHE__) {
        VueApexCharts = window.__VUE_APEXCHARTS_CACHE__
        console.log('Using cached ApexCharts module')
      } else {
        // Import the module
        const VueApexChartsModule = await import('vue3-apexcharts').catch(err => {
          console.error('Failed to import vue3-apexcharts:', err)
          throw new Error(`ApexCharts import failed: ${err.message}`)
        })

        if (!VueApexChartsModule || !VueApexChartsModule.default) {
          throw new Error('vue3-apexcharts module is invalid or missing default export')
        }

        VueApexCharts = VueApexChartsModule.default

        // Cache the module
        if (typeof window !== 'undefined') {
          window.__VUE_APEXCHARTS_CACHE__ = VueApexCharts
        }
      }

      // Register the component globally only if not already registered
      if (!nuxtApp.vueApp.component('apexchart')) {
        nuxtApp.vueApp.component('apexchart', VueApexCharts)
        console.log('ApexCharts component registered successfully')
      }

      // Set global flag
      if (typeof window !== 'undefined') {
        window[APEXCHARTS_LOADED_KEY] = VueApexCharts
      }

      // Make ApexCharts globally available
      return {
        provide: {
          apexCharts: VueApexCharts
        }
      }
    } catch (error) {
      console.error('Critical error in ApexCharts plugin:', error)

      // Even on error, set a flag to prevent retries
      if (typeof window !== 'undefined') {
        window[APEXCHARTS_LOADED_KEY] = null
      }

      return {
        provide: {
          apexCharts: null
        }
      }
    }
  }
})