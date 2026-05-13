/**
 * Store de instituciones.
 *
 * Cachea la lista completa de instituciones activas en memoria.
 * El backend las devuelve todas en una sola petición (sin paginación),
 * por lo que se puede usar como lookup table local: getById evita
 * peticiones redundantes en la vista de detalle de trámite.
 */

import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { createInstitucion, deactivateInstitucion, listInstituciones, updateInstitucion } from '@/api/instituciones'
import type { Institucion, InstitucionFormData } from '@/types'

export const useInstitucionesStore = defineStore('instituciones', () => {
  // ── State ───────────────────────────────────────────────────────────────────
  const instituciones = ref<Institucion[]>([])
  const loading = ref(false)
  const error = ref<Error | null>(null)

  // ── Getters ─────────────────────────────────────────────────────────────────

  /**
   * Subconjunto de instituciones con activo === true.
   * El backend de GET /instituciones ya solo devuelve activas, pero si en
   * el futuro se permite cargar todas (activas + inactivas para admin),
   * este getter seguirá funcionando como filtro de presentación.
   */
  const activeInstituciones = computed(() =>
    instituciones.value.filter((i) => i.activo),
  )

  /**
   * Busca una institución por ID en el caché local.
   * Devuelve una función para poder usarlo como getter con argumento:
   *   const inst = store.getById(3)
   *
   * @returns La institución encontrada o undefined si no está en caché.
   */
  const getById = computed(
    () =>
      (id: number): Institucion | undefined =>
        instituciones.value.find((i) => i.id === id),
  )

  // ── Actions ─────────────────────────────────────────────────────────────────

  /**
   * Carga todas las instituciones activas desde GET /instituciones.
   * Sobrescribe el caché local con la respuesta del servidor.
   * Idempotente: puede llamarse múltiples veces sin efectos secundarios.
   */
  async function fetchAll(): Promise<void> {
    loading.value = true
    error.value = null
    try {
      instituciones.value = await listInstituciones()
    } catch (e) {
      error.value = e instanceof Error ? e : new Error(String(e))
    } finally {
      loading.value = false
    }
  }

  /**
   * Crea una nueva institución via POST /instituciones y la agrega al caché.
   *
   * Actualizar el caché localmente evita un refetch innecesario tras la creación.
   * En caso de error, error.value contendrá el detalle (ValidationError, etc.).
   *
   * @param data - Nombre y tipo de la institución a crear.
   * @returns La institución creada o null si hubo error.
   */
  async function create(data: InstitucionFormData): Promise<Institucion | null> {
    loading.value = true
    error.value = null
    try {
      const nueva = await createInstitucion(data)
      instituciones.value.push(nueva)
      return nueva
    } catch (e) {
      error.value = e instanceof Error ? e : new Error(String(e))
      return null
    } finally {
      loading.value = false
    }
  }

  async function update(id: number, data: Partial<InstitucionFormData>): Promise<Institucion | null> {
    loading.value = true
    error.value = null
    try {
      const actualizada = await updateInstitucion(id, data)
      const idx = instituciones.value.findIndex((i) => i.id === id)
      if (idx !== -1) instituciones.value[idx] = actualizada
      return actualizada
    } catch (e) {
      error.value = e instanceof Error ? e : new Error(String(e))
      return null
    } finally {
      loading.value = false
    }
  }

  async function deactivate(id: number): Promise<boolean> {
    loading.value = true
    error.value = null
    try {
      await deactivateInstitucion(id)
      instituciones.value = instituciones.value.filter((i) => i.id !== id)
      return true
    } catch (e) {
      error.value = e instanceof Error ? e : new Error(String(e))
      return false
    } finally {
      loading.value = false
    }
  }

  return {
    // state
    instituciones,
    loading,
    error,
    // getters
    activeInstituciones,
    getById,
    // actions
    fetchAll,
    create,
    update,
    deactivate,
  }
})
