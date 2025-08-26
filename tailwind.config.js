    // tailwind.config.js
    import defaultTheme from 'tailwindcss/defaultTheme'
    import forms from '@tailwindcss/forms'

    /** @type {import('tailwindcss').Config} */
    export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/css/**/*.css',
        './resources/**/*.js',
        './resources/**/*.vue', // si usas Vue; si no, la puedes quitar
    ],
    theme: {
        extend: {
        fontFamily: {
            sans: ['Figtree', ...defaultTheme.fontFamily.sans],
        },
        },
    },
    plugins: [
        forms, // 👈 solo mantenemos forms, line-clamp ya viene incluido
    ],
    }
