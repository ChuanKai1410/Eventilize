import { ref, computed } from 'vue'
import api from '../services/api.js'
import { useAuth } from './useAuth.js'

const events = ref([])
const categories = ref([])

let fetchPromise = null

function getCurrentUserId() {
  const { user } = useAuth()
  return user.value?.email || user.value?.name || null
}

function upsertEvent(event) {
  const index = events.value.findIndex((e) => e.id === Number(event.id))
  if (index === -1) {
    events.value.push(event)
  } else {
    events.value[index] = event
  }
  return event
}

async function fetchCategories() {
  try {
    const response = await api.get('/categories')
    if (response.data?.success && Array.isArray(response.data.data)) {
      categories.value = response.data.data
    }
  } catch (error) {
    console.error('Failed to fetch categories:', error)
  }
}

async function fetchEvents(force = false) {
  if (fetchPromise && !force) return fetchPromise

  fetchPromise = (async () => {
    try {
      const response = await api.get('/events')
      if (response.data?.success && Array.isArray(response.data.data)) {
        events.value = response.data.data
      }
      return [...events.value]
    } catch (error) {
      console.error('Failed to fetch events:', error)
      return [...events.value]
    } finally {
      fetchPromise = null
    }
  })()

  return fetchPromise
}

fetchCategories()
fetchEvents()

export function useEventStore() {
  const bookmarkedEvents = computed(() =>
    events.value.filter((e) => e.isBookmarked).sort((a, b) => a.eventDate.localeCompare(b.eventDate))
  )

  const approvedEvents = computed(() => events.value.filter((e) => e.status === 'Approved'))

  function getEventById(id) {
    return events.value.find((e) => e.id === Number(id))
  }

  async function toggleBookmark(eventId) {
    const event = getEventById(eventId)
    const original = event ? { ...event } : null

    if (event) {
      event.isBookmarked = !event.isBookmarked
      event.bookmarksCount += event.isBookmarked ? 1 : -1
      if (event.bookmarksCount < 0) event.bookmarksCount = 0
    }

    try {
      const response = await api.post(`/events/${eventId}/bookmark`, { userId: getCurrentUserId() })
      if (response.data?.success) {
        return upsertEvent(response.data.data)
      }
    } catch (error) {
      console.error('Failed to toggle bookmark:', error)
      if (original) upsertEvent(original)
    }
    return null
  }

  async function incrementViews(eventId) {
    try {
      const response = await api.post(`/events/${eventId}/views`)
      if (response.data?.success) {
        return upsertEvent(response.data.data)
      }
    } catch (error) {
      console.error('Failed to increment views:', error)
    }
    return null
  }

  function getOrganizerEvents(organizerName) {
    return events.value.filter((e) => e.organizer === organizerName)
  }

  async function addEvent(eventData) {
    const response = await api.post('/events', eventData)
    if (response.data?.success) {
      return upsertEvent(response.data.data)
    }
    return null
  }

  async function updateEvent(id, eventData) {
    const response = await api.put(`/events/${id}`, eventData)
    if (response.data?.success) {
      return upsertEvent(response.data.data)
    }
    return null
  }

  async function deleteEvent(id) {
    const response = await api.delete(`/events/${id}`)
    if (response.data?.success) {
      const index = events.value.findIndex((e) => e.id === Number(id))
      if (index !== -1) events.value.splice(index, 1)
      return true
    }
    return false
  }

  async function updateEventStatus(id, status, extraFields = {}) {
    const response = await api.patch(`/events/${id}/status`, { status, ...extraFields })
    if (response.data?.success) {
      return upsertEvent(response.data.data)
    }
    return null
  }

  function getRecommended(excludeId = null, limit = 3) {
    return events.value
      .filter((e) => e.status === 'Approved' && e.id !== excludeId)
      .sort((a, b) => b.bookmarksCount - a.bookmarksCount)
      .slice(0, limit)
  }

  function addCategory(name) {
    if (!categories.value.includes(name)) {
      categories.value.push(name)
    }
  }

  return {
    events,
    categories,
    bookmarkedEvents,
    approvedEvents,
    fetchEvents,
    fetchCategories,
    getEventById,
    toggleBookmark,
    incrementViews,
    getOrganizerEvents,
    addEvent,
    updateEvent,
    deleteEvent,
    updateEventStatus,
    getRecommended,
    addCategory,
  }
}
