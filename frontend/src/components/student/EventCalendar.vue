<script setup>
import { ref, computed } from 'vue'
import { useRegisteredEvents } from '../../composables/useRegisteredEvents.js'
import StudentEventCard from './StudentEventCard.vue'
import EmptyState from '../EmptyState.vue'

const emit = defineEmits(['bookmark'])

const { getEventsOnDate, getDatesWithEvents } = useRegisteredEvents()

const today = new Date()
const viewYear = ref(today.getFullYear())
const viewMonth = ref(today.getMonth())
const selectedDate = ref(today.toISOString().slice(0, 10))

const monthLabel = computed(() =>
  new Date(viewYear.value, viewMonth.value).toLocaleDateString('en-MY', {
    month: 'long',
    year: 'numeric',
  })
)

const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']

const datesWithEvents = computed(() =>
  getDatesWithEvents(viewYear.value, viewMonth.value)
)

const calendarDays = computed(() => {
  const firstDay = new Date(viewYear.value, viewMonth.value, 1)
  const lastDay = new Date(viewYear.value, viewMonth.value + 1, 0)
  const days = []

  for (let i = 0; i < firstDay.getDay(); i++) {
    days.push({ date: null, inMonth: false })
  }

  for (let d = 1; d <= lastDay.getDate(); d++) {
    const dateStr = `${viewYear.value}-${String(viewMonth.value + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`
    days.push({
      date: dateStr,
      day: d,
      inMonth: true,
      hasEvents: datesWithEvents.value.includes(dateStr),
      isToday: dateStr === today.toISOString().slice(0, 10),
      isSelected: dateStr === selectedDate.value,
    })
  }

  return days
})

const selectedDateEvents = computed(() => getEventsOnDate(selectedDate.value))

const selectedDateLabel = computed(() => {
  const d = new Date(selectedDate.value + 'T00:00:00')
  return d.toLocaleDateString('en-MY', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
})

function prevMonth() {
  if (viewMonth.value === 0) {
    viewMonth.value = 11
    viewYear.value -= 1
  } else {
    viewMonth.value -= 1
  }

  selectedDate.value = `${viewYear.value}-${String(viewMonth.value + 1).padStart(2, '0')}-01`
}

function nextMonth() {
  if (viewMonth.value === 11) {
    viewMonth.value = 0
    viewYear.value += 1
  } else {
    viewMonth.value += 1
  }

  selectedDate.value = `${viewYear.value}-${String(viewMonth.value + 1).padStart(2, '0')}-01`
}

function selectDate(dateStr) {
  selectedDate.value = dateStr
}

function onBookmark(id) {
  emit('bookmark', id)
}
</script>

<template>
  <div class="event-calendar card">
    <div class="card-body">
      <div class="calendar-header">
        <h2>Event Calendar</h2>
        <div class="calendar-nav">
          <button type="button" class="nav-btn" aria-label="Previous month" @click="prevMonth">‹</button>
          <span class="month-label">{{ monthLabel }}</span>
          <button type="button" class="nav-btn" aria-label="Next month" @click="nextMonth">›</button>
        </div>
      </div>

      <div class="calendar-grid">
        <span v-for="day in weekDays" :key="day" class="weekday">{{ day }}</span>
        <button
          v-for="(cell, index) in calendarDays"
          :key="index"
          type="button"
          class="day-cell"
          :class="{
            empty: !cell.inMonth,
            today: cell.isToday,
            selected: cell.isSelected,
            'has-events': cell.hasEvents,
          }"
          :disabled="!cell.inMonth"
          @click="cell.date && selectDate(cell.date)"
        >
          <span v-if="cell.inMonth">{{ cell.day }}</span>
          <span v-if="cell.hasEvents" class="event-dot" aria-hidden="true" />
        </button>
      </div>

      <div class="selected-date-section">
        <h3>{{ selectedDateLabel }}</h3>
        <div v-if="selectedDateEvents.length" class="calendar-events">
          <StudentEventCard
            v-for="event in selectedDateEvents"
            :key="event.id"
            :event="event"
            compact
            @bookmark="onBookmark"
          />
        </div>
        <EmptyState
          v-else
          title="No events on this date"
          description="Select another date or browse upcoming campus events."
        />
      </div>
    </div>
  </div>
</template>

<style scoped>
.calendar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.calendar-header h2 {
  font-size: 1.125rem;
  font-weight: 600;
}

.calendar-nav {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.nav-btn {
  width: 32px;
  height: 32px;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  background: var(--color-surface);
  cursor: pointer;
  font-size: 1.125rem;
  line-height: 1;
  color: var(--color-text);
  transition: var(--transition);
}

.nav-btn:hover {
  border-color: var(--color-primary);
  color: var(--color-primary);
  background: var(--color-maroon-tint);
}

.month-label {
  font-size: 0.875rem;
  font-weight: 600;
  min-width: 140px;
  text-align: center;
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 0.375rem;
  margin-bottom: 1.5rem;
}

.weekday {
  font-size: 0.6875rem;
  font-weight: 600;
  text-transform: uppercase;
  color: var(--color-text-muted);
  text-align: center;
  padding: 0.25rem;
}

.day-cell {
  position: relative;
  aspect-ratio: 1;
  border: 1px solid transparent;
  border-radius: var(--radius-sm);
  background: var(--color-bg);
  font-size: 0.8125rem;
  font-weight: 500;
  cursor: pointer;
  transition: var(--transition);
  font-family: inherit;
  color: var(--color-text);
}

.day-cell.empty {
  background: transparent;
  cursor: default;
}

.day-cell:not(.empty):hover {
  border-color: var(--color-primary);
  background: var(--color-maroon-tint);
}

.day-cell.today {
  border-color: var(--color-primary);
}

.day-cell.selected {
  background: var(--color-primary);
  color: #fff;
  border-color: var(--color-primary);
}

.day-cell.selected .event-dot {
  background: #fff;
}

.day-cell.has-events .event-dot {
  display: block;
}

.event-dot {
  display: none;
  position: absolute;
  bottom: 4px;
  left: 50%;
  transform: translateX(-50%);
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: var(--color-primary);
}

.selected-date-section h3 {
  font-size: 0.9375rem;
  font-weight: 600;
  margin-bottom: 1rem;
  color: var(--color-text-muted);
}

.calendar-events {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.selected-date-section :deep(.empty-state) {
  padding: 1.5rem 1rem;
}
</style>
