import { ref, computed } from 'vue'
import { mockEvents, eventCategories } from '../data/mockEvents.js'

const events = ref(initializeEvents())
const categories = ref([...eventCategories])

function initializeEvents() {
  try {
    const stored = localStorage.getItem('eventilize_events')
    if (stored) return JSON.parse(stored)
  } catch {
    /* ignore */
  }
  return JSON.parse(JSON.stringify(mockEvents))
}

function persistEvents() {
  localStorage.setItem('eventilize_events', JSON.stringify(events.value))
}

function simulateDelay(ms = 600) {
  return new Promise((resolve) => setTimeout(resolve, ms))
}

export function useEventStore() {
  const bookmarkedEvents = computed(() =>
    events.value.filter((e) => e.isBookmarked).sort((a, b) => a.eventDate.localeCompare(b.eventDate))
  )

  const approvedEvents = computed(() => events.value.filter((e) => e.status === 'Approved'))

  async function fetchEvents() {
    await simulateDelay(800)
    return [...events.value]
  }

  function getEventById(id) {
    return events.value.find((e) => e.id === Number(id))
  }

  function toggleBookmark(eventId) {
    const event = events.value.find((e) => e.id === eventId)
    if (!event) return
    event.isBookmarked = !event.isBookmarked
    event.bookmarksCount += event.isBookmarked ? 1 : -1
    if (event.bookmarksCount < 0) event.bookmarksCount = 0
    persistEvents()
  }

  function incrementViews(eventId) {
    const event = events.value.find((e) => e.id === eventId)
    if (event) {
      event.viewsCount += 1
      persistEvents()
    }
  }

  function getOrganizerEvents(organizerName) {
    return events.value.filter((e) => e.organizer === organizerName)
  }

  function addEvent(eventData) {
    const newId = Math.max(0, ...events.value.map((e) => e.id)) + 1
    const newEvent = {
      id: newId,
      viewsCount: 0,
      bookmarksCount: 0,
      isBookmarked: false,
      ...eventData,
    }
    events.value.push(newEvent)
    persistEvents()
    return newEvent
  }

  function updateEvent(id, eventData) {
    const index = events.value.findIndex((e) => e.id === Number(id))
    if (index === -1) return null
    events.value[index] = { ...events.value[index], ...eventData }
    persistEvents()
    return events.value[index]
  }

  function deleteEvent(id) {
    const index = events.value.findIndex((e) => e.id === Number(id))
    if (index === -1) return false
    events.value.splice(index, 1)
    persistEvents()
    return true
  }

  function updateEventStatus(id, status) {
    return updateEvent(id, { status })
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
