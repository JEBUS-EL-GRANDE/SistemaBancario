<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    CreditCard,
    Eye,
    Plus,
    Search,
    UserCheck,
    UserPlus,
    Users,
    Wallet,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface Cliente {
    id: number;
    nombres: string;
    apellidos: string;
    tipo_documento: string;
    numero_documento: string;
    email: string;
    telefono: string | null;
    estado: string;
    cuentas_count: number;
    prestamos_count: number;
    created_at: string;
}

interface ClientesPagination {
    data: Cliente[];
    current_page: number;
    last_page: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

const props = defineProps<{
    clientes: ClientesPagination;
    filters: { search?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Clientes', href: '/clientes' },
];

const search = ref(props.filters.search || '');

let timeout: number | null = null;
watch(search, (val) => {
    if (timeout) clearTimeout(timeout);
    timeout = window.setTimeout(() => {
        router.get('/clientes', { search: val }, { preserveState: true, replace: true });
    }, 400);
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Expediente de Clientes" />

        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                        <Users class="h-7 w-7 text-blue-400" />
                        Expediente de Clientes
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">
                        Gestión centralizada de titulares, cuentas y créditos asociados.
                    </p>
                </div>
                <Link
                    href="/clientes/create"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 hover:from-blue-500 hover:to-indigo-500 transition-all cursor-pointer"
                >
                    <UserPlus class="h-4 w-4" />
                    Registrar Nuevo Cliente
                </Link>
            </div>

            <!-- Buscador y Métricas -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 rounded-2xl bg-slate-900/60 p-4 border border-slate-800">
                <div class="relative flex-1 max-w-md">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Buscar por C.I., Nombres o Correo..."
                        class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 pl-9 pr-4 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    />
                </div>
                <div class="text-xs text-slate-400 flex items-center gap-2">
                    <UserCheck class="h-4 w-4 text-emerald-400" />
                    <span>Total registrados: <strong class="text-white">{{ clientes.total }}</strong></span>
                </div>
            </div>

            <!-- Tabla de Clientes -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/50 backdrop-blur-sm overflow-hidden shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-300">
                        <thead class="bg-slate-950/60 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-800">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold">Cliente</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Documento</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Contacto</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Cuentas</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Préstamos</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Estado</th>
                                <th scope="col" class="px-6 py-4 font-semibold text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80">
                            <tr
                                v-for="c in clientes.data"
                                :key="c.id"
                                class="hover:bg-slate-800/40 transition-colors"
                            >
                                <td class="px-6 py-4">
                                    <div class="font-medium text-white">{{ c.nombres }} {{ c.apellidos }}</div>
                                    <div class="text-xs text-slate-500">ID: #{{ c.id }}</div>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs">
                                    <span class="rounded bg-slate-800 px-2 py-1 text-slate-300 font-semibold uppercase">
                                        {{ c.tipo_documento }}: {{ c.numero_documento }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div>{{ c.email }}</div>
                                    <div class="text-xs text-slate-500">{{ c.telefono || 'Sin teléfono' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-400 border border-emerald-500/20">
                                        <Wallet class="h-3 w-3" />
                                        {{ c.cuentas_count }} cuenta(s)
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-500/10 px-2.5 py-1 text-xs font-medium text-indigo-400 border border-indigo-500/20">
                                        <CreditCard class="h-3 w-3" />
                                        {{ c.prestamos_count }} crédito(s)
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="c.estado === 'activo' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : 'bg-rose-500/20 text-rose-300 border-rose-500/30'"
                                        class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold border capitalize"
                                    >
                                        {{ c.estado }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="`/clientes/${c.id}`"
                                            class="inline-flex items-center gap-1 rounded-lg bg-slate-800 hover:bg-slate-700 px-3 py-1.5 text-xs font-medium text-blue-400 hover:text-blue-300 transition-colors"
                                        >
                                            <Eye class="h-3.5 w-3.5" />
                                            Ver Expediente
                                        </Link>
                                        <Link
                                            :href="`/cuentas/create?cliente_id=${c.id}`"
                                            class="inline-flex items-center gap-1 rounded-lg bg-emerald-950/60 hover:bg-emerald-900/80 px-2.5 py-1.5 text-xs font-medium text-emerald-400 border border-emerald-800/50 transition-colors"
                                            title="Abrir Cuenta para este cliente"
                                        >
                                            <Plus class="h-3.5 w-3.5" />
                                            Cuenta
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="clientes.data.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                    <Users class="mx-auto h-12 w-12 text-slate-600 mb-2" />
                                    No se encontraron clientes registrados con los filtros aplicados.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="clientes.last_page > 1" class="flex items-center justify-between px-6 py-4 border-t border-slate-800 bg-slate-950/40">
                    <div class="text-xs text-slate-400">
                        Página {{ clientes.current_page }} de {{ clientes.last_page }}
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-if="clientes.prev_page_url"
                            :href="clientes.prev_page_url"
                            class="rounded-lg bg-slate-800 px-3 py-1 text-xs text-white hover:bg-slate-700"
                        >
                            Anterior
                        </Link>
                        <Link
                            v-if="clientes.next_page_url"
                            :href="clientes.next_page_url"
                            class="rounded-lg bg-slate-800 px-3 py-1 text-xs text-white hover:bg-slate-700"
                        >
                            Siguiente
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
