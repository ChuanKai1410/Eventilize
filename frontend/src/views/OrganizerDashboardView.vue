<script setup>
import { computed } from 'vue'
import ProtectedLayout from '../components/ProtectedLayout.vue'
import AnalyticsCard from '../components/AnalyticsCard.vue'
import StatusBadge from '../components/StatusBadge.vue'
import { useAuth } from '../composables/useAuth.js'
import { useEventStore } from '../composables/useEventStore.js'

const { user } = useAuth()
const { events } = useEventStore()

const organizerName = computed(() => user.value?.organizerName || 'Computing Students Society')

const myEvents = computed(() =>
  events.value.filter((e) => e.organizer === organizerName.value)
)

const stats = computed(() => ({
  total: myEvents.value.length,
  pending: myEvents.value.filter((e) => e.status === 'Pending').length,
  approved: myEvents.value.filter((e) => e.status === 'Approved').length,
  views: myEvents.value.reduce((sum, e) => sum + e.viewsCount, 0),
  bookmarks: myEvents.value.reduce((sum, e) => sum + e.bookmarksCount, 0),
}))

const recentEvents = computed(() =>
  [...myEvents.value]
    .sort((a, b) => b.id - a.id)
    .slice(0, 5)
)

const chartHeights = [40, 65, 50, 80, 55, 70, 45]
</script>

<template>
  <ProtectedLayout>
    <div class="page-container">
      <div class="page-header">
        <h1>Organizer Dashboard</h1>
        <p>Welcome, {{ user?.name }}! Manage your events and track engagement.</p>
      </div>

      <div class="stats-grid">
        <AnalyticsCard label="Total Events Created" :value="stats.total" icon="calendar" />
        <AnalyticsCard label="Pending Approval" :value="stats.pending" icon="chart" />
        <AnalyticsCard label="Approved Events" :value="stats.approved" icon="chart" />
        <AnalyticsCard label="Total Views" :value="stats.views" icon="chart" />
        <AnalyticsCard label="Total Bookmarks" :value="stats.bookmarks" icon="bookmark" />
      </div>

      <div class="analytics-section card">
        <div class="card-body">
          <h2>Analytics Overview</h2>
          <div class="chart-placeholder">
            <p>Engagement chart placeholder — views & bookmarks over time</p>
            <div class="chart-bars">
              <div
                v-for="(h, i) in chartHeights"
                :key="i"
                class="chart-bar"
                :style="{ height: h + '%' }"
              />
            </div>
          </div>
        </div>
      </div>

      <section class="section">
        <div class="section-title">
          <h2>Recent Events</h2>
          <router-link to="/organizer/events/create" class="btn btn-accent btn-sm">Create New Event</router-link>
        </div>
        <div class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Date</th>
                <th>Status</th>
                <th>Views</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="event in recentEvents" :key="event.id">
                <td>{{ event.title }}</td>
                <td>{{ event.category }}</td>
                <td>{{ event.eventDate }}</td>
                <td><StatusBadge :status="event.status" /></td>
                <td>{{ event.viewsCount }}</td>
                <td>
                  <div class="table-actions">
                    <router-link
                      :to="{ path: `/events/${event.id}`, query: { from: 'organizer-events' } }"
                      class="btn btn-ghost btn-sm"
                    >
                      View
                    </router-link>
                    <router-link :to="`/organizer/events/${event.id}/edit`" class="btn btn-ghost btn-sm">Edit</router-link>
                  </div>
                </td>
              </tr>
              <tr v-if="!recentEvents.length">
                <td colspan="6" class="empty-cell">No events yet. Create your first event!</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </ProtectedLayout>
</template>

<style scoped>
.page-header h1 {
  margin-bottom: 2rem;
}

.stats-grid {
  margin-bottom: 2rem;
}

.analytics-section  {
  margin-bottom: 2rem;
}

.section {
  margin-bottom: 1rem;
}

.analytics-section h2 {
  font-size: 1.125rem;
  margin-bottom: 1rem;
}

.empty-cell {
  text-align: center;
  color: var(--color-text-muted);
  padding: 2rem !important;
}
</style>
