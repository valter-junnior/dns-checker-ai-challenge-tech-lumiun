<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { computed, watch, nextTick, ref } from 'vue'

const props = defineProps<{
  show: boolean
  user: { id: number|string; name: string; email: string } | null
  destroyTemplate?: string
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'deleted'): void
}>()

const title = computed(() => props.user ? `Remover “${props.user.name}”` : 'Remover usuário')
const modalEl = ref<HTMLElement | null>(null)
const confirming = ref(false)

watch(
  () => props.show,
  async (v) => {
    if (v) {
      confirming.value = false
      await nextTick()
      modalEl.value?.focus()
    }
  }
)

function close() {
  if (confirming.value) return
  emit('close')
}

function makeDestroyUrl(id: string | number) {
  if (props.destroyTemplate) {
    return props.destroyTemplate.replace(':id', String(id))
  }
  return `/users/${id}`
}

function confirmDelete() {
  if (!props.user) return
  confirming.value = true

  router.delete(makeDestroyUrl(props.user.id), {
    preserveScroll: true,
    onSuccess: () => {
      emit('deleted')
      emit('close')
    },
    onFinish: () => {
      confirming.value = false
    },
  })
}
</script>

<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center"
    @keydown.esc="close"
  >
    <div class="absolute inset-0 bg-black/30" @click="close" aria-hidden="true" />
    <div
      ref="modalEl"
      class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl outline-none"
      role="dialog" aria-modal="true" :aria-label="title" tabindex="0"
    >
      <h3 class="mb-2 text-lg font-semibold text-gray-900">{{ title }}</h3>
      <p class="mb-4 text-sm text-gray-600">
        Esta ação é permanente e não pode ser desfeita.
        Tem certeza que deseja remover este usuário?
      </p>

      <div class="mt-6 flex items-center justify-end gap-2">
        <button
          type="button"
          class="rounded-lg px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
          @click="close"
          :disabled="confirming"
        >
          Cancelar
        </button>
        <button
          type="button"
          class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 disabled:opacity-60"
          @click="confirmDelete"
          :disabled="confirming"
          :aria-busy="confirming"
        >
          {{ confirming ? 'Removendo...' : 'Remover' }}
        </button>
      </div>
    </div>
  </div>
</template>
