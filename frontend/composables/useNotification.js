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
   * @param {string} title - 標題（選填，預設：成功！）
   * @param {string} message - 訊息內容（選填）
   * @returns {Promise}
   */
  const showSuccess = (title = '成功！', message = '') => {
    return $notify.success(title, message)
  }

  /**
   * 顯示錯誤訊息
   * @param {string} title - 標題（選填，預設：錯誤！）
   * @param {string} message - 錯誤訊息（選填）
   * @returns {Promise}
   */
  const showError = (title = '錯誤！', message = '') => {
    return $notify.error(title, message)
  }

  /**
   * 顯示警告訊息
   * @param {string} title - 標題（選填，預設：注意！）
   * @param {string} message - 警告訊息（選填）
   * @returns {Promise}
   */
  const showWarning = (title = '注意！', message = '') => {
    return $notify.warning(title, message)
  }

  /**
   * 顯示提示訊息
   * @param {string} title - 標題（選填，預設：提示）
   * @param {string} message - 提示訊息（選填）
   * @returns {Promise}
   */
  const showInfo = (title = '提示', message = '') => {
    return $notify.info(title, message)
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

  return {
    showSuccess,
    showError,
    showWarning,
    showInfo,
    showConfirm,
    showLoading,
    closeAll,
    showInput,
    toast
  }
}
