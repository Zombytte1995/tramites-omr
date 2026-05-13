<script setup lang="ts">
  import { computed } from 'vue'
  import { useRouter } from 'vue-router'
  import {
    BuildingOffice2Icon,
    DocumentTextIcon,
    CheckCircleIcon,
    XCircleIcon,
    ClockIcon,
    ArrowRightIcon,
  } from '@heroicons/vue/24/outline'
  import { useAsync } from '@/composables/useAsync'
  import { useAuthStore } from '@/stores/auth'
  import { getDashboardStats } from '@/api/dashboard'
  import Skeleton from '@/components/ui/Skeleton.vue'
  import type { DashboardInstitucionStat } from '@/types/dashboard'

  const router = useRouter()
  const authStore = useAuthStore()

  // ── Carga de datos ────────────────────────────────────────────────────────────
  const { data: stats, loading, error } = useAsync(getDashboardStats, { immediate: true })

  // ── Saludo personalizado ──────────────────────────────────────────────────────
  const greeting = computed(() => {
    const hour = new Date().getHours()
    if (hour < 12) return 'Buenos días'
    if (hour < 18) return 'Buenas tardes'
    return 'Buenas noches'
  })

  const firstName = computed(() => {
    const name = authStore.user?.name ?? ''
    return name.split(' ')[0]
  })

  const today = computed(() =>
    new Date().toLocaleDateString('es-SV', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    }),
  )

  // ── Barras del gráfico CSS ────────────────────────────────────────────────────
  const maxTramites = computed(() => {
    const list = stats.value?.tramites_por_institucion ?? []
    return Math.max(1, ...list.map((i) => i.total))
  })

  function barWidth(stat: DashboardInstitucionStat): string {
    return `${Math.round((stat.total / maxTramites.value) * 100)}%`
  }

  // ── KPI cards ─────────────────────────────────────────────────────────────────
  const kpiCards = computed(() => [
    {
      label: 'Total Trámites',
      value: stats.value?.total_tramites ?? 0,
      icon: DocumentTextIcon,
      color: 'text-indigo-600',
      bg: 'bg-indigo-50',
      ring: 'ring-indigo-100',
    },
    {
      label: 'Trámites Activos',
      value: stats.value?.tramites_activos ?? 0,
      icon: CheckCircleIcon,
      color: 'text-emerald-600',
      bg: 'bg-emerald-50',
      ring: 'ring-emerald-100',
    },
    {
      label: 'Trámites Inactivos',
      value: stats.value?.tramites_inactivos ?? 0,
      icon: XCircleIcon,
      color: 'text-slate-500',
      bg: 'bg-slate-50',
      ring: 'ring-slate-100',
    },
    {
      label: 'Instituciones Activas',
      value: stats.value?.total_instituciones ?? 0,
      icon: BuildingOffice2Icon,
      color: 'text-violet-600',
      bg: 'bg-violet-50',
      ring: 'ring-violet-100',
    },
  ])
</script>

<template>
  <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">

    <!-- ── Header de bienvenida ──────────────────────────────────────────────── -->
    <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="text-sm font-medium text-indigo-600">
          {{ today }}
        </p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900">
          {{ greeting }}, {{ firstName }}
        </h1>
        <p class="mt-1 text-sm text-slate-500">
          Aquí tienes un resumen del sistema de trámites.
        </p>
      </div>

      <button
        type="button"
        class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 sm:mt-0"
        @click="router.push('/tramites/nuevo')"
      >
        <span class="text-base font-bold leading-none" aria-hidden="true">+</span>
        Nuevo trámite
      </button>
    </div>

    <!-- ── Error de carga ────────────────────────────────────────────────────── -->
    <div
      v-if="error"
      class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"
      role="alert"
    >
      No se pudieron cargar las estadísticas. Verifica tu conexión e intenta de nuevo.
    </div>

    <!-- ── KPI Cards ─────────────────────────────────────────────────────────── -->
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
      <template v-if="loading">
        <div
          v-for="i in 4"
          :key="i"
          class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
        >
          <Skeleton class="mb-3 h-10 w-10 rounded-lg" />
          <Skeleton class="mb-2 h-7 w-16" />
          <Skeleton class="h-4 w-24" />
        </div>
      </template>

      <template v-else>
        <div
          v-for="card in kpiCards"
          :key="card.label"
          class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md"
        >
          <div
            class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-lg ring-1"
            :class="[card.bg, card.ring]"
          >
            <component
              :is="card.icon"
              class="h-5 w-5"
              :class="card.color"
              aria-hidden="true"
            />
          </div>
          <p class="text-2xl font-bold tabular-nums text-slate-900">
            {{ card.value.toLocaleString('es-SV') }}
          </p>
          <p class="mt-0.5 text-sm text-slate-500">{{ card.label }}</p>
        </div>
      </template>
    </div>

    <!-- ── Cuerpo: gráfico + recientes ──────────────────────────────────────── -->
    <div class="grid gap-6 lg:grid-cols-5">

      <!-- Trámites por Institución (barras CSS) -->
      <section
        class="col-span-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-3"
        aria-label="Trámites por institución"
      >
        <h2 class="mb-5 text-base font-semibold text-slate-900">
          Trámites por Institución
        </h2>

        <!-- Skeleton -->
        <div v-if="loading" class="space-y-4">
          <div v-for="i in 5" :key="i" class="space-y-1.5">
            <Skeleton class="h-4 w-40" />
            <Skeleton class="h-5 w-full rounded-full" />
          </div>
        </div>

        <!-- Sin datos -->
        <p
          v-else-if="!stats?.tramites_por_institucion.length"
          class="text-sm text-slate-400"
        >
          Sin datos de instituciones todavía.
        </p>

        <!-- Barras -->
        <div
          v-else
          class="space-y-4"
        >
          <div
            v-for="stat in stats.tramites_por_institucion"
            :key="stat.institucion"
          >
            <!-- Label + contadores -->
            <div class="mb-1.5 flex items-baseline justify-between gap-2">
              <span class="truncate text-sm font-medium text-slate-700">
                {{ stat.institucion }}
              </span>
              <span class="shrink-0 text-xs tabular-nums text-slate-400">
                {{ stat.activos }}/{{ stat.total }}
              </span>
            </div>

            <!-- Barra compuesta: activos (indigo) sobre total (slate) -->
            <div
              class="relative h-4 w-full overflow-hidden rounded-full bg-slate-100"
              :title="`${stat.activos} activos de ${stat.total} total`"
            >
              <!-- Total -->
              <div
                class="absolute inset-y-0 left-0 rounded-full bg-slate-300 transition-all duration-500"
                :style="{ width: barWidth(stat) }"
              />
              <!-- Activos (encima) -->
              <div
                class="absolute inset-y-0 left-0 rounded-full bg-indigo-500 transition-all duration-500"
                :style="{ width: `calc(${barWidth(stat)} * ${stat.total > 0 ? stat.activos / stat.total : 0})` }"
              />
            </div>
          </div>

          <!-- Leyenda -->
          <div class="mt-2 flex items-center gap-4 text-xs text-slate-500">
            <span class="flex items-center gap-1.5">
              <span class="h-2.5 w-2.5 rounded-full bg-indigo-500" />
              Activos
            </span>
            <span class="flex items-center gap-1.5">
              <span class="h-2.5 w-2.5 rounded-full bg-slate-300" />
              Inactivos
            </span>
          </div>
        </div>
      </section>

      <!-- Trámites Recientes -->
      <section
        class="col-span-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2"
        aria-label="Trámites recientes"
      >
        <div class="mb-5 flex items-center justify-between">
          <h2 class="text-base font-semibold text-slate-900">
            Trámites Recientes
          </h2>
          <button
            type="button"
            class="flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-800 focus-visible:outline-none"
            @click="router.push('/tramites')"
          >
            Ver todos
            <ArrowRightIcon class="h-3 w-3" />
          </button>
        </div>

        <!-- Skeleton -->
        <div v-if="loading" class="divide-y divide-slate-100">
          <div v-for="i in 5" :key="i" class="flex flex-col gap-1.5 py-3">
            <Skeleton class="h-4 w-32" />
            <Skeleton class="h-3.5 w-48" />
          </div>
        </div>

        <!-- Sin datos -->
        <p
          v-else-if="!stats?.tramites_recientes.length"
          class="text-sm text-slate-400"
        >
          Aún no hay trámites registrados.
        </p>

        <!-- Lista -->
        <ul
          v-else
          class="divide-y divide-slate-100"
          role="list"
        >
          <li
            v-for="t in stats.tramites_recientes"
            :key="t.id"
            class="group py-3"
          >
            <button
              type="button"
              class="flex w-full items-start justify-between gap-2 text-left focus-visible:outline-none"
              @click="router.push(`/tramites/${t.id}`)"
            >
              <div class="min-w-0">
                <p class="truncate text-sm font-medium text-slate-800 group-hover:text-indigo-600">
                  {{ t.nombre }}
                </p>
                <p class="mt-0.5 flex items-center gap-1.5 text-xs text-slate-400">
                  <span class="font-mono">{{ t.codigo }}</span>
                  <span aria-hidden="true">·</span>
                  <ClockIcon class="h-3 w-3 shrink-0" aria-hidden="true" />
                  {{ t.dias_habiles }}d
                </p>
              </div>

              <span
                class="mt-0.5 shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold ring-1"
                :class="
                  t.activo
                    ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                    : 'bg-slate-100 text-slate-500 ring-slate-200'
                "
              >
                {{ t.activo ? 'Activo' : 'Inactivo' }}
              </span>
            </button>
          </li>
        </ul>
      </section>
    </div>
  </div>
</template>
