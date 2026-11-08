<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowLeft,
    Check,
    CheckCircle2,
    CreditCard,
    DollarSign,
    Search,
    Wallet,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Cliente {
    nombres: string;
    apellidos: string;
    numero_documento: string;
}

interface Cuota {
    id: number;
    numero_cuota: number;
    fecha_vencimiento: string;
    cuota_total: number;
    capital: number;
    interes: number;
    estado: string;
    monto_pagado: number;
}

interface Prestamo {
    id: number;
    codigo: string;
    tipo: string;
    monto_aprobado: number;
    cuota_mensual: number;
    saldo_pendiente: number;
    estado: string;
    cliente: Cliente;
    cuotas: Cuota[];
}

const props = defineProps<{
    prestamoSeleccionado?: Prestamo | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Pago de Préstamo', href: '/pagos-prestamo/create' },
];

const codigoBusqueda = ref(props.prestamoSeleccionado?.codigo || '');

const form = useForm({
    prestamo_id: props.prestamoSeleccionado?.id || '',
    tipo: 'cuota_mensual',
    monto: props.prestamoSeleccionado?.cuota_mensual || '',
    cuota_id: '',
});

const siguienteCuota = computed(() => {
    if (!props.prestamoSeleccionado?.cuotas) return null;
    return props.prestamoSeleccionado.cuotas.find((c) => c.estado !== 'pagada') || null;
});

watch(() => props.prestamoSeleccionado, (p) => {
    if (p) {
        form.prestamo_id = p.id;
        const prox = p.cuotas?.find((c) => c.estado !== 'pagada');
        if (prox) {
            form.cuota_id = String(prox.id);
            form.monto = String(prox.cuota_total - prox.monto_pagado);
        } else {
            form.monto = String(p.saldo_pendiente);
        }
    }
}, { immediate: true });

watch(() => form.tipo, (tipo) => {
    if (!props.prestamoSeleccionado) return;
    if (tipo === 'cuota_mensual' && siguienteCuota.value) {
        form.monto = String(siguienteCuota.value.cuota_total - siguienteCuota.value.monto_pagado);
        form.cuota_id = String(siguienteCuota.value.id);
    } else if (tipo === 'cancelacion_total') {
        form.monto = String(props.prestamoSeleccionado.saldo_pendiente);
        form.cuota_id = '';
    } else {
        form.cuota_id = '';
    }
});

const buscarPrestamo = () => {
    if (!codigoBusqueda.value) return;
    router.get('/pagos-prestamo/create', { codigo: codigoBusqueda.value }, { preserveState: true });
};

const money = (n: number) => `Bs. ${Number(n || 0).toLocaleString('es-BO', { minimumFractionDigits: 2 })}`;

const submit = () => {
    form.post('/pagos-prestamo');
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Cobro de Cuota de Préstamo - Banco Continental" />

        <div class="mx-auto max-w-4xl space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                        <CreditCard class="h-7 w-7 text-blue-400" />
                        Recepción y Cobro de Préstamos (RF-025)
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">
                        Pago de cuotas regulares, amortizaciones extraordinarias y cancelaciones anticipadas.
                    </p>
                </div>
                <Link
                    href="/dashboard"
                    class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-3.5 py-2 text-sm font-medium text-slate-300 hover:bg-slate-700 hover:text-white transition-all"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Dashboard
                </Link>
            </div>

            <!-- Buscador de Préstamo -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                    Código del Préstamo a Cobrar
                </label>
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <CreditCard class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" />
                        <input
                            v-model="codigoBusqueda"
                            type="text"
                            placeholder="Ej. PRE-XXXXXXXX"
                            @keyup.enter="buscarPrestamo"
                            class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 pl-11 pr-4 py-2.5 font-mono text-base text-white placeholder-slate-500 focus:outline-none focus:border-blue-500"
                        />
                    </div>
                    <button
                        type="button"
                        @click="buscarPrestamo"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 px-5 py-2.5 text-xs font-semibold text-white shadow-md shadow-blue-500/20 cursor-pointer"
                    >
                        <Search class="h-4 w-4" />
                        Buscar Crédito
                    </button>
                </div>
            </div>

            <!-- Datos del Préstamo y Formulario de Cobro -->
            <div v-if="prestamoSeleccionado" class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 sm:p-8 shadow-xl backdrop-blur-sm space-y-6">
                <!-- Ficha del Préstamo -->
                <div class="rounded-2xl bg-slate-950/80 border border-slate-800 p-5 grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <span class="text-xs text-slate-400 block uppercase">Titular</span>
                        <div class="font-bold text-white text-sm">
                            {{ prestamoSeleccionado.cliente.nombres }} {{ prestamoSeleccionado.cliente.apellidos }}
                        </div>
                        <div class="text-xs text-slate-500">Doc: {{ prestamoSeleccionado.cliente.numero_documento }}</div>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block uppercase">Saldo Pendiente</span>
                        <div class="font-mono text-xl font-bold text-rose-400">
                            {{ money(prestamoSeleccionado.saldo_pendiente) }}
                        </div>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block uppercase">Cuota Mensual</span>
                        <div class="font-mono text-xl font-bold text-amber-400">
                            {{ money(prestamoSeleccionado.cuota_mensual) }}
                        </div>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block uppercase">Próximo Vencimiento</span>
                        <div class="text-sm font-semibold text-slate-200">
                            {{ siguienteCuota ? `Cuota #${siguienteCuota.numero_cuota} (${siguienteCuota.fecha_vencimiento})` : 'Al día' }}
                        </div>
                    </div>
                </div>

                <!-- Formulario -->
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <!-- Tipo de Pago -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Modalidad del Pago <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.tipo"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500"
                            >
                                <option value="cuota_mensual">Pago de Cuota Mensual Regular</option>
                                <option value="anticipado">Amortización Extraordinaria (Adelanto a Capital)</option>
                                <option value="cancelacion_total">Cancelación Total del Crédito</option>
                            </select>
                        </div>

                        <!-- Monto a Cobrar -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Monto a Cobrar (BOB) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-400 text-lg">Bs.</span>
                                <input
                                    v-model="form.monto"
                                    type="number"
                                    min="1"
                                    step="0.50"
                                    required
                                    class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 pl-14 pr-4 py-2.5 font-mono text-xl font-bold text-emerald-400 focus:outline-none focus:border-blue-500"
                                />
                            </div>
                            <p v-if="form.errors.monto" class="mt-1 text-xs text-rose-400">{{ form.errors.monto }}</p>
                        </div>
                    </div>

                    <!-- Botón de Cobro -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                        <Link
                            href="/prestamos"
                            class="rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-400 hover:text-white"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing || !form.monto"
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 hover:from-blue-500 cursor-pointer disabled:opacity-50"
                        >
                            <Check class="h-4 w-4" />
                            {{ form.processing ? 'Registrando Cobro...' : 'Procesar Pago de Cuota' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
