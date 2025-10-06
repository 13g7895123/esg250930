export default defineNuxtConfig({
  devtools: { enabled: true },
  ssr: true,
  devServer: {
    port: 3101
  },
  tailwindcss: {
    configPath: '~/tailwind.config.js'
  },
  routeRules: {
    // Client-side only pages that must not be pre-rendered
    '/risk-assessment/**': { ssr: false },
    '/settings/**': { ssr: false }
  },
  app: {
    head: {
      title: process.env.NUXT_PUBLIC_APP_TITLE || 'ESGMATE'
    }
  },
  modules: [
    '@nuxt/ui',
    '@pinia/nuxt'
  ],
  plugins: [
    '~/plugins/pinia-persisted.client.js',
    '~/plugins/auth.client.js',
    '~/plugins/auth-redirect.client.js',
    '~/plugins/navigation-state.client.js',
    '~/plugins/websiteSettings.client.js',
    '~/plugins/error-handler.client.js',
    '~/plugins/route-debug.client.js',
    '~/plugins/apexcharts.client.js',
    '~/plugins/sweetalert.client.js'
  ],
  // Transpile ApexCharts, SweetAlert2 and Heroicons for better compatibility
  build: {
    transpile: ['vue3-apexcharts', 'sweetalert2', '@heroicons/vue']
  },
  css: ['~/assets/css/main.css'],
  runtimeConfig: {
    // Private keys (only available on server-side)
    // Public keys (exposed to client-side)
    public: {
      apiBaseUrl: process.env.NUXT_PUBLIC_API_BASE_URL || process.env.BACKEND_API_URL || 'http://localhost:9218/api/v1',
      backendUrl: process.env.NUXT_PUBLIC_BACKEND_URL || process.env.BACKEND_URL || process.env.BACKEND_API_URL || 'http://localhost:9218/api/v1',
      backendHost: process.env.NUXT_PUBLIC_BACKEND_HOST || process.env.BACKEND_HOST || 'localhost',
      backendPort: process.env.NUXT_PUBLIC_BACKEND_PORT || process.env.BACKEND_PORT || '9218',
      requireAuth: process.env.NUXT_PUBLIC_REQUIRE_AUTH === 'true',
      showFooter: process.env.NUXT_PUBLIC_SHOW_FOOTER === 'true',
      appTitle: process.env.NUXT_PUBLIC_APP_TITLE || 'ESG系統'
    }
  },
  nitro: {
    // Development server setup with API proxy
    devProxy: {
      '/api': {
        target: process.env.BACKEND_URL || 'http://localhost:9218',
        changeOrigin: true,
        prependPath: false,
        ws: true
      }
    }
  },
  colorMode: {
    preference: 'light', // Default to light mode
    fallback: 'light',
    hid: 'nuxt-color-mode-script',
    globalName: '__NUXT_COLOR_MODE__',
    componentName: 'ColorScheme',
    classPrefix: '',
    classSuffix: '',
    storageKey: 'nuxt-color-mode',
    dataValue: 'theme' // This sets data-theme attribute
  },
  // Build optimization - ensure apexcharts and heroicons can be imported correctly
    vite: {
    optimizeDeps: {
      include: [
        'apexcharts',
        '@heroicons/vue/24/outline',
        '@heroicons/vue/24/solid',
        '@heroicons/vue/20/solid',
        '@nuxt/ui'
      ],
      exclude: ['vue3-apexcharts']
    },
    ssr: {
      noExternal: ['@nuxt/ui']
    },
    define: {
      // Prevent multiple ApexCharts instances
      __VUE_APEXCHARTS_LOADED__: false
    },
    server: {
      allowedHosts: [
        'project.local'
      ],
      proxy: {
        '/api': {
          target: process.env.BACKEND_URL || 'http://localhost:9218',
          changeOrigin: true,
          secure: false,
          rewrite: (path) => path
        }
      }
    },
    build: {
      rollupOptions: {
        external: [],
        output: {
          globals: {},
          // Prevent duplicate chunks for ApexCharts
          manualChunks: {
            'apexcharts-vendor': ['apexcharts', 'vue3-apexcharts']
          }
        }
      }
    }
  },
  // Disable problematic nuxt-icon server bundle to prevent SSR issues
  icon: {
    serverBundle: false
  }
})