/**
 * Store de trámites.
 *
 * Gestiona la lista paginada (con filtros), el detalle del trámite actual
 * y el ciclo de vida completo: crear, editar, desactivar y generar resumen IA.
 *
 * Los filtros se persisten en el store para que al volver al listado
 * los criterios de búsqueda no se pierdan.
 */

import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import {
  createTramite,
  deactivateTramite,
  generarResumenIA as apiGenerarResumenIA,
  getTramite,
  listTramites,
  updateTramite,
} from '@/api/tramites'
import type {
  PaginatedResponse,
  Tramite,
  TramiteFilters,
  TramiteFormData,
  TramiteResumen,
} from '@/types'

export const useTramitesStore = defineStore('tramites', () => {
  // ── State ───────────────────────────────────────────────────────────────────

  /** Respuesta paginada completa del listado. null hasta la primera carga. */
  const tramites = ref<PaginatedResponse<Tramite> | null>(null)

  /** Trámite actualmente visualizado en la vista de detalle. */
  const currentTramite = ref<Tramite | null>(null)

  /** Filtros activos aplicados al listado. Persisten entre navegaciones. */
  const filters = ref<TramiteFilters>({})

  const loading = ref(false)
  const error = ref<Error | null>(null)

  // ── Getters ─────────────────────────────────────────────────────────────────

  /**
   * true si hay al menos un filtro activo (búsqueda o institución seleccionada).
   * Útil para mostrar el botón "Limpiar filtros" en el listado.
   */
  const hasFilters = computed(
    () => Boolean(filters.value.institucion_id ?? filters.value.search),
  )

  // ── Helpers ─────────────────────────────────────────────────────────────────

  function captureError(e: unknown): void {
    error.value = e instanceof Error ? e : new Error(String(e))
  }

  // ── Actions ─────────────────────────────────────────────────────────────────

  /**
   * Carga el listado paginado de trámites aplicando los filtros dados.
   * Si se pasan newFilters, reemplaza los filtros actuales y reinicia a página 1.
   *
   * @param newFilters - Filtros opcionales; omitir para recargar con los actuales.
   */
  async function fetchList(newFilters?: TramiteFilters): Promise<void> {
    if (newFilters !== undefined) {
      filters.value = newFilters
    }
    loading.value = true
    error.value = null
    try {
      tramites.value = await listTramites(filters.value)
    } catch (e) {
      captureError(e)
    } finally {
      loading.value = false
    }
  }

  /**
   * Carga un único trámite por ID en currentTramite.
   * Incluye la institución relacionada (eager-loaded por el backend).
   *
   * @param id - ID del trámite a cargar.
   */
  async function fetchOne(id: number): Promise<void> {
    loading.value = true
    error.value = null
    try {
      currentTramite.value = await getTramite(id)
    } catch (e) {
      captureError(e)
    } finally {
      loading.value = false
    }
  }

  /**
   * Crea un nuevo trámite via POST /tramites.
   * No actualiza el listado automáticamente — el componente debe llamar
   * fetchList() si necesita reflejar el nuevo trámite en la tabla.
   *
   * @param data - Datos validados del formulario de creación.
   * @returns El trámite creado o null si hubo error.
   */
  async function create(data: TramiteFormData): Promise<Tramite | null> {
    loading.value = true
    error.value = null
    try {
      return await createTramite(data)
    } catch (e) {
      captureError(e)
      return null
    } finally {
      loading.value = false
    }
  }

  /**
   * Actualiza los campos de un trámite via PUT /tramites/{id}.
   * Si el trámite editado es el que está en currentTramite, lo sincroniza.
   *
   * @param id   - ID del trámite a actualizar.
   * @param data - Campos a modificar (parcial; el backend usa `sometimes`).
   * @returns El trámite actualizado o null si hubo error.
   */
  async function update(
    id: number,
    data: Partial<TramiteFormData>,
  ): Promise<Tramite | null> {
    loading.value = true
    error.value = null
    try {
      const actualizado = await updateTramite(id, data)
      if (currentTramite.value?.id === id) {
        currentTramite.value = actualizado
      }
      return actualizado
    } catch (e) {
      captureError(e)
      return null
    } finally {
      loading.value = false
    }
  }

  /**
   * Desactiva un trámite via DELETE /tramites/{id} (soft delete lógico).
   * Actualiza el listado local eliminando el trámite desactivado para evitar
   * un refetch completo. Si era el currentTramite, marca activo = false.
   *
   * @param id - ID del trámite a desactivar.
   * @returns true si la operación fue exitosa, false si hubo error.
   */
  async function deactivate(id: number): Promise<boolean> {
    loading.value = true
    error.value = null
    try {
      await deactivateTramite(id)

      // Elimina del listado local (el backend excluye inactivos del listado)
      if (tramites.value !== null) {
        tramites.value = {
          ...tramites.value,
          data: tramites.value.data.filter((t) => t.id !== id),
        }
      }

      // Sincroniza currentTramite si es el mismo
      const current = currentTramite.value
      if (current !== null && current.id === id) {
        currentTramite.value = { ...current, activo: false }
      }

      return true
    } catch (e) {
      captureError(e)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Genera un resumen ejecutivo del trámite usando Gemini API.
   * El servidor cachea el resultado 24h, así que llamadas repetidas son baratas.
   *
   * Los errores 503 (Gemini no configurada/caída) se capturan en error.value.
   * El componente puede distinguirlos con error.value instanceof ServerError.
   *
   * @param id - ID del trámite para el que generar el resumen.
   * @returns El objeto resumen o null si Gemini no está disponible.
   */
  async function generarResumenIA(id: number): Promise<TramiteResumen | null> {
    loading.value = true
    error.value = null
    try {
      return await apiGenerarResumenIA(id)
    } catch (e) {
      captureError(e)
      return null
    } finally {
      loading.value = false
    }
  }

  return {
    // state
    tramites,
    currentTramite,
    filters,
    loading,
    error,
    // getters
    hasFilters,
    // actions
    fetchList,
    fetchOne,
    create,
    update,
    deactivate,
    generarResumenIA,
  }
})
