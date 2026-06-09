<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: String,
    required: true,
  },
})

const badgeClass = computed(() => {
  const map = {
    Approved: 'badge-approved',
    Pending: 'badge-pending',
    Rejected: 'badge-rejected',
    Draft: 'badge-draft',
  }
  return map[props.status] || 'badge-draft'
})
</script>

<template>
  <span class="status-badge" :class="badgeClass">{{ status }}</span>
</template>

<style scoped>
.status-badge {
  display: inline-block;
  padding: 0.25rem 0.625rem;
  font-size: 0.6875rem;
  font-weight: 600;
  border-radius: var(--radius-pill);
  text-transform: capitalize;
  letter-spacing: 0.02em;
}

.badge-approved {
  background: #DCFCE7;
  color: #15803D;
}

.badge-pending {
  background: #FEF3C7;
  color: #B45309;
}

.badge-rejected {
  background: #FEE2E2;
  color: #DC2626;
}

.badge-draft {
  background: #F3F4F6;
  color: var(--color-draft);
}
</style>
