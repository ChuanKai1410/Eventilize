<script setup>
import Navbar from './Navbar.vue'
import Sidebar from './Sidebar.vue'

defineProps({
  showSidebar: { type: Boolean, default: true },
})
</script>

<template>
  <div class="protected-layout">
    <Navbar />
    <div class="layout-body">
      <Sidebar v-if="showSidebar" />
      <main class="layout-main">
        <slot />
      </main>
    </div>
  </div>
</template>

<style scoped>
.protected-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: var(--color-bg);
}

.layout-body {
  display: flex;
  flex: 1;
}

.layout-main {
  flex: 1;
  min-width: 0;
}

@media (max-width: 768px) {
  .layout-body {
    flex-direction: column;
  }
}
</style>
