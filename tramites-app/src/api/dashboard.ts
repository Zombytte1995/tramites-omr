import type { ApiResponse } from '@/types'
import type { DashboardStats } from '@/types/dashboard'
import apiClient from './client'

/**
 * GET /api/dashboard/stats
 * Devuelve las métricas agregadas del sistema (requiere autenticación).
 */
export async function getDashboardStats(): Promise<DashboardStats> {
  const { data } = await apiClient.get<ApiResponse<DashboardStats>>('/dashboard/stats')
  return data.data
}
