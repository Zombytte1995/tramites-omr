<script setup lang="ts">
  import { computed, ref, watch } from 'vue'
  import { useRoute, useRouter } from 'vue-router'
  import {
    ArrowDownTrayIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    EyeIcon,
    MagnifyingGlassIcon,
    PencilSquareIcon,
    TrashIcon,
    XMarkIcon,
  } from '@heroicons/vue/24/outline'
  import { exportTramites } from '@/api/tramites'
  import { useInstitucionesStore } from '@/stores/instituciones'
  import { useTramitesStore } from '@/stores/tramites'
  import { useToast } from '@/composables/useToast'
  import { useDebounce } from '@/composables/useDebounce'
  import { useAsync } from '@/composables/useAsync'
  import BaseButton from '@/components/ui/BaseButton.vue'
  import BaseSelect from '@/components/ui/BaseSelect.vue'
  import BaseTable from '@/components/ui/BaseTable.vue'
  import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'
  import TramiteFormModal from '@/components/tramites/TramiteFormModal.vue'
  import type { TableColumn } from '@/components/ui/BaseTable.vue'
  import type { SelectOption } from '@/components/ui/BaseSelect.vue'
  import type { Tramite, TramiteFilters } from '@/types'

  // ── Stores & composables ──────────────────────────────────────────────────────
  const route = useRoute()
  const router = useRouter()
  const tramitesStore = useTramitesStore()
  const institucionesStore = useInstitucionesStore()
  const toast = useToast()

  // ── Estado de filtros — inicializado desde query params (deep linking) ────────
  const searchInput = ref(String(route.query.search ?? ''))
  const selectedInstitucion = ref<string | number | null>(
    route.query.institucionId ? String(route.query.institucionId) : null,
  )
  const currentPage = ref(route.query.page ? Number(route.query.page) : 1)

  // Búsqueda con debounce de 300 ms para no disparar fetch en cada tecla
  const debouncedSearch = useDebounce(searchInput, 300)

  // ── Carga inicial (useAsync + immediate) ──────────────────────────────────────
  /**
   * Carga instituciones (para el select) y el primer listado de trámites
   * en paralelo. El loading de useAsync muestra un spinner de página
   * completa mientras llega la primera respuesta.
   */
  const { loading: pageLoading } = useAsync(
    async () => {
      await Promise.all([
        institucionesStore.instituciones.length === 0
          ? institucionesStore.fetchAll()
          : Promise.resolve(),
        tramitesStore.fetchList(buildFilters()),
      ])
    },
    { immediate: true },
  )

  // ── Helpers ───────────────────────────────────────────────────────────────────
  function buildFilters(): TramiteFilters {
    return {
      search: debouncedSearch.value || undefined,
      institucion_id:
        selectedInstitucion.value !== null
          ? Number(selectedInstitucion.value)
          : undefined,
      page: currentPage.value > 1 ? currentPage.value : undefined,
    }
  }

  async function syncUrlAndFetch(): Promise<void> {
    const query: Record<string, string> = {}
    if (debouncedSearch.value) query['search'] = debouncedSearch.value
    if (selectedInstitucion.value !== null) {
      query['institucionId'] = String(selectedInstitucion.value)
    }
    if (currentPage.value > 1) query['page'] = String(currentPage.value)

    await router.replace({ query })
    await tramitesStore.fetchList(buildFilters())

    if (tramitesStore.error) {
      toast.error('Error al cargar los trámites. Intenta de nuevo.')
    }
  }

  // ── Watchers de filtros ───────────────────────────────────────────────────────
  // Cuando el usuario cambia búsqueda o institución, resetea a página 1
  watch([debouncedSearch, selectedInstitucion], () => {
    currentPage.value = 1
    void syncUrlAndFetch()
  })

  // ── Limpiar filtros ───────────────────────────────────────────────────────────
  async function clearFilters(): Promise<void> {
    searchInput.value = ''
    selectedInstitucion.value = null
    currentPage.value = 1
    await router.replace({ query: {} })
    await tramitesStore.fetchList({})
  }

  const hasActiveFilters = computed(
    () => Boolean(debouncedSearch.value) || selectedInstitucion.value !== null,
  )

  // ── Paginación ────────────────────────────────────────────────────────────────
  const meta = computed(() => tramitesStore.tramites?.meta)

  const rangeText = computed(() => {
    const m = meta.value
    if (!m || m.total === 0) return 'Sin resultados'
    return `Mostrando ${m.from ?? 0}–${m.to ?? 0} de ${m.total} trámites`
  })

  const lastPage = computed(() => meta.value?.last_page ?? 1)

  const pageNumbers = computed((): (number | '...')[] => {
    const last = lastPage.value
    const cur = currentPage.value
    if (last <= 7) return Array.from({ length: last }, (_, i) => i + 1)

    const pages: (number | '...')[] = [1]
    if (cur > 3) pages.push('...')
    for (let p = Math.max(2, cur - 1); p <= Math.min(last - 1, cur + 1); p++) {
      pages.push(p)
    }
    if (cur < last - 2) pages.push('...')
    pages.push(last)
    return pages
  })

  async function goToPage(page: number): Promise<void> {
    if (page < 1 || page > lastPage.value || page === currentPage.value) return
    currentPage.value = page
    await syncUrlAndFetch()
  }

  // ── Tabla ─────────────────────────────────────────────────────────────────────
  const columns: TableColumn[] = [
    { key: 'codigo', label: 'Código' },
    { key: 'nombre', label: 'Nombre' },
    { key: 'institucion', label: 'Institución' },
    { key: 'dias_habiles', label: 'Días Hábiles', formatter: (v) => `${v} días` },
    { key: 'activo', label: 'Estado' },
  ]

  const tableRows = computed(
    (): Record<string, unknown>[] =>
      (tramitesStore.tramites?.data ?? []) as Record<string, unknown>[],
  )

  // Tipos guards para acceder a valores dentro del slot de BaseTable
  function getInstitucionNombre(value: unknown): string {
    if (typeof value === 'object' && value !== null && 'nombre' in value) {
      return String((value as { nombre: unknown }).nombre)
    }
    return '—'
  }

  function rowId(row: Record<string, unknown>): number {
    return Number(row['id'])
  }

  function rowNombre(row: Record<string, unknown>): string {
    return String(row['nombre'] ?? '')
  }

  function rowActivo(row: Record<string, unknown>): boolean {
    return row['activo'] === true
  }

  // ── Select de instituciones ───────────────────────────────────────────────────
  const institucionOptions = computed((): SelectOption[] =>
    institucionesStore.instituciones.map((i) => ({ value: i.id, label: i.nombre })),
  )

  // ── Confirmar desactivación ───────────────────────────────────────────────────
  const confirmOpen = ref(false)
  const tramitePendiente = ref<{ id: number; nombre: string } | null>(null)

  function openConfirm(row: Record<string, unknown>): void {
    tramitePendiente.value = { id: rowId(row), nombre: rowNombre(row) }
    confirmOpen.value = true
  }

  async function confirmarDesactivar(): Promise<void> {
    if (!tramitePendiente.value) return
    const { id, nombre } = tramitePendiente.value

    const success = await tramitesStore.deactivate(id)
    if (success) {
      toast.success(`Trámite "${nombre}" desactivado correctamente.`)
    } else if (tramitesStore.error) {
      toast.error(tramitesStore.error.message)
    }
    tramitePendiente.value = null
  }

  // ── Modal crear / editar ──────────────────────────────────────────────────────
  const formModalOpen  = ref(false)
  const tramiteEditing = ref<Tramite | null>(null)

  function openCreate(): void {
    tramiteEditing.value = null
    formModalOpen.value  = true
  }

  function openEdit(row: Record<string, unknown>): void {
    const tramites = tramitesStore.tramites?.data ?? []
    tramiteEditing.value = tramites.find((t) => t.id === rowId(row)) ?? null
    formModalOpen.value  = true
  }

  async function onTramiteSaved(): Promise<void> {
    await tramitesStore.fetchList(buildFilters())
  }

  // ── Exportar a Excel ──────────────────────────────────────────────────────────
  const exportLoading = ref(false)

  async function exportarExcel(): Promise<void> {
    if (exportLoading.value) return
    exportLoading.value = true

    try {
      const blob = await exportTramites(buildFilters())
      const url  = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      const date = new Date().toISOString().slice(0, 10) // YYYY-MM-DD

      link.href     = url
      link.download = `tramites_${date}.xlsx`
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      window.URL.revokeObjectURL(url)

      toast.success('Archivo Excel descargado correctamente.')
    } catch {
      toast.error('No se pudo generar el archivo Excel. Intenta de nuevo.')
    } finally {
      exportLoading.value = false
    }
  }
</script>

<template>
  <!-- Spinner de carga inicial — cubre la página antes de primer render -->
  <div
    v-if="pageLoading"
    class="flex min-h-screen items-center justify-center"
    aria-busy="true"
    aria-label="Cargando trámites…"
  >
    <svg
      class="h-10 w-10 animate-spin text-indigo-500"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      aria-hidden="true"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 12h-4z" />
    </svg>
  </div>

  <div v-else class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

    <!-- ── Header ──────────────────────────────────────────────────────────── -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Trámites Administrativos</h1>
        <p class="mt-1 text-sm text-slate-500">
          {{ rangeText }}
        </p>
      </div>

      <BaseButton
        variant="primary"
        size="md"
        @click="openCreate"
      >
        <template #icon-left>
          <span class="text-base font-bold leading-none" aria-hidden="true">+</span>
        </template>
        Nuevo Trámite
      </BaseButton>
    </div>

    <!-- ── Barra de filtros ─────────────────────────────────────────────────── -->
    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-end">

        <!-- Búsqueda por nombre -->
        <div class="relative flex-1">
          <MagnifyingGlassIcon
            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
            aria-hidden="true"
          />
          <input
            v-model="searchInput"
            type="search"
            placeholder="Buscar por nombre…"
            class="w-full rounded-md border border-slate-300 bg-white py-2 pl-9 pr-3 text-sm text-slate-900 placeholder:text-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-1"
            aria-label="Buscar trámites por nombre"
          />
        </div>

        <!-- Filtro por institución -->
        <div class="w-full sm:w-64">
          <BaseSelect
            v-model="selectedInstitucion"
            :options="institucionOptions"
            placeholder="Todas las instituciones"
            aria-label="Filtrar por institución"
          />
        </div>

        <!-- Acciones de filtro -->
        <div class="flex shrink-0 gap-2">
          <BaseButton
            v-if="hasActiveFilters"
            variant="secondary"
            size="md"
            title="Limpiar filtros activos"
            @click="clearFilters"
          >
            <template #icon-left>
              <XMarkIcon class="h-4 w-4" aria-hidden="true" />
            </template>
            Limpiar
          </BaseButton>

          <BaseButton
            variant="ghost"
            size="md"
            title="Exportar tabla a Excel"
            :disabled="exportLoading"
            :aria-busy="exportLoading"
            @click="exportarExcel"
          >
            <template #icon-left>
              <svg
                v-if="exportLoading"
                class="h-4 w-4 animate-spin"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                aria-hidden="true"
              >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 12h-4z" />
              </svg>
              <ArrowDownTrayIcon v-else class="h-4 w-4" aria-hidden="true" />
            </template>
            {{ exportLoading ? 'Exportando…' : 'Exportar' }}
          </BaseButton>
        </div>
      </div>
    </div>

    <!-- ── Tabla ────────────────────────────────────────────────────────────── -->
    <BaseTable
      :columns="columns"
      :rows="tableRows"
      :loading="tramitesStore.loading"
      empty-message="No se encontraron trámites con los filtros seleccionados."
    >
      <!-- Celda: Institución (objeto anidado) -->
      <template #cell-institucion="{ value }">
        <span class="text-slate-700">{{ getInstitucionNombre(value) }}</span>
      </template>

      <!-- Celda: Estado (badge) -->
      <template #cell-activo="{ value }">
        <span
          class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
          :class="
            value === true
              ? 'bg-green-100 text-green-700 ring-1 ring-green-200'
              : 'bg-slate-100 text-slate-500 ring-1 ring-slate-200'
          "
        >
          {{ value === true ? 'Activo' : 'Inactivo' }}
        </span>
      </template>

      <!-- Columna de acciones por fila -->
      <template #actions="{ row }">
        <div class="flex items-center justify-end gap-1">
          <!-- Ver detalle -->
          <button
            type="button"
            class="rounded-md p-1.5 text-slate-500 transition-colors hover:bg-slate-100 hover:text-indigo-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
            :title="`Ver trámite ${rowNombre(row)}`"
            @click="router.push(`/tramites/${rowId(row)}`)"
          >
            <EyeIcon class="h-4 w-4" aria-hidden="true" />
            <span class="sr-only">Ver {{ rowNombre(row) }}</span>
          </button>

          <!-- Editar -->
          <button
            type="button"
            class="rounded-md p-1.5 text-slate-500 transition-colors hover:bg-slate-100 hover:text-indigo-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
            :title="`Editar trámite ${rowNombre(row)}`"
            @click="openEdit(row)"
          >
            <PencilSquareIcon class="h-4 w-4" aria-hidden="true" />
            <span class="sr-only">Editar {{ rowNombre(row) }}</span>
          </button>

          <!-- Desactivar (solo si está activo) -->
          <button
            v-if="rowActivo(row)"
            type="button"
            class="rounded-md p-1.5 text-slate-500 transition-colors hover:bg-red-50 hover:text-red-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-400"
            :title="`Desactivar trámite ${rowNombre(row)}`"
            @click="openConfirm(row)"
          >
            <TrashIcon class="h-4 w-4" aria-hidden="true" />
            <span class="sr-only">Desactivar {{ rowNombre(row) }}</span>
          </button>
        </div>
      </template>
    </BaseTable>

    <!-- ── Paginación ───────────────────────────────────────────────────────── -->
    <div
      v-if="meta && meta.last_page > 1"
      class="flex flex-col items-center justify-between gap-4 sm:flex-row"
      aria-label="Paginación de trámites"
    >
      <!-- Rango de resultados -->
      <p class="text-sm text-slate-500">{{ rangeText }}</p>

      <!-- Controles de página -->
      <nav class="flex items-center gap-1" aria-label="Páginas">
        <!-- Anterior -->
        <button
          type="button"
          class="flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 text-slate-500 transition-colors hover:border-indigo-300 hover:text-indigo-600 disabled:cursor-not-allowed disabled:opacity-40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
          :disabled="currentPage <= 1"
          aria-label="Página anterior"
          @click="goToPage(currentPage - 1)"
        >
          <ChevronLeftIcon class="h-4 w-4" aria-hidden="true" />
        </button>

        <!-- Números de página -->
        <template v-for="(p, idx) in pageNumbers" :key="idx">
          <span
            v-if="p === '...'"
            class="flex h-8 w-8 items-center justify-center text-sm text-slate-400"
            aria-hidden="true"
          >
            …
          </span>
          <button
            v-else
            type="button"
            class="flex h-8 w-8 items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
            :class="
              p === currentPage
                ? 'border border-indigo-500 bg-indigo-50 text-indigo-700'
                : 'border border-slate-200 text-slate-600 hover:border-indigo-300 hover:text-indigo-600'
            "
            :aria-label="`Ir a página ${p}`"
            :aria-current="p === currentPage ? 'page' : undefined"
            @click="goToPage(p as number)"
          >
            {{ p }}
          </button>
        </template>

        <!-- Siguiente -->
        <button
          type="button"
          class="flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 text-slate-500 transition-colors hover:border-indigo-300 hover:text-indigo-600 disabled:cursor-not-allowed disabled:opacity-40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
          :disabled="currentPage >= lastPage"
          aria-label="Página siguiente"
          @click="goToPage(currentPage + 1)"
        >
          <ChevronRightIcon class="h-4 w-4" aria-hidden="true" />
        </button>
      </nav>
    </div>

    <!-- ── Modal crear / editar trámite ──────────────────────────────────────── -->
    <TramiteFormModal
      v-model:open="formModalOpen"
      :tramite="tramiteEditing"
      @saved="onTramiteSaved"
    />

    <!-- ── Modal de confirmación de desactivación ───────────────────────────── -->
    <ConfirmDialog
      v-model:open="confirmOpen"
      title="Desactivar trámite"
      :message="`¿Estás seguro de que deseas desactivar el trámite &quot;${tramitePendiente?.nombre}&quot;? Los ciudadanos no podrán verlo mientras esté inactivo.`"
      confirm-text="Sí, desactivar"
      cancel-text="Cancelar"
      variant="danger"
      @confirm="confirmarDesactivar"
      @cancel="tramitePendiente = null"
    />
  </div>
</template>
