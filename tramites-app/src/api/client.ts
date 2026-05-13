import axios from 'axios'
import type { AxiosResponse, InternalAxiosRequestConfig } from 'axios'
import {
  ApiError,
  NetworkError,
  NotFoundError,
  ServerError,
  UnauthorizedError,
  ValidationError,
} from './errors'

/** Clave usada en localStorage para el JWT. Centralizada aquí para que
 *  el store y los tests usen siempre el mismo valor. */
export const TOKEN_KEY = 'auth_token'

/** Shape mínima de las respuestas de error del Handler de Laravel */
type BackendError = {
  message?: string
  errors?: Record<string, string[]>
}

const apiClient = axios.create({
  baseURL: (import.meta.env.VITE_API_URL as string | undefined) ?? 'http://localhost:8000/api',
  timeout: 10_000,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

// ── Interceptor de request ────────────────────────────────────────────────────
apiClient.interceptors.request.use((config: InternalAxiosRequestConfig) => {
  const token = localStorage.getItem(TOKEN_KEY)
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// ── Interceptor de response ───────────────────────────────────────────────────
apiClient.interceptors.response.use(
  (response: AxiosResponse) => response,

  (error: unknown) => {
    // Sin respuesta del servidor: timeout o fallo de red
    if (!axios.isAxiosError(error) || !error.response) {
      throw new NetworkError()
    }

    const { status, data, config } = error.response
    const body = data as BackendError | undefined
    const message = body?.message

    if (status === 401) {
      // El 401 del endpoint de login significa "credenciales incorrectas",
      // no una sesión expirada — no debe redirigir al usuario.
      const isLoginRequest = config?.url?.includes('/auth/login') ?? false

      if (!isLoginRequest) {
        localStorage.removeItem(TOKEN_KEY)
        window.location.href = '/login'
      }

      throw new UnauthorizedError(message)
    }

    if (status === 404) {
      throw new NotFoundError(message)
    }

    if (status === 422) {
      throw new ValidationError(
        message ?? 'Los datos proporcionados no son válidos.',
        body?.errors ?? {},
      )
    }

    if (status >= 500) {
      throw new ServerError(message)
    }

    // Cualquier otro 4xx (403, 405, 429…)
    throw new ApiError(message ?? `Error ${status}.`, status)
  },
)

export default apiClient
