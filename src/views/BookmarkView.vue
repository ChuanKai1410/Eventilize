<script setup>
import ProtectedLayout from '../components/ProtectedLayout.vue'
import EventCard from '../components/EventCard.vue'
import EmptyState from '../components/EmptyState.vue'
import { useEventStore } from '../composables/useEventStore.js'

const { bookmarkedEvents, toggleBookmark } = useEventStore()
</script>

<template>
  <ProtectedLayout>
    <div class="page-container">
      <div class="page-header">
        <h1>My Bookmarks</h1>
        <p>Events you've saved for later — sorted by upcoming date.</p>
      </div>

      <div v-if="bookmarkedEvents.length" class="events-grid">
        <EventCard
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
        @action="$router.push('/events')"
      />
    </div>
  </ProtectedLayout>
</template>
