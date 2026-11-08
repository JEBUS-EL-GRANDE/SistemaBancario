<script setup lang="ts">
import BiometricScannerModal from '@/components/BiometricScannerModal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowLeft,
    ArrowUpRight,
    Check,
    CheckCircle2,
    CreditCard,
    DollarSign,
    Fingerprint,
    Scan,
    Search,
    ShieldAlert,
    ShieldCheck,
    Wallet,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

interface Props {
    cuentaPrevia?: {
        numero_cuenta: string;
        tipo: string;
        saldo: number;
        limite_retiro_diario: number;
        retiro_acumulado_hoy: number;
        estado: string;
        cliente: {
            id?: number;
            nombres: string;
            apellidos: string;
            numero_documento: string;
        };
    } | null;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Retiros', href: '/retiros' },
    { title: 'Nuevo Retiro', href: '/retiros/create' },
];

const form = useForm({
    numero_cuenta: props.cuentaPrevia?.numero_cuenta || '',
    monto: '',
    canal: 'ventanilla',
    metodo_autenticacion: 'identidad',
    pin: '',
    biometric_token: '',
    observacion: '',
});

const infoCuenta = ref<{
    titular: string;
    documento: string;
    tipo: string;
    saldo: number;
    limiteDiario: number;
    acumuladoHoy: number;
    estado: string;
    cliente_id?: number;
} | null>(null);

const buscandoCuenta = ref(false);
const errorCuenta = ref('');

const showBiometricModal = ref(false);
const huellaVerificada = ref(false);
const tokenBiometrico = ref('');
const matchBiometrico = ref('');

const onBiometricVerified = (data: { token: string; matchScore: string; dedo: string }) => {
    tokenBiometrico.value = data.token;
    form.biometric_token = data.token;
    matchBiometrico.value = data.matchScore;
    huellaVerificada.value = true;
    showBiometricModal.value = false;
};

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
            saldo: Number(data.saldo),
            limiteDiario: Number(data.limite_retiro_diario),
            acumuladoHoy: Number(data.retiro_acumulado_hoy),
            estado: data.estado,
            cliente_id: data.cliente_id,
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
            saldo: Number(props.cuentaPrevia.saldo),
            limiteDiario: Number(props.cuentaPrevia.limite_retiro_diario),
            acumuladoHoy: Number(props.cuentaPrevia.retiro_acumulado_hoy),
            estado: props.cuentaPrevia.estado,
            cliente_id: props.cuentaPrevia.cliente?.id,
        };
    }
});

const disponibleHoy = computed(() => {
    if (!infoCuenta.value) return 0;
    return Math.max(0, infoCuenta.value.limiteDiario - infoCuenta.value.acumuladoHoy);
});

const esMontoInvalido = computed(() => {
    const m = Number(form.monto) || 0;
    if (!infoCuenta.value) return false;
    return m > infoCuenta.value.saldo || m > disponibleHoy.value;
});

const mensajeErrorMonto = computed(() => {
    const m = Number(form.monto) || 0;
    if (!infoCuenta.value) return '';
    if (m > infoCuenta.value.saldo) {
        return `Saldo insuficiente en la cuenta (Disponible: Bs. ${infoCuenta.value.saldo.toLocaleString('es-BO', { minimumFractionDigits: 2 })})`;
    }
    if (m > disponibleHoy.value) {
        return `Supera el límite diario de retiro restante (Disponible hoy: Bs. ${disponibleHoy.value.toLocaleString('es-BO', { minimumFractionDigits: 2 })})`;
    }
    return '';
});

const money = (n: number) => `Bs. ${Number(n || 0).toLocaleString('es-BO', { minimumFractionDigits: 2 })}`;

const onCanalChange = () => {
    if (form.canal === 'atm') {
        form.metodo_autenticacion = 'tarjeta_pin';
    }
};

const submit = () => {
    form.post('/retiros');
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Procesar Retiro - Ventanilla / ATM" />

        <div class="mx-auto max-w-4xl space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                        <ArrowUpRight class="h-7 w-7 text-amber-400" />
                        Procesar Retiro de Fondos
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">
                        Validación estricta de saldo, control de límites diarios y autenticación reforzada (RF-014 al RF-019).
                    </p>
                </div>
                <Link
                    href="/retiros"
                    class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-3.5 py-2 text-sm font-medium text-slate-300 hover:bg-slate-700 hover:text-white transition-all"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Historial
                </Link>
            </div>

            <!-- Form Card -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 sm:p-8 shadow-xl backdrop-blur-sm">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Paso 1: Cuenta y Validación de Fondos en Tiempo Real -->
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                            Número de Cuenta Origen <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <Wallet class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" />
                                <input
                                    v-model="form.numero_cuenta"
                                    type="text"
                                    required
                                    placeholder="Ej. 1004567890"
                                    class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 pl-11 pr-4 py-2.5 font-mono text-base text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                                />
                            </div>
                            <button
                                type="button"
                                @click="buscarCuenta"
                                :disabled="buscandoCuenta || !form.numero_cuenta"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 px-4 py-2.5 text-xs font-semibold text-slate-200 border border-slate-700 cursor-pointer disabled:opacity-50"
                            >
                                <Search class="h-4 w-4" />
                                {{ buscandoCuenta ? 'Consultando...' : 'Consultar Saldo' }}
                            </button>
                        </div>
                        <p v-if="form.errors.numero_cuenta" class="text-xs text-rose-400">{{ form.errors.numero_cuenta }}</p>

                        <!-- Tarjeta de Saldo y Titular (RF-014, RF-017, RF-018) -->
                        <div v-if="infoCuenta" class="rounded-2xl bg-slate-950/80 border border-slate-800 p-4 mt-3 grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="space-y-1">
                                <span class="text-slate-500 text-xs block">Titular Verificado</span>
                                <div class="font-bold text-white text-sm">{{ infoCuenta.titular }}</div>
                                <div class="text-xs text-slate-400">Doc: {{ infoCuenta.documento }}</div>
                            </div>
                            <div class="space-y-1">
                                <span class="text-slate-500 text-xs block">Saldo Disponible</span>
                                <div class="font-mono text-xl font-black text-emerald-400">
                                    {{ money(infoCuenta.saldo) }}
                                </div>
                                <div class="text-xs text-slate-400">Estado: <span class="capitalize text-white">{{ infoCuenta.estado }}</span></div>
                            </div>
                            <div class="space-y-1">
                                <span class="text-slate-500 text-xs block">Límite Diario Restante</span>
                                <div class="font-mono text-xl font-bold text-amber-400">
                                    {{ money(disponibleHoy) }}
                                </div>
                                <div class="text-xs text-slate-400">Límite Total: {{ money(infoCuenta.limiteDiario) }}</div>
                            </div>
                        </div>

                        <div v-else-if="errorCuenta" class="rounded-xl bg-rose-950/30 border border-rose-500/30 p-3 mt-3 flex items-center gap-2 text-xs text-rose-300">
                            <AlertCircle class="h-4 w-4 text-rose-400 shrink-0" />
                            {{ errorCuenta }}
                        </div>
                    </div>

                    <!-- Paso 2: Datos del Retiro -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4 border-t border-slate-800">
                        <!-- Canal de Retiro -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Canal de Retiro <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.canal"
                                @change="onCanalChange"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                            >
                                <option value="ventanilla">Ventanilla de Caja</option>
                                <option value="atm">Emulador Cajero Automático (ATM)</option>
                            </select>
                        </div>

                        <!-- Método de Autenticación (RF-016) -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Método de Autenticación <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.metodo_autenticacion"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                            >
                                <option value="identidad">Validación de Cédula de Identidad en Ventanilla</option>
                                <option value="tarjeta_pin">Tarjeta de Débito + NIP / PIN</option>
                                <option value="biometria">Validación Biométrica (Huella Dactilar WebAuthn)</option>
                                <option value="2fa">Código de Seguridad Temporal (2FA SMS)</option>
                            </select>
                        </div>

                        <!-- Si requiere PIN (RF-015) -->
                        <div v-if="form.canal === 'atm' || form.metodo_autenticacion === 'tarjeta_pin'" class="sm:col-span-2 rounded-xl bg-indigo-950/30 border border-indigo-500/30 p-4">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-indigo-300 mb-1">
                                Ingrese NIP / PIN de 4 dígitos de la Tarjeta <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative max-w-xs">
                                <input
                                    v-model="form.pin"
                                    type="password"
                                    maxlength="4"
                                    pattern="[0-9]{4}"
                                    placeholder="••••"
                                    class="w-full rounded-xl bg-slate-900 border border-slate-700 px-4 py-2 text-base text-white font-mono tracking-widest text-center focus:outline-none focus:border-indigo-500"
                                />
                            </div>
                            <span class="text-[11px] text-slate-400 mt-1 block">Validación con hash seguro. 3 intentos fallidos bloquearán la tarjeta.</span>
                            <p v-if="form.errors.pin" class="text-xs text-rose-400 mt-1">{{ form.errors.pin }}</p>
                        </div>

                        <!-- Si requiere Validación Biométrica (RF-016) -->
                        <div v-if="form.metodo_autenticacion === 'biometria'" class="sm:col-span-2 rounded-xl bg-emerald-950/30 border border-emerald-500/40 p-4">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-emerald-300">
                                    Validación Biométrica del Titular (Huella Dactilar FIDO2) <span class="text-rose-500">*</span>
                                </label>
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-400 border border-emerald-500/20">
                                    <ShieldCheck class="h-3 w-3" /> RF-016
                                </span>
                            </div>

                            <div v-if="!huellaVerificada" class="flex flex-col sm:flex-row items-center gap-3">
                                <button
                                    type="button"
                                    @click="showBiometricModal = true"
                                    :disabled="!infoCuenta"
                                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 px-5 py-2.5 text-xs font-bold text-white shadow-lg shadow-emerald-500/20 transition-all cursor-pointer disabled:opacity-50"
                                >
                                    <Fingerprint class="h-4 w-4" />
                                    Escanear Huella Dactilar en Vivo
                                </button>
                                <span class="text-xs text-slate-400">
                                    {{ infoCuenta ? 'Se solicitará lectura de huella para cotejar con el titular ' + infoCuenta.titular : 'Consulte primero la cuenta bancaria origen' }}
                                </span>
                            </div>

                            <div v-else class="rounded-xl bg-emerald-950/60 border border-emerald-500/50 p-3 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <CheckCircle2 class="h-5 w-5 text-emerald-400 shrink-0" />
                                    <div>
                                        <span class="font-bold text-white text-xs block">Huella Dactilar Verificada Positivamente</span>
                                        <span class="font-mono text-[11px] text-emerald-300">Token: {{ tokenBiometrico }} (Concordancia: {{ matchBiometrico }})</span>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    @click="showBiometricModal = true"
                                    class="text-[11px] font-medium text-emerald-300 underline hover:text-white cursor-pointer"
                                >
                                    Reescanear
                                </button>
                            </div>
                            <p v-if="form.errors.biometric_token" class="text-xs text-rose-400 mt-1">{{ form.errors.biometric_token }}</p>
                        </div>

                        <!-- Monto a Retirar (RF-017, RF-018) -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Monto a Retirar (BOB) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-400 text-lg">Bs.</span>
                                <input
                                    v-model="form.monto"
                                    type="number"
                                    min="10"
                                    step="10"
                                    required
                                    placeholder="0.00"
                                    class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 pl-14 pr-4 py-3 font-mono text-2xl font-bold text-amber-400 placeholder-slate-600 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                                />
                            </div>

                            <!-- Botones rápidos ATM -->
                            <div class="flex flex-wrap gap-2 mt-2">
                                <button
                                    v-for="val in [50, 100, 200, 500, 1000, 2000]"
                                    :key="val"
                                    type="button"
                                    @click="form.monto = String(val)"
                                    class="rounded-lg bg-slate-800 hover:bg-slate-700 px-3 py-1 text-xs font-mono font-medium text-slate-300 border border-slate-700 cursor-pointer"
                                >
                                    {{ val }} Bs
                                </button>
                            </div>

                            <!-- Error de saldo o límite -->
                            <div v-if="mensajeErrorMonto" class="rounded-xl bg-rose-950/40 border border-rose-500/40 p-3 mt-3 flex items-center gap-2 text-xs text-rose-300">
                                <AlertCircle class="h-4 w-4 text-rose-400 shrink-0" />
                                <strong>Validación Bancaria:</strong> {{ mensajeErrorMonto }}
                            </div>

                            <!-- Notificación de Seguridad (RF-019) -->
                            <div v-if="Number(form.monto) >= 2000" class="rounded-xl bg-amber-500/10 border border-amber-500/30 p-3 mt-3 flex items-center gap-2 text-xs text-amber-300">
                                <ShieldAlert class="h-4 w-4 text-amber-400 shrink-0" />
                                <span><strong>Alerta Transaccional (RF-019):</strong> Retiro superior a Bs. 2,000. Se registrará notificación automática de seguridad para el titular.</span>
                            </div>

                            <p v-if="form.errors.monto" class="mt-1 text-xs text-rose-400">{{ form.errors.monto }}</p>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                        <Link
                            href="/retiros"
                            class="rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-400 hover:text-white transition-colors"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing || !form.numero_cuenta || !form.monto || esMontoInvalido || (form.metodo_autenticacion === 'biometria' && !huellaVerificada)"
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-amber-500/20 hover:from-amber-500 hover:to-orange-500 focus:outline-none disabled:opacity-50 transition-all cursor-pointer"
                        >
                            <Check class="h-4 w-4" />
                            {{ form.processing ? 'Validando...' : 'Confirmar Retiro' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Biométrico -->
        <BiometricScannerModal
            :show="showBiometricModal"
            modo="verificar"
            :cliente-id="infoCuenta?.cliente_id"
            :cliente-nombre="infoCuenta?.titular"
            :cliente-documento="infoCuenta?.documento"
            @close="showBiometricModal = false"
            @verified="onBiometricVerified"
        />
    </AppLayout>
</template>
