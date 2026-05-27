<script setup>
defineProps({
  label: { type: String, required: true },
  value: { type: [String, Number], required: true },
  icon: { type: String, default: 'chart' },
  trend: { type: String, default: '' },
})
</script>

<template>
  <div class="analytics-card card">
    <div class="card-body">
      <div class="analytics-icon" aria-hidden="true">
        <svg v-if="icon === 'chart'" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M18 20V10M12 20V4M6 20v-6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <svg v-else-if="icon === 'calendar'" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <svg v-else-if="icon === 'bookmark'" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <svg v-else-if="icon === 'bell'" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <p class="analytics-value">{{ value }}</p>
      <p class="analytics-label">{{ label }}</p>
      <p v-if="trend" class="analytics-trend">{{ trend }}</p>
    </div>
  </div>
</template>

<style scoped>
.analytics-card {
  transition: var(--transition);
}

.analytics-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.analytics-icon {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-sm);
  background: var(--color-maroon-tint);
  color: var(--color-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 0.75rem;
}

.analytics-value {
  font-size: 1.625rem;
  font-weight: 700;
  color: var(--color-text);
  line-height: 1.2;
}

.analytics-label {
  font-size: 0.8125rem;
  color: var(--color-text-muted);
  margin-top: 0.25rem;
  font-weight: 500;
}

.analytics-trend {
  font-size: 0.75rem;
  color: var(--color-success);
  margin-top: 0.375rem;
  font-weight: 500;
}
</style>
