<script setup lang="ts">
  import { useSlots } from 'vue'
  import Skeleton from './Skeleton.vue'

  export interface TableColumn {
    key: string
    label: string
    /** Función de formato opcional. Recibe el valor crudo y la fila completa. */
    formatter?: (value: unknown, row: Record<string, unknown>) => string
  }

  withDefaults(
    defineProps<{
      columns: TableColumn[]
      rows: Record<string, unknown>[]
      loading?: boolean
      emptyMessage?: string
    }>(),
    {
      loading: false,
      emptyMessage: 'No hay registros disponibles.',
    },
  )

  const slots = useSlots()

  const hasCellSlot = (key: string): boolean => !!slots[`cell-${key}`]

  function formatCell(col: TableColumn, row: Record<string, unknown>): string {
    const value = row[col.key]
    if (col.formatter) return col.formatter(value, row)
    return value === null || value === undefined ? '—' : String(value)
  }
</script>

<template>
  <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <!-- Encabezado -->
        <thead class="border-b border-slate-200 bg-slate-50">
          <tr>
            <th
              v-for="col in columns"
              :key="col.key"
              scope="col"
              class="px-4 py-3 font-semibold text-slate-600"
            >
              {{ col.label }}
            </th>
            <!-- Columna de acciones si se pasa el slot #actions -->
            <th
              v-if="$slots.actions"
              scope="col"
              class="px-4 py-3 text-right font-semibold text-slate-600"
            >
              <span class="sr-only">Acciones</span>
            </th>
          </tr>
        </thead>

        <tbody class="divide-y divide-slate-100">
          <!-- Estado de carga: 5 filas skeleton -->
          <template v-if="loading">
            <Skeleton
              v-for="i in 5"
              :key="`sk-${i}`"
              variant="table-row"
            />
          </template>

          <!-- Filas de datos -->
          <template v-else-if="rows.length > 0">
            <tr
              v-for="(row, rowIdx) in rows"
              :key="rowIdx"
              class="group transition-colors hover:bg-slate-50"
            >
              <td
                v-for="col in columns"
                :key="col.key"
                class="px-4 py-3 text-slate-700"
              >
                <!-- Slot por columna: #cell-{key} con { value, row } -->
                <slot
                  v-if="hasCellSlot(col.key)"
                  :name="`cell-${col.key}`"
                  :value="row[col.key]"
                  :row="row"
                />
                <span v-else>{{ formatCell(col, row) }}</span>
              </td>

              <!-- Slot de acciones por fila -->
              <td
                v-if="$slots.actions"
                class="px-4 py-3 text-right"
              >
                <slot name="actions" :row="row" />
              </td>
            </tr>
          </template>

          <!-- Estado vacío -->
          <tr v-else>
            <td
              :colspan="columns.length + ($slots.actions ? 1 : 0)"
              class="px-4 py-12 text-center text-sm text-slate-400"
            >
              <div class="flex flex-col items-center gap-2">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-10 w-10 text-slate-300"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  aria-hidden="true"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                  />
                </svg>
                <span>{{ emptyMessage }}</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
