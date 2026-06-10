<script setup>
import { computed } from 'vue'
import StudentProtectedLayout from '../components/student/StudentProtectedLayout.vue'
import { useAuth } from '../composables/useAuth.js'
import { useRegisteredEvents } from '../composables/useRegisteredEvents.js'
import { useEventStore } from '../composables/useEventStore.js'

const { user } = useAuth()
const { registeredEvents } = useRegisteredEvents()
const { bookmarkedEvents } = useEventStore()

const memberSince = computed(() => 'January 2026')

const profileDetails = computed(() => [
  { label: 'Full Name', value: user.value?.name || '—' },
  { label: 'Email', value: user.value?.email || '—' },
  { label: 'Role', value: 'Student' },
  { label: 'Faculty', value: 'Faculty of Computing' },
  { label: 'Member Since', value: memberSince.value },
])
</script>

<template>
  <StudentProtectedLayout>
    <div class="page-container profile-page">
      <div class="page-header">
        <h1>My Profile</h1>
        <p>View and manage your Eventilize account details.</p>
      </div>

      <div class="profile-grid">
        <section class="profile-card card">
          <div class="card-body profile-hero">
            <div class="profile-avatar">
              {{ user?.name?.charAt(0)?.toUpperCase() || 'S' }}
            </div>
            <div>
              <h2>{{ user?.name }}</h2>
              <p class="profile-email">{{ user?.email }}</p>
              <span class="role-badge">Student</span>
            </div>
          </div>
        </section>

        <section class="profile-card card">
          <div class="card-body">
            <h3>Account Details</h3>
            <dl class="detail-list">
              <div v-for="item in profileDetails" :key="item.label" class="detail-row">
                <dt>{{ item.label }}</dt>
                <dd>{{ item.value }}</dd>
              </div>
            </dl>
          </div>
        </section>

        <section class="profile-card card">
          <div class="card-body">
            <h3>Activity Summary</h3>
            <div class="summary-stats">
              <div class="summary-stat">
                <span class="stat-value">{{ registeredEvents.length }}</span>
                <span class="stat-label">Registered Events</span>
              </div>
              <div class="summary-stat">
                <span class="stat-value">{{ bookmarkedEvents.length }}</span>
                <span class="stat-label">Bookmarked Events</span>
              </div>
            </div>
          </div>
        </section>
      </div>

      <div class="profile-actions">
        <router-link to="/student/dashboard" class="btn btn-outline">Back to Dashboard</router-link>
      </div>
    </div>
  </StudentProtectedLayout>
</template>

<style scoped>
.profile-page {
  max-width: 880px;
}

.profile-grid {
  display: grid;
  gap: 1.25rem;
}

.profile-hero {
  display: flex;
  align-items: center;
  gap: 1.25rem;
}

.profile-avatar {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: var(--color-primary);
  color: #fff;
  font-size: 1.75rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.profile-hero h2 {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.profile-email {
  color: var(--color-text-muted);
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

.role-badge {
  display: inline-block;
  padding: 0.2rem 0.625rem;
  font-size: 0.6875rem;
  font-weight: 600;
  border-radius: var(--radius-pill);
  background: var(--color-maroon-tint);
  color: var(--color-primary);
}

.profile-card h3 {
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

.detail-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid var(--color-border);
}

.detail-row:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.detail-row dt {
  font-size: 0.8125rem;
  color: var(--color-text-muted);
  font-weight: 500;
}

.detail-row dd {
  font-size: 0.875rem;
  font-weight: 600;
  text-align: right;
}

.summary-stats {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.summary-stat {
  padding: 1rem;
  background: var(--color-bg);
  border-radius: var(--radius-sm);
  text-align: center;
}

.stat-value {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--color-primary);
}

.stat-label {
  font-size: 0.8125rem;
  color: var(--color-text-muted);
}

.profile-actions {
  margin-top: 1.5rem;
}
</style>
