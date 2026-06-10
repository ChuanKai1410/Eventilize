<script setup>
import { computed } from 'vue'
import StudentProtectedLayout from '../components/student/StudentProtectedLayout.vue'
import AnalyticsCard from '../components/AnalyticsCard.vue'
import StudentEventCard from '../components/student/StudentEventCard.vue'
import EventCalendar from '../components/student/EventCalendar.vue'
import RegisteredEventsTabs from '../components/student/RegisteredEventsTabs.vue'
import { useAuth } from '../composables/useAuth.js'
import { useEventStore } from '../composables/useEventStore.js'
import { useNotifications } from '../composables/useNotifications.js'
import { useRegisteredEvents } from '../composables/useRegisteredEvents.js'

const { user } = useAuth()
const { approvedEvents, toggleBookmark, getRecommended } = useEventStore()
const { unreadCount } = useNotifications()
const { upcomingRegistered, pastRegistered } = useRegisteredEvents()

const upcomingCount = computed(() =>
  approvedEvents.value.filter((e) => e.eventDate >= new Date().toISOString().slice(0, 10)).length
)

const recommended = computed(() => getRecommended(null, 3))
</script>

<template>
  <StudentProtectedLayout>
    <div class="page-container">
      <div class="page-header">
        <h1>Welcome back, {{ user?.name }}!</h1>
        <p>Here's what's happening on campus.</p>
      </div>

      <div class="stats-grid">
        <AnalyticsCard label="Upcoming Events" :value="upcomingCount" icon="calendar" />
        <AnalyticsCard label="Registered Events" :value="upcomingRegistered.length + pastRegistered.length" icon="chart" />
        <AnalyticsCard label="Unread Notifications" :value="unreadCount" icon="bell" />
        <AnalyticsCard label="Recommended Events" :value="recommended.length" icon="chart" />
      </div>

      <section class="section">
        <EventCalendar @bookmark="toggleBookmark" />
      </section>

      <section class="section">
        <div class="section-title">
          <h2>Upcoming Registered</h2>
        </div>
        <RegisteredEventsTabs
          :upcoming="upcomingRegistered"
          :past="pastRegistered"
          @bookmark="toggleBookmark"
        />
      </section>

      <section class="section">
        <div class="section-title">
          <h2>Recommended for You</h2>
          <router-link to="/events">View all</router-link>
        </div>
        <div class="events-grid">
          <StudentEventCard
            v-for="event in recommended"
            :key="event.id"
            :event="event"
            @bookmark="toggleBookmark"
          />
        </div>
      </section>
    </div>
  </StudentProtectedLayout>
</template>

<style scoped>
.section-title h2 {
  font-size: 1.125rem;
  font-weight: 600;
}
</style>
