<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import {
    useCustomAppearance,
    type AccentColor,
    type BgTheme,
    type FontFamily,
    type CardStyle,
    type Density,
} from '@/composables/useAppearance';

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Appearance settings', href: '/settings/appearance' },
];

const { accentColor, bgTheme, fontFamily, cardStyle, density, setAccent, setBg, setFont, setCardStyle, setDensity } =
    useCustomAppearance();

// ─── Accent color options ───────────────────────────────────────────────────
const accentOptions: { value: AccentColor; bg: string; label: string }[] = [
    { value: 'pink',    bg: 'linear-gradient(135deg,#e040fb,#f06292)', label: 'Rosa' },
    { value: 'orange',  bg: '#f5a623',  label: 'Naranja' },
    { value: 'teal',    bg: '#00bcd4',  label: 'Turquesa' },
    { value: 'purple',  bg: '#7c4dff',  label: 'Violeta' },
    { value: 'red',     bg: '#ff5252',  label: 'Rojo' },
    { value: 'emerald', bg: '#00e676',  label: 'Esmeralda' },
];

// ─── Background theme options ───────────────────────────────────────────────
const bgOptions: { value: BgTheme; bg: string; label: string }[] = [
    { value: 'black',    bg: '#000000', label: 'Negro puro' },
    { value: 'darkgray', bg: '#111111', label: 'Gris oscuro' },
    { value: 'slate',    bg: '#1a1a2e', label: 'Pizarra' },
    { value: 'darker',   bg: '#0d0d14', label: 'Oscuro plus' },
];

// ─── Font options ───────────────────────────────────────────────────────────
const fontOptions: { value: FontFamily; label: string; preview: string }[] = [
    { value: 'jakarta', label: 'Plus Jakarta Sans', preview: '"Plus Jakarta Sans", sans-serif' },
    { value: 'manrope', label: 'Manrope',           preview: '"Manrope", sans-serif' },
    { value: 'outfit',  label: 'Outfit',            preview: '"Outfit", sans-serif' },
];

// ─── Card style options ─────────────────────────────────────────────────────
const cardStyleOptions: { value: CardStyle; label: string }[] = [
    { value: 'below',   label: 'Debajo' },
    { value: 'overlay', label: 'Superpuesto' },
];

// ─── Density options ────────────────────────────────────────────────────────
const densityOptions: { value: Density; label: string }[] = [
    { value: 'compact',   label: 'Compacto' },
    { value: 'balanced',  label: 'Equilibrado' },
    { value: 'spacious',  label: 'Espacioso' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Appearance settings" />

        <SettingsLayout>
            <div class="space-y-8">
                <HeadingSmall
                    title="Appearance settings"
                    description="Update your account's appearance settings"
                />

                <!-- Tema claro / oscuro / sistema (original) -->
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-widest text-amber-500">Tema</p>
                    <p class="text-xs text-slate-400">Modo claro, oscuro o según el sistema operativo.</p>
                    <AppearanceTabs />
                </div>

                <div class="border-t border-slate-800" />

                <!-- ── Accent color ─────────────────────────────────────────── -->
                <div class="space-y-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-amber-500">Color de acento</p>
                        <p class="mt-0.5 text-xs text-slate-400">Color principal para botones y elementos destacados.</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button
                            v-for="opt in accentOptions"
                            :key="opt.value"
                            @click="setAccent(opt.value)"
                            :title="opt.label"
                            class="relative h-11 w-11 rounded-full ring-2 ring-offset-2 ring-offset-slate-900 transition-all focus:outline-none"
                            :style="{ background: opt.bg }"
                            :class="accentColor === opt.value ? 'ring-white scale-110' : 'ring-transparent hover:ring-slate-500'"
                        >
                            <Check
                                v-if="accentColor === opt.value"
                                class="absolute inset-0 m-auto h-5 w-5 text-white drop-shadow"
                            />
                        </button>
                    </div>
                </div>

                <!-- ── Background theme ─────────────────────────────────────── -->
                <div class="space-y-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-amber-500">Tema de fondo</p>
                        <p class="mt-0.5 text-xs text-slate-400">Variante de fondo oscuro para toda la aplicación.</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button
                            v-for="opt in bgOptions"
                            :key="opt.value"
                            @click="setBg(opt.value)"
                            :title="opt.label"
                            class="relative h-12 w-12 rounded-xl border border-slate-700 ring-2 ring-offset-2 ring-offset-slate-900 transition-all focus:outline-none"
                            :style="{ background: opt.bg }"
                            :class="bgTheme === opt.value ? 'ring-white scale-110' : 'ring-transparent hover:ring-slate-500'"
                        >
                            <Check
                                v-if="bgTheme === opt.value"
                                class="absolute inset-0 m-auto h-5 w-5 text-white drop-shadow"
                            />
                        </button>
                    </div>
                </div>

                <!-- ── Font ────────────────────────────────────────────────── -->
                <div class="space-y-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-amber-500">Tipografía</p>
                        <p class="mt-0.5 text-xs text-slate-400">Fuente principal de la interfaz.</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button
                            v-for="opt in fontOptions"
                            :key="opt.value"
                            @click="setFont(opt.value)"
                            class="flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm transition-all focus:outline-none"
                            :class="
                                fontFamily === opt.value
                                    ? 'border-amber-500 bg-amber-500/10 text-amber-400 ring-1 ring-amber-500'
                                    : 'border-slate-700 text-slate-300 hover:border-slate-500 hover:text-white'
                            "
                        >
                            <span
                                class="text-base font-bold"
                                :style="{ fontFamily: opt.preview }"
                            >Aa</span>
                            <span :style="{ fontFamily: opt.preview }">{{ opt.label }}</span>
                        </button>
                    </div>
                </div>

                <!-- ── Card style ──────────────────────────────────────────── -->
                <div class="space-y-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-amber-500">Estilo de tarjeta</p>
                        <p class="mt-0.5 text-xs text-slate-400">Cómo se muestran las tarjetas de contenido.</p>
                    </div>
                    <div class="flex gap-2">
                        <button
                            v-for="opt in cardStyleOptions"
                            :key="opt.value"
                            @click="setCardStyle(opt.value)"
                            class="rounded-full px-5 py-1.5 text-sm font-medium transition-all focus:outline-none"
                            :class="
                                cardStyle === opt.value
                                    ? 'bg-amber-500 text-black shadow'
                                    : 'bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white'
                            "
                        >
                            {{ opt.label }}
                        </button>
                    </div>
                </div>

                <!-- ── Browsing density ────────────────────────────────────── -->
                <div class="space-y-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-amber-500">Densidad de navegación</p>
                        <p class="mt-0.5 text-xs text-slate-400">Ajusta el espaciado general de la interfaz.</p>
                    </div>
                    <div class="flex gap-2">
                        <button
                            v-for="opt in densityOptions"
                            :key="opt.value"
                            @click="setDensity(opt.value)"
                            class="rounded-full px-5 py-1.5 text-sm font-medium transition-all focus:outline-none"
                            :class="
                                density === opt.value
                                    ? 'bg-amber-500 text-black shadow'
                                    : 'bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white'
                            "
                        >
                            {{ opt.label }}
                        </button>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
