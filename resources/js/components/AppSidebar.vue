<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { Link, usePage } from '@inertiajs/vue3';
import {
    ArrowDownLeft,
    ArrowLeftRight,
    ArrowUpRight,
    Calculator,
    CreditCard,
    FileText,
    Folder,
    LayoutGrid,
    Shield,
    Users,
    Wallet,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();
const auth = computed(() => page.props.auth as { user?: { rol?: string; name?: string } });
const rol = computed(() => auth.value?.user?.rol ?? 'administrador');

const mainNavItems = computed(() => {
    if (rol.value === 'cliente') {
        return [
            {
                title: 'Mi Banca Digital',
                href: '/dashboard',
                icon: LayoutGrid,
            },
            {
                title: 'Mis Cuentas',
                href: '/cuentas',
                icon: Wallet,
            },
            {
                title: 'Transferencias',
                href: '/transferencias',
                icon: ArrowLeftRight,
            },
            {
                title: 'Simulador Cuotas',
                href: '/prestamos/simulador',
                icon: Calculator,
            },
        ];
    }

    const items = [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'Clientes',
            href: '/clientes',
            icon: Users,
        },
        {
            title: 'Cuentas Bancarias',
            href: '/cuentas',
            icon: Wallet,
        },
    ];

    if (rol.value === 'administrador' || rol.value === 'cajero') {
        items.push(
            {
                title: 'Depósitos',
                href: '/depositos',
                icon: ArrowDownLeft,
            },
            {
                title: 'Retiros',
                href: '/retiros',
                icon: ArrowUpRight,
            },
            {
                title: 'Transferencias',
                href: '/transferencias',
                icon: ArrowLeftRight,
            },
            {
                title: 'Pago Préstamos',
                href: '/pagos-prestamo/create',
                icon: CreditCard,
            }
        );
    }

    if (rol.value === 'administrador' || rol.value === 'ejecutivo_credito') {
        items.push({
            title: 'Préstamos y Créditos',
            href: '/prestamos',
            icon: CreditCard,
        });
    }

    items.push({
        title: 'Simulador Cuotas',
        href: '/prestamos/simulador',
        icon: Calculator,
    });

    if (rol.value === 'administrador' || rol.value === 'cajero') {
        items.push({
            title: 'Reportes y Auditoría',
            href: '/reportes',
            icon: FileText,
        });
    }

    if (rol.value === 'administrador') {
        items.push({
            title: 'Gestión Usuarios',
            href: '/usuarios',
            icon: Shield,
        });
    }

    return items;
});

const footerNavItems = [
    {
        title: 'Sistema Bancario v1.0',
        href: '/dashboard',
        icon: Folder,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/dashboard" class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-amber-500 to-amber-300 text-slate-950 font-bold shadow-md shadow-amber-500/20">
                                {{ (page.props.auth as any)?.user?.banco?.codigo?.substring(0, 2) ?? 'SB' }}
                            </div>
                            <div class="flex flex-col text-left">
                                <span class="font-bold text-sm leading-tight text-white tracking-wide">
                                    {{ (page.props.auth as any)?.user?.banco?.nombre ?? 'Sistema Bancario' }}
                                </span>
                                <span class="text-[11px] text-amber-400 font-medium">
                                    {{ (page.props.auth as any)?.user?.banco?.sigla ?? 'Plataforma Multi-Banco' }}
                                </span>
                            </div>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
