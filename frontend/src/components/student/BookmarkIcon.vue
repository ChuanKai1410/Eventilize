<script setup>
defineProps({
  active: { type: Boolean, default: false },
  size: { type: String, default: 'md' },
  disabled: { type: Boolean, default: false },
  showLabel: { type: Boolean, default: false },
})

defineEmits(['toggle'])
</script>

<template>
  <button
    type="button"
    class="bookmark-icon-btn"
    :class="[`size-${size}`, { active, 'with-label': showLabel }]"
    :disabled="disabled"
    :aria-label="active ? 'Remove bookmark' : 'Bookmark event'"
    @click="$emit('toggle')"
  >
    <svg
      width="18"
      height="18"
      viewBox="0 0 24 24"
      :fill="active ? 'currentColor' : 'none'"
      stroke="currentColor"
      stroke-width="2"
      aria-hidden="true"
    >
      <path
        d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
    </svg>
    <span v-if="showLabel" class="bookmark-label">
      {{ active ? 'Bookmarked' : 'Bookmark' }}
    </span>
  </button>
</template>

<style scoped>
.bookmark-icon-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.375rem;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  background: var(--color-surface);
  color: var(--color-text-muted);
  cursor: pointer;
  transition: var(--transition);
  font-family: inherit;
  font-size: 0.8125rem;
  font-weight: 500;
}

.size-md {
  width: 36px;
  height: 36px;
}

.size-lg {
  width: auto;
  height: auto;
  padding: 0.5rem 0.875rem;
}

.with-label.size-lg {
  min-height: 40px;
}

.bookmark-icon-btn:hover:not(:disabled) {
  border-color: var(--color-primary);
  color: var(--color-primary);
  background: var(--color-maroon-tint);
}

.bookmark-icon-btn.active {
  color: var(--color-primary);
  border-color: var(--color-primary);
  background: var(--color-maroon-tint);
}

.bookmark-icon-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.bookmark-label {
  line-height: 1;
}
</style>
