/**
 * useAsync — gestión reactiva de estado para operaciones asíncronas.
 *
 * Envuelve cualquier función async y expone {data, loading, error}
 * como Refs reactivos. La función execute() nunca lanza: captura
 * el error y lo almacena en error.value, devolviendo null.
 *
 * @example Uso básico en <script setup>
 * ```ts
 * const { data, loading, error, execute } = useAsync(getTramite)
 *
 * onMounted(() => execute(route.params.id))
 * ```
 *
 * @example Con callbacks y ejecución inmediata
 * ```ts
 * const { data: tramites, loading } = useAsync(listTramites, {
 *   immediate: true,
 *   onSuccess: (list) => console.log(`${list.data.length} trámites`),
 *   onError:   (err)  => toast.error(err.message),
 * })
 * ```
 *
 * @example Reejecutar con nuevos argumentos (ej. filtros de búsqueda)
 * ```ts
 * const { data, execute } = useAsync(listTramites)
 * const applyFilters = (f: TramiteFilters) => execute(f)
 * ```
 */

// useAsync necesita `any[]` en su contrato para ser un wrapper genérico
// compatible con funciones de aridad variable. Es el único lugar del
// proyecto donde `any` explícito está justificado por diseño.
/* eslint-disable @typescript-eslint/no-explicit-any */

import { onMounted, ref } from 'vue'
import type { Ref } from 'vue'

// ── Tipos públicos ────────────────────────────────────────────────────────────

export interface UseAsyncReturn<T> {
  /** Resultado de la última ejecución exitosa. null antes de la primera. */
  data: Ref<T | null>
  /** true mientras la función asíncrona está en vuelo. */
  loading: Ref<boolean>
  /** Error de la última ejecución fallida. null si no hubo error. */
  error: Ref<Error | null>
  /**
   * Dispara la función asíncrona. Limpia el error previo antes de ejecutar.
   * @returns El resultado o null si ocurrió un error.
   */
  execute: (...args: any[]) => Promise<T | null>
  /** Restablece data, loading y error a sus valores iniciales. */
  reset: () => void
}

interface UseAsyncOptions<T> {
  /**
   * Si true, llama a execute() en onMounted.
   * Requiere que useAsync se invoque dentro del contexto de setup().
   */
  immediate?: boolean
  /** Callback invocado con el resultado cuando la ejecución tiene éxito. */
  onSuccess?: (data: T) => void
  /** Callback invocado con el Error cuando la ejecución falla. */
  onError?: (error: Error) => void
}

// ── Implementación ────────────────────────────────────────────────────────────

export function useAsync<T>(
  asyncFn: (...args: any[]) => Promise<T>,
  options: UseAsyncOptions<T> = {},
): UseAsyncReturn<T> {
  // `as Ref<T | null>` es necesario porque el tipo interno de Vue es
  // Ref<UnwrapRef<T | null>>, que difiere de Ref<T | null> cuando T
  // contiene Refs anidados. En nuestro caso T siempre es un DTO plano.
  const data = ref<T | null>(null) as Ref<T | null>
  const loading = ref(false)
  const error = ref<Error | null>(null)

  const execute = async (...args: any[]): Promise<T | null> => {
    loading.value = true
    error.value = null // limpia error de ejecución anterior

    try {
      const result = await asyncFn(...args)
      data.value = result
      options.onSuccess?.(result)
      return result
    } catch (caught) {
      const err = caught instanceof Error ? caught : new Error(String(caught))
      error.value = err
      options.onError?.(err)
      return null
    } finally {
      loading.value = false
    }
  }

  const reset = (): void => {
    data.value = null
    loading.value = false
    error.value = null
  }

  if (options.immediate) {
    onMounted(() => {
      void execute()
    })
  }

  return { data, loading, error, execute, reset }
}

/* eslint-enable @typescript-eslint/no-explicit-any */
