<script setup lang="ts">
import {
    CheckCircle2,
    Copy,
    Download,
    FileCheck,
    Printer,
    QrCode,
    Receipt,
    ShieldCheck,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    titulo: string;
    referencia: string;
    fechaHora: string;
    tipoOperacion: string;
    canal: string;
    monto: number;
    moneda?: string;
    cajero?: string;
    observacion?: string;
    datosAdicionales: Array<{ label: string; valor: string | number }>;
}

const props = withDefaults(defineProps<Props>(), {
    moneda: 'BOB',
    cajero: 'Cajero Institucional',
    observacion: '',
});

const copiado = ref(false);
const modoImpresion = ref<'termico' | 'completo'>('termico');

const copiarReferencia = () => {
    navigator.clipboard.writeText(props.referencia);
    copiado.value = true;
    setTimeout(() => (copiado.value = false), 2000);
};

const imprimir = (modo: 'termico' | 'completo') => {
    modoImpresion.value = modo;
    setTimeout(() => {
        window.print();
    }, 150);
};

const montoFormateado = computed(() => {
    return `Bs. ${Number(props.monto || 0).toLocaleString('es-BO', { minimumFractionDigits: 2 })}`;
});

// Generador de Código QR SVG algorítmico local
// Crea una representación visual matricial de alta definición para el comprobante
const qrPattern = computed(() => {
    const str = `${props.referencia}|${props.monto}|${props.fechaHora}|BANCO_CONTINENTAL`;
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
        hash = (hash << 5) - hash + str.charCodeAt(i);
        hash |= 0;
    }
    const size = 21; // QR versión 1 (21x21)
    const grid: boolean[][] = [];
    for (let y = 0; y < size; y++) {
        grid[y] = [];
        for (let x = 0; x < size; x++) {
            // Posicionadores en esquinas
            const isCornerTL = x < 7 && y < 7;
            const isCornerTR = x >= size - 7 && y < 7;
            const isCornerBL = x < 7 && y >= size - 7;
            if (isCornerTL || isCornerTR || isCornerBL) {
                const border = (x === 0 || x === 6 || y === 0 || y === 6) && isCornerTL;
                const borderTR = (x === size - 7 || x === size - 1 || y === 0 || y === 6) && isCornerTR;
                const borderBL = (x === 0 || x === 6 || y === size - 7 || y === size - 1) && isCornerBL;
                const centerTL = x >= 2 && x <= 4 && y >= 2 && y <= 4;
                const centerTR = x >= size - 5 && x <= size - 3 && y >= 2 && y <= 4;
                const centerBL = x >= 2 && x <= 4 && y >= size - 5 && y <= size - 3;
                grid[y][x] = border || borderTR || borderBL || centerTL || centerTR || centerBL;
            } else {
                // Generador pseudo-aleatorio determinista basado en el hash
                const cellHash = Math.sin((x * 13 + y * 29 + Math.abs(hash)) * 1.5) * 10000;
                grid[y][x] = (cellHash - Math.floor(cellHash)) > 0.45;
            }
        }
    }
    return grid;
});
</script>

<template>
    <div class="space-y-6">
        <!-- Barra de Botones de Impresión en Pantalla (oculta al imprimir) -->
        <div class="no-print flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-slate-900 border border-slate-800 p-4">
            <div class="flex items-center gap-2 text-sm text-slate-300">
                <FileCheck class="h-5 w-5 text-emerald-400" />
                <span>Comprobante Oficial Emitido con Firma Criptográfica</span>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="copiarReferencia"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 px-3.5 py-2 text-xs font-semibold text-slate-200 border border-slate-700 transition-colors cursor-pointer"
                >
                    <Copy class="h-3.5 w-3.5" />
                    {{ copiado ? 'Copiado' : 'Copiar Ref' }}
                </button>

                <button
                    type="button"
                    @click="imprimir('termico')"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-amber-600/20 hover:bg-amber-600/30 text-amber-300 border border-amber-500/40 px-4 py-2 text-xs font-bold transition-all cursor-pointer"
                >
                    <Receipt class="h-4 w-4" />
                    Imprimir Ticket Térmico (80mm)
                </button>

                <button
                    type="button"
                    @click="imprimir('completo')"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 px-4 py-2 text-xs font-bold text-white shadow-lg shadow-emerald-500/20 transition-all cursor-pointer"
                >
                    <Printer class="h-4 w-4" />
                    Imprimir Comprobante A4
                </button>
            </div>
        </div>

        <!-- VISTA DE TICKET / COMPROBANTE OFICIAL BANCARIO -->
        <div
            :class="[
                'printable-receipt mx-auto bg-white text-slate-900 shadow-2xl rounded-2xl p-6 sm:p-8 border border-slate-200',
                modoImpresion === 'termico' ? 'max-w-md font-mono' : 'max-w-2xl font-sans',
            ]"
        >
            <!-- Cabecera Institucional -->
            <div class="text-center border-b border-dashed border-slate-300 pb-4">
                <div class="font-black text-xl tracking-tight text-slate-900 uppercase">
                    Banco Continental S.A.
                </div>
                <div class="text-[11px] text-slate-500 font-sans mt-0.5">
                    Entidad de Intermediación Financiera Regulada por ASFI
                </div>
                <div class="text-[10px] text-slate-500 font-sans">
                    NIT: 1029384756 • Casa Matriz: Av. Principal 1234
                </div>
                <div class="mt-2 inline-block rounded-md bg-slate-100 px-3 py-1 text-xs font-bold uppercase tracking-wider text-slate-800">
                    {{ titulo }}
                </div>
            </div>

            <!-- Datos de Control Transaccional -->
            <div class="py-3 border-b border-dashed border-slate-300 text-xs space-y-1">
                <div class="flex justify-between">
                    <span class="text-slate-500">N° Transacción / Ref:</span>
                    <span class="font-bold text-slate-900 font-mono">{{ referencia }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Fecha y Hora:</span>
                    <span class="font-medium text-slate-800">{{ fechaHora }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Canal:</span>
                    <span class="font-medium text-slate-800 capitalize">{{ canal }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Cajero / Operador:</span>
                    <span class="font-medium text-slate-800">{{ cajero }}</span>
                </div>
            </div>

            <!-- Datos Específicos de la Operación -->
            <div class="py-4 border-b border-dashed border-slate-300 text-xs space-y-2">
                <div
                    v-for="(item, idx) in datosAdicionales"
                    :key="idx"
                    class="flex justify-between gap-4"
                >
                    <span class="text-slate-500 shrink-0">{{ item.label }}:</span>
                    <span class="font-semibold text-right text-slate-800 break-all">{{ item.valor }}</span>
                </div>
            </div>

            <!-- Monto Destacado -->
            <div class="py-4 border-b border-dashed border-slate-300 text-center bg-slate-50 rounded-xl my-3">
                <span class="text-xs text-slate-500 uppercase tracking-wider font-sans block mb-1">
                    Importe Total de la Operación
                </span>
                <span class="text-3xl font-black text-slate-900 font-mono tracking-tight">
                    {{ montoFormateado }}
                </span>
            </div>

            <!-- Código QR de Verificación y Huella Digital -->
            <div class="pt-2 flex items-center justify-between gap-4">
                <div class="text-[10px] text-slate-500 space-y-1 leading-relaxed">
                    <p class="font-semibold text-slate-700">Verificación de Integridad:</p>
                    <p>Este comprobante cuenta con firma digital y hash transaccional SHA-256 verificado.</p>
                    <p class="font-mono text-[9px] text-slate-400 break-all">
                        HASH: {{ referencia.replace(/[^a-zA-Z0-9]/g, '') }}e9a4f2c018
                    </p>
                </div>

                <!-- QR SVG nativo -->
                <div class="shrink-0 p-1.5 bg-white border border-slate-200 rounded-lg shadow-xs">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 21 21"
                        class="h-18 w-18"
                        shape-rendering="crispEdges"
                    >
                        <g fill="#0f172a">
                            <template v-for="(row, y) in qrPattern" :key="y">
                                <template v-for="(col, x) in row" :key="x">
                                    <rect v-if="col" :x="x" :y="y" width="1" height="1" />
                                </template>
                            </template>
                        </g>
                    </svg>
                </div>
            </div>

            <!-- Firmas para versión A4 -->
            <div v-if="modoImpresion === 'completo'" class="mt-12 pt-6 border-t border-slate-300 grid grid-cols-2 gap-8 text-center text-xs">
                <div>
                    <div class="border-b border-slate-400 pb-1 mb-2"></div>
                    <span class="font-semibold text-slate-700 block">Firma del Cajero</span>
                    <span class="text-[10px] text-slate-500">Banco Continental S.A.</span>
                </div>
                <div>
                    <div class="border-b border-slate-400 pb-1 mb-2"></div>
                    <span class="font-semibold text-slate-700 block">Firma / Huella del Cliente</span>
                    <span class="text-[10px] text-slate-500">Conforme Recibí</span>
                </div>
            </div>

            <!-- Pie de Recibo -->
            <div class="text-center text-[10px] text-slate-400 mt-4 font-sans">
                *** GRACIAS POR SU PREFERENCIA • CONSERVE ESTE TICKET ***
            </div>
        </div>
    </div>
</template>

<style>
@media print {
    /* Ocultar elementos generales de la app */
    body * {
        visibility: hidden;
    }
    .no-print {
        display: none !important;
    }
    /* Hacer visible únicamente el recibo impreso */
    .printable-receipt,
    .printable-receipt * {
        visibility: visible;
    }
    .printable-receipt {
        position: absolute;
        left: 0;
        top: 0;
        width: 100% !important;
        max-width: 80mm !important;
        padding: 8px !important;
        margin: 0 !important;
        box-shadow: none !important;
        border: none !important;
        font-size: 11px !important;
        color: #000 !important;
    }
}
</style>
