<script setup lang="ts">
  import { computed, ref } from 'vue'
  import { RouterLink, useRoute, useRouter } from 'vue-router'
  import {
    ArrowLeftIcon,
    ArrowPathIcon,
    ArrowUturnLeftIcon,
    BuildingOffice2Icon,
    CalendarDaysIcon,
    ClockIcon,
    ExclamationTriangleIcon,
    PencilSquareIcon,
    SparklesIcon,
    TrashIcon,
  } from '@heroicons/vue/24/outline'
  import { useTramitesStore } from '@/stores/tramites'
  import { useToast } from '@/composables/useToast'
  import { useAsync } from '@/composables/useAsync'
  import { NotFoundError } from '@/api/errors'
  import BaseButton from '@/components/ui/BaseButton.vue'
  import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'
  import Skeleton from '@/components/ui/Skeleton.vue'
  import type { TramiteResumen } from '@/types'

  // ── Stores & routing ─────────────────────────────────────────────────────────
  const route = useRoute()
  const router = useRouter()
  const tramitesStore = useTramitesStore()
  const toast = useToast()

  const tramiteId = computed(() => Number(route.params['id']))
  const tramite = computed(() => tramitesStore.currentTramite)

  // ── Carga inicial ─────────────────────────────────────────────────────────────
  const notFound = ref(false)

  const { loading: pageLoading } = useAsync(
    async () => {
      await tramitesStore.fetchOne(tramiteId.value)
      if (tramitesStore.error instanceof NotFoundError) {
        notFound.value = true
      }
    },
    { immediate: true },
  )

  // ── Desactivar ────────────────────────────────────────────────────────────────
  const confirmOpen = ref(false)

  async function confirmarDesactivar(): Promise<void> {
    const success = await tramitesStore.deactivate(tramiteId.value)
    if (success) {
      toast.success('Trámite desactivado correctamente.')
      await router.push('/tramites')
    } else if (tramitesStore.error) {
      toast.error(tramitesStore.error.message)
    }
  }

  // ── Resumen IA ────────────────────────────────────────────────────────────────
  const resumen = ref<TramiteResumen | null>(null)
  const resumenLoading = ref(false)
  const resumenError = ref<string | null>(null)

  async function generarResumen(): Promise<void> {
    resumenLoading.value = true
    resumenError.value = null

    const result = await tramitesStore.generarResumenIA(tramiteId.value)

    if (result) {
      resumen.value = result
    } else {
      resumenError.value =
        tramitesStore.error?.message ??
        'La generación de resúmenes no está disponible en este momento.'
    }

    resumenLoading.value = false
  }

  // ── Utilidades ────────────────────────────────────────────────────────────────
  function formatDate(dateStr: string): string {
    return new Intl.DateTimeFormat('es-SV', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    }).format(new Date(dateStr))
  }
</script>

<template>
  <!-- ── Spinner de carga inicial ────────────────────────────────────────────── -->
  <div
    v-if="pageLoading"
    class="flex min-h-screen items-center justify-center"
    aria-busy="true"
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

  <!-- ── Estado 404 ───────────────────────────────────────────────────────────── -->
  <div
    v-else-if="notFound"
    class="flex min-h-screen flex-col items-center justify-center gap-5 px-4 text-center"
  >
    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-100">
      <ArrowUturnLeftIcon class="h-10 w-10 text-slate-400" aria-hidden="true" />
    </div>
    <div>
      <h1 class="text-xl font-bold text-slate-800">Trámite no encontrado</h1>
      <p class="mt-1 text-sm text-slate-500">
        El trámite que buscas no existe o fue eliminado.
      </p>
    </div>
    <BaseButton variant="secondary" size="md" @click="router.push('/tramites')">
      <template #icon-left>
        <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
      </template>
      Volver a la lista
    </BaseButton>
  </div>

  <!-- ── Contenido principal ─────────────────────────────────────────────────── -->
  <div
    v-else-if="tramite"
    class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6 lg:px-8"
  >
    <!-- Breadcrumb + acciones de cabecera -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <!-- Breadcrumb -->
      <nav aria-label="Ruta de navegación">
        <ol class="flex items-center gap-2 text-sm">
          <li>
            <RouterLink
              to="/tramites"
              class="flex items-center gap-1 text-slate-500 transition-colors hover:text-indigo-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:rounded"
            >
              <ArrowLeftIcon class="h-3.5 w-3.5" aria-hidden="true" />
              Trámites
            </RouterLink>
          </li>
          <li class="text-slate-300" aria-hidden="true">›</li>
          <li
            class="max-w-xs truncate font-medium text-slate-900"
            aria-current="page"
          >
            {{ tramite.nombre }}
          </li>
        </ol>
      </nav>

      <!-- Botones de acción -->
      <div class="flex shrink-0 gap-2">
        <BaseButton
          variant="secondary"
          size="sm"
          @click="router.push(`/tramites/${tramiteId}/editar`)"
        >
          <template #icon-left>
            <PencilSquareIcon class="h-4 w-4" aria-hidden="true" />
          </template>
          Editar
        </BaseButton>

        <BaseButton
          v-if="tramite.activo"
          variant="danger"
          size="sm"
          @click="confirmOpen = true"
        >
          <template #icon-left>
            <TrashIcon class="h-4 w-4" aria-hidden="true" />
          </template>
          Desactivar
        </BaseButton>
      </div>
    </div>

    <!-- ── Card principal de información ─────────────────────────────────────── -->
    <article
      class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
      :aria-label="`Detalle del trámite ${tramite.nombre}`"
    >
      <!-- Cabecera: código + estado + nombre -->
      <header class="border-b border-slate-100 px-6 py-6">
        <div class="flex flex-wrap items-center gap-2">
          <!-- Badge de código -->
          <span
            class="rounded-md bg-slate-100 px-2.5 py-1 font-mono text-sm font-medium text-slate-600"
          >
            {{ tramite.codigo }}
          </span>

          <!-- Badge de estado -->
          <span
            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold ring-1"
            :class="
              tramite.activo
                ? 'bg-green-50 text-green-700 ring-green-200'
                : 'bg-slate-100 text-slate-500 ring-slate-200'
            "
          >
            <span
              class="h-1.5 w-1.5 rounded-full"
              :class="tramite.activo ? 'bg-green-500' : 'bg-slate-400'"
              aria-hidden="true"
            />
            {{ tramite.activo ? 'Activo' : 'Inactivo' }}
          </span>
        </div>

        <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900">
          {{ tramite.nombre }}
        </h1>
      </header>

      <!-- Grid: institución + días hábiles -->
      <div
        class="grid divide-y divide-slate-100 border-b border-slate-100 sm:grid-cols-2 sm:divide-x sm:divide-y-0"
      >
        <!-- Institución -->
        <section class="px-6 py-5">
          <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">
            Institución
          </p>
          <div class="mt-3 flex items-start gap-3">
            <div
              class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50"
            >
              <BuildingOffice2Icon
                class="h-5 w-5 text-indigo-600"
                aria-hidden="true"
              />
            </div>
            <div>
              <p class="font-semibold text-slate-900">
                {{ tramite.institucion?.nombre ?? '—' }}
              </p>
              <span
                v-if="tramite.institucion"
                class="mt-1 inline-block rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-700"
              >
                {{ tramite.institucion.tipo.etiqueta }}
              </span>
            </div>
          </div>
        </section>

        <!-- Días hábiles -->
        <section class="px-6 py-5">
          <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">
            Tiempo de resolución
          </p>
          <div class="mt-3 flex items-center gap-3">
            <div
              class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-50"
            >
              <CalendarDaysIcon
                class="h-5 w-5 text-amber-600"
                aria-hidden="true"
              />
            </div>
            <div class="flex items-baseline gap-1.5">
              <span class="text-3xl font-bold text-slate-900">
                {{ tramite.dias_habiles }}
              </span>
              <span class="text-sm text-slate-500">días hábiles</span>
            </div>
          </div>
        </section>
      </div>

      <!-- Descripción -->
      <section class="border-b border-slate-100 px-6 py-5">
        <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">
          Descripción
        </p>
        <p class="mt-3 text-sm leading-relaxed text-slate-700">
          {{ tramite.descripcion }}
        </p>
      </section>

      <!-- Fechas -->
      <footer class="flex flex-wrap gap-x-6 gap-y-1 px-6 py-4 text-xs text-slate-400">
        <span class="flex items-center gap-1.5">
          <ClockIcon class="h-3.5 w-3.5" aria-hidden="true" />
          Creado el {{ formatDate(tramite.created_at) }}
        </span>
        <span class="flex items-center gap-1.5">
          <ClockIcon class="h-3.5 w-3.5" aria-hidden="true" />
          Actualizado el {{ formatDate(tramite.updated_at) }}
        </span>
      </footer>
    </article>

    <!-- ── Card de Resumen IA ──────────────────────────────────────────────────── -->
    <section
      aria-label="Resumen ejecutivo generado por inteligencia artificial"
      class="overflow-hidden rounded-2xl border border-indigo-200 bg-gradient-to-br from-indigo-50 via-white to-violet-50 shadow-sm shadow-indigo-100"
    >
      <!-- Header de la card IA -->
      <header class="flex items-center gap-3 border-b border-indigo-100/80 px-6 py-4">
        <div
          class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-600 shadow-sm shadow-indigo-300"
        >
          <SparklesIcon class="h-4 w-4 text-white" aria-hidden="true" />
        </div>
        <div class="flex-1">
          <h2 class="font-semibold text-slate-900">
            Resumen Ejecutivo con IA
          </h2>
          <p class="text-xs text-indigo-500/80">Powered by Google Gemini</p>
        </div>

        <!-- Botón Regenerar (inline en header, visible solo con resumen) -->
        <BaseButton
          v-if="resumen && !resumenLoading"
          variant="ghost"
          size="sm"
          :loading="resumenLoading"
          @click="generarResumen"
        >
          <template #icon-left>
            <ArrowPathIcon class="h-3.5 w-3.5" aria-hidden="true" />
          </template>
          Regenerar
        </BaseButton>
      </header>

      <!-- Body con los 4 estados ───────────────────────────────────────────── -->
      <div class="px-6 py-6">

        <!-- Estado: cargando ──────────────────────────────────────────────── -->
        <div
          v-if="resumenLoading"
          class="space-y-4"
          aria-busy="true"
          aria-label="Generando resumen…"
        >
          <div class="flex items-center gap-2">
            <svg
              class="h-4 w-4 animate-spin text-indigo-500"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              aria-hidden="true"
            >
              <circle
                class="opacity-25"
                cx="12" cy="12" r="10"
                stroke="currentColor"
                stroke-width="4"
              />
              <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 22 12h-4z"
              />
            </svg>
            <span class="text-sm font-medium text-indigo-700">
              Generando resumen con IA…
            </span>
          </div>
          <Skeleton variant="text" :lines="3" />
        </div>

        <!-- Estado: error ─────────────────────────────────────────────────── -->
        <div
          v-else-if="resumenError"
          class="flex flex-col items-center gap-4 py-6 text-center"
        >
          <div
            class="flex h-14 w-14 items-center justify-center rounded-full bg-amber-100"
          >
            <ExclamationTriangleIcon
              class="h-7 w-7 text-amber-500"
              aria-hidden="true"
            />
          </div>
          <div>
            <p class="font-semibold text-slate-700">Servicio no disponible</p>
            <p class="mt-1 max-w-sm text-sm text-slate-500">
              {{ resumenError }}
            </p>
          </div>
          <BaseButton variant="secondary" size="sm" @click="generarResumen">
            Reintentar
          </BaseButton>
        </div>

        <!-- Estado: resumen generado ─────────────────────────────────────── -->
        <div v-else-if="resumen" class="space-y-4">
          <!-- Texto del resumen -->
          <blockquote
            class="relative rounded-xl border border-indigo-200 bg-white px-5 py-4"
          >
            <!-- Barra izquierda decorativa -->
            <span
              class="absolute inset-y-4 left-0 w-1 rounded-full bg-indigo-500"
              aria-hidden="true"
            />
            <p class="pl-4 text-sm leading-relaxed text-slate-700">
              {{ resumen.resumen }}
            </p>
          </blockquote>

          <!-- Disclaimer -->
          <p class="flex items-center gap-1.5 text-xs text-slate-400">
            <ExclamationTriangleIcon
              class="h-3.5 w-3.5 shrink-0 text-amber-400"
              aria-hidden="true"
            />
            Generado por IA. Puede contener imprecisiones. Verifica la información
            con las fuentes oficiales.
          </p>
        </div>

        <!-- Estado: vacío (sin resumen aún) ─────────────────────────────── -->
        <div
          v-else
          class="flex flex-col items-center gap-5 py-8 text-center"
        >
          <!-- Icono animado -->
          <div class="relative">
            <div
              class="flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-100"
            >
              <SparklesIcon
                class="h-8 w-8 text-indigo-600"
                aria-hidden="true"
              />
            </div>
            <!-- Pulso decorativo -->
            <span
              class="absolute inset-0 animate-ping rounded-2xl bg-indigo-200 opacity-30"
              aria-hidden="true"
            />
          </div>

          <div class="max-w-xs">
            <p class="font-semibold text-slate-800">
              Genera un resumen ejecutivo con IA
            </p>
            <p class="mt-1.5 text-sm text-slate-500">
              Obtén una síntesis del propósito y beneficio de este trámite
              para los ciudadanos en segundos.
            </p>
          </div>

          <BaseButton
            variant="primary"
            size="md"
            :loading="resumenLoading"
            @click="generarResumen"
          >
            <template #icon-left>
              <SparklesIcon class="h-4 w-4" aria-hidden="true" />
            </template>
            Generar Resumen con IA
          </BaseButton>

          <p class="text-xs text-indigo-400/80">
            Resultado cacheado 24 h · Sin costo adicional
          </p>
        </div>
      </div>
    </section>
  </div>

  <!-- ── Modal de confirmación de desactivación ─────────────────────────────── -->
  <ConfirmDialog
    v-model:open="confirmOpen"
    title="Desactivar trámite"
    :message="`¿Deseas desactivar &quot;${tramite?.nombre}&quot;? No aparecerá en el listado público mientras esté inactivo.`"
    confirm-text="Sí, desactivar"
    cancel-text="Cancelar"
    variant="danger"
    @confirm="confirmarDesactivar"
  />
</template>
