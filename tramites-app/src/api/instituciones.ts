import type { ApiResponse, Institucion, InstitucionFormData } from '@/types'
import apiClient from './client'

/**
 * GET /instituciones
 * Devuelve todas las instituciones activas ordenadas por nombre.
 * Endpoint público — no requiere autenticación.
 */
export async function listInstituciones(): Promise<Institucion[]> {
  const { data } = await apiClient.get<ApiResponse<Institucion[]>>('/instituciones')
  return data.data
}

/**
 * POST /instituciones
 * Crea una nueva institución.
 * Requiere autenticación.
 */
export async function createInstitucion(payload: InstitucionFormData): Promise<Institucion> {
  const { data } = await apiClient.post<ApiResponse<Institucion>>('/instituciones', payload)
  return data.data
}

/**
 * PUT /instituciones/{id}
 * Actualiza nombre y/o tipo de una institución.
 * Requiere autenticación.
 */
export async function updateInstitucion(
  id: number,
  payload: Partial<InstitucionFormData>,
): Promise<Institucion> {
  const { data } = await apiClient.put<ApiResponse<Institucion>>(`/instituciones/${id}`, payload)
  return data.data
}

/**
 * DELETE /instituciones/{id}
 * Desactiva una institución (soft delete lógico: activo = false).
 * Falla con 422 si tiene trámites activos asociados.
 * Requiere autenticación.
 */
export async function deactivateInstitucion(id: number): Promise<void> {
  await apiClient.delete(`/instituciones/${id}`)
}
