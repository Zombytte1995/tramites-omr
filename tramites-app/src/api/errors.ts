/**
 * Jerarquía de errores de la API.
 *
 * Object.setPrototypeOf en la clase base es necesario para que instanceof
 * funcione correctamente en subclases de Error con targets ES5/ES2015.
 * Se llama una sola vez: new.target dentro de super() ya apunta a la
 * subclase más derivada, por lo que el prototipo se establece correctamente
 * para toda la jerarquía.
 *
 * NOTA erasableSyntaxOnly: las propiedades se declaran explícitamente y se
 * asignan en el cuerpo del constructor — no se usan parameter properties
 * de TypeScript (constructor(private x: T)), que generan código runtime.
 */

export class ApiError extends Error {
  readonly status: number

  constructor(message: string, status = 0) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    Object.setPrototypeOf(this, new.target.prototype)
  }
}

/** Errores de validación 422 — contiene el mapa de errores por campo */
export class ValidationError extends ApiError {
  readonly errors: Record<string, string[]>

  constructor(message: string, errors: Record<string, string[]>) {
    super(message, 422)
    this.name = 'ValidationError'
    this.errors = errors
  }
}

/** Token ausente, inválido o expirado (401) */
export class UnauthorizedError extends ApiError {
  constructor(message = 'No autenticado.') {
    super(message, 401)
    this.name = 'UnauthorizedError'
  }
}

/** Recurso no encontrado (404) */
export class NotFoundError extends ApiError {
  constructor(message = 'Recurso no encontrado.') {
    super(message, 404)
    this.name = 'NotFoundError'
  }
}

/** Error interno del servidor (5xx) */
export class ServerError extends ApiError {
  constructor(message = 'Error interno del servidor.') {
    super(message, 500)
    this.name = 'ServerError'
  }
}

/** Sin conexión de red o timeout antes de recibir respuesta */
export class NetworkError extends ApiError {
  constructor(message = 'No se pudo conectar con el servidor. Verifica tu conexión.') {
    super(message, 0)
    this.name = 'NetworkError'
  }
}
