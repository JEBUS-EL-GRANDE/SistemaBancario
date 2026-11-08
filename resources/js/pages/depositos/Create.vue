<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowDownLeft,
    ArrowLeft,
    Check,
    CheckCircle2,
    DollarSign,
    Search,
    UserCheck,
    Wallet,
} from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

interface Props {
    cuentaPrevia?: {
        numero_cuenta: string;
        tipo: string;
        saldo: number;
        estado: string;
        cliente: {
            nombres: string;
            apellidos: string;
            numero_documento: string;
        };
    } | null;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Depósitos', href: '/depositos' },
    { title: 'Nuevo Depósito', href: '/depositos/create' },
];

const form = useForm({
    numero_cuenta: props.cuentaPrevia?.numero_cuenta || '',
    tipo: 'efectivo',
    monto: '',
    canal: 'ventanilla',
    banco_emisor: '',
    numero_cheque: '',
    observacion: '',
});

const infoCuenta = ref<{
    titular: string;
    documento: string;
    tipo: string;
    saldo: number;
    estado: string;
} | null>(null);

const buscandoCuenta = ref(false);
const errorCuenta = ref('');

const buscarCuenta = async () => {
    if (!form.numero_cuenta) return;
    buscandoCuenta.value = true;
    errorCuenta.value = '';
    infoCuenta.value = null;

    try {
        const res = await fetch(`/api/cuentas/buscar?numero_cuenta=${encodeURIComponent(form.numero_cuenta)}`);
        if (!res.ok) {
            throw new Error('La cuenta bancaria no existe.');
        }
        const data = await res.json();
        infoCuenta.value = {
            titular: data.titular,
            documento: data.documento_titular,
            tipo: data.tipo,
            saldo: data.saldo,
            estado: data.estado,
        };
    } catch (e: any) {
        errorCuenta.value = e.message || 'Error al consultar la cuenta';
    } finally {
        buscandoCuenta.value = false;
    }
};

onMounted(() => {
    if (props.cuentaPrevia) {
        infoCuenta.value = {
            titular: `${props.cuentaPrevia.cliente.nombres} ${props.cuentaPrevia.cliente.apellidos}`,
            documento: props.cuentaPrevia.cliente.numero_documento,
            tipo: props.cuentaPrevia.tipo,
            saldo: props.cuentaPrevia.saldo,
            estado: props.cuentaPrevia.estado,
        };
    }
});

const addMonto = (val: number) => {
    const current = Number(form.monto) || 0;
    form.monto = String(current + val);
};

const submit = () => {
    form.post('/depositos');
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Registrar Depósito - Ventanilla Bancaria" />

        <div class="mx-auto max-w-4xl space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                        <ArrowDownLeft class="h-7 w-7 text-emerald-400" />
                        Registro de Depósito
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">
                        Acreditación inmediata de fondos en ventanilla o ATM con verificación de titular (RF-009 al RF-013).
                    </p>
                </div>
                <Link
                    href="/depositos"
                    class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-3.5 py-2 text-sm font-medium text-slate-300 hover:bg-slate-700 hover:text-white transition-all"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Historial
                </Link>
            </div>

            <!-- Form Card -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 sm:p-8 shadow-xl backdrop-blur-sm">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Paso 1: Número de Cuenta y Verificación en Vivo (RF-012) -->
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                            Número de Cuenta Destino <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <Wallet class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" />
                                <input
                                    v-model="form.numero_cuenta"
                                    type="text"
                                    required
                                    placeholder="Ej. 1004567890"
                                    class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 pl-11 pr-4 py-2.5 font-mono text-base text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                />
                            </div>
                            <button
                                type="button"
                                @click="buscarCuenta"
                                :disabled="buscandoCuenta || !form.numero_cuenta"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 px-4 py-2.5 text-xs font-semibold text-slate-200 border border-slate-700 cursor-pointer disabled:opacity-50"
                            >
                                <Search class="h-4 w-4" />
                                {{ buscandoCuenta ? 'Consultando...' : 'Verificar Titular' }}
                            </button>
                        </div>
                        <p v-if="form.errors.numero_cuenta" class="text-xs text-rose-400">{{ form.errors.numero_cuenta }}</p>

                        <!-- Tarjeta de Confirmación de Titular (RF-012) -->
                        <div v-if="infoCuenta" class="rounded-xl bg-emerald-950/30 border border-emerald-500/30 p-4 mt-3 flex items-start gap-3">
                            <CheckCircle2 class="h-5 w-5 text-emerald-400 mt-0.5 shrink-0" />
                            <div class="space-y-1 text-xs">
                                <div class="font-bold text-white text-sm">
                                    {{ infoCuenta.titular }}
                                </div>
                                <div class="text-slate-300 flex flex-wrap gap-x-4 gap-y-1">
                                    <span>C.I. / Documento: <strong class="text-white">{{ infoCuenta.documento }}</strong></span>
                                    <span>Tipo: <strong class="text-white capitalize">Cuenta de {{ infoCuenta.tipo }}</strong></span>
                                    <span>Estado: <strong :class="infoCuenta.estado === 'activa' ? 'text-emerald-400' : 'text-rose-400'">{{ infoCuenta.estado }}</strong></span>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="errorCuenta" class="rounded-xl bg-rose-950/30 border border-rose-500/30 p-3 mt-3 flex items-center gap-2 text-xs text-rose-300">
                            <AlertCircle class="h-4 w-4 text-rose-400 shrink-0" />
                            {{ errorCuenta }}
                        </div>
                    </div>

                    <!-- Paso 2: Detalles de la Operación -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4 border-t border-slate-800">
                        <!-- Tipo de Depósito -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Modalidad de Ingreso <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.tipo"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                            >
                                <option value="efectivo">Efectivo en Ventanilla</option>
                                <option value="cheque">Cheque Bancario</option>
                                <option value="transferencia">Transferencia a Terceros</option>
                            </select>
                        </div>

                        <!-- Canal -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Canal de Recepción <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.canal"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                            >
                                <option value="ventanilla">Ventanilla de Caja</option>
                                <option value="atm">Terminal ATM / Autoservicio</option>
                            </select>
                        </div>

                        <!-- Si es Cheque: Datos Adicionales (RF-010) -->
                        <div v-if="form.tipo === 'cheque'" class="sm:col-span-2 rounded-xl bg-amber-950/20 border border-amber-500/30 p-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-amber-300 mb-1">
                                    Banco Emisor del Cheque <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    v-model="form.banco_emisor"
                                    type="text"
                                    placeholder="Ej. Banco Nacional de Bolivia"
                                    class="w-full rounded-xl bg-slate-900 border border-slate-700 px-4 py-2 text-sm text-white focus:outline-none focus:border-amber-500"
                                />
                                <p v-if="form.errors.banco_emisor" class="text-xs text-rose-400 mt-1">{{ form.errors.banco_emisor }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-amber-300 mb-1">
                                    Número de Cheque <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    v-model="form.numero_cheque"
                                    type="text"
                                    placeholder="Ej. CHQ-998877"
                                    class="w-full rounded-xl bg-slate-900 border border-slate-700 px-4 py-2 text-sm text-white font-mono focus:outline-none focus:border-amber-500"
                                />
                                <p v-if="form.errors.numero_cheque" class="text-xs text-rose-400 mt-1">{{ form.errors.numero_cheque }}</p>
                            </div>
                            <div class="sm:col-span-2 text-xs text-amber-400/90 leading-relaxed">
                                <strong>Fondos en Reserva:</strong> Los depósitos en cheque se acreditarán al saldo contable, permaneciendo retenidos hasta su compensación interbancaria (48 horas hábiles).
                            </div>
                        </div>

                        <!-- Monto a Depositar -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Monto a Depositar (BOB) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-400 text-lg">Bs.</span>
                                <input
                                    v-model="form.monto"
                                    type="number"
                                    min="1"
                                    step="0.50"
                                    required
                                    placeholder="0.00"
                                    class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 pl-14 pr-4 py-3 font-mono text-2xl font-bold text-emerald-400 placeholder-slate-600 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                />
                            </div>
                            <div class="flex flex-wrap gap-2 mt-2">
                                <button
                                    v-for="val in [50, 100, 200, 500, 1000]"
                                    :key="val"
                                    type="button"
                                    @click="addMonto(val)"
                                    class="rounded-lg bg-slate-800 hover:bg-slate-700 px-3 py-1 text-xs font-mono font-medium text-slate-300 border border-slate-700 cursor-pointer"
                                >
                                    +{{ val }} Bs
                                </button>
                            </div>
                            <p v-if="form.errors.monto" class="mt-1 text-xs text-rose-400">{{ form.errors.monto }}</p>
                        </div>

                        <!-- Observaciones -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">
                                Glosa / Observación (Opcional)
                            </label>
                            <input
                                v-model="form.observacion"
                                type="text"
                                placeholder="Ej. Depósito por venta de servicios / ahorro personal"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500"
                            />
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                        <Link
                            href="/depositos"
                            class="rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-400 hover:text-white transition-colors"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing || !form.numero_cuenta || !form.monto"
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 hover:from-emerald-500 hover:to-teal-500 focus:outline-none disabled:opacity-50 transition-all cursor-pointer"
                        >
                            <Check class="h-4 w-4" />
                            {{ form.processing ? 'Procesando...' : 'Confirmar y Procesar Depósito' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
