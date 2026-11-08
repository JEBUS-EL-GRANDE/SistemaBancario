<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Calculator,
    CreditCard,
    Eye,
    Plus,
    Search,
    TrendingUp,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface Cliente {
    nombres: string;
    apellidos: string;
    numero_documento: string;
}

interface Prestamo {
    id: number;
    codigo: string;
    tipo: string;
    monto_solicitado: number;
    monto_aprobado: number | null;
    cuota_mensual: number | null;
    saldo_pendiente: number;
    tasa_interes: number;
    plazo_meses: number;
    estado: string;
    created_at: string;
    cliente: Cliente;
}

interface PrestamosPagination {
    data: Prestamo[];
    current_page: number;
    last_page: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

const props = defineProps<{
    prestamos: PrestamosPagination;
    filters: { search?: string; estado?: string; tipo?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Préstamos y Créditos', href: '/prestamos' },
];

const search = ref(props.filters.search || '');
const estado = ref(props.filters.estado || '');
const tipo = ref(props.filters.tipo || '');
const money = (n: number) => `Bs. ${Number(n || 0).toLocaleString('es-BO', { minimumFractionDigits: 2 })}`;

let timeout: number | null = null;
const filtrar = () => {
    if (timeout) clearTimeout(timeout);
    timeout = window.setTimeout(() => {
        router.get('/prestamos', { search: search.value, estado: estado.value, tipo: tipo.value }, { preserveState: true, replace: true });
    }, 350);
};

watch([search, estado, tipo], filtrar);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Cartera de Préstamos y Créditos - Banco Continental" />

        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                        <CreditCard class="h-7 w-7 text-amber-400" />
                        Cartera de Préstamos y Créditos
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">
                        Evaluación crediticia, aprobación, desembolsos y amortizaciones (RF-020 al RF-025).
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link
                        href="/prestamos/simulador"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 px-3.5 py-2.5 text-sm font-semibold text-slate-200 border border-slate-700 transition-all cursor-pointer"
                    >
                        <Calculator class="h-4 w-4" />
                        Simulador Cuotas
                    </Link>
                    <Link
                        href="/prestamos/create"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-amber-500/20 hover:from-amber-500 hover:to-orange-500 transition-all cursor-pointer"
                    >
                        <Plus class="h-4 w-4" />
                        Nueva Solicitud
                    </Link>
                </div>
            </div>

            <!-- Filtros -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 rounded-2xl bg-slate-900/60 p-4 border border-slate-800">
                <div class="relative">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Buscar por código, cliente o documento..."
                        class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 pl-9 pr-4 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500"
                    />
                </div>
                <div>
                    <select
                        v-model="tipo"
                        class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2 text-sm text-white focus:outline-none focus:border-amber-500"
                    >
                        <option value="">Todos los tipos</option>
                        <option value="personal">Préstamo Personal</option>
                        <option value="hipotecario">Préstamo Hipotecario</option>
                        <option value="comercial">Préstamo Comercial</option>
                    </select>
                </div>
                <div>
                    <select
                        v-model="estado"
                        class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2 text-sm text-white focus:outline-none focus:border-amber-500"
                    >
                        <option value="">Todos los estados</option>
                        <option value="solicitado">Solicitado</option>
                        <option value="aprobado">Aprobado</option>
                        <option value="desembolsado">Desembolsado</option>
                        <option value="al_dia">Al Día</option>
                        <option value="moroso">En Mora</option>
                        <option value="cancelado">Cancelado</option>
                        <option value="rechazado">Rechazado</option>
                    </select>
                </div>
            </div>

            <!-- Tabla -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/50 backdrop-blur-sm overflow-hidden shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-300">
                        <thead class="bg-slate-950/60 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-800">
                            <tr>
                                <th class="px-6 py-4 font-semibold">Código</th>
                                <th class="px-6 py-4 font-semibold">Cliente Titular</th>
                                <th class="px-6 py-4 font-semibold">Tipo</th>
                                <th class="px-6 py-4 font-semibold">Monto Solicitado</th>
                                <th class="px-6 py-4 font-semibold">Cuota Mensual</th>
                                <th class="px-6 py-4 font-semibold">Saldo Pendiente</th>
                                <th class="px-6 py-4 font-semibold">Estado</th>
                                <th class="px-6 py-4 font-semibold text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80">
                            <tr v-for="p in prestamos.data" :key="p.id" class="hover:bg-slate-800/40">
                                <td class="px-6 py-4 font-mono font-bold text-white text-xs">
                                    {{ p.codigo }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-200">{{ p.cliente.nombres }} {{ p.cliente.apellidos }}</div>
                                    <div class="text-xs text-slate-500 font-mono">Doc: {{ p.cliente.numero_documento }}</div>
                                </td>
                                <td class="px-6 py-4 capitalize text-xs">
                                    {{ p.tipo }}
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-200 font-mono">
                                    {{ money(p.monto_solicitado) }}
                                </td>
                                <td class="px-6 py-4 font-mono text-amber-400 font-semibold">
                                    {{ money(p.cuota_mensual || 0) }}
                                </td>
                                <td class="px-6 py-4 font-mono font-semibold text-rose-400">
                                    {{ money(p.saldo_pendiente) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="{
                                            'bg-blue-500/20 text-blue-300': p.estado === 'solicitado',
                                            'bg-emerald-500/20 text-emerald-300': p.estado === 'aprobado' || p.estado === 'al_dia',
                                            'bg-amber-500/20 text-amber-300': p.estado === 'desembolsado',
                                            'bg-rose-500/20 text-rose-300': p.estado === 'moroso' || p.estado === 'rechazado',
                                            'bg-slate-700 text-slate-300': p.estado === 'cancelado',
                                        }"
                                        class="rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase"
                                    >
                                        {{ p.estado.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Link
                                        :href="`/prestamos/${p.id}`"
                                        class="inline-flex items-center gap-1 rounded-lg bg-slate-800 hover:bg-slate-700 px-3 py-1.5 text-xs font-medium text-blue-400 hover:text-blue-300 transition-colors"
                                    >
                                        <Eye class="h-3.5 w-3.5" />
                                        Evaluar / Ver
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="prestamos.data.length === 0">
                                <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                                    No se encontraron préstamos o solicitudes.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="prestamos.last_page > 1" class="flex items-center justify-between px-6 py-4 border-t border-slate-800 bg-slate-950/40">
                    <div class="text-xs text-slate-400">
                        Página {{ prestamos.current_page }} de {{ prestamos.last_page }}
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-if="prestamos.prev_page_url"
                            :href="prestamos.prev_page_url"
                            class="rounded-lg bg-slate-800 px-3 py-1 text-xs text-white hover:bg-slate-700"
                        >
                            Anterior
                        </Link>
                        <Link
                            v-if="prestamos.next_page_url"
                            :href="prestamos.next_page_url"
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
