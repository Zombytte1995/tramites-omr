<script setup lang="ts">
  import { computed, reactive, ref } from 'vue'
  import { PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline'
  import { useInstitucionesStore } from '@/stores/instituciones'
  import { useToast } from '@/composables/useToast'
  import { useAsync } from '@/composables/useAsync'
  import { ValidationError } from '@/api/errors'
  import { TipoInstitucion } from '@/types/institucion'
  import BaseButton from '@/components/ui/BaseButton.vue'
  import BaseModal from '@/components/ui/BaseModal.vue'
  import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'
  import Skeleton from '@/components/ui/Skeleton.vue'
  import type { SelectOption } from '@/components/ui/BaseSelect.vue'
  import type { Institucion } from '@/types'

  const store = useInstitucionesStore()
  const toast = useToast()

  // ── Carga inicial ─────────────────────────────────────────────────────────────
  const { loading: pageLoading } = useAsync(
    () => store.fetchAll(),
    { immediate: true },
  )

  // ── Opciones del select de tipo ───────────────────────────────────────────────
  const tipoOptions: SelectOption[] = [
    { value: TipoInstitucion.MINISTERIO, label: 'Ministerio' },
    { value: TipoInstitucion.ALCALDIA,   label: 'Alcaldía' },
    { value: TipoInstitucion.AUTONOMA,   label: 'Entidad Autónoma' },
  ]

  // ── Modal crear/editar ────────────────────────────────────────────────────────
  const modalOpen    = ref(false)
  const editTarget   = ref<Institucion | null>(null)
  const formErrors   = ref<Record<string, string>>({})
  const submitting   = ref(false)

  const form = reactive({ nombre: '', tipo: '' as string })

  const modalTitle = computed(() => editTarget.value ? 'Editar institución' : 'Nueva institución')
  const isEdit     = computed(() => editTarget.value !== null)

  function openCreate(): void {
    editTarget.value = null
    form.nombre = ''
    form.tipo   = ''
    formErrors.value = {}
    modalOpen.value = true
  }

  function openEdit(inst: Institucion): void {
    editTarget.value = inst
    form.nombre = inst.nombre
    form.tipo   = inst.tipo.valor
    formErrors.value = {}
    modalOpen.value = true
  }

  function closeModal(): void {
    modalOpen.value = false
  }

  async function submitForm(): Promise<void> {
    formErrors.value = {}
    if (!form.nombre.trim()) {
      formErrors.value['nombre'] = 'El nombre es obligatorio.'
      return
    }
    if (!form.tipo) {
      formErrors.value['tipo'] = 'El tipo es obligatorio.'
      return
    }

    submitting.value = true
    try {
      if (isEdit.value && editTarget.value) {
        const ok = await store.update(editTarget.value.id, {
          nombre: form.nombre.trim(),
          tipo:   form.tipo as typeof TipoInstitucion[keyof typeof TipoInstitucion],
        })
        if (ok) {
          toast.success(`Institución "${form.nombre}" actualizada.`)
          closeModal()
        } else if (store.error instanceof ValidationError) {
          formErrors.value = Object.fromEntries(
            Object.entries(store.error.errors).map(([k, v]) => [k, v[0]]),
          )
        } else {
          toast.error(store.error?.message ?? 'Error al actualizar.')
        }
      } else {
        const ok = await store.create({
          nombre: form.nombre.trim(),
          tipo:   form.tipo as typeof TipoInstitucion[keyof typeof TipoInstitucion],
        })
        if (ok) {
          toast.success(`Institución "${form.nombre}" creada correctamente.`)
          closeModal()
        } else if (store.error instanceof ValidationError) {
          formErrors.value = Object.fromEntries(
            Object.entries(store.error.errors).map(([k, v]) => [k, v[0]]),
          )
        } else {
          toast.error(store.error?.message ?? 'Error al crear.')
        }
      }
    } finally {
      submitting.value = false
    }
  }

  // ── Confirmar desactivación ───────────────────────────────────────────────────
  const confirmOpen      = ref(false)
  const deactivateTarget = ref<Institucion | null>(null)

  function openConfirm(inst: Institucion): void {
    deactivateTarget.value = inst
    confirmOpen.value = true
  }

  async function confirmarDesactivar(): Promise<void> {
    if (!deactivateTarget.value) return
    const { id, nombre } = deactivateTarget.value

    const ok = await store.deactivate(id)
    if (ok) {
      toast.success(`Institución "${nombre}" desactivada.`)
    } else {
      toast.error(store.error?.message ?? 'No se pudo desactivar.')
    }
    deactivateTarget.value = null
  }

  // ── Etiqueta legible del tipo ─────────────────────────────────────────────────
  function tipoLabel(valor: string): string {
    return tipoOptions.find((o) => o.value === valor)?.label ?? valor
  }
</script>

<template>
  <div class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Instituciones</h1>
        <p class="mt-1 text-sm text-slate-500">
          {{ store.instituciones.length }} institución{{ store.instituciones.length !== 1 ? 'es' : '' }} activa{{ store.instituciones.length !== 1 ? 's' : '' }}
        </p>
      </div>
      <BaseButton variant="primary" size="md" @click="openCreate">
        <template #icon-left>
          <span class="text-base font-bold leading-none" aria-hidden="true">+</span>
        </template>
        Nueva institución
      </BaseButton>
    </div>

    <!-- Skeleton -->
    <div v-if="pageLoading" class="space-y-3">
      <Skeleton v-for="i in 5" :key="i" class="h-16 rounded-xl" />
    </div>

    <!-- Tabla -->
    <div
      v-else-if="store.instituciones.length"
      class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
    >
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
            <th class="px-4 py-3">Nombre</th>
            <th class="px-4 py-3">Tipo</th>
            <th class="px-4 py-3">Estado</th>
            <th class="px-4 py-3 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr
            v-for="inst in store.instituciones"
            :key="inst.id"
            class="group transition-colors hover:bg-slate-50"
          >
            <td class="px-4 py-3 font-medium text-slate-800">{{ inst.nombre }}</td>
            <td class="px-4 py-3">
              <span class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 ring-1 ring-indigo-100">
                {{ tipoLabel(inst.tipo.valor) }}
              </span>
            </td>
            <td class="px-4 py-3">
              <span
                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1"
                :class="inst.activo
                  ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                  : 'bg-slate-100 text-slate-500 ring-slate-200'"
              >
                {{ inst.activo ? 'Activa' : 'Inactiva' }}
              </span>
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center justify-end gap-1">
                <!-- Editar -->
                <button
                  type="button"
                  class="rounded-md p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-indigo-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                  :title="`Editar ${inst.nombre}`"
                  @click="openEdit(inst)"
                >
                  <PencilSquareIcon class="h-4 w-4" aria-hidden="true" />
                  <span class="sr-only">Editar {{ inst.nombre }}</span>
                </button>

                <!-- Desactivar -->
                <button
                  v-if="inst.activo"
                  type="button"
                  class="rounded-md p-1.5 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-400"
                  :title="`Desactivar ${inst.nombre}`"
                  @click="openConfirm(inst)"
                >
                  <TrashIcon class="h-4 w-4" aria-hidden="true" />
                  <span class="sr-only">Desactivar {{ inst.nombre }}</span>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Estado vacío -->
    <div
      v-else
      class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 py-16 text-center"
    >
      <p class="text-slate-500">No hay instituciones registradas.</p>
      <BaseButton variant="primary" size="sm" class="mt-4" @click="openCreate">
        Crear la primera
      </BaseButton>
    </div>

    <!-- ── Modal crear/editar ──────────────────────────────────────────────────── -->
    <BaseModal v-model:open="modalOpen" :title="modalTitle" size="md">
      <form class="space-y-4" @submit.prevent="submitForm">

        <!-- Nombre -->
        <div>
          <label for="inst-nombre" class="block text-sm font-medium text-slate-700">
            Nombre de la institución
          </label>
          <input
            id="inst-nombre"
            v-model="form.nombre"
            type="text"
            maxlength="150"
            placeholder="Ministerio de Hacienda"
            class="mt-1 block w-full rounded-md border px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
            :class="formErrors['nombre'] ? 'border-red-400' : 'border-slate-300'"
          />
          <p v-if="formErrors['nombre']" class="mt-1 text-xs text-red-600">
            {{ formErrors['nombre'] }}
          </p>
        </div>

        <!-- Tipo -->
        <div>
          <label for="inst-tipo" class="block text-sm font-medium text-slate-700">
            Tipo de institución
          </label>
          <select
            id="inst-tipo"
            v-model="form.tipo"
            class="mt-1 block w-full rounded-md border px-3 py-2 text-sm text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
            :class="formErrors['tipo'] ? 'border-red-400' : 'border-slate-300'"
          >
            <option value="" disabled>Selecciona el tipo…</option>
            <option v-for="opt in tipoOptions" :key="String(opt.value)" :value="opt.value">
              {{ opt.label }}
            </option>
          </select>
          <p v-if="formErrors['tipo']" class="mt-1 text-xs text-red-600">
            {{ formErrors['tipo'] }}
          </p>
        </div>

        <!-- Acciones -->
        <div class="flex justify-end gap-3 pt-2">
          <BaseButton type="button" variant="secondary" size="md" @click="closeModal">
            Cancelar
          </BaseButton>
          <BaseButton type="submit" variant="primary" size="md" :disabled="submitting">
            {{ submitting ? 'Guardando…' : isEdit ? 'Guardar cambios' : 'Crear institución' }}
          </BaseButton>
        </div>
      </form>
    </BaseModal>

    <!-- ── Confirmar desactivación ─────────────────────────────────────────────── -->
    <ConfirmDialog
      v-model:open="confirmOpen"
      title="Desactivar institución"
      :message="`¿Deseas desactivar &quot;${deactivateTarget?.nombre}&quot;? Solo puedes desactivarla si no tiene trámites activos.`"
      confirm-text="Sí, desactivar"
      cancel-text="Cancelar"
      variant="danger"
      @confirm="confirmarDesactivar"
      @cancel="deactivateTarget = null"
    />
  </div>
</template>
