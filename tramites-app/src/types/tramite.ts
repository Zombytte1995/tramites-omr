import type { Institucion } from './institucion'

/**
 * Espejo completo de TramiteResource::toArray().
 *
 * institucion es opcional porque el backend usa whenLoaded():
 *   new InstitucionResource($this->whenLoaded('institucion'))
 * En la práctica los endpoints siempre lo eager-load, pero el tipo
 * debe ser honesto con el contrato del Resource.
 */
export interface Tramite {
  id: number
  codigo: string
  nombre: string
  descripcion: string
  dias_habiles: number
  activo: boolean
  /** Presente cuando la relación fue eager-loaded (siempre en los endpoints actuales) */
  institucion?: Institucion
  /** Formato: "YYYY-MM-DD HH:mm:ss" (toDateTimeString de Carbon) */
  created_at: string
  /** Formato: "YYYY-MM-DD HH:mm:ss" (toDateTimeString de Carbon) */
  updated_at: string
}

/**
 * Datos del formulario para crear un trámite.
 * Refleja los campos de CreateTramiteRequest (todos requeridos).
 * Para actualizar usa Partial<TramiteFormData> (UpdateTramiteRequest usa `sometimes`).
 */
export interface TramiteFormData {
  codigo: string
  nombre: string
  descripcion: string
  institucion_id: number
  dias_habiles: number
}

/**
 * Filtros del query string para GET /tramites.
 * Todos opcionales — el backend ignora los que no están presentes.
 */
export interface TramiteFilters {
  institucion_id?: number
  search?: string
  page?: number
}

/**
 * Espejo del campo data de la respuesta de POST /tramites/{id}/resumen-ia:
 * { "data": { "tramite_id": number, "resumen": string } }
 */
export interface TramiteResumen {
  tramite_id: number
  resumen: string
}
