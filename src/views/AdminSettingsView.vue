<script setup>
import { ref } from 'vue'
import ProtectedLayout from '../components/ProtectedLayout.vue'
import { useEventStore } from '../composables/useEventStore.js'

const { categories, addCategory } = useEventStore()

const newCategory = ref('')
const categoryMessage = ref('')
const showAdminProfile = ref(false)

const adminProfile = ref({
  name: 'Platform Admin',
  email: 'admin@utm.my',
  role: 'Administrator',
  accountType: 'Admin Account',
  accountStatus: 'Active',
  joinedDate: '2026-01-01',
})

const notificationSettings = ref({
  pendingApprovalAlert: true,
  eventReportAlert: true,
  organizerSubmissionAlert: true,
  systemAlert: true,
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

function deleteCategory(cat) {
  const confirmed = window.confirm(`Are you sure you want to delete "${cat}"?`)

  if (!confirmed) return

  const index = categories.value.indexOf(cat)

  if (index !== -1) {
    categories.value.splice(index, 1)
    categoryMessage.value = `Category "${cat}" deleted.`
  }
}
</script>

<template>
  <ProtectedLayout>
    <div class="page-container settings-page">
      <div class="page-header">
        <h1>Platform Settings</h1>
        <p>Manage categories, notifications, admin profile, and platform quality.</p>
      </div>

      <div class="settings-grid">
        <section class="card">
          <div class="card-body">
            <div class="profile-header">
              <div>
                <h2>Admin Profile</h2>
                <p class="section-desc">View administrator account details.</p>
              </div>

              <button
                type="button"
                class="btn btn-ghost btn-sm"
                @click="showAdminProfile = !showAdminProfile"
              >
                {{ showAdminProfile ? 'Hide Profile' : 'View Profile' }}
              </button>
            </div>

            <div v-if="showAdminProfile" class="profile-details">
              <div class="profile-detail">
                <strong>Name</strong>
                <span>{{ adminProfile.name }}</span>
              </div>

              <div class="profile-detail">
                <strong>Email</strong>
                <span>{{ adminProfile.email }}</span>
              </div>

              <div class="profile-detail">
                <strong>Role</strong>
                <span>{{ adminProfile.role }}</span>
              </div>

              <div class="profile-detail">
                <strong>Account Type</strong>
                <span>{{ adminProfile.accountType }}</span>
              </div>

              <div class="profile-detail">
                <strong>Status</strong>
                <span>{{ adminProfile.accountStatus }}</span>
              </div>

              <div class="profile-detail">
                <strong>Joined Date</strong>
                <span>{{ adminProfile.joinedDate }}</span>
              </div>
            </div>
          </div>
        </section>

        <section class="card">
          <div class="card-body">
            <h2>Category Management</h2>
            <p class="section-desc">Add, view, and remove event categories available to organizers.</p>

            <div class="add-category">
              <input
                v-model="newCategory"
                type="text"
                class="form-input"
                placeholder="New category name"
                @keyup.enter="addNewCategory"
              />

              <button type="button" class="btn btn-accent" @click="addNewCategory">
                Add Category
              </button>
            </div>

            <p
              v-if="categoryMessage"
              class="form-hint"
              :class="{
                success: categoryMessage.includes('added') || categoryMessage.includes('deleted')
              }"
            >
              {{ categoryMessage }}
            </p>

            <ul class="category-list">
              <li v-for="cat in categories" :key="cat">
                <div class="category-info">
                  <span>{{ cat }}</span>
                  <span class="cat-badge">Active</span>
                </div>

                <button
                  type="button"
                  class="category-delete-btn"
                  @click="deleteCategory(cat)"
                >
                  Delete
                </button>
              </li>
            </ul>
          </div>
        </section>

        <section class="card">
          <div class="card-body">
            <h2>Notification Settings</h2>
            <p class="section-desc">Configure admin-relevant notification defaults.</p>

            <div class="toggle-row">
              <span>Pending Approval Alert</span>
              <label class="toggle-switch">
                <input v-model="notificationSettings.pendingApprovalAlert" type="checkbox" />
                <span class="toggle-slider" />
              </label>
            </div>

            <div class="toggle-row">
              <span>Event Report Alert</span>
              <label class="toggle-switch">
                <input v-model="notificationSettings.eventReportAlert" type="checkbox" />
                <span class="toggle-slider" />
              </label>
            </div>

            <div class="toggle-row">
              <span>Organizer Submission Alert</span>
              <label class="toggle-switch">
                <input v-model="notificationSettings.organizerSubmissionAlert" type="checkbox" />
                <span class="toggle-slider" />
              </label>
            </div>

            <div class="toggle-row">
              <span>System Alert</span>
              <label class="toggle-switch">
                <input v-model="notificationSettings.systemAlert" type="checkbox" />
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

.profile-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  flex-wrap: wrap;
}

.profile-details {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 1rem;
  padding: 1rem;
  background: var(--color-bg);
  border-radius: var(--radius-sm);
}

.profile-detail {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.profile-detail strong {
  font-size: 0.75rem;
  color: var(--color-text-muted);
  text-transform: uppercase;
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
  gap: 1rem;
  padding: 0.625rem 0;
  border-bottom: 1px solid var(--color-border);
}

.category-info {
  display: flex;
  align-items: center;
  gap: 0.625rem;
}

.cat-badge {
  font-size: 0.6875rem;
  padding: 0.2rem 0.5rem;
  background: #DCFCE7;
  color: #15803D;
  border-radius: var(--radius-pill);
  font-weight: 600;
}

.category-delete-btn {
  border: 1px solid #fecaca;
  background: #fef2f2;
  color: var(--color-danger);
  border-radius: var(--radius-pill);
  padding: 0.25rem 0.625rem;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition);
}

.category-delete-btn:hover {
  background: #fee2e2;
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