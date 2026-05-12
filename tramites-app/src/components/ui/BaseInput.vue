<script setup lang="ts">
  let _counter = 0

  withDefaults(
    defineProps<{
      modelValue: string
      label?: string
      type?: string
      placeholder?: string
      error?: string
      required?: boolean
      disabled?: boolean
    }>(),
    {
      type: 'text',
      label: undefined,
      placeholder: undefined,
      error: undefined,
      required: false,
      disabled: false,
    },
  )

  const emit = defineEmits<{
    'update:modelValue': [value: string]
  }>()

  // ID único por instancia — useId no está re-exportado en Vue 3.5.34
  const uid = `bi-${++_counter}`
  const inputId = `input-${uid}`
  const errorId = `error-${uid}`
</script>

<template>
  <div class="flex flex-col gap-1">
    <!-- Label -->
    <label
      v-if="label"
      :for="inputId"
      class="text-sm font-medium text-slate-700"
      :class="{ 'after:ml-0.5 after:text-red-500 after:content-[\'*\']': required }"
    >
      {{ label }}
    </label>

    <!-- Input -->
    <input
      :id="inputId"
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      :disabled="disabled"
      :required="required"
      :aria-required="required"
      :aria-invalid="!!error"
      :aria-describedby="error ? errorId : undefined"
      class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-1 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500"
      :class="{
        'border-red-400 focus-visible:ring-red-400': error,
        'border-slate-300': !error,
      }"
      @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    />

    <!-- Mensaje de error -->
    <p
      v-if="error"
      :id="errorId"
      role="alert"
      class="text-xs text-red-600"
    >
      {{ error }}
    </p>
  </div>
</template>
