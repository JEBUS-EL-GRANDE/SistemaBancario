<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowDownLeft,
    ArrowUpRight,
    CreditCard,
    Eye,
    Plus,
    Search,
    Wallet,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface Cliente {
    id: number;
    nombres: string;
    apellidos: string;
    numero_documento: string;
}

interface Cuenta {
    id: number;
    numero_cuenta: string;
    tipo: string;
    moneda: string;
    saldo: number;
    limite_retiro_diario: number;
    estado: string;
    cliente: Cliente;
    created_at: string;
}

interface CuentasPagination {
    data: Cuenta[];
    current_page: number;
    last_page: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

const props = defineProps<{
    cuentas: CuentasPagination;
    filters: { search?: string; tipo?: string; estado?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Cuentas Bancarias', href: '/cuentas' },
];

const search = ref(props.filters.search || '');
const tipo = ref(props.filters.tipo || '');
const estado = ref(props.filters.estado || '');

const money = (n: number) => `Bs. ${Number(n || 0).toLocaleString('es-BO', { minimumFractionDigits: 2 })}`;

let timeout: number | null = null;
const filtrar = () => {
    if (timeout) clearTimeout(timeout);
    timeout = window.setTimeout(() => {
        router.get('/cuentas', {
            search: search.value,
            tipo: tipo.value,
            estado: estado.value,
        }, { preserveState: true, replace: true });
    }, 350);
};

watch([search, tipo, estado], filtrar);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Cuentas Bancarias - Banco Continental" />

        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                        <Wallet class="h-7 w-7 text-emerald-400" />
                        Cuentas Bancarias
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">
                        Apertura, parametrización y administración de cuentas de ahorro y corrientes (RF-006, RF-008).
                    </p>
                </div>
                <Link
                    href="/cuentas/create"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 hover:from-emerald-500 hover:to-teal-500 transition-all cursor-pointer"
                >
                    <Plus class="h-4 w-4" />
                    Abrir Nueva Cuenta
                </Link>
            </div>

            <!-- Filtros -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 rounded-2xl bg-slate-900/60 p-4 border border-slate-800">
                <div class="relative">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Buscar por N° cuenta o cliente..."
                        class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 pl-9 pr-4 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                    />
                </div>
                <div>
                    <select
                        v-model="tipo"
                        class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2 text-sm text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                    >
                        <option value="">Todos los tipos</option>
                        <option value="ahorro">Caja de Ahorro</option>
                        <option value="corriente">Cuenta Corriente</option>
                    </select>
                </div>
                <div>
                    <select
                        v-model="estado"
                        class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2 text-sm text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                    >
                        <option value="">Todos los estados</option>
                        <option value="activa">Activa</option>
                        <option value="bloqueada">Bloqueada</option>
                        <option value="inactiva">Inactiva</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>
            </div>

            <!-- Tabla de Cuentas -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/50 backdrop-blur-sm overflow-hidden shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-300">
                        <thead class="bg-slate-950/60 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-800">
                            <tr>
                                <th class="px-6 py-4 font-semibold">N° Cuenta</th>
                                <th class="px-6 py-4 font-semibold">Titular</th>
                                <th class="px-6 py-4 font-semibold">Tipo</th>
                                <th class="px-6 py-4 font-semibold">Saldo Disponible</th>
                                <th class="px-6 py-4 font-semibold">Límite Diario</th>
                                <th class="px-6 py-4 font-semibold">Estado</th>
                                <th class="px-6 py-4 font-semibold text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80">
                            <tr
                                v-for="c in cuentas.data"
                                :key="c.id"
                                class="hover:bg-slate-800/40 transition-colors"
                            >
                                <td class="px-6 py-4 font-mono font-bold text-white tracking-wide">
                                    {{ c.numero_cuenta }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-200">{{ c.cliente.nombres }} {{ c.cliente.apellidos }}</div>
                                    <div class="text-xs text-slate-500 font-mono">Doc: {{ c.cliente.numero_documento }}</div>
                                </td>
                                <td class="px-6 py-4 capitalize">
                                    <span class="rounded bg-slate-800 px-2 py-0.5 text-xs text-slate-300 font-medium">
                                        {{ c.tipo }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-emerald-400">
                                    {{ money(c.saldo) }}
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-400">
                                    {{ money(c.limite_retiro_diario) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="{
                                            'bg-emerald-500/20 text-emerald-300 border-emerald-500/30': c.estado === 'activa',
                                            'bg-rose-500/20 text-rose-300 border-rose-500/30': c.estado === 'bloqueada' || c.estado === 'cancelada',
                                            'bg-amber-500/20 text-amber-300 border-amber-500/30': c.estado === 'inactiva'
                                        }"
                                        class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold border capitalize"
                                    >
                                        {{ c.estado }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link
                                            :href="`/cuentas/${c.id}`"
                                            class="rounded-lg bg-slate-800 hover:bg-slate-700 px-2.5 py-1.5 text-xs font-medium text-blue-400 hover:text-blue-300 transition-colors flex items-center gap-1"
                                        >
                                            <Eye class="h-3.5 w-3.5" />
                                            Ver
                                        </Link>
                                        <Link
                                            :href="`/depositos/create?cuenta=${c.numero_cuenta}`"
                                            class="rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 px-2 py-1.5 text-xs font-semibold text-emerald-400 border border-emerald-500/30 flex items-center gap-1"
                                            title="Depositar"
                                        >
                                            <ArrowDownLeft class="h-3.5 w-3.5" />
                                            Depósito
                                        </Link>
                                        <Link
                                            :href="`/retiros/create?cuenta=${c.numero_cuenta}`"
                                            class="rounded-lg bg-amber-500/10 hover:bg-amber-500/20 px-2 py-1.5 text-xs font-semibold text-amber-400 border border-amber-500/30 flex items-center gap-1"
                                            title="Retirar"
                                        >
                                            <ArrowUpRight class="h-3.5 w-3.5" />
                                            Retiro
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="cuentas.data.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                    <Wallet class="mx-auto h-12 w-12 text-slate-600 mb-2" />
                                    No se encontraron cuentas bancarias registradas.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="cuentas.last_page > 1" class="flex items-center justify-between px-6 py-4 border-t border-slate-800 bg-slate-950/40">
                    <div class="text-xs text-slate-400">
                        Página {{ cuentas.current_page }} de {{ cuentas.last_page }} (Total: {{ cuentas.total }})
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-if="cuentas.prev_page_url"
                            :href="cuentas.prev_page_url"
                            class="rounded-lg bg-slate-800 px-3 py-1 text-xs text-white hover:bg-slate-700"
                        >
                            Anterior
                        </Link>
                        <Link
                            v-if="cuentas.next_page_url"
                            :href="cuentas.next_page_url"
                            class="rounded-lg bg-slate-800 px-3 py-1 text-xs text-white hover:bg-slate-700"
                        >
                            Siguiente
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
