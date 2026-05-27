<script setup>
import { computed } from 'vue'
import ProtectedLayout from '../components/ProtectedLayout.vue'
import AnalyticsCard from '../components/AnalyticsCard.vue'
import StatusBadge from '../components/StatusBadge.vue'
import { useEventStore } from '../composables/useEventStore.js'

const { events } = useEventStore()

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
        <p>Platform overview and pending approvals.</p>
      </div>

      <div class="stats-grid">
        <AnalyticsCard label="Total Users" :value="totalUsers" icon="chart" />
        <AnalyticsCard label="Total Events" :value="stats.totalEvents" icon="calendar" />
        <AnalyticsCard label="Pending Approvals" :value="stats.pending" icon="chart" />
        <AnalyticsCard label="Approved Events" :value="stats.approved" icon="chart" />
        <AnalyticsCard label="Rejected Events" :value="stats.rejected" icon="chart" />
      </div>

      <div class="dashboard-grid">
        <section class="card">
          <div class="card-body">
            <div class="section-title">
              <h2>Pending Approvals</h2>
              <router-link to="/admin/approvals">View all</router-link>
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
                    <td><StatusBadge :status="event.status" /></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <p v-else class="empty-hint">No pending approvals.</p>
          </div>
        </section>

        <section class="card">
          <div class="card-body">
            <h2>Platform Activity</h2>
            <div class="activity-list">
              <div class="activity-item">
                <span class="activity-dot approved" />
                <span>{{ stats.approved }} events live on platform</span>
              </div>
              <div class="activity-item">
                <span class="activity-dot pending" />
                <span>{{ stats.pending }} events awaiting review</span>
              </div>
              <div class="activity-item">
                <span class="activity-dot rejected" />
                <span>{{ stats.rejected }} events rejected</span>
              </div>
              <div class="activity-item">
                <span class="activity-dot info" />
                <span>{{ totalUsers }} registered users</span>
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
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 1.25rem;
}

.section-title h2,
.card h2 {
  font-size: 1.125rem;
}

.empty-hint {
  color: var(--color-text-muted);
  font-size: 0.9375rem;
}

.activity-list {
  margin-top: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.9375rem;
}

.activity-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
}

.activity-dot.approved { background: var(--color-success); }
.activity-dot.pending { background: var(--color-warning); }
.activity-dot.rejected { background: var(--color-danger); }
.activity-dot.info { background: var(--color-info); }
</style>
