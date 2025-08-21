<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormUserModal from '@/Pages/Users/FormUserModal.vue'
import DeleteUserModal from '@/Pages/Users/DeleteUserModal.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  users: Object,
  filters: Object,
})

const modalShow = ref(false)
const modalMode = ref('create')
const currentUser = ref(null)

function openCreate() {
  modalMode.value = 'create'
  currentUser.value = null
  modalShow.value = true
}

function openEdit(user) {
  modalMode.value = 'edit'
  currentUser.value = user
  modalShow.value = true
}

function closeModal() {
  modalShow.value = false
}

const deleteShow = ref(false)
const userToDelete = ref(null)

function openDelete(user) {
  userToDelete.value = user
  deleteShow.value = true
}
function closeDelete() {
  deleteShow.value = false
  userToDelete.value = null
}

function reloadUsers() {
  router.reload({ only: ['users'], preserveScroll: true })
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Usuários" />

    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800">Usuários</h2>
        <button
          class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
          @click="openCreate"
        >
          + Novo
        </button>
      </div>
    </template>

    <div class="py-10">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- filtro -->
        <form method="GET" class="flex w-full sm:max-w-md items-center gap-2">
          <input
            type="text" name="search" :value="filters?.search ?? ''"
            placeholder="Buscar por nome ou e-mail…"
            class="w-full rounded-xl border border-gray-300 bg-white/60 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          />
          <button
            class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700"
          >Filtrar</button>
        </form>

        <!-- card -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-600">
                <tr>
                  <th class="px-5 py-3">ID</th>
                  <th class="px-5 py-3">Nome</th>
                  <th class="px-5 py-3">Email</th>
                  <th class="px-5 py-3 text-right">Ações</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="!users?.data?.length">
                  <td colspan="4" class="px-5 py-10 text-center text-gray-500">Nenhum usuário encontrado.</td>
                </tr>
                <tr
                  v-for="user in users.data"
                  :key="user.id"
                  class="odd:bg-white even:bg-gray-50 hover:bg-indigo-50/40 transition-colors"
                >
                  <td class="px-5 py-3 font-mono text-gray-700">
                    <span class="inline-flex rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                      {{ user.id }}
                    </span>
                  </td>
                  <td class="px-5 py-3 font-medium text-gray-900">{{ user.name }}</td>
                  <td class="px-5 py-3 text-gray-700">{{ user.email }}</td>
                  <td class="px-5 py-3">
                    <div class="flex justify-end gap-2">
                      <button
                        class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                        @click="openEdit(user)"
                      >Editar</button>
                      <button
                        class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-red-700"
                        @click="openDelete(user)"
                      >Remover</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- paginação -->
          <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 px-4 py-3">
            <div class="text-xs text-gray-500">
              Página <strong class="text-gray-800">{{ users?.meta?.current_page }}</strong>
              de <strong class="text-gray-800">{{ users?.meta?.last_page }}</strong>
            </div>
            <div class="flex flex-wrap gap-2">
              <Link v-for="link in users.links" :key="link.label" :href="link.url || '#'"
                    preserve-scroll preserve-state
                    :class="[
                      'px-3 py-1.5 rounded-lg border text-sm transition',
                      link.active ? 'border-indigo-600 bg-indigo-600 text-white'
                                  : link.url ? 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'
                                             : 'border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed'
                    ]"
                    v-html="link.label" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <FormUserModal
      :show="modalShow"
      :mode="modalMode"
      :user="currentUser"
      @close="closeModal"
      @saved="reloadUsers"
    />

    <DeleteUserModal
      :show="deleteShow"
      :user="userToDelete"
      @close="closeDelete"
      @deleted="reloadUsers"
    />
  </AuthenticatedLayout>
</template>
