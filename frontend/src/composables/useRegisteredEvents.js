import { ref, computed } from 'vue'
import api from '../services/api.js'
import { useAuth } from './useAuth.js'
import { useEventStore } from './useEventStore.js'

const registeredEventsState = ref([])
let fetchPromise = null

function getCurrentUserId() {
  const { user } = useAuth()
  return user.value?.id || user.value?.email || null
}

async function fetchRegisteredEvents(force = false) {
  const userId = getCurrentUserId()
  if (!userId) {
    registeredEventsState.value = []
    return []
  }

  if (fetchPromise && !force) return fetchPromise

  fetchPromise = (async () => {
    try {
      const response = await api.get(`/users/${encodeURIComponent(userId)}/registrations`)
      if (response.data?.success && Array.isArray(response.data.data)) {
        registeredEventsState.value = response.data.data
      }
      return registeredEventsState.value
    } catch (error) {
      console.error('Failed to fetch registered events:', error)
      return registeredEventsState.value
    } finally {
      fetchPromise = null
    }
  })()

  return fetchPromise
}

export function useRegisteredEvents() {
  const { approvedEvents } = useEventStore()

  const today = () => new Date().toISOString().slice(0, 10)

  const registeredEvents = computed(() =>
    [...registeredEventsState.value].sort((a, b) => a.eventDate.localeCompare(b.eventDate))
  )

  const registeredIds = computed(() => registeredEvents.value.map((e) => Number(e.id)))

  const upcomingRegistered = computed(() =>
    registeredEvents.value.filter((e) => e.eventDate >= today())
  )

  const pastRegistered = computed(() =>
    registeredEvents.value.filter((e) => e.eventDate < today()).reverse()
  )

  function isRegistered(eventId) {
    return registeredIds.value.includes(Number(eventId))
  }

  async function fetchRegistrationStatus(eventId) {
    const userId = getCurrentUserId()
    if (!userId || !eventId) return false

    try {
      const response = await api.get(`/users/${encodeURIComponent(userId)}/registrations/${eventId}`)
      const status = response.data?.data?.isRegistered
      return status === true || status === 1 || status === '1'
    } catch (error) {
      console.error('Failed to fetch registration status:', error)
      return false
    }
  }

  async function registerForEvent(eventId) {
    const userId = getCurrentUserId()
    if (!userId) return null

    const response = await api.post(`/events/${eventId}/registrations`, { userId })
    if (response.data?.success) {
      await fetchRegisteredEvents(true)
      return response.data.data
    }
    return null
  }

  function getEventsOnDate(dateStr) {
    return approvedEvents.value.filter((e) => e.eventDate === dateStr)
  }

  function getDatesWithEvents(year, month) {
    const prefix = `${year}-${String(month + 1).padStart(2, '0')}`
    return [
      ...new Set(
        approvedEvents.value
          .filter((e) => e.eventDate.startsWith(prefix))
          .map((e) => e.eventDate)
      ),
    ]
  }

  fetchRegisteredEvents()

  return {
    registeredEvents,
    upcomingRegistered,
    pastRegistered,
    fetchRegisteredEvents,
    fetchRegistrationStatus,
    isRegistered,
    registerForEvent,
    getEventsOnDate,
    getDatesWithEvents,
  }
}
