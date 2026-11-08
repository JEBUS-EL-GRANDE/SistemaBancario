<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Check, CreditCard, ShieldCheck, Wallet } from 'lucide-vue-next';
import { ref } from 'vue';

interface Cliente {
    id: number;
    nombres: string;
    apellidos: string;
    numero_documento: string;
    email: string;
}

const props = defineProps<{
    clientes: Cliente[];
    selectedClienteId: number | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Cuentas Bancarias', href: '/cuentas' },
    { title: 'Apertura', href: '/cuentas/create' },
];

const form = useForm({
    cliente_id: props.selectedClienteId || (props.clientes.length > 0 ? props.clientes[0].id : ''),
    tipo: 'ahorro',
    moneda: 'BOB',
    limite_retiro_diario: 5000,
    deposito_inicial: 0,
    emitir_tarjeta: true,
    pin_tarjeta: '1234',
});

const submit = () => {
    form.post('/cuentas');
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Apertura de Cuenta Bancaria - Banco Continental" />

        <div class="mx-auto max-w-4xl space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                        <Wallet class="h-7 w-7 text-emerald-400" />
                        Apertura de Cuenta Bancaria
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">
                        Generación automática de número de cuenta y vinculación con tarjeta de débito (RF-006, RF-007).
                    </p>
                </div>
                <Link
                    href="/cuentas"
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
                        <!-- Seleccionar Cliente -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Titular de la Cuenta (Cliente) <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.cliente_id"
                                required
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                            >
                                <option value="" disabled>Seleccione un cliente registrado...</option>
                                <option v-for="c in clientes" :key="c.id" :value="c.id">
                                    {{ c.nombres }} {{ c.apellidos }} — Doc: {{ c.numero_documento }} ({{ c.email }})
                                </option>
                            </select>
                            <p v-if="clientes.length === 0" class="mt-2 text-xs text-amber-400">
                                No hay clientes registrados aún.
                                <Link href="/clientes/create" class="underline font-semibold ml-1 text-blue-400">
                                    Registrar un cliente primero →
                                </Link>
                            </p>
                            <p v-if="form.errors.cliente_id" class="mt-1 text-xs text-rose-400">{{ form.errors.cliente_id }}</p>
                        </div>

                        <!-- Tipo de Cuenta -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Tipo de Cuenta <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.tipo"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                            >
                                <option value="ahorro">Caja de Ahorro (Prefijo 100)</option>
                                <option value="corriente">Cuenta Corriente (Prefijo 200)</option>
                            </select>
                            <p v-if="form.errors.tipo" class="mt-1 text-xs text-rose-400">{{ form.errors.tipo }}</p>
                        </div>

                        <!-- Moneda -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Moneda
                            </label>
                            <input
                                type="text"
                                value="BOB - Bolivianos (Bs.)"
                                disabled
                                class="w-full rounded-xl bg-slate-950/40 border border-slate-800 px-4 py-2.5 text-sm text-slate-400 cursor-not-allowed"
                            />
                        </div>

                        <!-- Límite de Retiro Diario -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Límite Diario de Retiro (Bs.) <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.limite_retiro_diario"
                                type="number"
                                min="100"
                                max="50000"
                                step="100"
                                required
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                            />
                            <span class="text-[11px] text-slate-400">Control de seguridad por ventanilla/ATM</span>
                            <p v-if="form.errors.limite_retiro_diario" class="mt-1 text-xs text-rose-400">{{ form.errors.limite_retiro_diario }}</p>
                        </div>

                        <!-- Depósito Inicial -->
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                                Depósito Inicial Voluntario (Bs.)
                            </label>
                            <input
                                v-model="form.deposito_inicial"
                                type="number"
                                min="0"
                                step="10"
                                placeholder="0.00"
                                class="w-full rounded-xl bg-slate-950/70 border border-slate-700/80 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                            />
                            <span class="text-[11px] text-slate-400">Si es mayor a 0, se acreditará de inmediato</span>
                            <p v-if="form.errors.deposito_inicial" class="mt-1 text-xs text-rose-400">{{ form.errors.deposito_inicial }}</p>
                        </div>
                    </div>

                    <!-- Sección de Tarjeta de Débito (RF-007) -->
                    <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-5 space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <CreditCard class="h-6 w-6 text-indigo-400" />
                                <div>
                                    <div class="text-sm font-bold text-white">Tarjeta de Débito Institucional (BIN 453288)</div>
                                    <div class="text-xs text-slate-400">Emisión automática para compras y retiros ATM</div>
                                </div>
                            </div>
                            <input
                                v-model="form.emitir_tarjeta"
                                type="checkbox"
                                class="h-5 w-5 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500"
                            />
                        </div>

                        <div v-if="form.emitir_tarjeta" class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-slate-800/80">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">
                                    NIP / PIN de Seguridad (4 dígitos)
                                </label>
                                <input
                                    v-model="form.pin_tarjeta"
                                    type="password"
                                    maxlength="4"
                                    pattern="[0-9]{4}"
                                    placeholder="1234"
                                    class="w-full rounded-xl bg-slate-900 border border-slate-700 px-4 py-2 text-sm text-white font-mono tracking-widest focus:outline-none focus:border-indigo-500"
                                />
                                <span class="text-[11px] text-slate-400">Se almacenará encriptado mediante hash BCrypt (RNF-007)</span>
                            </div>
                            <div class="flex items-center text-xs text-slate-400 leading-relaxed">
                                <ShieldCheck class="h-4 w-4 text-emerald-400 mr-2 shrink-0" />
                                La tarjeta tendrá vigencia de 4 años y se vinculará directamente al saldo disponible de esta cuenta.
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                        <Link
                            href="/cuentas"
                            class="rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-400 hover:text-white transition-colors"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing || clientes.length === 0"
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 hover:from-emerald-500 hover:to-teal-500 focus:outline-none disabled:opacity-50 transition-all cursor-pointer"
                        >
                            <Check class="h-4 w-4" />
                            {{ form.processing ? 'Aperturando...' : 'Confirmar Apertura de Cuenta' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
