/**
 * Tipos que reflejan AuthController y la respuesta de GET /auth/me.
 */

// ---------------------------------------------------------------------------
// Espejo de la respuesta de GET /auth/me:
// { "data": { "id": number, "name": string, "email": string } }
// ---------------------------------------------------------------------------
export interface User {
  id: number
  name: string
  email: string
}

/**
 * Credenciales enviadas a POST /auth/login.
 * Refleja LoginRequest del backend.
 */
export interface LoginCredentials {
  email: string
  password: string
}

/**
 * Respuesta de POST /auth/login y POST /auth/refresh.
 * Espejo de AuthController::tokenResponse():
 * { success: true, token, token_type: 'bearer', expires_in }
 *
 * NOTA: El backend NO incluye el usuario en la respuesta de login.
 * El campo user es opcional — el store lo agrega tras llamar GET /auth/me.
 */
export interface AuthResponse {
  success: true
  token: string
  token_type: 'bearer'
  /** Segundos hasta que expira el token (TTL del JWT, normalmente 3600) */
  expires_in: number
  /** No viene del backend; el store lo agrega con la respuesta de /auth/me */
  user?: User
}

/**
 * Estado de autenticación persistido en el store de Pinia.
 * Tipo local — no refleja una respuesta del backend directamente.
 */
export interface AuthState {
  user: User | null
  token: string | null
  /** Timestamp Unix (ms) en que expira el token. Calculado desde expires_in. */
  expiresAt: number | null
}
