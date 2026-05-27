<script setup>
import { ref, computed } from 'vue'
import ProtectedLayout from '../components/ProtectedLayout.vue'
import StatusBadge from '../components/StatusBadge.vue'
import EmptyState from '../components/EmptyState.vue'
import { useEventStore } from '../composables/useEventStore.js'

const { events, updateEventStatus } = useEventStore()

const pendingEvents = computed(() =>
  events.value.filter((e) => e.status === 'Pending')
)

const showModal = ref(false)
const modalAction = ref('')
const selectedEvent = ref(null)
const successMessage = ref('')

function openModal(event, action) {
  selectedEvent.value = event
  modalAction.value = action
  showModal.value = true
}

function confirmAction() {
  if (!selectedEvent.value) return
  const status = modalAction.value === 'approve' ? 'Approved' : 'Rejected'
  updateEventStatus(selectedEvent.value.id, status)
  successMessage.value = `Event "${selectedEvent.value.title}" has been ${status.toLowerCase()}.`
  showModal.value = false
  selectedEvent.value = null
  setTimeout(() => {
    successMessage.value = ''
  }, 3000)
}
</script>

<template>
  <ProtectedLayout>
    <div class="page-container">
      <div class="page-header">
        <h1>Event Approvals</h1>
        <p>Review and approve submitted campus events.</p>
      </div>

      <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>

      <div v-if="pendingEvents.length" class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>Event Title</th>
              <th>Organizer</th>
              <th>Category</th>
              <th>Date</th>
              <th>Location</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="event in pendingEvents" :key="event.id">
              <td>{{ event.title }}</td>
              <td>{{ event.organizer }}</td>
              <td>{{ event.category }}</td>
              <td>{{ event.eventDate }}</td>
              <td>{{ event.location }}</td>
              <td><StatusBadge :status="event.status" /></td>
              <td>
                <div class="table-actions">
                  <router-link :to="`/events/${event.id}`" class="btn btn-ghost btn-sm">View</router-link>
                  <button type="button" class="btn btn-accent btn-sm" @click="openModal(event, 'approve')">
                    Approve
                  </button>
                  <button type="button" class="btn btn-danger btn-sm" @click="openModal(event, 'reject')">
                    Reject
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <EmptyState
        v-else
        title="No pending approvals"
        description="All submitted events have been reviewed."
      />

      <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
        <div class="modal">
          <h3>{{ modalAction === 'approve' ? 'Approve Event?' : 'Reject Event?' }}</h3>
          <p>
            Are you sure you want to {{ modalAction }} "{{ selectedEvent?.title }}"?
          </p>
          <div class="modal-actions">
            <button type="button" class="btn btn-ghost" @click="showModal = false">Cancel</button>
            <button
              type="button"
              :class="modalAction === 'approve' ? 'btn btn-accent' : 'btn btn-danger'"
              @click="confirmAction"
            >
              {{ modalAction === 'approve' ? 'Approve' : 'Reject' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </ProtectedLayout>
</template>
