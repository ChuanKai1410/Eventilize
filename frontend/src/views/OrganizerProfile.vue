<template>
  <ProtectedLayout>
    <div class="profile-container">
      <h2>Organizer Profile</h2>

      <div class="profile-card">
        <p><strong>Name:</strong> {{ profile.name }}</p>
        <p><strong>Email:</strong> {{ profile.email }}</p>
        <p><strong>Role:</strong> {{ profile.role }}</p>
        <p><strong>Organization:</strong> {{ profile.organization }}</p>
        <p><strong>Faculty:</strong> {{ profile.faculty }}</p>
      </div>
    </div>
  </ProtectedLayout>
</template>

<script setup>
import ProtectedLayout from "../components/ProtectedLayout.vue"
import { mockOrganizerProfile } from "../data/mockData.js"

const profile = mockOrganizerProfile
</script>

<style scoped>
.profile-container {
  padding: 20px;
}

.profile-card {
  background: #f5f5f5;
  padding: 20px;
  border-radius: 10px;
  width: 400px;
}
</style>