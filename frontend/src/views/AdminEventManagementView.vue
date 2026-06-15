<script setup>
import { ref, computed } from 'vue'
import ProtectedLayout from '../components/ProtectedLayout.vue'
import StatusBadge from '../components/StatusBadge.vue'
import { useEventStore } from '../composables/useEventStore.js'
import { eventCategories } from '../data/mockEvents.js'

const { events, updateEventStatus, deleteEvent } = useEventStore()

const statusFilter = ref('')
const categoryFilter = ref('')
const organizerFilter = ref('')
const showDeleteModal = ref(false)
const eventToDelete = ref(null)
const successMessage = ref('')

const showRejectModal = ref(false)
const eventToReject = ref(null)
const rejectReason = ref('')
const rejectReasonError = ref('')

const organizers = computed(() => [...new Set(events.value.map((e) => e.organizer))].sort())

const filteredEvents = computed(() => {
  let list = [...events.value]

  if (statusFilter.value) list = list.filter((e) => e.status === statusFilter.value)
  if (categoryFilter.value) list = list.filter((e) => e.category === categoryFilter.value)
  if (organizerFilter.value) list = list.filter((e) => e.organizer === organizerFilter.value)

  return list.sort((a, b) => b.eventDate.localeCompare(a.eventDate))
})

function approve(event) {
  updateEventStatus(event.id, 'Approved', { rejectReason: '' })
  successMessage.value = `"${event.title}" approved.`
  clearSuccess()
}

function openRejectModal(event) {
  eventToReject.value = event
  rejectReason.value = ''
  rejectReasonError.value = ''
  showRejectModal.value = true
}

function closeRejectModal() {
  showRejectModal.value = false
  eventToReject.value = null
  rejectReason.value = ''
  rejectReasonError.value = ''
}

function confirmReject() {
  if (!rejectReason.value.trim()) {
    rejectReasonError.value = 'Reject reason is required.'
    return
  }

  if (!eventToReject.value) return

  updateEventStatus(eventToReject.value.id, 'Rejected', { rejectReason: rejectReason.value.trim() })

  successMessage.value = `"${eventToReject.value.title}" rejected.`
  closeRejectModal()
  clearSuccess()
}

function confirmDelete(event) {
  eventToDelete.value = event
  showDeleteModal.value = true
}

function closeDeleteModal() {
  showDeleteModal.value = false
  eventToDelete.value = null
}

function handleDelete() {
  if (eventToDelete.value) {
    deleteEvent(eventToDelete.value.id)
    successMessage.value = 'Event deleted.'
    clearSuccess()
  }

  closeDeleteModal()
}

function clearSuccess() {
  setTimeout(() => {
    successMessage.value = ''
  }, 3000)
}

function formatDateTime(dateStr) {
  if (!dateStr) return '—'

  const d = new Date(dateStr)
  if (Number.isNaN(d.getTime())) return '—'

  return d.toLocaleString('en-MY', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<template>
  <ProtectedLayout>
    <div class="page-container">
      <div class="page-header">
        <h1>Event Management</h1>
        <p>View and manage all platform events.</p>
      </div>

      <div v-if="successMessage" class="alert alert-success">
        {{ successMessage }}
      </div>

      <div class="filters-row">
        <select v-model="statusFilter" class="form-select">
          <option value="">All statuses</option>
          <option value="Approved">Approved</option>
          <option value="Pending">Pending</option>
          <option value="Rejected">Rejected</option>
          <option value="Draft">Draft</option>
        </select>

        <select v-model="categoryFilter" class="form-select">
          <option value="">All categories</option>
          <option v-for="cat in eventCategories" :key="cat" :value="cat">
            {{ cat }}
          </option>
        </select>

        <select v-model="organizerFilter" class="form-select">
          <option value="">All organizers</option>
          <option v-for="org in organizers" :key="org" :value="org">
            {{ org }}
          </option>
        </select>
      </div>

      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>Title</th>
              <th>Organizer</th>
              <th>Category</th>
              <th>Date</th>
              <th>Status</th>
              <th>Status Updated Date</th>
              <th>Views</th>
              <th>Bookmarks</th>
              <th>Actions</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="event in filteredEvents" :key="event.id">
              <td>{{ event.title }}</td>
              <td>{{ event.organizer }}</td>
              <td>{{ event.category }}</td>
              <td>{{ event.eventDate }}</td>
              <td>
                <StatusBadge :status="event.status" />
              </td>
              <td>{{ formatDateTime(event.statusUpdatedAt) }}</td>
              <td>{{ event.viewsCount }}</td>
              <td>{{ event.bookmarksCount }}</td>
              <td>
                <div class="table-actions">
                  <router-link
                    :to="{ path: `/events/${event.id}`, query: { from: 'admin-events' } }"
                    class="btn btn-ghost btn-sm"
                  >
                    View
                  </router-link>

                  <button
                    v-if="event.status === 'Pending'"
                    type="button"
                    class="btn btn-accent btn-sm"
                    @click="approve(event)"
                  >
                    Approve
                  </button>

                  <button
                    v-if="event.status === 'Pending'"
                    type="button"
                    class="btn btn-ghost btn-sm"
                    @click="openRejectModal(event)"
                  >
                    Reject
                  </button>

                  <button
                    type="button"
                    class="btn btn-danger btn-sm"
                    @click="confirmDelete(event)"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="showRejectModal" class="modal-overlay" @click.self="closeRejectModal">
        <div class="modal">
          <h3>Reject Event</h3>

          <p>
            Please provide a reason before rejecting "{{ eventToReject?.title }}".
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

      <div v-if="showDeleteModal" class="modal-overlay" @click.self="closeDeleteModal">
        <div class="modal">
          <h3>Delete Event?</h3>

          <p>
            Permanently delete "{{ eventToDelete?.title }}"?
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
    </div>
  </ProtectedLayout>
</template>

<style scoped>
.filters-row {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
  margin-bottom: 1.5rem;
}

.filters-row .form-select {
  max-width: 200px;
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
</style>