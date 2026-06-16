<script setup>
import { computed, ref, watch } from 'vue'
import { useEventStore } from '../composables/useEventStore.js'

const emit = defineEmits(['filter'])

const { events, categories } = useEventStore()

const filters = ref({
  category: '',
  date: '',
  location: '',
  organizer: '',
  sort: '',
})

const locations = computed(() => [...new Set(events.value.map((e) => e.location))].sort())
const organizers = computed(() => [...new Set(events.value.map((e) => e.organizer))].sort())

watch(filters, () => emit('filter', { ...filters.value }), { deep: true })

function resetFilters() {
  filters.value = {
    category: '',
    date: '',
    location: '',
    organizer: '',
    sort: '',
  }
  emit('filter', { ...filters.value })
}

defineExpose({ resetFilters })
</script>

<template>
  <div class="filter-bar card">
    <div class="card-body filter-grid">
      <div class="filter-item">
        <label class="form-label">Category</label>
        <select v-model="filters.category" class="form-select">
          <option value="">All categories</option>
          <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
        </select>
      </div>
      <div class="filter-item">
        <label class="form-label">Date</label>
        <input v-model="filters.date" type="date" class="form-input" />
      </div>
      <div class="filter-item">
        <label class="form-label">Location</label>
        <select v-model="filters.location" class="form-select">
          <option value="">All locations</option>
          <option v-for="loc in locations" :key="loc" :value="loc">{{ loc }}</option>
        </select>
      </div>
      <div class="filter-item">
        <label class="form-label">Organizer</label>
        <select v-model="filters.organizer" class="form-select">
          <option value="">All organizers</option>
          <option v-for="org in organizers" :key="org" :value="org">{{ org }}</option>
        </select>
      </div>
      <div class="filter-item">
        <label class="form-label">Sort</label>
        <select v-model="filters.sort" class="form-select">
          <option value="">Default</option>
          <option value="popular">Popular</option>
          <option value="upcoming">Upcoming</option>
        </select>
      </div>
      <div class="filter-item filter-reset">
        <button type="button" class="btn btn-ghost btn-sm" @click="resetFilters">Reset filters</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.filter-bar {
  box-shadow: var(--shadow-sm);
}

.filter-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 1rem;
  align-items: end;
}

.filter-item .form-label {
  font-size: 0.75rem;
  margin-bottom: 0.3rem;
}

.filter-reset {
  display: flex;
  align-items: flex-end;
}
</style>
