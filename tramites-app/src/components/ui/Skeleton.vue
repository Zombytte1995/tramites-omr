<script setup lang="ts">
  withDefaults(
    defineProps<{
      /** Forma del skeleton: bloque de texto, tarjeta o celda de tabla */
      variant?: 'text' | 'card' | 'table-row'
      /** Líneas a mostrar en el variant 'text' */
      lines?: number
    }>(),
    { variant: 'text', lines: 3 },
  )
</script>

<template>
  <!-- text: líneas de ancho variable, la última más corta -->
  <div v-if="variant === 'text'" class="space-y-2" aria-hidden="true">
    <div
      v-for="i in lines"
      :key="i"
      class="h-4 animate-pulse rounded-md bg-slate-200"
      :class="i === lines ? 'w-3/4' : 'w-full'"
    />
  </div>

  <!-- card: header + cuerpo de tres líneas -->
  <div
    v-else-if="variant === 'card'"
    class="animate-pulse space-y-3 rounded-lg border border-slate-200 p-5"
    aria-hidden="true"
  >
    <div class="h-5 w-2/5 rounded-md bg-slate-300" />
    <div class="space-y-2">
      <div class="h-4 w-full rounded-md bg-slate-200" />
      <div class="h-4 w-5/6 rounded-md bg-slate-200" />
      <div class="h-4 w-4/6 rounded-md bg-slate-200" />
    </div>
  </div>

  <!-- table-row: fila de tabla con 5 celdas (usa dentro de <tbody>) -->
  <tr v-else class="animate-pulse" aria-hidden="true">
    <td v-for="w in ['w-28', 'w-48', 'w-24', 'w-20', 'w-14']" :key="w" class="px-4 py-3">
      <div class="h-4 rounded-md bg-slate-200" :class="w" />
    </td>
  </tr>
</template>
