<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import ProtectedLayout from '../components/ProtectedLayout.vue'
import SearchBar from '../components/SearchBar.vue'
import EventFilterBar from '../components/EventFilterBar.vue'
import EventCard from '../components/EventCard.vue'
import EmptyState from '../components/EmptyState.vue'
import { useEventStore } from '../composables/useEventStore.js'
import { useAuth } from '../composables/useAuth.js'

const { fetchEvents, toggleBookmark } = useEventStore()
const { isAuthenticated, user } = useAuth()
const route = useRoute()

const showSidebar = computed(() => isAuthenticated.value && user.value?.role === 'student')

const events = ref([])
const filteredEvents = ref([])
const loading = ref(true)
const searchQuery = ref('')
const activeFilters = ref({})
const filterBarRef = ref(null)

onMounted(async () => {
  if (route.query.q) {
    searchQuery.value = String(route.query.q)
  }
  loading.value = true
  const data = await fetchEvents()
  events.value = data.filter((e) => e.status === 'Approved')
  applyFilters()
  loading.value = false
})

function applyFilters() {
  let result = [...events.value]

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    result = result.filter(
      (e) =>
        e.title.toLowerCase().includes(q) ||
        e.description.toLowerCase().includes(q) ||
        e.organizer.toLowerCase().includes(q) ||
        e.category.toLowerCase().includes(q)
    )
  }

  const f = activeFilters.value
  if (f.category) result = result.filter((e) => e.category === f.category)
  if (f.date) result = result.filter((e) => e.eventDate === f.date)
  if (f.location) result = result.filter((e) => e.location === f.location)
  if (f.organizer) result = result.filter((e) => e.organizer === f.organizer)

  if (f.sort === 'popular') {
    result.sort((a, b) => b.bookmarksCount - a.bookmarksCount)
  } else if (f.sort === 'upcoming') {
    result.sort((a, b) => a.eventDate.localeCompare(b.eventDate))
  }

  filteredEvents.value = result
}

function onSearch(keyword) {
  searchQuery.value = keyword
  applyFilters()
}

function onFilter(filters) {
  activeFilters.value = filters
  applyFilters()
}

function resetAll() {
  searchQuery.value = ''
  filterBarRef.value?.resetFilters()
  applyFilters()
}

function onBookmark(id) {
  toggleBookmark(id)
  applyFilters()
}
</script>

<template>
  <ProtectedLayout :show-sidebar="showSidebar">
    <div class="page-container">
      <div class="page-header">
        <h1>Discover Events</h1>
        <p>Browse and explore upcoming UTM campus events.</p>
      </div>

      <div class="discovery-controls">
        <SearchBar v-model="searchQuery" @search="onSearch" />
        <EventFilterBar ref="filterBarRef" @filter="onFilter" />
      </div>

      <div v-if="loading" class="loading-spinner">
        <div class="spinner" />
        <p>Loading events...</p>
      </div>

      <template v-else>
        <div v-if="filteredEvents.length" class="results-info">
          <p>{{ filteredEvents.length }} event(s) found</p>
        </div>

        <div v-if="filteredEvents.length" class="events-grid">
          <EventCard
            v-for="event in filteredEvents"
            :key="event.id"
            :event="event"
            @bookmark="onBookmark"
          />
        </div>

        <EmptyState
          v-else
          title="No events found"
          description="Try adjusting your search or filters to find more events."
          action-label="Reset filters"
          @action="resetAll"
        />
      </template>
    </div>
  </ProtectedLayout>
</template>

<style scoped>
.discovery-controls {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.results-info {
  margin-bottom: 1rem;
  color: var(--color-text-muted);
  font-size: 0.875rem;
}
</style>
