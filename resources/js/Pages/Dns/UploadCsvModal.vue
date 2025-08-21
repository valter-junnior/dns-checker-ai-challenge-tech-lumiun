<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { ref, watch, nextTick, computed } from 'vue'

const props = defineProps<{
  show: boolean
  action: string
  title?: string
  accept?: string
  maxSizeMB?: number
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'uploaded'): void
}>()

const heading = computed(() => props.title ?? 'Enviar CSV para processamento')
const accept = computed(() => props.accept ?? '.csv,.txt')
const maxMB = computed(() => props.maxSizeMB ?? 20)

const dropActive = ref(false)
const inputEl = ref<HTMLInputElement | null>(null)
const modalEl = ref<HTMLElement | null>(null)
const progress = ref<number | null>(null)

const form = useForm<{ csv: File | null }>({ csv: null })

watch(
  () => props.show,
  async (v) => {
    if (v) {
      form.clearErrors()
      form.reset()
      progress.value = null
      await nextTick()
      modalEl.value?.focus()
    }
  }
)

function chooseFile() {
  inputEl.value?.click()
}

function setFile(file: File) {
  form.csv = file
}

function onFileChange(e: Event) {
  const t = e.target as HTMLInputElement
  if (t.files && t.files[0]) setFile(t.files[0])
}

function onDragOver(e: DragEvent) {
  e.preventDefault()
  dropActive.value = true
}
function onDragLeave(e: DragEvent) {
  e.preventDefault()
  dropActive.value = false
}
function onDrop(e: DragEvent) {
  e.preventDefault()
  dropActive.value = false
  const f = e.dataTransfer?.files?.[0]
  if (f) setFile(f)
}

function close() {
  if (form.processing) return
  emit('close')
}

function submit() {
  if (!form.csv) {
    form.setError('csv', 'Selecione um arquivo primeiro.')
    return
  }
  form.post(props.action, {
    forceFormData: true,
    preserveScroll: true,
    onStart: () => { progress.value = 0 },
    onProgress: (p) => { progress.value = p?.percentage ?? null },
    onSuccess: () => {
      emit('uploaded')
      emit('close')
    },
    onFinish: () => {  },
  })
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center" @keydown.esc="close">
    <div class="absolute inset-0 bg-black/30" @click="close" aria-hidden="true" />
    <div
      ref="modalEl"
      class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl outline-none"
      role="dialog" aria-modal="true" :aria-label="heading" tabindex="0"
    >
      <h3 class="mb-2 text-lg font-semibold text-gray-900">{{ heading }}</h3>
      <p class="mb-4 text-sm text-gray-600">
        Formatos aceitos: CSV/TXT. Tamanho máximo: {{ maxMB }}MB.
      </p>

      <!-- Dropzone -->
      <div
        class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed px-4 py-8 text-center transition"
        :class="dropActive ? 'border-indigo-500 bg-indigo-50/50' : 'border-gray-300 bg-gray-50/50'"
        @dragover="onDragOver" @dragleave="onDragLeave" @drop="onDrop"
      >
        <input ref="inputEl" type="file" class="hidden" :accept="accept" @change="onFileChange" />
        <div class="text-sm text-gray-700">
          <strong class="text-indigo-700 cursor-pointer" @click="chooseFile">Clique para selecionar</strong>
          ou arraste o arquivo para cá
        </div>
        <div v-if="form.csv" class="mt-3 text-xs text-gray-600">
          Selecionado: <span class="font-medium">{{ form.csv.name }}</span>
        </div>
        <div v-if="form.errors.csv" class="mt-2 text-sm text-rose-600">
          {{ form.errors.csv }}
        </div>
      </div>

      <!-- Barra de progresso -->
      <div v-if="progress !== null" class="mt-4">
        <div class="h-2 w-full rounded-full bg-gray-200 overflow-hidden">
          <div class="h-2 bg-indigo-600 transition-all" :style="{ width: `${progress}%` }"></div>
        </div>
        <div class="mt-1 text-right text-xs text-gray-500">{{ progress }}%</div>
      </div>

      <!-- Ações -->
      <div class="mt-6 flex items-center justify-end gap-2">
        <button type="button" class="rounded-lg px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" @click="close" :disabled="form.processing">
          Cancelar
        </button>
        <button
          type="button"
          class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-60"
          @click="submit"
          :disabled="form.processing"
          :aria-busy="form.processing"
        >
          {{ form.processing ? 'Enviando...' : 'Enviar' }}
        </button>
      </div>
    </div>
  </div>
</template>
