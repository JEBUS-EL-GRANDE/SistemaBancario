<script setup lang="ts">
import BiometricScannerModal from '@/components/BiometricScannerModal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowLeft,
    ArrowLeftRight,
    ArrowRight,
    Check,
    CheckCircle2,
    DollarSign,
    Fingerprint,
    Search,
    ShieldCheck,
    UserCheck,
    Wallet,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

interface Props {
    cuentaPrevia?: {
        numero_cuenta: string;
        tipo: string;
        saldo: number;
        estado: string;
        cliente: {
            id: number;
            nombres: string;
            apellidos: string;
            numero_documento: string;
        };
    } | null;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Transferencias', href: '/transferencias' },
    { title: 'Nueva Transferencia', href: '/transferencias/create' },
];

const form = useForm({
    cuenta_origen: props.cuentaPrevia?.numero_cuenta || '',
    cuenta_destino: '',
    monto: '',
    canal: 'ventanilla',
    metodo_autenticacion: 'identidad',
    motivo: '',
    biometric_token: '',
});

// Información en vivo de Cuentas
const infoOrigen = ref<{
    id?: number;
    titular: string;
    documento: string;
    tipo: string;
    saldo: number;
    estado: string;
    cliente_id?: number;
} | null>(null);

const infoDestino = ref<{
    titular: string;
    documento: string;
    tipo: string;
    estado: string;
} | null>(null);

const buscandoOrigen = ref(false);
const errorOrigen = ref('');
const buscandoDestino = ref(false);
const errorDestino = ref('');

// Modal Biométrico
const showBiometricModal = ref(false);
const huellaVerificada = ref(false);
const tokenBiometrico = ref('');

const buscarCuentaOrigen = async () => {
    if (!form.cuenta_origen) return;
    buscandoOrigen.value = true;
    errorOrigen.value = '';
    infoOrigen.value = null;

    try {
        const res = await fetch(`/api/cuentas/buscar?numero_cuenta=${encodeURIComponent(form.cuenta_origen)}`);
        if (!res.ok) throw new Error('La cuenta de origen no existe.');
        const data = await res.json();
        infoOrigen.value = {
            id: data.id,
            titular: data.titular,
            documento: data.documento_titular,
            tipo: data.tipo,
            saldo: Number(data.saldo),
            estado: data.estado,
            cliente_id: data.cliente_id,
        };
    } catch (e: any) {
        errorOrigen.value = e.message || 'Error al consultar cuenta origen';
    } finally {
        buscandoOrigen.value = false;
    }
};

const buscarCuentaDestino = async () => {
    if (!form.cuenta_destino) return;
    if (form.cuenta_destino === form.cuenta_origen) {
        errorDestino.value = 'La cuenta de destino no puede ser igual a la de origen.';
        infoDestino.value = null;
        return;
    }
    buscandoDestino.value = true;
    errorDestino.value = '';
    infoDestino.value = null;

    try {
        const res = await fetch(`/api/cuentas/buscar?numero_cuenta=${encodeURIComponent(form.cuenta_destino)}`);
        if (!res.ok) throw new Error('La cuenta de destino no existe.');
        const data = await res.json();
        infoDestino.value = {
            titular: data.titular,
            documento: data.documento_titular,
            tipo: data.tipo,
            estado: data.estado,
        };
    } catch (e: any) {
        errorDestino.value = e.message || 'Error al consultar cuenta destino';
    } finally {
        buscandoDestino.value = false;
    }
};

onMounted(() => {
    if (props.cuentaPrevia) {
        infoOrigen.value = {
            titular: `${props.cuentaPrevia.cliente.nombres} ${props.cuentaPrevia.cliente.apellidos}`,
            documento: props.cuentaPrevia.cliente.numero_documento,
            tipo: props.cuentaPrevia.tipo,
            saldo: Number(props.cuentaPrevia.saldo),
            estado: props.cuentaPrevia.estado,
            cliente_id: props.cuentaPrevia.cliente.id,
        };
    }
});

const onBiometricVerified = (data: { token: string; matchScore: string; dedo: string }) => {
    tokenBiometrico.value = data.token;
    form.biometric_token = data.token;
    huellaVerificada.value = true;
    showBiometricModal.value = false;
};

const esMontoInvalido = computed(() => {
    const m = Number(form.monto) || 0;
    if (!infoOrigen.value) return false;
    return m <= 0 || m > infoOrigen.value.saldo;
});

const money = (n: number) => `Bs. ${Number(n || 0).toLocaleString('es-BO', { minimumFractionDigits: 2 })}`;

const submit = () => {
    form.post('/transferencias');
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Procesar Transferencia - Banco Continental" />

        <div class="mx-auto max-w-4xl space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                        <ArrowLeftRight class="h-7 w-7 text-emerald-400" />
                        Transferencia de Fondos entre Cuentas
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">
                        Traspaso inmediato con validación de titularidad de origen y destino.
                    </p>
                </div>
                <Link
                    href="/transferencias"
                    class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-3.5 py-2 text-sm font-medium text-slate-300 hover:bg-slate-700 hover:text-white transition-all"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Historial
                </Link>
            </div>

            <!-- Form Card -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 sm:p-8 shadow-xl backdrop-blur-sm">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Paso 1: Cuenta de Origen y Cuenta de Destino -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Origen -->
                        <div class="space-y-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                                1. Cuenta de Origen <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <Wallet class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" />
                                    <input
                                        v-model="form.cuenta_origen"
                                        type="text"
                                        required
                                        placeholder="N° de cuenta débito..."
                                        class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 pl-11 pr-3 py-2.5 font-mono text-sm text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500"
                                    />
                                </div>
                                <button
                                    type="button"
                                    @click="buscarCuentaOrigen"
                                    :disabled="buscandoOrigen || !form.cuenta_origen"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 px-3.5 py-2 text-xs font-semibold text-slate-200 border border-slate-700 cursor-pointer disabled:opacity-50"
                                >
                                    <Search class="h-4 w-4" />
                                    {{ buscandoOrigen ? '...' : 'Consultar' }}
                                </button>
                            </div>

                            <!-- Tarjeta de Saldo Origen -->
                            <div v-if="infoOrigen" class="rounded-xl bg-slate-950/80 border border-slate-800 p-3.5 space-y-2 text-xs mt-2">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="text-[10px] text-slate-500 uppercase">Titular Origen</div>
                                        <div class="font-bold text-white text-sm">{{ infoOrigen.titular }}</div>
                                        <div class="text-slate-400">Doc: {{ infoOrigen.documento }}</div>
                                    </div>
                                    <span class="rounded-md bg-emerald-500/10 px-2 py-0.5 text-[10px] text-emerald-400 font-bold capitalize">
                                        {{ infoOrigen.estado }}
                                    </span>
                                </div>
                                <div class="pt-2 border-t border-slate-800 flex justify-between items-center">
                                    <span class="text-slate-400">Saldo Disponible:</span>
                                    <span class="font-mono text-base font-black text-emerald-400">{{ money(infoOrigen.saldo) }}</span>
                                </div>
                            </div>

                            <div v-else-if="errorOrigen" class="rounded-xl bg-rose-950/30 border border-rose-500/30 p-2.5 text-xs text-rose-300 flex items-center gap-2">
                                <AlertCircle class="h-4 w-4 text-rose-400 shrink-0" />
                                <span>{{ errorOrigen }}</span>
                            </div>
                            <p v-if="form.errors.cuenta_origen" class="text-xs text-rose-400">{{ form.errors.cuenta_origen }}</p>
                        </div>

                        <!-- Destino -->
                        <div class="space-y-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                                2. Cuenta de Destino <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <Wallet class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" />
                                    <input
                                        v-model="form.cuenta_destino"
                                        type="text"
                                        required
                                        placeholder="N° de cuenta crédito..."
                                        class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 pl-11 pr-3 py-2.5 font-mono text-sm text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500"
                                    />
                                </div>
                                <button
                                    type="button"
                                    @click="buscarCuentaDestino"
                                    :disabled="buscandoDestino || !form.cuenta_destino"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 px-3.5 py-2 text-xs font-semibold text-slate-200 border border-slate-700 cursor-pointer disabled:opacity-50"
                                >
                                    <Search class="h-4 w-4" />
                                    {{ buscandoDestino ? '...' : 'Verificar' }}
                                </button>
                            </div>

                            <!-- Tarjeta de Verificación Destino -->
                            <div v-if="infoDestino" class="rounded-xl bg-slate-950/80 border border-emerald-500/30 p-3.5 space-y-2 text-xs mt-2">
                                <div class="flex items-center gap-1.5 text-emerald-400 text-xs font-bold">
                                    <UserCheck class="h-4 w-4" /> Destinatario Confirmado
                                </div>
                                <div>
                                    <div class="font-bold text-white text-sm">{{ infoDestino.titular }}</div>
                                    <div class="text-slate-400">Doc: {{ infoDestino.documento }} • {{ infoDestino.tipo }}</div>
                                </div>
                            </div>

                            <div v-else-if="errorDestino" class="rounded-xl bg-rose-950/30 border border-rose-500/30 p-2.5 text-xs text-rose-300 flex items-center gap-2">
                                <AlertCircle class="h-4 w-4 text-rose-400 shrink-0" />
                                <span>{{ errorDestino }}</span>
                            </div>
                            <p v-if="form.errors.cuenta_destino" class="text-xs text-rose-400">{{ form.errors.cuenta_destino }}</p>
                        </div>
                    </div>

                    <!-- Paso 2: Importe y Motivo -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-800">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Importe a Transferir (BOB) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-400 text-lg">Bs.</span>
                                <input
                                    v-model="form.monto"
                                    type="number"
                                    min="1"
                                    step="1"
                                    required
                                    placeholder="0.00"
                                    class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 pl-14 pr-4 py-3 font-mono text-2xl font-bold text-emerald-400 placeholder-slate-600 focus:outline-none focus:border-emerald-500"
                                />
                            </div>

                            <!-- Botones rápidos -->
                            <div class="flex flex-wrap gap-2 mt-2">
                                <button
                                    v-for="val in [50, 100, 200, 500, 1000]"
                                    :key="val"
                                    type="button"
                                    @click="form.monto = String(val)"
                                    class="rounded-lg bg-slate-800 hover:bg-slate-700 px-2.5 py-1 text-xs font-mono font-medium text-slate-300 border border-slate-700 cursor-pointer"
                                >
                                    {{ val }} Bs
                                </button>
                            </div>
                            <p v-if="form.errors.monto" class="mt-1 text-xs text-rose-400">{{ form.errors.monto }}</p>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                    Canal de Operación
                                </label>
                                <select
                                    v-model="form.canal"
                                    class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-xs text-white focus:outline-none focus:border-emerald-500"
                                >
                                    <option value="ventanilla">Ventanilla de Caja</option>
                                    <option value="banca_web">Banca por Internet (Web)</option>
                                    <option value="atm">Terminal ATM</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                    Concepto / Motivo de Transferencia
                                </label>
                                <input
                                    v-model="form.motivo"
                                    type="text"
                                    placeholder="Ej. Pago de servicios, cuota mensual, etc."
                                    class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Paso 3: Método de Autenticación y Validación Biométrica -->
                    <div class="pt-4 border-t border-slate-800 space-y-4">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                            Método de Autenticación del Titular Origen <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <select
                                v-model="form.metodo_autenticacion"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-xs text-white focus:outline-none focus:border-emerald-500"
                            >
                                <option value="identidad">Validación de Cédula de Identidad en Ventanilla</option>
                                <option value="biometria">Autenticación Biométrica (Huella Dactilar WebAuthn)</option>
                                <option value="tarjeta_pin">Tarjeta de Débito + PIN</option>
                                <option value="2fa">Código Temporal 2FA</option>
                            </select>

                            <!-- Botón de Escaneo Biométrico si se selecciona biometría -->
                            <div v-if="form.metodo_autenticacion === 'biometria'">
                                <button
                                    v-if="!huellaVerificada"
                                    type="button"
                                    @click="showBiometricModal = true"
                                    :disabled="!infoOrigen"
                                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600/20 border border-emerald-500/40 hover:bg-emerald-600/30 px-4 py-2.5 text-xs font-bold text-emerald-300 transition-all cursor-pointer disabled:opacity-50"
                                >
                                    <Fingerprint class="h-4 w-4" />
                                    Escanear Huella Dactilar del Titular
                                </button>
                                <div
                                    v-else
                                    class="rounded-xl bg-emerald-950/40 border border-emerald-500/40 p-2.5 text-xs text-emerald-300 flex items-center justify-between"
                                >
                                    <div class="flex items-center gap-2">
                                        <CheckCircle2 class="h-4 w-4 text-emerald-400" />
                                        <span>Huella Verificada con Éxito</span>
                                    </div>
                                    <span class="font-mono text-[10px] text-emerald-400">OK</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Envío -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                        <Link
                            href="/transferencias"
                            class="rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-400 hover:text-white transition-colors"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing || !form.cuenta_origen || !form.cuenta_destino || !form.monto || esMontoInvalido"
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 hover:from-emerald-500 hover:to-teal-500 focus:outline-none disabled:opacity-50 transition-all cursor-pointer"
                        >
                            <Check class="h-4 w-4" />
                            {{ form.processing ? 'Procesando...' : 'Confirmar Transferencia' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Biométrico -->
        <BiometricScannerModal
            :show="showBiometricModal"
            modo="verificar"
            :cliente-id="infoOrigen?.cliente_id"
            :cliente-nombre="infoOrigen?.titular"
            :cliente-documento="infoOrigen?.documento"
            @close="showBiometricModal = false"
            @verified="onBiometricVerified"
        />
    </AppLayout>
</template>
