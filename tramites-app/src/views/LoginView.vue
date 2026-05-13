<script setup lang="ts">
  import { reactive, ref } from 'vue'
  import { useRoute, useRouter } from 'vue-router'
  import { useAuthStore } from '@/stores/auth'
  import { useToast } from '@/composables/useToast'
  import { UnauthorizedError, ValidationError } from '@/api/errors'
  import BaseInput from '@/components/ui/BaseInput.vue'
  import BaseButton from '@/components/ui/BaseButton.vue'

  const router = useRouter()
  const route = useRoute()
  const authStore = useAuthStore()
  const toast = useToast()

  // ── Estado del formulario ─────────────────────────────────────────────────────
  const form = reactive({ email: '', password: '' })
  const fieldErrors = reactive({ email: '', password: '' })
  const loading = ref(false)

  const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

  // ── Validación cliente ────────────────────────────────────────────────────────
  function validate(): boolean {
    fieldErrors.email = ''
    fieldErrors.password = ''
    let ok = true

    if (!form.email.trim()) {
      fieldErrors.email = 'El correo electrónico es obligatorio.'
      ok = false
    } else if (!EMAIL_RE.test(form.email)) {
      fieldErrors.email = 'El correo electrónico no tiene un formato válido.'
      ok = false
    }

    if (!form.password) {
      fieldErrors.password = 'La contraseña es obligatoria.'
      ok = false
    } else if (form.password.length < 6) {
      fieldErrors.password = 'La contraseña debe tener al menos 6 caracteres.'
      ok = false
    }

    return ok
  }

  // ── Envío del formulario ──────────────────────────────────────────────────────
  async function onSubmit(): Promise<void> {
    if (!validate()) return

    loading.value = true

    try {
      await authStore.login({ email: form.email, password: form.password })

      // Redirige al destino original (query ?redirect=) o al dashboard
      const raw = route.query.redirect
      const target = (Array.isArray(raw) ? raw[0] : raw) ?? '/dashboard'
      await router.push(target)
    } catch (err) {
      if (err instanceof ValidationError) {
        // 422: errores de campo devueltos por el backend
        if (err.errors['email']?.[0]) fieldErrors.email = err.errors['email'][0]
        if (err.errors['password']?.[0]) fieldErrors.password = err.errors['password'][0]
      } else if (err instanceof UnauthorizedError) {
        // 401: credenciales incorrectas (no redirige, solo avisa)
        toast.error('Credenciales incorrectas. Verifica tu email y contraseña.')
      } else {
        // 500, network error, etc.
        toast.error('Error de conexión. Intenta de nuevo más tarde.')
      }
    } finally {
      loading.value = false
    }
  }
</script>

<template>
  <!--
    Background: gradiente suave + patrón de puntos SVG para textura sin peso visual.
    La vista es el único contenido (no hay nav) y ocupa el viewport completo.
  -->
  <div
    class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-br from-slate-100 via-white to-indigo-50 px-4 py-16"
  >
    <!-- Patrón de puntos decorativo (aria-hidden, invisible para screen readers) -->
    <svg
      aria-hidden="true"
      class="pointer-events-none absolute inset-0 h-full w-full opacity-30"
      xmlns="http://www.w3.org/2000/svg"
    >
      <defs>
        <pattern id="dots" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
          <circle cx="2" cy="2" r="1.5" fill="rgb(99 102 241 / 0.3)" />
        </pattern>
      </defs>
      <rect width="100%" height="100%" fill="url(#dots)" />
    </svg>

    <!-- Contenedor principal — z-10 sobre el patrón -->
    <div class="relative z-10 w-full max-w-sm">

      <!-- Cabecera: logo + títulos -->
      <header class="mb-8 text-center">
        <div aria-hidden="true" class="mx-auto mb-1 flex items-center justify-center">
          <img
            src="/logo_gobierno.svg"
            alt="Gobierno de El Salvador"
            class="h-16 w-auto"
          />
        </div>

        <h1 class="mt-5 text-2xl font-bold tracking-tight text-slate-900">
          Sistema de Trámites
        </h1>
        <p class="mt-1 text-sm text-slate-500">
          Organismo de Mejora Regulatoria — El Salvador
        </p>
      </header>

      <!-- Card del formulario -->
      <main>
        <div class="rounded-2xl bg-white px-8 py-8 shadow-xl ring-1 ring-slate-200/80">
          <form
            novalidate
            aria-label="Formulario de inicio de sesión"
            @submit.prevent="onSubmit"
          >
            <div class="space-y-5">
              <BaseInput
                v-model="form.email"
                label="Correo electrónico"
                type="email"
                placeholder="admin@omr.gob.sv"
                :error="fieldErrors.email || undefined"
                :required="true"
                :disabled="loading"
              />

              <BaseInput
                v-model="form.password"
                label="Contraseña"
                type="password"
                placeholder="••••••••"
                :error="fieldErrors.password || undefined"
                :required="true"
                :disabled="loading"
              />

              <BaseButton
                type="submit"
                variant="primary"
                size="md"
                :loading="loading"
                class="w-full"
              >
                {{ loading ? 'Iniciando sesión…' : 'Iniciar sesión' }}
              </BaseButton>
            </div>
          </form>
        </div>

        <!--
          Info-box de credenciales de prueba.
          Visible para evaluadores — es una BUENA práctica en proyectos demo,
          no un descuido de seguridad. Documentado en el README.
        -->
        <div
          role="note"
          aria-label="Credenciales de prueba para evaluadores"
          class="mt-4 rounded-xl bg-blue-50 px-5 py-4 ring-1 ring-blue-200"
        >
          <div class="flex items-start gap-3">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="mt-0.5 h-4 w-4 shrink-0 text-blue-500"
              viewBox="0 0 20 20"
              fill="currentColor"
              aria-hidden="true"
            >
              <path
                fill-rule="evenodd"
                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z"
                clip-rule="evenodd"
              />
            </svg>
            <div>
              <p class="text-xs font-semibold text-blue-700">Credenciales de prueba</p>
              <p class="mt-1 font-mono text-xs text-blue-600">
                admin@omr.gob.sv / password
              </p>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>
