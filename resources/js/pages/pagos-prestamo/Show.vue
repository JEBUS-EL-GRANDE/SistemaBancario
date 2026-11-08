<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import {
    CheckCircle2,
    CreditCard,
    DollarSign,
    Plus,
    Printer,
    ShieldCheck,
} from 'lucide-vue-next';

interface Cliente {
    nombres: string;
    apellidos: string;
    numero_documento: string;
}

interface Cuota {
    numero_cuota: number;
}

interface Prestamo {
    codigo: string;
    tipo: string;
    cliente: Cliente;
}

interface User {
    name: string;
}

interface Pago {
    id: number;
    referencia: string;
    monto: number;
    tipo: string;
    capital_abonado: number;
    interes_abonado: number;
    saldo_pendiente_previo: number;
    saldo_pendiente_posterior: number;
    created_at: string;
    prestamo: Prestamo;
    cuota: Cuota | null;
    cajero: User | null;
}

const props = defineProps<{
    pago: Pago;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Pago Préstamo', href: '/pagos-prestamo/create' },
    { title: props.pago.referencia, href: `/pagos-prestamo/${props.pago.id}` },
];

const money = (n: number) => `Bs. ${Number(n || 0).toLocaleString('es-BO', { minimumFractionDigits: 2 })}`;

const imprimir = () => {
    window.print();
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Recibo Pago: ${pago.referencia}`" />

        <div class="mx-auto max-w-2xl space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Acciones Superiores -->
            <div class="flex items-center justify-between no-print">
                <Link
                    href="/dashboard"
                    class="text-xs font-semibold text-slate-400 hover:text-white"
                >
                    ← Volver al Dashboard
                </Link>
                <div class="flex gap-2">
                    <button
                        @click="imprimir"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 px-3.5 py-2 text-xs font-semibold text-slate-200 border border-slate-700 transition-all cursor-pointer"
                    >
                        <Printer class="h-4 w-4" />
                        Imprimir Recibo
                    </button>
                    <Link
                        href="/pagos-prestamo/create"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 px-3.5 py-2 text-xs font-semibold text-white shadow-md shadow-blue-500/20 transition-all cursor-pointer"
                    >
                        <Plus class="h-4 w-4" />
                        Nuevo Cobro
                    </Link>
                </div>
            </div>

            <!-- Ticket / Comprobante de Pago de Cuota -->
            <div class="rounded-3xl border border-slate-800 bg-slate-900/90 p-8 shadow-2xl backdrop-blur-md relative overflow-hidden print:border-none print:shadow-none print:bg-white print:text-black">
                <div class="flex flex-col items-center text-center pb-6 border-b border-slate-800 print:border-black">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-500/10 text-blue-400 mb-3 border border-blue-500/20 print:bg-transparent">
                        <CheckCircle2 class="h-7 w-7" />
                    </div>
                    <h2 class="text-xl font-bold tracking-tight text-white print:text-black">
                        BANCO CONTINENTAL S.A.
                    </h2>
                    <span class="text-xs text-slate-400 uppercase tracking-widest mt-1 print:text-gray-600">
                        Comprobante de Pago de Crédito
                    </span>
                    <span class="font-mono text-xs text-blue-400 mt-2 font-bold px-2.5 py-1 rounded-full bg-blue-500/10 border border-blue-500/20">
                        {{ pago.referencia }}
                    </span>
                </div>

                <!-- Monto Pagado -->
                <div class="my-6 text-center py-4 rounded-2xl bg-slate-950/60 border border-slate-800 print:bg-gray-100 print:border-gray-300">
                    <span class="text-xs uppercase tracking-wider text-slate-400 block mb-1">Monto Cobrado</span>
                    <span class="font-mono text-4xl font-black text-blue-400 print:text-black">
                        {{ money(pago.monto) }}
                    </span>
                    <span class="text-xs text-slate-500 block mt-1 capitalize font-medium">
                        Modalidad: {{ pago.tipo.replace('_', ' ') }} {{ pago.cuota ? `(Cuota #${pago.cuota.numero_cuota})` : '' }}
                    </span>
                </div>

                <!-- Desglose de Operación -->
                <div class="space-y-3 text-xs divide-y divide-slate-800/80 print:divide-gray-300">
                    <div class="flex justify-between py-2">
                        <span class="text-slate-400">Código de Crédito:</span>
                        <span class="font-mono font-bold text-white print:text-black">{{ pago.prestamo.codigo }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-slate-400">Titular del Crédito:</span>
                        <span class="font-semibold text-slate-200 print:text-black">{{ pago.prestamo.cliente.nombres }} {{ pago.prestamo.cliente.apellidos }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-slate-400">Abono a Capital:</span>
                        <span class="font-mono text-emerald-400 font-semibold print:text-black">{{ money(pago.capital_abonado) }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-slate-400">Intereses Pagados:</span>
                        <span class="font-mono text-amber-400 font-semibold print:text-black">{{ money(pago.interes_abonado) }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-slate-400">Saldo Anterior:</span>
                        <span class="font-mono text-slate-400 print:text-black">{{ money(pago.saldo_pendiente_previo) }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-slate-400">Nuevo Saldo Pendiente:</span>
                        <span class="font-mono font-bold text-rose-400 print:text-black">{{ money(pago.saldo_pendiente_posterior) }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-slate-400">Fecha y Hora:</span>
                        <span class="text-slate-300 print:text-black">{{ new Date(pago.created_at).toLocaleString('es-BO') }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-slate-400">Cajero Responsable:</span>
                        <span class="text-slate-300 print:text-black">{{ pago.cajero?.name || 'Sistema Central' }}</span>
                    </div>
                </div>

                <!-- Pie de Seguridad -->
                <div class="mt-8 pt-6 border-t border-slate-800 flex items-center justify-between text-[11px] text-slate-500 print:border-black print:text-gray-600">
                    <div class="flex items-center gap-1.5">
                        <ShieldCheck class="h-4 w-4 text-blue-400" />
                        <span>Comprobante Válido para Descargo Financiero</span>
                    </div>
                    <span class="font-mono">PAG-OP-{{ pago.id }}</span>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
