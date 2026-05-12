<script setup lang="ts">
  import { useRoute } from 'vue-router'

  // Determina el modo a partir de la ruta: 'tramite-crear' → crear, cualquier otro → editar.
  // Cuando se implemente, también recibirá los datos del trámite vía useTramitesStore.
  const route = useRoute()
  const isEdit = route.name === 'tramite-editar'
</script>

<template>
  <main class="p-8">
    <h1 class="text-2xl font-bold text-gray-900">
      {{ isEdit ? 'Editar trámite' : 'Nuevo trámite' }}
    </h1>
    <p class="mt-2 text-gray-500">Vista en construcción.</p>
  </main>
</template>
