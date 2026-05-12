/**
 * Store de autenticación.
 *
 * Responsabilidad: mantener el estado persistente de sesión
 * (token + perfil de usuario). El estado transiente de carga y
 * error de los formularios lo gestionan los componentes con useAsync.
 *
 * Flujo típico:
 *   main.ts  → initFromStorage()  (restaura sesión al arrancar)
 *   LoginView → login(credentials) (inicia sesión)
 *   NavBar   → logout()           (cierra sesión)
 */

import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import * as apiAuth from '@/api/auth'
import { TOKEN_KEY } from '@/api/client'
import type { LoginCredentials, User } from '@/types'

export const useAuthStore = defineStore('auth', () => {
  // ── State ───────────────────────────────────────────────────────────────────
  const user = ref<User | null>(null)
  const token = ref<string | null>(null)

  // ── Getters ─────────────────────────────────────────────────────────────────

  /**
   * true cuando hay token válido Y perfil de usuario cargado.
   * Ambas condiciones son necesarias: un token sin usuario puede indicar
   * una sesión en estado inconsistente.
   */
  const isAuthenticated = computed(
    () => token.value !== null && user.value !== null,
  )

  // ── Actions ─────────────────────────────────────────────────────────────────

  /**
   * Obtiene el perfil del usuario autenticado desde GET /auth/me.
   * Requiere que el token ya esté en estado/localStorage para que
   * el interceptor de Axios lo adjunte al header Authorization.
   *
   * @throws {UnauthorizedError} Si el token expiró o es inválido.
   */
  async function fetchMe(): Promise<void> {
    user.value = await apiAuth.me()
  }

  /**
   * Autentica al usuario, guarda el token y carga el perfil.
   *
   * Si la carga del perfil falla (red caída, token rechazado), revierte
   * el token para dejar el estado limpio y relanza el error al componente.
   *
   * @param credentials - Email y contraseña del formulario de login.
   * @throws {UnauthorizedError}  Credenciales incorrectas.
   * @throws {ValidationError}    Datos de formulario inválidos.
   * @throws {Error}              No se pudo cargar el perfil.
   */
  async function login(credentials: LoginCredentials): Promise<void> {
    const response = await apiAuth.login(credentials)

    token.value = response.token
    localStorage.setItem(TOKEN_KEY, response.token)

    try {
      await fetchMe()
    } catch {
      // Si el perfil falla, revertimos para no dejar estado inconsistente.
      token.value = null
      localStorage.removeItem(TOKEN_KEY)
      throw new Error('Sesión iniciada pero no se pudo cargar el perfil. Intente de nuevo.')
    }
  }

  /**
   * Cierra la sesión: invalida el token en el servidor y limpia el estado.
   *
   * Usa finally para garantizar la limpieza local aunque el servidor falle
   * (token ya expirado, sin conexión, etc.).
   */
  async function logout(): Promise<void> {
    try {
      await apiAuth.logout() // invalida token + elimina de localStorage
    } catch {
      // Si la petición falla, eliminamos el token manualmente
      localStorage.removeItem(TOKEN_KEY)
    } finally {
      user.value = null
      token.value = null
    }
  }

  /**
   * Restaura la sesión desde localStorage al arrancar la aplicación.
   *
   * Si hay un token guardado, intenta validarlo llamando a /auth/me.
   * Si la validación falla (token expirado o inválido), limpia el estado
   * silenciosamente para que el usuario arranque como no autenticado.
   *
   * Debe llamarse en main.ts antes de montar la aplicación:
   * ```ts
   * await authStore.initFromStorage()
   * app.mount('#app')
   * ```
   */
  async function initFromStorage(): Promise<void> {
    const savedToken = localStorage.getItem(TOKEN_KEY)
    if (!savedToken) return

    token.value = savedToken

    try {
      await fetchMe()
    } catch {
      // Token inválido o expirado — limpiamos sin redirigir
      // (el interceptor de Axios ya redirigirá si hace falta en navegación)
      token.value = null
      localStorage.removeItem(TOKEN_KEY)
    }
  }

  return {
    // state
    user,
    token,
    // getters
    isAuthenticated,
    // actions
    fetchMe,
    login,
    logout,
    initFromStorage,
  }
})
