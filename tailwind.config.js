import defaultTheme from 'tailwindcss/defaultTheme';

const token = (name) => `rgb(var(--${name}) / <alpha-value>)`;

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            // Colours come from CSS variables in app.css so one set of
            // classes serves both the light and the dark theme.
            colors: {
                page: token('page'),
                sunken: token('sunken'),
                line: token('line'),
                ink: token('ink'),
                muted: token('muted'),
                accent: token('accent'),
                'accent-ink': token('accent-ink'),
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                mono: ['"JetBrains Mono"', ...defaultTheme.fontFamily.mono],
                thaana: ['"Noto Sans Thaana"', 'Faruma', 'MV Boli', 'sans-serif'],
            },
            maxWidth: {
                prose: '46rem',
            },
        },
    },
    plugins: [],
};
