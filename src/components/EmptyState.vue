<script setup>
defineProps({
  title: { type: String, default: 'Nothing here yet' },
  description: { type: String, default: '' },
  actionLabel: { type: String, default: '' },
})

defineEmits(['action'])
</script>

<template>
  <div class="empty-state card">
    <div class="empty-icon" aria-hidden="true">
      <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>
    <h3>{{ title }}</h3>
    <p v-if="description">{{ description }}</p>
    <button v-if="actionLabel" type="button" class="btn btn-primary" @click="$emit('action')">
      {{ actionLabel }}
    </button>
  </div>
</template>

<style scoped>
.empty-state {
  text-align: center;
  padding: 3rem 1.5rem;
  color: var(--color-text-muted);
}

.empty-icon {
  color: var(--color-border);
  margin-bottom: 1rem;
}

.empty-state h3 {
  color: var(--color-text);
  font-weight: 600;
  font-size: 1.0625rem;
  margin-bottom: 0.5rem;
}

.empty-state p {
  margin-bottom: 1.25rem;
  max-width: 360px;
  margin-left: auto;
  margin-right: auto;
  font-size: 0.875rem;
  line-height: 1.6;
}
</style>
