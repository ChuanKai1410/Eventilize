<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ProtectedLayout from '../components/ProtectedLayout.vue'
import StatusBadge from '../components/StatusBadge.vue'
import EmptyState from '../components/EmptyState.vue'
import { useEventStore } from '../composables/useEventStore.js'

const route = useRoute()
const router = useRouter()

const {
  getEventById,
  deleteEvent,
  incrementViews,
} = useEventStore()

const event = computed(() => getEventById(route.params.id))

const showDeleteModal = ref(false)

const formattedDate = computed(() => {
  if (!event.value) return ''

  const d = new Date(event.value.eventDate + 'T00:00:00')

  return d.toLocaleDateString('en-MY', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
})

onMounted(() => {
  if (event.value) incrementViews(event.value.id)
})

function openDeleteModal() {
  showDeleteModal.value = true
}

function closeDeleteModal() {
  showDeleteModal.value = false
}

function handleDelete() {
  if (event.value) {
    deleteEvent(event.value.id)
    router.push('/organizer/events')
  }
  showDeleteModal.value = false
}
</script>

<template>
  <ProtectedLayout>
    <div class="page-container">
      <button type="button" class="back-link back-link-btn" @click="$router.back()">
        &larr; Back
      </button>

      <EmptyState
        v-if="!event"
        title="Event not found"
        description="The event you're looking for doesn't exist or may have been removed."
        action-label="Go to event management"
        @action="$router.push('/organizer/events')"
      />

      <template v-else>
        <div class="event-detail card">
          <div v-if="event.imageUrl" class="event-hero-image-container">
            <img :src="event.imageUrl" alt="Event Banner" class="event-hero-image" />
          </div>
          <div class="card-body">
            <div class="detail-header">
              <span class="category-badge cat-default">{{ event.category }}</span>
              <StatusBadge :status="event.status" />
            </div>

            <h1>{{ event.title }}</h1>

            <div class="detail-meta">
              <div class="meta-item">
                <strong>Date</strong>
                <span>{{ formattedDate }}</span>
              </div>

              <div class="meta-item">
                <strong>Time</strong>
                <span>{{ event.startTime }} &ndash; {{ event.endTime }}</span>
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

            <div v-if="event.rejectReason && event.status === 'Rejected'" class="reject-reason-box alert alert-error">
              <strong>Rejected Reason:</strong> {{ event.rejectReason }}
            </div>

            <div class="detail-section">
              <h2>About this event</h2>
              <p>{{ event.description }}</p>
            </div>

            <div v-if="event.posters && event.posters.length" class="detail-section">
              <h2>Event Posters</h2>
              <div class="detail-posters-gallery">
                <div v-for="(poster, index) in event.posters" :key="index" class="detail-poster-card">
                  <a :href="poster.url" target="_blank" rel="noopener noreferrer" class="detail-poster-link">
                    <img :src="poster.url" :alt="poster.name" class="detail-poster-img" />
                    <span class="detail-poster-name">{{ poster.name }}</span>
                  </a>
                </div>
              </div>
            </div>

            <div class="detail-section">
              <h2>Location Map</h2>

              <div class="map-placeholder">
                <svg
                  width="48"
                  height="48"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.5"
                  aria-hidden="true"
                >
                  <path
                    d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
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
              <router-link :to="`/organizer/events/${event.id}/edit`" class="btn btn-primary">
                Edit Event
              </router-link>

              <button type="button" class="btn btn-danger" @click="openDeleteModal">
                Delete Event
              </button>
            </div>
          </div>
        </div>

        <div v-if="showDeleteModal" class="modal-overlay" @click.self="closeDeleteModal">
          <div class="modal">
            <h3>Delete Event?</h3>
            <p>
              Are you sure you want to delete "{{ event?.title }}"? This action cannot be undone.
            </p>
            <div class="modal-actions">
              <button type="button" class="btn btn-ghost" @click="closeDeleteModal">
                Cancel
              </button>
              <button type="button" class="btn btn-danger" @click="handleDelete">
                Delete
              </button>
            </div>
          </div>
        </div>
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

.back-link-btn {
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
  font-family: inherit;
  text-align: left;
}

.event-hero-image-container {
  width: 100%;
  height: 280px;
  overflow: hidden;
  border-bottom: 1px solid var(--color-border);
}

.event-hero-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
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

.reject-reason-box {
  margin-bottom: 1.5rem;
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

.detail-posters-gallery {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 1.25rem;
  margin-top: 0.75rem;
}

.detail-poster-card {
  background: var(--color-bg);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  padding: 0.5rem;
  transition: var(--transition);
}

.detail-poster-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-sm);
  border-color: var(--color-primary);
}

.detail-poster-link {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  text-decoration: none;
}

.detail-poster-img {
  width: 100%;
  height: 140px;
  object-fit: cover;
  border-radius: var(--radius-xs);
}

.detail-poster-name {
  font-size: 0.75rem;
  color: var(--color-text-muted);
  text-align: center;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-weight: 500;
}
</style>
