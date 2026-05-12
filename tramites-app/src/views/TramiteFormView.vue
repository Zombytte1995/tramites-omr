<script setup lang="ts">
  import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue'
  import { RouterLink, useRoute, useRouter } from 'vue-router'
  import { useTramitesStore } from '@/stores/tramites'
  import { useInstitucionesStore } from '@/stores/instituciones'
  import { useToast } from '@/composables/useToast'
  import { useAsync } from '@/composables/useAsync'
  import { ValidationError } from '@/api/errors'
  import BaseButton from '@/components/ui/BaseButton.vue'
  import BaseInput from '@/components/ui/BaseInput.vue'
  import BaseSelect from '@/components/ui/BaseSelect.vue'
  import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'
  import type { SelectOption } from '@/components/ui/BaseSelect.vue'
  import type { TramiteFormData } from '@/types'

  // ── Stores & composables ──────────────────────────────────────────────────────
  const route = useRoute()
  const router = useRouter()
  const tramitesStore = useTramitesStore()
  const institucionesStore = useInstitucionesStore()
  const toast = useToast()

  // ── Modo: crear vs editar ─────────────────────────────────────────────────────
  const isEdit = computed(() => !!route.params['id'])
  const tramiteId = computed(() => (isEdit.value ? Number(route.params['id']) : null))

  // ── Estado del formulario ─────────────────────────────────────────────────────
  interface FormState {
    codigo: string
    nombre: string
    descripcion: string
    institucion_id: string | number | null
    dias_habiles: string
    activo: boolean
  }

  const form = reactive<FormState>({
    codigo: '',
    nombre: '',
    descripcion: '',
    institucion_id: null,
    dias_habiles: '',
    activo: true,
  })

  const fieldErrors = reactive<Record<string, string>>({
    codigo: '',
    nombre: '',
    descripcion: '',
    institucion_id: '',
    dias_habiles: '',
  })

  const loading = ref(false)

  // Snapshot del estado original para detectar cambios sin guardar
  const initialForm = ref<FormState | null>(null)

  const isDirty = computed(() => {
    const init = initialForm.value
    if (!init) return false
    return (
      form.codigo !== init.codigo ||
      form.nombre !== init.nombre ||
      form.descripcion !== init.descripcion ||
      String(form.institucion_id) !== String(init.institucion_id) ||
      form.dias_habiles !== init.dias_habiles ||
      form.activo !== init.activo
    )
  })

  // ── Validación ────────────────────────────────────────────────────────────────
  function validateField(field: string): boolean {
    fieldErrors[field] = ''

    switch (field) {
      case 'codigo':
        if (!form.codigo.trim()) {
          fieldErrors['codigo'] = 'El código es obligatorio.'
          return false
        }
        if (form.codigo.length > 20) {
          fieldErrors['codigo'] = 'El código no puede superar los 20 caracteres.'
          return false
        }
        return true

      case 'nombre':
        if (!form.nombre.trim()) {
          fieldErrors['nombre'] = 'El nombre es obligatorio.'
          return false
        }
        if (form.nombre.length > 200) {
          fieldErrors['nombre'] = 'El nombre no puede superar los 200 caracteres.'
          return false
        }
        return true

      case 'descripcion':
        if (form.descripcion.length > 1000) {
          fieldErrors['descripcion'] = 'La descripción no puede superar los 1000 caracteres.'
          return false
        }
        return true

      case 'institucion_id':
        if (form.institucion_id === null || form.institucion_id === '') {
          fieldErrors['institucion_id'] = 'Debe seleccionar una institución.'
          return false
        }
        return true

      case 'dias_habiles': {
        const n = Number(form.dias_habiles)
        if (!form.dias_habiles) {
          fieldErrors['dias_habiles'] = 'Los días hábiles son obligatorios.'
          return false
        }
        if (!Number.isInteger(n) || n < 1) {
          fieldErrors['dias_habiles'] = 'Ingresa un número entero mayor a 0.'
          return false
        }
        if (n > 365) {
          fieldErrors['dias_habiles'] = 'No puede superar los 365 días hábiles.'
          return false
        }
        return true
      }

      default:
        return true
    }
  }

  function validateAll(): boolean {
    const fields = ['codigo', 'nombre', 'descripcion', 'institucion_id', 'dias_habiles']
    return fields.map((f) => validateField(f)).every(Boolean)
  }

  // ── Carga inicial (useAsync + immediate) ──────────────────────────────────────
  function fillFormFromStore(): void {
    const t = tramitesStore.currentTramite
    if (!t) return
    form.codigo = t.codigo
    form.nombre = t.nombre
    form.descripcion = t.descripcion
    form.institucion_id = t.institucion?.id ?? null
    form.dias_habiles = String(t.dias_habiles)
    form.activo = t.activo
  }

  const { loading: initLoading } = useAsync(
    async () => {
      await Promise.all([
        institucionesStore.instituciones.length === 0
          ? institucionesStore.fetchAll()
          : Promise.resolve(),
        isEdit.value && tramiteId.value !== null
          ? tramitesStore.fetchOne(tramiteId.value)
          : Promise.resolve(),
      ])

      if (isEdit.value) {
        fillFormFromStore()
      } else if (route.query['institucion_id']) {
        // Flujo de retorno desde InstitucionFormView (?return=tramite-form):
        // preselecciona la institución recién creada en el select.
        form.institucion_id = Number(route.query['institucion_id'])
      }

      // Snapshot inicial — a partir de aquí isDirty puede ser true
      initialForm.value = { ...form }
    },
    { immediate: true },
  )

  // ── Select de instituciones ───────────────────────────────────────────────────
  const institucionOptions = computed((): SelectOption[] =>
    institucionesStore.instituciones.map((i) => ({ value: i.id, label: i.nombre })),
  )

  const descLength = computed(() => form.descripcion.length)
  const DESC_MAX = 1000

  // ── Manejo de envío ───────────────────────────────────────────────────────────
  function handleServerError(err: Error): void {
    if (err instanceof ValidationError) {
      Object.entries(err.errors).forEach(([field, messages]) => {
        if (field in fieldErrors) {
          fieldErrors[field] = messages[0] ?? ''
        }
      })
    } else {
      toast.error(err.message || 'Error al guardar. Intenta de nuevo.')
    }
  }

  async function onSubmit(): Promise<void> {
    if (!validateAll()) return

    loading.value = true

    const payload: TramiteFormData = {
      codigo: form.codigo.trim(),
      nombre: form.nombre.trim(),
      descripcion: form.descripcion.trim(),
      institucion_id: Number(form.institucion_id),
      dias_habiles: Number(form.dias_habiles),
    }

    try {
      if (isEdit.value && tramiteId.value !== null) {
        const result = await tramitesStore.update(tramiteId.value, payload)
        if (result !== null) {
          ignoreLeave.value = true
          toast.success('Trámite actualizado correctamente.')
          await router.push('/tramites')
        } else if (tramitesStore.error) {
          handleServerError(tramitesStore.error)
        }
      } else {
        const result = await tramitesStore.create(payload)
        if (result !== null) {
          ignoreLeave.value = true
          toast.success('Trámite creado correctamente.')
          await router.push('/tramites')
        } else if (tramitesStore.error) {
          handleServerError(tramitesStore.error)
        }
      }
    } finally {
      loading.value = false
    }
  }

  // ── Cancelar con dirty check ──────────────────────────────────────────────────
  function handleCancel(): void {
    if (isDirty.value) {
      pendingRoute.value = '/tramites'
      showLeaveConfirm.value = true
    } else {
      void router.push('/tramites')
    }
  }

  // ── Guard de salida con cambios sin guardar ───────────────────────────────────
  const showLeaveConfirm = ref(false)
  const pendingRoute = ref<string | null>(null)
  const ignoreLeave = ref(false)

  const removeGuard = router.beforeEach((to) => {
    if (ignoreLeave.value || !isDirty.value) return true
    // No bloquear navegación interna a la misma ruta (p.ej. reloads)
    if (to.name === route.name) return true
    pendingRoute.value = to.fullPath
    showLeaveConfirm.value = true
    return false
  })

  async function confirmLeave(): Promise<void> {
    showLeaveConfirm.value = false
    ignoreLeave.value = true
    await router.push(pendingRoute.value ?? '/tramites')
  }

  function cancelLeave(): void {
    showLeaveConfirm.value = false
    pendingRoute.value = null
  }

  // ── Atajos de teclado ─────────────────────────────────────────────────────────
  function onKeydown(event: KeyboardEvent): void {
    if ((event.ctrlKey || event.metaKey) && event.key === 's') {
      event.preventDefault()
      void onSubmit()
    }
    if (event.key === 'Escape' && !showLeaveConfirm.value) {
      handleCancel()
    }
  }

  onMounted(() => window.addEventListener('keydown', onKeydown))
  onUnmounted(() => {
    window.removeEventListener('keydown', onKeydown)
    removeGuard()
  })

  // Limpiar fieldErrors del servidor cuando el usuario corrige el campo
  watch(
    () => ({ ...form }),
    (next, prev) => {
      const keys = Object.keys(next) as (keyof FormState)[]
      for (const key of keys) {
        if (next[key] !== prev[key] && fieldErrors[key]) {
          fieldErrors[key] = ''
        }
      }
    },
    { deep: false },
  )
</script>

<template>
  <!-- Spinner de carga inicial -->
  <div
    v-if="initLoading"
    class="flex min-h-screen items-center justify-center"
    aria-busy="true"
    aria-label="Cargando formulario…"
  >
    <svg
      class="h-10 w-10 animate-spin text-indigo-500"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      aria-hidden="true"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 12h-4z" />
    </svg>
  </div>

  <div v-else class="mx-auto max-w-3xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

    <!-- ── Breadcrumb ───────────────────────────────────────────────────────── -->
    <nav aria-label="Ruta de navegación">
      <ol class="flex items-center gap-2 text-sm">
        <li>
          <RouterLink
            to="/tramites"
            class="text-slate-500 transition-colors hover:text-indigo-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:rounded"
          >
            Trámites
          </RouterLink>
        </li>
        <li class="text-slate-300" aria-hidden="true">›</li>
        <li class="font-medium text-slate-900" aria-current="page">
          {{
            isEdit
              ? `Editar: ${tramitesStore.currentTramite?.nombre ?? '…'}`
              : 'Nuevo Trámite'
          }}
        </li>
      </ol>
    </nav>

    <!-- ── Card del formulario ──────────────────────────────────────────────── -->
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

      <!-- Header de la card -->
      <div class="border-b border-slate-100 px-6 py-5">
        <h1 class="text-lg font-semibold text-slate-900">
          {{ isEdit ? 'Editar trámite' : 'Nuevo trámite' }}
        </h1>
        <p class="mt-1 text-sm text-slate-500">
          {{
            isEdit
              ? 'Modifica los campos y guarda los cambios.'
              : 'Completa el formulario para registrar un nuevo trámite.'
          }}
        </p>
      </div>

      <form
        novalidate
        :aria-label="isEdit ? 'Formulario de edición de trámite' : 'Formulario de nuevo trámite'"
        class="space-y-6 px-6 py-6"
        @submit.prevent="onSubmit"
      >
        <!-- ── Fila: Código + Días hábiles ─────────────────────────────────── -->
        <div class="grid gap-5 sm:grid-cols-2">
          <BaseInput
            v-model="form.codigo"
            label="Código"
            placeholder="TRM-0001"
            :error="fieldErrors['codigo'] || undefined"
            :required="true"
            :disabled="loading"
            @blur="validateField('codigo')"
          />

          <BaseInput
            v-model="form.dias_habiles"
            label="Días hábiles"
            type="number"
            placeholder="15"
            :error="fieldErrors['dias_habiles'] || undefined"
            :required="true"
            :disabled="loading"
            @blur="validateField('dias_habiles')"
          />
        </div>

        <!-- ── Nombre ──────────────────────────────────────────────────────── -->
        <BaseInput
          v-model="form.nombre"
          label="Nombre"
          placeholder="Certificación de Antecedentes Penales"
          :error="fieldErrors['nombre'] || undefined"
          :required="true"
          :disabled="loading"
          @blur="validateField('nombre')"
        />

        <!-- ── Institución ─────────────────────────────────────────────────── -->
        <div class="space-y-1">
          <BaseSelect
            v-model="form.institucion_id"
            label="Institución responsable"
            :options="institucionOptions"
            placeholder="Selecciona una institución…"
            :error="fieldErrors['institucion_id'] || undefined"
            :required="true"
            :disabled="loading"
            @blur="validateField('institucion_id')"
          />
          <!-- Flujo premium: crear nueva institución sin perder el formulario -->
          <div class="flex justify-end">
            <RouterLink
              to="/instituciones/nueva?return=tramite-form"
              class="text-xs text-indigo-600 transition-colors hover:text-indigo-800 focus-visible:outline-none focus-visible:underline"
            >
              + Nueva institución
            </RouterLink>
          </div>
        </div>

        <!-- ── Descripción con contador de caracteres ──────────────────────── -->
        <div class="flex flex-col gap-1">
          <div class="flex items-baseline justify-between">
            <label
              for="textarea-descripcion"
              class="text-sm font-medium text-slate-700"
            >
              Descripción
              <span class="ml-1 text-xs font-normal text-slate-400">(opcional)</span>
            </label>
            <span
              class="text-xs tabular-nums transition-colors"
              :class="descLength > DESC_MAX ? 'text-red-500' : 'text-slate-400'"
              aria-live="polite"
              :aria-label="`${descLength} de ${DESC_MAX} caracteres`"
            >
              {{ descLength }}/{{ DESC_MAX }}
            </span>
          </div>

          <textarea
            id="textarea-descripcion"
            v-model="form.descripcion"
            rows="4"
            placeholder="Describe el propósito del trámite y los requisitos para el ciudadano…"
            :disabled="loading"
            :aria-invalid="!!fieldErrors['descripcion']"
            :aria-describedby="fieldErrors['descripcion'] ? 'error-descripcion' : undefined"
            class="w-full resize-none rounded-md border bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-1 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500"
            :class="{
              'border-red-400 focus-visible:ring-red-400': fieldErrors['descripcion'],
              'border-slate-300': !fieldErrors['descripcion'],
            }"
            @blur="validateField('descripcion')"
          />

          <p
            v-if="fieldErrors['descripcion']"
            id="error-descripcion"
            role="alert"
            class="text-xs text-red-600"
          >
            {{ fieldErrors['descripcion'] }}
          </p>
        </div>

        <!-- ── Toggle "Activo" (solo en modo editar) ───────────────────────── -->
        <div v-if="isEdit" class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-3">
          <div>
            <p class="text-sm font-medium text-slate-700">Estado del trámite</p>
            <p class="text-xs text-slate-500">
              {{ form.activo ? 'Activo — visible para los ciudadanos.' : 'Inactivo — no aparecerá en el listado.' }}
            </p>
          </div>

          <button
            type="button"
            role="switch"
            :aria-checked="form.activo"
            :aria-label="form.activo ? 'Desactivar trámite' : 'Activar trámite'"
            :disabled="loading"
            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
            :class="form.activo ? 'bg-indigo-600' : 'bg-slate-200'"
            @click="form.activo = !form.activo"
          >
            <span
              class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
              :class="form.activo ? 'translate-x-5' : 'translate-x-0'"
            />
          </button>
        </div>
      </form>

      <!-- ── Footer con botones ──────────────────────────────────────────────── -->
      <div class="flex items-center justify-between border-t border-slate-100 px-6 py-4">
        <!-- Indicador de cambios sin guardar -->
        <p
          v-if="isDirty"
          class="text-xs text-amber-600"
          aria-live="polite"
        >
          Hay cambios sin guardar
        </p>
        <span v-else />

        <div class="flex gap-3">
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
            {{ isEdit ? 'Guardar cambios' : 'Crear trámite' }}
          </BaseButton>
        </div>
      </div>
    </div>

    <!-- Atajo de teclado informativo -->
    <p class="text-center text-xs text-slate-400" aria-hidden="true">
      <kbd class="rounded border border-slate-200 px-1.5 py-0.5 font-mono text-slate-500">Ctrl</kbd>
      +
      <kbd class="rounded border border-slate-200 px-1.5 py-0.5 font-mono text-slate-500">S</kbd>
      para guardar ·
      <kbd class="rounded border border-slate-200 px-1.5 py-0.5 font-mono text-slate-500">Esc</kbd>
      para cancelar
    </p>
  </div>

  <!-- ── Modal: cambios sin guardar ─────────────────────────────────────────── -->
  <ConfirmDialog
    v-model:open="showLeaveConfirm"
    title="Cambios sin guardar"
    message="Tienes cambios que no se han guardado. ¿Deseas salir sin guardar?"
    confirm-text="Salir sin guardar"
    cancel-text="Seguir editando"
    variant="danger"
    @confirm="confirmLeave"
    @cancel="cancelLeave"
  />
</template>
