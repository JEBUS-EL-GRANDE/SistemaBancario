<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowDownLeft,
    ArrowLeft,
    ArrowUpRight,
    CreditCard,
    History,
    Lock,
    Shield,
    Unlock,
    User,
    Wallet,
} from 'lucide-vue-next';
import { ref } from 'vue';

interface Cliente {
    id: number;
    nombres: string;
    apellidos: string;
    numero_documento: string;
    email: string;
    telefono: string | null;
}

interface Tarjeta {
    id: number;
    numero_tarjeta: string;
    bin: string;
    fecha_vencimiento: string;
    estado: string;
    intentos_fallidos: number;
}

interface Movimiento {
    id: number;
    referencia: string;
    tipo: string;
    monto: number;
    saldo_anterior: number;
    saldo_nuevo: number;
    canal: string;
    descripcion: string;
    created_at: string;
}

interface Cuenta {
    id: number;
    numero_cuenta: string;
    tipo: string;
    moneda: string;
    saldo: number;
    saldo_contable: number;
    limite_retiro_diario: number;
    retiro_acumulado_hoy: number;
    estado: string;
    fecha_apertura: string;
    cliente: Cliente;
    tarjeta: Tarjeta | null;
    movimientos: Movimiento[];
}

const props = defineProps<{
    cuenta: Cuenta;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Cuentas Bancarias', href: '/cuentas' },
    { title: props.cuenta.numero_cuenta, href: `/cuentas/${props.cuenta.id}` },
];

const money = (n: number) => `Bs. ${Number(n || 0).toLocaleString('es-BO', { minimumFractionDigits: 2 })}`;

const showModalEstado = ref(false);
const formEstado = useForm({
    estado: props.cuenta.estado,
    motivo: '',
});

const cambiarEstado = () => {
    formEstado.post(`/cuentas/${props.cuenta.id}/estado`, {
        onSuccess: () => {
            showModalEstado.value = false;
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Cuenta ${cuenta.numero_cuenta}`" />

        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="rounded-lg bg-emerald-500/10 px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-emerald-400 border border-emerald-500/20">
                            Cuenta de {{ cuenta.tipo }}
                        </span>
                        <span
                            :class="{
                                'bg-emerald-500/20 text-emerald-300 border-emerald-500/30': cuenta.estado === 'activa',
                                'bg-rose-500/20 text-rose-300 border-rose-500/30': cuenta.estado === 'bloqueada' || cuenta.estado === 'cancelada',
                                'bg-amber-500/20 text-amber-300 border-amber-500/30': cuenta.estado === 'inactiva'
                            }"
                            class="rounded-full px-2.5 py-0.5 text-xs font-semibold border capitalize"
                        >
                            {{ cuenta.estado }}
                        </span>
                    </div>
                    <h1 class="text-3xl font-black font-mono tracking-wider text-white mt-2">
                        {{ cuenta.numero_cuenta }}
                    </h1>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <Link
                        :href="`/depositos/create?cuenta=${cuenta.numero_cuenta}`"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-emerald-500/20 hover:from-emerald-500 hover:to-teal-500 transition-all cursor-pointer"
                    >
                        <ArrowDownLeft class="h-4 w-4" />
                        Registrar Depósito
                    </Link>
                    <Link
                        :href="`/retiros/create?cuenta=${cuenta.numero_cuenta}`"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-amber-500/20 hover:from-amber-500 hover:to-orange-500 transition-all cursor-pointer"
                    >
                        <ArrowUpRight class="h-4 w-4" />
                        Procesar Retiro
                    </Link>
                    <button
                        @click="showModalEstado = true"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 px-3.5 py-2.5 text-sm font-semibold text-slate-300 hover:text-white transition-all cursor-pointer"
                    >
                        <Lock class="h-4 w-4 text-slate-400" />
                        Cambiar Estado
                    </button>
                    <Link
                        href="/cuentas"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-slate-800 px-3.5 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-700 transition-all"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Volver
                    </Link>
                </div>
            </div>

            <!-- Balances y Métricas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-2xl border border-emerald-500/30 bg-emerald-950/20 p-5">
                    <div class="text-xs font-semibold uppercase tracking-wider text-emerald-400 mb-1">
                        Saldo Disponible
                    </div>
                    <div class="text-3xl font-black text-emerald-400 font-mono">
                        {{ money(cuenta.saldo) }}
                    </div>
                    <div class="text-xs text-slate-400 mt-2">Fondos libres para retiros inmediatos</div>
                </div>

                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">
                        Saldo Contable
                    </div>
                    <div class="text-2xl font-bold text-white font-mono">
                        {{ money(cuenta.saldo_contable) }}
                    </div>
                    <div class="text-xs text-slate-500 mt-2">Incluye cheques en reserva</div>
                </div>

                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">
                        Límite de Retiro Diario
                    </div>
                    <div class="text-2xl font-bold text-slate-200 font-mono">
                        {{ money(cuenta.limite_retiro_diario) }}
                    </div>
                    <div class="text-xs text-amber-400 mt-2">
                        Retirado hoy: {{ money(cuenta.retiro_acumulado_hoy) }}
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">
                        Fecha de Apertura
                    </div>
                    <div class="text-lg font-semibold text-slate-200">
                        {{ new Date(cuenta.fecha_apertura).toLocaleDateString('es-BO') }}
                    </div>
                    <div class="text-xs text-slate-500 mt-2">Moneda: {{ cuenta.moneda }}</div>
                </div>
            </div>

            <!-- Fila Titular y Tarjeta -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Titular -->
                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                            <User class="h-4 w-4 text-blue-400" />
                            Titular de la Cuenta
                        </h3>
                        <Link
                            :href="`/clientes/${cuenta.cliente.id}`"
                            class="text-xs font-semibold text-blue-400 hover:text-blue-300"
                        >
                            Ver Expediente Completo →
                        </Link>
                    </div>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between py-1.5 border-b border-slate-800/80">
                            <span class="text-slate-400">Nombre Completo:</span>
                            <span class="font-semibold text-white">{{ cuenta.cliente.nombres }} {{ cuenta.cliente.apellidos }}</span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-slate-800/80">
                            <span class="text-slate-400">Documento de Identidad:</span>
                            <span class="font-mono text-slate-200">{{ cuenta.cliente.numero_documento }}</span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-slate-800/80">
                            <span class="text-slate-400">Correo Electrónico:</span>
                            <span class="text-slate-200">{{ cuenta.cliente.email }}</span>
                        </div>
                        <div class="flex justify-between py-1.5">
                            <span class="text-slate-400">Teléfono:</span>
                            <span class="text-slate-200">{{ cuenta.cliente.telefono || 'Sin teléfono' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta de Débito Vinculada -->
                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300 flex items-center gap-2 mb-4">
                        <CreditCard class="h-4 w-4 text-indigo-400" />
                        Tarjeta de Débito Institucional
                    </h3>
                    <div v-if="cuenta.tarjeta" class="rounded-2xl bg-gradient-to-tr from-slate-950 via-indigo-950/40 to-slate-900 border border-indigo-500/20 p-5 shadow-lg relative overflow-hidden">
                        <div class="flex justify-between items-center mb-6">
                            <div class="text-xs font-bold text-indigo-400 tracking-wider">BANCO CONTINENTAL DÉBITO</div>
                            <span class="rounded bg-indigo-500/20 text-indigo-300 text-xs px-2 py-0.5 font-semibold capitalize">
                                {{ cuenta.tarjeta.estado }}
                            </span>
                        </div>
                        <div class="font-mono text-xl font-bold tracking-widest text-white mb-4">
                            **** **** **** {{ cuenta.tarjeta.numero_tarjeta.slice(-4) }}
                        </div>
                        <div class="flex justify-between text-xs text-slate-400">
                            <div>
                                <span class="block text-[10px] uppercase">Titular</span>
                                <span class="font-semibold text-slate-200 uppercase">{{ cuenta.cliente.nombres }} {{ cuenta.cliente.apellidos }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] uppercase">Vence</span>
                                <span class="font-semibold text-slate-200">{{ cuenta.tarjeta.fecha_vencimiento }}</span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-slate-500 text-sm">
                        Esta cuenta no tiene tarjeta de débito emitida.
                    </div>
                </div>
            </div>

            <!-- Movimientos Recientes (Libro Mayor) -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 backdrop-blur-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <History class="h-5 w-5 text-blue-400" />
                        Extracto de Movimientos Recientes
                    </h3>
                    <span class="text-xs text-slate-400">Últimas 30 transacciones</span>
                </div>

                <div v-if="cuenta.movimientos.length > 0" class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-300">
                        <thead class="bg-slate-950/60 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-800">
                            <tr>
                                <th class="px-4 py-3">Fecha y Hora</th>
                                <th class="px-4 py-3">Referencia</th>
                                <th class="px-4 py-3">Tipo</th>
                                <th class="px-4 py-3">Canal</th>
                                <th class="px-4 py-3">Descripción</th>
                                <th class="px-4 py-3 text-right">Monto</th>
                                <th class="px-4 py-3 text-right">Saldo Resultante</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80">
                            <tr v-for="m in cuenta.movimientos" :key="m.id" class="hover:bg-slate-800/30">
                                <td class="px-4 py-3 text-xs text-slate-400">
                                    {{ new Date(m.created_at).toLocaleString('es-BO') }}
                                </td>
                                <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-300">
                                    {{ m.referencia }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="m.tipo.includes('deposito') ? 'text-emerald-400 bg-emerald-500/10' : 'text-amber-400 bg-amber-500/10'"
                                        class="rounded px-2 py-0.5 text-xs font-semibold uppercase"
                                    >
                                        {{ m.tipo }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 capitalize text-xs text-slate-400">
                                    {{ m.canal }}
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-300">
                                    {{ m.descripcion }}
                                </td>
                                <td
                                    :class="m.tipo.includes('deposito') ? 'text-emerald-400 font-semibold' : 'text-rose-400 font-semibold'"
                                    class="px-4 py-3 text-right font-mono"
                                >
                                    {{ m.tipo.includes('deposito') ? '+' : '-' }}{{ money(m.monto) }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-white font-semibold">
                                    {{ money(m.saldo_nuevo) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="text-center py-8 text-slate-500 text-sm">
                    No se registran movimientos en esta cuenta bancaria aún.
                </div>
            </div>
        </div>

        <!-- Modal Cambiar Estado de Cuenta (RF-008) -->
        <div v-if="showModalEstado" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
            <div class="w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-2xl">
                <h3 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
                    <Shield class="h-5 w-5 text-amber-400" />
                    Cambiar Estado de la Cuenta
                </h3>
                <p class="text-xs text-slate-400 mb-4">
                    Al bloquear o cancelar una cuenta se restringirán todos los retiros y depósitos (RF-008).
                </p>

                <form @submit.prevent="cambiarEstado" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Nuevo Estado</label>
                        <select
                            v-model="formEstado.estado"
                            class="w-full rounded-xl bg-slate-950 border border-slate-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-blue-500"
                        >
                            <option value="activa">Activa</option>
                            <option value="bloqueada">Bloqueada</option>
                            <option value="inactiva">Inactiva</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Motivo del Cambio</label>
                        <input
                            v-model="formEstado.motivo"
                            type="text"
                            placeholder="Ej. Solicitud expresa del titular / orden judicial"
                            class="w-full rounded-xl bg-slate-950 border border-slate-700 px-3 py-2 text-sm text-white focus:outline-none focus:border-blue-500"
                        />
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                        <button
                            type="button"
                            @click="showModalEstado = false"
                            class="rounded-xl px-4 py-2 text-xs font-semibold text-slate-400 hover:text-white"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="formEstado.processing"
                            class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-500"
                        >
                            Guardar Estado
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
