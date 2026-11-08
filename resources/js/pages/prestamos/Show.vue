<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowLeft,
    Banknote,
    Check,
    CheckCircle2,
    CreditCard,
    DollarSign,
    Lock,
    Printer,
    ShieldAlert,
    ShieldCheck,
    TrendingUp,
    User,
    Wallet,
    X,
} from 'lucide-vue-next';
import { ref } from 'vue';

interface Cliente {
    id: number;
    nombres: string;
    apellidos: string;
    numero_documento: string;
    email: string;
}

interface Cuenta {
    id: number;
    numero_cuenta: string;
    tipo: string;
    saldo: number;
}

interface Cuota {
    id: number;
    numero_cuota: number;
    fecha_vencimiento: string;
    cuota_total: number;
    capital: number;
    interes: number;
    saldo_restante: number;
    estado: string;
    monto_pagado: number;
    fecha_pago: string | null;
}

interface Pago {
    id: number;
    referencia: string;
    monto: number;
    tipo: string;
    created_at: string;
}

interface Prestamo {
    id: number;
    codigo: string;
    tipo: string;
    monto_solicitado: number;
    monto_aprobado: number | null;
    tasa_interes: number;
    plazo_meses: number;
    destino_credito: string | null;
    cuota_mensual: number | null;
    saldo_pendiente: number;
    estado: string;
    motivo_rechazo: string | null;
    ingreso_mensual: number | null;
    egreso_mensual: number | null;
    capacidad_pago: number | null;
    fecha_solicitud: string;
    fecha_aprobacion: string | null;
    fecha_desembolso: string | null;
    cliente: Cliente;
    cuenta_desembolso: Cuenta | null;
    cuotas: Cuota[];
    pagos: Pago[];
}

const props = defineProps<{
    prestamo: Prestamo;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Préstamos', href: '/prestamos' },
    { title: props.prestamo.codigo, href: `/prestamos/${props.prestamo.id}` },
];

const money = (n: number) => `Bs. ${Number(n || 0).toLocaleString('es-BO', { minimumFractionDigits: 2 })}`;

// Modal para Aprobar / Rechazar (RF-022)
const showModalEvaluar = ref(false);
const decisionEval = ref<'aprobar' | 'rechazar'>('aprobar');

const formEvaluar = useForm({
    decision: 'aprobar',
    monto_aprobado: props.prestamo.monto_aprobado || props.prestamo.monto_solicitado,
    motivo: '',
});

const abrirEvaluacion = (tipo: 'aprobar' | 'rechazar') => {
    decisionEval.value = tipo;
    formEvaluar.decision = tipo;
    showModalEvaluar.value = true;
};

const enviarEvaluacion = () => {
    formEvaluar.post(`/prestamos/${props.prestamo.id}/evaluar`, {
        onSuccess: () => {
            showModalEvaluar.value = false;
        },
    });
};

// Desembolso (RF-024)
const formDesembolsar = useForm({});
const desembolsar = () => {
    if (confirm(`¿Confirma el desembolso de ${money(props.prestamo.monto_aprobado || 0)} a la cuenta del cliente?`)) {
        formDesembolsar.post(`/prestamos/${props.prestamo.id}/desembolsar`);
    }
};

const imprimir = () => {
    window.print();
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Préstamo: ${prestamo.codigo}`" />

        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-print">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="rounded-lg bg-amber-500/10 px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-amber-400 border border-amber-500/20">
                            Crédito {{ prestamo.tipo }}
                        </span>
                        <span
                            :class="{
                                'bg-blue-500/20 text-blue-300': prestamo.estado === 'solicitado',
                                'bg-emerald-500/20 text-emerald-300': prestamo.estado === 'aprobado' || prestamo.estado === 'al_dia',
                                'bg-amber-500/20 text-amber-300': prestamo.estado === 'desembolsado',
                                'bg-rose-500/20 text-rose-300': prestamo.estado === 'moroso' || prestamo.estado === 'rechazado',
                                'bg-slate-700 text-slate-300': prestamo.estado === 'cancelado',
                            }"
                            class="rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase"
                        >
                            {{ prestamo.estado.replace('_', ' ') }}
                        </span>
                    </div>
                    <h1 class="text-3xl font-black font-mono tracking-wider text-white mt-2">
                        {{ prestamo.codigo }}
                    </h1>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <!-- Botones de Acción según estado -->
                    <template v-if="prestamo.estado === 'solicitado' || prestamo.estado === 'en_evaluacion'">
                        <button
                            @click="abrirEvaluacion('aprobar')"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-emerald-500/20 hover:from-emerald-500 cursor-pointer"
                        >
                            <Check class="h-4 w-4" />
                            Aprobar Crédito
                        </button>
                        <button
                            @click="abrirEvaluacion('rechazar')"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-rose-600 hover:bg-rose-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-rose-500/20 cursor-pointer"
                        >
                            <X class="h-4 w-4" />
                            Rechazar
                        </button>
                    </template>

                    <template v-if="prestamo.estado === 'aprobado'">
                        <button
                            @click="desembolsar"
                            :disabled="formDesembolsar.processing"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-500/30 hover:from-emerald-500 cursor-pointer animate-pulse"
                        >
                            <Banknote class="h-5 w-5" />
                            {{ formDesembolsar.processing ? 'Desembolsando...' : 'Efectuar Desembolso a Cuenta' }}
                        </button>
                    </template>

                    <template v-if="prestamo.estado === 'desembolsado' || prestamo.estado === 'al_dia' || prestamo.estado === 'moroso'">
                        <Link
                            :href="`/pagos-prestamo/create?codigo=${prestamo.codigo}`"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-blue-500/20 hover:from-blue-500 cursor-pointer"
                        >
                            <DollarSign class="h-4 w-4" />
                            Registrar Pago de Cuota
                        </Link>
                    </template>

                    <button
                        @click="imprimir"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 px-3.5 py-2.5 text-sm font-semibold text-slate-300 hover:text-white"
                    >
                        <Printer class="h-4 w-4" />
                        Imprimir
                    </button>
                    <Link
                        href="/prestamos"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-slate-800 px-3.5 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-700"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Volver
                    </Link>
                </div>
            </div>

            <!-- Balances del Crédito -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-2xl border border-amber-500/30 bg-amber-950/20 p-5">
                    <span class="text-xs font-semibold uppercase tracking-wider text-amber-400 block mb-1">
                        Monto Aprobado / Solicitado
                    </span>
                    <div class="text-3xl font-black text-amber-400 font-mono">
                        {{ money(prestamo.monto_aprobado || prestamo.monto_solicitado) }}
                    </div>
                    <span class="text-xs text-slate-400 mt-2 block">
                        Plazo: {{ prestamo.plazo_meses }} meses • Tasa: {{ prestamo.tasa_interes }}%
                    </span>
                </div>

                <div class="rounded-2xl border border-indigo-500/30 bg-indigo-950/20 p-5">
                    <span class="text-xs font-semibold uppercase tracking-wider text-indigo-400 block mb-1">
                        Cuota Fija Mensual
                    </span>
                    <div class="text-3xl font-black text-indigo-400 font-mono">
                        {{ money(prestamo.cuota_mensual || 0) }}
                    </div>
                    <span class="text-xs text-slate-400 mt-2 block">Amortización Francesa</span>
                </div>

                <div class="rounded-2xl border border-rose-500/30 bg-rose-950/20 p-5">
                    <span class="text-xs font-semibold uppercase tracking-wider text-rose-400 block mb-1">
                        Saldo Pendiente
                    </span>
                    <div class="text-3xl font-black text-rose-400 font-mono">
                        {{ money(prestamo.saldo_pendiente) }}
                    </div>
                    <span class="text-xs text-slate-400 mt-2 block">Capital adeudado</span>
                </div>

                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 block mb-1">
                        Cuenta de Desembolso
                    </span>
                    <div class="text-xl font-bold text-white font-mono">
                        {{ prestamo.cuenta_desembolso?.numero_cuenta || 'No asignada' }}
                    </div>
                    <span class="text-xs text-slate-500 mt-2 block">
                        Titular: {{ prestamo.cliente.nombres }} {{ prestamo.cliente.apellidos }}
                    </span>
                </div>
            </div>

            <!-- Calendario de Cuotas de Amortización (RF-023) -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm print:border-none print:bg-white print:text-black">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-white print:text-black flex items-center gap-2">
                        <CreditCard class="h-5 w-5 text-indigo-400" />
                        Plan de Amortización Francesa ({{ prestamo.cuotas.length }} Cuotas)
                    </h3>
                    <span class="text-xs text-slate-400">Cuotas calculadas matemáticamente</span>
                </div>

                <div v-if="prestamo.cuotas.length > 0" class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-300 print:text-black">
                        <thead class="bg-slate-950/70 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-800 print:bg-gray-100 print:border-black print:text-black">
                            <tr>
                                <th class="px-4 py-3 text-center">N°</th>
                                <th class="px-4 py-3">Vencimiento</th>
                                <th class="px-4 py-3 text-right">Cuota Total</th>
                                <th class="px-4 py-3 text-right">Capital</th>
                                <th class="px-4 py-3 text-right">Interés</th>
                                <th class="px-4 py-3 text-right">Saldo Restante</th>
                                <th class="px-4 py-3 text-center">Estado</th>
                                <th class="px-4 py-3">Fecha de Pago</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80 font-mono text-xs print:divide-gray-300">
                            <tr v-for="c in prestamo.cuotas" :key="c.id" class="hover:bg-slate-800/30">
                                <td class="px-4 py-3 text-center font-bold text-slate-400 print:text-black">
                                    {{ c.numero_cuota }}
                                </td>
                                <td class="px-4 py-3 font-sans text-slate-300 print:text-black">
                                    {{ c.fecha_vencimiento }}
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-white print:text-black">
                                    {{ money(c.cuota_total) }}
                                </td>
                                <td class="px-4 py-3 text-right text-emerald-400 print:text-black">
                                    {{ money(c.capital) }}
                                </td>
                                <td class="px-4 py-3 text-right text-amber-400 print:text-black">
                                    {{ money(c.interes) }}
                                </td>
                                <td class="px-4 py-3 text-right text-slate-300 print:text-black">
                                    {{ money(c.saldo_restante) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        :class="c.estado === 'pagada' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-800 text-slate-300'"
                                        class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase"
                                    >
                                        {{ c.estado }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-sans text-slate-400 print:text-black">
                                    {{ c.fecha_pago || '—' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="text-center py-10 text-slate-500 text-sm">
                    El calendario de amortización se generará automáticamente una vez aprobado el crédito.
                </div>
            </div>
        </div>

        <!-- Modal Evaluar Solicitud (RF-022) -->
        <div v-if="showModalEvaluar" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
            <div class="w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-2xl">
                <h3 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
                    <ShieldCheck v-if="decisionEval === 'aprobar'" class="h-5 w-5 text-emerald-400" />
                    <ShieldAlert v-else class="h-5 w-5 text-rose-400" />
                    {{ decisionEval === 'aprobar' ? 'Aprobar Solicitud de Crédito' : 'Rechazar Solicitud de Crédito' }}
                </h3>
                <p class="text-xs text-slate-400 mb-4">
                    {{ decisionEval === 'aprobar' ? 'Al aprobar se generará el calendario de cuotas bajo amortización francesa.' : 'Indique el motivo formal del rechazo para constancia en bitácora.' }}
                </p>

                <form @submit.prevent="enviarEvaluacion" class="space-y-4">
                    <div v-if="decisionEval === 'aprobar'">
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Monto Aprobado (BOB)</label>
                        <input
                            v-model.number="formEvaluar.monto_aprobado"
                            type="number"
                            step="100"
                            required
                            class="w-full rounded-xl bg-slate-950 border border-slate-700 px-3 py-2 text-sm text-emerald-400 font-mono font-bold focus:outline-none focus:border-emerald-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">
                            {{ decisionEval === 'aprobar' ? 'Observaciones / Condiciones' : 'Motivo del Rechazo *' }}
                        </label>
                        <textarea
                            v-model="formEvaluar.motivo"
                            :required="decisionEval === 'rechazar'"
                            rows="2"
                            placeholder="Detalle la resolución crediticia..."
                            class="w-full rounded-xl bg-slate-950 border border-slate-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-500"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                        <button
                            type="button"
                            @click="showModalEvaluar = false"
                            class="rounded-xl px-4 py-2 text-xs font-semibold text-slate-400 hover:text-white"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="formEvaluar.processing"
                            :class="decisionEval === 'aprobar' ? 'bg-emerald-600 hover:bg-emerald-500' : 'bg-rose-600 hover:bg-rose-500'"
                            class="rounded-xl px-4 py-2 text-xs font-semibold text-white cursor-pointer"
                        >
                            Confirmar {{ decisionEval === 'aprobar' ? 'Aprobación' : 'Rechazo' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
