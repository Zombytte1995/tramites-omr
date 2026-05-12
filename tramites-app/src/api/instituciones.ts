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
