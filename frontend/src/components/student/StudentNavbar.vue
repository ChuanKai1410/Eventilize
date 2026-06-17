<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../../composables/useAuth.js'
import { useNotifications } from '../../composables/useNotifications.js'

const { user, isAuthenticated, logout } = useAuth()
const { unreadCount } = useNotifications()
const router = useRouter()

const searchQuery = ref('')

const roleLabel = computed(() => {
  if (!user.value) return ''
  return user.value.role.charAt(0).toUpperCase() + user.value.role.slice(1)
})

const userInitials = computed(() => {
  if (!user.value?.name) return '?'
  return user.value.name
    .split(' ')
    .map((n) => n[0])
    .join('')
    .slice(0, 2)
    .toUpperCase()
})

function handleLogout() {
  logout()
  router.push('/')
}

function handleSearch() {
  if (searchQuery.value.trim()) {
    router.push({ path: '/student/events', query: { q: searchQuery.value.trim() } })
  } else {
    router.push('/student/events')
  }
}

const isDropdownOpen = ref(false)

function toggleDropdown() {
  isDropdownOpen.value = !isDropdownOpen.value
}

function closeDropdown() {
  isDropdownOpen.value = false
}

function goToProfile() {
  router.push('/student/profile')
}
</script>

<template>
  <header class="navbar">
    <div class="navbar-inner">
      <router-link to="/student/dashboard" class="navbar-brand">
        <span class="brand-icon" aria-hidden="true">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
            <rect width="24" height="24" rx="6" fill="#8B1E3F"/>
            <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
        <span>Eventilize</span>
      </router-link>

      <div v-if="isAuthenticated" class="navbar-search">
        <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <input
          v-model="searchQuery"
          type="search"
          class="navbar-search-input"
          placeholder="Search events..."
          @keydown.enter="handleSearch"
        />
      </div>

      <nav v-if="isAuthenticated" class="navbar-links">
        <router-link to="/student/notifications" class="notif-bell" aria-label="Notifications">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span v-if="unreadCount > 0" class="notif-badge">{{ unreadCount }}</span>
        </router-link>

        <div class="user-profile-container">
          <button type="button" class="user-profile" aria-label="Toggle user menu" @click="toggleDropdown">
            <span class="user-avatar">{{ userInitials }}</span>
            <div class="user-info">
              <span class="user-name">{{ user.name }}</span>
              <span class="user-role">{{ roleLabel }}</span>
            </div>
            <svg class="dropdown-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>

          <div v-if="isDropdownOpen" class="profile-dropdown">
            <button type="button" class="dropdown-item" @click="goToProfile(); closeDropdown()">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="12" cy="7" r="4" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              View Profile
            </button>
            <button type="button" class="dropdown-item logout-item" @click="handleLogout(); closeDropdown()">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              Logout
            </button>
          </div>

          <div v-if="isDropdownOpen" class="dropdown-overlay" @click="closeDropdown"></div>
        </div>
      </nav>
    </div>
  </header>
</template>

<style scoped>
.navbar {
  background: var(--color-surface);
  border-bottom: 1px solid var(--color-border);
  height: var(--navbar-height);
  position: sticky;
  top: 0;
  z-index: 100;
}

.navbar-inner {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 1.5rem;
  height: 100%;
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.navbar-brand {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--color-primary);
  text-decoration: none;
  flex-shrink: 0;
}

.navbar-brand:hover {
  color: var(--color-primary-dark);
  text-decoration: none;
}

.brand-icon {
  display: flex;
}

.navbar-search {
  flex: 1;
  max-width: 420px;
  position: relative;
  margin: 0 auto;
}

.search-icon {
  position: absolute;
  left: 0.875rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--color-text-muted);
  pointer-events: none;
}

.navbar-search-input {
  width: 100%;
  padding: 0.5rem 1rem 0.5rem 2.5rem;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-pill);
  font-size: 0.875rem;
  font-family: inherit;
  background: var(--color-bg);
  transition: var(--transition);
}

.navbar-search-input:focus {
  outline: none;
  border-color: var(--color-primary);
  background: var(--color-surface);
  box-shadow: 0 0 0 3px rgba(139, 30, 63, 0.1);
}

.navbar-links {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  flex-shrink: 0;
  margin-left: auto;
}

.notif-bell {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: var(--radius-sm);
  color: var(--color-text-muted);
  transition: var(--transition);
  text-decoration: none;
}

.notif-bell:hover {
  background: var(--color-bg);
  color: var(--color-primary);
}

.notif-badge {
  position: absolute;
  top: 2px;
  right: 2px;
  background: var(--color-danger);
  color: #fff;
  font-size: 0.625rem;
  font-weight: 700;
  padding: 0.1rem 0.3rem;
  border-radius: var(--radius-pill);
  min-width: 16px;
  text-align: center;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.25rem 0.5rem;
  border-radius: var(--radius-sm);
  transition: var(--transition);
  font-family: inherit;
}

.user-profile:hover {
  background: var(--color-maroon-tint);
}

.user-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: var(--color-maroon-tint);
  color: var(--color-primary);
  font-size: 0.75rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
}

.user-info {
  display: flex;
  flex-direction: column;
  line-height: 1.25;
  text-align: left;
}

.user-name {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--color-text);
}

.user-role {
  font-size: 0.6875rem;
  color: var(--color-text-muted);
}

.user-profile-container {
  position: relative;
}

.dropdown-chevron {
  color: var(--color-text-muted);
  transition: transform var(--transition);
}

.user-profile:hover .dropdown-chevron {
  color: var(--color-primary);
}

.profile-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-md);
  padding: 0.375rem;
  width: 160px;
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
  z-index: 100;
  animation: fadeIn 0.15s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  font-size: 0.8125rem;
  color: var(--color-text);
  background: none;
  border: none;
  width: 100%;
  text-align: left;
  cursor: pointer;
  border-radius: var(--radius-sm);
  font-weight: 500;
  transition: var(--transition);
  font-family: inherit;
}

.dropdown-item:hover {
  background: var(--color-bg);
  color: var(--color-primary);
}

.dropdown-item.logout-item {
  color: var(--color-danger);
}

.dropdown-item.logout-item:hover {
  background: #FEF2F2;
  color: var(--color-danger);
}

.dropdown-overlay {
  position: fixed;
  inset: 0;
  z-index: 99;
}

@media (max-width: 900px) {
  .navbar-search {
    display: none;
  }
}

@media (max-width: 768px) {
  .navbar-inner {
    padding: 0 1rem;
  }

  .user-info {
    display: none;
  }
}
</style>
