import type { ApiResponse, AuthResponse, LoginCredentials, User } from '@/types'
import apiClient, { TOKEN_KEY } from './client'

/**
 * POST /auth/login
 * Autentica al usuario y devuelve el JWT.
 * El token NO se persiste aquí — es responsabilidad del store de auth.
 */
export async function login(credentials: LoginCredentials): Promise<AuthResponse> {
  const { data } = await apiClient.post<AuthResponse>('/auth/login', credentials)
  return data
}

/**
 * POST /auth/logout
 * Invalida el token en la blacklist del servidor y lo elimina de localStorage.
 */
export async function logout(): Promise<void> {
  await apiClient.post('/auth/logout')
  localStorage.removeItem(TOKEN_KEY)
}

/**
 * GET /auth/me
 * Devuelve los datos del usuario autenticado.
 * Usado tras el login para obtener el perfil completo.
 */
export async function me(): Promise<User> {
  const { data } = await apiClient.get<ApiResponse<User>>('/auth/me')
  return data.data
}

/**
 * POST /auth/refresh
 * Rota el JWT: invalida el token actual y emite uno nuevo.
 * El store debe reemplazar el token en localStorage con el nuevo.
 */
export async function refresh(): Promise<AuthResponse> {
  const { data } = await apiClient.post<AuthResponse>('/auth/refresh')
  return data
}
