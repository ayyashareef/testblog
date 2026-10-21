import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/Services/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                ink: {
                    950: '#070b14',
                    900: '#0b1220',
                    800: '#121b2d',
                    700: '#1c2840',
                    600: '#2a3a57',
                    400: '#7d8ba6',
                    300: '#a7b3c9',
                    100: '#e6ebf3',
                },
                ember: {
                    300: '#fde68a',
                    400: '#f8b24a',
                    500: '#ea741a',
                    700: '#7c2d12',
                },
                lagoon: '#2dd4bf',
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                mono: ['"JetBrains Mono"', ...defaultTheme.fontFamily.mono],
                thaana: ['"Noto Sans Thaana"', 'Faruma', 'MV Boli', 'sans-serif'],
            },
        },
    },
    plugins: [],
};
