<script setup>
import { ref } from 'vue'
import ProtectedLayout from '../components/ProtectedLayout.vue'
import { useEventStore } from '../composables/useEventStore.js'

const { categories, addCategory } = useEventStore()

const newCategory = ref('')
const categoryMessage = ref('')

const notificationSettings = ref({
  newEvent: true,
  eventApproved: true,
  eventReminder: true,
  registrationReminder: false,
  weeklyDigest: true,
})

function addNewCategory() {
  categoryMessage.value = ''
  if (!newCategory.value.trim()) {
    categoryMessage.value = 'Please enter a category name.'
    return
  }
  if (categories.value.includes(newCategory.value.trim())) {
    categoryMessage.value = 'Category already exists.'
    return
  }
  addCategory(newCategory.value.trim())
  categoryMessage.value = `Category "${newCategory.value.trim()}" added.`
  newCategory.value = ''
}
</script>

<template>
  <ProtectedLayout>
    <div class="page-container settings-page">
      <div class="page-header">
        <h1>Platform Settings</h1>
        <p>Manage categories, notifications, and platform quality.</p>
      </div>

      <div class="settings-grid">
        <section class="card">
          <div class="card-body">
            <h2>Category Management</h2>
            <p class="section-desc">Add and manage event categories available to organizers.</p>

            <div class="add-category">
              <input
                v-model="newCategory"
                type="text"
                class="form-input"
                placeholder="New category name"
                @keyup.enter="addNewCategory"
              />
              <button type="button" class="btn btn-accent" @click="addNewCategory">Add Category</button>
            </div>
            <p v-if="categoryMessage" class="form-hint" :class="{ success: categoryMessage.includes('added') }">
              {{ categoryMessage }}
            </p>

            <ul class="category-list">
              <li v-for="cat in categories" :key="cat">
                <span>{{ cat }}</span>
                <span class="cat-badge">Active</span>
              </li>
            </ul>
          </div>
        </section>

        <section class="card">
          <div class="card-body">
            <h2>Notification Settings</h2>
            <p class="section-desc">Configure system notification defaults (mock toggles).</p>

            <div class="toggle-row">
              <span>New event notifications</span>
              <label class="toggle-switch">
                <input v-model="notificationSettings.newEvent" type="checkbox" />
                <span class="toggle-slider" />
              </label>
            </div>
            <div class="toggle-row">
              <span>Event approval notifications</span>
              <label class="toggle-switch">
                <input v-model="notificationSettings.eventApproved" type="checkbox" />
                <span class="toggle-slider" />
              </label>
            </div>
            <div class="toggle-row">
              <span>Upcoming event reminders</span>
              <label class="toggle-switch">
                <input v-model="notificationSettings.eventReminder" type="checkbox" />
                <span class="toggle-slider" />
              </label>
            </div>
            <div class="toggle-row">
              <span>Registration deadline reminders</span>
              <label class="toggle-switch">
                <input v-model="notificationSettings.registrationReminder" type="checkbox" />
                <span class="toggle-slider" />
              </label>
            </div>
            <div class="toggle-row">
              <span>Weekly digest emails</span>
              <label class="toggle-switch">
                <input v-model="notificationSettings.weeklyDigest" type="checkbox" />
                <span class="toggle-slider" />
              </label>
            </div>
          </div>
        </section>

        <section class="card quality-card">
          <div class="card-body">
            <h2>Platform Quality</h2>
            <p class="section-desc">
              Eventilize maintains platform quality through admin approval workflows,
              category standards, and engagement monitoring. Events must be reviewed
              before appearing in student discovery feeds.
            </p>
            <div class="quality-stats">
              <div class="quality-stat">
                <strong>Approval Rate</strong>
                <span>87%</span>
              </div>
              <div class="quality-stat">
                <strong>Avg. Review Time</strong>
                <span>1.2 days</span>
              </div>
              <div class="quality-stat">
                <strong>Active Categories</strong>
                <span>{{ categories.length }}</span>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </ProtectedLayout>
</template>

<style scoped>
.settings-page {
  max-width: 900px;
}

.settings-grid {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.section-desc {
  color: var(--color-text-muted);
  font-size: 0.9375rem;
  margin-bottom: 1rem;
}

.card h2 {
  font-size: 1.125rem;
  margin-bottom: 0.25rem;
}

.add-category {
  display: flex;
  gap: 0.75rem;
  margin-bottom: 0.5rem;
  flex-wrap: wrap;
}

.add-category .form-input {
  flex: 1;
  min-width: 200px;
}

.form-hint.success {
  color: var(--color-success);
}

.category-list {
  list-style: none;
  margin-top: 1rem;
}

.category-list li {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.625rem 0;
  border-bottom: 1px solid var(--color-border);
}

.cat-badge {
  font-size: 0.6875rem;
  padding: 0.2rem 0.5rem;
  background: #DCFCE7;
  color: #15803D;
  border-radius: var(--radius-pill);
  font-weight: 600;
}

.quality-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
  padding: 1rem;
  background: var(--color-bg);
  border-radius: var(--radius-sm);
}

.quality-stat {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.quality-stat strong {
  font-size: 0.75rem;
  color: var(--color-text-muted);
  text-transform: uppercase;
}

.quality-stat span {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--color-primary);
}
</style>
