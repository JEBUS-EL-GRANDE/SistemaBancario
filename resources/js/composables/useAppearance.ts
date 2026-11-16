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

const ACCENT_RGB: Record<AccentColor, string> = {
    pink:    '224 64 251',
    orange:  '245 166 35',
    teal:    '0 188 212',
    purple:  '124 77 255',
    red:     '255 82 82',
    emerald: '0 230 118',
};

function applyAccent(color: AccentColor) {
    document.documentElement.style.setProperty('--accent-bg', ACCENT_VARS[color]);
    document.documentElement.style.setProperty('--accent-color', `#${ACCENT_RGB[color].split(' ').map((n) => Number(n).toString(16).padStart(2, '0')).join('')}`);
    document.documentElement.style.setProperty('--accent-rgb', ACCENT_RGB[color]);
}

// ─── Background themes ──────────────────────────────────────────────────────────
const BG_VARS: Record<BgTheme, string> = {
    black:   '#000000',
    darkgray:'#111111',
    slate:   '#1a1a2e',
    darker:  '#0d0d14',
};

function hexToHsl(hex: string) {
    const r = parseInt(hex.slice(1, 3), 16) / 255;
    const g = parseInt(hex.slice(3, 5), 16) / 255;
    const b = parseInt(hex.slice(5, 7), 16) / 255;
    const max = Math.max(r, g, b);
    const min = Math.min(r, g, b);
    const l = (max + min) / 2;
    let h = 0;
    let s = 0;
    if (max !== min) {
        const d = max - min;
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
        if (max === r) h = (g - b) / d + (g < b ? 6 : 0);
        else if (max === g) h = (b - r) / d + 2;
        else h = (r - g) / d + 4;
        h /= 6;
    }
    return `${Math.round(h * 360)} ${Math.round(s * 100)}% ${Math.round(l * 100)}%`;
}

function applyBg(theme: BgTheme) {
    const hsl = hexToHsl(BG_VARS[theme]);
    const root = document.documentElement.style;
    root.setProperty('--bg-base', BG_VARS[theme]);
    root.setProperty('--background', hsl);
    root.setProperty('--card', hsl);
    root.setProperty('--popover', hsl);
    root.setProperty('--sidebar-background', hsl);
    root.setProperty('--foreground', '0 0% 98%');
    root.setProperty('--card-foreground', '0 0% 98%');
    root.setProperty('--popover-foreground', '0 0% 98%');
    root.setProperty('--sidebar-foreground', '0 0% 96%');
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

// ─── Card style ────────────────────────────────────────────────────────────────
function applyCardStyle(style: CardStyle) {
    document.documentElement.dataset.cardStyle = style;
}

// ─── Density ───────────────────────────────────────────────────────────────────
function applyDensity(density: Density) {
    document.documentElement.dataset.density = density;
}

// ─── Global init ───────────────────────────────────────────────────────────────
export function initializeCustomAppearance() {
    const accent    = (localStorage.getItem('accent-color') as AccentColor) || 'orange';
    const bg        = (localStorage.getItem('bg-theme')     as BgTheme)     || 'black';
    const font      = (localStorage.getItem('font-family')  as FontFamily)  || 'jakarta';
    const cardStyle = (localStorage.getItem('card-style')   as CardStyle)   || 'below';
    const density   = (localStorage.getItem('density')      as Density)     || 'balanced';
    applyAccent(accent);
    applyBg(bg);
    applyFont(font);
    applyCardStyle(cardStyle);
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
