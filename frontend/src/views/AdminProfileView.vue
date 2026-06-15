<script setup>
import { computed } from 'vue'
import ProtectedLayout from '../components/ProtectedLayout.vue'
import { useAuth } from '../composables/useAuth.js'
import { useEventStore } from '../composables/useEventStore.js'

const { user } = useAuth()
const { events } = useEventStore()

const memberSince = computed(() => 'January 2026')

const stats = computed(() => ({
  total: events.value.length,
  pending: events.value.filter((e) => e.status === 'Pending').length,
  approved: events.value.filter((e) => e.status === 'Approved').length,
  rejected: events.value.filter((e) => e.status === 'Rejected').length,
}))

const profileDetails = computed(() => [
  { label: 'Full Name', value: user.value?.name || 'Platform Admin' },
  { label: 'Email', value: user.value?.email || 'admin@utm.my' },
  { label: 'Role', value: 'Administrator' },
  { label: 'Account Type', value: 'Admin Account' },
  { label: 'Account Status', value: 'Active' },
  { label: 'Member Since', value: memberSince.value },
])
</script>

<template>
  <ProtectedLayout>
    <div class="page-container profile-page">
      <div class="profile-actions-top">
        <router-link to="/admin/dashboard" class="btn-back">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Back to Dashboard
        </router-link>
      </div>

      <div class="page-header">
        <h1>My Profile</h1>
        <p>View and manage your Eventilize administrator account details.</p>
      </div>

      <div class="profile-grid">
        <section class="profile-card card">
          <div class="card-body profile-hero">
            <div class="profile-avatar">
              {{ user?.name?.charAt(0)?.toUpperCase() || 'A' }}
            </div>
            <div>
              <h2>{{ user?.name || 'Platform Admin' }}</h2>
              <p class="profile-email">{{ user?.email || 'admin@utm.my' }}</p>
              <span class="role-badge">Administrator</span>
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
            <h3>Platform Summary</h3>
            <div class="summary-stats">
              <div class="summary-stat">
                <span class="stat-value">{{ stats.total }}</span>
                <span class="stat-label">Total Events</span>
              </div>
              <div class="summary-stat">
                <span class="stat-value">{{ stats.pending }}</span>
                <span class="stat-label">Pending Events</span>
              </div>
              <div class="summary-stat">
                <span class="stat-value">{{ stats.approved }}</span>
                <span class="stat-label">Approved Events</span>
              </div>
              <div class="summary-stat">
                <span class="stat-value">{{ stats.rejected }}</span>
                <span class="stat-label">Rejected Events</span>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </ProtectedLayout>
</template>

<style scoped>
.profile-page {
  max-width: 880px;
}

.profile-actions-top {
  margin-bottom: 1rem;
}

.btn-back {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--color-text-muted);
  transition: var(--transition);
}

.btn-back:hover {
  color: var(--color-primary);
  text-decoration: none;
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
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
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
</style>
