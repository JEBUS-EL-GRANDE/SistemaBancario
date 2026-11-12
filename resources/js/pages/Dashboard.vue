<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import DashboardMetrics from '@/components/DashboardMetrics.vue';
import TransactionHistory from '@/components/TransactionHistory.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import {
    ArrowDownLeft,
    ArrowLeftRight,
    ArrowUpRight,
    Banknote,
    CreditCard,
    FileText,
    TrendingUp,
    Users,
    Wallet,
} from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
const auth = page.props.auth as { user: { name: string; apellidos: string; rol: string } };
const user = auth.user;
const stats = page.props.stats as Record<string, number>;

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }];

const rolLabel: Record<string, string> = {
    administrador: 'Administrador',
    cajero: 'Cajero',
    ejecutivo_credito: 'Ejecutivo de Crédito',
    cliente: 'Cliente',
};

const rolBadgeClass: Record<string, string> = {
    administrador: 'badge-admin',
    cajero: 'badge-cajero',
    ejecutivo_credito: 'badge-ejecutivo',
    cliente: 'badge-cliente',
};

/** Formatea números grandes con separador de miles */
const fmt = (n: number) => n?.toLocaleString('es-BO') ?? '0';
/** Formatea montos como moneda BOB */
const money = (n: number) => `Bs. ${n?.toLocaleString('es-BO', { minimumFractionDigits: 2 }) ?? '0.00'}`;

const currentStats = computed(() => {
    if (user.rol === 'administrador') {
        return [
            { label: 'Clientes registrados', value: fmt(stats.clientes_registrados), icon: Users, color: '#3b82f6', bg: '#eff6ff' },
            { label: 'Cuentas activas', value: fmt(stats.cuentas_activas), icon: Wallet, color: '#10b981', bg: '#ecfdf5' },
            { label: 'Depósitos hoy', value: `${fmt(stats.depositos_hoy)} (${money(stats.monto_depositos_hoy)})`, icon: ArrowDownLeft, color: '#8b5cf6', bg: '#f5f3ff' },
            { label: 'Retiros hoy', value: `${fmt(stats.retiros_hoy)} (${money(stats.monto_retiros_hoy)})`, icon: ArrowUpRight, color: '#f59e0b', bg: '#fffbeb' },
            { label: 'Préstamos activos', value: fmt(stats.prestamos_activos), icon: CreditCard, color: '#ef4444', bg: '#fef2f2' },
            { label: 'Cartera total', value: money(stats.cartera_total), icon: TrendingUp, color: '#06b6d4', bg: '#ecfeff' },
        ];
    }
    if (user.rol === 'cajero') {
        return [
            { label: 'Depósitos realizados hoy', value: `${fmt(stats.depositos_hoy)} (${money(stats.monto_depositos_hoy)})`, icon: Banknote, color: '#10b981', bg: '#ecfdf5' },
            { label: 'Retiros procesados hoy', value: `${fmt(stats.retiros_hoy)} (${money(stats.monto_retiros_hoy)})`, icon: ArrowUpRight, color: '#f59e0b', bg: '#fffbeb' },
            { label: 'Cuentas consultadas', value: fmt(stats.cuentas_consultadas), icon: FileText, color: '#8b5cf6', bg: '#f5f3ff' },
        ];
    }
    if (user.rol === 'cliente') {
        return [
            { label: 'Saldo total disponible', value: money(stats.saldo_total), icon: Wallet, color: '#10b981', bg: '#ecfdf5' },
            { label: 'Cuentas activas', value: fmt(stats.cuentas_activas), icon: CreditCard, color: '#3b82f6', bg: '#eff6ff' },
            { label: 'Préstamos vigentes', value: fmt(stats.prestamos_activos), icon: FileText, color: '#f59e0b', bg: '#fffbeb' },
            { label: 'Deuda pendiente', value: money(stats.deuda_total), icon: TrendingUp, color: '#ef4444', bg: '#fef2f2' },
        ];
    }
    return [
        { label: 'Solicitudes pendientes', value: fmt(stats.solicitudes_pendientes), icon: FileText, color: '#f59e0b', bg: '#fffbeb' },
        { label: 'Aprobados hoy', value: fmt(stats.aprobados_hoy), icon: TrendingUp, color: '#10b981', bg: '#ecfdf5' },
        { label: 'Cartera activa', value: money(stats.cartera_activa), icon: CreditCard, color: '#3b82f6', bg: '#eff6ff' },
        { label: 'En mora', value: fmt(stats.en_mora), icon: ArrowUpRight, color: '#ef4444', bg: '#fef2f2' },
    ];
});



const quickActionsAdmin = [
    { label: 'Registrar Cliente', href: '/clientes/create', icon: Users, color: '#3b82f6' },
    { label: 'Abrir Cuenta', href: '/cuentas/create', icon: Wallet, color: '#10b981' },
    { label: 'Transferencia', href: '/transferencias/create', icon: ArrowLeftRight, color: '#06b6d4' },
    { label: 'Gestionar Usuarios', href: '/usuarios', icon: Users, color: '#8b5cf6' },
    { label: 'Ver Reportes', href: '/reportes', icon: FileText, color: '#f59e0b' },
];

const quickActionsCajero = [
    { label: 'Registrar Depósito', href: '/depositos/create', icon: Banknote, color: '#10b981' },
    { label: 'Registrar Retiro', href: '/retiros/create', icon: ArrowUpRight, color: '#f59e0b' },
    { label: 'Transferencia', href: '/transferencias/create', icon: ArrowLeftRight, color: '#06b6d4' },
    { label: 'Pago de Préstamo', href: '/pagos-prestamo/create', icon: CreditCard, color: '#3b82f6' },
    { label: 'Consultar Cuenta', href: '/cuentas', icon: Wallet, color: '#8b5cf6' },
];

const quickActionsEjecutivo = [
    { label: 'Nueva Solicitud', href: '/prestamos/create', icon: FileText, color: '#f59e0b' },
    { label: 'Evaluar Solicitudes', href: '/prestamos', icon: TrendingUp, color: '#10b981' },
    { label: 'Cartera de Clientes', href: '/clientes', icon: Users, color: '#3b82f6' },
    { label: 'Tabla de Amortización', href: '/prestamos/simulador', icon: CreditCard, color: '#8b5cf6' },
];

const quickActionsCliente = [
    { label: 'Mis Cuentas', href: '/cuentas', icon: Wallet, color: '#10b981' },
    { label: 'Realizar Transferencia', href: '/transferencias/create', icon: ArrowLeftRight, color: '#06b6d4' },
    { label: 'Simulador de Cuotas', href: '/prestamos/simulador', icon: FileText, color: '#8b5cf6' },
];

const currentActions =
    user.rol === 'administrador'
        ? quickActionsAdmin
        : user.rol === 'cajero'
          ? quickActionsCajero
          : user.rol === 'ejecutivo_credito'
            ? quickActionsEjecutivo
            : quickActionsCliente;
</script>

<template>
    <Head title="Dashboard — Sistema Bancario" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="dashboard-wrapper">

            <!-- Encabezado de bienvenida -->
            <div class="welcome-header">
                <div class="welcome-info">
                    <div class="welcome-avatar">
                        {{ user.name.charAt(0) }}{{ user.apellidos?.charAt(0) ?? '' }}
                    </div>
                    <div>
                        <h1 class="welcome-title">
                            Bienvenido, {{ user.name }} {{ user.apellidos }}
                        </h1>
                        <div class="welcome-meta">
                            <span :class="['role-badge', rolBadgeClass[user.rol]]">
                                {{ rolLabel[user.rol] }}
                            </span>
                            <span class="welcome-date">
                                {{ new Date().toLocaleDateString('es-BO', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjetas de métricas -->
            <div class="stats-grid">
                <div
                    v-for="stat in currentStats"
                    :key="stat.label"
                    class="stat-card"
                >
                    <div class="stat-icon-wrapper" :style="{ background: stat.bg }">
                        <component :is="stat.icon" :size="22" :style="{ color: stat.color }" />
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">{{ stat.value }}</div>
                        <div class="stat-label">{{ stat.label }}</div>
                    </div>
                </div>
            </div>

            <!-- Acciones rápidas -->
            <div class="section-header">
                <h2 class="section-title">Acciones rápidas</h2>
                <p class="section-sub">Accede a las operaciones más frecuentes</p>
            </div>

            <div class="actions-grid">
                <a
                    v-for="action in currentActions"
                    :key="action.label"
                    :href="action.href"
                    class="action-card"
                >
                    <div class="action-icon" :style="{ background: action.color + '18', color: action.color }">
                        <component :is="action.icon" :size="24" />
                    </div>
                    <span class="action-label">{{ action.label }}</span>
                    <ArrowUpRight class="action-arrow" :size="16" />
                </a>
            </div>

            <!-- Banner informativo según rol -->
            <div class="info-banner" :class="'banner-' + user.rol">
                <div class="banner-icon">
                    <component :is="user.rol === 'cajero' ? Banknote : user.rol === 'ejecutivo_credito' ? TrendingUp : Users" :size="20" />
                </div>
                <div class="banner-content">
                    <strong v-if="user.rol === 'administrador'">Panel de Administración</strong>
                    <strong v-else-if="user.rol === 'cajero'">Panel de Operaciones de Ventanilla</strong>
                    <strong v-else>Panel de Créditos</strong>
                    <p v-if="user.rol === 'administrador'">Tienes acceso completo al sistema: usuarios, clientes, cuentas, transacciones y reportes.</p>
                    <p v-else-if="user.rol === 'cajero'">Puedes registrar depósitos, retiros y pagos de préstamos. Las métricas se actualizarán con cada operación.</p>
                    <p v-else>Puedes gestionar solicitudes de crédito, aprobar préstamos y consultar la cartera de clientes.</p>
                </div>
            </div>

            <!-- Componentes de Métricas y Transacciones Recientes -->
            <DashboardMetrics />
            <TransactionHistory />

        </div>
    </AppLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

* { box-sizing: border-box; }

.dashboard-wrapper {
    font-family: 'Inter', sans-serif;
    padding: 1.5rem;
    max-width: 1280px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
}

/* ── Bienvenida ── */
.welcome-header {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
    border-radius: 16px;
    padding: 1.75rem 2rem;
    color: white;
}

.welcome-info {
    display: flex;
    align-items: center;
    gap: 1.25rem;
}

.welcome-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6, #60a5fa);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    font-weight: 700;
    color: white;
    flex-shrink: 0;
    letter-spacing: -1px;
}

.welcome-title {
    font-size: 1.375rem;
    font-weight: 700;
    margin: 0 0 0.5rem;
}

.welcome-meta {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.role-badge {
    display: inline-block;
    padding: 0.2rem 0.75rem;
    border-radius: 100px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.badge-admin { background: #fbbf24; color: #78350f; }
.badge-cajero { background: #34d399; color: #064e3b; }
.badge-ejecutivo { background: #818cf8; color: #1e1b4b; }
.badge-cliente { background: #94a3b8; color: #1e293b; }

.welcome-date {
    font-size: 0.8rem;
    color: rgba(255,255,255,0.55);
    text-transform: capitalize;
}

/* ── Stats grid ── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1rem;
}

.stat-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: box-shadow 0.2s, transform 0.2s;
}
.stat-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    transform: translateY(-2px);
}

.stat-icon-wrapper {
    width: 46px;
    height: 46px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1;
    margin-bottom: 0.25rem;
}
.stat-label {
    font-size: 0.8rem;
    color: #64748b;
    font-weight: 500;
}

/* ── Sección acciones ── */
.section-header { margin-bottom: -0.75rem; }
.section-title { font-size: 1.125rem; font-weight: 700; color: #0f172a; margin: 0 0 0.25rem; }
.section-sub { font-size: 0.875rem; color: #64748b; margin: 0; }

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 0.875rem;
}

.action-card {
    background: white;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.875rem;
    text-decoration: none;
    color: #1e293b;
    cursor: pointer;
    transition: all 0.2s;
    position: relative;
}
.action-card:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 16px rgba(59,130,246,0.12);
    transform: translateY(-2px);
}

.action-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.action-label { font-size: 0.875rem; font-weight: 600; flex: 1; }
.action-arrow {
    color: #94a3b8;
    flex-shrink: 0;
    transition: transform 0.2s;
}
.action-card:hover .action-arrow {
    color: #3b82f6;
    transform: translate(2px, -2px);
}

/* ── Banner informativo ── */
.info-banner {
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    border: 1px solid;
}
.banner-administrador { background: #fefce8; border-color: #fde68a; color: #713f12; }
.banner-cajero { background: #f0fdf4; border-color: #bbf7d0; color: #14532d; }
.banner-ejecutivo_credito { background: #eef2ff; border-color: #c7d2fe; color: #312e81; }
.banner-cliente { background: #f8fafc; border-color: #e2e8f0; color: #334155; }

.banner-icon { flex-shrink: 0; margin-top: 2px; }
.banner-content strong { font-size: 0.9375rem; font-weight: 700; display: block; margin-bottom: 0.25rem; }
.banner-content p { margin: 0; font-size: 0.875rem; opacity: 0.85; line-height: 1.5; }

/* ── Responsive ── */
@media (max-width: 640px) {
    .dashboard-wrapper { padding: 1rem; gap: 1.25rem; }
    .welcome-header { padding: 1.25rem; }
    .welcome-title { font-size: 1.125rem; }
    .stats-grid { grid-template-columns: 1fr 1fr; }
    .actions-grid { grid-template-columns: 1fr 1fr; }
}
</style>
