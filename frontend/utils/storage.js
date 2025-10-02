/**
 * SSR-safe localStorage wrapper
 * Provides safe access to localStorage that works in both client and server contexts
 */

export const storage = {
  /**
   * Get item from localStorage
   * @param {string} key - Storage key
   * @returns {string|null} - Stored value or null
   */
  getItem(key) {
    if (!process.client) return null
    try {
      return localStorage.getItem(key)
    } catch (error) {
      console.error('Storage getItem error:', error)
      return null
    }
  },

  /**
   * Set item in localStorage
   * @param {string} key - Storage key
   * @param {string} value - Value to store
   * @returns {boolean} - Success status
   */
  setItem(key, value) {
    if (!process.client) return false
    try {
      localStorage.setItem(key, value)
      return true
    } catch (error) {
      console.error('Storage setItem error:', error)
      return false
    }
  },

  /**
   * Remove item from localStorage
   * @param {string} key - Storage key
   * @returns {boolean} - Success status
   */
  removeItem(key) {
    if (!process.client) return false
    try {
      localStorage.removeItem(key)
      return true
    } catch (error) {
      console.error('Storage removeItem error:', error)
      return false
    }
  },

  /**
   * Clear all items from localStorage
   * @returns {boolean} - Success status
   */
  clear() {
    if (!process.client) return false
    try {
      localStorage.clear()
      return true
    } catch (error) {
      console.error('Storage clear error:', error)
      return false
    }
  },

  /**
   * Check if localStorage is available
   * @returns {boolean} - Availability status
   */
  isAvailable() {
    return process.client && typeof localStorage !== 'undefined'
  }
}
