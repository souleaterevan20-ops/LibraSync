import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Dark red / maroon — primary brand color
                maroon: {
                    50: '#fdf3f2',
                    100: '#fbe4e1',
                    200: '#f5c7c1',
                    300: '#e79f95',
                    400: '#d5716a',
                    500: '#bc4c42',
                    600: '#9c352c',
                    700: '#7c1e18', // primary
                    800: '#661a16',
                    900: '#4a1310',
                    950: '#2c0a08',
                },
                // Soft brown — secondary / warm neutral
                bark: {
                    50: '#faf7f2',
                    100: '#f2ebe0',
                    200: '#e3d3bd',
                    300: '#cfb494',
                    400: '#b8936d',
                    500: '#a17a54',
                    600: '#816044',
                    700: '#634a36',
                    800: '#4a382a',
                    900: '#332720',
                    950: '#1f1713',
                },
                // Parchment — warm off-white background
                parchment: {
                    50: '#fdfbf7',
                    100: '#f9f3e9',
                    200: '#f1e6d3',
                },
            },
        },
    },

    plugins: [forms],
};
