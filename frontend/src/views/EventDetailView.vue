<script setup>
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import ProtectedLayout from '../components/ProtectedLayout.vue'
import StatusBadge from '../components/StatusBadge.vue'
import EventCard from '../components/EventCard.vue'
import EmptyState from '../components/EmptyState.vue'
import { useEventStore } from '../composables/useEventStore.js'
import { useAuth } from '../composables/useAuth.js'

const route = useRoute()
const { getEventById, toggleBookmark, incrementViews, getRecommended } = useEventStore()
const { isAuthenticated, user } = useAuth()

const showSidebar = computed(() => {
  if (!isAuthenticated.value) return false
  return ['student', 'organizer', 'admin'].includes(user.value?.role)
})

const event = computed(() => getEventById(route.params.id))
const recommended = computed(() =>
  event.value ? getRecommended(event.value.id, 3) : []
)

const formattedDate = computed(() => {
  if (!event.value) return ''
  const d = new Date(event.value.eventDate + 'T00:00:00')
  return d.toLocaleDateString('en-MY', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
})

onMounted(() => {
  if (event.value) incrementViews(event.value.id)
})

function handleBookmark() {
  if (event.value) toggleBookmark(event.value.id)
}
</script>

<template>
  <ProtectedLayout :show-sidebar="showSidebar">
    <div class="page-container">
      <router-link to="/events" class="back-link">← Back to events</router-link>

      <EmptyState
        v-if="!event"
        title="Event not found"
        description="The event you're looking for doesn't exist or may have been removed."
        action-label="Browse events"
        @action="$router.push('/events')"
      />

      <template v-else>
        <div class="event-detail card">
          <div class="card-body">
            <div class="detail-header">
              <span class="category-badge">{{ event.category }}</span>
              <StatusBadge v-if="event.status !== 'Approved'" :status="event.status" />
            </div>
            <h1>{{ event.title }}</h1>

            <div class="detail-meta">
              <div class="meta-item">
                <strong>Date</strong>
                <span>{{ formattedDate }}</span>
              </div>
              <div class="meta-item">
                <strong>Time</strong>
                <span>{{ event.startTime }} – {{ event.endTime }}</span>
              </div>
              <div class="meta-item">
                <strong>Location</strong>
                <span>{{ event.location }}</span>
              </div>
              <div class="meta-item">
                <strong>Organizer</strong>
                <span>{{ event.organizer }}</span>
              </div>
              <div class="meta-item">
                <strong>Views</strong>
                <span>{{ event.viewsCount }}</span>
              </div>
              <div class="meta-item">
                <strong>Bookmarks</strong>
                <span>{{ event.bookmarksCount }}</span>
              </div>
            </div>

            <div class="detail-section">
              <h2>About this event</h2>
              <p>{{ event.description }}</p>
            </div>

            <div class="detail-section">
              <h2>Location Map</h2>
              <div class="map-placeholder">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                  <path d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <p>Map integration placeholder</p>
                <a
                  v-if="event.mapLink"
                  :href="event.mapLink"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="btn btn-outline btn-sm"
                >
                  Open in Google Maps
                </a>
              </div>
            </div>

            <div class="detail-actions">
              <a
                v-if="event.registrationLink"
                :href="event.registrationLink"
                target="_blank"
                rel="noopener noreferrer"
                class="btn btn-primary"
              >
                Register for Event
              </a>
              <button
                type="button"
                class="btn btn-ghost"
                :class="{ active: event.isBookmarked }"
                @click="handleBookmark"
              >
                {{ event.isBookmarked ? '★ Bookmarked' : '☆ Bookmark Event' }}
              </button>
            </div>
          </div>
        </div>

        <section v-if="recommended.length" class="section">
          <h2>Similar Events You Might Like</h2>
          <div class="events-grid">
            <EventCard
              v-for="rec in recommended"
              :key="rec.id"
              :event="rec"
              @bookmark="toggleBookmark"
            />
          </div>
        </section>
      </template>
    </div>
  </ProtectedLayout>
</template>

<style scoped>
.back-link {
  display: inline-block;
  margin-bottom: 1rem;
  font-weight: 500;
  font-size: 0.875rem;
  color: var(--color-primary);
  text-decoration: none;
}

.back-link:hover {
  color: var(--color-primary-dark);
  text-decoration: none;
}

.detail-header {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.event-detail h1 {
  font-size: 1.75rem;
  margin-bottom: 1.25rem;
}

.detail-meta {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
  padding: 1rem;
  background: var(--color-bg);
  border-radius: var(--radius-sm);
}

.meta-item {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.meta-item strong {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  color: var(--color-text-muted);
}

.detail-section {
  margin-bottom: 1.5rem;
}

.detail-section h2 {
  font-size: 1.125rem;
  margin-bottom: 0.5rem;
}

.detail-section p {
  color: var(--color-text-muted);
  line-height: 1.7;
}

.detail-actions {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
  margin-top: 1rem;
}

.btn-ghost.active {
  color: var(--color-primary);
  border-color: var(--color-primary);
  background: var(--color-maroon-tint);
}

.section h2 {
  font-size: 1.25rem;
  margin-bottom: 1rem;
}
</style>
