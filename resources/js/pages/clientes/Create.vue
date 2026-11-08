<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Check, Eye, EyeOff, KeyRound, ShieldCheck, UserPlus } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Clientes', href: '/clientes' },
    { title: 'Registrar', href: '/clientes/create' },
];

const showPassword = ref(false);

const form = useForm({
    nombres: '',
    apellidos: '',
    tipo_documento: 'ci',
    numero_documento: '',
    email: '',
    telefono: '',
    direccion: '',
    fecha_nacimiento: '',
    crear_usuario: true,
    username: '',
    password: '',
});

const suggestedUsername = computed(() => {
    if (!form.nombres && !form.apellidos) return '';
    const clean = (str: string) => str.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/[^a-z0-9]/g, '');
    const n = clean(form.nombres.split(' ')[0] || '');
    const a = clean(form.apellidos.split(' ')[0] || '');
    return n && a ? `${n}.${a}` : n || a;
});

const submit = () => {
    if (!form.username && suggestedUsername.value) {
        form.username = suggestedUsername.value;
    }
    form.post('/clientes');
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Registrar Cliente - Banco Continental" />

        <div class="mx-auto max-w-4xl space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                        <UserPlus class="h-7 w-7 text-blue-400" />
                        Registrar Nuevo Cliente
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">
                        Formulario oficial de enrolamiento y expediente bancario (RF-004, RF-005).
                    </p>
                </div>
                <Link
                    href="/clientes"
                    class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-3.5 py-2 text-sm font-medium text-slate-300 hover:bg-slate-700 hover:text-white transition-all"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Volver al listado
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
                                v-model="form.nombres"
                                type="text"
                                required
                                placeholder="Ej. Carlos Alberto"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            />
                            <p v-if="form.errors.nombres" class="mt-1 text-xs text-rose-400">{{ form.errors.nombres }}</p>
                        </div>

                        <!-- Apellidos -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Apellidos <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.apellidos"
                                type="text"
                                required
                                placeholder="Ej. Gómez Miranda"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            />
                            <p v-if="form.errors.apellidos" class="mt-1 text-xs text-rose-400">{{ form.errors.apellidos }}</p>
                        </div>

                        <!-- Tipo de Documento -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Tipo de Documento <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.tipo_documento"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            >
                                <option value="ci">Cédula de Identidad (C.I.)</option>
                                <option value="pasaporte">Pasaporte</option>
                                <option value="ruc">RUC / NIT</option>
                            </select>
                            <p v-if="form.errors.tipo_documento" class="mt-1 text-xs text-rose-400">{{ form.errors.tipo_documento }}</p>
                        </div>

                        <!-- Número de Documento -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Número de Documento <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.numero_documento"
                                type="text"
                                required
                                placeholder="Ej. 8765432 LP"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            />
                            <p v-if="form.errors.numero_documento" class="mt-1 text-xs text-rose-400">{{ form.errors.numero_documento }}</p>
                        </div>

                        <!-- Correo Electrónico -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Correo Electrónico <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                placeholder="cliente@correo.com"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            />
                            <p v-if="form.errors.email" class="mt-1 text-xs text-rose-400">{{ form.errors.email }}</p>
                        </div>

                        <!-- Teléfono / Celular -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Teléfono / Celular
                            </label>
                            <input
                                v-model="form.telefono"
                                type="text"
                                placeholder="Ej. 71234567"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            />
                            <p v-if="form.errors.telefono" class="mt-1 text-xs text-rose-400">{{ form.errors.telefono }}</p>
                        </div>

                        <!-- Fecha de Nacimiento -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Fecha de Nacimiento
                            </label>
                            <input
                                v-model="form.fecha_nacimiento"
                                type="date"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            />
                            <p v-if="form.errors.fecha_nacimiento" class="mt-1 text-xs text-rose-400">{{ form.errors.fecha_nacimiento }}</p>
                        </div>

                        <!-- Dirección -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Dirección de Domicilio
                            </label>
                            <textarea
                                v-model="form.direccion"
                                rows="2"
                                placeholder="Av. Principal #123, Zona Central"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            ></textarea>
                            <p v-if="form.errors.direccion" class="mt-1 text-xs text-rose-400">{{ form.errors.direccion }}</p>
                        </div>
                    </div>

                    <!-- Sección: Acceso a Banca Digital (Usuario para Iniciar Sesión) -->
                    <div class="rounded-2xl border border-cyan-500/30 bg-gradient-to-br from-cyan-950/30 via-slate-900/40 to-slate-950/60 p-5 sm:p-6 space-y-4 shadow-lg shadow-cyan-950/20">
                        <div class="flex items-center justify-between flex-wrap gap-3">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 rounded-xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                                    <KeyRound class="h-5 w-5" />
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-white flex items-center gap-2">
                                        Crear Acceso a Banca Digital Simultáneamente
                                        <span class="rounded-full bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 text-[10px] px-2 py-0.5 font-bold uppercase tracking-wider">
                                            Recomendado
                                        </span>
                                    </h3>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        Genera automáticamente las credenciales para que el cliente pueda iniciar sesión de inmediato con su correo.
                                    </p>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="form.crear_usuario" class="sr-only peer" />
                                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cyan-500"></div>
                            </label>
                        </div>

                        <div v-if="form.crear_usuario" class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-cyan-500/15">
                            <!-- Nombre de Usuario -->
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                    Usuario de Acceso (Opcional)
                                </label>
                                <input
                                    v-model="form.username"
                                    type="text"
                                    :placeholder="suggestedUsername || 'Ej. carlos.gomez'"
                                    class="w-full rounded-xl bg-slate-950/80 border border-slate-700/80 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 font-mono text-xs"
                                />
                                <p v-if="form.errors.username" class="mt-1 text-xs text-rose-400">{{ form.errors.username }}</p>
                                <p class="text-[11px] text-slate-400 mt-1">
                                    Por defecto: <span class="text-cyan-400 font-mono">{{ suggestedUsername || 'nombre.apellido' }}</span> (o podrá usar su correo).
                                </p>
                            </div>

                            <!-- Contraseña -->
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                    Contraseña Inicial de Acceso
                                </label>
                                <div class="relative">
                                    <input
                                        v-model="form.password"
                                        :type="showPassword ? 'text' : 'password'"
                                        placeholder="password123"
                                        class="w-full rounded-xl bg-slate-950/80 border border-slate-700/80 px-4 py-2.5 pr-10 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 font-mono text-xs"
                                    />
                                    <button
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-white"
                                    >
                                        <Eye v-if="!showPassword" class="h-4 w-4" />
                                        <EyeOff v-else class="h-4 w-4" />
                                    </button>
                                </div>
                                <p v-if="form.errors.password" class="mt-1 text-xs text-rose-400">{{ form.errors.password }}</p>
                                <p class="text-[11px] text-slate-400 mt-1">
                                    Si se deja vacío, se asignará: <span class="text-cyan-400 font-mono font-bold">password123</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Banner de Seguridad -->
                    <div class="rounded-xl bg-blue-500/10 border border-blue-500/20 p-4 flex items-start gap-3">
                        <ShieldCheck class="h-5 w-5 text-blue-400 mt-0.5 shrink-0" />
                        <div class="text-xs text-slate-300 leading-relaxed">
                            <strong>Validación de Registro:</strong> Los datos serán verificados de forma única en el sistema. Una vez registrado, se habilitará la opción para asignarle cuentas de ahorro o corriente y solicitudes crediticias.
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                        <Link
                            href="/clientes"
                            class="rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-400 hover:text-white transition-colors"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 hover:from-blue-500 hover:to-indigo-500 focus:outline-none disabled:opacity-50 transition-all cursor-pointer"
                        >
                            <Check class="h-4 w-4" />
                            {{ form.processing ? 'Registrando...' : 'Completar Registro' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
