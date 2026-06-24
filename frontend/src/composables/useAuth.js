import { ref, computed } from 'vue'
import api from '../services/api.js'

const STORAGE_USER = 'eventilize_user'
const STORAGE_TOKEN = 'eventilize_token'

const user = ref(loadUser())

function loadUser() {
  try {
    if (!localStorage.getItem(STORAGE_TOKEN)) {
      localStorage.removeItem(STORAGE_USER)
      return null
    }

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
    localStorage.setItem(STORAGE_TOKEN, token)
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

  function logout() {
    user.value = null
    localStorage.removeItem(STORAGE_USER)
    localStorage.removeItem(STORAGE_TOKEN)
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
