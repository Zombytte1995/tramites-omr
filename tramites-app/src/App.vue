<script setup lang="ts">
  import { computed } from 'vue'
  import { RouterView, useRoute } from 'vue-router'
  import MainLayout from '@/components/layout/MainLayout.vue'
  import AuthLayout from '@/components/layout/AuthLayout.vue'
  import ToastContainer from '@/components/ui/ToastContainer.vue'

  const route = useRoute()

  /**
   * Layout dinámico basado en route.meta.layout.
   * 'auth' → AuthLayout (solo slot, sin sidebar)
   * cualquier otro (undefined/'main') → MainLayout (sidebar + topbar)
   */
  const Layout = computed(() =>
    route.meta.layout === 'auth' ? AuthLayout : MainLayout,
  )
</script>

<template>
  <!--
    component :is= resuelve el layout correcto por ruta.
    RouterView va DENTRO del layout como slot, no fuera.
  -->
  <component :is="Layout">
    <RouterView />
  </component>

  <!--
    ToastContainer fuera del layout (fixed position) para que funcione
    tanto en vistas con MainLayout como en AuthLayout (ej. errores de login).
  -->
  <ToastContainer />
</template>
