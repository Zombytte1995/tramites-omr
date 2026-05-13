<script setup lang="ts">
  import { computed, ref, watch } from 'vue'
  import { RouterLink, useRoute, useRouter } from 'vue-router'
  import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
  import {
    ArrowRightOnRectangleIcon,
    Bars3Icon,
    BellIcon,
    BuildingOffice2Icon,
    ChevronDoubleLeftIcon,
    ChevronDoubleRightIcon,
    DocumentTextIcon,
    HomeIcon,
    PlusIcon,
    XMarkIcon,
  } from '@heroicons/vue/24/outline'
  import { useAuthStore } from '@/stores/auth'
  import { useToast } from '@/composables/useToast'
  import { useAsync } from '@/composables/useAsync'

  const route = useRoute()
  const router = useRouter()
  const authStore = useAuthStore()
  const toast = useToast()

  // ── Estado del sidebar ────────────────────────────────────────────────────────
  const STORAGE_KEY = 'omr-sidebar-expanded'

  /** Desktop: sidebar colapsable, estado persistido en localStorage */
  const sidebarExpanded = ref(
    localStorage.getItem(STORAGE_KEY) !== 'false',
  )
  watch(sidebarExpanded, (val) =>
    localStorage.setItem(STORAGE_KEY, String(val)),
  )

  /** Mobile: drawer superpuesto, no persistido */
  const mobileOpen = ref(false)

  // Cierra el drawer al cambiar de ruta
  watch(() => route.path, () => { mobileOpen.value = false })

  // ── Navegación ────────────────────────────────────────────────────────────────
  interface NavLink {
    to: string
    label: string
    icon: typeof HomeIcon
    bonus?: typeof PlusIcon
    match: (path: string) => boolean
  }

  const navLinks: NavLink[] = [
    {
      to: '/dashboard',
      label: 'Dashboard',
      icon: HomeIcon,
      match: (p) => p === '/dashboard',
    },
    {
      to: '/tramites',
      label: 'Trámites',
      icon: DocumentTextIcon,
      match: (p) => p.startsWith('/tramites'),
    },
    {
      to: '/instituciones/nueva',
      label: 'Nueva Institución',
      icon: BuildingOffice2Icon,
      bonus: PlusIcon,
      match: (p) => p.startsWith('/instituciones'),
    },
  ]

  // ── Usuario ───────────────────────────────────────────────────────────────────
  const user = computed(() => authStore.user)

  const initials = computed(() => {
    const name = user.value?.name ?? 'U'
    return name
      .split(' ')
      .map((w) => w[0])
      .join('')
      .toUpperCase()
      .slice(0, 2)
  })

  // ── Logout ────────────────────────────────────────────────────────────────────
  const { execute: doLogout, loading: logoutLoading } = useAsync(
    async () => {
      await authStore.logout()
      await router.push('/login')
    },
    { onError: () => toast.error('Error al cerrar sesión. Intenta de nuevo.') },
  )
</script>

<template>
  <div class="flex h-screen overflow-hidden bg-slate-50">

    <!-- ── Backdrop móvil ─────────────────────────────────────────────────────── -->
    <Transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="mobileOpen"
        class="fixed inset-0 z-20 bg-slate-900/50 backdrop-blur-sm sm:hidden"
        aria-hidden="true"
        @click="mobileOpen = false"
      />
    </Transition>

    <!-- ── Sidebar ────────────────────────────────────────────────────────────── -->
    <aside
      id="sidebar"
      :aria-expanded="sidebarExpanded"
      :aria-hidden="!mobileOpen && !sidebarExpanded ? 'true' : undefined"
      class="fixed inset-y-0 left-0 z-30 flex flex-col bg-indigo-900 shadow-xl transition-all duration-300 ease-in-out sm:sticky sm:top-0 sm:h-screen sm:translate-x-0"
      :class="[
        mobileOpen ? 'translate-x-0' : '-translate-x-full sm:translate-x-0',
        sidebarExpanded ? 'w-64' : 'sm:w-16 w-64',
      ]"
    >
      <!-- Logo + botón colapsar -->
      <div
        class="flex h-16 shrink-0 items-center justify-between border-b border-indigo-800 px-4"
      >
        <RouterLink
          to="/dashboard"
          class="flex items-center gap-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400 focus-visible:rounded"
          :aria-label="sidebarExpanded ? 'OMR — ir al dashboard' : 'Ir al dashboard'"
        >
          <span
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-600 text-sm font-extrabold text-white shadow"
          >
            OMR
          </span>
          <span
            v-if="sidebarExpanded"
            class="overflow-hidden whitespace-nowrap text-sm font-semibold text-white transition-all duration-200 sm:block"
          >
            Trámites OMR
          </span>
        </RouterLink>

        <!-- Botón colapsar (solo desktop) -->
        <button
          type="button"
          class="hidden shrink-0 rounded-md p-1 text-indigo-300 transition-colors hover:bg-indigo-800 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400 sm:flex"
          :aria-label="sidebarExpanded ? 'Colapsar sidebar' : 'Expandir sidebar'"
          @click="sidebarExpanded = !sidebarExpanded"
        >
          <ChevronDoubleLeftIcon
            v-if="sidebarExpanded"
            class="h-4 w-4"
            aria-hidden="true"
          />
          <ChevronDoubleRightIcon
            v-else
            class="h-4 w-4"
            aria-hidden="true"
          />
        </button>

        <!-- Botón cerrar (solo mobile) -->
        <button
          type="button"
          class="shrink-0 rounded-md p-1 text-indigo-300 transition-colors hover:bg-indigo-800 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400 sm:hidden"
          aria-label="Cerrar menú"
          @click="mobileOpen = false"
        >
          <XMarkIcon class="h-5 w-5" aria-hidden="true" />
        </button>
      </div>

      <!-- Navegación principal -->
      <nav
        class="flex flex-1 flex-col gap-1 overflow-y-auto px-2 py-4"
        aria-label="Navegación principal"
      >
        <RouterLink
          v-for="link in navLinks"
          :key="link.to"
          :to="link.to"
          class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400"
          :class="
            link.match(route.path)
              ? 'bg-indigo-700 text-white'
              : 'text-indigo-200 hover:bg-indigo-800 hover:text-white'
          "
          :aria-current="link.match(route.path) ? 'page' : undefined"
          :title="!sidebarExpanded ? link.label : undefined"
        >
          <!-- Icono con badge "+" opcional -->
          <span class="relative shrink-0">
            <component
              :is="link.icon"
              class="h-5 w-5"
              aria-hidden="true"
            />
            <component
              :is="link.bonus"
              v-if="link.bonus"
              class="absolute -bottom-1 -right-1 h-3 w-3 rounded-full bg-indigo-500 ring-2 ring-indigo-900 group-[.active]:bg-indigo-300"
              aria-hidden="true"
            />
          </span>

          <!-- Texto (oculto en sidebar colapsado en desktop) -->
          <span
            class="truncate transition-all duration-200"
            :class="sidebarExpanded ? 'opacity-100' : 'sm:hidden opacity-100'"
          >
            {{ link.label }}
          </span>
        </RouterLink>
      </nav>

      <!-- Sección de usuario al pie -->
      <div class="shrink-0 border-t border-indigo-800 p-2">
        <Menu as="div" class="relative">
          <MenuButton
            class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm transition-colors hover:bg-indigo-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400"
            :title="!sidebarExpanded ? (user?.name ?? 'Usuario') : undefined"
          >
            <!-- Avatar con iniciales -->
            <span
              class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white ring-2 ring-indigo-500"
              aria-hidden="true"
            >
              {{ initials }}
            </span>

            <!-- Nombre + email (ocultos en sidebar colapsado) -->
            <span
              class="min-w-0 flex-1 transition-all duration-200"
              :class="sidebarExpanded ? 'opacity-100' : 'sm:hidden opacity-100'"
            >
              <span class="block truncate font-medium text-white">
                {{ user?.name }}
              </span>
              <span class="block truncate text-xs text-indigo-300">
                {{ user?.email }}
              </span>
            </span>
          </MenuButton>

          <!-- Dropdown del usuario -->
          <MenuItems
            class="absolute bottom-full left-0 z-10 mb-1 w-56 overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-slate-200 focus:outline-none"
          >
            <!-- Info del usuario (no clickable) -->
            <div class="border-b border-slate-100 px-4 py-3">
              <p class="text-xs font-semibold text-slate-500">Sesión activa</p>
              <p class="mt-0.5 truncate text-sm font-medium text-slate-900">
                {{ user?.name }}
              </p>
              <p class="truncate text-xs text-slate-500">{{ user?.email }}</p>
            </div>

            <!-- Cerrar sesión -->
            <MenuItem v-slot="{ active }">
              <button
                type="button"
                :class="[
                  'flex w-full items-center gap-2 px-4 py-3 text-sm transition-colors',
                  active
                    ? 'bg-red-50 text-red-700'
                    : 'text-slate-700 hover:bg-slate-50',
                ]"
                :aria-busy="logoutLoading"
                @click="doLogout()"
              >
                <ArrowRightOnRectangleIcon
                  class="h-4 w-4 shrink-0"
                  aria-hidden="true"
                />
                {{ logoutLoading ? 'Cerrando sesión…' : 'Cerrar sesión' }}
              </button>
            </MenuItem>
          </MenuItems>
        </Menu>
      </div>
    </aside>

    <!-- ── Área principal ─────────────────────────────────────────────────────── -->
    <div class="flex min-w-0 flex-1 flex-col overflow-hidden">

      <!-- Topbar -->
      <header class="flex h-16 shrink-0 items-center gap-4 border-b border-slate-200 bg-white px-4 shadow-sm">

        <!-- Hamburger (solo mobile) -->
        <button
          type="button"
          class="rounded-md p-2 text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 sm:hidden"
          aria-controls="sidebar"
          :aria-expanded="mobileOpen"
          aria-label="Abrir menú de navegación"
          @click="mobileOpen = true"
        >
          <Bars3Icon class="h-5 w-5" aria-hidden="true" />
        </button>

        <!-- Título de la página -->
        <h1 class="truncate text-base font-semibold text-slate-800">
          {{ route.meta.title ?? 'Trámites OMR' }}
        </h1>

        <div class="flex-1" />

        <!-- Notificaciones (placeholder decorativo) -->
        <button
          type="button"
          class="relative rounded-md p-2 text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
          aria-label="Notificaciones (próximamente)"
          title="Notificaciones (próximamente)"
        >
          <BellIcon class="h-5 w-5" aria-hidden="true" />
          <!-- Dot decorativo -->
          <span
            class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-indigo-500 ring-2 ring-white"
            aria-hidden="true"
          />
        </button>
      </header>

      <!-- Contenido de la página -->
      <main class="flex-1 overflow-y-auto">
        <slot />
      </main>
    </div>
  </div>
</template>
