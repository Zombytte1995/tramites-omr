<script setup lang="ts">
  import { useToast } from '@/composables/useToast'
  import BaseToast from './BaseToast.vue'

  const { toasts } = useToast()
</script>

<template>
  <!-- Posicionado fijo en la esquina inferior derecha, sobre todo el contenido -->
  <div
    aria-live="polite"
    aria-atomic="false"
    class="pointer-events-none fixed bottom-5 right-5 z-[100] flex flex-col items-end gap-2"
  >
    <TransitionGroup
      tag="div"
      class="flex flex-col items-end gap-2"
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="translate-x-full opacity-0"
      enter-to-class="translate-x-0 opacity-100"
      leave-active-class="absolute transition duration-200 ease-in"
      leave-from-class="translate-x-0 opacity-100"
      leave-to-class="translate-x-full opacity-0"
      move-class="transition-all duration-300"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="pointer-events-auto"
      >
        <BaseToast :toast="toast" />
      </div>
    </TransitionGroup>
  </div>
</template>
