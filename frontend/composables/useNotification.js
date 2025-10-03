/**
 * 統一的通知系統 Composable
 * 封裝 SweetAlert2 功能，提供一致的 API 介面
 *
 * @example
 * const { showSuccess, showError, showWarning, showConfirm } = useNotification()
 *
 * // 顯示成功訊息
 * await showSuccess('儲存成功', '資料已成功儲存')
 *
 * // 顯示錯誤訊息
 * await showError('儲存失敗', '請稍後再試')
 *
 * // 顯示確認對話框
 * const result = await showConfirm('確認刪除', '此操作無法復原')
 * if (result.isConfirmed) {
 *   // 執行刪除
 * }
 */
export const useNotification = () => {
  const { $notify } = useNuxtApp()

  /**
   * 顯示成功訊息
   * @param {string} message - 訊息內容（從後端回傳）
   * @returns {Promise}
   */
  const showSuccess = (message = '操作成功') => {
    return $notify.success(message)
  }

  /**
   * 顯示錯誤訊息
   * @param {string} message - 錯誤訊息（從後端回傳）
   * @returns {Promise}
   */
  const showError = (message = '操作失敗') => {
    return $notify.error(message)
  }

  /**
   * 顯示警告訊息
   * @param {string} message - 警告訊息（從後端回傳）
   * @returns {Promise}
   */
  const showWarning = (message = '請注意') => {
    return $notify.warning(message)
  }

  /**
   * 顯示提示訊息
   * @param {string} message - 提示訊息（從後端回傳）
   * @returns {Promise}
   */
  const showInfo = (message = '提示訊息') => {
    return $notify.info(message)
  }

  /**
   * 顯示確認對話框
   * @param {string} title - 標題（選填，預設：確認）
   * @param {string} message - 確認訊息（選填）
   * @param {string} confirmText - 確認按鈕文字（選填，預設：確認）
   * @param {string} cancelText - 取消按鈕文字（選填，預設：取消）
   * @returns {Promise<{isConfirmed: boolean, isDenied: boolean, isDismissed: boolean}>}
   */
  const showConfirm = (title = '確認', message = '', confirmText = '確認', cancelText = '取消') => {
    return $notify.confirm(title, message, confirmText, cancelText)
  }

  /**
   * 顯示載入中對話框
   * @param {string} title - 標題（選填，預設：處理中...）
   * @param {string} message - 訊息（選填，預設：請稍候）
   * @returns {Promise}
   */
  const showLoading = (title = '處理中...', message = '請稍候') => {
    return $notify.loading(title, message)
  }

  /**
   * 關閉所有對話框
   * @returns {Promise}
   */
  const closeAll = () => {
    return $notify.close()
  }

  /**
   * 顯示輸入對話框
   * @param {string} title - 標題（選填，預設：請輸入）
   * @param {string} message - 提示訊息（選填）
   * @param {string} inputType - 輸入類型（選填，預設：text）
   * @param {string} placeholder - 佔位符（選填）
   * @returns {Promise<{isConfirmed: boolean, value: string}>}
   */
  const showInput = (title = '請輸入', message = '', inputType = 'text', placeholder = '') => {
    return $notify.input(title, message, inputType, placeholder)
  }

  /**
   * Toast 通知（右上角小通知）
   */
  const toast = {
    success: (title = '成功！') => $notify.toast.success(title),
    error: (title = '錯誤！') => $notify.toast.error(title),
    warning: (title = '注意！') => $notify.toast.warning(title),
    info: (title = '提示') => $notify.toast.info(title)
  }

  /**
   * 執行非同步操作並顯示載入與結果提示
   * @param {Function} asyncFn - 要執行的非同步函數
   * @param {Object} options - 選項
   * @param {string} options.loadingMessage - 載入訊息（預設：處理中...）
   * @param {string} options.successMessage - 成功訊息（預設：操作成功）
   * @param {string} options.errorMessage - 錯誤訊息（預設：操作失敗）
   * @param {boolean} options.showSuccessNotification - 是否顯示成功通知（預設：true）
   * @param {boolean} options.showErrorNotification - 是否顯示錯誤通知（預設：true）
   * @returns {Promise} 返回非同步函數的結果
   *
   * @example
   * const { executeWithNotification } = useNotification()
   *
   * await executeWithNotification(
   *   async () => await apiClient.templates.delete(id),
   *   {
   *     loadingMessage: '刪除中...',
   *     successMessage: '範本已成功刪除',
   *     errorMessage: '刪除範本時發生錯誤'
   *   }
   * )
   */
  const executeWithNotification = async (asyncFn, options = {}) => {
    const {
      loadingMessage = '處理中...',
      successMessage = '操作成功',
      errorMessage = '操作失敗',
      showSuccessNotification = true,
      showErrorNotification = true
    } = options

    try {
      // 顯示載入中對話框
      showLoading('系統提示', loadingMessage)

      // 執行非同步操作
      const result = await asyncFn()

      // 關閉載入對話框
      closeAll()

      // 顯示成功提示
      if (showSuccessNotification) {
        await showSuccess(successMessage)
      }

      return result
    } catch (error) {
      // 關閉載入對話框
      closeAll()

      // 顯示錯誤提示
      if (showErrorNotification) {
        const message = error?.response?.data?.message || error?.message || errorMessage
        await showError(message)
      }

      throw error
    }
  }

  return {
    showSuccess,
    showError,
    showWarning,
    showInfo,
    showConfirm,
    showLoading,
    closeAll,
    showInput,
    toast,
    executeWithNotification
  }
}
