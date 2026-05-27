<script setup>
defineProps({
  label: { type: String, default: '' },
  type: { type: String, default: 'text' },
  modelValue: { type: [String, Number], default: '' },
  error: { type: String, default: '' },
  hint: { type: String, default: '' },
  placeholder: { type: String, default: '' },
  required: { type: Boolean, default: false },
  id: { type: String, default: '' },
  rows: { type: Number, default: 4 },
})

defineEmits(['update:modelValue'])
</script>

<template>
  <div class="form-group">
    <label v-if="label" :for="id" class="form-label">
      {{ label }}
      <span v-if="required" class="required">*</span>
    </label>
    <textarea
      v-if="type === 'textarea'"
      :id="id"
      class="form-textarea"
      :class="{ error: error }"
      :value="modelValue"
      :placeholder="placeholder"
      :rows="rows"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <select
      v-else-if="type === 'select'"
      :id="id"
      class="form-select"
      :class="{ error: error }"
      :value="modelValue"
      @change="$emit('update:modelValue', $event.target.value)"
    >
      <slot />
    </select>
    <input
      v-else
      :id="id"
      :type="type"
      class="form-input"
      :class="{ error: error }"
      :value="modelValue"
      :placeholder="placeholder"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <p v-if="error" class="form-error">{{ error }}</p>
    <p v-else-if="hint" class="form-hint">{{ hint }}</p>
  </div>
</template>

<style scoped>
.required {
  color: var(--color-danger);
}
</style>
