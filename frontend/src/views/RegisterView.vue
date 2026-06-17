<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import FormInput from '../components/FormInput.vue'
import AuthLayout from '../components/AuthLayout.vue'
import { useAuth } from '../composables/useAuth.js'

const router = useRouter()
const { register, getDashboardRoute } = useAuth()

const name = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const role = ref('student')
const errors = ref({})
const apiError = ref('')
const loading = ref(false)

function validateEmail(value) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
}

function validate() {
  errors.value = {}
  if (!name.value.trim()) errors.value.name = 'Full name is required.'
  if (!email.value.trim()) errors.value.email = 'Email is required.'
  else if (!validateEmail(email.value)) errors.value.email = 'Please enter a valid email address.'
  if (!password.value) errors.value.password = 'Password is required.'
  else if (password.value.length < 8) errors.value.password = 'Password must be at least 8 characters.'
  if (!confirmPassword.value) errors.value.confirmPassword = 'Please confirm your password.'
  else if (confirmPassword.value !== password.value) errors.value.confirmPassword = 'Passwords do not match.'
  if (!role.value) errors.value.role = 'Please select a role.'
  return Object.keys(errors.value).length === 0
}

async function handleSubmit() {
  apiError.value = ''
  if (!validate()) return

  loading.value = true
  const result = await register({
    name: name.value.trim(),
    email: email.value.trim(),
    password: password.value,
    role: role.value,
  })
  loading.value = false
  if (result.success) {
    router.push(getDashboardRoute())
  } else {
    apiError.value = result.message
    if (result.errors) errors.value = { ...errors.value, ...result.errors }
  }
}
</script>

<template>
  <AuthLayout title="Create Account" subtitle="Join Eventilize as a student or organizer">
    <div v-if="apiError" class="alert alert-error">{{ apiError }}</div>

    <form @submit.prevent="handleSubmit">
      <FormInput
        id="reg-name"
        v-model="name"
        label="Full Name"
        placeholder="Your full name"
        required
        :error="errors.name"
      />
      <FormInput
        id="reg-email"
        v-model="email"
        label="Email"
        type="email"
        placeholder="you@utm.my"
        required
        :error="errors.email"
      />
      <FormInput
        id="reg-password"
        v-model="password"
        label="Password"
        type="password"
        placeholder="Minimum 8 characters"
        required
        :error="errors.password"
      />
      <FormInput
        id="reg-confirm"
        v-model="confirmPassword"
        label="Confirm Password"
        type="password"
        placeholder="Re-enter password"
        required
        :error="errors.confirmPassword"
      />
      <div class="form-group">
        <label class="form-label">Role <span class="required">*</span></label>
        <select v-model="role" class="form-select" :class="{ error: errors.role }">
          <option value="student">Student</option>
          <option value="organizer">Organizer</option>
        </select>
        <p v-if="errors.role" class="form-error">{{ errors.role }}</p>
      </div>
      <button type="submit" class="btn btn-primary btn-lg auth-submit" :disabled="loading">
        {{ loading ? 'Creating account...' : 'Register' }}
      </button>
    </form>

    <p class="auth-footer">
      Already have an account?
      <router-link to="/login">Login</router-link>
    </p>
  </AuthLayout>
</template>

<style scoped>
.required {
  color: var(--color-danger);
}
</style>
