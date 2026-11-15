import { onMounted, ref } from 'vue';

type Appearance = 'light' | 'dark' | 'system';
export type AccentColor = 'pink' | 'orange' | 'teal' | 'purple' | 'red' | 'emerald';
export type BgTheme = 'black' | 'darkgray' | 'slate' | 'darker';
export type FontFamily = 'jakarta' | 'manrope' | 'outfit';
export type CardStyle = 'below' | 'overlay';
export type Density = 'compact' | 'balanced' | 'spacious';

// ─── Accent colors ─────────────────────────────────────────────────────────────
const ACCENT_VARS: Record<AccentColor, string> = {
    pink:    'linear-gradient(135deg,#e040fb,#f06292)',
    orange:  '#f5a623',
    teal:    '#00bcd4',
    purple:  '#7c4dff',
    red:     '#ff5252',
    emerald: '#00e676',
};

function applyAccent(color: AccentColor) {
    const v = ACCENT_VARS[color];
    document.documentElement.style.setProperty('--accent-bg', v);
    // expose a plain rgb for text uses (strip gradient if needed)
    const plainMap: Record<AccentColor, string> = {
        pink: '#e040fb', orange: '#f5a623', teal: '#00bcd4',
        purple: '#7c4dff', red: '#ff5252', emerald: '#00e676',
    };
    document.documentElement.style.setProperty('--accent-color', plainMap[color]);
}

// ─── Background themes ──────────────────────────────────────────────────────────
const BG_VARS: Record<BgTheme, string> = {
    black:   '#000000',
    darkgray:'#111111',
    slate:   '#1a1a2e',
    darker:  '#0d0d14',
};

function applyBg(theme: BgTheme) {
    document.documentElement.style.setProperty('--bg-base', BG_VARS[theme]);
}

// ─── Fonts ─────────────────────────────────────────────────────────────────────
const FONT_STACK: Record<FontFamily, string> = {
    jakarta: '"Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif',
    manrope: '"Manrope", ui-sans-serif, system-ui, sans-serif',
    outfit:  '"Outfit", ui-sans-serif, system-ui, sans-serif',
};

function applyFont(font: FontFamily) {
    document.documentElement.style.setProperty('--font-family', FONT_STACK[font]);
    document.body.style.fontFamily = FONT_STACK[font];
}

// ─── Density ───────────────────────────────────────────────────────────────────
function applyDensity(density: Density) {
    document.documentElement.dataset.density = density;
}

// ─── Global init ───────────────────────────────────────────────────────────────
export function initializeCustomAppearance() {
    const accent  = (localStorage.getItem('accent-color')  as AccentColor)  || 'orange';
    const bg      = (localStorage.getItem('bg-theme')      as BgTheme)      || 'black';
    const font    = (localStorage.getItem('font-family')   as FontFamily)   || 'jakarta';
    const density = (localStorage.getItem('density')       as Density)      || 'balanced';
    applyAccent(accent);
    applyBg(bg);
    applyFont(font);
    applyDensity(density);
}

// ─── Composable ────────────────────────────────────────────────────────────────
export function useCustomAppearance() {
    const accentColor = ref<AccentColor>('orange');
    const bgTheme     = ref<BgTheme>('black');
    const fontFamily  = ref<FontFamily>('jakarta');
    const cardStyle   = ref<CardStyle>('below');
    const density     = ref<Density>('balanced');

    onMounted(() => {
        accentColor.value = (localStorage.getItem('accent-color') as AccentColor)  || 'orange';
        bgTheme.value     = (localStorage.getItem('bg-theme')     as BgTheme)      || 'black';
        fontFamily.value  = (localStorage.getItem('font-family')  as FontFamily)   || 'jakarta';
        cardStyle.value   = (localStorage.getItem('card-style')   as CardStyle)    || 'below';
        density.value     = (localStorage.getItem('density')      as Density)      || 'balanced';
    });

    function setAccent(c: AccentColor) {
        accentColor.value = c;
        localStorage.setItem('accent-color', c);
        applyAccent(c);
    }
    function setBg(t: BgTheme) {
        bgTheme.value = t;
        localStorage.setItem('bg-theme', t);
        applyBg(t);
    }
    function setFont(f: FontFamily) {
        fontFamily.value = f;
        localStorage.setItem('font-family', f);
        applyFont(f);
    }
    function setCardStyle(s: CardStyle) {
        cardStyle.value = s;
        localStorage.setItem('card-style', s);
    }
    function setDensity(d: Density) {
        density.value = d;
        localStorage.setItem('density', d);
        applyDensity(d);
    }

    return { accentColor, bgTheme, fontFamily, cardStyle, density, setAccent, setBg, setFont, setCardStyle, setDensity };
}

// ─── Original theme (light / dark / system) ─────────────────────────────────
export function updateTheme(value: Appearance) {
    if (value === 'system') {
        const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        document.documentElement.classList.toggle('dark', systemTheme === 'dark');
    } else {
        document.documentElement.classList.toggle('dark', value === 'dark');
    }
}

const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

const handleSystemThemeChange = () => {
    const currentAppearance = localStorage.getItem('appearance') as Appearance | null;
    updateTheme(currentAppearance || 'system');
};

export function initializeTheme() {
    const savedAppearance = localStorage.getItem('appearance') as Appearance | null;
    updateTheme(savedAppearance || 'system');
    mediaQuery.addEventListener('change', handleSystemThemeChange);
}

export function useAppearance() {
    const appearance = ref<Appearance>('system');

    onMounted(() => {
        initializeTheme();
        const savedAppearance = localStorage.getItem('appearance') as Appearance | null;
        if (savedAppearance) {
            appearance.value = savedAppearance;
        }
    });

    function updateAppearance(value: Appearance) {
        appearance.value = value;
        localStorage.setItem('appearance', value);
        updateTheme(value);
    }

    return { appearance, updateAppearance };
}
