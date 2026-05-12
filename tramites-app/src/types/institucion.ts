/**
 * Tipos que reflejan InstitucionResource.php y TipoInstitucion.php (enum PHP).
 *
 * NOTA: Se usa const-object + type en lugar de TypeScript enum porque
 * tsconfig.app.json tiene erasableSyntaxOnly: true (TS 6). Los enum
 * de TS generan código runtime, lo cual esa opción prohíbe. El patrón
 * `const as const` + `typeof` es el equivalente idiomático y erasable.
 *
 * Uso equivalente al de un enum:
 *   TipoInstitucion.MINISTERIO       → 'MINISTERIO'
 *   const t: TipoInstitucion = 'ALCALDIA'
 */
export const TipoInstitucion = {
  MINISTERIO: 'MINISTERIO',
  ALCALDIA: 'ALCALDIA',
  AUTONOMA: 'AUTONOMA',
} as const

export type TipoInstitucion = (typeof TipoInstitucion)[keyof typeof TipoInstitucion]

// ---------------------------------------------------------------------------
// Espejo de InstitucionResource::toArray()
//
// "tipo" => [
//     "valor"    => $this->tipo->value,   // ej. "MINISTERIO"
//     "etiqueta" => $this->tipo->label(), // ej. "Ministerio"
// ]
// ---------------------------------------------------------------------------
export interface InstitucionTipo {
  valor: TipoInstitucion
  etiqueta: string
}

/**
 * Espejo completo de InstitucionResource::toArray().
 * Forma de la respuesta de GET /instituciones y campos anidados en TramiteResource.
 */
export interface Institucion {
  id: number
  nombre: string
  tipo: InstitucionTipo
  activo: boolean
  /** Formato: "YYYY-MM-DD HH:mm:ss" (toDateTimeString de Carbon) */
  created_at: string
}

/**
 * Datos del formulario para crear una institución.
 * Refleja CreateInstitucionRequest: nombre + tipo (valor plano, no objeto).
 * activo no se incluye — el backend lo defaultea a true.
 */
export interface InstitucionFormData {
  nombre: string
  tipo: TipoInstitucion
}
