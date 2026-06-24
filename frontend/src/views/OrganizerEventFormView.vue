<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ProtectedLayout from '../components/ProtectedLayout.vue'
import FormInput from '../components/FormInput.vue'
import { useAuth } from '../composables/useAuth.js'
import { useEventStore } from '../composables/useEventStore.js'

const route = useRoute()
const router = useRouter()
const { user } = useAuth()
const { fetchEvents, fetchCategories, getEventById, addEvent, updateEvent, categories } = useEventStore()

const isEdit = computed(() => !!route.params.id)
const pageTitle = computed(() => (isEdit.value ? 'Update Event' : 'Create Event'))

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
  eventImage: null,
  poster: [],
})

const errors = ref({})
const successMessage = ref('')
const loading = ref(false)
const eventImagePreview = ref('')
const posterPreviews = ref([])
const MAX_POSTERS = 4

const categoryOptions = computed(() => categories.value)

onMounted(async () => {
  await fetchCategories()
  await fetchEvents()

  if (isEdit.value) {
    const event = getEventById(route.params.id)
    if (event) {
      const eventImage = typeof event.eventImage === 'string' ? event.eventImage : null
      const poster = Array.isArray(event.poster)
        ? event.poster.filter((item) => typeof item === 'string')
        : []

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
        eventImage,
        poster,
      }
      eventImagePreview.value = eventImage || ''
      posterPreviews.value = poster
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

async function submit(status) {
  successMessage.value = ''
  if (!validate()) return

  loading.value = true
  try {
    const organizerName = user.value?.organizerName || user.value?.name || 'Unknown Organizer'
    const payload = {
      ...form.value,
      organizerId: user.value?.id,
      organizerEmail: user.value?.email,
      organizer: organizerName,
      status,
    }

    if (isEdit.value) {
      await updateEvent(route.params.id, payload)
      successMessage.value = `Event ${status === 'Draft' ? 'saved as draft' : 'updated'} successfully!`
    } else {
      await addEvent(payload)
      successMessage.value = `Event ${status === 'Draft' ? 'saved as draft' : 'submitted for approval'} successfully!`
    }

    setTimeout(() => router.push('/organizer/events'), 1500)
  } catch (error) {
    const apiErrors = error.response?.data?.errors
    if (apiErrors && typeof apiErrors === 'object') {
      errors.value = apiErrors
    } else {
      errors.value = { form: error.response?.data?.message || 'Failed to save event.' }
    }
  } finally {
    loading.value = false
  }
}

function cancel() {
  router.back()
}

function resizeImage(file, maxWidth = 1200, quality = 0.78) {
  return new Promise((resolve, reject) => {
    const img = new Image()
    const objectUrl = URL.createObjectURL(file)

    img.onload = () => {
      const scale = Math.min(1, maxWidth / img.width)
      const canvas = document.createElement('canvas')
      canvas.width = Math.round(img.width * scale)
      canvas.height = Math.round(img.height * scale)

      const ctx = canvas.getContext('2d')
      if (!ctx) {
        URL.revokeObjectURL(objectUrl)
        reject(new Error('Unable to process image.'))
        return
      }
      ctx.drawImage(img, 0, 0, canvas.width, canvas.height)
      URL.revokeObjectURL(objectUrl)
      resolve(canvas.toDataURL('image/jpeg', quality))
    }

    img.onerror = () => {
      URL.revokeObjectURL(objectUrl)
      reject(new Error('Unable to process image.'))
    }

    img.src = objectUrl
  })
}

async function handleEventImage(event) {
  const file = event.target.files[0]
  if(file) {
    try {
      const dataUrl = await resizeImage(file, 1200, 0.78)
      form.value.eventImage = dataUrl
      eventImagePreview.value = dataUrl
      errors.value.eventImage = ''
    } catch (error) {
      errors.value.eventImage = error.message
    }
  }
}

async function handlePosterUpload(event) {
  const files = Array.from(event.target.files).slice(0, MAX_POSTERS)

  try {
    const dataUrls = await Promise.all(files.map((file) => resizeImage(file, 1000, 0.75)))
    form.value.poster = dataUrls
    posterPreviews.value = dataUrls
    errors.value.poster = ''
  } catch (error) {
    errors.value.poster = error.message
  }
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
      <div v-if="errors.form" class="alert alert-error">{{ errors.form }}</div>

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
            <label class="form-label">Event Pictures</label>
            <input type="file" accept="image/*" @change="handleEventImage" />
            <p v-if="errors.eventImage" class="form-error">{{ errors.eventImage }}</p>
            <img v-if="eventImagePreview" :src="eventImagePreview" alt="Event Preview" class="preview-image" />
            <label class="form-label">Event Posters</label>
            <input type="file" accept="image/*" multiple @change="handlePosterUpload" />
            <p class="form-hint">Images are compressed automatically before submit. Maximum {{ MAX_POSTERS }} posters.</p>
            <p v-if="errors.poster" class="form-error">{{ errors.poster }}</p>
            <div v-if="posterPreviews.length" class="poster-previews-grid">
              <img v-for="(poster, index) in posterPreviews" :key="index" :src="poster" alt="Poster Preview" class="preview-image" />
            </div>
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
              {{ isEdit ? 'Update Event' : 'Submit Event' }}
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

.form-group {
  margin-bottom: 1rem;
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

.preview-image {
  width: 120px;
  height: auto;
  margin-top: 0.75rem;
  border-radius: 8px;
  border: 1px solid var(--color-border);
}

.poster-previews-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-top: 0.75rem;
}

.required {
  color: var(--color-danger);
}
</style>
