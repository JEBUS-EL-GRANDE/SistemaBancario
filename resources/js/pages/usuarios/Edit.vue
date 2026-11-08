<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Check, Edit, Shield } from 'lucide-vue-next';

interface User {
    id: number;
    banco_id?: number | null;
    name: string;
    apellidos: string | null;
    username: string;
    email: string;
    rol: string;
    estado: string;
    telefono: string | null;
}

const props = defineProps<{
    usuario: User;
    bancos?: Array<{ id: number; nombre: string; codigo: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Usuarios', href: '/usuarios' },
    { title: `Editar ${props.usuario.username}`, href: `/usuarios/${props.usuario.id}/edit` },
];

const form = useForm({
    banco_id: props.usuario.banco_id ?? props.bancos?.[0]?.id ?? null,
    name: props.usuario.name,
    apellidos: props.usuario.apellidos || '',
    username: props.usuario.username,
    email: props.usuario.email,
    rol: props.usuario.rol,
    estado: props.usuario.estado,
    telefono: props.usuario.telefono || '',
    password: '',
});

const submit = () => {
    form.put(`/usuarios/${props.usuario.id}`);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar Usuario: ${usuario.username}`" />

        <div class="mx-auto max-w-3xl space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                        <Edit class="h-7 w-7 text-indigo-400" />
                        Editar Usuario: {{ usuario.username }}
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">
                        Actualizar información personal, rol y estado de seguridad (RF-001, RF-003).
                    </p>
                </div>
                <Link
                    href="/usuarios"
                    class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-3.5 py-2 text-sm font-medium text-slate-300 hover:bg-slate-700 hover:text-white transition-all"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Volver
                </Link>
            </div>

            <!-- Form Card -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 sm:p-8 shadow-xl backdrop-blur-sm">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Nombres -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Nombres <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-rose-400">{{ form.errors.name }}</p>
                        </div>

                        <!-- Apellidos -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Apellidos
                            </label>
                            <input
                                v-model="form.apellidos"
                                type="text"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500"
                            />
                        </div>

                        <!-- Username -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Nombre de Usuario <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.username"
                                type="text"
                                required
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm font-mono text-white focus:outline-none focus:border-indigo-500"
                            />
                            <p v-if="form.errors.username" class="mt-1 text-xs text-rose-400">{{ form.errors.username }}</p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Correo Electrónico <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500"
                            />
                            <p v-if="form.errors.email" class="mt-1 text-xs text-rose-400">{{ form.errors.email }}</p>
                        </div>

                        <!-- Banco (Multi-inquilino) -->
                        <div v-if="props.bancos && props.bancos.length > 0">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Banco / Inquilino Asignado <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.banco_id"
                                required
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-emerald-400 font-semibold focus:outline-none focus:border-emerald-500"
                            >
                                <option v-for="b in props.bancos" :key="b.id" :value="b.id">
                                    🏦 {{ b.nombre }} ({{ b.codigo }})
                                </option>
                            </select>
                            <p v-if="form.errors.banco_id" class="mt-1 text-xs text-rose-400">{{ form.errors.banco_id }}</p>
                        </div>

                        <!-- Rol -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Rol Asignado <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.rol"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500"
                            >
                                <option value="cajero">Cajero</option>
                                <option value="ejecutivo_credito">Ejecutivo de Crédito</option>
                                <option value="administrador">Administrador</option>
                                <option value="cliente">Cliente</option>
                            </select>
                        </div>

                        <!-- Estado -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Estado de la Cuenta <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.estado"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500"
                            >
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                                <option value="bloqueado">Bloqueado</option>
                            </select>
                        </div>

                        <!-- Teléfono -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Teléfono / Celular
                            </label>
                            <input
                                v-model="form.telefono"
                                type="text"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500"
                            />
                        </div>

                        <!-- Nueva Contraseña -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Cambiar Contraseña (Opcional)
                            </label>
                            <input
                                v-model="form.password"
                                type="password"
                                placeholder="Dejar en blanco para no modificar"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white font-mono focus:outline-none focus:border-indigo-500"
                            />
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                        <Link
                            href="/usuarios"
                            class="rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-400 hover:text-white"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/20 hover:from-indigo-500 cursor-pointer disabled:opacity-50"
                        >
                            <Check class="h-4 w-4" />
                            {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
