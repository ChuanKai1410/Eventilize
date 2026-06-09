<script setup>
import { ref, computed } from 'vue'
import ProtectedLayout from '../components/ProtectedLayout.vue'
import NotificationItem from '../components/NotificationItem.vue'
import EmptyState from '../components/EmptyState.vue'
import { useNotifications } from '../composables/useNotifications.js'

const { notifications, markAsRead, markAllAsRead } = useNotifications()

const filter = ref('all')

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
  <ProtectedLayout>
    <div class="page-container">
      <div class="page-header">
        <h1>Notifications</h1>
        <p>Stay updated on events and reminders.</p>
      </div>

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
  </ProtectedLayout>
</template>

<style scoped>
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
