import { ref, computed } from 'vue'

const STORAGE_USER = 'eventilize_user'
const STORAGE_USERS = 'eventilize_users'

const user = ref(loadUser())

function loadUser() {
  try {
    const stored = localStorage.getItem(STORAGE_USER)
    return stored ? JSON.parse(stored) : null
  } catch {
    return null
  }
}

function loadUsers() {
  try {
    const stored = localStorage.getItem(STORAGE_USERS)
    if (stored) return JSON.parse(stored)
  } catch {
    /* ignore */
  }
  return [
    {
      email: 'admin@utm.my',
      password: 'admin1234',
      name: 'Platform Admin',
      role: 'admin',
    },
    {
      email: 'organizer@utm.my',
      password: 'organizer123',
      name: 'Event Organizer',
      role: 'organizer',
      organizerName: 'Computing Students Society',
    },
    {
      email: 'student@utm.my',
      password: 'student123',
      name: 'Ahmad Student',
      role: 'student',
    },
  ]
}

function saveUsers(users) {
  localStorage.setItem(STORAGE_USERS, JSON.stringify(users))
}

export function useAuth() {
  const isAuthenticated = computed(() => !!user.value)

  function login(email, password) {
    const users = loadUsers()
    const account = users.find(
      (u) => u.email.toLowerCase() === email.toLowerCase() && u.password === password
    )
    if (!account) {
      return { success: false, message: 'Invalid email or password.' }
    }
    const session = {
      email: account.email,
      name: account.name,
      role: account.role,
      organizerName: account.organizerName || null,
    }
    user.value = session
    localStorage.setItem(STORAGE_USER, JSON.stringify(session))
    return { success: true, user: session }
  }

  function register({ name, email, password, role }) {
    const users = loadUsers()
    if (users.some((u) => u.email.toLowerCase() === email.toLowerCase())) {
      return { success: false, message: 'An account with this email already exists.' }
    }
    const newUser = {
      email,
      password,
      name,
      role,
      organizerName: role === 'organizer' ? name : undefined,
    }
    users.push(newUser)
    saveUsers(users)
    return login(email, password)
  }

  function logout() {
    user.value = null
    localStorage.removeItem(STORAGE_USER)
    localStorage.removeItem('eventilize_token')
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
