<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  placeholder: { type: String, default: 'Search events...' },
  modelValue: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue', 'search'])

const localValue = ref(props.modelValue)
let debounceTimer = null

watch(
  () => props.modelValue,
  (val) => {
    localValue.value = val
  }
)

function onInput() {
  emit('update:modelValue', localValue.value)
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    emit('search', localValue.value)
  }, 350)
}

function clearSearch() {
  localValue.value = ''
  emit('update:modelValue', '')
  emit('search', '')
}
</script>

<template>
  <div class="search-bar">
    <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
      <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    <input
      v-model="localValue"
      type="search"
      class="search-input"
      :placeholder="placeholder"
      @input="onInput"
    />
    <button
      v-if="localValue"
      type="button"
      class="clear-btn"
      aria-label="Clear search"
      @click="clearSearch"
    >
      ×
    </button>
  </div>
</template>

<style scoped>
.search-bar {
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 0.875rem;
  color: var(--color-text-muted);
  pointer-events: none;
}

.search-input {
  width: 100%;
  padding: 0.625rem 2.5rem 0.625rem 2.625rem;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  font-size: 0.875rem;
  font-family: inherit;
  background: var(--color-surface);
  transition: var(--transition);
}

.search-input:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(139, 30, 63, 0.1);
}

.clear-btn {
  position: absolute;
  right: 0.5rem;
  background: none;
  border: none;
  font-size: 1.125rem;
  color: var(--color-text-muted);
  cursor: pointer;
  padding: 0.25rem 0.5rem;
  line-height: 1;
  transition: var(--transition);
}

.clear-btn:hover {
  color: var(--color-text);
}
</style>
