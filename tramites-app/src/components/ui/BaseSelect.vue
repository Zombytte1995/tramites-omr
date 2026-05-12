<script setup lang="ts">
  export interface SelectOption {
    value: string | number
    label: string
  }

  let _counter = 0

  withDefaults(
    defineProps<{
      modelValue: string | number | null
      options: SelectOption[]
      label?: string
      placeholder?: string
      error?: string
      required?: boolean
      disabled?: boolean
    }>(),
    {
      label: undefined,
      placeholder: undefined,
      error: undefined,
      required: false,
      disabled: false,
    },
  )

  const emit = defineEmits<{
    'update:modelValue': [value: string | number | null]
  }>()

  const uid = `bs-${++_counter}`
  const selectId = `select-${uid}`
  const errorId = `serror-${uid}`

  function onChange(event: Event): void {
    const target = event.target as HTMLSelectElement
    const raw = target.value
    // Devuelve null cuando se selecciona el placeholder (value vacío)
    emit('update:modelValue', raw === '' ? null : raw)
  }
</script>

<template>
  <div class="flex flex-col gap-1">
    <label
      v-if="label"
      :for="selectId"
      class="text-sm font-medium text-slate-700"
      :class="{ 'after:ml-0.5 after:text-red-500 after:content-[\'*\']': required }"
    >
      {{ label }}
    </label>

    <select
      :id="selectId"
      :disabled="disabled"
      :required="required"
      :aria-required="required"
      :aria-invalid="!!error"
      :aria-describedby="error ? errorId : undefined"
      class="w-full rounded-md border bg-white px-3 py-2 text-sm text-slate-900 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-1 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500"
      :class="{
        'border-red-400 focus-visible:ring-red-400': error,
        'border-slate-300': !error,
      }"
      :value="modelValue ?? ''"
      @change="onChange"
    >
      <!-- Placeholder / Todos -->
      <option v-if="placeholder" value="" :disabled="required">
        {{ placeholder }}
      </option>

      <option
        v-for="opt in options"
        :key="opt.value"
        :value="opt.value"
      >
        {{ opt.label }}
      </option>
    </select>

    <p v-if="error" :id="errorId" role="alert" class="text-xs text-red-600">
      {{ error }}
    </p>
  </div>
</template>
