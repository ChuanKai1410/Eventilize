<script setup>
import { computed } from 'vue'

const props = defineProps({
  notification: { type: Object, required: true },
})

const emit = defineEmits(['mark-read'])

const formattedTime = computed(() => {
  const d = new Date(props.notification.createdAt)
  return d.toLocaleString('en-MY', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  })
})

function markRead() {
  if (!props.notification.isRead) {
    emit('mark-read', props.notification.id)
  }
}
</script>

<template>
  <div
    class="notification-item card"
    :class="{ unread: !notification.isRead }"
    @click="markRead"
  >
    <div class="card-body">
      <div class="notification-header">
        <span v-if="!notification.isRead" class="unread-dot" aria-label="Unread" />
        <p class="notification-message">{{ notification.message }}</p>
      </div>
      <p class="notification-event">{{ notification.eventTitle }}</p>
      <p class="notification-time">{{ formattedTime }}</p>
      <button
        v-if="!notification.isRead"
        type="button"
        class="btn btn-text btn-sm mark-read-btn"
        @click.stop="markRead"
      >
        Mark as read
      </button>
    </div>
  </div>
</template>

<style scoped>
.notification-item {
  cursor: pointer;
  transition: var(--transition);
  margin-bottom: 0.625rem;
}

.notification-item.unread {
  border-left: 3px solid var(--color-primary);
  background: var(--color-maroon-tint);
}

.notification-item:not(.unread) {
  background: var(--color-surface);
}

.notification-header {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
}

.unread-dot {
  width: 7px;
  height: 7px;
  background: var(--color-primary);
  border-radius: 50%;
  flex-shrink: 0;
  margin-top: 0.45rem;
}

.notification-message {
  font-weight: 600;
  font-size: 0.875rem;
  color: var(--color-text);
  margin-bottom: 0.25rem;
}

.notification-event {
  font-size: 0.8125rem;
  color: var(--color-primary);
  font-weight: 500;
  margin-bottom: 0.25rem;
}

.notification-time {
  font-size: 0.75rem;
  color: var(--color-text-muted);
}

.mark-read-btn {
  margin-top: 0.5rem;
  padding-left: 0;
}
</style>
