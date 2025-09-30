import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

export default defineConfig({
  plugins: [vue()],
  test: {
    environment: 'happy-dom',
    globals: true,
    setupFiles: ['./tests/setup.ts'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      exclude: [
        'node_modules/',
        '.nuxt/',
        'dist/',
        'coverage/',
        'tests/',
        '**/*.test.{js,ts,vue}',
        '**/*.spec.{js,ts,vue}',
        '**/*.config.{js,ts}',
        '**/types.ts'
      ],
      include: [
        'utils/**/*.{js,ts}',
        'stores/**/*.{js,ts}',
        'composables/**/*.{js,ts}',
        'pages/**/*.vue',
        'components/**/*.vue'
      ],
      thresholds: {
        global: {
          branches: 80,
          functions: 80,
          lines: 80,
          statements: 80
        }
      }
    },
    include: [
      'tests/**/*.test.{js,ts}',
      'tests/**/*.spec.{js,ts}'
    ],
    exclude: [
      'node_modules/',
      '.nuxt/',
      'dist/'
    ]
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, '.'),
      '~': resolve(__dirname, '.'),
      '#app': resolve(__dirname, 'node_modules/nuxt/dist/app'),
      '#imports': resolve(__dirname, '.nuxt/imports')
    }
  }
})