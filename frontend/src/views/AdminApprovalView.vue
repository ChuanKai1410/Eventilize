<script setup>
import { ref, computed, onMounted } from 'vue'
import ProtectedLayout from '../components/ProtectedLayout.vue'
import StatusBadge from '../components/StatusBadge.vue'
import EmptyState from '../components/EmptyState.vue'
import { useEventStore } from '../composables/useEventStore.js'

const { events, fetchEvents, updateEventStatus } = useEventStore()

onMounted(() => {
  fetchEvents()
})

const pendingEvents = computed(() =>
  events.value.filter((e) => e.status === 'Pending')
)

const showModal = ref(false)
const modalAction = ref('')
const selectedEvent = ref(null)
const successMessage = ref('')
const rejectReason = ref('')
const rejectReasonError = ref('')

function openModal(event, action) {
  selectedEvent.value = event
  modalAction.value = action
  rejectReason.value = ''
  rejectReasonError.value = ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  selectedEvent.value = null
  modalAction.value = ''
  rejectReason.value = ''
  rejectReasonError.value = ''
}

async function confirmAction() {
  if (!selectedEvent.value) return

  if (modalAction.value === 'reject' && !rejectReason.value.trim()) {
    rejectReasonError.value = 'Reject reason is required.'
    return
  }

  const status = modalAction.value === 'approve' ? 'Approved' : 'Rejected'
  const extraFields = {
    rejectReason: modalAction.value === 'reject' ? rejectReason.value.trim() : ''
  }

  await updateEventStatus(selectedEvent.value.id, status, extraFields)

  successMessage.value =
    modalAction.value === 'approve'
      ? `Event "${selectedEvent.value.title}" has been approved.`
      : `Event "${selectedEvent.value.title}" has been rejected.`

  closeModal()

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

      <div v-if="successMessage" class="alert alert-success">
        {{ successMessage }}
      </div>

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
              <td>
                <StatusBadge :status="event.status" />
              </td>
              <td>
                <div class="table-actions">
                  <router-link
                    :to="{ path: `/events/${event.id}`, query: { from: 'admin-approval' } }"
                    class="btn btn-ghost btn-sm"
                  >
                    View
                  </router-link>

                  <button
                    type="button"
                    class="btn btn-success btn-sm"
                    @click="openModal(event, 'approve')"
                  >
                    Approve
                  </button>

                  <button
                    type="button"
                    class="btn btn-danger btn-sm"
                    @click="openModal(event, 'reject')"
                  >
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

      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal">
          <h3>{{ modalAction === 'approve' ? 'Approve Event' : 'Reject Event' }}</h3>

          <p>
            Are you sure you want to {{ modalAction }} "{{ selectedEvent?.title }}"?
          </p>

          <div v-if="modalAction === 'reject'" class="reject-form">
            <label for="rejectReason" class="form-label">Reject Reason</label>

            <textarea
              id="rejectReason"
              v-model="rejectReason"
              class="form-input reject-textarea"
              rows="4"
              placeholder="Enter reject reason"
            />

            <p v-if="rejectReasonError" class="form-error">
              {{ rejectReasonError }}
            </p>
          </div>

          <div class="modal-actions">
            <button type="button" class="btn btn-ghost" @click="closeModal">
              Cancel
            </button>

            <button
              type="button"
              :class="modalAction === 'approve' ? 'btn btn-success' : 'btn btn-danger'"
              @click="confirmAction"
            >
              {{ modalAction === 'approve' ? 'Approve' : 'Confirm Reject' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </ProtectedLayout>
</template>

<style scoped>
.reject-form {
  margin-top: 1rem;
}

.reject-textarea {
  width: 100%;
  resize: vertical;
  min-height: 110px;
}

.form-error {
  color: var(--color-danger);
  font-size: 0.875rem;
  margin-top: 0.5rem;
}
</style>
