<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

type RiskKey = 'safe' | 'suspicious' | 'malicious';

interface Stats {
  total: number;
  byCategory: Record<RiskKey, number>;
  percent: Record<RiskKey, number>;
}

interface LastMaliciousItem {
  domain: string;
  client_ip: string;
  queried_at: string;
  risk: RiskKey;
}

const props = defineProps<{
  auth: any;
  stats: Stats;
  lastMalicious: LastMaliciousItem[];
}>();

const riskLabels: Record<RiskKey, string> = {
  safe: 'Seguro',
  suspicious: 'Suspeito',
  malicious: 'Malicioso',
};

function fmtDate(s: string) {
  const d = new Date(s);
  return isNaN(d.getTime()) ? s : d.toLocaleString();
}
</script>

<template>
  <Head title="Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Dashboard
      </h2>
    </template>

    <div class="py-12 bg-white">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Boas-vindas -->
        <div class="mb-6 overflow-hidden rounded-lg border border-gray-200">
          <div class="p-6 text-gray-800">
            <p>Bem-vindo, <strong>{{ props.auth.user.name }}</strong>!</p>
            <p class="text-sm text-gray-600">Seu e-mail: {{ props.auth.user.email }}</p>
          </div>
        </div>

        <!-- Cards de estatísticas -->
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <div class="rounded-lg border border-gray-200 bg-white p-5">
            <div class="text-sm text-gray-500">Total de Logs</div>
            <div class="mt-2 text-3xl font-semibold text-gray-800">{{ props.stats.total }}</div>
          </div>

          <div class="rounded-lg border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-500">Seguro</div>
              <span class="text-xs rounded-full bg-green-100 px-2 py-0.5 font-medium text-green-700">
                {{ props.stats.percent.safe }}%
              </span>
            </div>
            <div class="mt-2 text-2xl font-semibold text-gray-800">{{ props.stats.byCategory.safe }}</div>
          </div>

          <div class="rounded-lg border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-500">Suspeito</div>
              <span class="text-xs rounded-full bg-yellow-100 px-2 py-0.5 font-medium text-yellow-700">
                {{ props.stats.percent.suspicious }}%
              </span>
            </div>
            <div class="mt-2 text-2xl font-semibold text-gray-800">{{ props.stats.byCategory.suspicious }}</div>
          </div>

          <div class="rounded-lg border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-500">Malicioso</div>
              <span class="text-xs rounded-full bg-red-100 px-2 py-0.5 font-medium text-red-700">
                {{ props.stats.percent.malicious }}%
              </span>
            </div>
            <div class="mt-2 text-2xl font-semibold text-gray-800">{{ props.stats.byCategory.malicious }}</div>
          </div>
        </div>

        <!-- Tabela de últimos maliciosos -->
        <div class="mt-8 overflow-hidden rounded-lg border border-gray-200 bg-white">
          <div class="border-b border-gray-200 p-4">
            <h3 class="text-lg font-semibold text-gray-800">
              Últimos 10 domínios classificados como maliciosos
            </h3>
            <p class="text-xs text-gray-500">Ordenado por data (mais recente primeiro)</p>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Domínio</th>
                  <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Cliente IP</th>
                  <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Categoria</th>
                  <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Consultado em</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr v-if="!props.lastMalicious.length">
                  <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">
                    Nenhum domínio malicioso encontrado ainda.
                  </td>
                </tr>

                <tr
                  v-for="(item, idx) in props.lastMalicious"
                  :key="idx"
                  class="hover:bg-gray-50"
                >
                  <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ item.domain }}</td>
                  <td class="px-4 py-3 text-sm text-gray-700">{{ item.client_ip }}</td>
                  <td class="px-4 py-3 text-sm">
                    <span
                      class="rounded-full px-2 py-0.5 text-xs font-medium"
                      :class="{
                        'bg-green-100 text-green-700': item.risk === 'safe',
                        'bg-yellow-100 text-yellow-700': item.risk === 'suspicious',
                        'bg-red-100 text-red-700': item.risk === 'malicious',
                      }"
                    >
                      {{ riskLabels[item.risk] }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-700">{{ fmtDate(item.queried_at) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Rodapé -->
        <div class="mt-6 text-right text-xs text-gray-500">
          Estes números refletem apenas os seus logs.
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
