<script setup>
import { computed, onMounted } from 'vue'
import ProtectedLayout from '../components/ProtectedLayout.vue'
import AnalyticsCard from '../components/AnalyticsCard.vue'
import StatusBadge from '../components/StatusBadge.vue'
import { useEventStore } from '../composables/useEventStore.js'

const { events, fetchEvents } = useEventStore()

onMounted(() => {
  fetchEvents()
})

const stats = computed(() => ({
  totalEvents: events.value.length,
  pending: events.value.filter((e) => e.status === 'Pending').length,
  approved: events.value.filter((e) => e.status === 'Approved').length,
  rejected: events.value.filter((e) => e.status === 'Rejected').length,
}))

const pendingList = computed(() =>
  events.value
    .filter((e) => e.status === 'Pending')
    .slice(0, 5)
)

// Mock user count for admin dashboard
const totalUsers = 1247
</script>

<template>
  <ProtectedLayout>
    <div class="page-container">
      <div class="page-header">
        <h1>Admin Dashboard</h1>
        <p>Monitor platform activity, event approvals, and system status.</p>
      </div>

      <div class="stats-grid">
        <AnalyticsCard label="Total Users" :value="totalUsers" icon="chart" />
        <AnalyticsCard label="Total Events" :value="stats.totalEvents" icon="calendar" />
        <AnalyticsCard label="Pending Approvals" :value="stats.pending" icon="chart" />
        <AnalyticsCard label="Approved Events" :value="stats.approved" icon="chart" />
      </div>

      <div class="dashboard-grid">
        <section class="card">
          <div class="card-body">
            <div class="section-title">
              <div>
                <h2>Pending Review Queue</h2>
                <p class="section-desc">Latest submitted events waiting for admin review.</p>
              </div>

              <router-link to="/admin/approvals" class="section-link">
                View all
              </router-link>
            </div>

            <div v-if="pendingList.length" class="table-wrapper">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Event</th>
                    <th>Organizer</th>
                    <th>Date</th>
                    <th>Status</th>
                  </tr>
                </thead>

                <tbody>
                  <tr v-for="event in pendingList" :key="event.id">
                    <td>{{ event.title }}</td>
                    <td>{{ event.organizer }}</td>
                    <td>{{ event.eventDate }}</td>
                    <td>
                      <StatusBadge :status="event.status" />
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <p v-else class="empty-hint">
              No pending approvals.
            </p>
          </div>
        </section>

        <section class="card">
          <div class="card-body">
            <h2>Platform Activity</h2>
            <p class="section-desc">Quick summary of current platform status.</p>

            <div class="activity-list">
              <div class="activity-item">
                <span class="activity-dot approved" />
                <span>{{ stats.approved }} events are live on the platform</span>
              </div>

              <div class="activity-item">
                <span class="activity-dot pending" />
                <span>{{ stats.pending }} events are waiting for approval</span>
              </div>

              <div class="activity-item">
                <span class="activity-dot rejected" />
                <span>{{ stats.rejected }} events have been rejected</span>
              </div>

              <div class="activity-item">
                <span class="activity-dot info" />
                <span>{{ totalUsers }} registered users in the system</span>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </ProtectedLayout>
</template>

<style scoped>
.dashboard-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.4fr) minmax(280px, 0.8fr);
  gap: 1.25rem;
}

.section-title {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1rem;
}

.section-title h2,
.card h2 {
  font-size: 1.125rem;
  margin-bottom: 0.25rem;
}

.section-desc {
  color: var(--color-text-muted);
  font-size: 0.9rem;
}

.section-link {
  color: var(--color-primary);
  font-size: 0.875rem;
  font-weight: 600;
  white-space: nowrap;
}

.empty-hint {
  color: var(--color-text-muted);
  font-size: 0.9375rem;
  padding: 1rem 0;
}

.activity-list {
  margin-top: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.9375rem;
  color: var(--color-text);
}

.activity-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
}

.activity-dot.approved {
  background: var(--color-success);
}

.activity-dot.pending {
  background: var(--color-warning);
}

.activity-dot.rejected {
  background: var(--color-danger);
}

.activity-dot.info {
  background: var(--color-info);
}

@media (max-width: 900px) {
  .dashboard-grid {
    grid-template-columns: 1fr;
  }

  .section-title {
    flex-direction: column;
  }
}
</style>
