<script setup lang="ts">
  import { RouterView } from 'vue-router'
  import ToastContainer from '@/components/ui/ToastContainer.vue'
</script>

<template>
  <RouterView />
  <!-- Toasts globales: se posicionan fixed, no afectan el layout -->
  <ToastContainer />
</template>
