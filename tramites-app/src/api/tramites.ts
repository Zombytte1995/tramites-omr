import type {
  ApiResponse,
  PaginatedResponse,
  Tramite,
  TramiteFilters,
  TramiteFormData,
  TramiteResumen,
} from '@/types'
import apiClient from './client'

/**
 * GET /tramites
 * Devuelve el listado paginado de trámites activos con filtros opcionales.
 */
export async function listTramites(
  filters: TramiteFilters = {},
): Promise<PaginatedResponse<Tramite>> {
  const { data } = await apiClient.get<PaginatedResponse<Tramite>>('/tramites', {
    params: filters,
  })
  return data
}

/**
 * GET /tramites/{id}
 * Devuelve un único trámite con la institución cargada.
 */
export async function getTramite(id: number): Promise<Tramite> {
  const { data } = await apiClient.get<ApiResponse<Tramite>>(`/tramites/${id}`)
  return data.data
}

/**
 * POST /tramites
 * Crea un nuevo trámite. Requiere autenticación.
 */
export async function createTramite(payload: TramiteFormData): Promise<Tramite> {
  const { data } = await apiClient.post<ApiResponse<Tramite>>('/tramites', payload)
  return data.data
}

/**
 * PUT /tramites/{id}
 * Actualiza los campos del trámite. Acepta actualización parcial.
 * Requiere autenticación.
 */
export async function updateTramite(
  id: number,
  payload: Partial<TramiteFormData>,
): Promise<Tramite> {
  const { data } = await apiClient.put<ApiResponse<Tramite>>(`/tramites/${id}`, payload)
  return data.data
}

/**
 * DELETE /tramites/{id}
 * Desactiva el trámite (soft delete lógico: activo = false).
 * Requiere autenticación.
 */
export async function deactivateTramite(id: number): Promise<void> {
  await apiClient.delete(`/tramites/${id}`)
}

/**
 * POST /tramites/{id}/resumen-ia
 * Genera un resumen ejecutivo del trámite usando Gemini API.
 * Requiere autenticación. Resultado cacheado 24h en el servidor.
 */
export async function generarResumenIA(id: number): Promise<TramiteResumen> {
  const { data } = await apiClient.post<ApiResponse<TramiteResumen>>(
    `/tramites/${id}/resumen-ia`,
  )
  return data.data
}
