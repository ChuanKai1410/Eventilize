<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuth } from '../composables/useAuth.js'

const { user } = useAuth()
const route = useRoute()

const studentLinks = [
  { to: '/student/dashboard', label: 'Dashboard', icon: 'home' },
  { to: '/events', label: 'Discover Events', icon: 'search' },
  { to: '/bookmarks', label: 'Bookmarks', icon: 'bookmark' },
  { to: '/notifications', label: 'Notifications', icon: 'bell' },
]

const organizerLinks = [
  { to: '/organizer/dashboard', label: 'Dashboard', icon: 'home' },
  { to: '/organizer/events/create', label: 'Create Event', icon: 'plus' },
  { to: '/organizer/events', label: 'Manage Events', icon: 'list' },
  { to: '/organizer/profile', label: 'View Profile', icon: 'user' },
]

const adminLinks = [
  { to: '/admin/dashboard', label: 'Dashboard', icon: 'home' },
  { to: '/admin/approvals', label: 'Approvals', icon: 'check' },
  { to: '/admin/events', label: 'All Events', icon: 'list' },
  { to: '/admin/settings', label: 'Settings', icon: 'settings' },
]

const links = computed(() => {
  if (!user.value) return []
  const map = {
    student: studentLinks,
    organizer: organizerLinks,
    admin: adminLinks,
  }
  return map[user.value.role] || []
})

function isActive(path) {
  if (user.value?.role === 'admin' && route.path.startsWith('/events/')) {
    if (route.query.from === 'admin-approval' || route.query.from === 'admin-approvals') {
      return path === '/admin/approvals'
    }

    if (route.query.from === 'admin-events') {
      return path === '/admin/events'
    }

    return path === '/admin/events'
  }

  if (path === '/events') return route.path === '/events'

  if (path === '/organizer/events' && route.path === '/organizer/events/create') {
    return false
  }

  return route.path === path || route.path.startsWith(path + '/')
}
</script>

<template>
  <aside class="sidebar">
    <nav class="sidebar-nav">
      <p class="sidebar-label">Menu</p>
      <router-link
        v-for="link in links"
        :key="link.to"
        :to="link.to"
        class="sidebar-link"
        :class="{ active: isActive(link.to) }"
      >
        <span class="sidebar-icon" aria-hidden="true">
          <svg v-if="link.icon === 'home'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <svg v-else-if="link.icon === 'search'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <svg v-else-if="link.icon === 'bookmark'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <svg v-else-if="link.icon === 'bell'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <svg v-else-if="link.icon === 'plus'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <svg v-else-if="link.icon === 'check'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <svg v-else-if="link.icon === 'settings'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <svg v-else-if="link.icon === 'user'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="12" cy="7" r="4" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 6h16M4 10h16M4 14h16M4 18h16" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
        {{ link.label }}
      </router-link>
    </nav>
  </aside>
</template>

<style scoped>
.sidebar {
  width: var(--sidebar-width);
  background: var(--color-surface);
  border-right: 1px solid var(--color-border);
  flex-shrink: 0;
  min-height: calc(100vh - var(--navbar-height));
}

.sidebar-nav {
  padding: 1.25rem 0.875rem;
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.sidebar-label {
  font-size: 0.6875rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--color-text-muted);
  padding: 0 0.75rem;
  margin-bottom: 0.5rem;
}

.sidebar-link {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  padding: 0.625rem 0.875rem;
  border-radius: var(--radius-pill);
  color: var(--color-text-muted);
  text-decoration: none;
  font-size: 0.875rem;
  font-weight: 500;
  transition: var(--transition);
}

.sidebar-link:hover {
  background: var(--color-maroon-tint);
  color: var(--color-primary);
  text-decoration: none;
}

.sidebar-link.active {
  background: var(--color-primary);
  color: #fff;
  box-shadow: var(--shadow-sm);
}

.sidebar-link.active .sidebar-icon {
  color: #fff;
}

.sidebar-icon {
  display: flex;
  align-items: center;
  flex-shrink: 0;
}

@media (max-width: 768px) {
  .sidebar {
    width: 100%;
    min-height: auto;
    border-right: none;
    border-bottom: 1px solid var(--color-border);
  }

  .sidebar-label {
    display: none;
  }

  .sidebar-nav {
    flex-direction: row;
    overflow-x: auto;
    padding: 0.625rem;
    gap: 0.375rem;
  }

  .sidebar-link {
    white-space: nowrap;
    padding: 0.5rem 0.75rem;
    font-size: 0.8125rem;
  }
}
</style>
