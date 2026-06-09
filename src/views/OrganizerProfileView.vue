<script setup>
import ProtectedLayout from '../components/ProtectedLayout.vue'
import { useAuth } from '../composables/useAuth.js'

const { user } = useAuth()
</script>

<template>
  <ProtectedLayout>
    <div class="page-container profile-page">
      <div class="page-header">
        <h1>Organizer Profile</h1>
        <p>View your account details and organization information.</p>
      </div>

      <div class="profile-card card">
        <div class="card-body">
          <div class="profile-header">
            <div class="profile-avatar">
              {{ user?.name ? user.name.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase() : 'OR' }}
            </div>
            <div class="profile-title">
              <h2>{{ user?.name || 'Event Organizer' }}</h2>
              <span class="profile-role-badge">Organizer</span>
            </div>
          </div>

          <div class="profile-details">
            <div class="detail-row">
              <span class="detail-label">Name</span>
              <span class="detail-value">{{ user?.name || 'N/A' }}</span>
            </div>

            <div class="detail-row">
              <span class="detail-label">Email Address</span>
              <span class="detail-value">{{ user?.email || 'N/A' }}</span>
            </div>

            <div class="detail-row">
              <span class="detail-label">Role</span>
              <span class="detail-value">Event Organizer</span>
            </div>

            <div class="detail-row">
              <span class="detail-label">Organization / Faculty</span>
              <span class="detail-value">{{ user?.organizerName || 'Computing Students Society' }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </ProtectedLayout>
</template>

<style scoped>
.profile-page {
  max-width: 600px;
}

.profile-header {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid var(--color-border);
  margin-bottom: 1.5rem;
}

.profile-avatar {
  width: 70px;
  height: 70px;
  background: var(--color-maroon-tint);
  color: var(--color-primary);
  font-size: 1.5rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  border: 2px solid var(--color-primary);
}

.profile-title h2 {
  font-size: 1.25rem;
  margin-bottom: 0.25rem;
}

.profile-role-badge {
  display: inline-block;
  font-size: 0.75rem;
  font-weight: 600;
  color: #fff;
  background: var(--color-primary);
  padding: 0.125rem 0.625rem;
  border-radius: var(--radius-pill);
}

.profile-details {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 0.75rem 0;
  border-bottom: 1px solid var(--color-bg);
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-label {
  font-weight: 500;
  color: var(--color-text-muted);
  font-size: 0.875rem;
}

.detail-value {
  font-weight: 600;
  color: var(--color-text);
  font-size: 0.9375rem;
}

@media (max-width: 480px) {
  .profile-header {
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }
  
  .detail-row {
    flex-direction: column;
    gap: 0.25rem;
  }
}
</style>
