<script setup lang="ts">
  import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
  } from '@headlessui/vue'
  import { XMarkIcon } from '@heroicons/vue/24/outline'

  type ModalSize = 'sm' | 'md' | 'lg' | 'xl'

  const props = withDefaults(
    defineProps<{
      /** Controla la visibilidad. Usar con v-model:open. */
      open: boolean
      title: string
      size?: ModalSize
      /** Callback opcional antes de cerrar (Escape / backdrop). Retorna false para cancelar el cierre. */
      onBeforeClose?: () => boolean
    }>(),
    { size: 'md' },
  )

  const emit = defineEmits<{
    'update:open': [value: boolean]
  }>()

  const sizeClasses: Record<ModalSize, string> = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
  }

  const close = (): void => {
    if (props.onBeforeClose && props.onBeforeClose() === false) return
    emit('update:open', false)
  }
</script>

<template>
  <TransitionRoot appear :show="props.open" as="template">
    <Dialog as="div" class="relative z-50" @close="close">

      <!-- Backdrop -->
      <TransitionChild
        as="template"
        enter="duration-200 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-150 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" aria-hidden="true" />
      </TransitionChild>

      <!-- Contenedor centrado -->
      <div class="fixed inset-0 flex items-center justify-center p-4">
        <TransitionChild
          as="template"
          enter="duration-200 ease-out"
          enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100"
          leave="duration-150 ease-in"
          leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95"
        >
          <DialogPanel
            class="w-full rounded-xl bg-white shadow-xl ring-1 ring-slate-200"
            :class="sizeClasses[size]"
          >
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
              <DialogTitle
                as="h3"
                class="text-base font-semibold text-slate-900"
              >
                {{ title }}
              </DialogTitle>

              <button
                type="button"
                class="rounded-md p-1 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                aria-label="Cerrar modal"
                @click="close"
              >
                <XMarkIcon class="h-5 w-5" aria-hidden="true" />
              </button>
            </div>

            <!-- Body (slot por defecto) -->
            <div class="px-6 py-5">
              <slot />
            </div>

            <!-- Footer (slot opcional) -->
            <div
              v-if="$slots.footer"
              class="flex justify-end gap-3 border-t border-slate-100 px-6 py-4"
            >
              <slot name="footer" />
            </div>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>
