<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    Edit,
    Lock,
    Plus,
    Search,
    Shield,
    ShieldAlert,
    ShieldCheck,
    Unlock,
    UserCheck,
    UserPlus,
    Users,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface User {
    id: number;
    name: string;
    apellidos: string | null;
    username: string;
    email: string;
    rol: string;
    estado: string;
    telefono: string | null;
    ultimo_acceso: string | null;
    created_at: string;
}

interface UsersPagination {
    data: User[];
    current_page: number;
    last_page: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

const props = defineProps<{
    usuarios: UsersPagination;
    filters: { search?: string; rol?: string; estado?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Gestión de Usuarios', href: '/usuarios' },
];

const search = ref(props.filters.search || '');
const rol = ref(props.filters.rol || '');
const estado = ref(props.filters.estado || '');

let timeout: number | null = null;
const filtrar = () => {
    if (timeout) clearTimeout(timeout);
    timeout = window.setTimeout(() => {
        router.get('/usuarios', { search: search.value, rol: rol.value, estado: estado.value }, { preserveState: true, replace: true });
    }, 350);
};

watch([search, rol, estado], filtrar);

const cambiarEstado = (usuario: User, nuevoEstado: string) => {
    if (confirm(`¿Desea cambiar el estado del usuario '${usuario.username}' a '${nuevoEstado}'?`)) {
        router.post(`/usuarios/${usuario.id}/estado`, { estado: nuevoEstado });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Gestión de Usuarios y Roles - Banco Continental" />

        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                        <Shield class="h-7 w-7 text-indigo-400" />
                        Control de Usuarios y Permisos (RBAC)
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">
                        Administración interna de cajeros, ejecutivos de crédito y administradores (RF-001 al RF-003).
                    </p>
                </div>
                <Link
                    href="/usuarios/create"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/20 hover:from-indigo-500 hover:to-blue-500 transition-all cursor-pointer"
                >
                    <UserPlus class="h-4 w-4" />
                    Registrar Nuevo Usuario
                </Link>
            </div>

            <!-- Filtros -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 rounded-2xl bg-slate-900/60 p-4 border border-slate-800">
                <div class="relative">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Buscar por usuario, nombre o email..."
                        class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 pl-9 pr-4 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500"
                    />
                </div>
                <div>
                    <select
                        v-model="rol"
                        class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2 text-sm text-white focus:outline-none focus:border-indigo-500"
                    >
                        <option value="">Todos los roles</option>
                        <option value="administrador">Administrador</option>
                        <option value="cajero">Cajero</option>
                        <option value="ejecutivo_credito">Ejecutivo de Crédito</option>
                        <option value="cliente">Cliente</option>
                    </select>
                </div>
                <div>
                    <select
                        v-model="estado"
                        class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2 text-sm text-white focus:outline-none focus:border-indigo-500"
                    >
                        <option value="">Todos los estados</option>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                        <option value="bloqueado">Bloqueado</option>
                    </select>
                </div>
            </div>

            <!-- Tabla de Usuarios -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/50 backdrop-blur-sm overflow-hidden shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-300">
                        <thead class="bg-slate-950/60 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-800">
                            <tr>
                                <th class="px-6 py-4 font-semibold">Usuario</th>
                                <th class="px-6 py-4 font-semibold">Banco</th>
                                <th class="px-6 py-4 font-semibold">Nombre Completo</th>
                                <th class="px-6 py-4 font-semibold">Correo Electrónico</th>
                                <th class="px-6 py-4 font-semibold">Rol Asignado</th>
                                <th class="px-6 py-4 font-semibold">Estado</th>
                                <th class="px-6 py-4 font-semibold">Último Acceso</th>
                                <th class="px-6 py-4 font-semibold text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80">
                            <tr v-for="u in usuarios.data" :key="u.id" class="hover:bg-slate-800/40">
                                <td class="px-6 py-4 font-mono font-bold text-white text-xs">
                                    {{ u.username }}
                                </td>
                                <td class="px-6 py-4">
                                    <span v-if="(u as any).banco" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        🏦 {{ (u as any).banco.nombre }}
                                    </span>
                                    <span v-else class="text-xs text-slate-500 italic">Global</span>
                                </td>
                                <td class="px-6 py-4 font-medium text-slate-200">
                                    {{ u.name }} {{ u.apellidos || '' }}
                                </td>
                                <td class="px-6 py-4 text-slate-300">
                                    {{ u.email }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="{
                                            'bg-rose-500/20 text-rose-300 border-rose-500/30': u.rol === 'administrador',
                                            'bg-emerald-500/20 text-emerald-300 border-emerald-500/30': u.rol === 'cajero',
                                            'bg-amber-500/20 text-amber-300 border-amber-500/30': u.rol === 'ejecutivo_credito',
                                            'bg-blue-500/20 text-blue-300 border-blue-500/30': u.rol === 'cliente',
                                        }"
                                        class="rounded-full px-2.5 py-0.5 text-xs font-semibold border capitalize"
                                    >
                                        {{ u.rol.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="{
                                            'bg-emerald-500/20 text-emerald-300': u.estado === 'activo',
                                            'bg-amber-500/20 text-amber-300': u.estado === 'inactivo',
                                            'bg-rose-500/20 text-rose-300': u.estado === 'bloqueado',
                                        }"
                                        class="rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase"
                                    >
                                        {{ u.estado }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-400">
                                    {{ u.ultimo_acceso ? new Date(u.ultimo_acceso).toLocaleString('es-BO') : 'Nunca' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link
                                            :href="`/usuarios/${u.id}/edit`"
                                            class="rounded-lg bg-slate-800 hover:bg-slate-700 px-2.5 py-1 text-xs font-medium text-slate-300 hover:text-white transition-colors flex items-center gap-1"
                                            title="Editar Usuario"
                                        >
                                            <Edit class="h-3.5 w-3.5" />
                                            Editar
                                        </Link>
                                        <button
                                            v-if="u.estado === 'activo'"
                                            @click="cambiarEstado(u, 'bloqueado')"
                                            class="rounded-lg bg-rose-500/10 hover:bg-rose-500/20 px-2 py-1 text-xs font-medium text-rose-400 border border-rose-500/30 cursor-pointer"
                                            title="Bloquear usuario"
                                        >
                                            <Lock class="h-3.5 w-3.5" />
                                        </button>
                                        <button
                                            v-else
                                            @click="cambiarEstado(u, 'activo')"
                                            class="rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 px-2 py-1 text-xs font-medium text-emerald-400 border border-emerald-500/30 cursor-pointer"
                                            title="Activar usuario"
                                        >
                                            <Unlock class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="usuarios.last_page > 1" class="flex items-center justify-between px-6 py-4 border-t border-slate-800 bg-slate-950/40">
                    <div class="text-xs text-slate-400">
                        Página {{ usuarios.current_page }} de {{ usuarios.last_page }}
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-if="usuarios.prev_page_url"
                            :href="usuarios.prev_page_url"
                            class="rounded-lg bg-slate-800 px-3 py-1 text-xs text-white hover:bg-slate-700"
                        >
                            Anterior
                        </Link>
                        <Link
                            v-if="usuarios.next_page_url"
                            :href="usuarios.next_page_url"
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
