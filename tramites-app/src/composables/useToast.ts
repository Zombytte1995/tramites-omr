/**
 * useToast — sistema global de notificaciones tipo toast.
 *
 * El array de toasts es un singleton a nivel de módulo: todos los
 * componentes que llamen useToast() comparten la misma lista reactiva.
 * Los toasts se auto-descartan después de DISMISS_MS milisegundos.
 *
 * Para renderizarlos, monta un <ToastContainer> en App.vue que observe
 * el array `toasts` retornado por este composable.
 *
 * @example Mostrar notificaciones desde un componente
 * ```ts
 * const toast = useToast()
 *
 * // Éxito
 * toast.success('Trámite creado correctamente.')
 *
 * // Error de API
 * try {
 *   await createTramite(form)
 * } catch (e) {
 *   toast.error(e instanceof Error ? e.message : 'Error desconocido.')
 * }
 *
 * // Información
 * toast.info('Los cambios se aplicarán en el próximo ciclo.')
 *
 * // Advertencia
 * toast.warning('Esta acción no se puede deshacer.')
 * ```
 *
 * @example Descartar manualmente (ej. botón de cerrar en el toast)
 * ```ts
 * const { toasts, dismiss } = useToast()
 * // En el template: @click="dismiss(toast.id)"
 * ```
 */

import { ref } from 'vue'
import type { Ref } from 'vue'

// ── Tipos públicos ────────────────────────────────────────────────────────────

export type ToastType = 'success' | 'error' | 'info' | 'warning'

export interface Toast {
  id: number
  type: ToastType
  message: string
}

// ── Estado singleton (compartido por todos los componentes) ───────────────────

/** Toasts activos visibles en pantalla. */
const toasts: Ref<Toast[]> = ref([])

/** Contador monótono que garantiza IDs únicos aunque se creen rápidamente. */
let counter = 0

/** Milisegundos hasta el auto-descarte de cada toast. */
const DISMISS_MS = 3_000

// ── Composable ────────────────────────────────────────────────────────────────

export function useToast() {
  /**
   * Agrega un toast y programa su auto-descarte.
   * @returns ID del toast creado (útil para descarte manual anticipado).
   */
  function add(type: ToastType, message: string): number {
    const id = ++counter
    toasts.value.push({ id, type, message })
    setTimeout(() => dismiss(id), DISMISS_MS)
    return id
  }

  /**
   * Elimina el toast con el ID dado antes de que expire el timer.
   * Llamar con un ID ya descartado es un no-op seguro.
   */
  function dismiss(id: number): void {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  }

  return {
    /** Lista reactiva de toasts activos. Pásala a <ToastContainer>. */
    toasts,

    /** Notificación de operación exitosa (fondo verde). */
    success: (message: string): number => add('success', message),

    /** Notificación de error (fondo rojo). */
    error: (message: string): number => add('error', message),

    /** Notificación informativa (fondo azul). */
    info: (message: string): number => add('info', message),

    /** Advertencia que no bloquea el flujo (fondo amarillo). */
    warning: (message: string): number => add('warning', message),

    /** Descarta manualmente un toast por ID. */
    dismiss,
  }
}
