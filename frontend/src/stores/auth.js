import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/api/client'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('token') || '')
  const user = ref(JSON.parse(localStorage.getItem('user') || 'null'))
  const company = ref(JSON.parse(localStorage.getItem('company') || 'null'))

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.role === 'admin' || user.value?.role === 'super_admin')
  const isSuperAdmin = computed(() => user.value?.role === 'super_admin')

  function setSession(newToken, newUser) {
    token.value = newToken
    user.value = newUser
    company.value = newUser?.company || null
    localStorage.setItem('token', newToken)
    localStorage.setItem('user', JSON.stringify(newUser))
    localStorage.setItem('company', JSON.stringify(company.value))
  }

  function clearSession() {
    token.value = ''
    user.value = null
    company.value = null
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    localStorage.removeItem('company')
  }

  async function login(email, password) {
    const { data } = await api.post('/login', { email, password })
    if (data.success) {
      setSession(data.data.token, data.data.user)
    }
    return data
  }

  async function logout() {
    try {
      await api.post('/logout')
    } catch {
      /* ignore */
    }
    clearSession()
  }

  async function fetchMe() {
    const { data } = await api.get('/me')
    if (data.success) {
      user.value = data.data
      company.value = data.data?.company || null
      localStorage.setItem('user', JSON.stringify(data.data))
      localStorage.setItem('company', JSON.stringify(company.value))
    }
    return data
  }

  return {
    token,
    user,
    company,
    isAuthenticated,
    isAdmin,
    isSuperAdmin,
    login,
    logout,
    fetchMe,
    clearSession,
    setSession,
  }
})
