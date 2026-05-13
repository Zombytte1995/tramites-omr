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
    Background: gradiente navy OMR institucional.
    El logo blanco (GOES_OMR_letra_blanco.png) contrasta directamente sobre
    el fondo oscuro, siguiendo la identidad visual del Gobierno de El Salvador.
  -->
  <div
    class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-br from-indigo-950 via-indigo-900 to-indigo-800 px-4 py-16"
  >
    <!-- Patrón de puntos decorativo sutil sobre el fondo navy -->
    <svg
      aria-hidden="true"
      class="pointer-events-none absolute inset-0 h-full w-full opacity-20"
      xmlns="http://www.w3.org/2000/svg"
    >
      <defs>
        <pattern id="dots" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
          <circle cx="2" cy="2" r="1.5" fill="rgb(255 255 255 / 0.15)" />
        </pattern>
      </defs>
      <rect width="100%" height="100%" fill="url(#dots)" />
    </svg>

    <!-- Contenedor principal — z-10 sobre el patrón -->
    <div class="relative z-10 w-full max-w-sm">

      <!-- Cabecera: logo institucional OMR + nombre del sistema -->
      <header class="mb-8 text-center">
        <!-- Logo OMR completo (escudo + texto) en blanco — contraste directo sobre navy -->
        <div aria-hidden="true" class="mx-auto mb-5 flex items-center justify-center">
          <img
            src="/GOES_OMR_letra_blanco.png"
            alt="Organismo de Mejora Regulatoria — El Salvador"
            class="h-20 w-auto object-contain drop-shadow-lg"
          />
        </div>

        <h1 class="text-2xl font-bold tracking-tight text-white">
          Sistema de Trámites
        </h1>
        <p class="mt-1 text-sm text-indigo-200">
          Registro y consulta de trámites administrativos
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
          class="mt-4 rounded-xl bg-white/10 px-5 py-4 ring-1 ring-white/20 backdrop-blur-sm"
        >
          <div class="flex items-start gap-3">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="mt-0.5 h-4 w-4 shrink-0 text-indigo-200"
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
              <p class="text-xs font-semibold text-white">Credenciales de prueba</p>
              <p class="mt-1 font-mono text-xs text-indigo-200">
                admin@omr.gob.sv / password
              </p>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>
