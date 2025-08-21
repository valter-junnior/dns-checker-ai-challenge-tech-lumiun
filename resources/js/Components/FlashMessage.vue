<template>
  <div class="fixed top-4 right-4 space-y-2 z-50 pointer-events-none">
    <transition-group name="fade" tag="div">
      <div v-for="(msg, idx) in messages" :key="idx"
           class="px-4 py-2 rounded-lg shadow-lg text-white pointer-events-auto"
           :class="colorClass(msg.type)">
        {{ msg.text }}
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'

const messages = ref([])

function colorClass(type) {
  switch (type) {
    case 'success': return 'bg-green-600'
    case 'warning': return 'bg-yellow-500 text-black'
    case 'error':   return 'bg-red-600'
    default:        return 'bg-gray-600'
  }
}

function pushFrom(flash) {
  Object.entries(flash || {}).forEach(([type, text]) => {
    if (text) messages.value.push({ type, text })
  })
  if (messages.value.length) {
    setTimeout(() => { messages.value = [] }, 4000)
  }
}

const page = usePage()

// 1) Ao montar: pega do Inertia (SPA) e faz fallback pro window (primeiro load)
onMounted(() => {
  pushFrom(page.props.flash)
  if (!messages.value.length && window?.Laravel?.flash) {
    pushFrom(window.Laravel.flash)
  }
})

// 2) A cada navegação: Inertia atualiza props, então escutamos mudanças
watch(() => page.props.flash, (val) => {
  pushFrom(val)
})
</script>

<style>
.fade-enter-active, .fade-leave-active { transition: opacity .3s, transform .3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-10px); }
</style>
