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

  return {
    showFootbar,
    sidebarMenuItems,
    toggleFootbar
  }
})