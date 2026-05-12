<script setup lang="ts">
  import {
    CheckCircleIcon,
    ExclamationCircleIcon,
    ExclamationTriangleIcon,
    InformationCircleIcon,
    XMarkIcon,
  } from '@heroicons/vue/24/solid'
  import { useToast } from '@/composables/useToast'
  import type { Toast, ToastType } from '@/composables/useToast'

  defineProps<{ toast: Toast }>()

  const { dismiss } = useToast()

  type ToastStyle = { bg: string; text: string; icon: typeof CheckCircleIcon }

  const styles: Record<ToastType, ToastStyle> = {
    success: {
      bg: 'bg-green-50 ring-green-200',
      text: 'text-green-800',
      icon: CheckCircleIcon,
    },
    error: {
      bg: 'bg-red-50 ring-red-200',
      text: 'text-red-800',
      icon: ExclamationCircleIcon,
    },
    warning: {
      bg: 'bg-yellow-50 ring-yellow-200',
      text: 'text-yellow-800',
      icon: ExclamationTriangleIcon,
    },
    info: {
      bg: 'bg-blue-50 ring-blue-200',
      text: 'text-blue-800',
      icon: InformationCircleIcon,
    },
  }
</script>

<template>
  <div
    role="alert"
    aria-live="assertive"
    class="flex w-80 items-start gap-3 rounded-lg px-4 py-3 shadow-lg ring-1"
    :class="styles[toast.type].bg"
  >
    <!-- Icono del tipo -->
    <component
      :is="styles[toast.type].icon"
      class="mt-0.5 h-5 w-5 shrink-0"
      :class="styles[toast.type].text"
      aria-hidden="true"
    />

    <!-- Mensaje -->
    <p class="flex-1 text-sm font-medium" :class="styles[toast.type].text">
      {{ toast.message }}
    </p>

    <!-- Botón de cierre manual -->
    <button
      type="button"
      :aria-label="`Cerrar notificación: ${toast.message}`"
      class="shrink-0 rounded p-0.5 transition-colors hover:bg-black/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-current"
      :class="styles[toast.type].text"
      @click="dismiss(toast.id)"
    >
      <XMarkIcon class="h-4 w-4" aria-hidden="true" />
    </button>
  </div>
</template>
