<script setup>
defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
})
</script>

<template>
  <div class="auth-split">
    <aside class="auth-hero">
      <div class="auth-hero-inner">
        <router-link to="/" class="auth-brand">Eventilize</router-link>
        <div class="auth-slogan">
          <span>Discover.</span>
          <span>Connect.</span>
          <span class="highlight">Experience.</span>
        </div>
        <p class="auth-tagline">
          Your centralized hub for UTM campus events — find, bookmark, and stay updated.
        </p>
        <ul class="auth-features">
          <li>
            <span class="feature-icon" aria-hidden="true">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
            Find Events
          </li>
          <li>
            <span class="feature-icon" aria-hidden="true">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
            Bookmark
          </li>
          <li>
            <span class="feature-icon" aria-hidden="true">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
            Stay Updated
          </li>
        </ul>
      </div>
    </aside>

    <main class="auth-form-panel">
      <div class="auth-form-inner">
        <router-link to="/" class="auth-back-link">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Back to Home
        </router-link>
        <h1>{{ title }}</h1>
        <p v-if="subtitle" class="auth-subtitle">{{ subtitle }}</p>
        <slot />
      </div>
    </main>
  </div>
</template>

<style scoped>
.auth-split {
  min-height: 100vh;
  display: grid;
  grid-template-columns: 1fr 1fr;
}

.auth-hero {
  background: linear-gradient(160deg, var(--color-primary-dark) 0%, var(--color-primary) 45%, var(--color-accent) 100%);
  color: #fff;
  display: flex;
  align-items: center;
  padding: 3rem;
}

.auth-hero-inner {
  max-width: 420px;
}

.auth-brand {
  display: inline-block;
  font-size: 1.125rem;
  font-weight: 700;
  color: #fff;
  text-decoration: none;
  margin-bottom: 3rem;
  letter-spacing: 0.02em;
}

.auth-brand:hover {
  text-decoration: none;
  opacity: 0.9;
}

.auth-slogan {
  display: flex;
  flex-direction: column;
  font-size: clamp(2rem, 4vw, 2.75rem);
  font-weight: 700;
  line-height: 1.15;
  margin-bottom: 1.25rem;
}

.auth-slogan .highlight {
  color: var(--color-gold);
}

.auth-tagline {
  font-size: 0.9375rem;
  opacity: 0.88;
  line-height: 1.65;
  margin-bottom: 2.5rem;
  font-weight: 400;
}

.auth-features {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.auth-features li {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.9375rem;
  font-weight: 500;
}

.feature-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  background: rgba(255, 255, 255, 0.12);
  border-radius: var(--radius-sm);
}

.auth-back-link {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: var(--color-text-muted);
  text-decoration: none;
  margin-bottom: 1.5rem;
  font-weight: 500;
  transition: var(--transition);
}

.auth-back-link:hover {
  color: var(--color-primary);
  text-decoration: none;
}

.auth-form-panel {
  background: var(--color-surface);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.auth-form-inner {
  width: 100%;
  max-width: 400px;
}

.auth-form-inner h1 {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--color-text);
  margin-bottom: 0.375rem;
}

.auth-subtitle {
  color: var(--color-text-muted);
  font-size: 0.9375rem;
  margin-bottom: 1.75rem;
  font-weight: 400;
}

@media (max-width: 900px) {
  .auth-split {
    grid-template-columns: 1fr;
  }

  .auth-hero {
    padding: 2rem 1.5rem;
    min-height: auto;
  }

  .auth-brand {
    margin-bottom: 1.5rem;
  }

  .auth-slogan {
    font-size: 1.75rem;
  }

  .auth-tagline {
    margin-bottom: 1.5rem;
  }

  .auth-form-panel {
    padding: 2rem 1.5rem 3rem;
  }
}
</style>
