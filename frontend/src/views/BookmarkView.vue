<script setup>
import { onMounted } from 'vue'
import StudentProtectedLayout from '../components/student/StudentProtectedLayout.vue'
import StudentEventCard from '../components/student/StudentEventCard.vue'
import RegisteredEventsTabs from '../components/student/RegisteredEventsTabs.vue'
import EmptyState from '../components/EmptyState.vue'
import { useEventStore } from '../composables/useEventStore.js'
import { useRegisteredEvents } from '../composables/useRegisteredEvents.js'

const { bookmarkedEvents, fetchEvents, toggleBookmark } = useEventStore()
const { upcomingRegistered, pastRegistered } = useRegisteredEvents()

onMounted(() => {
  fetchEvents(true)
})
</script>

<template>
  <StudentProtectedLayout>
    <div class="page-container">
      <div class="page-header">
        <h1>My Bookmarks</h1>
        <p>Events you've saved for later — sorted by upcoming date.</p>
      </div>

      <section class="section">
        <div class="section-title">
          <h2>Saved Events</h2>
        </div>
        <div v-if="bookmarkedEvents.length" class="events-grid">
          <StudentEventCard
            v-for="event in bookmarkedEvents"
            :key="event.id"
            :event="event"
            @bookmark="toggleBookmark"
          />
        </div>
        <EmptyState
          v-else
          title="No bookmarked events yet"
          description="Browse events and bookmark the ones you're interested in."
          action-label="Discover events"
          @action="$router.push('/student/events')"
        />
      </section>

      <section class="section registered-section">
        <div class="section-title">
          <h2>Registered Events</h2>
        </div>
        <RegisteredEventsTabs
          :upcoming="upcomingRegistered"
          :past="pastRegistered"
          @bookmark="toggleBookmark"
        />
      </section>
    </div>
  </StudentProtectedLayout>
</template>

<style scoped>
.section {
  margin-bottom: 2rem;
}

.section-title {
  margin-bottom: 1rem;
}

.section-title h2 {
  font-size: 1.125rem;
  font-weight: 600;
}

.registered-section {
  padding-top: 1.5rem;
  border-top: 1px solid var(--color-border);
}
</style>
