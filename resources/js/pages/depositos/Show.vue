<script setup lang="ts">
import ComprobanteBancario from '@/components/ComprobanteBancario.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowDownLeft,
    ArrowLeft,
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
    username: string;
}

interface Deposito {
    id: number;
    referencia: string;
    tipo: string;
    monto: number;
    estado: string;
    canal: string;
    banco_emisor: string | null;
    numero_cheque: string | null;
    observacion: string | null;
    created_at: string;
    cuenta: Cuenta;
    cajero: User | null;
}

const props = defineProps<{
    deposito: Deposito;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Depósitos', href: '/depositos' },
    { title: props.deposito.referencia, href: `/depositos/${props.deposito.id}` },
];

const fechaHora = computed(() => {
    return new Date(props.deposito.created_at).toLocaleString('es-BO', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
});

const cajeroNombre = computed(() => {
    return props.deposito.cajero
        ? `${props.deposito.cajero.name} ${props.deposito.cajero.apellidos || ''}`.trim()
        : 'Caja Ventanilla';
});

const datosComprobante = computed(() => [
    {
        label: 'Cuenta Destino (Abono)',
        valor: `${props.deposito.cuenta.numero_cuenta} (${props.deposito.cuenta.tipo})`,
    },
    {
        label: 'Titular de la Cuenta',
        valor: `${props.deposito.cuenta.cliente.nombres} ${props.deposito.cuenta.cliente.apellidos}`,
    },
    {
        label: 'Documento Titular',
        valor: props.deposito.cuenta.cliente.numero_documento,
    },
    {
        label: 'Modalidad de Depósito',
        valor: props.deposito.tipo.toUpperCase(),
    },
    ...(props.deposito.numero_cheque ? [{ label: 'N° de Cheque', valor: props.deposito.numero_cheque }] : []),
    ...(props.deposito.banco_emisor ? [{ label: 'Banco Emisor Cheque', valor: props.deposito.banco_emisor }] : []),
    {
        label: 'Estado del Abono',
        valor: props.deposito.estado.toUpperCase(),
    },
    ...(props.deposito.observacion ? [{ label: 'Observaciones', valor: props.deposito.observacion }] : []),
]);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Comprobante Depósito: ${deposito.referencia} - Banco Continental`" />

        <div class="mx-auto max-w-4xl space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Acciones Superiores -->
            <div class="no-print flex items-center justify-between">
                <Link
                    href="/depositos"
                    class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-3.5 py-2 text-sm font-medium text-slate-300 hover:bg-slate-700 hover:text-white transition-all"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Volver a Depósitos
                </Link>

                <Link
                    href="/depositos/create"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 transition-all cursor-pointer"
                >
                    <Plus class="h-4 w-4" />
                    Nuevo Depósito
                </Link>
            </div>

            <!-- Comprobante Oficial Bancario y Ticket Térmico -->
            <ComprobanteBancario
                titulo="Comprobante Oficial de Depósito de Fondos"
                :referencia="deposito.referencia"
                :fecha-hora="fechaHora"
                tipo-operacion="Depósito Bancario"
                :canal="deposito.canal"
                :monto="Number(deposito.monto)"
                :cajero="cajeroNombre"
                :observacion="deposito.observacion || ''"
                :datos-adicionales="datosComprobante"
            />
        </div>
    </AppLayout>
</template>
