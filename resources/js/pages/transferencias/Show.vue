<script setup lang="ts">
import ComprobanteBancario from '@/components/ComprobanteBancario.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowLeftRight,
    CheckCircle2,
    Plus,
} from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    transferencia: {
        id: number;
        referencia: string;
        monto: number;
        canal: string;
        metodo_autenticacion: string;
        motivo: string;
        estado: string;
        created_at: string;
        saldo_previo_origen: number;
        saldo_posterior_origen: number;
        saldo_previo_destino: number;
        saldo_posterior_destino: number;
        cuenta_origen: {
            numero_cuenta: string;
            tipo: string;
            cliente: {
                nombres: string;
                apellidos: string;
                numero_documento: string;
            };
        };
        cuenta_destino: {
            numero_cuenta: string;
            tipo: string;
            cliente: {
                nombres: string;
                apellidos: string;
                numero_documento: string;
            };
        };
        usuario?: {
            name: string;
            apellidos: string;
        };
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Transferencias', href: '/transferencias' },
    { title: props.transferencia.referencia, href: `/transferencias/${props.transferencia.id}` },
];

const fechaHora = computed(() => {
    return new Date(props.transferencia.created_at).toLocaleString('es-BO', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
});

const cajeroNombre = computed(() => {
    return props.transferencia.usuario
        ? `${props.transferencia.usuario.name} ${props.transferencia.usuario.apellidos}`
        : 'Sistema Central';
});

const datosComprobante = computed(() => [
    {
        label: 'Cuenta Origen (Débito)',
        valor: `${props.transferencia.cuenta_origen.numero_cuenta} (${props.transferencia.cuenta_origen.cliente.nombres} ${props.transferencia.cuenta_origen.cliente.apellidos})`,
    },
    {
        label: 'Doc. Titular Origen',
        valor: props.transferencia.cuenta_origen.cliente.numero_documento,
    },
    {
        label: 'Cuenta Destino (Crédito)',
        valor: `${props.transferencia.cuenta_destino.numero_cuenta} (${props.transferencia.cuenta_destino.cliente.nombres} ${props.transferencia.cuenta_destino.cliente.apellidos})`,
    },
    {
        label: 'Doc. Titular Destino',
        valor: props.transferencia.cuenta_destino.cliente.numero_documento,
    },
    {
        label: 'Concepto / Motivo',
        valor: props.transferencia.motivo || 'Traspaso de fondos entre cuentas',
    },
    {
        label: 'Método de Autenticación',
        valor: props.transferencia.metodo_autenticacion.toUpperCase(),
    },
    {
        label: 'Estado de la Transacción',
        valor: props.transferencia.estado.toUpperCase(),
    },
]);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Comprobante Transferencia ${transferencia.referencia} - Banco Continental`" />

        <div class="mx-auto max-w-4xl space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Header con Navegación -->
            <div class="no-print flex items-center justify-between">
                <Link
                    href="/transferencias"
                    class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-3.5 py-2 text-sm font-medium text-slate-300 hover:bg-slate-700 hover:text-white transition-all"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Volver a Transferencias
                </Link>

                <Link
                    href="/transferencias/create"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 transition-all cursor-pointer"
                >
                    <Plus class="h-4 w-4" />
                    Nueva Transferencia
                </Link>
            </div>

            <!-- Comprobante Oficial Bancario y Ticket Térmico -->
            <ComprobanteBancario
                titulo="Comprobante de Transferencia de Fondos"
                :referencia="transferencia.referencia"
                :fecha-hora="fechaHora"
                tipo-operacion="Transferencia Bancaria"
                :canal="transferencia.canal"
                :monto="Number(transferencia.monto)"
                :cajero="cajeroNombre"
                :datos-adicionales="datosComprobante"
            />
        </div>
    </AppLayout>
</template>
