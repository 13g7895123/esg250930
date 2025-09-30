import Swal from 'sweetalert2'

export default defineNuxtPlugin(() => {
  // 配置預設的 SweetAlert 設定
  const swalDefaults = {
    confirmButtonColor: '#059669', // 綠色，配合系統主色調
    cancelButtonColor: '#6b7280',  // 灰色
    confirmButtonText: '確認',
    cancelButtonText: '取消',
    reverseButtons: true, // 讓取消按鈕在左邊，確認在右邊
  }

  // 建立客製化的 SweetAlert 實例
  const $swal = Swal.mixin(swalDefaults)

  // 定義常用的通知方法
  const notification = {
    // 成功通知
    success: (title = '成功！', text = '') => {
      return $swal.fire({
        icon: 'success',
        title,
        text,
        showConfirmButton: true,
        timer: 3000,
        timerProgressBar: true
      })
    },

    // 錯誤通知
    error: (title = '錯誤！', text = '') => {
      return $swal.fire({
        icon: 'error',
        title,
        html: text, // 使用 html 而不是 text 以支援 HTML 格式
        showConfirmButton: true
      })
    },

    // 警告通知
    warning: (title = '注意！', text = '') => {
      return $swal.fire({
        icon: 'warning',
        title,
        html: text, // 使用 html 而不是 text 以支援 HTML 格式
        showConfirmButton: true
      })
    },

    // 訊息通知
    info: (title = '提示', text = '') => {
      return $swal.fire({
        icon: 'info',
        title,
        html: text, // 使用 html 而不是 text 以支援 HTML 格式
        showConfirmButton: true
      })
    },

    // 確認對話框
    confirm: (title = '確認', text = '', confirmText = '確認', cancelText = '取消') => {
      return $swal.fire({
        icon: 'info',
        title,
        html: text, // 使用 html 而不是 text 以支援 HTML 格式
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText
      })
    },

    // 載入中對話框
    loading: (title = '處理中...', text = '請稍候') => {
      return $swal.fire({
        title,
        text,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading()
        }
      })
    },

    // 關閉所有對話框
    close: () => {
      return $swal.close()
    },

    // 表單輸入對話框
    input: (title = '請輸入', text = '', inputType = 'text', inputPlaceholder = '') => {
      return $swal.fire({
        title,
        text,
        input: inputType,
        inputPlaceholder,
        showCancelButton: true,
        inputValidator: (value) => {
          if (!value) {
            return '請輸入內容！'
          }
        }
      })
    },

    // Toast 通知（右上角小通知）
    toast: {
      success: (title = '成功！') => {
        return $swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title,
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        })
      },

      error: (title = '錯誤！') => {
        return $swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'error',
          title,
          showConfirmButton: false,
          timer: 4000,
          timerProgressBar: true
        })
      },

      warning: (title = '注意！') => {
        return $swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'warning',
          title,
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        })
      },

      info: (title = '提示') => {
        return $swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'info',
          title,
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        })
      }
    }
  }

  // 將 SweetAlert 和通知方法注入到 Nuxt 應用中
  return {
    provide: {
      swal: $swal,
      notify: notification
    }
  }
})