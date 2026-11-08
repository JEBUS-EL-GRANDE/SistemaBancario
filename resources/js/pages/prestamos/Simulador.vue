<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import {
    Calculator,
    Check,
    CreditCard,
    DollarSign,
    Percent,
    Printer,
    TrendingUp,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Préstamos', href: '/prestamos' },
    { title: 'Simulador de Cuotas', href: '/prestamos/simulador' },
];

const monto = ref<number>(25000);
const plazo = ref<number>(24); // meses
const tasaAnual = ref<number>(12); // % anual

const money = (n: number) => `Bs. ${Number(n || 0).toLocaleString('es-BO', { minimumFractionDigits: 2 })}`;

// Cálculo del Sistema Francés de Amortización (Cuota Fija)
const resultado = computed(() => {
    const P = Number(monto.value) || 0;
    const n = Number(plazo.value) || 1;
    const r = (Number(tasaAnual.value) || 0) / 100 / 12;

    if (P <= 0 || n <= 0) {
        return { cuota: 0, totalPagar: 0, totalInteres: 0, cronograma: [] };
    }

    let cuota = 0;
    if (r > 0) {
        const factor = Math.pow(1 + r, n);
        cuota = P * ((r * factor) / (factor - 1));
    } else {
        cuota = P / n;
    }

    const cronograma = [];
    let saldo = P;

    for (let i = 1; i <= n; i++) {
        const interes = saldo * r;
        let capital = cuota - interes;

        if (i === n || capital > saldo) {
            capital = saldo;
            cuota = capital + interes;
            saldo = 0;
        } else {
            saldo -= capital;
        }

        cronograma.push({
            numero: i,
            cuotaTotal: Math.round(cuota * 100) / 100,
            capital: Math.round(capital * 100) / 100,
            interes: Math.round(interes * 100) / 100,
            saldoRestante: Math.max(0, Math.round(saldo * 100) / 100),
        });
    }

    const totalPagar = cronograma.reduce((acc, c) => acc + c.cuotaTotal, 0);
    const totalInteres = cronograma.reduce((acc, c) => acc + c.interes, 0);

    return {
        cuota: Math.round(cuota * 100) / 100,
        totalPagar,
        totalInteres,
        cronograma,
    };
});

const imprimir = () => {
    window.print();
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Simulador de Crédito - Amortización Francesa" />

        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-print">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                        <Calculator class="h-7 w-7 text-indigo-400" />
                        Simulador de Amortización Francesa (RF-023)
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">
                        Cálculo financiero de cuota fija con desglose mensual de capital e intereses en tiempo real.
                    </p>
                </div>
                <div class="flex gap-2">
                    <button
                        @click="imprimir"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 px-3.5 py-2 text-xs font-semibold text-slate-200 border border-slate-700 transition-all cursor-pointer"
                    >
                        <Printer class="h-4 w-4" />
                        Imprimir Tabla
                    </button>
                    <Link
                        href="/prestamos/create"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 px-4 py-2 text-xs font-semibold text-white shadow-md shadow-amber-500/20 hover:from-amber-500 transition-all cursor-pointer"
                    >
                        <CreditCard class="h-4 w-4" />
                        Solicitar este Préstamo
                    </Link>
                </div>
            </div>

            <!-- Controles y Métricas Clave -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 no-print">
                <!-- Tarjeta de Controles -->
                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 space-y-6 backdrop-blur-sm">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300">
                        Parámetros Financieros
                    </h3>

                    <!-- Monto -->
                    <div>
                        <div class="flex justify-between text-xs mb-2">
                            <span class="text-slate-400 font-semibold uppercase">Monto Solicitado:</span>
                            <span class="font-mono text-emerald-400 font-bold">{{ money(monto) }}</span>
                        </div>
                        <input
                            v-model.number="monto"
                            type="range"
                            min="1000"
                            max="500000"
                            step="1000"
                            class="w-full accent-emerald-500 cursor-pointer"
                        />
                        <div class="mt-2 relative">
                            <input
                                v-model.number="monto"
                                type="number"
                                min="500"
                                max="1000000"
                                class="w-full rounded-xl bg-slate-950 border border-slate-700 px-3 py-1.5 text-sm text-white font-mono"
                            />
                        </div>
                    </div>

                    <!-- Plazo -->
                    <div>
                        <div class="flex justify-between text-xs mb-2">
                            <span class="text-slate-400 font-semibold uppercase">Plazo de Pago:</span>
                            <span class="font-mono text-indigo-400 font-bold">{{ plazo }} Meses</span>
                        </div>
                        <input
                            v-model.number="plazo"
                            type="range"
                            min="3"
                            max="120"
                            step="1"
                            class="w-full accent-indigo-500 cursor-pointer"
                        />
                        <div class="mt-2 relative">
                            <input
                                v-model.number="plazo"
                                type="number"
                                min="1"
                                max="360"
                                class="w-full rounded-xl bg-slate-950 border border-slate-700 px-3 py-1.5 text-sm text-white font-mono"
                            />
                        </div>
                    </div>

                    <!-- Tasa de Interés -->
                    <div>
                        <div class="flex justify-between text-xs mb-2">
                            <span class="text-slate-400 font-semibold uppercase">Tasa de Interés Anual:</span>
                            <span class="font-mono text-amber-400 font-bold">{{ tasaAnual }}%</span>
                        </div>
                        <input
                            v-model.number="tasaAnual"
                            type="range"
                            min="1"
                            max="36"
                            step="0.5"
                            class="w-full accent-amber-500 cursor-pointer"
                        />
                        <div class="mt-2 relative">
                            <input
                                v-model.number="tasaAnual"
                                type="number"
                                min="0.1"
                                max="50"
                                step="0.1"
                                class="w-full rounded-xl bg-slate-950 border border-slate-700 px-3 py-1.5 text-sm text-white font-mono"
                            />
                        </div>
                    </div>
                </div>

                <!-- Métricas Calculadas -->
                <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="rounded-2xl border border-indigo-500/30 bg-indigo-950/20 p-6 flex flex-col justify-between">
                        <span class="text-xs uppercase tracking-wider text-indigo-400 font-semibold">Cuota Fija Mensual</span>
                        <div class="font-mono text-3xl font-black text-indigo-400 my-2">
                            {{ money(resultado.cuota) }}
                        </div>
                        <span class="text-xs text-slate-400">Capital + Interés constante</span>
                    </div>

                    <div class="rounded-2xl border border-amber-500/30 bg-amber-950/20 p-6 flex flex-col justify-between">
                        <span class="text-xs uppercase tracking-wider text-amber-400 font-semibold">Total Intereses</span>
                        <div class="font-mono text-3xl font-black text-amber-400 my-2">
                            {{ money(resultado.totalInteres) }}
                        </div>
                        <span class="text-xs text-slate-400">Costo financiero acumulado</span>
                    </div>

                    <div class="rounded-2xl border border-emerald-500/30 bg-emerald-950/20 p-6 flex flex-col justify-between">
                        <span class="text-xs uppercase tracking-wider text-emerald-400 font-semibold">Costo Total del Crédito</span>
                        <div class="font-mono text-3xl font-black text-emerald-400 my-2">
                            {{ money(resultado.totalPagar) }}
                        </div>
                        <span class="text-xs text-slate-400">Capital amortizado + intereses</span>
                    </div>

                    <div class="sm:col-span-3 rounded-2xl bg-slate-900/60 border border-slate-800 p-4 text-xs text-slate-400 leading-relaxed">
                        <strong class="text-white">Método Francés:</strong> Las cuotas son idénticas todos los meses. En las primeras cuotas se abona una mayor proporción de intereses y menor de capital, invirtiéndose esta proporción a medida que avanza el calendario.
                    </div>
                </div>
            </div>

            <!-- Tabla del Calendario de Amortización -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/50 backdrop-blur-sm overflow-hidden shadow-xl print:border-none print:bg-white print:text-black">
                <div class="p-4 border-b border-slate-800 bg-slate-950/60 print:bg-transparent print:border-black flex justify-between items-center">
                    <h3 class="text-base font-bold text-white print:text-black">
                        Cronograma Oficial de Cuotas ({{ plazo }} Meses)
                    </h3>
                    <span class="text-xs text-slate-400 print:text-gray-600">
                        Préstamo de {{ money(monto) }} al {{ tasaAnual }}% Anual
                    </span>
                </div>

                <div class="overflow-x-auto max-h-[500px]">
                    <table class="w-full text-left text-sm text-slate-300 print:text-black">
                        <thead class="bg-slate-950 text-xs uppercase tracking-wider text-slate-400 sticky top-0 border-b border-slate-800 print:bg-gray-100 print:border-black print:text-black">
                            <tr>
                                <th class="px-6 py-3 font-semibold text-center">N° Cuota</th>
                                <th class="px-6 py-3 font-semibold text-right">Cuota Mensual</th>
                                <th class="px-6 py-3 font-semibold text-right">Abono Capital</th>
                                <th class="px-6 py-3 font-semibold text-right">Intereses</th>
                                <th class="px-6 py-3 font-semibold text-right">Saldo Restante</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80 font-mono text-xs print:divide-gray-300">
                            <tr v-for="c in resultado.cronograma" :key="c.numero" class="hover:bg-slate-800/40">
                                <td class="px-6 py-3 text-center font-bold text-slate-400 print:text-black">
                                    {{ c.numero }}
                                </td>
                                <td class="px-6 py-3 text-right font-bold text-white print:text-black">
                                    {{ money(c.cuotaTotal) }}
                                </td>
                                <td class="px-6 py-3 text-right text-emerald-400 print:text-black">
                                    {{ money(c.capital) }}
                                </td>
                                <td class="px-6 py-3 text-right text-amber-400 print:text-black">
                                    {{ money(c.interes) }}
                                </td>
                                <td class="px-6 py-3 text-right text-slate-300 print:text-black">
                                    {{ money(c.saldoRestante) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
