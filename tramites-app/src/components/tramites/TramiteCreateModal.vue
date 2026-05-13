<script setup lang="ts">
  import { computed, reactive, ref, watch } from 'vue'
  import { useTramitesStore } from '@/stores/tramites'
  import { useInstitucionesStore } from '@/stores/instituciones'
  import { useToast } from '@/composables/useToast'
  import { ValidationError } from '@/api/errors'
  import BaseModal from '@/components/ui/BaseModal.vue'
  import BaseButton from '@/components/ui/BaseButton.vue'
  import BaseInput from '@/components/ui/BaseInput.vue'
  import BaseSelect from '@/components/ui/BaseSelect.vue'
  import type { SelectOption } from '@/components/ui/BaseSelect.vue'
  import type { TramiteFormData } from '@/types'

  // ── Props / Emits ─────────────────────────────────────────────────────────────
  const props = defineProps<{ open: boolean }>()
  const emit  = defineEmits<{
    'update:open': [value: boolean]
    created: []
  }>()

  const tramitesStore    = useTramitesStore()
  const institucionesStore = useInstitucionesStore()
  const toast            = useToast()

  // ── Select de instituciones ───────────────────────────────────────────────────
  const institucionOptions = computed((): SelectOption[] =>
    institucionesStore.instituciones.map((i) => ({ value: i.id, label: i.nombre })),
  )

  // ── Estado del formulario ─────────────────────────────────────────────────────
  const form = reactive({
    codigo:         '',
    nombre:         '',
    descripcion:    '',
    institucion_id: null as string | number | null,
    dias_habiles:   '',
  })

  const fieldErrors = reactive<Record<string, string>>({
    codigo: '', nombre: '', descripcion: '', institucion_id: '', dias_habiles: '',
  })

  const submitting = ref(false)
  const descLength = computed(() => form.descripcion.length)

  // Limpiar el formulario cada vez que el modal se abre
  watch(() => props.open, (val) => {
    if (val) resetForm()
  })

  function resetForm(): void {
    form.codigo         = ''
    form.nombre         = ''
    form.descripcion    = ''
    form.institucion_id = null
    form.dias_habiles   = ''
    Object.keys(fieldErrors).forEach((k) => { fieldErrors[k] = '' })
  }

  // ── Validación ────────────────────────────────────────────────────────────────
  function validateField(field: string): boolean {
    fieldErrors[field] = ''
    switch (field) {
      case 'codigo':
        if (!form.codigo.trim()) { fieldErrors['codigo'] = 'El código es obligatorio.'; return false }
        if (form.codigo.length > 20) { fieldErrors['codigo'] = 'Máximo 20 caracteres.'; return false }
        return true
      case 'nombre':
        if (!form.nombre.trim()) { fieldErrors['nombre'] = 'El nombre es obligatorio.'; return false }
        if (form.nombre.length > 200) { fieldErrors['nombre'] = 'Máximo 200 caracteres.'; return false }
        return true
      case 'descripcion':
        if (form.descripcion.length > 1000) { fieldErrors['descripcion'] = 'Máximo 1000 caracteres.'; return false }
        return true
      case 'institucion_id':
        if (!form.institucion_id) { fieldErrors['institucion_id'] = 'Debes seleccionar una institución.'; return false }
        return true
      case 'dias_habiles': {
        const n = Number(form.dias_habiles)
        if (!form.dias_habiles) { fieldErrors['dias_habiles'] = 'Los días hábiles son obligatorios.'; return false }
        if (!Number.isInteger(n) || n < 1) { fieldErrors['dias_habiles'] = 'Debe ser un entero mayor a 0.'; return false }
        if (n > 365) { fieldErrors['dias_habiles'] = 'No puede superar 365.'; return false }
        return true
      }
      default: return true
    }
  }

  function validateAll(): boolean {
    return ['codigo', 'nombre', 'descripcion', 'institucion_id', 'dias_habiles']
      .map((f) => validateField(f))
      .every(Boolean)
  }

  // ── Envío ─────────────────────────────────────────────────────────────────────
  async function onSubmit(): Promise<void> {
    if (!validateAll()) return
    submitting.value = true

    const payload: TramiteFormData = {
      codigo:         form.codigo.trim(),
      nombre:         form.nombre.trim(),
      descripcion:    form.descripcion.trim(),
      institucion_id: Number(form.institucion_id),
      dias_habiles:   Number(form.dias_habiles),
    }

    try {
      const result = await tramitesStore.create(payload)
      if (result !== null) {
        toast.success(`Trámite "${result.nombre}" creado correctamente.`)
        emit('update:open', false)
        emit('created')
      } else if (tramitesStore.error instanceof ValidationError) {
        Object.entries(tramitesStore.error.errors).forEach(([field, msgs]) => {
          if (field in fieldErrors) fieldErrors[field] = msgs[0] ?? ''
        })
      } else {
        toast.error(tramitesStore.error?.message ?? 'Error al crear el trámite.')
      }
    } finally {
      submitting.value = false
    }
  }

  // Limpiar error de servidor cuando el usuario edita el campo
  watch(
    () => ({ ...form }),
    (next, prev) => {
      ;(Object.keys(next) as (keyof typeof form)[]).forEach((k) => {
        if (next[k] !== prev[k] && fieldErrors[k]) fieldErrors[k] = ''
      })
    },
  )
</script>

<template>
  <BaseModal
    :open="open"
    title="Nuevo trámite"
    size="lg"
    @update:open="$emit('update:open', $event)"
  >
    <form novalidate class="space-y-5" @submit.prevent="onSubmit">

      <!-- Código + Días hábiles -->
      <div class="grid gap-4 sm:grid-cols-2">
        <BaseInput
          v-model="form.codigo"
          label="Código"
          placeholder="TRM-0011"
          :error="fieldErrors['codigo'] || undefined"
          :required="true"
          :disabled="submitting"
          @blur="validateField('codigo')"
        />
        <BaseInput
          v-model="form.dias_habiles"
          label="Días hábiles"
          type="number"
          placeholder="15"
          :error="fieldErrors['dias_habiles'] || undefined"
          :required="true"
          :disabled="submitting"
          @blur="validateField('dias_habiles')"
        />
      </div>

      <!-- Nombre -->
      <BaseInput
        v-model="form.nombre"
        label="Nombre del trámite"
        placeholder="Certificación de Antecedentes Penales"
        :error="fieldErrors['nombre'] || undefined"
        :required="true"
        :disabled="submitting"
        @blur="validateField('nombre')"
      />

      <!-- Institución -->
      <BaseSelect
        v-model="form.institucion_id"
        label="Institución responsable"
        :options="institucionOptions"
        placeholder="Selecciona una institución…"
        :error="fieldErrors['institucion_id'] || undefined"
        :required="true"
        :disabled="submitting"
        @blur="validateField('institucion_id')"
      />

      <!-- Descripción -->
      <div class="flex flex-col gap-1">
        <div class="flex items-baseline justify-between">
          <label for="modal-descripcion" class="text-sm font-medium text-slate-700">
            Descripción
            <span class="ml-1 text-xs font-normal text-slate-400">(opcional)</span>
          </label>
          <span
            class="text-xs tabular-nums transition-colors"
            :class="descLength > 1000 ? 'text-red-500' : 'text-slate-400'"
          >
            {{ descLength }}/1000
          </span>
        </div>
        <textarea
          id="modal-descripcion"
          v-model="form.descripcion"
          rows="3"
          placeholder="Describe el propósito del trámite y los requisitos para el ciudadano…"
          :disabled="submitting"
          class="w-full resize-none rounded-md border px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-1 disabled:cursor-not-allowed disabled:bg-slate-50"
          :class="fieldErrors['descripcion'] ? 'border-red-400' : 'border-slate-300'"
          @blur="validateField('descripcion')"
        />
        <p v-if="fieldErrors['descripcion']" class="text-xs text-red-600">
          {{ fieldErrors['descripcion'] }}
        </p>
      </div>

      <!-- Acciones -->
      <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
        <BaseButton
          type="button"
          variant="ghost"
          size="md"
          :disabled="submitting"
          @click="$emit('update:open', false)"
        >
          Cancelar
        </BaseButton>
        <BaseButton
          type="submit"
          variant="primary"
          size="md"
          :loading="submitting"
        >
          Crear trámite
        </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>
