<script setup lang="ts">
  import { computed } from 'vue'

  type Variant = 'primary' | 'secondary' | 'danger' | 'ghost'
  type Size = 'sm' | 'md' | 'lg'

  const props = withDefaults(
    defineProps<{
      variant?: Variant
      size?: Size
      loading?: boolean
      disabled?: boolean
      type?: 'button' | 'submit' | 'reset'
    }>(),
    {
      variant: 'primary',
      size: 'md',
      loading: false,
      disabled: false,
      type: 'button',
    },
  )

  const variantClasses: Record<Variant, string> = {
    primary:
      'bg-indigo-600 text-white shadow-sm hover:bg-indigo-700 focus-visible:ring-indigo-500',
    secondary:
      'bg-white text-slate-700 shadow-sm ring-1 ring-slate-300 hover:bg-slate-50 focus-visible:ring-indigo-500',
    danger:
      'bg-red-600 text-white shadow-sm hover:bg-red-700 focus-visible:ring-red-500',
    ghost:
      'bg-transparent text-slate-700 hover:bg-slate-100 focus-visible:ring-indigo-500',
  }

  const sizeClasses: Record<Size, string> = {
    sm: 'px-3 py-1.5 text-xs gap-1.5',
    md: 'px-4 py-2 text-sm gap-2',
    lg: 'px-6 py-3 text-base gap-2.5',
  }

  const isDisabled = computed(() => props.disabled || props.loading)
</script>

<template>
  <button
    :type="type"
    :disabled="isDisabled"
    :aria-busy="loading"
    :aria-disabled="isDisabled"
    class="inline-flex items-center justify-center rounded-md font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-60"
    :class="[variantClasses[variant], sizeClasses[size]]"
  >
    <!-- Spinner en estado loading -->
    <svg
      v-if="loading"
      class="animate-spin"
      :class="size === 'sm' ? 'h-3.5 w-3.5' : 'h-4 w-4'"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      aria-hidden="true"
    >
      <circle
        class="opacity-25"
        cx="12"
        cy="12"
        r="10"
        stroke="currentColor"
        stroke-width="4"
      />
      <path
        class="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8V0C5.373 0 22 12h-4z"
      />
    </svg>

    <!-- Icono izquierdo (sin spinner activo) -->
    <span v-if="!loading && $slots['icon-left']" aria-hidden="true">
      <slot name="icon-left" />
    </span>

    <!-- Texto del botón -->
    <slot />

    <!-- Icono derecho -->
    <span v-if="$slots['icon-right']" aria-hidden="true">
      <slot name="icon-right" />
    </span>
  </button>
</template>
