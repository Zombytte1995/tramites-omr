<script setup lang="ts">
  import { reactive, ref } from 'vue'
  import { useRoute, useRouter } from 'vue-router'
  import { useInstitucionesStore } from '@/stores/instituciones'
  import { useToast } from '@/composables/useToast'
  import { ValidationError } from '@/api/errors'
  import { TipoInstitucion } from '@/types'
  import BaseButton from '@/components/ui/BaseButton.vue'
  import BaseInput from '@/components/ui/BaseInput.vue'
  import BaseSelect from '@/components/ui/BaseSelect.vue'
  import type { SelectOption } from '@/components/ui/BaseSelect.vue'
  import type { InstitucionFormData } from '@/types'

  // ── Routing ───────────────────────────────────────────────────────────────────
  const route = useRoute()
  const router = useRouter()
  const institucionesStore = useInstitucionesStore()
  const toast = useToast()

  /**
   * Cuando viene de TramiteFormView (?return=tramite-form), al crear
   * exitosamente redirige de vuelta con la nueva institución preseleccionada.
   */
  const returnToTramiteForm = route.query['return'] === 'tramite-form'

  // ── Opciones del select de tipo ───────────────────────────────────────────────
  const tipoOptions: SelectOption[] = [
    { value: TipoInstitucion.MINISTERIO, label: 'Ministerio' },
    { value: TipoInstitucion.ALCALDIA, label: 'Alcaldía' },
    { value: TipoInstitucion.AUTONOMA, label: 'Institución Autónoma' },
  ]

  // ── Estado del formulario ─────────────────────────────────────────────────────
  const form = reactive({
    nombre: '',
    tipo: null as string | number | null,
  })

  const fieldErrors = reactive<Record<string, string>>({
    nombre: '',
    tipo: '',
  })

  const loading = ref(false)

  // ── Validación ────────────────────────────────────────────────────────────────
  function validateField(field: string): boolean {
    fieldErrors[field] = ''

    switch (field) {
      case 'nombre':
        if (!form.nombre.trim()) {
          fieldErrors['nombre'] = 'El nombre de la institución es obligatorio.'
          return false
        }
        if (form.nombre.length > 200) {
          fieldErrors['nombre'] = 'El nombre no puede superar los 200 caracteres.'
          return false
        }
        return true

      case 'tipo':
        if (!form.tipo) {
          fieldErrors['tipo'] = 'Debe seleccionar el tipo de institución.'
          return false
        }
        return true

      default:
        return true
    }
  }

  function validateAll(): boolean {
    return ['nombre', 'tipo'].map((f) => validateField(f)).every(Boolean)
  }

  // ── Cancelar ──────────────────────────────────────────────────────────────────
  function handleCancel(): void {
    void router.push(returnToTramiteForm ? '/tramites/nuevo' : '/tramites')
  }

  // ── Submit ────────────────────────────────────────────────────────────────────
  async function onSubmit(): Promise<void> {
    if (!validateAll()) return

    loading.value = true

    const payload: InstitucionFormData = {
      nombre: form.nombre.trim(),
      // form.tipo validado como no-null antes de llegar aquí
      tipo: String(form.tipo) as InstitucionFormData['tipo'],
    }

    const nueva = await institucionesStore.create(payload)

    if (nueva) {
      toast.success(`Institución "${nueva.nombre}" creada exitosamente.`)

      if (returnToTramiteForm) {
        // Flujo premium: vuelve al formulario de trámite con la institución
        // recién creada ya preseleccionada en el select.
        await router.push(`/tramites/nuevo?institucion_id=${nueva.id}`)
      } else {
        await router.push('/tramites')
      }
    } else if (institucionesStore.error) {
      const err = institucionesStore.error
      if (err instanceof ValidationError) {
        Object.entries(err.errors).forEach(([field, messages]) => {
          if (field in fieldErrors) fieldErrors[field] = messages[0] ?? ''
        })
      } else {
        toast.error(err.message || 'Error al crear la institución. Intenta de nuevo.')
      }
    }

    loading.value = false
  }
</script>

<template>
  <!--
    Layout "modal-like": gradiente suave + card centrada estrecha.
    No es un modal real (tiene su propia URL) pero visualmente actúa
    como uno: fondo diferenciado, card con shadow prominente, ancho acotado.
  -->
  <div
    class="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-100 via-white to-indigo-50 px-4 py-12"
  >
    <div class="w-full max-w-lg">

      <!-- Contexto: aviso si viene del formulario de trámite -->
      <div
        v-if="returnToTramiteForm"
        class="mb-4 flex items-start gap-2 rounded-lg bg-indigo-50 px-4 py-3 text-sm text-indigo-700 ring-1 ring-indigo-200"
        role="note"
      >
        <span aria-hidden="true">💡</span>
        <span>
          Al crear la institución volverás al formulario de trámite con ella
          ya seleccionada.
        </span>
      </div>

      <!-- Card del formulario -->
      <div class="overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-slate-200/80">

        <!-- Header -->
        <div class="border-b border-slate-100 px-7 py-5">
          <h1 class="text-lg font-semibold text-slate-900">Nueva Institución</h1>
          <p class="mt-0.5 text-sm text-slate-500">
            Registra una nueva institución en el sistema.
          </p>
        </div>

        <!-- Formulario -->
        <form
          novalidate
          aria-label="Formulario de nueva institución"
          class="space-y-5 px-7 py-6"
          @submit.prevent="onSubmit"
        >
          <BaseInput
            v-model="form.nombre"
            label="Nombre de la institución"
            placeholder="Ministerio de Hacienda"
            :error="fieldErrors['nombre'] || undefined"
            :required="true"
            :disabled="loading"
            @blur="validateField('nombre')"
          />

          <BaseSelect
            v-model="form.tipo"
            label="Tipo de institución"
            :options="tipoOptions"
            placeholder="Selecciona el tipo…"
            :error="fieldErrors['tipo'] || undefined"
            :required="true"
            :disabled="loading"
            @blur="validateField('tipo')"
          />
        </form>

        <!-- Footer con botones -->
        <div class="flex justify-end gap-3 border-t border-slate-100 px-7 py-4">
          <BaseButton
            type="button"
            variant="ghost"
            size="md"
            :disabled="loading"
            @click="handleCancel"
          >
            Cancelar
          </BaseButton>

          <BaseButton
            type="submit"
            variant="primary"
            size="md"
            :loading="loading"
            @click="onSubmit"
          >
            Crear institución
          </BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>
