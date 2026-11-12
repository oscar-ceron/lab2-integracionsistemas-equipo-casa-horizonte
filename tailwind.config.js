import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Instrument Sans"', ...defaultTheme.fontFamily.sans],
                display: ['Fraunces', 'Georgia', ...defaultTheme.fontFamily.serif],
            },
            // Neutros cálidos (papel) y azul marino del logo reemplazan gray e indigo de Breeze.
            colors: {
                gray: {
                    50: '#fbfaf7', 100: '#f6f5f1', 200: '#e9e6df', 300: '#d6d2c8', 400: '#a09b8d',
                    500: '#6f6b60', 600: '#55524a', 700: '#3b3933', 800: '#25241f', 900: '#171612',
                },
                indigo: {
                    50: '#eef1f9', 100: '#dfe5f4', 200: '#c3cde8', 300: '#9aaad6', 400: '#6b80bd',
                    500: '#41589f', 600: '#2c4087', 700: '#1f3070', 800: '#16245a', 900: '#0f1a45',
                },
                gold: { 300: '#e0c88f', 400: '#d0ae6b', 500: '#b8924a', 600: '#96742f' },
            },
            transitionTimingFunction: {
                snap: 'cubic-bezier(0.23, 1, 0.32, 1)',
            },
        },
    },

    plugins: [forms],
};