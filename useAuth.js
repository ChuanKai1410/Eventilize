import { ref, computed } from 'vue'
import api from '../services/api.js'

const STORAGE_USER = 'eventilize_user'

const user = ref(loadUser())

function loadUser() {
  try {
    const stored = localStorage.getItem(STORAGE_USER)
    return stored ? JSON.parse(stored) : null
  } catch {
    return null
  }
}

function saveSession(sessionUser, token) {
  user.value = sessionUser
  localStorage.setItem(STORAGE_USER, JSON.stringify(sessionUser))
  if (token) {
    localStorage.setItem('eventilize_token', token)
  }
}

export function useAuth() {
  const isAuthenticated = computed(() => !!user.value)

  async function login(email, password) {
    try {
      const response = await api.post('/auth/login', { email, password })
      if (response.data?.success) {
        saveSession(response.data.data.user, response.data.data.token)
        return { success: true, user: response.data.data.user }
      }
      return { success: false, message: response.data?.message || 'Login failed.' }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.errors?.credentials || error.response?.data?.message || 'Login failed.',
      }
    }
  }

  async function register({ name, email, password, role }) {
    try {
      const response = await api.post('/auth/register', { name, email, password, role })
      if (response.data?.success) {
        saveSession(response.data.data.user, response.data.data.token)
        return { success: true, user: response.data.data.user }
      }
      return { success: false, message: response.data?.message || 'Registration failed.' }
    } catch (error) {
      const errors = error.response?.data?.errors
      return {
        success: false,
        message: errors?.email || errors?.password || errors?.name || error.response?.data?.message || 'Registration failed.',
        errors,
      }
    }
  }

  async function logout() {
    try {
      await api.post('/auth/logout')
    } catch (e) {
      console.error('Logout API call failed:', e)
    } finally {
      user.value = null
      localStorage.removeItem(STORAGE_USER)
      localStorage.removeItem('eventilize_token')
    }
  }

  function getDashboardRoute() {
    if (!user.value) return '/login'
    const routes = {
      student: '/student/dashboard',
      organizer: '/organizer/dashboard',
      admin: '/admin/dashboard',
    }
    return routes[user.value.role] || '/'
  }

  return {
    user,
    isAuthenticated,
    login,
    register,
    logout,
    getDashboardRoute,
  }
}
