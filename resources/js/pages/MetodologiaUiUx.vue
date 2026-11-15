<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import {
    Activity,
    AlertCircle,
    ArrowRight,
    Award,
    BookOpen,
    CheckCircle2,
    Code,
    Compass,
    Download,
    Eye,
    FileCheck,
    FileCode,
    Fingerprint,
    HelpCircle,
    Layers,
    Lightbulb,
    MessageSquare,
    Palette,
    Search,
    Shield,
    Sparkles,
    Target,
    UserCheck,
    Users,
    Workflow,
    Zap,
} from 'lucide-vue-next';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Metodología UI/UX', href: '/metodologia-ui-ux' },
];

const activeTab = ref<number>(1);

const phases = [
    {
        id: 1,
        title: '1. Empatizar e Investigar',
        subtitle: 'Don Norman & IDEO',
        category: 'UX (Experiencia de Usuario)',
        icon: Users,
        color: 'from-blue-500 to-indigo-600',
        badge: 'Fase Inicial UX',
    },
    {
        id: 2,
        title: '2. Definir el Problema',
        subtitle: 'IDEO / Design Thinking',
        category: 'UX (Experiencia de Usuario)',
        icon: Target,
        color: 'from-amber-500 to-orange-600',
        badge: 'Síntesis & Empatía',
    },
    {
        id: 3,
        title: '3. Idear y Estructurar',
        subtitle: 'Jesse James Garrett',
        category: 'UX (Estructura y Flujos)',
        icon: Lightbulb,
        color: 'from-emerald-500 to-teal-600',
        badge: 'Planos de Garrett',
    },
    {
        id: 4,
        title: '4. Prototipar',
        subtitle: 'Garrett - Esqueleto',
        category: 'UX / UI (Fidelidad)',
        icon: Layers,
        color: 'from-cyan-500 to-blue-600',
        badge: 'Wireframes & Layout',
    },
    {
        id: 5,
        title: '5. Diseño Visual',
        subtitle: 'Garrett - Superficie',
        category: 'UI (Interfaz Visual)',
        icon: Palette,
        color: 'from-pink-500 to-rose-600',
        badge: 'Design System',
    },
    {
        id: 6,
        title: '6. Testear y Evaluar',
        subtitle: 'Nielsen Norman Group',
        category: 'UX / UI (Validación)',
        icon: Award,
        color: 'from-purple-500 to-violet-600',
        badge: '10 Heurísticas',
    },
];

// Heurísticas de Nielsen para la fase 6
const nielsenHeuristics = [
    {
        id: 1,
        name: 'Visibilidad del estado del sistema',
        desc: 'El sistema mantiene informado al cajero/cliente sobre lo que ocurre con badges en tiempo real (Activa, Bloqueada, Procesada) y spinners durante transacciones.',
        applied: 'Badges semánticos en tablas, alertas dinámicas de éxito o error al registrar depósitos/retiros.',
    },
    {
        id: 2,
        name: 'Correspondencia entre el sistema y el mundo real',
        desc: 'Uso de terminología bancaria estándar en español (Abono, Débito, Tasa de Interés Nominal, Amortización Francesa/Alemana).',
        applied: 'Formularios con lenguaje claro sin tecnicismos de base de datos.',
    },
    {
        id: 3,
        name: 'Control y libertad del usuario',
        desc: 'Soporte para cancelar operaciones antes de la confirmación final y navegación jerárquica con Breadcrumbs.',
        applied: 'Modales de confirmación antes de debitar fondos, enlaces de retorno en cada vista.',
    },
    {
        id: 4,
        name: 'Consistencia y estándares',
        desc: 'Mismos patrones de interacción, tablas paginadas, botones de acción y modales en todos los módulos (Depósitos, Retiros, Créditos).',
        applied: 'Diseño unificado basado en Tailwind CSS y componentes de interfaz reutilizables.',
    },
    {
        id: 5,
        name: 'Prevención de errores',
        desc: 'Verificación de saldo disponible antes de procesar retiros o transferencias, impidiendo saldos negativos.',
        applied: 'El formulario desactiva el botón de retiro si el monto supera el saldo disponible de la cuenta.',
    },
    {
        id: 6,
        name: 'Reconocimiento antes que recuerdo',
        desc: 'Búsqueda reactiva de cuentas que autocompleta el nombre y carnet del titular destino en transferencias.',
        applied: 'El usuario no necesita memorizar nombres; el sistema consulta la API y muestra la identidad verificada.',
    },
    {
        id: 7,
        name: 'Flexibilidad y eficiencia de uso',
        desc: 'Atajos y botones rápidos de montos predefinidos (Bs. 50, 100, 200, 500) en retiros y depósitos.',
        applied: 'Agiliza la atención en caja reduciendo la digitación manual a un solo clic.',
    },
    {
        id: 8,
        name: 'Diseño estético y minimalista',
        desc: 'Interfaces limpias, sin sobrecarga cognitiva, destacando montos con tipografía tabular.',
        applied: 'Tarjetas de resumen financiero con contraste de colores optimizado (WCAG AAA).',
    },
    {
        id: 9,
        name: 'Ayudar a reconocer, diagnosticar y recuperarse de errores',
        desc: 'Mensajes de validación claros y contextuales junto a cada campo erróneo, no códigos crípticos.',
        applied: '"El monto a transferir excede su saldo disponible de Bs. 4,500.00".',
    },
    {
        id: 10,
        name: 'Ayuda y documentación',
        desc: 'Simulador de créditos con tabla interactiva de amortización y explicaciones de cada cuota.',
        applied: 'Sección metodológica interactiva accesible en el sistema y exportación de comprobantes en PDF.',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Metodología UI/UX - Proceso de Diseño" />

        <div class="min-h-screen bg-slate-950 text-slate-100 p-4 md:p-8 space-y-8">
            <!-- Header Académico Institucional -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 border border-indigo-500/30 p-6 md:p-8 shadow-2xl">
                <div class="absolute -right-10 -top-10 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -left-10 -bottom-10 w-64 h-64 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-semibold tracking-wider uppercase border border-indigo-500/30">
                            <Sparkles class="w-3.5 h-3.5 text-amber-400" />
                            Ingeniería de Software 1 • UAGRM FICH
                        </div>
                        <h1 class="text-2xl md:text-4xl font-extrabold tracking-tight text-white">
                            El Proceso de Diseño UI/UX en el Sistema Bancario
                        </h1>
                        <p class="text-sm md:text-base text-slate-300 max-w-3xl">
                            Fundamentación metodológica rigurosa basada en los máximos referentes de la industria:
                            <span class="text-amber-400 font-semibold">Don Norman</span> (Psicología Cognitiva),
                            <span class="text-blue-400 font-semibold">Jesse James Garrett</span> (Los 5 Planos),
                            <span class="text-emerald-400 font-semibold">IDEO</span> (Design Thinking) y
                            <span class="text-purple-400 font-semibold">Nielsen Norman Group</span> (10 Heurísticas).
                        </p>
                        <div class="pt-2 flex flex-wrap items-center gap-4 text-xs text-slate-400">
                            <span class="flex items-center gap-1.5"><BookOpen class="w-4 h-4 text-indigo-400" /> Docente: Msc. Ing. Ligeron Rolando</span>
                            <span class="flex items-center gap-1.5"><Shield class="w-4 h-4 text-emerald-400" /> Multi-Tenant: Banco Unión & BMSC</span>
                            <span class="flex items-center gap-1.5"><Fingerprint class="w-4 h-4 text-amber-400" /> Biometría SHA-256</span>
                        </div>
                    </div>

                    <!-- Botón de Descarga de PDF -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
                        <a
                            href="/Documento_Proceso_Diseno_UI_UX_Sistema_Bancario.pdf"
                            target="_blank"
                            class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-bold text-sm shadow-lg shadow-amber-500/20 transition-all hover:scale-[1.02] active:scale-[0.98]"
                        >
                            <Download class="w-4 h-4" />
                            Descargar Informe PDF
                        </a>
                    </div>
                </div>
            </div>

            <!-- Navegador de Fases (Selector de Pestañas Interactivas) -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                <button
                    v-for="phase in phases"
                    :key="phase.id"
                    @click="activeTab = phase.id"
                    class="group relative flex flex-col p-4 rounded-xl border text-left transition-all duration-300"
                    :class="[
                        activeTab === phase.id
                            ? 'bg-slate-900 border-indigo-500 shadow-lg shadow-indigo-500/20 ring-2 ring-indigo-500/50 scale-[1.02]'
                            : 'bg-slate-900/60 border-slate-800 hover:bg-slate-800/80 hover:border-slate-700'
                    ]"
                >
                    <div class="flex items-center justify-between mb-2">
                        <div
                            class="w-8 h-8 rounded-lg flex items-center justify-center bg-gradient-to-tr text-white shadow"
                            :class="phase.color"
                        >
                            <component :is="phase.icon" class="w-4 h-4" />
                        </div>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-800 text-slate-400">
                            #{{ phase.id }}
                        </span>
                    </div>
                    <span class="font-bold text-xs md:text-sm text-white group-hover:text-indigo-300 leading-snug">
                        {{ phase.title }}
                    </span>
                    <span class="text-[11px] text-slate-400 mt-1">
                        {{ phase.subtitle }}
                    </span>
                    <div
                        v-if="activeTab === phase.id"
                        class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-amber-500 rounded-b-xl"
                    ></div>
                </button>
            </div>

            <!-- Contenedor del Contenido Activo de la Fase -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 md:p-8 shadow-xl">
                <!-- FASE 1: Empatizar e Investigar -->
                <div v-if="activeTab === 1" class="space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-wider text-blue-400">Fase 1 • UX Research</span>
                            <h2 class="text-xl md:text-2xl font-bold text-white flex items-center gap-2">
                                <Users class="w-6 h-6 text-blue-400" />
                                Empatizar e Investigar (Don Norman / IDEO)
                            </h2>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/20 text-xs font-medium">
                            Investigación de Campo & Personas
                        </span>
                    </div>

                    <p class="text-slate-300 text-sm md:text-base leading-relaxed">
                        Según <strong>Don Norman</strong> en <em>"The Design of Everyday Things"</em>, el diseño centrado en el usuario comienza descubriendo las necesidades humanas reales y las limitaciones cognitivas del operador. Realizamos observación contextual en agencias bancarias bolivianas y modelamos dos perfiles principales:
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- User Persona 1: Cajero -->
                        <div class="rounded-xl bg-slate-950/70 border border-slate-800 p-5 space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white font-bold text-lg">
                                    JM
                                </div>
                                <div>
                                    <h3 class="font-bold text-white text-base">Javier Mendoza (34 años)</h3>
                                    <p class="text-xs text-blue-400 font-medium">Cajero Bancario en Ventanilla • Operativo</p>
                                </div>
                            </div>
                            <div class="space-y-2 text-xs text-slate-300">
                                <p><strong class="text-white">Objetivo:</strong> Procesar depósitos y retiros en menos de 90 segundos con cero errores de digitación en moneda nacional (BOB).</p>
                                <p><strong class="text-white">Frustraciones:</strong> Caídas del sistema al ingresar el carnet, interfaces con letra pequeña que causan fatiga visual tras 8 horas de turno.</p>
                                <p><strong class="text-white">Solución de Diseño:</strong> Modo Oscuro para reducir fatiga ocular, atajos numéricos, validación predictiva de titulares y confirmación biométrica con sensor digital.</p>
                            </div>
                        </div>

                        <!-- User Persona 2: Cliente -->
                        <div class="rounded-xl bg-slate-950/70 border border-slate-800 p-5 space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-600 flex items-center justify-center text-white font-bold text-lg">
                                    RV
                                </div>
                                <div>
                                    <h3 class="font-bold text-white text-base">Rosa Villagómez (52 años)</h3>
                                    <p class="text-xs text-emerald-400 font-medium">Comerciante Minorista • Cliente de Banca</p>
                                </div>
                            </div>
                            <div class="space-y-2 text-xs text-slate-300">
                                <p><strong class="text-white">Objetivo:</strong> Depositar sus ventas diarias con seguridad y recibir un comprobante impreso legible con código de verificación.</p>
                                <p><strong class="text-white">Frustraciones:</strong> Filas largas, desconfianza de que el dinero no se abone a la cuenta correcta, términos bancarios confusos.</p>
                                <p><strong class="text-white">Solución de Diseño:</strong> Comprobante en pantalla con confirmación clara de titular, saldo anterior y saldo resultante antes del clic definitivo.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FASE 2: Definir el Problema -->
                <div v-if="activeTab === 2" class="space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-wider text-amber-400">Fase 2 • Síntesis y Enfoque</span>
                            <h2 class="text-xl md:text-2xl font-bold text-white flex items-center gap-2">
                                <Target class="w-6 h-6 text-amber-400" />
                                Definir el Problema (IDEO / Design Thinking)
                            </h2>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 text-xs font-medium">
                            Mapa de Empatía Interactivo
                        </span>
                    </div>

                    <p class="text-slate-300 text-sm md:text-base">
                        Sintetizamos los hallazgos en un <strong>Mapa de Empatía</strong> multidimensional para aislar los dolores críticos y formular el <em>Problem Statement</em> formal:
                    </p>

                    <!-- Interactive Empathy Map Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 rounded-xl bg-slate-950/80 border border-amber-500/20 space-y-2">
                            <span class="text-xs font-bold text-amber-400 flex items-center gap-1.5">
                                <Lightbulb class="w-4 h-4" /> ¿Qué Piensa y Siente?
                            </span>
                            <ul class="text-xs text-slate-300 space-y-1 list-disc list-inside">
                                <li>Temor al descuadre de caja al final de la jornada.</li>
                                <li>Deseo de transparencia total en los intereses y cuotas de préstamos.</li>
                                <li>Preocupación por la seguridad de los fondos ante fraudes o suplantación de identidad.</li>
                            </ul>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-950/80 border border-blue-500/20 space-y-2">
                            <span class="text-xs font-bold text-blue-400 flex items-center gap-1.5">
                                <Eye class="w-4 h-4" /> ¿Qué Ve?
                            </span>
                            <ul class="text-xs text-slate-300 space-y-1 list-disc list-inside">
                                <li>Filas de espera de más de 20 minutos en horas pico.</li>
                                <li>Interfaces tradicionales saturadas con docenas de campos irrelevantes.</li>
                                <li>Comprobantes térmicos ilegibles sin desglose de comisiones ni saldos.</li>
                            </ul>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-950/80 border border-rose-500/20 space-y-2">
                            <span class="text-xs font-bold text-rose-400 flex items-center gap-1.5">
                                <AlertCircle class="w-4 h-4" /> Esfuerzos / Dolores (Pains)
                            </span>
                            <ul class="text-xs text-slate-300 space-y-1 list-disc list-inside">
                                <li>Errores tipográficos al ingresar números de cuenta largos (10 dígitos).</li>
                                <li>Dificultad para calcular el plan de pagos bajo los métodos Francés y Alemán.</li>
                                <li>Falta de validación del carnet o huella en retiros de alto valor.</li>
                            </ul>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-950/80 border border-emerald-500/20 space-y-2">
                            <span class="text-xs font-bold text-emerald-400 flex items-center gap-1.5">
                                <CheckCircle2 class="w-4 h-4" /> Resultados / Deseos (Gains)
                            </span>
                            <ul class="text-xs text-slate-300 space-y-1 list-disc list-inside">
                                <li>Búsqueda instantánea con autocompletado del nombre del cliente en pantalla.</li>
                                <li>Simulador de amortización dinámico con exportación inmediata.</li>
                                <li>Soporte Multi-Banco nativo (Banco Unión y BMSC) en una sola plataforma.</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Problem Statement Box -->
                    <div class="p-4 rounded-xl bg-indigo-950/50 border border-indigo-500/40">
                        <span class="text-xs font-bold uppercase tracking-wider text-indigo-300">Declaración del Problema (How Might We):</span>
                        <p class="text-sm text-slate-200 mt-1 font-medium italic">
                            "¿Cómo podríamos reducir el tiempo de atención en ventanilla bancaria en un 60% y eliminar los errores de digitación en depósitos y retiros, garantizando al mismo tiempo la verificación biométrica y el soporte multi-entidad institucional?"
                        </p>
                    </div>
                </div>

                <!-- FASE 3: Idear y Estructurar -->
                <div v-if="activeTab === 3" class="space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-400">Fase 3 • Arquitectura de Información</span>
                            <h2 class="text-xl md:text-2xl font-bold text-white flex items-center gap-2">
                                <Workflow class="w-6 h-6 text-emerald-400" />
                                Idear y Estructurar (Jesse James Garrett)
                            </h2>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-medium">
                            Planos de Alcance y Estructura
                        </span>
                    </div>

                    <p class="text-slate-300 text-sm md:text-base">
                        Aplicando el modelo de <strong>The Elements of User Experience</strong> de <strong>Jesse James Garrett</strong>, estructuramos la información y el árbol de navegación del sistema bancario:
                    </p>

                    <!-- Garrett's 5 Planes Hierarchy -->
                    <div class="space-y-3">
                        <div class="p-3.5 rounded-lg bg-slate-950/80 border-l-4 border-emerald-500 flex items-start justify-between">
                            <div>
                                <span class="font-bold text-white text-sm">1. Plano de Estrategia</span>
                                <p class="text-xs text-slate-400">Necesidades del usuario bancario (rapidez, cero errores) + Objetivos del negocio (cumplimiento ASFI, trazabilidad multi-tenant).</p>
                            </div>
                            <span class="text-[10px] px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-semibold">Base</span>
                        </div>

                        <div class="p-3.5 rounded-lg bg-slate-950/80 border-l-4 border-teal-500 flex items-start justify-between">
                            <div>
                                <span class="font-bold text-white text-sm">2. Plano de Alcance</span>
                                <p class="text-xs text-slate-400">Especificaciones funcionales: Módulos de Clientes, Cuentas, Depósitos, Retiros, Transferencias, Simulador Francés/Alemán y Auditoría.</p>
                            </div>
                            <span class="text-[10px] px-2 py-0.5 rounded bg-teal-500/20 text-teal-300 font-semibold">Requerimientos</span>
                        </div>

                        <div class="p-3.5 rounded-lg bg-slate-950/80 border-l-4 border-cyan-500 flex items-start justify-between">
                            <div>
                                <span class="font-bold text-white text-sm">3. Plano de Estructura</span>
                                <p class="text-xs text-slate-400">Diseño de interacción (IxD) y arquitectura de información (árbol de navegación con RBAC por roles: Administrador, Cajero, Ejecutivo, Cliente).</p>
                            </div>
                            <span class="text-[10px] px-2 py-0.5 rounded bg-cyan-500/20 text-cyan-300 font-semibold">Navegación</span>
                        </div>
                    </div>

                    <!-- User Flow Interactivo -->
                    <div class="p-5 rounded-xl bg-slate-950/70 border border-slate-800 space-y-3">
                        <h3 class="font-bold text-white text-sm flex items-center gap-2">
                            <Zap class="w-4 h-4 text-amber-400" />
                            Flujo de Usuario Optimizado: Retiro en Ventanilla con Biometría
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-center">
                            <div class="p-3 rounded-lg bg-slate-900 border border-slate-800">
                                <span class="text-xs font-bold text-indigo-400">Paso 1</span>
                                <p class="text-xs text-slate-300 font-medium mt-1">Búsqueda rápida de cuenta por Nro. o Carnet</p>
                            </div>
                            <div class="p-3 rounded-lg bg-slate-900 border border-slate-800">
                                <span class="text-xs font-bold text-indigo-400">Paso 2</span>
                                <p class="text-xs text-slate-300 font-medium mt-1">Validación visual de saldo y titular</p>
                            </div>
                            <div class="p-3 rounded-lg bg-slate-900 border border-slate-800">
                                <span class="text-xs font-bold text-indigo-400">Paso 3</span>
                                <p class="text-xs text-slate-300 font-medium mt-1">Captura de huella y validación biométrica</p>
                            </div>
                            <div class="p-3 rounded-lg bg-slate-900 border border-slate-800">
                                <span class="text-xs font-bold text-indigo-400">Paso 4</span>
                                <p class="text-xs text-slate-300 font-medium mt-1">Generación de comprobante con Hash SHA-256</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FASE 4: Prototipar -->
                <div v-if="activeTab === 4" class="space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-wider text-cyan-400">Fase 4 • Prototipado y Esqueleto</span>
                            <h2 class="text-xl md:text-2xl font-bold text-white flex items-center gap-2">
                                <Layers class="w-6 h-6 text-cyan-400" />
                                Prototipar (Jesse J. Garrett - Plano de Esqueleto)
                            </h2>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 text-xs font-medium">
                            Wireframes de Baja a Alta Fidelidad
                        </span>
                    </div>

                    <p class="text-slate-300 text-sm md:text-base">
                        El <strong>Plano de Esqueleto</strong> define la disposición física de los elementos interactivos, la jerarquía visual de información y la navegación en pantalla:
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-5 rounded-xl bg-slate-950/80 border border-slate-800 space-y-3">
                            <span class="text-xs font-bold text-cyan-400 uppercase tracking-wide">Patrones de Lectura Visual</span>
                            <ul class="text-xs text-slate-300 space-y-2 list-disc list-inside">
                                <li><strong>Patrón en F:</strong> Los cajeros escanean de izquierda a derecha el saldo actual, el nombre del titular y luego el botón de confirmación.</li>
                                <li><strong>Tarjetas Modulares:</strong> Cada métrica clave (Total Depósitos, Retiros del Día, Préstamos Activos) se aísla en una tarjeta con icono y porcentaje.</li>
                                <li><strong>Estado Empty State:</strong> Cuando una tabla carece de transacciones, se presenta una ilustración explicativa guiando la primera acción.</li>
                            </ul>
                        </div>

                        <div class="p-5 rounded-xl bg-slate-950/80 border border-slate-800 space-y-3">
                            <span class="text-xs font-bold text-cyan-400 uppercase tracking-wide">Microinteracciones y Feedback</span>
                            <ul class="text-xs text-slate-300 space-y-2 list-disc list-inside">
                                <li><strong>Skeletons reactivos:</strong> Transición suave durante la carga de cuentas bancarias.</li>
                                <li><strong>Toasts de notificación:</strong> Alerta flotante no invasiva al completar cada depósito con éxito.</li>
                                <li><strong>Doble confirmación modal:</strong> Evita transferencias erróneas solicitando revisión final del monto en pantalla antes de ejecutar.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- FASE 5: Diseño Visual -->
                <div v-if="activeTab === 5" class="space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-wider text-pink-400">Fase 5 • Superficie y UI</span>
                            <h2 class="text-xl md:text-2xl font-bold text-white flex items-center gap-2">
                                <Palette class="w-6 h-6 text-pink-400" />
                                Diseño Visual (Jesse J. Garrett - Superficie)
                            </h2>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-pink-500/10 text-pink-400 border border-pink-500/20 text-xs font-medium">
                            Design System Multi-Banco
                        </span>
                    </div>

                    <p class="text-slate-300 text-sm md:text-base">
                        El <strong>Plano de Superficie</strong> traduce la arquitectura lógica en una interfaz visual atractiva, cohesiva y altamente accesible, soportando identidad institucional dinámica:
                    </p>

                    <!-- Paleta de Colores Dinámica -->
                    <div class="space-y-4">
                        <h3 class="font-bold text-white text-sm">Colores Institucionales Multi-Tenant</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                            <div class="p-3 rounded-xl bg-blue-950 border border-blue-500/30 text-center">
                                <div class="w-8 h-8 rounded-full bg-blue-600 mx-auto mb-2 shadow"></div>
                                <span class="font-bold text-white text-xs block">Banco Unión Primario</span>
                                <span class="text-[11px] text-blue-300">#1e3a8a • Azul Soberano</span>
                            </div>
                            <div class="p-3 rounded-xl bg-amber-950 border border-amber-500/30 text-center">
                                <div class="w-8 h-8 rounded-full bg-amber-500 mx-auto mb-2 shadow"></div>
                                <span class="font-bold text-white text-xs block">Banco Unión Acento</span>
                                <span class="text-[11px] text-amber-300">#f59e0b • Dorado Institucional</span>
                            </div>
                            <div class="p-3 rounded-xl bg-emerald-950 border border-emerald-500/30 text-center">
                                <div class="w-8 h-8 rounded-full bg-emerald-700 mx-auto mb-2 shadow"></div>
                                <span class="font-bold text-white text-xs block">BMSC Primario</span>
                                <span class="text-[11px] text-emerald-300">#047857 • Verde Corporativo</span>
                            </div>
                            <div class="p-3 rounded-xl bg-yellow-950 border border-yellow-500/30 text-center">
                                <div class="w-8 h-8 rounded-full bg-yellow-500 mx-auto mb-2 shadow"></div>
                                <span class="font-bold text-white text-xs block">BMSC Acento</span>
                                <span class="text-[11px] text-yellow-300">#eab308 • Oro Mercantil</span>
                            </div>
                        </div>

                        <!-- Tipografía y Modo Oscuro -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                            <div class="p-4 rounded-xl bg-slate-950/80 border border-slate-800 space-y-2">
                                <span class="text-xs font-bold text-pink-400">Jerarquía Tipográfica Numérica</span>
                                <p class="text-xs text-slate-300">
                                    Utilizamos números tabulares (<code class="text-pink-300">font-variant-numeric: tabular-nums</code>) para garantizar que los decimales y cifras monetarias se alineen perfectamente en columnas de contabilidad.
                                </p>
                            </div>
                            <div class="p-4 rounded-xl bg-slate-950/80 border border-slate-800 space-y-2">
                                <span class="text-xs font-bold text-pink-400">Soporte Dark Mode / Light Mode</span>
                                <p class="text-xs text-slate-300">
                                    Diseño optimizado para trabajo diurno en ventanilla y modo oscuro para reducir fatiga visual del cajero en jornadas continuas, cumpliendo con la directiva de accesibilidad WCAG AAA.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FASE 6: Testear y Evaluar -->
                <div v-if="activeTab === 6" class="space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-wider text-purple-400">Fase 6 • Evaluación Heurística</span>
                            <h2 class="text-xl md:text-2xl font-bold text-white flex items-center gap-2">
                                <Award class="w-6 h-6 text-purple-400" />
                                Testear y Evaluar (Nielsen Norman Group)
                            </h2>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-purple-500/10 text-purple-400 border border-purple-500/20 text-xs font-medium">
                            10 Heurísticas de Usabilidad Cumplidas
                        </span>
                    </div>

                    <p class="text-slate-300 text-sm md:text-base">
                        Evaluamos rigurosamente el sistema bajo las <strong>10 Heurísticas de Usabilidad de Jakob Nielsen</strong>, garantizando que cada pantalla implemente mecanismos comprobados de usabilidad bancaria:
                    </p>

                    <!-- Lista de las 10 Heurísticas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div
                            v-for="h in nielsenHeuristics"
                            :key="h.id"
                            class="p-4 rounded-xl bg-slate-950/80 border border-slate-800/80 hover:border-purple-500/40 transition-colors space-y-2"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-bold text-sm text-white">
                                    <span class="text-purple-400 mr-1.5">#{{ h.id }}</span>
                                    {{ h.name }}
                                </h3>
                                <CheckCircle2 class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" />
                            </div>
                            <p class="text-xs text-slate-400">{{ h.desc }}</p>
                            <div class="text-[11px] p-2 rounded bg-purple-950/30 text-purple-300 border border-purple-500/20">
                                <strong>Aplicado en el sistema:</strong> {{ h.applied }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer con Referencias Académicas -->
            <div class="rounded-xl bg-slate-900/50 border border-slate-800 p-4 text-xs text-slate-400 flex flex-col md:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <BookOpen class="w-4 h-4 text-indigo-400" />
                    <span><strong>Bibliografía:</strong> Norman (2013), Garrett (2011), Nielsen & Budiu (2012), IDEO Field Guide to Human-Centered Design.</span>
                </div>
                <div class="text-slate-500 text-[11px]">
                    Facultad Integral del Chaco • Camiri, Santa Cruz, Bolivia • 2026
                </div>
            </div>
        </div>
    </AppLayout>
</template>
