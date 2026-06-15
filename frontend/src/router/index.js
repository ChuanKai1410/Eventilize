import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from '../composables/useAuth.js'

const routes = [
  {
    path: '/',
    name: 'landing',
    component: () => import('../views/LandingView.vue'),
    meta: { public: true },
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/LoginView.vue'),
    meta: { public: true, guestOnly: true },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('../views/RegisterView.vue'),
    meta: { public: true, guestOnly: true },
  },
  {
    path: '/student/dashboard',
    name: 'student-dashboard',
    component: () => import('../views/StudentDashboardView.vue'),
    meta: { requiresAuth: true, roles: ['student'] },
  },
  {
    path: '/student/profile',
    name: 'student-profile',
    component: () => import('../views/StudentProfileView.vue'),
    meta: { requiresAuth: true, roles: ['student'] },
  },
  {
    path: '/events',
    name: 'events',
    component: () => import('../views/EventDiscoveryView.vue'),
    meta: { public: true },
  },
  {
    path: '/events/:id',
    name: 'event-detail',
    component: () => import('../views/EventDetailView.vue'),
    meta: { public: true },
  },
  {
    path: '/bookmarks',
    name: 'bookmarks',
    component: () => import('../views/BookmarkView.vue'),
    meta: { requiresAuth: true, roles: ['student'] },
  },
  {
    path: '/notifications',
    name: 'notifications',
    component: () => import('../views/NotificationView.vue'),
    meta: { requiresAuth: true, roles: ['student'] },
  },
  {
    path: '/organizer/dashboard',
    name: 'organizer-dashboard',
    component: () => import('../views/OrganizerDashboardView.vue'),
    meta: { requiresAuth: true, roles: ['organizer'] },
  },
  {
    path: '/organizer/events',
    name: 'organizer-events',
    component: () => import('../views/OrganizerEventManagementView.vue'),
    meta: { requiresAuth: true, roles: ['organizer'] },
  },
  {
    path: '/organizer/events/create',
    name: 'organizer-event-create',
    component: () => import('../views/OrganizerEventFormView.vue'),
    meta: { requiresAuth: true, roles: ['organizer'] },
  },
  {
    path: '/organizer/events/:id/edit',
    name: 'organizer-event-edit',
    component: () => import('../views/OrganizerEventFormView.vue'),
    meta: { requiresAuth: true, roles: ['organizer'] },
  },
  {
    path: "/organizer/profile",
    name: "OrganizerProfile",
    component: () => import("../views/OrganizerProfile.vue"),
    meta: { requiresAuth: true, roles: ['organizer'] },
  },
  {
    path: '/admin/dashboard',
    name: 'admin-dashboard',
    component: () => import('../views/AdminDashboardView.vue'),
    meta: { requiresAuth: true, roles: ['admin'] },
  },
  {
    path: '/admin/approvals',
    name: 'admin-approvals',
    component: () => import('../views/AdminApprovalView.vue'),
    meta: { requiresAuth: true, roles: ['admin'] },
  },
  {
    path: '/admin/events',
    name: 'admin-events',
    component: () => import('../views/AdminEventManagementView.vue'),
    meta: { requiresAuth: true, roles: ['admin'] },
  },
  {
    path: '/admin/settings',
    name: 'admin-settings',
    component: () => import('../views/AdminSettingsView.vue'),
    meta: { requiresAuth: true, roles: ['admin'] },
  },
  {
    path: '/admin/profile',
    name: 'admin-profile',
    component: () => import('../views/AdminProfileView.vue'),
    meta: { requiresAuth: true, roles: ['admin'] },
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})

// Navigation guard placeholder — will connect to JWT backend later
router.beforeEach((to, _from, next) => {
  const { user, isAuthenticated, getDashboardRoute } = useAuth()

  if (to.meta.guestOnly && isAuthenticated.value) {
    return next(getDashboardRoute())
  }

  if (to.meta.requiresAuth && !isAuthenticated.value) {
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }

  if (to.meta.roles && isAuthenticated.value) {
    if (!to.meta.roles.includes(user.value?.role)) {
      return next(getDashboardRoute())
    }
  }

  next()
})

export default router
