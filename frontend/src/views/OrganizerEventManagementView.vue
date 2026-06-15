<script setup>
import { ref, computed } from 'vue'
import ProtectedLayout from '../components/ProtectedLayout.vue'
import StatusBadge from '../components/StatusBadge.vue'
import EmptyState from '../components/EmptyState.vue'
import { useAuth } from '../composables/useAuth.js'
import { useEventStore } from '../composables/useEventStore.js'

const { user } = useAuth()
const { events, deleteEvent } = useEventStore()

const statusFilter = ref('')
const showDeleteModal = ref(false)
const eventToDelete = ref(null)

const organizerName = computed(() => user.value?.organizerName || 'Computing Students Society')

const myEvents = computed(() => {
  let list = events.value.filter((e) => e.organizer === organizerName.value)
  if (statusFilter.value) {
    list = list.filter((e) => e.status === statusFilter.value)
  }
  return list.sort((a, b) => b.eventDate.localeCompare(a.eventDate))
})

function confirmDelete(event) {
  eventToDelete.value = event
  showDeleteModal.value = true
}

function handleDelete() {
  if (eventToDelete.value) {
    deleteEvent(eventToDelete.value.id)
  }
  showDeleteModal.value = false
  eventToDelete.value = null
}
</script>

<template>
  <ProtectedLayout>
    <div class="page-container">
      <div class="page-header">
        <h1>Event Management</h1>
        <p>View and manage all your submitted events.</p>
      </div>

      <div class="toolbar">
        <select v-model="statusFilter" class="form-select filter-select">
          <option value="">All statuses</option>
          <option value="Draft">Draft</option>
          <option value="Pending">Pending</option>
          <option value="Approved">Approved</option>
          <option value="Rejected">Rejected</option>
        </select>
        <router-link to="/organizer/events/create" class="btn btn-accent btn-sm">Create New Event</router-link>
      </div>

      <div v-if="myEvents.length" class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>Title</th>
              <th>Category</th>
              <th>Date</th>
              <th>Status</th>
              <th>Views</th>
              <th>Bookmarks</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="event in myEvents" :key="event.id">
              <td>{{ event.title }}</td>
              <td>{{ event.category }}</td>
              <td>{{ event.eventDate }}</td>
              <td><StatusBadge :status="event.status" /></td>
              <td>{{ event.viewsCount }}</td>
              <td>{{ event.bookmarksCount }}</td>
              <td>
                <div class="table-actions">
                  <router-link
                    :to="{ path: `/events/${event.id}`, query: { from: 'organizer-events' } }"
                    class="btn btn-ghost btn-sm"
                  >
                    View
                  </router-link>
                  <router-link :to="`/organizer/events/${event.id}/edit`" class="btn btn-ghost btn-sm">Edit</router-link>
                  <button type="button" class="btn btn-danger btn-sm" @click="confirmDelete(event)">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <EmptyState
        v-else
        title="No events found"
        description="Create your first event or adjust the status filter."
        action-label="Create event"
        @action="$router.push('/organizer/events/create')"
      />

      <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
        <div class="modal">
          <h3>Delete Event?</h3>
          <p>Are you sure you want to delete "{{ eventToDelete?.title }}"? This action cannot be undone.</p>
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
.toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.table-actions {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.filter-select {
  max-width: 200px;
}
</style>
