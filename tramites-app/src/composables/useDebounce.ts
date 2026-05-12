/**
 * useDebounce — retrasa la propagación de un valor reactivo.
 *
 * Devuelve un Ref que solo se actualiza cuando la fuente lleva
 * `delay` milisegundos sin cambiar. Ideal para filtros de búsqueda
 * donde no se quiere disparar una petición por cada tecla.
 *
 * @param source - Ref cuyo valor se quiere debounce-ar.
 * @param delay  - Milisegundos de espera tras el último cambio.
 * @returns      Ref con el valor debounce-ado.
 *
 * @example Búsqueda en tiempo real con debounce de 300 ms
 * ```ts
 * const searchInput = ref('')
 * const search = useDebounce(searchInput, 300)
 *
 * // En el watch, search.value solo cambia 300 ms después de que
 * // el usuario para de escribir — no en cada keystroke.
 * watch(search, (term) => listTramites({ search: term }))
 * ```
 *
 * @example Con useAsync para cancelar peticiones obsoletas
 * ```ts
 * const query = ref('')
 * const debouncedQuery = useDebounce(query, 300)
 * const { data, execute } = useAsync(listTramites)
 *
 * watch(debouncedQuery, (q) => execute({ search: q }))
 * ```
 */

import { onUnmounted, ref, watch } from 'vue'
import type { Ref } from 'vue'

export function useDebounce<T>(source: Ref<T>, delay: number): Ref<T> {
  // Inicia con el valor actual de la fuente para evitar el flash
  // de un valor vacío antes del primer cambio.
  const debounced = ref(source.value) as Ref<T>
  let timer: ReturnType<typeof setTimeout> | undefined

  watch(source, (newValue) => {
    clearTimeout(timer)
    timer = setTimeout(() => {
      debounced.value = newValue
    }, delay)
  })

  // Limpia el timer pendiente si el componente se desmonta antes
  // de que el delay expire — evita actualizar refs destruidas.
  onUnmounted(() => clearTimeout(timer))

  return debounced
}
