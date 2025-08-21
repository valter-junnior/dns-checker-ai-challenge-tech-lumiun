<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
  canLogin?: boolean;
  canRegister?: boolean;
  laravelVersion: string;
  phpVersion: string;
}>();
</script>

<template>
  <Head title="Bem-vindo" />

  <div class="bg-gray-50 min-h-screen flex flex-col items-center justify-center text-gray-700">
    <!-- Logo / Header -->
    <header class="flex flex-col items-center gap-4">
      <svg
        class="h-16 w-16 text-red-600"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
        stroke-width="1.5"
        stroke="currentColor"
      >
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <h1 class="text-3xl font-bold">🔎 Classificação de Logs DNS</h1>
      <p class="text-center max-w-lg text-gray-500">
        Plataforma para análise de consultas DNS com auxílio de Inteligência Artificial.<br />
        Classifique domínios como <span class="text-green-600">Seguro</span>, 
        <span class="text-yellow-500">Suspeito</span> ou 
        <span class="text-red-600">Malicioso</span>.
      </p>
    </header>

    <!-- Links de Acesso -->
    <nav class="flex gap-4 mt-10">
      <Link
        v-if="$page.props.auth.user"
        :href="route('dashboard.index')"
        class="px-5 py-2 rounded-lg bg-red-600 text-white font-medium hover:bg-red-700 transition"
      >
        Ir para o Dashboard
      </Link>

      <template v-else>
        <Link
          v-if="canLogin"
          :href="route('login')"
          class="px-5 py-2 rounded-lg bg-red-600 text-white font-medium hover:bg-red-700 transition"
        >
          Entrar
        </Link>

        <Link
          v-if="canRegister"
          :href="route('register')"
          class="px-5 py-2 rounded-lg border border-red-600 text-red-600 font-medium hover:bg-red-600 hover:text-white transition"
        >
          Criar Conta
        </Link>
      </template>
    </nav>

    <!-- Cards de Recursos -->
    <main class="grid gap-6 mt-16 max-w-4xl w-full px-6 lg:grid-cols-3">
      <div class="rounded-lg bg-white p-6 shadow hover:shadow-lg transition">
        <h2 class="text-lg font-semibold mb-2">📂 Upload de Logs</h2>
        <p class="text-sm text-gray-600">
          Envie arquivos CSV contendo registros DNS para serem processados e armazenados.
        </p>
      </div>

      <div class="rounded-lg bg-white p-6 shadow hover:shadow-lg transition">
        <h2 class="text-lg font-semibold mb-2">🤖 Classificação com IA</h2>
        <p class="text-sm text-gray-600">
          Cada domínio é analisado por uma API de Inteligência Artificial para determinar o nível de risco.
        </p>
      </div>

      <div class="rounded-lg bg-white p-6 shadow hover:shadow-lg transition">
        <h2 class="text-lg font-semibold mb-2">📊 Dashboard</h2>
        <p class="text-sm text-gray-600">
          Acompanhe estatísticas, filtros de consultas e os últimos domínios identificados como maliciosos.
        </p>
      </div>
    </main>

    <!-- Rodapé -->
    <footer class="mt-16 text-sm text-gray-500">
      Laravel v{{ laravelVersion }} · PHP v{{ phpVersion }}
    </footer>
  </div>
</template>
