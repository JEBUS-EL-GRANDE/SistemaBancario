<script setup lang="ts">
import ComprobanteBancario from '@/components/ComprobanteBancario.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowUpRight,
    Plus,
} from 'lucide-vue-next';
import { computed } from 'vue';

interface Cliente {
    nombres: string;
    apellidos: string;
    numero_documento: string;
}

interface Cuenta {
    numero_cuenta: string;
    tipo: string;
    cliente: Cliente;
}

interface User {
    name: string;
    apellidos?: string;
}

interface Retiro {
    id: number;
    referencia: string;
    monto: number;
    canal: string;
    metodo_autenticacion: string;
    estado: string;
    saldo_previo: number;
    saldo_posterior: number;
    notificacion_enviada: boolean;
    observacion: string | null;
    created_at: string;
    cuenta: Cuenta;
    cajero: User | null;
}

const props = defineProps<{
    retiro: Retiro;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Retiros', href: '/retiros' },
    { title: props.retiro.referencia, href: `/retiros/${props.retiro.id}` },
];

const fechaHora = computed(() => {
    return new Date(props.retiro.created_at).toLocaleString('es-BO', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
});

const cajeroNombre = computed(() => {
    return props.retiro.cajero ? `${props.retiro.cajero.name} ${props.retiro.cajero.apellidos || ''}`.trim() : 'ATM Terminal';
});

const datosComprobante = computed(() => [
    {
        label: 'Cuenta Debitada',
        valor: `${props.retiro.cuenta.numero_cuenta} (${props.retiro.cuenta.tipo})`,
    },
    {
        label: 'Titular de la Cuenta',
        valor: `${props.retiro.cuenta.cliente.nombres} ${props.retiro.cuenta.cliente.apellidos}`,
    },
    {
        label: 'Documento de Identidad',
        valor: props.retiro.cuenta.cliente.numero_documento,
    },
    {
        label: 'Método de Autenticación',
        valor: props.retiro.metodo_autenticacion === 'biometria'
            ? 'AUTENTICACIÓN BIOMÉTRICA (HUELLA DACTILAR)'
            : props.retiro.metodo_autenticacion.toUpperCase(),
    },
    {
        label: 'Saldo Anterior',
        valor: `Bs. ${Number(props.retiro.saldo_previo).toLocaleString('es-BO', { minimumFractionDigits: 2 })}`,
    },
    {
        label: 'Nuevo Saldo Disponible',
        valor: `Bs. ${Number(props.retiro.saldo_posterior).toLocaleString('es-BO', { minimumFractionDigits: 2 })}`,
    },
    ...(props.retiro.observacion ? [{ label: 'Observaciones / Registro', valor: props.retiro.observacion }] : []),
]);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Comprobante Retiro: ${retiro.referencia} - Banco Continental`" />

        <div class="mx-auto max-w-4xl space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Acciones Superiores -->
            <div class="no-print flex items-center justify-between">
                <Link
                    href="/retiros"
                    class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-3.5 py-2 text-sm font-medium text-slate-300 hover:bg-slate-700 hover:text-white transition-all"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Volver a Retiros
                </Link>

                <Link
                    href="/retiros/create"
                    class="inline-flex items-center gap-2 rounded-xl bg-amber-600 hover:bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-amber-500/20 transition-all cursor-pointer"
                >
                    <Plus class="h-4 w-4" />
                    Nuevo Retiro
                </Link>
            </div>

            <!-- Comprobante Oficial Bancario y Ticket Térmico -->
            <ComprobanteBancario
                titulo="Comprobante de Retiro de Fondos"
                :referencia="retiro.referencia"
                :fecha-hora="fechaHora"
                tipo-operacion="Retiro de Efectivo"
                :canal="retiro.canal"
                :monto="Number(retiro.monto)"
                :cajero="cajeroNombre"
                :observacion="retiro.observacion || ''"
                :datos-adicionales="datosComprobante"
            />
        </div>
    </AppLayout>
</template>
