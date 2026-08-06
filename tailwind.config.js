import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: ['class'],
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.{vue,js,ts,jsx,tsx}',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                serif: ['Instrument Serif', ...defaultTheme.fontFamily.serif],
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },
            animation: {
                'hh-rise': 'hh-rise 0.35s ease both',
                'hh-pop': 'hh-pop 0.4s ease both',
                'hh-toast': 'hh-toast 0.26s cubic-bezier(0.2, 0.9, 0.3, 1.3) both',
                'hh-glow': 'hh-glow 2.4s ease infinite',
            },
            boxShadow: {
                hh: 'var(--hh-shadow)',
            },
            borderRadius: {
                lg: 'var(--radius)',
                md: 'calc(var(--radius) - 2px)',
                sm: 'calc(var(--radius) - 4px)',
            },
            colors: {
                // HouseHub design tokens — see the `--hh-*` block in app.css.
                hh: {
                    bg: 'var(--hh-bg)',
                    shell: 'var(--hh-shell)',
                    panel: 'var(--hh-panel)',
                    card: 'var(--hh-card)',
                    soft: 'var(--hh-soft)',
                    sunk: 'var(--hh-sunk)',
                    ink: 'var(--hh-ink)',
                    ink2: 'var(--hh-ink2)',
                    ink3: 'var(--hh-ink3)',
                    line: 'var(--hh-line)',
                    onhero: 'var(--hh-onhero)',
                    onhero2: 'var(--hh-onhero2)',
                    herofilm: 'var(--hh-herofilm)',
                    heroline: 'var(--hh-heroline)',
                    ontint: 'var(--hh-ontint)',
                    coral: 'var(--hh-coral)',
                    mint: 'var(--hh-mint)',
                    lilac: 'var(--hh-lilac)',
                    sun: 'var(--hh-sun)',
                    sky: 'var(--hh-sky)',
                },
                background: 'hsl(var(--background))',
                foreground: 'hsl(var(--foreground))',
                card: {
                    DEFAULT: 'hsl(var(--card))',
                    foreground: 'hsl(var(--card-foreground))',
                },
                popover: {
                    DEFAULT: 'hsl(var(--popover))',
                    foreground: 'hsl(var(--popover-foreground))',
                },
                primary: {
                    DEFAULT: 'hsl(var(--primary))',
                    foreground: 'hsl(var(--primary-foreground))',
                },
                secondary: {
                    DEFAULT: 'hsl(var(--secondary))',
                    foreground: 'hsl(var(--secondary-foreground))',
                },
                muted: {
                    DEFAULT: 'hsl(var(--muted))',
                    foreground: 'hsl(var(--muted-foreground))',
                },
                accent: {
                    DEFAULT: 'hsl(var(--accent))',
                    foreground: 'hsl(var(--accent-foreground))',
                },
                destructive: {
                    DEFAULT: 'hsl(var(--destructive))',
                    foreground: 'hsl(var(--destructive-foreground))',
                },
                border: 'hsl(var(--border))',
                input: 'hsl(var(--input))',
                ring: 'hsl(var(--ring))',
                chart: {
                    1: 'hsl(var(--chart-1))',
                    2: 'hsl(var(--chart-2))',
                    3: 'hsl(var(--chart-3))',
                    4: 'hsl(var(--chart-4))',
                    5: 'hsl(var(--chart-5))',
                },
                sidebar: {
                    DEFAULT: 'hsl(var(--sidebar-background))',
                    foreground: 'hsl(var(--sidebar-foreground))',
                    primary: 'hsl(var(--sidebar-primary))',
                    'primary-foreground': 'hsl(var(--sidebar-primary-foreground))',
                    accent: 'hsl(var(--sidebar-accent))',
                    'accent-foreground': 'hsl(var(--sidebar-accent-foreground))',
                    border: 'hsl(var(--sidebar-border))',
                    ring: 'hsl(var(--sidebar-ring))',
                },
            },
        },
    },
    plugins: [require('tailwindcss-animate')],
};
