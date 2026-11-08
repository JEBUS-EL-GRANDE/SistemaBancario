<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowDownLeft,
    Eye,
    Plus,
    Search,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface Cliente {
    nombres: string;
    apellidos: string;
}

interface Cuenta {
    numero_cuenta: string;
    cliente: Cliente;
}

interface User {
    name: string;
}

interface Deposito {
    id: number;
    referencia: string;
    tipo: string;
    monto: number;
    estado: string;
    canal: string;
    created_at: string;
    cuenta: Cuenta;
    cajero: User | null;
}

interface DepositosPagination {
    data: Deposito[];
    current_page: number;
    last_page: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

const props = defineProps<{
    depositos: DepositosPagination;
    filters: { search?: string; tipo?: string; fecha_inicio?: string; fecha_fin?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Depósitos', href: '/depositos' },
];

const search = ref(props.filters.search || '');
const tipo = ref(props.filters.tipo || '');
const money = (n: number) => `Bs. ${Number(n || 0).toLocaleString('es-BO', { minimumFractionDigits: 2 })}`;

let timeout: number | null = null;
const filtrar = () => {
    if (timeout) clearTimeout(timeout);
    timeout = window.setTimeout(() => {
        router.get('/depositos', { search: search.value, tipo: tipo.value }, { preserveState: true, replace: true });
    }, 350);
};

watch([search, tipo], filtrar);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Historial de Depósitos - Banco Continental" />

        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                        <ArrowDownLeft class="h-7 w-7 text-emerald-400" />
                        Historial de Depósitos
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">
                        Control de ingresos en efectivo, cheques y transferencias.
                    </p>
                </div>
                <Link
                    href="/depositos/create"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 hover:from-emerald-500 hover:to-teal-500 transition-all cursor-pointer"
                >
                    <Plus class="h-4 w-4" />
                    Nuevo Depósito
                </Link>
            </div>

            <!-- Filtros -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 rounded-2xl bg-slate-900/60 p-4 border border-slate-800">
                <div class="relative">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Buscar por referencia, cuenta o titular..."
                        class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 pl-9 pr-4 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500"
                    />
                </div>
                <div>
                    <select
                        v-model="tipo"
                        class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2 text-sm text-white focus:outline-none focus:border-emerald-500"
                    >
                        <option value="">Todas las modalidades</option>
                        <option value="efectivo">Efectivo</option>
                        <option value="cheque">Cheque</option>
                        <option value="transferencia">Transferencia</option>
                    </select>
                </div>
            </div>

            <!-- Tabla -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/50 backdrop-blur-sm overflow-hidden shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-300">
                        <thead class="bg-slate-950/60 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-800">
                            <tr>
                                <th class="px-6 py-4 font-semibold">Referencia</th>
                                <th class="px-6 py-4 font-semibold">Cuenta / Titular</th>
                                <th class="px-6 py-4 font-semibold">Monto</th>
                                <th class="px-6 py-4 font-semibold">Tipo</th>
                                <th class="px-6 py-4 font-semibold">Canal</th>
                                <th class="px-6 py-4 font-semibold">Estado</th>
                                <th class="px-6 py-4 font-semibold">Fecha</th>
                                <th class="px-6 py-4 font-semibold text-right">Comprobante</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80">
                            <tr v-for="d in depositos.data" :key="d.id" class="hover:bg-slate-800/40">
                                <td class="px-6 py-4 font-mono font-bold text-white text-xs">
                                    {{ d.referencia }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-mono text-xs text-slate-200">{{ d.cuenta.numero_cuenta }}</div>
                                    <div class="text-xs text-slate-400">{{ d.cuenta.cliente.nombres }} {{ d.cuenta.cliente.apellidos }}</div>
                                </td>
                                <td class="px-6 py-4 font-semibold text-emerald-400 font-mono">
                                    {{ money(d.monto) }}
                                </td>
                                <td class="px-6 py-4 capitalize text-xs">
                                    {{ d.tipo }}
                                </td>
                                <td class="px-6 py-4 capitalize text-xs text-slate-400">
                                    {{ d.canal }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="d.estado === 'completado' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-amber-500/20 text-amber-300'"
                                        class="rounded-full px-2 py-0.5 text-xs font-semibold capitalize"
                                    >
                                        {{ d.estado }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-400">
                                    {{ new Date(d.created_at).toLocaleString('es-BO') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Link
                                        :href="`/depositos/${d.id}`"
                                        class="inline-flex items-center gap-1 rounded-lg bg-slate-800 hover:bg-slate-700 px-2.5 py-1 text-xs font-medium text-emerald-400 transition-colors"
                                    >
                                        <Eye class="h-3.5 w-3.5" />
                                        Ver Recibo
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="depositos.data.length === 0">
                                <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                                    No hay depósitos registrados.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="depositos.last_page > 1" class="flex items-center justify-between px-6 py-4 border-t border-slate-800 bg-slate-950/40">
                    <div class="text-xs text-slate-400">
                        Página {{ depositos.current_page }} de {{ depositos.last_page }}
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-if="depositos.prev_page_url"
                            :href="depositos.prev_page_url"
                            class="rounded-lg bg-slate-800 px-3 py-1 text-xs text-white hover:bg-slate-700"
                        >
                            Anterior
                        </Link>
                        <Link
                            v-if="depositos.next_page_url"
                            :href="depositos.next_page_url"
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
