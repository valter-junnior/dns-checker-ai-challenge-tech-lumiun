<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UploadCsvModal from '@/Pages/Dns/UploadCsvModal.vue';
import { echo } from '@/echo';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';

type Item = {
    id: string;
    user_id: number | string;
    domain: string;
    client_ip: string;
    risk: 'safe' | 'suspicious' | 'malicious' | null;
    ai_score: number | null;
    ai_model: string | null;
    status: 'pending' | 'enriched' | 'classified' | 'error';
    queried_at: string;
    user?: { id: number | string; name: string };
};

const props = defineProps<{
    items: {
        data: Item[];
        links: any[];
        meta: { current_page: number; last_page: number };
    };
    filters: Partial<{
        search: string;
        risk: string;
        status: string;
        user_id: string;
        from: string;
        to: string;
    }>;
    enums: { risk: string[]; status: string[] };
}>();

const search = ref(props.filters?.search ?? '');
const risk = ref(props.filters?.risk ?? '');
const status = ref(props.filters?.status ?? '');
const userId = ref(props.filters?.user_id ?? '');
const from = ref(props.filters?.from ?? '');
const to = ref(props.filters?.to ?? '');

const page = usePage();
const currentUserId = (page.props as any)?.auth?.user?.id;

let channel: any = null;
onMounted(() => {
    if (!currentUserId) return;
    channel = echo
        .channel(`dns.${currentUserId}`)
        .listen('.DnsEvent', (payload: any) => {
            console.log(payload);
            const list = props.items?.data;
            if (Array.isArray(list)) {
                const i = list.findIndex((x) => x.id === payload.id);
                if (i >= 0) list[i] = { ...list[i], ...payload };
                else reloadList();
            } else {
                reloadList();
            }
        });
});

onBeforeUnmount(() => {
    if (channel) {
        channel.stopListening('.DnsEvent');
        echo.leave(`private-dns.${currentUserId}`);
    }
});

function applyFilters() {
    router.get(
        route('dns.index'),
        {
            search: search.value || undefined,
            risk: risk.value || undefined,
            status: status.value || undefined,
            user_id: userId.value || undefined,
            from: from.value || undefined,
            to: to.value || undefined,
        },
        { preserveState: true, preserveScroll: true },
    );
}

function resetFilters() {
    search.value = '';
    risk.value = '';
    status.value = '';
    userId.value = '';
    from.value = '';
    to.value = '';
    applyFilters();
}

function riskClass(r: Item['risk']) {
    if (r === 'malicious') return 'bg-red-100 text-red-800 border-red-200';
    if (r === 'suspicious')
        return 'bg-amber-100 text-amber-800 border-amber-200';
    if (r === 'safe')
        return 'bg-emerald-100 text-emerald-800 border-emerald-200';
    return 'bg-gray-100 text-gray-700 border-gray-200';
}
function statusClass(s: Item['status']) {
    const map: Record<string, string> = {
        pending: 'bg-gray-100 text-gray-700 border-gray-200',
        enriched: 'bg-blue-100 text-blue-800 border-blue-200',
        classified: 'bg-indigo-100 text-indigo-800 border-indigo-200',
        error: 'bg-rose-100 text-rose-800 border-rose-200',
    };
    return map[s] ?? 'bg-gray-100 text-gray-700 border-gray-200';
}

const uploadShow = ref(false);
function openUpload() {
    uploadShow.value = true;
}
function closeUpload() {
    uploadShow.value = false;
}
function reloadList() {
    router.reload({ only: ['items'], preserveScroll: true });
}

function translateStatus(s: Item['status']): string {
    const map: Record<string, string> = {
        pending: 'Pendente',
        enriched: 'Enriquecido',
        classified: 'Classificado',
        error: 'Erro',
    };
    return map[s] ?? s;
}

function translateRisk(r: Item['risk']): string {
    const map: Record<string, string> = {
        safe: 'Seguro',
        suspicious: 'Suspeito',
        malicious: 'Malicioso',
    };
    return map[r] ?? r ?? '—';
}

</script>

<template>
    <AuthenticatedLayout>
        <Head title="Consultas de Domínio" />

        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">DNS</h2>
                <button
                    class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    @click="openUpload"
                    type="button"
                >
                    + Upload CSV
                </button>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <form
                    class="grid grid-cols-1 gap-4 md:grid-cols-6"
                    @submit.prevent="applyFilters"
                >
                    <!-- Busca -->
                    <div class="md:col-span-2">
                        <label for="search" class="mb-1 block text-sm font-medium text-gray-700">
                            Buscar
                        </label>
                        <input
                            id="search"
                            v-model="search"
                            type="text"
                            placeholder="Domínio, IP, modelo…"
                            class="w-full rounded-xl border border-gray-300 bg-white/60 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>

                    <!-- Risco -->
                    <div>
                        <label for="risk" class="mb-1 block text-sm font-medium text-gray-700">
                            Risco
                        </label>
                        <select
                            id="risk"
                            v-model="risk"
                            class="w-full rounded-xl border border-gray-300 bg-white/60 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">Todos</option>
                            <option v-for="r in enums.risk" :key="r" :value="r">
                                <!-- tradução -->
                                {{ translateRisk(r) }}
                            </option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="mb-1 block text-sm font-medium text-gray-700">
                            Status
                        </label>
                        <select
                            id="status"
                            v-model="status"
                            class="w-full rounded-xl border border-gray-300 bg-white/60 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">Todos</option>
                            <option v-for="s in enums.status" :key="s" :value="s">
                                {{ translateStatus(s) }}
                            </option>
                        </select>
                    </div>

                    <!-- Data inicial -->
                    <div>
                        <label for="from" class="mb-1 block text-sm font-medium text-gray-700">
                            Data inicial
                        </label>
                        <input
                            id="from"
                            v-model="from"
                            type="date"
                            class="w-full rounded-xl border border-gray-300 bg-white/60 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>

                    <!-- Data final -->
                    <div>
                        <label for="to" class="mb-1 block text-sm font-medium text-gray-700">
                            Data final
                        </label>
                        <input
                            id="to"
                            v-model="to"
                            type="date"
                            class="w-full rounded-xl border border-gray-300 bg-white/60 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>

                    <!-- Botões -->
                    <div class="flex items-end gap-2 md:col-span-6">
                        <button
                            type="submit"
                            class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700"
                        >
                            Filtrar
                        </button>
                        <button
                            type="button"
                            @click="resetFilters"
                            class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 shadow-sm hover:bg-gray-50"
                        >
                            Limpar
                        </button>
                    </div>
                </form>


                <!-- card -->
                <div
                    class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm"
                >
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead
                                class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-600"
                            >
                                <tr>
                                    <th class="px-5 py-3">ID</th>
                                    <th class="px-5 py-3">Data/Hora</th>
                                    <th class="px-5 py-3">Domínio</th>
                                    <th class="px-5 py-3">IP</th>
                                    <th class="px-5 py-3">Risco</th>
                                    <th class="px-5 py-3">AI Score</th>
                                    <th class="px-5 py-3">Modelo</th>
                                    <th class="px-5 py-3">Status</th>
                                    <th class="px-5 py-3">Usuário</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!items?.data?.length">
                                    <td
                                        colspan="9"
                                        class="px-5 py-10 text-center text-gray-500"
                                    >
                                        Nenhum registro encontrado.
                                    </td>
                                </tr>
                                <tr
                                    v-for="row in items.data"
                                    :key="row.id"
                                    class="transition-colors odd:bg-white even:bg-gray-50 hover:bg-indigo-50/40"
                                >
                                    <td
                                        class="px-5 py-3 font-mono text-gray-700"
                                    >
                                        <span
                                            class="inline-flex rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700"
                                        >
                                            {{ row.id.slice(0, 8) }}…
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-gray-700">
                                        {{
                                            new Date(
                                                row.queried_at,
                                            ).toLocaleString()
                                        }}
                                    </td>
                                    <td
                                        class="px-5 py-3 font-medium text-gray-900"
                                    >
                                        {{ row.domain }}
                                    </td>
                                    <td class="px-5 py-3 text-gray-700">
                                        {{ row.client_ip }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <span
                                            class="inline-flex items-center gap-1 rounded-md border px-2 py-0.5 text-xs font-medium"
                                            :class="riskClass(row.risk)"
                                        >
                                            {{ translateRisk(row.risk) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-gray-700">
                                        <span v-if="row.ai_score !== null">{{
                                            Number(row.ai_score).toFixed(2)
                                        }}</span>
                                        <span v-else>—</span>
                                    </td>
                                    <td class="px-5 py-3 text-gray-700">
                                        {{ row.ai_model ?? '—' }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <span
                                            class="inline-flex items-center gap-1 rounded-md border px-2 py-0.5 text-xs font-medium"
                                            :class="statusClass(row.status)"
                                        >
                                            {{ translateStatus(row.status) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-gray-700">
                                        <span v-if="row.user?.name">{{
                                            row.user.name
                                        }}</span>
                                        <span v-else>#{{ row.user_id }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- paginação -->
                    <div
                        class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 px-4 py-3"
                    >
                        <div class="text-xs text-gray-500">
                            Página
                            <strong class="text-gray-800">{{
                                items?.meta?.current_page
                            }}</strong>
                            de
                            <strong class="text-gray-800">{{
                                items?.meta?.last_page
                            }}</strong>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <template v-for="(link, i) in items.links" :key="i">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    preserve-scroll
                                    preserve-state
                                    :class="[
                                        'rounded-lg border px-3 py-1.5 text-sm transition',
                                        link.active
                                            ? 'border-indigo-600 bg-indigo-600 text-white'
                                            : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50',
                                    ]"
                                    v-html="link.label"
                                />
                                <span
                                    v-else
                                    class="cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-3 py-1.5 text-sm text-gray-400"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <UploadCsvModal
            :show="uploadShow"
            action="/upload"
            @close="closeUpload"
            @uploaded="reloadList"
        />
    </AuthenticatedLayout>
</template>
