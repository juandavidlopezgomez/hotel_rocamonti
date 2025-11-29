import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null)
  const token = ref(localStorage.getItem('admin_token') || null)
  const permissions = ref([])
  const roles = ref([])
  const loading = ref(false)
  const error = ref(null)

  // Getters
  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => roles.value.includes('Super Admin'))
  const isGerente = computed(() => roles.value.includes('Gerente'))
  const isRecepcionista = computed(() => roles.value.includes('Recepcionista'))
  const isHousekeeping = computed(() => roles.value.includes('Housekeeping'))

  // Actions
  async function login(credentials) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post(`${API_URL}/admin/login`, credentials)

      if (response.data.success) {
        user.value = response.data.user
        token.value = response.data.token
        permissions.value = response.data.permissions || []
        roles.value = response.data.roles || []

        localStorage.setItem('admin_token', response.data.token)
        localStorage.setItem('admin_user', JSON.stringify(response.data.user))

        // Configurar axios para incluir token en todas las peticiones
        axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`

        return { success: true }
      } else {
        error.value = response.data.message || 'Error al iniciar sesión'
        return { success: false, error: error.value }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error de conexión'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await axios.post(`${API_URL}/admin/logout`)
    } catch (err) {
      console.error('Error al cerrar sesión:', err)
    } finally {
      user.value = null
      token.value = null
      permissions.value = []
      roles.value = []

      localStorage.removeItem('admin_token')
      localStorage.removeItem('admin_user')

      delete axios.defaults.headers.common['Authorization']
    }
  }

  async function checkAuth() {
    const savedToken = localStorage.getItem('admin_token')
    const savedUser = localStorage.getItem('admin_user')

    if (!savedToken) {
      return false
    }

    try {
      token.value = savedToken
      user.value = savedUser ? JSON.parse(savedUser) : null

      axios.defaults.headers.common['Authorization'] = `Bearer ${savedToken}`

      // Verificar que el token sea válido
      const response = await axios.get(`${API_URL}/admin/me`)

      if (response.data.success) {
        user.value = response.data.user
        permissions.value = response.data.permissions || []
        roles.value = response.data.roles || []
        return true
      } else {
        await logout()
        return false
      }
    } catch (err) {
      await logout()
      return false
    }
  }

  function hasPermission(permission) {
    if (isAdmin.value) return true
    return permissions.value.includes(permission)
  }

  function hasAnyPermission(permissionsList) {
    if (isAdmin.value) return true
    return permissionsList.some(p => permissions.value.includes(p))
  }

  function hasAllPermissions(permissionsList) {
    if (isAdmin.value) return true
    return permissionsList.every(p => permissions.value.includes(p))
  }

  function hasRole(role) {
    return roles.value.includes(role)
  }

  function hasAnyRole(rolesList) {
    return rolesList.some(r => roles.value.includes(r))
  }

  return {
    // State
    user,
    token,
    permissions,
    roles,
    loading,
    error,

    // Getters
    isAuthenticated,
    isAdmin,
    isGerente,
    isRecepcionista,
    isHousekeeping,

    // Actions
    login,
    logout,
    checkAuth,
    hasPermission,
    hasAnyPermission,
    hasAllPermissions,
    hasRole,
    hasAnyRole
  }
})
