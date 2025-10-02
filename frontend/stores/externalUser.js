export const useExternalUserStore = defineStore('externalUser', () => {
  // 外部用戶資訊
  const userInfo = ref(null)
  const token = ref(null)
  const isLoaded = ref(false)
  const lastUpdated = ref(null)

  // Computed properties
  const hasUserInfo = computed(() => !!userInfo.value)
  const userName = computed(() => {
    // 從 userInfo.data.user_name 取得用戶名稱
    return userInfo.value?.data?.user_name || userInfo.value?.name || userInfo.value?.username || '未知用戶'
  })
  const externalId = computed(() => {
    // 從 userInfo.data 取得外部用戶ID（原本的userId邏輯）
    return userInfo.value?.data?.user_id || userInfo.value?.data?.id || userInfo.value?.id || null
  })
  const userEmail = computed(() => {
    // 從 userInfo.data 或直接從 userInfo 取得 email
    return userInfo.value?.data?.email || userInfo.value?.email || null
  })
  const comId = computed(() => {
    // 從 userInfo.data 取得公司ID
    return userInfo.value?.data?.com_id || null
  })

  // 新的 userId - 通過 externalId 查詢 external_personnel 表獲得的內部ID
  const internalUserId = ref(null)

  // userId getter - 回傳內部用戶ID
  const userId = computed(() => {
    return internalUserId.value
  })

  // 查詢 external_personnel 表獲取內部用戶ID
  const fetchInternalUserId = async (extId) => {
    if (!extId) return null

    try {
      console.log('=== 查詢內部用戶ID ===')
      console.log('External ID:', extId)

      // 調用後端API查詢 external_personnel 表
      const response = await $fetch('/api/v1/external-personnel/find-by-external-id', {
        method: 'POST',
        body: { external_id: extId }
      })

      if (response.success && response.data) {
        const internalId = response.data.id
        console.log('✅ 查詢到內部用戶ID:', internalId)
        return internalId
      } else {
        console.log('⚠️ 未找到對應的內部用戶ID，嘗試同步人員資料...')

        // 如果找不到用戶，嘗試同步人員資料
        const currentComId = comId.value
        if (currentComId) {
          console.log('📥 開始同步人員資料，Company ID:', currentComId)

          try {
            // 調用人員同步API
            const syncResponse = await $fetch(`/api/v1/personnel/companies/${currentComId}/sync`, {
              method: 'POST'
            })

            if (syncResponse.success) {
              console.log('✅ 人員資料同步成功:', syncResponse.message)

              // 同步成功後，重新查詢一次
              console.log('🔄 重新查詢內部用戶ID...')
              const retryResponse = await $fetch('/api/v1/external-personnel/find-by-external-id', {
                method: 'POST',
                body: { external_id: extId }
              })

              if (retryResponse.success && retryResponse.data) {
                const internalId = retryResponse.data.id
                console.log('✅ 同步後查詢到內部用戶ID:', internalId)
                return internalId
              } else {
                console.log('❌ 同步後仍未找到用戶ID')
                return null
              }
            } else {
              console.error('❌ 人員資料同步失敗:', syncResponse.message)
              return null
            }
          } catch (syncError) {
            console.error('❌ 調用人員同步API失敗:', syncError)
            return null
          }
        } else {
          console.log('❌ 缺少 comId，無法執行人員同步')
          return null
        }
      }

    } catch (error) {
      console.error('❌ 查詢內部用戶ID失敗:', error)
      return null
    }
  }

  // 設定用戶資訊
  const setUserInfo = async (data, tokenValue = null) => {
    userInfo.value = data
    if (tokenValue) {
      token.value = tokenValue
    }
    isLoaded.value = true
    lastUpdated.value = new Date().toISOString()

    console.log('=== 用戶資訊已儲存到 Pinia Store ===')
    console.log('原始用戶資訊:', data)
    console.log('解析後用戶名稱 (data.user_name):', data?.data?.user_name)
    console.log('解析後外部用戶ID (externalId):', externalId.value)
    console.log('解析後公司ID (comId):', comId.value)
    console.log('Token:', tokenValue)

    // 查詢並設置內部用戶ID
    if (externalId.value) {
      const internalId = await fetchInternalUserId(externalId.value)
      internalUserId.value = internalId
      console.log('設置內部用戶ID (userId):', userId.value)
    }

    console.log('最後更新:', lastUpdated.value)
  }

  // 清除用戶資訊
  const clearUserInfo = () => {
    userInfo.value = null
    token.value = null
    internalUserId.value = null
    isLoaded.value = false
    lastUpdated.value = null

    console.log('=== 用戶資訊已清除 ===')
  }

  // 從外部 API 獲取用戶資訊
  const fetchExternalUserData = async (apiToken) => {
    if (!apiToken) {
      console.log('=== 無 Token，無法獲取外部用戶資訊 ===')
      return null
    }

    try {
      console.log('=== 開始從外部 API 獲取用戶資訊 ===')

      const apiUrl = 'https://csr.cc-sustain.com/admin/api/user/user_data_decrypt'

      // 使用 FormData 以便後端可以使用 $_POST 接收
      const formData = new FormData()
      formData.append('token', apiToken)

      const response = await $fetch(apiUrl, {
        method: 'POST',
        body: formData
      })

      console.log('=== 外部 API 調用成功 ===')
      console.log('API 回傳結果:', response)

      // 儲存到 store
      setUserInfo(response, apiToken)

      return response

    } catch (error) {
      console.error('=== 外部 API 調用失敗，嘗試備用方法 ===')
      console.error('錯誤詳情:', error)

      try {
        // 備用方法：使用原生 fetch API
        const formData = new FormData()
        formData.append('token', apiToken)

        const fetchResponse = await fetch('https://csr.cc-sustain.com/admin/api/user/user_data_decrypt', {
          method: 'POST',
          body: formData
        })

        if (fetchResponse.ok) {
          const responseText = await fetchResponse.text()
          let responseData

          try {
            responseData = JSON.parse(responseText)
          } catch (jsonError) {
            responseData = responseText
          }

          console.log('=== 備用方法調用成功 ===')
          console.log('API 回傳結果:', responseData)

          // 儲存到 store
          setUserInfo(responseData, apiToken)

          return responseData
        } else {
          throw new Error(`HTTP ${fetchResponse.status}: ${fetchResponse.statusText}`)
        }

      } catch (error2) {
        console.error('=== 所有方法都失敗 ===')
        console.error('錯誤詳情:', error2)
        return null
      }
    }
  }

  // 更新用戶資訊（不重新調用 API）
  const updateUserInfo = (newData) => {
    if (userInfo.value) {
      userInfo.value = { ...userInfo.value, ...newData }
      lastUpdated.value = new Date().toISOString()

      console.log('=== 用戶資訊已更新 ===')
      console.log('更新後資訊:', userInfo.value)
    }
  }

  return {
    // 狀態
    userInfo: readonly(userInfo),
    token: readonly(token),
    isLoaded: readonly(isLoaded),
    lastUpdated: readonly(lastUpdated),

    // Computed (getters)
    hasUserInfo,
    userName,
    userEmail,
    comId,
    externalId,
    userId, // userId 現在是 computed getter

    // 方法
    setUserInfo,
    clearUserInfo,
    fetchExternalUserData,
    fetchInternalUserId,
    updateUserInfo
  }
}, {
  persist: {
    storage: sessionStorage, // 使用 sessionStorage（當前瀏覽器分頁有效）
    pick: ['userInfo', 'token', 'internalUserId', 'isLoaded', 'lastUpdated'] // 持久化用戶相關字段
  }
})