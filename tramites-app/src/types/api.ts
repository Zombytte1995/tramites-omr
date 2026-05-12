/**
 * Tipos que reflejan las respuestas HTTP del backend Laravel.
 *
 * Formas de respuesta del backend:
 *   - JsonResource (recurso único)  →  ApiResponse<T>          { data: T }
 *   - ResourceCollection paginada  →  PaginatedResponse<T>     { data, links, meta }
 *   - Acciones sin recurso         →  ActionResponse           { success, message, errors }
 *   - Errores (Handler)            →  ApiError                 { success: false, message, errors }
 */

// ---------------------------------------------------------------------------
// Respuesta de un único recurso — espejo de Laravel JsonResource
// Ejemplo: GET /tramites/{id}, POST /instituciones, GET /auth/me
// ---------------------------------------------------------------------------
export interface ApiResponse<T> {
  data: T
}

// ---------------------------------------------------------------------------
// Respuesta paginada — espejo de Laravel ResourceCollection con LengthAwarePaginator
// Ejemplo: GET /tramites (TramiteCollection)
// ---------------------------------------------------------------------------
export interface PaginatedResponse<T> {
  data: T[]
  links: PaginationLinks
  meta: PaginationMeta
}

/** Metadatos del paginador de Laravel — espejo de LengthAwarePaginator::toArray() */
export interface PaginationMeta {
  current_page: number
  /** null cuando la consulta no devuelve resultados */
  from: number | null
  last_page: number
  /** Array interno del paginador (números de página + prev/next) */
  links: PaginationLink[]
  path: string
  per_page: number
  /** null cuando la consulta no devuelve resultados */
  to: number | null
  total: number
}

/** Entrada individual del array meta.links de Laravel */
export interface PaginationLink {
  url: string | null
  label: string
  active: boolean
}

/** Links de nivel superior de la respuesta paginada */
export interface PaginationLinks {
  first: string
  last: string
  prev: string | null
  next: string | null
}

// ---------------------------------------------------------------------------
// Respuesta de acciones sin recurso — espejo del formato de AuthController y TramiteController
// Ejemplo: DELETE /tramites/{id}, POST /auth/logout, POST /auth/refresh
// { success: bool, message: string, errors: (object) [] }
// ---------------------------------------------------------------------------
export interface ActionResponse {
  success: boolean
  message: string
  errors: Record<string, string[]>
}

// ---------------------------------------------------------------------------
// Respuesta de error — espejo del Handler.php para cualquier código 4xx/5xx
// Tipo discriminado: success: false permite narrowing automático en el cliente
// ---------------------------------------------------------------------------
export interface ApiError {
  success: false
  message: string
  /** Errores por campo (validación) o vacío para errores genéricos */
  errors: Record<string, string[]>
}
