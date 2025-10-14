export const useSettingsStore = defineStore('settings', () => {
  const route = useRoute()
  const externalUserStore = useExternalUserStore()

  const showFootbar = ref(true)

  // 動態計算側邊欄選單項目
  const sidebarMenuItems = computed(() => {
    // 檢查是否為 /web 路徑（domain 後面第一個參數是 web）
    const isWebPath = route.path.startsWith('/web')

    // 檢查是否有 company_agent 群組
    const hasCompanyAgent = externalUserStore.group?.includes('company_agent')

    // 取得內部公司ID
    const companyId = externalUserStore.companyId

    // 基本選單結構
    const menuItems = [
      {
        name: '風險評估表',
        icon: 'ClipboardDocumentListIcon',
        href: '/admin/risk-assessment',
        children: []
      }
    ]

    // 如果是 /web 路徑，不顯示範本管理
    if (!isWebPath) {
      menuItems[0].children.push({
        name: '範本管理',
        href: '/admin/risk-assessment/templates'
      })
    }

    // 題項管理項目
    let questionManagementItem = { name: '題項管理' }

    // 根據路徑、群組和資料決定連結
    if (isWebPath) {
      // 在 /web 路徑下
      if (hasCompanyAgent && companyId) {
        // 有 company_agent 群組：導到管理頁面
        questionManagementItem.href = `/web/risk-assessment/questions/${companyId}/management`
      } else if (externalUserStore.verifiedUrl) {
        // 沒有 company_agent 群組且有 verifiedUrl：優先使用 verify API 回傳的 URL
        questionManagementItem.href = externalUserStore.verifiedUrl
      } else if (companyId && externalUserStore.latestAssignedQuestionId) {
        // Fallback：使用最新被指派的題項ID
        questionManagementItem.href = `/web/risk-assessment/questions/${companyId}/management/${externalUserStore.latestAssignedQuestionId}/content`
      } else {
        // 沒有必要資料：暫時導到管理頁面
        questionManagementItem.href = companyId ? `/web/risk-assessment/questions/${companyId}/management` : '/admin/risk-assessment/questions'
      }
    } else {
      // 在 /admin 路徑下：保持原有邏輯
      if (hasCompanyAgent && companyId) {
        questionManagementItem.href = `/web/risk-assessment/questions/${companyId}/management`
      } else {
        questionManagementItem.href = '/admin/risk-assessment/questions'
      }
    }

    menuItems[0].children.push(questionManagementItem)

    return menuItems
  })

  const toggleFootbar = () => {
    showFootbar.value = !showFootbar.value
  }

  // 除錯方法：顯示題項管理連結的判斷資料
  const showQuestionManagementDebugInfo = async () => {
    const isWebPath = route.path.startsWith('/web')
    const hasCompanyAgent = externalUserStore.group?.includes('company_agent')
    const companyId = externalUserStore.companyId
    const latestAssignedQuestionId = externalUserStore.latestAssignedQuestionId
    const group = externalUserStore.group
    const externalCompanyId = externalUserStore.externalCompanyId
    const externalId = externalUserStore.externalId
    const userId = externalUserStore.userId

    console.log('=== 開始除錯題項管理連結 ===')

    // 調用 external-access verify API
    let verifyResult = null
    let verifyError = null

    if (externalCompanyId && externalId) {
      try {
        console.log(`調用 API: /api/v1/external-access/verify?company_id=${externalCompanyId}&user_id=${externalId}`)

        const response = await $fetch(`/api/v1/external-access/verify?company_id=${externalCompanyId}&user_id=${externalId}`)
        verifyResult = response

        console.log('✅ API 調用成功:', verifyResult)
      } catch (error) {
        verifyError = error
        console.error('❌ API 調用失敗:', error)
      }
    } else {
      console.warn('⚠️ 缺少 externalCompanyId 或 externalId，無法調用 verify API')
    }

    // 計算本地邏輯的連結（作為 fallback）
    let localHref = ''
    let localReason = ''

    if (isWebPath) {
      if (hasCompanyAgent && companyId) {
        localHref = `/web/risk-assessment/questions/${companyId}/management`
        localReason = '在 /web 路徑下，有 company_agent 群組，導到管理頁面'
      } else if (companyId && latestAssignedQuestionId) {
        localHref = `/web/risk-assessment/questions/${companyId}/management/${latestAssignedQuestionId}/content`
        localReason = '在 /web 路徑下，沒有 company_agent 群組，導到最新被指派題項的內容頁面'
      } else {
        localHref = companyId ? `/web/risk-assessment/questions/${companyId}/management` : '/admin/risk-assessment/questions'
        localReason = '在 /web 路徑下，缺少必要資料，使用 fallback 連結'
      }
    } else {
      if (hasCompanyAgent && companyId) {
        localHref = `/web/risk-assessment/questions/${companyId}/management`
        localReason = '在 /admin 路徑下，有 company_agent 群組'
      } else {
        localHref = '/admin/risk-assessment/questions'
        localReason = '在 /admin 路徑下，沒有 company_agent 群組'
      }
    }

    // 決定最終使用的連結：優先使用 API 回傳的 url
    let finalHref = ''
    let reason = ''

    if (verifyResult && verifyResult.success && verifyResult.url) {
      finalHref = verifyResult.url
      reason = '✅ 使用 Verify API 回傳的 URL'
      console.log('🎯 優先使用 API 回傳的 URL:', finalHref)
    } else {
      finalHref = localHref
      reason = `⚙️ 使用本地計算的 URL（${localReason}）`
      console.log('🎯 使用本地計算的 URL:', finalHref)
    }

    const debugInfo = {
      '當前路徑': route.path,
      '是否為 /web 路徑': isWebPath,
      '使用者群組': group,
      '是否有 company_agent 群組': hasCompanyAgent,
      '外部公司ID (externalCompanyId)': externalCompanyId,
      '外部用戶ID (externalId)': externalId,
      '內部公司ID (companyId)': companyId,
      '內部用戶ID (userId)': userId,
      '最新被指派題項ID': latestAssignedQuestionId,
      '本地計算連結': localHref,
      '本地判斷原因': localReason,
      'API 回傳連結': verifyResult?.url || 'N/A',
      '最終使用連結': finalHref,
      '最終判斷原因': reason
    }

    console.log('=== 題項管理連結判斷資料 ===')
    console.table(debugInfo)
    console.log('詳細資料:', debugInfo)

    if (verifyResult) {
      console.log('=== Verify API 回傳結果 ===')
      console.log(verifyResult)
    }

    // 建立顯示訊息
    let alertMessage = `題項管理連結判斷資料：\n\n` +
      `當前路徑: ${route.path}\n` +
      `是否為 /web 路徑: ${isWebPath}\n` +
      `使用者群組: ${JSON.stringify(group)}\n` +
      `是否有 company_agent: ${hasCompanyAgent}\n\n` +
      `外部公司ID: ${externalCompanyId}\n` +
      `外部用戶ID: ${externalId}\n` +
      `內部公司ID: ${companyId}\n` +
      `內部用戶ID: ${userId}\n` +
      `最新被指派題項ID: ${latestAssignedQuestionId}\n\n`

    // 添加 API 調用結果
    if (verifyResult) {
      alertMessage += `✅ Verify API 調用成功\n`
      alertMessage += `API 授權狀態: ${verifyResult.is_authorized ? '✓ 已授權' : '✗ 未授權'}\n`
      if (verifyResult.url) {
        alertMessage += `API 回傳 URL: ${verifyResult.url}\n`
      }
      alertMessage += `完整回傳: ${JSON.stringify(verifyResult, null, 2)}\n\n`
    } else if (verifyError) {
      alertMessage += `❌ Verify API 調用失敗\n`
      alertMessage += `錯誤: ${verifyError.message || verifyError}\n\n`
    } else {
      alertMessage += `⚠️ 未調用 Verify API（缺少必要參數）\n\n`
    }

    // 添加本地計算結果
    alertMessage += `本地計算連結: ${localHref}\n`
    alertMessage += `本地判斷原因: ${localReason}\n\n`

    // 添加最終決定
    alertMessage += `━━━━━━━━━━━━━━━━━━━━\n`
    alertMessage += `${reason}\n`
    alertMessage += `最終導航連結:\n${finalHref}\n`
    alertMessage += `━━━━━━━━━━━━━━━━━━━━\n\n`
    alertMessage += `詳細資料請查看 Console`

    // 使用 alert 顯示
    alert(alertMessage)

    return { debugInfo, finalHref, verifyResult }
  }

  return {
    showFootbar,
    sidebarMenuItems,
    toggleFootbar,
    showQuestionManagementDebugInfo
  }
})