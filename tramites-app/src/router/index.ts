/**
 * Configuración de Vue Router 4.
 *
 * Convenciones:
 * - LoginView se importa de forma eager (no lazy) porque es la primera
 *   vista que ve un usuario no autenticado — no queremos que espere
 *   una carga dinámica justo cuando más importa la velocidad percibida.
 * - El resto de vistas se cargan de forma lazy con import() dinámico.
 * - Las rutas protegidas llevan meta.requiresAuth: true.
 * - meta.title alimenta document.title vía afterEach.
 */

import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import LoginView from '@/views/LoginView.vue'

// ── Augmentación de tipos de RouteMeta ────────────────────────────────────────
// Permite acceder a meta.requiresAuth y meta.title con tipos seguros.
declare module 'vue-router' {
  interface RouteMeta {
    /** Si true, el guard global redirige a /login cuando no hay sesión. */
    requiresAuth?: boolean
    /** Texto que aparece en la pestaña del navegador (<title>). */
    title?: string
    /** Layout a usar. 'auth' = sin sidebar, 'main' (default) = con sidebar. */
    layout?: 'main' | 'auth'
  }
}

// ── Definición de rutas ───────────────────────────────────────────────────────

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),

  routes: [
    // ── Raíz ──────────────────────────────────────────────────────────────────
    {
      path: '/',
      redirect: () => {
        const { isAuthenticated } = useAuthStore()
        return isAuthenticated ? { name: 'dashboard' } : { name: 'login' }
      },
    },

    // ── Autenticación ─────────────────────────────────────────────────────────
    {
      path: '/login',
      name: 'login',
      component: LoginView, // eager: primera vista del usuario no autenticado
      meta: { title: 'Iniciar sesión', layout: 'auth' as const },
    },

    // ── Dashboard ─────────────────────────────────────────────────────────────
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('@/views/DashboardView.vue'),
      meta: { requiresAuth: true, title: 'Dashboard' },
    },

    // ── Trámites ──────────────────────────────────────────────────────────────
    {
      path: '/tramites',
      name: 'tramites-list',
      component: () => import('@/views/TramitesListView.vue'),
      meta: { requiresAuth: true, title: 'Trámites' },
    },
    {
      // Debe ir antes de /:id para que /nuevo no se capture como un ID
      path: '/tramites/nuevo',
      name: 'tramite-crear',
      component: () => import('@/views/TramiteFormView.vue'),
      meta: { requiresAuth: true, title: 'Nuevo trámite' },
    },
    {
      path: '/tramites/:id',
      name: 'tramite-detail',
      component: () => import('@/views/TramiteDetailView.vue'),
      meta: { requiresAuth: true, title: 'Detalle del trámite' },
    },
    {
      path: '/tramites/:id/editar',
      name: 'tramite-editar',
      component: () => import('@/views/TramiteFormView.vue'),
      meta: { requiresAuth: true, title: 'Editar trámite' },
    },

    // ── Instituciones ─────────────────────────────────────────────────────────
    {
      path: '/instituciones',
      name: 'instituciones-list',
      component: () => import('@/views/InstitucionesListView.vue'),
      meta: { requiresAuth: true, title: 'Instituciones' },
    },
    {
      // Se mantiene para el flujo de retorno desde TramiteFormView
      path: '/instituciones/nueva',
      name: 'institucion-crear',
      component: () => import('@/views/InstitucionFormView.vue'),
      meta: { requiresAuth: true, title: 'Nueva institución' },
    },

    // ── 404 ───────────────────────────────────────────────────────────────────
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('@/views/NotFoundView.vue'),
      meta: { title: 'Página no encontrada' },
    },
  ],
})

// ── Guards ────────────────────────────────────────────────────────────────────

/**
 * Guard global de autenticación.
 *
 * Casos que maneja:
 * 1. Ruta protegida sin sesión     → /login?redirect=<ruta-original>
 * 2. Ruta /login con sesión activa → /dashboard
 * 3. Cualquier otro caso           → continúa normalmente (return undefined)
 *
 * Se usa (to) sin declarar `from` porque no se necesita y TypeScript
 * prohíbe parámetros declarados pero no usados (noUnusedParameters: true).
 */
router.beforeEach((to) => {
  const { isAuthenticated } = useAuthStore()

  if (to.meta.requiresAuth && !isAuthenticated) {
    // Guarda la ruta original para redirigir después del login
    return {
      name: 'login',
      query: { redirect: to.fullPath },
    }
  }

  if (to.name === 'login' && isAuthenticated) {
    return { name: 'dashboard' }
  }
})

/**
 * Actualiza document.title con el título de la ruta activa.
 * Se hace en afterEach (no beforeEach) para actualizar solo cuando
 * la navegación se confirma — si un beforeEach redirige, el título
 * se actualiza con el destino final, no con la ruta cancelada.
 */
router.afterEach((to) => {
  const base = 'Trámites OMR'
  document.title = to.meta.title ? `${to.meta.title} — ${base}` : base
})

export default router
