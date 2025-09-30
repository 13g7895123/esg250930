/**
 * Website Settings API Composable
 * Provides methods for managing website settings
 */
export const useWebsiteSettingsApi = () => {
  // Remove runtime config usage for now to avoid SSR issues

  /**
   * Get current website settings
   */
  const getSettings = async () => {
    try {
      // In a real application, this would make an API call
      // For now, return mock data
      return {
        success: true,
        data: {
          siteName: 'ESG管理系統',
          siteDescription: '企業永續發展管理平台',
          logoUrl: null,
          primaryColor: '#7c3aed',
          secondaryColor: '#e5e7eb',
          language: 'zh-TW',
          timezone: 'Asia/Taipei',
          dateFormat: 'YYYY-MM-DD',
          emailNotifications: true,
          smsNotifications: false,
          maintenanceMode: false,
          googleAnalyticsId: '',
          customCss: '',
          footerText: '© 2024 ESG管理系統. All rights reserved.',
          contactEmail: 'admin@esg-system.com',
          supportPhone: '+886-2-1234-5678'
        }
      }
    } catch (error) {
      console.error('Failed to fetch website settings:', error)
      return {
        success: false,
        error: error.message
      }
    }
  }

  /**
   * Update website settings
   */
  const updateSettings = async (settings) => {
    try {
      // In a real application, this would make an API call
      // For now, simulate success
      await new Promise(resolve => setTimeout(resolve, 500))
      
      return {
        success: true,
        data: settings,
        message: '設定已成功更新'
      }
    } catch (error) {
      console.error('Failed to update website settings:', error)
      return {
        success: false,
        error: error.message
      }
    }
  }

  /**
   * Reset settings to default values
   */
  const resetSettings = async () => {
    try {
      const defaultSettings = {
        siteName: 'ESG管理系統',
        siteDescription: '企業永續發展管理平台',
        logoUrl: null,
        primaryColor: '#7c3aed',
        secondaryColor: '#e5e7eb',
        language: 'zh-TW',
        timezone: 'Asia/Taipei',
        dateFormat: 'YYYY-MM-DD',
        emailNotifications: true,
        smsNotifications: false,
        maintenanceMode: false,
        googleAnalyticsId: '',
        customCss: '',
        footerText: '© 2024 ESG管理系統. All rights reserved.',
        contactEmail: 'admin@esg-system.com',
        supportPhone: '+886-2-1234-5678'
      }

      return await updateSettings(defaultSettings)
    } catch (error) {
      console.error('Failed to reset website settings:', error)
      return {
        success: false,
        error: error.message
      }
    }
  }

  /**
   * Upload and update logo
   */
  const uploadLogo = async (file) => {
    try {
      // In a real application, this would upload the file and return the URL
      // For now, create a mock URL
      const mockUrl = URL.createObjectURL(file)
      
      return {
        success: true,
        data: {
          logoUrl: mockUrl
        },
        message: 'Logo已成功上傳'
      }
    } catch (error) {
      console.error('Failed to upload logo:', error)
      return {
        success: false,
        error: error.message
      }
    }
  }

  /**
   * Update document title based on settings
   */
  const updateDocumentTitle = (title) => {
    if (process.client && typeof document !== 'undefined') {
      if (title) {
        document.title = title
      } else {
        document.title = 'ESG管理系統'
      }
    }
  }

  /**
   * Validate settings before saving
   */
  const validateSettings = (settings) => {
    const errors = []

    if (!settings.siteName?.trim()) {
      errors.push('網站名稱不能為空')
    }

    if (settings.siteName?.length > 100) {
      errors.push('網站名稱不能超過100個字符')
    }

    if (settings.siteDescription?.length > 500) {
      errors.push('網站描述不能超過500個字符')
    }

    if (settings.contactEmail && !isValidEmail(settings.contactEmail)) {
      errors.push('聯絡郵箱格式不正確')
    }

    if (settings.supportPhone && !isValidPhone(settings.supportPhone)) {
      errors.push('客服電話格式不正確')
    }

    return {
      isValid: errors.length === 0,
      errors
    }
  }

  /**
   * Helper function to validate email
   */
  const isValidEmail = (email) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(email)
  }

  /**
   * Helper function to validate phone number
   */
  const isValidPhone = (phone) => {
    const phoneRegex = /^[\+]?[\d\s\-\(\)]+$/
    return phoneRegex.test(phone)
  }

  return {
    getSettings,
    updateSettings,
    resetSettings,
    uploadLogo,
    updateDocumentTitle,
    validateSettings
  }
}