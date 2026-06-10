<script setup>
import { ref, computed } from 'vue'
import StudentEventCard from './StudentEventCard.vue'
import EmptyState from '../EmptyState.vue'

const props = defineProps({
  upcoming: { type: Array, default: () => [] },
  past: { type: Array, default: () => [] },
})

const emit = defineEmits(['bookmark'])

const activeTab = ref('upcoming')

const displayedEvents = computed(() =>
  activeTab.value === 'upcoming' ? props.upcoming : props.past
)

const emptyTitle = computed(() =>
  activeTab.value === 'upcoming' ? 'No upcoming registered events' : 'No past registered events'
)

const emptyDescription = computed(() =>
  activeTab.value === 'upcoming'
    ? 'Register for events to see them here.'
    : 'Your past registered events will appear here.'
)
</script>

<template>
  <div class="registered-tabs">
    <div class="tab-bar">
      <button
        type="button"
        class="tab"
        :class="{ active: activeTab === 'upcoming' }"
        @click="activeTab = 'upcoming'"
      >
        Upcoming Registered
        <span v-if="upcoming.length" class="tab-count">{{ upcoming.length }}</span>
      </button>
      <button
        type="button"
        class="tab"
        :class="{ active: activeTab === 'past' }"
        @click="activeTab = 'past'"
      >
        Past Registered
        <span v-if="past.length" class="tab-count">{{ past.length }}</span>
      </button>
    </div>

    <div v-if="displayedEvents.length" class="events-grid">
      <StudentEventCard
        v-for="event in displayedEvents"
        :key="event.id"
        :event="event"
        @bookmark="emit('bookmark', $event)"
      />
    </div>

    <EmptyState
      v-else
      :title="emptyTitle"
      :description="emptyDescription"
      action-label="Discover events"
      @action="$router.push('/events')"
    />
  </div>
</template>

<style scoped>
.tab-bar {
  display: flex;
  gap: 0.375rem;
  margin-bottom: 1.25rem;
  padding: 0.25rem;
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  width: fit-content;
  max-width: 100%;
  flex-wrap: wrap;
}

.tab {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.5rem 0.875rem;
  border: none;
  background: transparent;
  border-radius: var(--radius-sm);
  cursor: pointer;
  font-size: 0.8125rem;
  font-weight: 500;
  color: var(--color-text-muted);
  font-family: inherit;
  transition: var(--transition);
}

.tab.active {
  background: var(--color-primary);
  color: #fff;
}

.tab-count {
  font-size: 0.6875rem;
  font-weight: 700;
  padding: 0.1rem 0.35rem;
  border-radius: var(--radius-pill);
  background: rgba(255, 255, 255, 0.2);
}

.tab:not(.active) .tab-count {
  background: var(--color-bg);
  color: var(--color-text-muted);
}

.events-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.25rem;
}
</style>
