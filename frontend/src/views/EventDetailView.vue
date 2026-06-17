<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ProtectedLayout from '../components/ProtectedLayout.vue'
import StudentProtectedLayout from '../components/student/StudentProtectedLayout.vue'
import StatusBadge from '../components/StatusBadge.vue'
import EventCard from '../components/EventCard.vue'
import StudentEventCard from '../components/student/StudentEventCard.vue'
import BookmarkIcon from '../components/student/BookmarkIcon.vue'
import EmptyState from '../components/EmptyState.vue'
import { useEventStore } from '../composables/useEventStore.js'
import { useAuth } from '../composables/useAuth.js'
import { useRegisteredEvents } from '../composables/useRegisteredEvents.js'

const route = useRoute()
const router = useRouter()

const {
  fetchEvents,
  getEventById,
  toggleBookmark,
  incrementViews,
  getRecommended,
  updateEventStatus,
} = useEventStore()

const { isAuthenticated, user } = useAuth()
const {
  fetchRegisteredEvents,
  isRegistered,
  registerForEvent,
  unregisterFromEvent,
} = useRegisteredEvents()

const showSidebar = computed(() => {
  if (!isAuthenticated.value) return false
  return ['student', 'organizer', 'admin'].includes(user.value?.role)
})

const event = computed(() => getEventById(route.params.id))

const isAdmin = computed(() => user.value?.role === 'admin')
const isStudent = computed(() => user.value?.role === 'student')
const isOrganizer = computed(() => user.value?.role === 'organizer')
const isPendingEvent = computed(() => event.value?.status === 'Pending')
const isEventRegistered = computed(() => event.value ? isRegistered(event.value.id) : false)
const isOrganizerEvent = computed(() => {
  if (!event.value || !user.value) return false
  const organizerName = user.value.organizerName || user.value.name
  return user.value.role === 'organizer' && event.value.organizer === organizerName
})

const eventImageSrc = computed(() =>
  typeof event.value?.eventImage === 'string' ? event.value.eventImage : ''
)

const posterImages = computed(() =>
  Array.isArray(event.value?.poster)
    ? event.value.poster.filter((poster) => typeof poster === 'string')
    : []
)

const layoutComponent = computed(() =>
  isStudent.value ? StudentProtectedLayout : ProtectedLayout
)

const categoryClass = computed(() => {
  if (!event.value) return 'cat-default'
  const map = {
    'Tech Talk': 'cat-academic',
    Seminar: 'cat-academic',
    Career: 'cat-academic',
    Workshop: 'cat-workshop',
    Cultural: 'cat-cultural',
    Sports: 'cat-sports',
    Residential: 'cat-default',
  }
  if (
    event.value.title?.toLowerCase().includes('hackathon') ||
    event.value.title?.toLowerCase().includes('tournament')
  ) {
    return 'cat-competition'
  }
  return map[event.value.category] || 'cat-default'
})

const recommended = computed(() =>
  !isAdmin.value && !isOrganizer.value && event.value ? getRecommended(event.value.id, 3) : []
)

const fallbackBackRoute = computed(() => {
  if (isAdmin.value && route.query.from === 'admin-approval') {
    return '/admin/approvals'
  }

  if (isAdmin.value && route.query.from === 'admin-events') {
    return '/admin/events'
  }

  if (isOrganizer.value && route.query.from === 'organizer-events') {
    return '/organizer/events'
  }

  return '/events'
})

const showRejectModal = ref(false)
const rejectReason = ref('')
const rejectReasonError = ref('')
const successMessage = ref('')

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

onMounted(async () => {
  await fetchEvents()
  await fetchRegisteredEvents(true)
  if (event.value) incrementViews(event.value.id)
})

function handleBookmark() {
  if (event.value) toggleBookmark(event.value.id)
}

async function handleRegistration() {
  if (!event.value) return

  if (isEventRegistered.value) {
    await unregisterFromEvent(event.value.id)
    successMessage.value = `Registration for "${event.value.title}" has been cancelled.`
  } else {
    await registerForEvent(event.value.id)
    successMessage.value = `You have registered for "${event.value.title}".`
  }

  clearSuccess()
}

function goBack() {
  if (window.history.state?.back) {
    router.back()
    return
  }

  router.push(fallbackBackRoute.value)
}

function goToEdit() {
  if (event.value) router.push(`/organizer/events/${event.value.id}/edit`)
}

async function approveEvent() {
  if (!event.value) return

  await updateEventStatus(event.value.id, 'Approved', { rejectReason: '' })

  successMessage.value = `Event "${event.value.title}" has been approved.`
  clearSuccess()
}

function openRejectModal() {
  rejectReason.value = ''
  rejectReasonError.value = ''
  showRejectModal.value = true
}

function closeRejectModal() {
  showRejectModal.value = false
  rejectReason.value = ''
  rejectReasonError.value = ''
}

async function confirmReject() {
  if (!rejectReason.value.trim()) {
    rejectReasonError.value = 'Reject reason is required.'
    return
  }

  if (!event.value) return

  await updateEventStatus(event.value.id, 'Rejected', { rejectReason: rejectReason.value.trim() })

  successMessage.value = `Event "${event.value.title}" has been rejected.`
  closeRejectModal()
  clearSuccess()
}

function clearSuccess() {
  setTimeout(() => {
    successMessage.value = ''
  }, 3000)
}
</script>

<template>
  <component :is="layoutComponent" :show-sidebar="showSidebar">
    <div class="page-container">
      <button type="button" class="back-link" @click="goBack">
        Back
      </button>

      <div v-if="successMessage" class="alert alert-success">
        {{ successMessage }}
      </div>

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
              <div v-if="isStudent" class="category-block" :class="categoryClass">
                <span class="category-block-label">Category</span>
                <span class="category-block-value">{{ event.category }}</span>
              </div>
              <span v-else class="category-badge">{{ event.category }}</span>
              <StatusBadge v-if="isAdmin || event.status !== 'Approved'" :status="event.status" />
            </div>

            <h1>{{ event.title }}</h1>

            <div v-if="event.status === 'Rejected' && event.rejectReason && (isAdmin || isOrganizerEvent)" class="alert alert-error reject-reason-banner">
              <strong>Rejection Reason:</strong>
              <p class="reject-reason-text">{{ event.rejectReason }}</p>
            </div>

            <div v-if="eventImageSrc || posterImages.length" class="event-media">
              <img
                v-if="eventImageSrc"
                :src="eventImageSrc"
                :alt="event.title"
                class="event-main-image"
              />

              <div v-if="posterImages.length" class="poster-grid">
                <img
                  v-for="(poster, index) in posterImages"
                  :key="index"
                  :src="poster"
                  :alt="`${event.title} poster ${index + 1}`"
                  class="poster-image"
                />
              </div>
            </div>

            <div v-if="isStudent" class="info-boxes">
              <div class="info-box info-date">
                <span class="info-label">Date</span>
                <span class="info-value">{{ formattedDate }}</span>
              </div>
              <div class="info-box info-time">
                <span class="info-label">Time</span>
                <span class="info-value">{{ event.startTime }} – {{ event.endTime }}</span>
              </div>
              <div class="info-box info-location">
                <span class="info-label">Location</span>
                <span class="info-value">{{ event.location }}</span>
              </div>
              <div class="info-box info-organizer">
                <span class="info-label">Organizer</span>
                <span class="info-value">{{ event.organizer }}</span>
              </div>
              <div class="info-box info-stats">
                <span class="info-label">Engagement</span>
                <span class="info-value">{{ event.viewsCount }} views · {{ event.bookmarksCount }} bookmarks</span>
              </div>
            </div>

            <div v-else class="detail-meta">
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
              <template v-if="isAdmin">
                <template v-if="isPendingEvent">
                  <button type="button" class="btn btn-accent" @click="approveEvent">
                    Approve
                  </button>

                  <button type="button" class="btn btn-danger" @click="openRejectModal">
                    Reject
                  </button>
                </template>

                <div v-else class="admin-status-note">
                  <strong>Status:</strong>
                  <StatusBadge :status="event.status" />

                  <span v-if="event.rejectReason">
                    Reason: {{ event.rejectReason }}
                  </span>
                </div>
              </template>

              <template v-else>
                <button
                  v-if="isOrganizer"
                  type="button"
                  class="btn btn-accent"
                  :disabled="!isOrganizerEvent"
                  @click="goToEdit"
                >
                  Edit Event
                </button>

                <button
                  v-else-if="isStudent"
                  type="button"
                  class="btn btn-primary"
                  @click="handleRegistration"
                >
                  {{ isEventRegistered ? 'Cancel Registration' : 'Register for Event' }}
                </button>

                <a
                  v-else-if="event.registrationLink"
                  :href="event.registrationLink"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="btn btn-primary"
                >
                  Open Registration Link
                </a>

                <BookmarkIcon
                  v-if="isStudent"
                  size="lg"
                  show-label
                  :active="event.isBookmarked"
                  @toggle="handleBookmark"
                />

                <button
                  v-else-if="!isOrganizer"
                  type="button"
                  class="btn btn-ghost"
                  :class="{ active: event.isBookmarked }"
                  @click="handleBookmark"
                >
                  {{ event.isBookmarked ? '★ Bookmarked' : '☆ Bookmark Event' }}
                </button>
              </template>
            </div>
          </div>
        </div>

        <section v-if="recommended.length" class="section similar-section">
          <h2>Similar Events You Might Like</h2>

          <div class="events-grid">
            <template v-if="isStudent">
              <StudentEventCard
                v-for="rec in recommended"
                :key="rec.id"
                :event="rec"
                @bookmark="toggleBookmark"
              />
            </template>
            <template v-else>
              <EventCard
                v-for="rec in recommended"
                :key="rec.id"
                :event="rec"
                @bookmark="toggleBookmark"
              />
            </template>
          </div>
        </section>

        <div v-if="showRejectModal" class="modal-overlay" @click.self="closeRejectModal">
          <div class="modal">
            <h3>Reject Event</h3>

            <p>
              Please provide a reason before rejecting "{{ event?.title }}".
            </p>

            <textarea
              v-model="rejectReason"
              class="form-input reject-textarea"
              rows="4"
              placeholder="Enter reject reason"
            />

            <p v-if="rejectReasonError" class="form-error">
              {{ rejectReasonError }}
            </p>

            <div class="modal-actions">
              <button type="button" class="btn btn-ghost" @click="closeRejectModal">
                Cancel
              </button>

              <button type="button" class="btn btn-danger" @click="confirmReject">
                Confirm Reject
              </button>
            </div>
          </div>
        </div>
      </template>
    </div>
  </component>
</template>

<style scoped>
.back-link {
  display: inline-flex;
  align-items: center;
  margin-bottom: 1rem;
  font-weight: 500;
  font-size: 0.875rem;
  color: var(--color-primary);
  text-decoration: none;
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
  font-family: inherit;
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

.admin-status-note {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  flex-wrap: wrap;
  padding: 0.875rem 1rem;
  background: var(--color-bg);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
}

.reject-textarea {
  width: 100%;
  resize: vertical;
  min-height: 110px;
  margin-top: 1rem;
}

.form-error {
  color: var(--color-danger);
  font-size: 0.875rem;
  margin-top: 0.5rem;
}

.section h2 {
  font-size: 1.25rem;
  margin-bottom: 1rem;
}

.similar-section {
  margin-top: 3rem;
  padding-top: 2rem;
  border-top: 1px solid var(--color-border);
}

.category-block {
  display: flex;
  flex-direction: column;
  justify-content: center;
  min-width: 88px;
  min-height: 72px;
  padding: 0.625rem 0.875rem;
  border-radius: var(--radius-sm);
  border: 1px solid transparent;
}

.category-block-label {
  font-size: 0.625rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  opacity: 0.75;
  margin-bottom: 0.25rem;
}

.category-block-value {
  font-size: 0.8125rem;
  font-weight: 700;
  line-height: 1.2;
}

.category-block.cat-academic {
  background: #EDE9FE;
  color: #6D28D9;
}

.category-block.cat-cultural {
  background: #FCE7F3;
  color: #BE185D;
}

.category-block.cat-competition {
  background: #DCFCE7;
  color: #15803D;
}

.category-block.cat-workshop {
  background: #DBEAFE;
  color: #1D4ED8;
}

.category-block.cat-sports {
  background: #FFEDD5;
  color: #C2410C;
}

.category-block.cat-default {
  background: var(--color-bg);
  color: var(--color-text-muted);
}

.info-boxes {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.info-box {
  padding: 0.875rem 1rem;
  border-radius: var(--radius-sm);
  border: 1px solid var(--color-border);
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.info-label {
  font-size: 0.6875rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--color-text-muted);
}

.info-value {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-text);
  line-height: 1.4;
}

.info-date {
  background: rgba(139, 30, 63, 0.06);
  border-color: rgba(139, 30, 63, 0.12);
}

.info-time {
  background: #EFF6FF;
  border-color: #BFDBFE;
}

.info-location {
  background: #F0FDF4;
  border-color: #BBF7D0;
}

.info-organizer {
  background: #FFF7ED;
  border-color: #FED7AA;
}

.info-stats {
  background: var(--color-bg);
}

.event-media {
  display: grid;
  gap: 0.875rem;
  margin-bottom: 1.5rem;
}

.event-main-image {
  width: 100%;
  max-height: 360px;
  object-fit: cover;
  border-radius: var(--radius-sm);
  border: 1px solid var(--color-border);
}

.poster-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 0.75rem;
}

.poster-image {
  width: 100%;
  aspect-ratio: 4 / 3;
  object-fit: cover;
  border-radius: var(--radius-sm);
  border: 1px solid var(--color-border);
}

.reject-reason-banner {
  margin-top: 1rem;
  margin-bottom: 1.5rem;
}

.reject-reason-banner strong {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
  color: var(--color-danger);
}

.reject-reason-text {
  font-size: 0.9375rem;
  line-height: 1.5;
  margin: 0;
  color: var(--color-danger);
  opacity: 0.9;
}
</style>
