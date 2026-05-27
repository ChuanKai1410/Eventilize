import { ref, computed } from 'vue'
import { mockNotifications } from '../data/mockNotifications.js'

const notifications = ref(initializeNotifications())

function initializeNotifications() {
  try {
    const stored = localStorage.getItem('eventilize_notifications')
    if (stored) return JSON.parse(stored)
  } catch {
    /* ignore */
  }
  return JSON.parse(JSON.stringify(mockNotifications))
}

function persist() {
  localStorage.setItem('eventilize_notifications', JSON.stringify(notifications.value))
}

export function useNotifications() {
  const unreadCount = computed(() => notifications.value.filter((n) => !n.isRead).length)

  function markAsRead(id) {
    const item = notifications.value.find((n) => n.id === id)
    if (item) {
      item.isRead = true
      persist()
    }
  }

  function markAllAsRead() {
    notifications.value.forEach((n) => {
      n.isRead = true
    })
    persist()
  }

  function filterNotifications(filter) {
    if (filter === 'unread') return notifications.value.filter((n) => !n.isRead)
    if (filter === 'read') return notifications.value.filter((n) => n.isRead)
    return notifications.value
  }

  return {
    notifications,
    unreadCount,
    markAsRead,
    markAllAsRead,
    filterNotifications,
  }
}
