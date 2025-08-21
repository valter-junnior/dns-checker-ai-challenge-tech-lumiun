<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed, watch, nextTick, ref } from 'vue'

const props = defineProps({
  mode: { type: String, required: true }, 
  show: { type: Boolean, default: false },
  user: { type: Object, default: null },
})

const emit = defineEmits(['close', 'saved'])

const title = computed(() => props.mode === 'create' ? 'Novo usuário' : 'Editar usuário')
const isCreate = computed(() => props.mode === 'create')

const nameInput = ref(null)

const form = useForm({
  id: null,
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

watch(
  () => props.show,
  async (val) => {
    if (val) {
      form.clearErrors()
      form.reset()
      if (!isCreate.value && props.user) {
        form.id = props.user.id
        form.name = props.user.name
        form.email = props.user.email
      }
      await nextTick()
      nameInput.value?.focus()
    }
  }
)

function close() {
  emit('close')
}

function submit() {
  if (isCreate.value) {
    form.post(route('users.store'), {
      preserveScroll: true,
      onSuccess: () => {
        emit('saved')
        close()
        form.reset()
      },
    })
  } else {
    form.put(route('users.update', form.id), {
      preserveScroll: true,
      onSuccess: () => {
        emit('saved')
        close()
        form.reset()
      },
    })
  }
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/30" @click="close" />
    <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
      <h3 class="mb-4 text-lg font-semibold text-gray-900">{{ title }}</h3>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Nome</label>
          <input
            ref="nameInput"
            v-model="form.name"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
            required
          />
          <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Email</label>
          <input
            v-model="form.email"
            type="email"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
            required
          />
          <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">
              Senha <span v-if="!isCreate" class="text-gray-400 text-xs">(opcional)</span>
            </label>
            <input
              v-model="form.password"
              type="password"
              :required="isCreate"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
            />
            <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Confirmar senha</label>
            <input
              v-model="form.password_confirmation"
              type="password"
              :required="isCreate"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
            />
          </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-2">
          <button
            type="button"
            class="rounded-lg px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
            @click="close"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-60"
          >
            {{ form.processing ? 'Salvando...' : (isCreate ? 'Salvar' : 'Salvar alterações') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
