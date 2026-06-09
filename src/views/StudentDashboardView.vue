<script setup>
import { computed } from 'vue'
import ProtectedLayout from '../components/ProtectedLayout.vue'
import AnalyticsCard from '../components/AnalyticsCard.vue'
import EventCard from '../components/EventCard.vue'
import NotificationItem from '../components/NotificationItem.vue'
import { useAuth } from '../composables/useAuth.js'
import { useEventStore } from '../composables/useEventStore.js'
import { useNotifications } from '../composables/useNotifications.js'

const { user } = useAuth()
const { approvedEvents, bookmarkedEvents, toggleBookmark, getRecommended } = useEventStore()
const { notifications, unreadCount, markAsRead } = useNotifications()

const upcomingCount = computed(() =>
  approvedEvents.value.filter((e) => e.eventDate >= new Date().toISOString().slice(0, 10)).length
)

const recommended = computed(() => getRecommended(null, 3))

const upcomingBookmarked = computed(() =>
  bookmarkedEvents.value.filter((e) => e.eventDate >= new Date().toISOString().slice(0, 10)).slice(0, 3)
)

const recentNotifications = computed(() =>
  [...notifications.value]
    .sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt))
    .slice(0, 3)
)
</script>

<template>
  <ProtectedLayout>
    <div class="page-container">
      <div class="page-header">
        <h1>Welcome back, {{ user?.name }}!</h1>
        <p>Here's what's happening on campus.</p>
      </div>

      <div class="stats-grid">
        <AnalyticsCard label="Upcoming Events" :value="upcomingCount" icon="calendar" />
        <AnalyticsCard label="Bookmarked Events" :value="bookmarkedEvents.length" icon="bookmark" />
        <AnalyticsCard label="Unread Notifications" :value="unreadCount" icon="bell" />
        <AnalyticsCard label="Recommended Events" :value="recommended.length" icon="chart" />
      </div>

      <section class="section">
        <div class="section-title">
          <h2>Recommended for You</h2>
          <router-link to="/events">View all</router-link>
        </div>
        <div class="events-grid">
          <EventCard
            v-for="event in recommended"
            :key="event.id"
            :event="event"
            @bookmark="toggleBookmark"
          />
        </div>
      </section>

      <section class="section">
        <div class="section-title">
          <h2>Upcoming Bookmarked Events</h2>
          <router-link to="/bookmarks">View bookmarks</router-link>
        </div>
        <div v-if="upcomingBookmarked.length" class="events-grid">
          <EventCard
            v-for="event in upcomingBookmarked"
            :key="event.id"
            :event="event"
            @bookmark="toggleBookmark"
          />
        </div>
        <p v-else class="empty-hint">Bookmark events to see them here.</p>
      </section>

      <section class="section">
        <div class="section-title">
          <h2>Recent Notifications</h2>
          <router-link to="/notifications">View all</router-link>
        </div>
        <NotificationItem
          v-for="notif in recentNotifications"
          :key="notif.id"
          :notification="notif"
          @mark-read="markAsRead"
        />
      </section>
    </div>
  </ProtectedLayout>
</template>

<style scoped>
.section-title h2 {
  font-size: 1.25rem;
}

.empty-hint {
  color: var(--color-text-muted);
  font-size: 0.9375rem;
}
</style>
