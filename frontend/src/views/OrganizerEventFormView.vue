<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ProtectedLayout from '../components/ProtectedLayout.vue'
import FormInput from '../components/FormInput.vue'
import { useAuth } from '../composables/useAuth.js'
import { useEventStore } from '../composables/useEventStore.js'
import { eventCategories } from '../data/mockEvents.js'

const route = useRoute()
const router = useRouter()
const { user } = useAuth()
const { getEventById, addEvent, updateEvent, categories } = useEventStore()

const isEdit = computed(() => !!route.params.id)
const pageTitle = computed(() => (isEdit.value ? 'Edit Event' : 'Create Event'))

const form = ref({
  title: '',
  description: '',
  category: '',
  eventDate: '',
  startTime: '',
  endTime: '',
  location: '',
  mapLink: '',
  registrationLink: '',
})

const errors = ref({})
const successMessage = ref('')
const loading = ref(false)

const categoryOptions = computed(() => categories.value)

onMounted(() => {
  if (isEdit.value) {
    const event = getEventById(route.params.id)
    if (event) {
      form.value = {
        title: event.title,
        description: event.description,
        category: event.category,
        eventDate: event.eventDate,
        startTime: event.startTime,
        endTime: event.endTime,
        location: event.location,
        mapLink: event.mapLink || '',
        registrationLink: event.registrationLink || '',
      }
    }
  }
})

function validate() {
  errors.value = {}
  if (!form.value.title.trim()) errors.value.title = 'Event title is required.'
  if (!form.value.description.trim()) errors.value.description = 'Description is required.'
  if (!form.value.category) errors.value.category = 'Category is required.'
  if (!form.value.eventDate) errors.value.eventDate = 'Event date is required.'
  if (!form.value.startTime) errors.value.startTime = 'Start time is required.'
  if (!form.value.endTime) errors.value.endTime = 'End time is required.'
  else if (form.value.endTime <= form.value.startTime) {
    errors.value.endTime = 'End time must be after start time.'
  }
  if (!form.value.location.trim()) errors.value.location = 'Location is required.'
  return Object.keys(errors.value).length === 0
}

function submit(status) {
  successMessage.value = ''
  if (!validate()) return

  loading.value = true
  setTimeout(() => {
    const organizerName = user.value?.organizerName || user.value?.name || 'Unknown Organizer'
    const payload = {
      ...form.value,
      organizer: organizerName,
      status,
    }

    if (isEdit.value) {
      updateEvent(route.params.id, payload)
      successMessage.value = `Event ${status === 'Draft' ? 'saved as draft' : 'updated'} successfully!`
    } else {
      addEvent(payload)
      successMessage.value = `Event ${status === 'Draft' ? 'saved as draft' : 'submitted for approval'} successfully!`
    }

    loading.value = false
    setTimeout(() => router.push('/organizer/events'), 1500)
  }, 500)
}

function cancel() {
  router.push('/organizer/events')
}
</script>

<template>
  <ProtectedLayout>
    <div class="page-container form-page">
      <div class="page-header">
        <h1>{{ pageTitle }}</h1>
        <p>{{ isEdit ? 'Update your event details.' : 'Fill in the details to create a new campus event.' }}</p>
      </div>

      <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>

      <form class="event-form card" @submit.prevent>
        <div class="card-body">
          <FormInput
            id="event-title"
            v-model="form.title"
            label="Event Title"
            placeholder="Enter event title"
            required
            :error="errors.title"
          />
          <FormInput
            id="event-desc"
            v-model="form.description"
            label="Description"
            type="textarea"
            placeholder="Describe your event"
            required
            :error="errors.description"
          />
          <div class="form-group">
            <label class="form-label">Category <span class="required">*</span></label>
            <select v-model="form.category" class="form-select" :class="{ error: errors.category }">
              <option value="">Select category</option>
              <option v-for="cat in categoryOptions" :key="cat" :value="cat">{{ cat }}</option>
            </select>
            <p v-if="errors.category" class="form-error">{{ errors.category }}</p>
          </div>
          <div class="form-row">
            <FormInput
              id="event-date"
              v-model="form.eventDate"
              label="Event Date"
              type="date"
              required
              :error="errors.eventDate"
            />
            <FormInput
              id="start-time"
              v-model="form.startTime"
              label="Start Time"
              type="time"
              required
              :error="errors.startTime"
            />
            <FormInput
              id="end-time"
              v-model="form.endTime"
              label="End Time"
              type="time"
              required
              :error="errors.endTime"
            />
          </div>
          <FormInput
            id="event-location"
            v-model="form.location"
            label="Location"
            placeholder="Venue name, UTM"
            required
            :error="errors.location"
          />
          <FormInput
            id="map-link"
            v-model="form.mapLink"
            label="Google Maps Link"
            type="url"
            placeholder="https://maps.google.com/..."
          />
          <FormInput
            id="reg-link"
            v-model="form.registrationLink"
            label="Registration Link"
            type="url"
            placeholder="https://forms.utm.my/..."
          />

          <div class="form-actions">
            <button type="button" class="btn btn-ghost" :disabled="loading" @click="submit('Draft')">
              Save as Draft
            </button>
            <button type="button" class="btn btn-accent" :disabled="loading" @click="submit('Pending')">
              Submit Event
            </button>
            <button type="button" class="btn btn-outline" @click="cancel">Cancel</button>
          </div>
        </div>
      </form>
    </div>
  </ProtectedLayout>
</template>

<style scoped>
.form-page {
  max-width: 720px;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 1rem;
}

.form-actions {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid var(--color-border);
}

.required {
  color: var(--color-danger);
}
</style>
