<script setup>
import { computed } from 'vue'
import Navbar from '../components/Navbar.vue'
import EventCard from '../components/EventCard.vue'
import { useEventStore } from '../composables/useEventStore.js'

const { approvedEvents, toggleBookmark } = useEventStore()

const upcomingPreview = computed(() =>
  [...approvedEvents.value]
    .sort((a, b) => a.eventDate.localeCompare(b.eventDate))
    .slice(0, 3)
)

function onBookmark(id) {
  toggleBookmark(id)
}
</script>

<template>
  <div class="landing">
    <Navbar />

    <section class="hero">
      <div class="hero-content">
        <p class="hero-eyebrow">UTM Campus Events</p>
        <h1>Discover, Connect, Experience UTM Events</h1>
        <p class="hero-desc">
          Eventilize is your centralized hub for campus event discovery at Universiti Teknologi Malaysia.
          Find workshops, career fairs, cultural nights, and more — all in one place.
        </p>
        <div class="hero-actions">
          <router-link to="/events" class="btn btn-primary btn-lg">Browse Events</router-link>
          <router-link to="/login" class="btn btn-outline btn-lg">Login</router-link>
        </div>
      </div>
    </section>

    <section class="features page-container">
      <h2>Why Eventilize?</h2>
      <div class="features-grid">
        <div class="feature-card card">
          <div class="card-body">
            <div class="feature-icon-wrap">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <h3>Centralized Event Discovery</h3>
            <p>All UTM campus events in one platform — no more scattered announcements.</p>
          </div>
        </div>
        <div class="feature-card card">
          <div class="card-body">
            <div class="feature-icon-wrap">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <h3>Smart Search & Filters</h3>
            <p>Find events by category, date, location, or organizer with powerful filters.</p>
          </div>
        </div>
        <div class="feature-card card">
          <div class="card-body">
            <div class="feature-icon-wrap">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <h3>Bookmarks & Reminders</h3>
            <p>Save events you love and get notified before they happen.</p>
          </div>
        </div>
        <div class="feature-card card">
          <div class="card-body">
            <div class="feature-icon-wrap">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 20V10M12 20V4M6 20v-6" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <h3>Organizer Analytics</h3>
            <p>Organizers track views, bookmarks, and engagement for every event.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="preview section page-container">
      <h2>Upcoming Events</h2>
      <div class="events-grid">
        <EventCard
          v-for="event in upcomingPreview"
          :key="event.id"
          :event="event"
          @bookmark="onBookmark"
        />
      </div>
      <div class="preview-cta">
        <router-link to="/events" class="btn btn-primary">View All Events</router-link>
      </div>
    </section>

    <footer class="landing-footer">
      <p>Eventilize — A Centralized UTM Event Gathering System</p>
      <p class="footer-sub">© 2026 Universiti Teknologi Malaysia</p>
    </footer>
  </div>
</template>

<style scoped>
.landing {
  background: var(--color-bg);
}

.hero {
  background: var(--color-surface);
  border-bottom: 1px solid var(--color-border);
  padding: 4.5rem 1.5rem;
  text-align: center;
}

.hero-content {
  max-width: 680px;
  margin: 0 auto;
}

.hero-eyebrow {
  font-size: 0.8125rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--color-primary);
  margin-bottom: 0.875rem;
}

.hero h1 {
  font-size: clamp(1.625rem, 3.5vw, 2.375rem);
  font-weight: 700;
  margin-bottom: 1rem;
  color: var(--color-text);
  line-height: 1.25;
}

.hero-desc {
  font-size: 1rem;
  color: var(--color-text-muted);
  margin-bottom: 2rem;
  line-height: 1.7;
  font-weight: 400;
}

.hero-actions {
  display: flex;
  gap: 0.875rem;
  justify-content: center;
  flex-wrap: wrap;
}

.features {
  padding-top: 3.5rem;
  padding-bottom: 2rem;
}

.features h2,
.preview h2 {
  text-align: center;
  margin-bottom: 2rem;
  font-size: 1.375rem;
  font-weight: 700;
  color: var(--color-text);
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.25rem;
}

.feature-card {
  transition: var(--transition);
}

.feature-card:hover {
  transform: translateY(-2px);
}

.feature-icon-wrap {
  width: 44px;
  height: 44px;
  border-radius: var(--radius-sm);
  background: var(--color-maroon-tint);
  color: var(--color-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1rem;
}

.feature-card h3 {
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: var(--color-text);
}

.feature-card p {
  font-size: 0.875rem;
  color: var(--color-text-muted);
  line-height: 1.6;
}

.preview-cta {
  text-align: center;
  margin-top: 2rem;
}

.landing-footer {
  background: var(--color-primary-dark);
  color: rgba(255, 255, 255, 0.9);
  text-align: center;
  padding: 2rem 1.5rem;
  margin-top: 2rem;
}

.footer-sub {
  font-size: 0.8125rem;
  opacity: 0.65;
  margin-top: 0.25rem;
}
</style>
