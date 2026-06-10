<script setup>
import { ref, computed, onMounted } from 'vue'
import StudentProtectedLayout from '../components/student/StudentProtectedLayout.vue'
import NotificationItem from '../components/NotificationItem.vue'
import EmptyState from '../components/EmptyState.vue'
import { useNotifications } from '../composables/useNotifications.js'

const { notifications, markAsRead, markAllAsRead } = useNotifications()

const filter = ref('all')

const notificationSettings = ref({
  newEvent: true,
  upcomingEvent: true,
  registrationDeadline: false,
})

onMounted(() => {
  try {
    const stored = localStorage.getItem('eventilize_notif_settings')
    if (stored) {
      notificationSettings.value = { ...notificationSettings.value, ...JSON.parse(stored) }
    }
  } catch {
    /* ignore */
  }
})

function saveSettings() {
  localStorage.setItem('eventilize_notif_settings', JSON.stringify(notificationSettings.value))
}

function toggleSetting(key) {
  notificationSettings.value[key] = !notificationSettings.value[key]
  saveSettings()
}

const filteredList = computed(() => {
  const sorted = [...notifications.value].sort(
    (a, b) => new Date(b.createdAt) - new Date(a.createdAt)
  )
  if (filter.value === 'unread') return sorted.filter((n) => !n.isRead)
  if (filter.value === 'read') return sorted.filter((n) => n.isRead)
  return sorted
})
</script>

<template>
  <StudentProtectedLayout>
    <div class="page-container">
      <div class="page-header">
        <h1>Notifications</h1>
        <p>Stay updated on events and reminders.</p>
      </div>

      <section class="settings-card card">
        <div class="card-body">
          <h2>Notification Settings</h2>
          <p class="settings-desc">Choose which alerts you want to receive.</p>

          <div class="toggle-row">
            <div>
              <span class="toggle-label">New Event</span>
              <span class="toggle-hint">When a new campus event is published</span>
            </div>
            <label class="toggle-switch">
              <input
                type="checkbox"
                :checked="notificationSettings.newEvent"
                @change="toggleSetting('newEvent')"
              />
              <span class="toggle-slider" />
            </label>
          </div>

          <div class="toggle-row">
            <div>
              <span class="toggle-label">Upcoming Event</span>
              <span class="toggle-hint">Reminders before your registered events</span>
            </div>
            <label class="toggle-switch">
              <input
                type="checkbox"
                :checked="notificationSettings.upcomingEvent"
                @change="toggleSetting('upcomingEvent')"
              />
              <span class="toggle-slider" />
            </label>
          </div>

          <div class="toggle-row">
            <div>
              <span class="toggle-label">Registration Deadline</span>
              <span class="toggle-hint">Alerts when registration is closing soon</span>
            </div>
            <label class="toggle-switch">
              <input
                type="checkbox"
                aria-label="Registration Deadline"
                :checked="notificationSettings.registrationDeadline"
                @change="toggleSetting('registrationDeadline')"
              />
              <span class="toggle-slider" />
            </label>
          </div>
        </div>
      </section>

      <div class="notif-toolbar">
        <div class="filter-tabs">
          <button
            type="button"
            class="tab"
            :class="{ active: filter === 'all' }"
            @click="filter = 'all'"
          >
            All
          </button>
          <button
            type="button"
            class="tab"
            :class="{ active: filter === 'unread' }"
            @click="filter = 'unread'"
          >
            Unread
          </button>
          <button
            type="button"
            class="tab"
            :class="{ active: filter === 'read' }"
            @click="filter = 'read'"
          >
            Read
          </button>
        </div>
        <button type="button" class="btn btn-ghost btn-sm" @click="markAllAsRead">
          Mark all as read
        </button>
      </div>

      <div v-if="filteredList.length">
        <NotificationItem
          v-for="notif in filteredList"
          :key="notif.id"
          :notification="notif"
          @mark-read="markAsRead"
        />
      </div>

      <EmptyState
        v-else
        title="No notifications"
        description="You're all caught up! Check back later for updates."
      />
    </div>
  </StudentProtectedLayout>
</template>

<style scoped>
.settings-card {
  margin-bottom: 1.75rem;
}

.settings-card h2 {
  font-size: 1.0625rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.settings-desc {
  font-size: 0.875rem;
  color: var(--color-text-muted);
  margin-bottom: 0.5rem;
}

.toggle-label {
  display: block;
  font-size: 0.9375rem;
  font-weight: 500;
}

.toggle-hint {
  display: block;
  font-size: 0.75rem;
  color: var(--color-text-muted);
  margin-top: 0.125rem;
}

.notif-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.filter-tabs {
  display: flex;
  gap: 0.25rem;
  background: var(--color-surface);
  padding: 0.25rem;
  border-radius: var(--radius-sm);
  border: 1px solid var(--color-border);
}

.tab {
  padding: 0.5rem 1rem;
  border: none;
  background: transparent;
  border-radius: var(--radius-sm);
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--color-text-muted);
  font-family: inherit;
}

.tab.active {
  background: var(--color-primary);
  color: #fff;
}
</style>
