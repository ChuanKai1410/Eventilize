<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import BookmarkIcon from './BookmarkIcon.vue'
import StatusBadge from '../StatusBadge.vue'

const props = defineProps({
  event: { type: Object, required: true },
  showStatus: { type: Boolean, default: false },
  compact: { type: Boolean, default: false },
})

const emit = defineEmits(['bookmark', 'view-details'])

const router = useRouter()

const formattedDate = computed(() => {
  const d = new Date(props.event.eventDate + 'T00:00:00')
  return d.toLocaleDateString('en-MY', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' })
})

const categoryClass = computed(() => {
  const map = {
    'Tech Talk': 'cat-academic',
    Seminar: 'cat-academic',
    Career: 'cat-academic',
    Workshop: 'cat-workshop',
    Cultural: 'cat-cultural',
    Sports: 'cat-sports',
    Residential: 'cat-default',
  }
  if (props.event.title?.toLowerCase().includes('hackathon') || props.event.title?.toLowerCase().includes('tournament')) {
    return 'cat-competition'
  }
  return map[props.event.category] || 'cat-default'
})

const thumbnailStyle = computed(() => {
  const hues = {
    'cat-academic': 'linear-gradient(135deg, #EDE9FE 0%, #C4B5FD 100%)',
    'cat-cultural': 'linear-gradient(135deg, #FCE7F3 0%, #F9A8D4 100%)',
    'cat-competition': 'linear-gradient(135deg, #DCFCE7 0%, #86EFAC 100%)',
    'cat-workshop': 'linear-gradient(135deg, #DBEAFE 0%, #93C5FD 100%)',
    'cat-sports': 'linear-gradient(135deg, #FFEDD5 0%, #FDBA74 100%)',
    'cat-default': 'linear-gradient(135deg, #F3F4F6 0%, #D1D5DB 100%)',
  }
  return { background: hues[categoryClass.value] || hues['cat-default'] }
})

function goToDetails() {
  emit('view-details', props.event.id)
  router.push(`/events/${props.event.id}`)
}

function toggleBookmark() {
  emit('bookmark', props.event.id)
}
</script>

<template>
  <article class="student-event-card card" :class="{ compact }">
    <template v-if="compact">
      <div class="compact-body">
        <div class="compact-info">
          <span class="category-badge" :class="categoryClass">{{ event.category }}</span>
          <h3 class="event-title">{{ event.title }}</h3>
          <p class="event-meta">{{ formattedDate }} · {{ event.startTime }} – {{ event.endTime }}</p>
        </div>
        <div class="compact-actions">
          <BookmarkIcon :active="event.isBookmarked" @toggle="toggleBookmark" />
          <button type="button" class="btn btn-primary btn-sm" @click="goToDetails">Details</button>
        </div>
      </div>
    </template>

    <template v-else>
      <div class="event-thumbnail" :style="thumbnailStyle">
        <span class="category-badge" :class="categoryClass">{{ event.category }}</span>
      </div>
      <div class="card-body">
        <div v-if="showStatus" class="event-status-row">
          <StatusBadge :status="event.status" />
        </div>
        <h3 class="event-title">{{ event.title }}</h3>
        <div class="event-meta-row">
          <span class="event-meta">{{ formattedDate }}</span>
          <span class="event-meta">{{ event.startTime }} – {{ event.endTime }}</span>
        </div>
        <p class="event-meta location">{{ event.location }}</p>
        <p class="event-organizer">By {{ event.organizer }}</p>
        <p class="event-desc">{{ event.description }}</p>
        <div class="event-stats">
          <span>{{ event.viewsCount }} views</span>
          <span>{{ event.bookmarksCount }} bookmarks</span>
        </div>
        <div class="event-actions">
          <BookmarkIcon :active="event.isBookmarked" @toggle="toggleBookmark" />
          <button type="button" class="btn btn-primary btn-sm" @click="goToDetails">View Details</button>
        </div>
      </div>
    </template>
  </article>
</template>

<style scoped>
.student-event-card {
  overflow: hidden;
  transition: var(--transition);
}

.student-event-card:not(.compact):hover {
  box-shadow: var(--shadow-md);
  transform: translateY(-2px);
}

.event-thumbnail {
  position: relative;
  height: 120px;
  display: flex;
  align-items: flex-end;
  padding: 0.75rem;
}

.event-thumbnail .category-badge {
  position: static;
}

.card-body {
  padding: 1rem 1.125rem 1.125rem;
}

.event-title {
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  line-height: 1.35;
}

.event-meta-row {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-bottom: 0.25rem;
}

.event-meta {
  font-size: 0.75rem;
  color: var(--color-text-muted);
  font-weight: 500;
}

.event-meta.location {
  margin-bottom: 0.375rem;
}

.event-organizer {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--color-primary);
  margin: 0.375rem 0;
}

.event-desc {
  font-size: 0.8125rem;
  color: var(--color-text-muted);
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  margin-bottom: 0.75rem;
}

.event-stats {
  display: flex;
  gap: 1rem;
  font-size: 0.75rem;
  color: var(--color-text-muted);
  margin-bottom: 0.875rem;
}

.event-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 0.75rem;
  border-top: 1px solid var(--color-border);
}

.compact-body {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.875rem 1rem;
  flex-wrap: wrap;
}

.compact-info .event-title {
  margin: 0.375rem 0 0.25rem;
  font-size: 0.9375rem;
}

.compact-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
</style>
