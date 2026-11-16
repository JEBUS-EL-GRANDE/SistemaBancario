<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />

        <script>
            (function() {
                try {
                    const appearance = localStorage.getItem('appearance') || 'system';
                    const isDark = appearance === 'dark' || (appearance === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                    if (isDark) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                    const font = localStorage.getItem('font-family') || 'jakarta';
                    const fontMap = {
                        jakarta: '"Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif',
                        manrope: '"Manrope", ui-sans-serif, system-ui, sans-serif',
                        outfit: '"Outfit", ui-sans-serif, system-ui, sans-serif',
                    };
                    if (fontMap[font]) {
                        document.documentElement.style.setProperty('--font-sans', fontMap[font]);
                        document.documentElement.style.setProperty('--font-family', fontMap[font]);
                    }
                    const cardStyle = localStorage.getItem('card-style') || 'below';
                    document.documentElement.dataset.cardStyle = cardStyle;
                    const density = localStorage.getItem('density') || 'balanced';
                    document.documentElement.dataset.density = density;
                } catch (e) {}
            })();
        </script>

        @routes
        @vite(['resources/js/app.ts'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
