/** Espejo de la respuesta de GET /api/dashboard/stats */

export interface DashboardInstitucionStat {
  institucion: string
  tipo: string
  total: number
  activos: number
}

export interface DashboardTramiteReciente {
  id: number
  codigo: string
  nombre: string
  dias_habiles: number
  activo: boolean
  created_at: string
  institucion_nombre: string | null
}

export interface DashboardStats {
  total_tramites: number
  tramites_activos: number
  tramites_inactivos: number
  total_instituciones: number
  tramites_por_institucion: DashboardInstitucionStat[]
  tramites_recientes: DashboardTramiteReciente[]
}
