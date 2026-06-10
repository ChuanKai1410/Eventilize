import { ref, computed } from 'vue'
import { useEventStore } from './useEventStore.js'

const STORAGE_KEY = 'eventilize_registered'

function loadRegisteredIds() {
  try {
    const stored = localStorage.getItem(STORAGE_KEY)
    if (stored) return JSON.parse(stored)
  } catch {
    /* ignore */
  }
  // Mock: student registered for these approved events
  return [2, 4, 5, 7]
}

const registeredIds = ref(loadRegisteredIds())

function persistRegistered() {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(registeredIds.value))
}

export function useRegisteredEvents() {
  const { events, approvedEvents } = useEventStore()

  const today = () => new Date().toISOString().slice(0, 10)

  const registeredEvents = computed(() =>
    events.value
      .filter((e) => registeredIds.value.includes(e.id) && e.status === 'Approved')
      .sort((a, b) => a.eventDate.localeCompare(b.eventDate))
  )

  const upcomingRegistered = computed(() =>
    registeredEvents.value.filter((e) => e.eventDate >= today())
  )

  const pastRegistered = computed(() =>
    registeredEvents.value.filter((e) => e.eventDate < today()).reverse()
  )

  function isRegistered(eventId) {
    return registeredIds.value.includes(Number(eventId))
  }

  function registerForEvent(eventId) {
    const id = Number(eventId)
    if (!registeredIds.value.includes(id)) {
      registeredIds.value.push(id)
      persistRegistered()
    }
  }

  function unregisterFromEvent(eventId) {
    const id = Number(eventId)
    registeredIds.value = registeredIds.value.filter((rid) => rid !== id)
    persistRegistered()
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

  return {
    registeredEvents,
    upcomingRegistered,
    pastRegistered,
    isRegistered,
    registerForEvent,
    unregisterFromEvent,
    getEventsOnDate,
    getDatesWithEvents,
  }
}
