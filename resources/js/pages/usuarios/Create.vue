<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Check, Shield, UserPlus } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Usuarios', href: '/usuarios' },
    { title: 'Nuevo Usuario', href: '/usuarios/create' },
];

const props = defineProps<{
    bancos?: Array<{ id: number; nombre: string; codigo: string }>;
}>();

const form = useForm({
    banco_id: props.bancos?.[0]?.id ?? null,
    name: '',
    apellidos: '',
    username: '',
    email: '',
    password: '',
    rol: 'cajero',
    telefono: '',
});

const submit = () => {
    form.post('/usuarios');
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Registrar Usuario Interno - Banco Continental" />

        <div class="mx-auto max-w-3xl space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                        <UserPlus class="h-7 w-7 text-indigo-400" />
                        Registrar Nuevo Usuario del Sistema
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">
                        Asignación de perfiles internos y control de acceso (RF-001, RF-002).
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
                                placeholder="Ej. Roberto"
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
                                placeholder="Ej. Vargas Paredes"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500"
                            />
                            <p v-if="form.errors.apellidos" class="mt-1 text-xs text-rose-400">{{ form.errors.apellidos }}</p>
                        </div>

                        <!-- Username -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Nombre de Usuario (Login) <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.username"
                                type="text"
                                required
                                placeholder="Ej. cajero02"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm font-mono text-white focus:outline-none focus:border-indigo-500"
                            />
                            <p v-if="form.errors.username" class="mt-1 text-xs text-rose-400">{{ form.errors.username }}</p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Correo Institucional <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                placeholder="usuario@bancocontinental.com"
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

                        <!-- Rol (RBAC) -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Rol y Privilegios en el Sistema <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.rol"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500"
                            >
                                <option value="cajero">Cajero de Ventanilla (Depósitos, Retiros, Cobro Cuotas)</option>
                                <option value="ejecutivo_credito">Ejecutivo de Crédito (Solicitudes, Evaluación, Aprobación)</option>
                                <option value="administrador">Administrador Total (Configuración, Auditoría, Usuarios)</option>
                                <option value="cliente">Cliente (Consultas Básicas)</option>
                            </select>
                            <p v-if="form.errors.rol" class="mt-1 text-xs text-rose-400">{{ form.errors.rol }}</p>
                        </div>

                        <!-- Teléfono -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Teléfono / Celular
                            </label>
                            <input
                                v-model="form.telefono"
                                type="text"
                                placeholder="Ej. 70012345"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500"
                            />
                        </div>

                        <!-- Contraseña -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Contraseña Inicial de Acceso <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.password"
                                type="password"
                                required
                                minlength="8"
                                placeholder="Mínimo 8 caracteres seguros"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white font-mono focus:outline-none focus:border-indigo-500"
                            />
                            <p v-if="form.errors.password" class="mt-1 text-xs text-rose-400">{{ form.errors.password }}</p>
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
                            {{ form.processing ? 'Registrando...' : 'Crear Usuario' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
