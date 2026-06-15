<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import StatusBadge from './StatusBadge.vue'

const props = defineProps({
  event: { type: Object, required: true },
  showStatus: { type: Boolean, default: false },
  previewOnly: { type: Boolean, default: false },
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
  if (props.event.category === 'Tech Talk' && props.event.title?.toLowerCase().includes('hackathon')) {
    return 'cat-competition'
  }
  if (props.event.title?.toLowerCase().includes('hackathon') || props.event.title?.toLowerCase().includes('tournament')) {
    return 'cat-competition'
  }
  return map[props.event.category] || 'cat-default'
})

const eventImageSrc = computed(() =>
  typeof props.event.eventImage === 'string' ? props.event.eventImage : ''
)

const thumbnailStyle = computed(() => {
  if (eventImageSrc.value) {
    return { backgroundImage: `url(${eventImageSrc.value})` }
  }

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
  if (props.previewOnly) return
  emit('view-details', props.event.id)
  router.push(`/events/${props.event.id}`)
}

function toggleBookmark() {
  if (props.previewOnly) return
  emit('bookmark', props.event.id)
}
</script>

<template>
  <article class="event-card card" :class="{ 'preview-only': previewOnly }">
    <div class="event-thumbnail" :style="thumbnailStyle">
      <img
        v-if="eventImageSrc"
        :src="eventImageSrc"
        :alt="event.title"
        class="event-thumbnail-img"
      />
      <span class="category-badge" :class="categoryClass">{{ event.category }}</span>
      <div v-if="!eventImageSrc" class="thumbnail-icon" aria-hidden="true">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.35">
          <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
    </div>
    <div class="card-body">
      <div v-if="showStatus" class="event-status-row">
        <StatusBadge :status="event.status" />
      </div>
      <h3 class="event-title">{{ event.title }}</h3>
      <div class="event-meta-row">
        <span class="event-meta">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          {{ formattedDate }}
        </span>
        <span class="event-meta">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          {{ event.startTime }} – {{ event.endTime }}
        </span>
      </div>
      <p class="event-meta location">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        {{ event.location }}
      </p>
      <p class="event-organizer">By {{ event.organizer }}</p>
      <p class="event-desc">{{ event.description }}</p>
      <div class="event-stats">
        <span>{{ event.viewsCount }} views</span>
        <span>{{ event.bookmarksCount }} bookmarks</span>
      </div>
      <div class="event-actions">
        <button
          type="button"
          class="bookmark-btn"
          :class="{ active: event.isBookmarked }"
          :disabled="previewOnly"
          :aria-label="previewOnly ? 'Login to bookmark' : event.isBookmarked ? 'Remove bookmark' : 'Bookmark event'"
          @click="toggleBookmark"
        >
          <svg width="18" height="18" viewBox="0 0 24 24" :fill="event.isBookmarked ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2">
            <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <button
          type="button"
          class="btn btn-primary btn-sm"
          :disabled="previewOnly"
          @click="goToDetails"
        >
          View Details
        </button>
      </div>
    </div>
  </article>
</template>

<style scoped>
.event-card {
  display: flex;
  flex-direction: column;
  overflow: hidden;
  transition: var(--transition);
}

.event-card:hover {
  box-shadow: var(--shadow-md);
  transform: translateY(-2px);
}

.event-thumbnail {
  position: relative;
  height: 140px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.event-thumbnail .category-badge {
  position: absolute;
  top: 0.75rem;
  left: 0.75rem;
  z-index: 1;
}

.event-thumbnail-img {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.thumbnail-icon {
  color: var(--color-text);
}

.event-status-row {
  margin-bottom: 0.5rem;
}

.card-body {
  padding: 1rem 1.125rem 1.125rem;
}

.event-title {
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 0.625rem;
  line-height: 1.35;
  color: var(--color-text);
}

.event-meta-row {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-bottom: 0.375rem;
}

.event-meta {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
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
  line-height: 1.5;
}

.event-stats {
  display: flex;
  gap: 1rem;
  font-size: 0.75rem;
  color: var(--color-text-muted);
  margin-bottom: 0.875rem;
  font-weight: 500;
}

.event-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  padding-top: 0.75rem;
  border-top: 1px solid var(--color-border);
}

.bookmark-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  background: var(--color-surface);
  color: var(--color-text-muted);
  cursor: pointer;
  transition: var(--transition);
}

.bookmark-btn:hover {
  border-color: var(--color-primary);
  color: var(--color-primary);
  background: var(--color-maroon-tint);
}

.bookmark-btn.active {
  color: var(--color-primary);
  border-color: var(--color-primary);
  background: var(--color-maroon-tint);
}

.bookmark-btn:disabled,
.event-actions .btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
  transform: none;
}

.preview-only:hover {
  transform: none;
}
</style>
