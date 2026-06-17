<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import FormInput from '../components/FormInput.vue'
import AuthLayout from '../components/AuthLayout.vue'
import { useAuth } from '../composables/useAuth.js'

const router = useRouter()
const { login, getDashboardRoute } = useAuth()

const email = ref('')
const password = ref('')
const errors = ref({})
const apiError = ref('')
const loading = ref(false)

function validateEmail(value) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
}

function validate() {
  errors.value = {}
  if (!email.value.trim()) errors.value.email = 'Email is required.'
  else if (!validateEmail(email.value)) errors.value.email = 'Please enter a valid email address.'
  if (!password.value) errors.value.password = 'Password is required.'
  return Object.keys(errors.value).length === 0
}

async function handleSubmit() {
  apiError.value = ''
  if (!validate()) return

  loading.value = true
  const result = await login(email.value.trim(), password.value)
  loading.value = false
  if (result.success) {
    const redirect = router.currentRoute.value.query.redirect
    router.push(typeof redirect === 'string' ? redirect : getDashboardRoute())
  } else {
    apiError.value = result.message
  }
}
</script>

<template>
  <AuthLayout title="Welcome Back" subtitle="Sign in to your Eventilize account">
    <div v-if="apiError" class="alert alert-error">{{ apiError }}</div>

    <form @submit.prevent="handleSubmit">
      <FormInput
        id="login-email"
        v-model="email"
        label="Email"
        type="email"
        placeholder="you@utm.my"
        required
        :error="errors.email"
      />
      <FormInput
        id="login-password"
        v-model="password"
        label="Password"
        type="password"
        placeholder="Enter your password"
        required
        :error="errors.password"
      />
      <p class="form-hint role-hint">
        Demo: student@utm.my / student123 · organizer@utm.my / organizer123 · admin@utm.my / admin1234
      </p>
      <button type="submit" class="btn btn-primary btn-lg auth-submit" :disabled="loading">
        {{ loading ? 'Signing in...' : 'Login' }}
      </button>
    </form>

    <p class="auth-footer">
      Don't have an account?
      <router-link to="/register">Register</router-link>
    </p>
  </AuthLayout>
</template>

<style scoped>
.role-hint {
  margin-bottom: 1rem;
  font-size: 0.75rem;
  line-height: 1.5;
}
</style>
