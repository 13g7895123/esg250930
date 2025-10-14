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
      } else if (companyId && externalUserStore.latestAssignedQuestionId) {
        // 沒有 company_agent 群組：導到內容頁面（使用最新被指派的題項ID）
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
  const showQuestionManagementDebugInfo = () => {
    const isWebPath = route.path.startsWith('/web')
    const hasCompanyAgent = externalUserStore.group?.includes('company_agent')
    const companyId = externalUserStore.companyId
    const latestAssignedQuestionId = externalUserStore.latestAssignedQuestionId
    const group = externalUserStore.group
    const externalId = externalUserStore.externalId
    const userId = externalUserStore.userId

    // 計算最終連結
    let finalHref = ''
    let reason = ''

    if (isWebPath) {
      if (hasCompanyAgent && companyId) {
        finalHref = `/web/risk-assessment/questions/${companyId}/management`
        reason = '在 /web 路徑下，有 company_agent 群組，導到管理頁面'
      } else if (companyId && latestAssignedQuestionId) {
        finalHref = `/web/risk-assessment/questions/${companyId}/management/${latestAssignedQuestionId}/content`
        reason = '在 /web 路徑下，沒有 company_agent 群組，導到最新被指派題項的內容頁面'
      } else {
        finalHref = companyId ? `/web/risk-assessment/questions/${companyId}/management` : '/admin/risk-assessment/questions'
        reason = '在 /web 路徑下，缺少必要資料，使用 fallback 連結'
      }
    } else {
      if (hasCompanyAgent && companyId) {
        finalHref = `/web/risk-assessment/questions/${companyId}/management`
        reason = '在 /admin 路徑下，有 company_agent 群組'
      } else {
        finalHref = '/admin/risk-assessment/questions'
        reason = '在 /admin 路徑下，沒有 company_agent 群組'
      }
    }

    const debugInfo = {
      '當前路徑': route.path,
      '是否為 /web 路徑': isWebPath,
      '使用者群組': group,
      '是否有 company_agent 群組': hasCompanyAgent,
      '內部公司ID (companyId)': companyId,
      '外部用戶ID (externalId)': externalId,
      '內部用戶ID (userId)': userId,
      '最新被指派題項ID': latestAssignedQuestionId,
      '判斷原因': reason,
      '最終導航連結': finalHref
    }

    console.log('=== 題項管理連結判斷資料 ===')
    console.table(debugInfo)
    console.log('詳細資料:', debugInfo)

    // 使用 alert 顯示簡化版資訊
    alert(
      `題項管理連結判斷資料：\n\n` +
      `當前路徑: ${route.path}\n` +
      `是否為 /web 路徑: ${isWebPath}\n` +
      `使用者群組: ${JSON.stringify(group)}\n` +
      `是否有 company_agent: ${hasCompanyAgent}\n` +
      `內部公司ID: ${companyId}\n` +
      `內部用戶ID: ${userId}\n` +
      `最新被指派題項ID: ${latestAssignedQuestionId}\n\n` +
      `判斷原因:\n${reason}\n\n` +
      `最終導航連結:\n${finalHref}\n\n` +
      `詳細資料請查看 Console`
    )

    return { debugInfo, finalHref }
  }

  return {
    showFootbar,
    sidebarMenuItems,
    toggleFootbar,
    showQuestionManagementDebugInfo
  }
})