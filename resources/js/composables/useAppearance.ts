import { onMounted, ref } from 'vue';

export type Appearance = 'light' | 'dark' | 'system';
export type AccentColor = 'pink' | 'orange' | 'teal' | 'purple' | 'red' | 'emerald';
export type BgTheme = 'black' | 'darkgray' | 'slate' | 'darker';
export type FontFamily = 'jakarta' | 'manrope' | 'outfit';
export type CardStyle = 'below' | 'overlay';
export type Density = 'compact' | 'balanced' | 'spacious';

// ─── Accent Colors ─────────────────────────────────────────────────────────────
export const ACCENT_CONFIG: Record<
    AccentColor,
    { primary: string; gradient: string; rgb: string; label: string }
> = {
    pink: {
        primary: '#e040fb',
        gradient: 'linear-gradient(135deg, #e040fb, #f06292)',
        rgb: '224 64 251',
        label: 'Rosa',
    },
    orange: {
        primary: '#f5a623',
        gradient: 'linear-gradient(135deg, #f5a623, #ff7043)',
        rgb: '245 166 35',
        label: 'Naranja',
    },
    teal: {
        primary: '#00bcd4',
        gradient: 'linear-gradient(135deg, #00bcd4, #26c6da)',
        rgb: '0 188 212',
        label: 'Turquesa',
    },
    purple: {
        primary: '#7c4dff',
        gradient: 'linear-gradient(135deg, #7c4dff, #651fff)',
        rgb: '124 77 255',
        label: 'Violeta',
    },
    red: {
        primary: '#ff5252',
        gradient: 'linear-gradient(135deg, #ff5252, #ff1744)',
        rgb: '255 82 82',
        label: 'Rojo',
    },
    emerald: {
        primary: '#00e676',
        gradient: 'linear-gradient(135deg, #00e676, #00c853)',
        rgb: '0 230 118',
        label: 'Esmeralda',
    },
};

export function applyAccent(color: AccentColor) {
    const cfg = ACCENT_CONFIG[color] || ACCENT_CONFIG.orange;
    const root = document.documentElement.style;
    root.setProperty('--accent-color', cfg.primary);
    root.setProperty('--accent-bg', cfg.gradient);
    root.setProperty('--accent-rgb', cfg.rgb);
    root.setProperty('--ring', cfg.primary);
    root.setProperty('--sidebar-ring', cfg.primary);
}

// ─── Background & Theme Configurations ─────────────────────────────────────────
export const DARK_BG_CONFIG: Record<
    BgTheme,
    {
        bg: string;
        bgHsl: string;
        sidebarHsl: string;
        cardHsl: string;
        borderHsl: string;
        label: string;
    }
> = {
    black: {
        bg: '#000000',
        bgHsl: '0 0% 0%',
        sidebarHsl: '0 0% 5%',
        cardHsl: '0 0% 7%',
        borderHsl: '0 0% 16%',
        label: 'Negro puro',
    },
    darkgray: {
        bg: '#111114',
        bgHsl: '240 5% 7%',
        sidebarHsl: '240 6% 4%',
        cardHsl: '240 5% 12%',
        borderHsl: '240 5% 18%',
        label: 'Gris oscuro',
    },
    slate: {
        bg: '#0f172a',
        bgHsl: '222 47% 11%',
        sidebarHsl: '224 71% 5%',
        cardHsl: '217 33% 16%',
        borderHsl: '217 33% 22%',
        label: 'Pizarra',
    },
    darker: {
        bg: '#0d0d14',
        bgHsl: '240 21% 6%',
        sidebarHsl: '240 33% 3%',
        cardHsl: '240 18% 11%',
        borderHsl: '240 14% 17%',
        label: 'Oscuro plus',
    },
};

export const LIGHT_CONFIG = {
    bgHsl: '210 20% 98%',          // slate-50 #f8fafc
    sidebarHsl: '0 0% 100%',       // pure white #ffffff
    cardHsl: '0 0% 100%',          // pure white
    borderHsl: '214 32% 91%',      // slate-200 #e2e8f0
    foregroundHsl: '222 47% 11%',  // slate-900 #0f172a
    cardForegroundHsl: '222 47% 11%',
    mutedForegroundHsl: '215 16% 47%',
};

export function applyThemeAndBg(appearanceMode?: Appearance, bgVariant?: BgTheme) {
    const currentAppearance =
        appearanceMode ?? ((localStorage.getItem('appearance') as Appearance) || 'system');
    const currentBg =
        bgVariant ?? ((localStorage.getItem('bg-theme') as BgTheme) || 'darker');

    const isDark =
        currentAppearance === 'system'
            ? window.matchMedia('(prefers-color-scheme: dark)').matches
            : currentAppearance === 'dark';

    document.documentElement.classList.toggle('dark', isDark);

    const root = document.documentElement.style;

    if (isDark) {
        const cfg = DARK_BG_CONFIG[currentBg] || DARK_BG_CONFIG.darker;
        root.setProperty('--bg-base', cfg.bg);
        root.setProperty('--background', cfg.bgHsl);
        root.setProperty('--sidebar-background', cfg.sidebarHsl);
        root.setProperty('--card', cfg.cardHsl);
        root.setProperty('--popover', cfg.cardHsl);
        root.setProperty('--border', cfg.borderHsl);
        root.setProperty('--sidebar-border', cfg.borderHsl);
        root.setProperty('--foreground', '0 0% 98%');
        root.setProperty('--card-foreground', '0 0% 98%');
        root.setProperty('--popover-foreground', '0 0% 98%');
        root.setProperty('--sidebar-foreground', '0 0% 96%');
        root.setProperty('--muted-foreground', '215 20% 65%');
        root.setProperty('--sidebar-accent', cfg.cardHsl);
        root.setProperty('--sidebar-accent-foreground', '0 0% 98%');
    } else {
        root.setProperty('--bg-base', '#f8fafc');
        root.setProperty('--background', LIGHT_CONFIG.bgHsl);
        root.setProperty('--sidebar-background', LIGHT_CONFIG.sidebarHsl);
        root.setProperty('--card', LIGHT_CONFIG.cardHsl);
        root.setProperty('--popover', LIGHT_CONFIG.cardHsl);
        root.setProperty('--border', LIGHT_CONFIG.borderHsl);
        root.setProperty('--sidebar-border', LIGHT_CONFIG.borderHsl);
        root.setProperty('--foreground', LIGHT_CONFIG.foregroundHsl);
        root.setProperty('--card-foreground', LIGHT_CONFIG.cardForegroundHsl);
        root.setProperty('--popover-foreground', LIGHT_CONFIG.cardForegroundHsl);
        root.setProperty('--sidebar-foreground', LIGHT_CONFIG.foregroundHsl);
        root.setProperty('--muted-foreground', LIGHT_CONFIG.mutedForegroundHsl);
        root.setProperty('--sidebar-accent', '210 20% 94%');
        root.setProperty('--sidebar-accent-foreground', LIGHT_CONFIG.foregroundHsl);
    }
}

// ─── Fonts ─────────────────────────────────────────────────────────────────────
export const FONT_STACK: Record<FontFamily, string> = {
    jakarta: '"Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif',
    manrope: '"Manrope", ui-sans-serif, system-ui, sans-serif',
    outfit:  '"Outfit", ui-sans-serif, system-ui, sans-serif',
};

export function applyFont(font: FontFamily) {
    const stack = FONT_STACK[font] || FONT_STACK.jakarta;
    document.documentElement.style.setProperty('--font-sans', stack);
    document.documentElement.style.setProperty('--font-family', stack);
    document.body.style.fontFamily = stack;
}

// ─── Card Style ────────────────────────────────────────────────────────────────
export function applyCardStyle(style: CardStyle) {
    document.documentElement.dataset.cardStyle = style;
}

// ─── Density ───────────────────────────────────────────────────────────────────
export function applyDensity(density: Density) {
    document.documentElement.dataset.density = density;
}

// ─── Global Initializers ───────────────────────────────────────────────────────
const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
const handleSystemThemeChange = () => {
    const currentAppearance = localStorage.getItem('appearance') as Appearance | null;
    if (currentAppearance === 'system' || !currentAppearance) {
        applyThemeAndBg('system');
    }
};

export function initializeTheme() {
    const savedAppearance = (localStorage.getItem('appearance') as Appearance | null) || 'system';
    const savedBg = (localStorage.getItem('bg-theme') as BgTheme | null) || 'darker';
    applyThemeAndBg(savedAppearance, savedBg);
    mediaQuery.removeEventListener('change', handleSystemThemeChange);
    mediaQuery.addEventListener('change', handleSystemThemeChange);
}

export function updateTheme(value: Appearance) {
    applyThemeAndBg(value);
}

export function initializeCustomAppearance() {
    const accent    = (localStorage.getItem('accent-color') as AccentColor) || 'orange';
    const bg        = (localStorage.getItem('bg-theme')     as BgTheme)     || 'darker';
    const font      = (localStorage.getItem('font-family')  as FontFamily)  || 'jakarta';
    const cardStyle = (localStorage.getItem('card-style')   as CardStyle)   || 'below';
    const density   = (localStorage.getItem('density')      as Density)     || 'balanced';
    const theme     = (localStorage.getItem('appearance')   as Appearance)  || 'system';

    applyAccent(accent);
    applyThemeAndBg(theme, bg);
    applyFont(font);
    applyCardStyle(cardStyle);
    applyDensity(density);
}

// ─── Composables ───────────────────────────────────────────────────────────────
export function useCustomAppearance() {
    const accentColor = ref<AccentColor>('orange');
    const bgTheme     = ref<BgTheme>('darker');
    const fontFamily  = ref<FontFamily>('jakarta');
    const cardStyle   = ref<CardStyle>('below');
    const density     = ref<Density>('balanced');

    onMounted(() => {
        accentColor.value = (localStorage.getItem('accent-color') as AccentColor)  || 'orange';
        bgTheme.value     = (localStorage.getItem('bg-theme')     as BgTheme)      || 'darker';
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
        applyThemeAndBg(undefined, t);
    }

    function setFont(f: FontFamily) {
        fontFamily.value = f;
        localStorage.setItem('font-family', f);
        applyFont(f);
    }

    function setCardStyle(s: CardStyle) {
        cardStyle.value = s;
        localStorage.setItem('card-style', s);
        applyCardStyle(s);
    }

    function setDensity(d: Density) {
        density.value = d;
        localStorage.setItem('density', d);
        applyDensity(d);
    }

    return {
        accentColor,
        bgTheme,
        fontFamily,
        cardStyle,
        density,
        setAccent,
        setBg,
        setFont,
        setCardStyle,
        setDensity,
    };
}

export function useAppearance() {
    const appearance = ref<Appearance>('system');

    onMounted(() => {
        const savedAppearance = localStorage.getItem('appearance') as Appearance | null;
        if (savedAppearance) {
            appearance.value = savedAppearance;
        }
    });

    function updateAppearance(value: Appearance) {
        appearance.value = value;
        localStorage.setItem('appearance', value);
        applyThemeAndBg(value);
    }

    return { appearance, updateAppearance };
}
