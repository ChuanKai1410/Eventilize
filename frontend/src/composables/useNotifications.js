import { ref, computed } from 'vue'
import api from '../services/api.js'
import { useAuth } from './useAuth.js'

const notifications = ref([])
let fetchPromise = null

function getCurrentUserId() {
  const { user } = useAuth()
  return user.value?.id || user.value?.email || null
}

async function fetchNotifications(force = false) {
  const userId = getCurrentUserId()
  if (!userId) {
    notifications.value = []
    return []
  }

  if (fetchPromise && !force) return fetchPromise

  fetchPromise = (async () => {
    try {
      const response = await api.get(`/users/${encodeURIComponent(userId)}/notifications`)
      if (response.data?.success && Array.isArray(response.data.data)) {
        notifications.value = response.data.data
      }
      return notifications.value
    } catch (error) {
      console.error('Failed to fetch notifications:', error)
      return notifications.value
    } finally {
      fetchPromise = null
    }
  })()

  return fetchPromise
}

async function fetchNotificationSettings() {
  const userId = getCurrentUserId()
  if (!userId) {
    return {
      newEvent: true,
      upcomingEvent: true,
      registrationDeadline: false,
    }
  }

  const response = await api.get(`/users/${encodeURIComponent(userId)}/notification-settings`)
  return response.data?.data
}

async function updateNotificationSettings(settings) {
  const userId = getCurrentUserId()
  if (!userId) return settings

  try {
    const response = await api.put(`/users/${encodeURIComponent(userId)}/notification-settings`, settings)
    if (response.data?.success && response.data?.data && typeof response.data.data === 'object') {
      return response.data.data
    }
    return settings
  } catch (error) {
    console.error('Failed to update notification settings:', error)
    return settings
  }
}

export function useNotifications() {
  const unreadCount = computed(() => notifications.value.filter((n) => !n.isRead).length)

  async function markAsRead(id) {
    const response = await api.patch(`/notifications/${id}/read`)
    if (response.data?.success) {
      const index = notifications.value.findIndex((n) => n.id === Number(id))
      if (index !== -1) notifications.value[index] = response.data.data
    }
  }

  async function markAllAsRead() {
    const userId = getCurrentUserId()
    if (!userId) return

    await api.patch(`/users/${encodeURIComponent(userId)}/notifications/read-all`)
    notifications.value = notifications.value.map((n) => ({ ...n, isRead: true }))
  }

  function filterNotifications(filter) {
    if (filter === 'unread') return notifications.value.filter((n) => !n.isRead)
    if (filter === 'read') return notifications.value.filter((n) => n.isRead)
    return notifications.value
  }

  fetchNotifications()

  return {
    notifications,
    unreadCount,
    fetchNotifications,
    fetchNotificationSettings,
    updateNotificationSettings,
    markAsRead,
    markAllAsRead,
    filterNotifications,
  }
}
