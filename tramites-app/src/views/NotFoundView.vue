<script setup lang="ts">
  import { useRouter } from 'vue-router'

  const router = useRouter()
</script>

<template>
  <main class="flex min-h-screen flex-col items-center justify-center p-8 text-center">
    <p class="text-6xl font-bold text-gray-300">404</p>
    <h1 class="mt-4 text-2xl font-bold text-gray-900">Página no encontrada</h1>
    <p class="mt-2 text-gray-500">La ruta que buscas no existe.</p>
    <button
      class="mt-6 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
      @click="router.push('/dashboard')"
    >
      Ir al inicio
    </button>
  </main>
</template>
