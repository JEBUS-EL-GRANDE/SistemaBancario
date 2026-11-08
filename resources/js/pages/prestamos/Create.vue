<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowLeft,
    Check,
    CheckCircle2,
    CreditCard,
    DollarSign,
    ShieldAlert,
    TrendingUp,
} from 'lucide-vue-next';
import { computed } from 'vue';

interface Cuenta {
    id: number;
    numero_cuenta: string;
    tipo: string;
    saldo: number;
}

interface Cliente {
    id: number;
    nombres: string;
    apellidos: string;
    numero_documento: string;
    cuentas: Cuenta[];
}

const props = defineProps<{
    clientes: Cliente[];
    selectedClienteId: number | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Préstamos', href: '/prestamos' },
    { title: 'Nueva Solicitud', href: '/prestamos/create' },
];

const form = useForm({
    cliente_id: props.selectedClienteId || (props.clientes.length > 0 ? props.clientes[0].id : ''),
    cuenta_desembolso_id: '',
    tipo: 'personal',
    monto_solicitado: 20000,
    tasa_interes: 12,
    plazo_meses: 24,
    destino_credito: 'Inversión comercial / personal',
    ingreso_mensual: 5000,
    egreso_mensual: 1500,
});

const clienteSeleccionado = computed(() => {
    return props.clientes.find((c) => c.id === Number(form.cliente_id));
});

const money = (n: number) => `Bs. ${Number(n || 0).toLocaleString('es-BO', { minimumFractionDigits: 2 })}`;

// Cuota fija estimada (Sistema Francés)
const cuotaEstimada = computed(() => {
    const P = Number(form.monto_solicitado) || 0;
    const n = Number(form.plazo_meses) || 1;
    const r = (Number(form.tasa_interes) || 0) / 100 / 12;

    if (P <= 0 || n <= 0 || r <= 0) return 0;
    const factor = Math.pow(1 + r, n);
    return Math.round((P * ((r * factor) / (factor - 1))) * 100) / 100;
});

// Capacidad de endeudamiento máxima sugerida: 40% del ingreso neto (RF-021)
const capacidadPago = computed(() => {
    const neto = (Number(form.ingreso_mensual) || 0) - (Number(form.egreso_mensual) || 0);
    return Math.max(0, Math.round(neto * 0.40 * 100) / 100);
});

const esApto = computed(() => {
    return capacidadPago.value >= cuotaEstimada.value;
});

const submit = () => {
    form.post('/prestamos');
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Solicitud de Crédito - Banco Continental" />

        <div class="mx-auto max-w-4xl space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                        <CreditCard class="h-7 w-7 text-amber-400" />
                        Nueva Solicitud de Préstamo
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">
                        Evaluación crediticia automática y precalificación financiera (RF-020, RF-021).
                    </p>
                </div>
                <Link
                    href="/prestamos"
                    class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-3.5 py-2 text-sm font-medium text-slate-300 hover:bg-slate-700 hover:text-white transition-all"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Volver
                </Link>
            </div>

            <!-- Form Card -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 sm:p-8 shadow-xl backdrop-blur-sm">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Cliente -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Cliente Solicitante <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.cliente_id"
                                required
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-amber-500"
                            >
                                <option value="" disabled>Seleccione un cliente...</option>
                                <option v-for="c in clientes" :key="c.id" :value="c.id">
                                    {{ c.nombres }} {{ c.apellidos }} (Doc: {{ c.numero_documento }})
                                </option>
                            </select>
                        </div>

                        <!-- Cuenta de Desembolso -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Cuenta de Desembolso Asociada <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.cuenta_desembolso_id"
                                required
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-amber-500"
                            >
                                <option value="" disabled>Seleccione la cuenta bancaria del cliente para el abono...</option>
                                <template v-if="clienteSeleccionado && clienteSeleccionado.cuentas.length > 0">
                                    <option v-for="cta in clienteSeleccionado.cuentas" :key="cta.id" :value="cta.id">
                                        N° {{ cta.numero_cuenta }} ({{ cta.tipo }}) — Saldo actual: {{ money(cta.saldo) }}
                                    </option>
                                </template>
                            </select>
                            <p v-if="clienteSeleccionado && clienteSeleccionado.cuentas.length === 0" class="mt-2 text-xs text-amber-400">
                                El cliente seleccionado no tiene cuentas bancarias abiertas.
                                <Link :href="`/cuentas/create?cliente_id=${clienteSeleccionado.id}`" class="underline font-bold ml-1 text-blue-400">
                                    Abrir una cuenta primero →
                                </Link>
                            </p>
                        </div>

                        <!-- Tipo de Préstamo -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Tipo de Préstamo <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.tipo"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-amber-500"
                            >
                                <option value="personal">Préstamo Personal (Libre Disponibilidad)</option>
                                <option value="hipotecario">Préstamo Hipotecario (Vivienda)</option>
                                <option value="comercial">Préstamo Comercial (Empresarial)</option>
                            </select>
                        </div>

                        <!-- Monto Solicitado -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Monto Solicitado (BOB) <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model.number="form.monto_solicitado"
                                type="number"
                                min="500"
                                max="1000000"
                                step="500"
                                required
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 font-mono text-base font-bold text-white focus:outline-none focus:border-amber-500"
                            />
                        </div>

                        <!-- Tasa de Interés Anual -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Tasa de Interés Anual (%) <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model.number="form.tasa_interes"
                                type="number"
                                min="1"
                                max="50"
                                step="0.25"
                                required
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 font-mono text-sm text-white focus:outline-none focus:border-amber-500"
                            />
                        </div>

                        <!-- Plazo en Meses -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Plazo en Meses <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model.number="form.plazo_meses"
                                type="number"
                                min="3"
                                max="360"
                                required
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 font-mono text-sm text-white focus:outline-none focus:border-amber-500"
                            />
                        </div>

                        <!-- Evaluación Financiera (RF-021) -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Ingreso Mensual Declarado (Bs.) <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model.number="form.ingreso_mensual"
                                type="number"
                                min="100"
                                step="100"
                                required
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 font-mono text-sm text-emerald-400 focus:outline-none focus:border-amber-500"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Egresos / Gastos Mensuales (Bs.) <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model.number="form.egreso_mensual"
                                type="number"
                                min="0"
                                step="100"
                                required
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 font-mono text-sm text-rose-400 focus:outline-none focus:border-amber-500"
                            />
                        </div>

                        <!-- Destino del Crédito -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Destino del Crédito
                            </label>
                            <input
                                v-model="form.destino_credito"
                                type="text"
                                placeholder="Ej. Compra de inventario para negocio comercial"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-amber-500"
                            />
                        </div>
                    </div>

                    <!-- Panel de Precalificación Crediticia (RF-021) -->
                    <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="space-y-1">
                            <span class="text-xs text-slate-400 block uppercase">Cuota Mensual Estimada</span>
                            <div class="font-mono text-2xl font-bold text-amber-400">
                                {{ money(cuotaEstimada) }}
                            </div>
                            <span class="text-[11px] text-slate-500">Amortización Francesa Fija</span>
                        </div>

                        <div class="space-y-1">
                            <span class="text-xs text-slate-400 block uppercase">Capacidad Máxima de Pago (40%)</span>
                            <div class="font-mono text-2xl font-bold text-emerald-400">
                                {{ money(capacidadPago) }}
                            </div>
                            <span class="text-[11px] text-slate-500">40% del ingreso disponible</span>
                        </div>

                        <div class="flex items-center justify-start sm:justify-end">
                            <div
                                :class="esApto ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' : 'bg-rose-500/10 text-rose-400 border-rose-500/30'"
                                class="rounded-xl border p-3 flex items-center gap-2 text-xs font-bold"
                            >
                                <CheckCircle2 v-if="esApto" class="h-5 w-5" />
                                <AlertCircle v-else class="h-5 w-5" />
                                <div>
                                    <div>{{ esApto ? 'Precalificación Favorable' : 'Riesgo Elevado' }}</div>
                                    <div class="text-[10px] font-normal text-slate-300">
                                        {{ esApto ? 'Cuota dentro del margen de pago' : 'Cuota excede el 40% del ingreso' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                        <Link
                            href="/prestamos"
                            class="rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-400 hover:text-white transition-colors"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing || !form.cuenta_desembolso_id"
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-amber-500/20 hover:from-amber-500 hover:to-orange-500 focus:outline-none disabled:opacity-50 transition-all cursor-pointer"
                        >
                            <Check class="h-4 w-4" />
                            {{ form.processing ? 'Registrando...' : 'Radicar Solicitud de Crédito' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
