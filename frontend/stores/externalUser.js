export const useExternalUserStore = defineStore('externalUser', () => {
  // 外部用戶資訊
  const userInfo = ref(null)
  const token = ref(null)
  const isLoaded = ref(false)
  const lastUpdated = ref(null)

  // Computed properties
  const hasUserInfo = computed(() => !!userInfo.value)
  const userName = computed(() => {
    // 優先從 userInfo.user.user_name 取得用戶名稱，fallback 到其他路徑
    return userInfo.value?.user?.user_name ||
           userInfo.value?.data?.user_name ||
           userInfo.value?.name ||
           userInfo.value?.username ||
           '未知用戶'
  })
  const externalId = computed(() => {
    // 優先從 userInfo.user.user_id 取得外部用戶ID，fallback 到其他路徑
    return userInfo.value?.user?.user_id ||
           userInfo.value?.data?.user_id ||
           userInfo.value?.user?.id ||
           userInfo.value?.data?.id ||
           userInfo.value?.id ||
           null
  })
  const userEmail = computed(() => {
    // 優先從 userInfo.user.email 取得 email，fallback 到其他路徑
    return userInfo.value?.user?.email ||
           userInfo.value?.data?.email ||
           userInfo.value?.email ||
           null
  })
  const externalCompanyId = computed(() => {
    // 優先從 userInfo.user.com_id 取得外部公司ID，fallback 到其他路徑
    return userInfo.value?.user?.com_id ||
           userInfo.value?.data?.com_id ||
           null
  })
  const group = computed(() => {
    // 從 userInfo 中取得 group 陣列，並提取每筆資料的 name
    const groupData = userInfo.value?.user?.group ||
                      userInfo.value?.data?.group ||
                      userInfo.value?.group ||
                      []

    // 確保 groupData 是陣列，並提取每個項目的 name
    if (Array.isArray(groupData)) {
      return groupData.map(item => item?.name).filter(name => name != null)
    }

    return []
  })

  // 新的 userId - 通過 externalId 查詢 external_personnel 表獲得的內部ID
  const internalUserId = ref(null)

  // userId getter - 回傳內部用戶ID
  const userId = computed(() => {
    return internalUserId.value
  })

  // 新的 companyId - 通過 externalCompanyId 查詢 local_companies 表獲得的內部ID
  const internalCompanyId = ref(null)

  // companyId getter - 回傳內部公司ID
  const companyId = computed(() => {
    return internalCompanyId.value
  })

  // 最新被指派的題項ID
  const latestAssignedQuestionId = ref(null)

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
        const currentExternalCompanyId = externalCompanyId.value
        if (currentExternalCompanyId) {
          console.log('📥 開始同步人員資料，External Company ID:', currentExternalCompanyId)

          try {
            // 調用人員同步API
            const syncResponse = await $fetch(`/api/v1/personnel/companies/${currentExternalCompanyId}/sync`, {
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
          console.log('❌ 缺少 externalCompanyId，無法執行人員同步')
          return null
        }
      }

    } catch (error) {
      console.error('❌ 查詢內部用戶ID失敗:', error)
      return null
    }
  }

  // 查詢 local_companies 表獲取內部公司ID
  const fetchInternalCompanyId = async (extCompanyId) => {
    if (!extCompanyId) return null

    try {
      console.log('=== 查詢內部公司ID ===')
      console.log('External Company ID:', extCompanyId)

      // 調用後端API查詢 local_companies 表
      const response = await $fetch('/api/v1/local-companies/find-by-external-id', {
        method: 'POST',
        body: { external_company_id: extCompanyId }
      })

      if (response.success && response.data) {
        const internalId = response.data.id
        console.log('✅ 查詢到內部公司ID:', internalId)
        return internalId
      } else {
        console.log('⚠️ 未找到對應的內部公司ID')
        return null
      }

    } catch (error) {
      console.error('❌ 查詢內部公司ID失敗:', error)
      return null
    }
  }

  // 查詢使用者被指派的最新年度題項ID
  const fetchLatestAssignedQuestion = async () => {
    // 需要有 userId 和 companyId
    if (!internalUserId.value || !internalCompanyId.value) {
      console.log('❌ 缺少 userId 或 companyId，無法查詢最新被指派題項')
      return null
    }

    try {
      console.log('=== 查詢最新被指派題項 ===')
      console.log('User ID:', internalUserId.value)
      console.log('Company ID:', internalCompanyId.value)

      // 調用後端API查詢使用者被指派的最新年度題項
      const response = await $fetch('/api/v1/personnel-assignments/latest-assigned-question', {
        method: 'POST',
        body: {
          user_id: internalUserId.value,
          company_id: internalCompanyId.value
        }
      })

      if (response.success && response.data) {
        const questionId = response.data.question_id
        console.log('✅ 查詢到最新被指派題項ID:', questionId)
        return questionId
      } else {
        console.log('⚠️ 未找到被指派的題項')
        return null
      }

    } catch (error) {
      console.error('❌ 查詢最新被指派題項失敗:', error)
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
    console.log('原始用戶資訊:', JSON.stringify(data, null, 2))
    console.log('')
    console.log('=== 資料結構檢測 ===')
    console.log('userInfo.user:', data?.user)
    console.log('userInfo.data:', data?.data)
    console.log('')
    console.log('=== 路徑測試 (user 路徑) ===')
    console.log('userInfo.user.user_name:', data?.user?.user_name)
    console.log('userInfo.user.email:', data?.user?.email)
    console.log('userInfo.user.user_id:', data?.user?.user_id)
    console.log('userInfo.user.com_id:', data?.user?.com_id)
    console.log('')
    console.log('=== 路徑測試 (data 路徑) ===')
    console.log('userInfo.data.user_name:', data?.data?.user_name)
    console.log('userInfo.data.email:', data?.data?.email)
    console.log('userInfo.data.user_id:', data?.data?.user_id)
    console.log('userInfo.data.com_id:', data?.data?.com_id)
    console.log('')
    console.log('=== Computed 結果 ===')
    console.log('userName (computed):', userName.value)
    console.log('userEmail (computed):', userEmail.value)
    console.log('externalId (computed):', externalId.value)
    console.log('externalCompanyId (computed):', externalCompanyId.value)
    console.log('group (computed):', group.value)
    console.log('Token:', tokenValue)

    // 查詢並設置內部用戶ID
    if (externalId.value) {
      try {
        const internalId = await fetchInternalUserId(externalId.value)
        internalUserId.value = internalId
        console.log('設置內部用戶ID (userId):', userId.value)
      } catch (error) {
        console.error('查詢內部用戶ID時發生錯誤，但不影響頁面載入:', error)
        // 不拋出錯誤，允許頁面繼續載入
      }
    }

    // 查詢並設置內部公司ID
    if (externalCompanyId.value) {
      try {
        const internalId = await fetchInternalCompanyId(externalCompanyId.value)
        internalCompanyId.value = internalId
        console.log('設置內部公司ID (companyId):', companyId.value)
      } catch (error) {
        console.error('查詢內部公司ID時發生錯誤，但不影響頁面載入:', error)
        // 不拋出錯誤，允許頁面繼續載入
      }
    }

    // 查詢並設置最新被指派的題項ID
    if (internalUserId.value && internalCompanyId.value) {
      try {
        const questionId = await fetchLatestAssignedQuestion()
        latestAssignedQuestionId.value = questionId
        console.log('設置最新被指派題項ID (latestAssignedQuestionId):', latestAssignedQuestionId.value)
      } catch (error) {
        console.error('查詢最新被指派題項時發生錯誤，但不影響頁面載入:', error)
        // 不拋出錯誤，允許頁面繼續載入
      }
    }

    console.log('最後更新:', lastUpdated.value)
  }

  // 清除用戶資訊
  const clearUserInfo = () => {
    userInfo.value = null
    token.value = null
    internalUserId.value = null
    internalCompanyId.value = null
    latestAssignedQuestionId.value = null
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
    externalCompanyId, // 外部公司ID（從 token 解密後的 com_id）
    companyId, // 內部公司ID（查詢 local_companies 表獲得）
    externalId, // 外部用戶ID（從 token 解密後的 user_id）
    userId, // 內部用戶ID（查詢 external_personnel 表獲得）
    group, // 用戶所屬群組名稱陣列
    latestAssignedQuestionId: readonly(latestAssignedQuestionId), // 最新被指派的題項ID

    // 方法
    setUserInfo,
    clearUserInfo,
    fetchExternalUserData,
    fetchInternalUserId,
    fetchInternalCompanyId,
    fetchLatestAssignedQuestion,
    updateUserInfo
  }
}, {
  persist: {
    storage: typeof window !== 'undefined' ? sessionStorage : undefined, // 使用 sessionStorage（當前瀏覽器分頁有效）
    pick: ['userInfo', 'token', 'internalUserId', 'internalCompanyId', 'latestAssignedQuestionId', 'isLoaded', 'lastUpdated'] // 持久化用戶相關字段
  }
})