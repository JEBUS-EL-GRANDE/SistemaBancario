<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowDownLeft,
    ArrowLeftRight,
    ArrowUpRight,
    Calendar,
    ChevronLeft,
    ChevronRight,
    Eye,
    Filter,
    Plus,
    Search,
    Wallet,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface TransferenciaItem {
    id: number;
    referencia: string;
    monto: number;
    canal: string;
    metodo_autenticacion: string;
    motivo: string;
    estado: string;
    created_at: string;
    cuenta_origen: {
        numero_cuenta: string;
        tipo: string;
        cliente: {
            nombres: string;
            apellidos: string;
        };
    };
    cuenta_destino: {
        numero_cuenta: string;
        tipo: string;
        cliente: {
            nombres: string;
            apellidos: string;
        };
    };
    usuario?: {
        name: string;
        apellidos: string;
    };
}

interface TransferenciasPagination {
    data: TransferenciaItem[];
    current_page: number;
    last_page: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

interface Props {
    transferencias: TransferenciasPagination;
    filters: {
        search?: string;
        canal?: string;
        fecha_inicio?: string;
        fecha_fin?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Transferencias', href: '/transferencias' },
];

const search = ref(props.filters.search || '');
const canal = ref(props.filters.canal || '');
const fechaInicio = ref(props.filters.fecha_inicio || '');
const fechaFin = ref(props.filters.fecha_fin || '');

const aplicarFiltros = () => {
    router.get(
        '/transferencias',
        {
            search: search.value || undefined,
            canal: canal.value || undefined,
            fecha_inicio: fechaInicio.value || undefined,
            fecha_fin: fechaFin.value || undefined,
        },
        { preserveState: true, replace: true }
    );
};

const money = (n: number) => `Bs. ${Number(n || 0).toLocaleString('es-BO', { minimumFractionDigits: 2 })}`;
const formatDate = (d: string) => {
    if (!d) return '';
    return new Date(d).toLocaleDateString('es-BO', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Historial de Transferencias - Banco Continental" />

        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Encabezado con Botón de Nueva Transferencia -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                        <ArrowLeftRight class="h-7 w-7 text-emerald-400" />
                        Transferencias entre Cuentas
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">
                        Traspaso en línea de fondos propios y a terceros con validación en tiempo real.
                    </p>
                </div>
                <Link
                    href="/transferencias/create"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 hover:from-emerald-500 hover:to-teal-500 transition-all cursor-pointer"
                >
                    <Plus class="h-4 w-4" />
                    Nueva Transferencia
                </Link>
            </div>

            <!-- Filtros -->
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-4 rounded-2xl border border-slate-800 bg-slate-900/60 p-4 backdrop-blur-sm">
                <div class="relative sm:col-span-2">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                    <input
                        v-model="search"
                        @keyup.enter="aplicarFiltros"
                        type="text"
                        placeholder="Buscar por referencia, cuenta o titular..."
                        class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 pl-9 pr-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500"
                    />
                </div>

                <div>
                    <select
                        v-model="canal"
                        @change="aplicarFiltros"
                        class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500"
                    >
                        <option value="">Todos los canales</option>
                        <option value="ventanilla">Ventanilla de Caja</option>
                        <option value="banca_web">Banca por Internet</option>
                        <option value="atm">Cajero ATM</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button
                        type="button"
                        @click="aplicarFiltros"
                        class="flex-1 rounded-xl bg-slate-800 hover:bg-slate-700 px-3 py-2 text-xs font-semibold text-white border border-slate-700 transition-colors cursor-pointer"
                    >
                        Filtrar
                    </button>
                    <button
                        v-if="search || canal || fechaInicio || fechaFin"
                        type="button"
                        @click="() => { search = ''; canal = ''; fechaInicio = ''; fechaFin = ''; aplicarFiltros(); }"
                        class="rounded-xl bg-slate-800/60 hover:bg-slate-800 px-3 py-2 text-xs text-slate-400 hover:text-white transition-colors cursor-pointer"
                    >
                        Limpiar
                    </button>
                </div>
            </div>

            <!-- Tabla de Transferencias -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 overflow-hidden shadow-xl backdrop-blur-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-slate-950/80 text-[11px] uppercase tracking-wider text-slate-400 border-b border-slate-800">
                            <tr>
                                <th class="px-5 py-3.5 font-semibold">Referencia</th>
                                <th class="px-5 py-3.5 font-semibold">Cuenta Origen</th>
                                <th class="px-5 py-3.5 font-semibold">Cuenta Destino</th>
                                <th class="px-5 py-3.5 font-semibold">Monto</th>
                                <th class="px-5 py-3.5 font-semibold">Canal</th>
                                <th class="px-5 py-3.5 font-semibold">Fecha y Hora</th>
                                <th class="px-5 py-3.5 text-right font-semibold">Comprobante</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            <tr
                                v-for="t in transferencias.data"
                                :key="t.id"
                                class="hover:bg-slate-800/40 transition-colors"
                            >
                                <td class="px-5 py-3.5 font-mono font-bold text-white">
                                    {{ t.referencia }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="font-mono text-slate-200">{{ t.cuenta_origen.numero_cuenta }}</div>
                                    <div class="text-[11px] text-slate-400">
                                        {{ t.cuenta_origen.cliente.nombres }} {{ t.cuenta_origen.cliente.apellidos }}
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="font-mono text-emerald-400">{{ t.cuenta_destino.numero_cuenta }}</div>
                                    <div class="text-[11px] text-slate-400">
                                        {{ t.cuenta_destino.cliente.nombres }} {{ t.cuenta_destino.cliente.apellidos }}
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 font-mono text-sm font-black text-emerald-400">
                                    {{ money(t.monto) }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center rounded-lg bg-slate-800 px-2 py-0.5 text-[10px] font-medium text-slate-300 capitalize">
                                        {{ t.canal }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-slate-400">
                                    {{ formatDate(t.created_at) }}
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <Link
                                        :href="`/transferencias/${t.id}`"
                                        class="inline-flex items-center gap-1 rounded-lg bg-slate-800 hover:bg-slate-700 px-2.5 py-1 text-[11px] font-medium text-slate-200 transition-colors"
                                    >
                                        <Eye class="h-3.5 w-3.5" />
                                        Ver
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="transferencias.data.length === 0">
                                <td colspan="7" class="px-5 py-12 text-center text-slate-500 text-sm">
                                    No se encontraron transferencias bancarias.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="transferencias.last_page > 1" class="flex items-center justify-between px-6 py-4 border-t border-slate-800 bg-slate-950/40 text-xs text-slate-400">
                    <div>
                        Página {{ transferencias.current_page }} de {{ transferencias.last_page }} ({{ transferencias.total }} transferencias)
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-if="transferencias.prev_page_url"
                            :href="transferencias.prev_page_url"
                            class="inline-flex items-center gap-1 rounded-lg bg-slate-800 hover:bg-slate-700 px-3 py-1.5 text-white transition-colors"
                        >
                            <ChevronLeft class="h-4 w-4" /> Anterior
                        </Link>
                        <Link
                            v-if="transferencias.next_page_url"
                            :href="transferencias.next_page_url"
                            class="inline-flex items-center gap-1 rounded-lg bg-slate-800 hover:bg-slate-700 px-3 py-1.5 text-white transition-colors"
                        >
                            Siguiente <ChevronRight class="h-4 w-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
