<script setup lang="ts">
  import BaseModal from './BaseModal.vue'
  import BaseButton from './BaseButton.vue'

  const props = withDefaults(
    defineProps<{
      open: boolean
      title: string
      message: string
      confirmText?: string
      cancelText?: string
      /** Variant del botón de confirmación. 'danger' para acciones destructivas. */
      variant?: 'primary' | 'danger'
    }>(),
    {
      confirmText: 'Confirmar',
      cancelText: 'Cancelar',
      variant: 'danger',
    },
  )

  const emit = defineEmits<{
    confirm: []
    cancel: []
    'update:open': [value: boolean]
  }>()

  const cancel = (): void => {
    emit('cancel')
    emit('update:open', false)
  }

  const confirm = (): void => {
    emit('confirm')
    emit('update:open', false)
  }
</script>

<template>
  <BaseModal :open="props.open" :title="title" size="sm" @update:open="cancel">
    <p class="text-sm text-slate-600">{{ message }}</p>

    <template #footer>
      <BaseButton variant="ghost" type="button" @click="cancel">
        {{ cancelText }}
      </BaseButton>
      <BaseButton :variant="variant" type="button" @click="confirm">
        {{ confirmText }}
      </BaseButton>
    </template>
  </BaseModal>
</template>
