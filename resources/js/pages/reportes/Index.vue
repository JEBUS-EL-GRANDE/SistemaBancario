<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowDownLeft,
    ArrowUpRight,
    CreditCard,
    Download,
    FileSpreadsheet,
    FileText,
    History,
    Printer,
    Search,
    Shield,
    TrendingUp,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface Resumen {
    total_depositos: number;
    total_retiros: number;
    flujo_neto: number;
    cartera_prestamos: number;
}

interface Pagination {
    data: any[];
    current_page: number;
    last_page: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

const props = defineProps<{
    tipoReporte: string;
    fechaInicio: string;
    fechaFin: string;
    resumen: Resumen;
    datos: Pagination;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Reportes y Auditoría', href: '/reportes' },
];

const tipo = ref(props.tipoReporte);
const fechaInicio = ref(props.fechaInicio);
const fechaFin = ref(props.fechaFin);

const money = (n: number) => `Bs. ${Number(n || 0).toLocaleString('es-BO', { minimumFractionDigits: 2 })}`;

const aplicarFiltros = () => {
    router.get('/reportes', {
        tipo: tipo.value,
        fecha_inicio: fechaInicio.value,
        fecha_fin: fechaFin.value,
    }, { preserveState: true });
};

const imprimir = () => {
    window.print();
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Reportes y Auditoría Bancaria - Banco Continental" />

        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-print">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                        <FileText class="h-7 w-7 text-amber-400" />
                        Reportes y Auditoría Bancaria
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">
                        Consolidado financiero, extractos de movimientos, cartera crediticia y bitácora inmutable (RF-026 al RF-030).
                    </p>
                </div>
                <div class="flex gap-2">
                    <a
                        :href="`/reportes/exportar?tipo=${tipo}&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-700 hover:bg-emerald-600 px-4 py-2.5 text-xs font-semibold text-white shadow-md shadow-emerald-500/20 transition-all cursor-pointer"
                        title="Descargar archivo CSV"
                    >
                        <FileSpreadsheet class="h-4 w-4" />
                        Exportar CSV
                    </a>
                    <button
                        @click="imprimir"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 px-3.5 py-2.5 text-xs font-semibold text-slate-200 border border-slate-700 transition-all cursor-pointer"
                    >
                        <Printer class="h-4 w-4" />
                        Imprimir Reporte
                    </button>
                </div>
            </div>

            <!-- Resumen Financiero del Período -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 no-print">
                <div class="rounded-2xl border border-emerald-500/30 bg-emerald-950/20 p-5">
                    <span class="text-xs uppercase tracking-wider text-emerald-400 font-semibold flex items-center gap-1.5 mb-1">
                        <ArrowDownLeft class="h-4 w-4" />
                        Total Ingresos (Depósitos)
                    </span>
                    <div class="font-mono text-2xl font-black text-emerald-400">
                        {{ money(resumen.total_depositos) }}
                    </div>
                </div>

                <div class="rounded-2xl border border-amber-500/30 bg-amber-950/20 p-5">
                    <span class="text-xs uppercase tracking-wider text-amber-400 font-semibold flex items-center gap-1.5 mb-1">
                        <ArrowUpRight class="h-4 w-4" />
                        Total Egresos (Retiros)
                    </span>
                    <div class="font-mono text-2xl font-black text-amber-400">
                        {{ money(resumen.total_retiros) }}
                    </div>
                </div>

                <div class="rounded-2xl border border-blue-500/30 bg-blue-950/20 p-5">
                    <span class="text-xs uppercase tracking-wider text-blue-400 font-semibold flex items-center gap-1.5 mb-1">
                        <TrendingUp class="h-4 w-4" />
                        Flujo Neto en Período
                    </span>
                    <div class="font-mono text-2xl font-black text-blue-400">
                        {{ money(resumen.flujo_neto) }}
                    </div>
                </div>

                <div class="rounded-2xl border border-indigo-500/30 bg-indigo-950/20 p-5">
                    <span class="text-xs uppercase tracking-wider text-indigo-400 font-semibold flex items-center gap-1.5 mb-1">
                        <CreditCard class="h-4 w-4" />
                        Cartera Préstamos Activa
                    </span>
                    <div class="font-mono text-2xl font-black text-indigo-400">
                        {{ money(resumen.cartera_prestamos) }}
                    </div>
                </div>
            </div>

            <!-- Filtros de Reporte -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 rounded-2xl bg-slate-900/60 p-4 border border-slate-800 no-print">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">
                        Tipo de Informe
                    </label>
                    <select
                        v-model="tipo"
                        @change="aplicarFiltros"
                        class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2 text-sm text-white focus:outline-none focus:border-amber-500"
                    >
                        <option value="movimientos">Libro Mayor de Movimientos (RF-027)</option>
                        <option value="depositos">Consolidado de Depósitos (RF-028)</option>
                        <option value="retiros">Consolidado de Retiros (RF-028)</option>
                        <option value="prestamos">Cartera de Préstamos y Créditos (RF-029)</option>
                        <option value="bitacora">Bitácora de Auditoría del Sistema (RNF-013)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">
                        Desde Fecha
                    </label>
                    <input
                        v-model="fechaInicio"
                        type="date"
                        @change="aplicarFiltros"
                        class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2 text-sm text-white focus:outline-none focus:border-amber-500"
                    />
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">
                        Hasta Fecha
                    </label>
                    <input
                        v-model="fechaFin"
                        type="date"
                        @change="aplicarFiltros"
                        class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2 text-sm text-white focus:outline-none focus:border-amber-500"
                    />
                </div>
            </div>

            <!-- Tabla Dinámica del Reporte -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/50 backdrop-blur-sm overflow-hidden shadow-xl print:border-none print:bg-white print:text-black">
                <div class="p-4 border-b border-slate-800 bg-slate-950/60 print:bg-transparent print:border-black flex justify-between items-center">
                    <h3 class="text-base font-bold text-white print:text-black capitalize">
                        Reporte: {{ tipo }}
                    </h3>
                    <span class="text-xs text-slate-400 print:text-gray-600">
                        Período: {{ fechaInicio }} al {{ fechaFin }} ({{ datos.total }} registros)
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <!-- Vista Movimientos -->
                    <table v-if="tipo === 'movimientos'" class="w-full text-left text-sm text-slate-300 print:text-black">
                        <thead class="bg-slate-950 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-800 print:bg-gray-100 print:border-black print:text-black">
                            <tr>
                                <th class="px-6 py-3 font-semibold">Fecha / Hora</th>
                                <th class="px-6 py-3 font-semibold">Referencia</th>
                                <th class="px-6 py-3 font-semibold">Tipo</th>
                                <th class="px-6 py-3 font-semibold">Cuenta / Titular</th>
                                <th class="px-6 py-3 font-semibold text-right">Monto</th>
                                <th class="px-6 py-3 font-semibold text-right">Saldo Anterior</th>
                                <th class="px-6 py-3 font-semibold text-right">Saldo Resultante</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80 font-mono text-xs print:divide-gray-300">
                            <tr v-for="m in datos.data" :key="m.id" class="hover:bg-slate-800/40">
                                <td class="px-6 py-3 font-sans text-slate-400 print:text-black">{{ new Date(m.created_at).toLocaleString('es-BO') }}</td>
                                <td class="px-6 py-3 font-bold text-white print:text-black">{{ m.referencia }}</td>
                                <td class="px-6 py-3 capitalize font-sans">{{ m.tipo }}</td>
                                <td class="px-6 py-3 font-sans">
                                    <div>{{ m.cuenta?.numero_cuenta }}</div>
                                    <div class="text-[11px] text-slate-500">{{ m.cuenta?.cliente?.nombres }} {{ m.cuenta?.cliente?.apellidos }}</div>
                                </td>
                                <td :class="m.tipo.includes('deposito') ? 'text-emerald-400' : 'text-rose-400'" class="px-6 py-3 text-right font-bold print:text-black">
                                    {{ m.tipo.includes('deposito') ? '+' : '-' }}{{ money(m.monto) }}
                                </td>
                                <td class="px-6 py-3 text-right text-slate-400 print:text-black">{{ money(m.saldo_anterior) }}</td>
                                <td class="px-6 py-3 text-right font-bold text-white print:text-black">{{ money(m.saldo_nuevo) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Vista Préstamos -->
                    <table v-else-if="tipo === 'prestamos'" class="w-full text-left text-sm text-slate-300 print:text-black">
                        <thead class="bg-slate-950 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-800 print:bg-gray-100 print:border-black print:text-black">
                            <tr>
                                <th class="px-6 py-3 font-semibold">Código</th>
                                <th class="px-6 py-3 font-semibold">Titular</th>
                                <th class="px-6 py-3 font-semibold">Tipo</th>
                                <th class="px-6 py-3 font-semibold text-right">Solicitado</th>
                                <th class="px-6 py-3 font-semibold text-right">Aprobado</th>
                                <th class="px-6 py-3 font-semibold text-right">Saldo Pendiente</th>
                                <th class="px-6 py-3 font-semibold">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80 font-mono text-xs print:divide-gray-300">
                            <tr v-for="p in datos.data" :key="p.id" class="hover:bg-slate-800/40">
                                <td class="px-6 py-3 font-bold text-white print:text-black">{{ p.codigo }}</td>
                                <td class="px-6 py-3 font-sans">{{ p.cliente?.nombres }} {{ p.cliente?.apellidos }}</td>
                                <td class="px-6 py-3 font-sans capitalize">{{ p.tipo }}</td>
                                <td class="px-6 py-3 text-right">{{ money(p.monto_solicitado) }}</td>
                                <td class="px-6 py-3 text-right text-emerald-400 font-bold print:text-black">{{ money(p.monto_aprobado || 0) }}</td>
                                <td class="px-6 py-3 text-right text-rose-400 font-bold print:text-black">{{ money(p.saldo_pendiente) }}</td>
                                <td class="px-6 py-3 font-sans uppercase font-bold text-[11px]">{{ p.estado }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Vista Bitácora -->
                    <table v-else-if="tipo === 'bitacora'" class="w-full text-left text-sm text-slate-300 print:text-black">
                        <thead class="bg-slate-950 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-800 print:bg-gray-100 print:border-black print:text-black">
                            <tr>
                                <th class="px-6 py-3 font-semibold">Fecha / Hora</th>
                                <th class="px-6 py-3 font-semibold">Acción</th>
                                <th class="px-6 py-3 font-semibold">Usuario</th>
                                <th class="px-6 py-3 font-semibold">Tabla Afectada</th>
                                <th class="px-6 py-3 font-semibold">Detalles</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80 text-xs print:divide-gray-300">
                            <tr v-for="b in datos.data" :key="b.id" class="hover:bg-slate-800/40">
                                <td class="px-6 py-3 font-mono text-slate-400 print:text-black">{{ new Date(b.created_at).toLocaleString('es-BO') }}</td>
                                <td class="px-6 py-3 font-semibold text-white print:text-black">{{ b.accion }}</td>
                                <td class="px-6 py-3 text-slate-300 print:text-black">{{ b.usuario?.name || 'Sistema' }} ({{ b.usuario?.rol || 'daemon' }})</td>
                                <td class="px-6 py-3 font-mono text-amber-400 print:text-black">{{ b.tabla_afectada || '—' }}</td>
                                <td class="px-6 py-3 text-slate-400 print:text-black">{{ b.detalles }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Vista Depósitos o Retiros -->
                    <table v-else class="w-full text-left text-sm text-slate-300 print:text-black">
                        <thead class="bg-slate-950 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-800 print:bg-gray-100 print:border-black print:text-black">
                            <tr>
                                <th class="px-6 py-3 font-semibold">Fecha / Hora</th>
                                <th class="px-6 py-3 font-semibold">Referencia</th>
                                <th class="px-6 py-3 font-semibold">Cuenta / Titular</th>
                                <th class="px-6 py-3 font-semibold text-right">Monto</th>
                                <th class="px-6 py-3 font-semibold">Canal</th>
                                <th class="px-6 py-3 font-semibold">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80 font-mono text-xs print:divide-gray-300">
                            <tr v-for="t in datos.data" :key="t.id" class="hover:bg-slate-800/40">
                                <td class="px-6 py-3 font-sans text-slate-400 print:text-black">{{ new Date(t.created_at).toLocaleString('es-BO') }}</td>
                                <td class="px-6 py-3 font-bold text-white print:text-black">{{ t.referencia }}</td>
                                <td class="px-6 py-3 font-sans">
                                    {{ t.cuenta?.numero_cuenta }} ({{ t.cuenta?.cliente?.nombres }} {{ t.cuenta?.cliente?.apellidos }})
                                </td>
                                <td class="px-6 py-3 text-right font-bold text-emerald-400 print:text-black">{{ money(t.monto) }}</td>
                                <td class="px-6 py-3 capitalize font-sans">{{ t.canal }}</td>
                                <td class="px-6 py-3 uppercase font-sans font-bold text-[11px]">{{ t.estado }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="datos.data.length === 0" class="text-center py-12 text-slate-500 text-sm">
                        No se encontraron registros para el tipo de reporte y período seleccionados.
                    </div>
                </div>

                <!-- Paginación -->
                <div v-if="datos.last_page > 1" class="flex items-center justify-between px-6 py-4 border-t border-slate-800 bg-slate-950/40 no-print">
                    <div class="text-xs text-slate-400">
                        Página {{ datos.current_page }} de {{ datos.last_page }}
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-if="datos.prev_page_url"
                            :href="datos.prev_page_url"
                            class="rounded-lg bg-slate-800 px-3 py-1 text-xs text-white hover:bg-slate-700"
                        >
                            Anterior
                        </Link>
                        <Link
                            v-if="datos.next_page_url"
                            :href="datos.next_page_url"
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
