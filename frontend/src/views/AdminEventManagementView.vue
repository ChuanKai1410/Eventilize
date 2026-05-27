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

const organizers = computed(() => [...new Set(events.value.map((e) => e.organizer))].sort())

const filteredEvents = computed(() => {
  let list = [...events.value]
  if (statusFilter.value) list = list.filter((e) => e.status === statusFilter.value)
  if (categoryFilter.value) list = list.filter((e) => e.category === categoryFilter.value)
  if (organizerFilter.value) list = list.filter((e) => e.organizer === organizerFilter.value)
  return list.sort((a, b) => b.eventDate.localeCompare(a.eventDate))
})

function approve(event) {
  updateEventStatus(event.id, 'Approved')
  successMessage.value = `"${event.title}" approved.`
  clearSuccess()
}

function reject(event) {
  updateEventStatus(event.id, 'Rejected')
  successMessage.value = `"${event.title}" rejected.`
  clearSuccess()
}

function confirmDelete(event) {
  eventToDelete.value = event
  showDeleteModal.value = true
}

function handleDelete() {
  if (eventToDelete.value) {
    deleteEvent(eventToDelete.value.id)
    successMessage.value = 'Event deleted.'
    clearSuccess()
  }
  showDeleteModal.value = false
  eventToDelete.value = null
}

function clearSuccess() {
  setTimeout(() => {
    successMessage.value = ''
  }, 3000)
}
</script>

<template>
  <ProtectedLayout>
    <div class="page-container">
      <div class="page-header">
        <h1>Event Management</h1>
        <p>View and manage all platform events.</p>
      </div>

      <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>

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
          <option v-for="cat in eventCategories" :key="cat" :value="cat">{{ cat }}</option>
        </select>
        <select v-model="organizerFilter" class="form-select">
          <option value="">All organizers</option>
          <option v-for="org in organizers" :key="org" :value="org">{{ org }}</option>
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
              <td><StatusBadge :status="event.status" /></td>
              <td>{{ event.viewsCount }}</td>
              <td>{{ event.bookmarksCount }}</td>
              <td>
                <div class="table-actions">
                  <router-link :to="`/events/${event.id}`" class="btn btn-ghost btn-sm">View</router-link>
                  <button
                    v-if="event.status !== 'Approved'"
                    type="button"
                    class="btn btn-accent btn-sm"
                    @click="approve(event)"
                  >
                    Approve
                  </button>
                  <button
                    v-if="event.status !== 'Rejected'"
                    type="button"
                    class="btn btn-ghost btn-sm"
                    @click="reject(event)"
                  >
                    Reject
                  </button>
                  <button type="button" class="btn btn-danger btn-sm" @click="confirmDelete(event)">
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
        <div class="modal">
          <h3>Delete Event?</h3>
          <p>Permanently delete "{{ eventToDelete?.title }}"?</p>
          <div class="modal-actions">
            <button type="button" class="btn btn-ghost" @click="showDeleteModal = false">Cancel</button>
            <button type="button" class="btn btn-danger" @click="handleDelete">Delete</button>
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
</style>
