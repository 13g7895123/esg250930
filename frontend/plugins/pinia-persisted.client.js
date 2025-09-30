import { createPersistedState } from 'pinia-plugin-persistedstate'

export default defineNuxtPlugin(({ $pinia }) => {
  if (process.client) {
    $pinia.use(createPersistedState({
      storage: sessionStorage,
      auto: false // 只有明確設定的 store 才會被持久化
    }))
  }
})